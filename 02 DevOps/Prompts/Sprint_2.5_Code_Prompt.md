# Sprint 2.5 — Code Task Execution Prompt

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 2.5 — Integration Gate
**Prepared by:** Taskmaster
**For:** Claude Code + Cowork agents (DevOps / Cyber Ops / Foreman roles)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Sprint 2.5 without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — GET YOUR CREDENTIALS

**⚠️ Before touching ANY endpoint or running ANY test, ask Yeti for:**

1. **WordPress Application Password** — required for all authenticated REST API calls
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.
   - Assume it rotates within hours.

2. **GitLab PAT** (optional) — `api` scope, for closing issues and committing results

**Do not proceed past this section until you have the Application Password.**

---

## 1. MISSION

Verify the core editorial pipeline and security stack of **The Markdown** still function correctly with 85+ active snippets before building Sprint 3 on top. This is a **verification gate** — no new features, no new code. Just confirming the foundation is solid.

**Sprint 2.5 scope:** 6 tasks (~4h), all VERIFY or OPS. Zero new snippets.

**What changed since Sprint 1.5 gate:**
- Snippet count went from 31 active → 85+ active (Sprint 2 Gate 4 + Gate 5 deployed)
- Social API connectors added (X, LinkedIn, YouTube, Medium)
- AI reformatter, quick-publish panel, publish dispatch all deployed
- RSS output feed added
- Security surface area expanded significantly

**What we're NOT testing (deferred to Sprint 3.5):**
- Social API OAuth flows, token refresh, post dispatch
- AI content reformatter chain
- Cross-platform posting pipeline
- Instagram (BLK-001)

---

## 2. PLATFORM — READ THIS FIRST

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — large payloads get blocked. Keep snippets <80 lines. One function per snippet.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`
- **Auth:** HTTP Basic — username `h3ndriksj` + Application Password (ask Yeti at session start).
- **Custom namespace:** `ns/v1`
- **Custom Post Type:** `ns_feed_item` (rest_base: `feed-items`)

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — fastest, most reliable. Use for health checks, reading data, snippet status.
2. **GitLab REST API** — for issue tracking, commits. PAT with `api` scope from Yeti.
3. **Admin AJAX** — for nonce-protected admin actions only.
4. **Browser automation** — LAST RESORT. Only for visual QA or nonce-dependent admin actions with no REST equivalent.

**At the start of every task, state your tool choice and reasoning.**

### Lego Block Micro-Snippet Method (DR-0016) — Applies to ALL Verification

Even though Sprint 2.5 writes no new code, all verification **must be performed through the WordPress REST API**, not through the admin UI or browser automation. This matches the project's Lego block architecture:

- **Query snippets via REST:** `GET /wp-json/code-snippets/v1/snippets` — inventory, status, load order
- **Check health via REST:** `GET /wp-json/ns/v1/diagnostics/health` — system status, error counts
- **Read feed items via REST:** `GET /wp-json/wp/v2/feed-items` — editorial pipeline verification
- **Read blocks via REST:** `GET /wp-json/ns/v1/blocks` — page render verification
- **Auth check via REST:** `GET /wp-json/wp/v2/users/me` — credential verification

**Every test that CAN be done via REST API MUST be done via REST API.** Browser automation is only for visual QA steps that have no REST equivalent (e.g., confirming commentary card CSS renders correctly on the front-end).

The micro-snippet architecture means every function is independently testable through the API surface. Use that to your advantage — test each layer of the dependency map independently before testing the full chain.

---

## 3. EXISTING INFRASTRUCTURE — What You Can Call

### REST Endpoints (confirmed working in S1.5.1)

| Endpoint | Method | Auth | Response |
|----------|--------|------|----------|
| `ns/v1/diagnostics/health` | GET | No | `{"status":"healthy","errors_24h":0,...}` |
| `ns/v1/blocks` | GET | No | 7-block layout data + dateline |
| `wp/v2/feed-items` | GET | No (read) | Feed items from `ns_feed_item` CPT |
| `code-snippets/v1/snippets` | GET | Yes | All snippet metadata |
| `wp/v2/users/me` | GET | Yes | Auth verification |

### Key Functions Available

| Function | Snippet | Purpose |
|----------|---------|---------|
| `ns_diag_write($level, $component, $message, $context)` | ID 37 (priority 2) | Structured diagnostic logging |
| `ns_log($message, $level)` | ID 9 (Core Utilities) | Forwards to ns_diag_write() |
| `ns_vault_store($service, $token)` | ID 30 (Token Vault) | Encrypt + store token |
| `ns_vault_retrieve($service)` | ID 30 (Token Vault) | Decrypt + return token |
| `ns_audit_log($action, $target_type, $target_id, $label, $details)` | ID 32 (Audit Log) | Write to audit table |

### Current Snippet Scale

| Layer | Count | Notes |
|-------|-------|-------|
| Sprint 0 (Security) | 2 active | Hardening + visual fixes |
| Sprint 1 Gate 1 (Data Model) | 1 active | Monolithic — 640 lines (flagged) |
| Sprint 1 Gate 2 (RSS/AI) | 16 active | Micro-snippet pattern — reference architecture |
| Sprint 1 Gate 3 (Foundation) | 12 active | Monolithic — flagged for future refactor |
| Sprint 2 Gate 4 (Social APIs) | 21 active | X, LinkedIn, YouTube, Medium + security |
| Sprint 2 Gate 5 (AI + Publish) | 33+ active | Reformatter, quick-publish, dispatch |
| **Total** | **85+** | Significant scale increase since S1.5 gate (was 31) |

---

## 4. YOUR 6 TASKS — Execute in This Order

### Task 1: S2.5.3 — Snippet Inventory Update (0.5h)
**Owner:** DevOps | **Type:** OPS

Produce an updated inventory of all active Code Snippets at current scale:

1. Query WordPress REST API: `GET /wp-json/code-snippets/v1/snippets`
2. Count total, active, inactive
3. Compare against S1.5.3 baseline (was 37 total, 31 active)
4. Verify load order — no priority conflicts at 85+ scale
5. Check for any deactivated or errored snippets that shouldn't be
6. Flag any snippets over 150 lines (practical ceiling per DR-0016)

**Output file:** `02 DevOps/S2.5.3_Snippet_Inventory.md`
Include: count summary, any drift from expected, load order issues, flagged snippets.

---

### Task 2: S2.5.4 — REST Endpoint Health Check (0.5h)
**Owner:** DevOps | **Type:** OPS

Verify all custom REST endpoints are responding correctly:

| Endpoint | Method | Expected Status | Expected Body |
|----------|--------|----------------|---------------|
| `/wp-json/ns/v1/diagnostics/health` | GET | 200 | `"status":"healthy"`, `"errors_24h":0` |
| `/wp-json/ns/v1/blocks` | GET | 200 | 7-block JSON with dateline |
| `/wp-json/wp/v2/feed-items` | GET | 200 | Array of feed items |
| `/wp-json/wp/v2/feed-items?per_page=1` | GET | 200 | Single item with all meta fields |
| `/wp-json/code-snippets/v1/snippets` | GET (auth) | 200 | Snippet list |
| `/wp-json/wp/v2/users/me` | GET (auth) | 200 | User object |

For each endpoint:
1. Record HTTP status code
2. Record response time
3. Verify response body structure matches expected
4. Flag any 4xx/5xx responses or timeouts >5s

**Output file:** `02 DevOps/S2.5.4_REST_Health_Check.md`
Include: pass/fail matrix, response times, any anomalies.

---

### Task 3: S2.5.5 — RSS Output Feed Verification (0.5h)
**Owner:** DevOps | **Type:** OPS

Verify the RSS output feed is valid and rendering:

1. Fetch `https://justin-kuiper.com/feed/the-markdown`
2. Validate XML structure (well-formed RSS 2.0 or Atom)
3. Check that items exist and have required fields (title, link, description, pubDate)
4. Verify item content renders correctly (no broken HTML, no raw shortcodes)
5. Test feed in an RSS validator (W3C Feed Validation Service or similar)
6. Confirm feed URL is discoverable from the site HTML (`<link rel="alternate" type="application/rss+xml">`)

**Output file:** `02 DevOps/S2.5.5_RSS_Feed_Verification.md`
Include: validation results, item count, any malformed entries, feed discoverability check.

---

### Task 4: S2.5.1 — Full Editorial Chain Smoke Test (1.5h)
**Owner:** DevOps + Foreman | **Type:** VERIFY | **CRITICAL — Must pass with zero failures**

Test the ENTIRE editorial pipeline end-to-end. Every step must succeed:

| Step | Test | Method | Expected Result |
|------|------|--------|-----------------|
| 1 | RSS ingest fires | Check `wp/v2/feed-items` for recent items | Feed items present with timestamps within cron window |
| 2 | AI scoring runs | Check feed items for `ns_ai_score` meta | Score field populated (1-10 range) on recent items |
| 3 | Editorial dashboard loads | Browser or REST — verify admin feed list | Dashboard renders with feed items and filters |
| 4 | Promote action works | Promote a feed item via admin AJAX | Item status changes, block assignment saves |
| 5 | 7-block page renders | `GET /wp-json/ns/v1/blocks` | All 7 blocks return content, dateline present |
| 6 | Commentary cards render | Check front-end page render | All 3 embed styles display correctly |
| 7 | Auto-refresh fires | Wait for AJAX poll cycle or trigger manually | Fresh data returns without page reload |
| 8 | Morning digest | Check digest endpoint or cron output | Digest content generates with top-scored items |

**Gate Criteria:** ALL 8 steps must pass. Any failure = stop, diagnose, fix, re-test before continuing.

**Comparison baseline:** S1.5.1 results (8 PASS, 2 PARTIAL). This run should resolve any partials.

**Output file:** `02 DevOps/S2.5.1_Smoke_Test_Results.md`
Include: pass/fail for each step, response times, evidence (API responses or screenshots), comparison to S1.5.1 baseline.

---

### Task 5: S2.5.2 — Security Stack Re-Verification (1h)
**Owner:** Cyber Ops | **Type:** VERIFY | **CRITICAL — Must pass with zero failures**

Verify every security component is still active and functioning at 85+ snippet scale:

| Component | Test Method | Expected |
|-----------|------------|----------|
| Token Vault (ID 30) | Read encrypted option, verify AES-256 | Encrypted value in wp_options |
| Nonce Verification (ID 33) | Send admin AJAX without nonce | 403 rejection |
| App Passwords (ID 36) | Auth REST call with app password | 200 success |
| Rate Limiting (ID 34) | Burst 65+ requests to REST endpoint | 429 after 60 req/min/IP |
| Audit Logging (ID 32) | Perform admin action, check DB table | Row appears with correct data |
| Diagnostic Logger (ID 37) | Trigger a warning, check log table | Entry with level/component/message |

**Additional checks at 85+ scale:**
- Verify no snippet load-order conflicts cause security bypasses
- Confirm nonce enforcement still covers all `ns_*` AJAX actions
- Check diagnostic logger isn't being overwhelmed by volume (review error counts in health endpoint)
- Verify audit log table isn't growing unbounded (check row count)

**Comparison baseline:** S1.5.2 results (6/6 PASS at 31 snippets). Confirm same results at 85+.

**Output file:** `02 DevOps/S2.5.2_Security_Verification.md`
Include: pass/fail matrix, test evidence (status codes, response bodies), comparison to S1.5.2, any new risks from scale increase.

---

### Task 6: S2.5.6 — Update Operational Playbook (0.5h)
**Owner:** Taskmaster | **Type:** OPS

Update the Operational Playbook (`01 Scrum Master/Operational_Playbook.md`) for Sprint 3 readiness:

1. Update snippet count (Section 1 — currently says "31 active of 37 total")
2. Review tool landscape — any platform API changes since Sprint 2?
3. Document any new REST endpoints added in Sprint 2
4. Update credential handling section with Sprint 2 social API tokens
5. Review and update the dependency map with Sprint 2 layers
6. Add Sprint 2.5 verification results to the confirmed-working section
7. Note any new connectors or MCP tools available for Sprint 3

**This is retro action B10 from the Sprint 2 retrospective.**

**Output file:** Updated `01 Scrum Master/Operational_Playbook.md` (in-place edit, not a new file)

---

## 5. GATE CRITERIA — SPRINT 2.5 PASSES WHEN:

1. ✅ S2.5.1 editorial chain smoke test — **zero failures on all 8 steps**
2. ✅ S2.5.2 security stack — **all 6 components verified functional at 85+ snippet scale**
3. ✅ No new blockers surfaced that would prevent Sprint 3 start
4. ✅ Operational Playbook updated for Sprint 3

If either verification task (S2.5.1 or S2.5.2) fails, the defect is **fixed and re-tested** before Sprint 3 begins. Do not proceed to Sprint 3 with a failing gate.

---

## 6. CREDENTIALS NEEDED AT SESSION START

Ask Yeti for these before doing anything:

1. **WordPress Application Password** — for REST API auth (health checks, snippet inventory)
2. **GitLab PAT** (optional) — `api` scope, for closing issues and committing results

**Handling rules:** Use in-memory only. Never write to disk or git. Never include in logs or commit messages.

---

## 7. DEFINITION OF DONE — PER TASK

A Sprint 2.5 task is DONE when:

1. ✅ Test/verification procedure completed
2. ✅ Output file saved to `02 DevOps/` folder (or Playbook updated in-place for S2.5.6)
3. ✅ Pass/fail results documented with evidence
4. ✅ Comparison to S1.5 baseline included (where applicable)
5. ✅ Any issues discovered are logged as blockers or flagged for Sprint 3
6. ✅ Sprint Tracker updated (status changed from ⬜ OPEN to ✅ CLOSED)

---

## 8. EXIT CRITERIA — SPRINT 2.5 GATE REVIEW

Sprint 2.5 is ready for Yeti sign-off when:

- [ ] S2.5.1 — Editorial chain smoke test: ALL 8 PASS
- [ ] S2.5.2 — Security stack verification: ALL 6 PASS at 85+ scale
- [ ] S2.5.3 — Snippet inventory current and documented
- [ ] S2.5.4 — All REST endpoints healthy
- [ ] S2.5.5 — RSS feed valid and discoverable
- [ ] S2.5.6 — Operational Playbook updated for Sprint 3
- [ ] No new blockers preventing Sprint 3 start

**Gate pass unlocks Sprint 3 — Scale & Polish (18 tasks, ~55h).**

---

## 9. SECURITY REQUIREMENTS — NON-NEGOTIABLE

Even though this is a verification sprint with no new code:

1. **No credentials in output files.** Redact any tokens, passwords, or API keys from test evidence.
2. **No credentials in git commits.** Scrub all output before committing.
3. **Test with real endpoints but sanitize results.** Show status codes and response structure, not raw token values.
4. **If a security test fails, escalate immediately.** Do not mark it as a pass and move on.

---

## 10. FILE REFERENCES

If you need deeper context, read these files (in priority order):

| File | Location | When to Read |
|------|----------|-------------|
| `PROJECT_INDEX.md` | `01 Scrum Master/` | First — master boot reference |
| `Sprint_2.5_Task_List.md` | `01 Scrum Master/` | Task list with gate criteria |
| `Sprint_Tracker.md` | `01 Scrum Master/` | Live status across all sprints |
| `Operational_Playbook.md` | `01 Scrum Master/` | Platform details, tool hierarchy, snippet inventory |
| `S1.5.1_Smoke_Test_Results.md` | `01 Scrum Master/` | Baseline — S1.5 smoke test (8 PASS, 2 PARTIAL) |
| `S1.5.2_Security_Verification.md` | `01 Scrum Master/` | Baseline — S1.5 security (6/6 PASS at 31 snippets) |
| `S1.5.3_Snippet_Inventory.md` | `01 Scrum Master/` | Baseline — 37 total, 31 active, dependency map |
| `Decision_Register.md` | `01 Scrum Master/` | All project decisions incl. DR-0016 (Lego blocks) |
| `Retrospective_Sprint2.html` | `01 Scrum Master/` | Sprint 2 retro — 10 action items, risk register |

---

## 11. COWORK-SPECIFIC NOTES

When running in **Cowork** (not Claude Code):

- **Workspace folder:** The mounted folder IS the project folder. Read/write directly.
- **REST API calls:** Use `curl` via Bash tool. The VM has network access.
- **Output files:** Write to `02 DevOps/` for verification results.
- **No native git push.** Use GitLab REST API (Commits endpoint) to push results:
  ```
  POST https://gitlab.com/api/v4/projects/80070684/repository/commits
  ```
- **Dashboard updates:** After completing tasks, update `Sprint_Tracker.md`.

---

## 12. CLAUDE CODE-SPECIFIC NOTES

When running in **Claude Code**:

- **Working directory:** `~/Documents/00 Notebooks/Gunther/`
- **Git available:** Can do native git push to `gunther-ops` repo if PAT is set.
- **File I/O:** Direct filesystem access to all project files.
- **REST API:** Use `curl` or write helper scripts. No browser automation unless visual QA needed.
- **After completion:** Update Sprint_Tracker.md, close GitLab issues, log transcript.

---

## 13. RECOMMENDED START

If you're reading this fresh, here's the fastest path:

1. **Ask Yeti for WordPress Application Password**
2. **Start with S2.5.3 (Snippet Inventory)** — establishes baseline for all other tests
3. **Then S2.5.4 (REST Health Check)** — confirms API layer is healthy
4. **Then S2.5.5 (RSS Feed)** — quick standalone check
5. **Then S2.5.1 (Smoke Test)** — the big one, uses inventory + health check as foundation
6. **Then S2.5.2 (Security Verification)** — runs after you know the system is baseline healthy
7. **Last: S2.5.6 (Playbook Update)** — incorporates all results from above

**Total estimated time: ~4 hours. No new features. No new code. Just verification.**

---

*Prepared by Taskmaster — 2026-03-10. Sprint 2.5 Integration Gate. Standing by for tasking.*
