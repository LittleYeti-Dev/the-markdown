# The Markdown — Operational Playbook

**Version:** 2.0
**Last Reviewed:** 2026-03-10 (Sprint 2.5)
**Next Review:** Sprint 3 boundary
**Owner:** Taskmaster
**Status:** LIVING DOCUMENT — update every sprint boundary

> **BOOT SEQUENCE REQUIREMENT:** This file must be read at the start of every session, immediately after `PROJECT_INDEX.md`. Added to boot sequence per retro action A16 and confirmed in S1.5.7 continuity update.

---

## Section 1 — Platform Profile

### WordPress.com (NOT Self-Hosted)

This project runs on **WordPress.com managed hosting**, not a self-hosted WordPress installation. This single fact drives the entire deployment and tool strategy.

**What WordPress.com means for this project:**

- **No SFTP access.** You cannot SSH into the server or upload files directly.
- **No wp-config.php access.** Server-level configuration changes are not possible. The vault encryption key lives in a Code Snippet instead (DR-0012).
- **No server-side plugin installation via filesystem.** All plugins are installed through the WordPress.com admin or marketplace.
- **WAF (Web Application Firewall) active.** Large payloads and certain code patterns are blocked. This is why monolithic snippets failed during Sprint 1 Gate 3 deployment and why we adopted the Lego block architecture (DR-0016).
- **Deploy via Code Snippets plugin.** The Code Snippets plugin is the deployment mechanism. Snippets are managed via REST API at `code-snippets/v1/snippets` (DR-0011).

### Site Details

| Property | Value |
|----------|-------|
| Site URL | `https://justin-kuiper.com` |
| Admin URL | `*.wordpress.com/wp-admin` |
| Custom Post Type | `ns_feed_item` (rest_base: `feed-items`) |
| Custom REST Namespace | `ns/v1` |
| Custom Routes | `/blocks`, `/diagnostics/health`, `/feed-items` |
| Code Snippets API | `code-snippets/v1/snippets` |
| REST API Base | `https://justin-kuiper.com/wp-json/` |
| Total Active Snippets | 74 (of 85 total) |
| Custom RSS Feed | `/?feed=the-markdown` (pretty URL `/feed/the-markdown` needs permalink flush) |
| WordPress User ID | 204791015 |
| Display Name | Cyberfied |

---

## Section 2 — Tool Hierarchy

Tools are ranked by efficiency. Always start from the top. Only fall back to lower-ranked tools when the higher-ranked tool genuinely cannot accomplish the task.

### Rank 1: WordPress REST API (Fastest, Most Reliable)

Use for: reading data, deploying snippets, checking status, managing feed items, diagnostics.

| Endpoint Category | Base Path | Auth Required | Notes |
|-------------------|-----------|---------------|-------|
| Custom endpoints | `ns/v1/blocks` | No (read) | Returns 7-block layout data + dateline |
| Custom endpoints | `ns/v1/diagnostics/health` | **Yes** | System health, error counts, log stats |
| Custom endpoints | `ns/v1/feed-items` | No (read) | Extended feed items with scores, domains, promoted status (Sprint 2) |
| Standard endpoints | `wp/v2/feed-items` | Read: No, Write: Yes | Custom post type `ns_feed_item` |
| Standard endpoints | `wp/v2/users/me` | Yes | Verify authentication |
| Code Snippets | `code-snippets/v1/snippets` | Yes | Full CRUD on PHP snippets |

**Authentication:** Application Password via HTTP Basic Auth. Username: `h3ndriksj`. Password provided by Yeti at session start.

**Confirmed working (S2.5.4 health check — 2026-03-10):**
- Feed items endpoint: 200, 0.56s response (760 items)
- Blocks endpoint: 200, 0.12s response
- Health endpoint: 200, 0.42s response (**now requires auth — returns 401 without credentials**)
- Feed-items (ns/v1): 200, returns structured JSON with scores and domains
- Snippets endpoint: 200, 1.53s response (412KB payload — 85 snippets)
- User auth: 200 with app password, 401 without

### Rank 2: GitLab REST API (Issue Tracking, Commits, Pipelines)

Use for: updating issues, closing milestones, checking pipeline status, committing code.

| Property | Value |
|----------|-------|
| Project ID | `80070684` |
| Repo URL | `gitlab.com/h3ndriks.j/JK.com-ver02` |
| Auth | Personal Access Token (PAT) with `api` scope |
| CLI | `glab` if available in environment |
| Provider | Yeti at session start |
| Rotation | Multiple times daily — assume any PAT is short-lived |

### Rank 3: Admin AJAX (When REST Can't Do It)

Use for: nonce-protected admin actions (promote, block assign, vault operations).

| Property | Value |
|----------|-------|
| Endpoint | `POST /wp-admin/admin-ajax.php` |
| Auth | Requires valid WordPress session + nonce |
| Nonce enforcement | All `ns_*` actions (S1.18) |
| Error format | `{"success":false,"data":{"code":"missing_nonce","message":"..."}}` |

**Important:** Admin AJAX requires a browser session with a valid nonce. REST API calls cannot generate nonces. If you need to perform an AJAX action, you must use browser automation to obtain a nonce first.

### Rank 4: Browser Automation (Last Resort — Slowest, Most Fragile)

Use ONLY for:
- Nonce-protected admin actions that have no REST equivalent
- Visual QA and screenshot capture
- Verifying front-end rendering

**NEVER use browser automation for:**
- Reading data (use REST API)
- Deploying snippets (use Code Snippets API)
- Checking status (use REST API health endpoint)
- Managing feed items (use REST API)

### Transparent Tool Selection Protocol (A19/DR-0021)

At the start of every task, every agent must state their tool choice and reasoning:

> "I'm using **[tool]** because **[reason]**. Alternatives: **[list]**. New since last review: **[any]**."

This is not optional. It ensures Yeti can course-correct before time is spent on the wrong approach. If conditions have changed since this playbook was last reviewed (new MCP connectors, platform updates, connectivity changes), flag it immediately.

---

## Section 3 — Authentication & Credentials

### Quick Reference

| API | Credential Type | Scope | Provider | Rotation |
|-----|----------------|-------|----------|----------|
| WordPress REST API | Application Password | `feed-items`, `snippets`, write endpoints | Stored in WordPress.com | Per WordPress.com policy |
| GitLab REST API | Personal Access Token (PAT) | `api` | Yeti at session start | Multiple times daily |
| Claude API | API Key | Anthropic API calls | Yeti stores in Token Vault (S1.17) | Per key rotation procedure (S1.20) |
| X (Twitter) API | OAuth 2.0 tokens | Post tweets, upload media | Token Vault (S1.17) via ID 38 | Auto-refresh via ID 55 |
| LinkedIn API | OAuth 2.0 tokens | Post articles | Token Vault (S1.17) via ID 39 | Auto-refresh via ID 55 |
| YouTube API | OAuth 2.0 tokens | Community posts | Token Vault (S1.17) via ID 40 | Auto-refresh via ID 55 |
| Medium API | Integration token | Post articles | Token Vault (S1.17) via ID 52 | Manual rotation |
| WordPress Admin | OAuth / browser session | Full admin access | Browser login | Session-based |

### Handling Rules

1. **Credentials are provided by Yeti at session start — ASK IMMEDIATELY.**
2. Use in-memory only — **NEVER write to disk, files, or git.**
3. **NEVER include in commit messages, log files, or output documents.**
4. Assume any PAT is rotated within hours.
5. If a credential stops working mid-session, ask Yeti for a fresh one — don't troubleshoot.
6. Token Vault (S1.17) is the ONLY persistent credential store — encrypted AES-256-CBC in `wp_options`.

### Session Start Pattern

At the beginning of every session, request credentials:

> "I need a GitLab PAT with `api` scope to update issues. I also need the WordPress Application Password for REST API access. Can you provide both for this session?"

### Full Protocol

See `S1.5.6_Credential_Protocol.md` for the complete credential handling protocol including per-API details, security boundaries, and rotation procedures.

---

## Section 4 — Active Snippet Inventory

*Source: S2.5.3_Snippet_Inventory.md (verified 2026-03-10)*

### Summary

| Metric | Value |
|--------|-------|
| Total snippets | 85 |
| Active | 74 |
| Inactive | 11 |
| Snippets >150 lines | 15 (13 Sprint 1 legacy + 2 Sprint 2) |
| Critical monoliths (>300 lines) | 7 (all Sprint 1 — unchanged) |
| Sprint 2 DR-0016 compliance | 95% (42 of 44 new snippets under 150 lines) |
| Code errors | 0 |

### Load Order (Critical)

| Priority | ID | Name | Why It Must Load First |
|----------|-----|------|----------------------|
| 1 | 31 | NS Vault Key | Encryption key constant — must exist before Token Vault loads |
| 2 | 37 | S1.23 Diagnostic Logger | `ns_diag_write()` must exist before Core Utilities (`ns_log()`) |
| 10 | all others | Everything else | Default priority |

WordPress Code Snippets processes by priority (lower number = earlier), then by ID within same priority. The current ordering is correct and must not be changed without understanding the dependency chain.

### 4-Layer Dependency Map

```
Layer 0 — Foundation:
  NS Vault Key (31, p1) ────────────── provides VAULT_KEY constant
  S1.23 Diagnostic Logger (37, p2) ─── provides ns_diag_write()

Layer 1 — Core:
  G2-M01 Core Utilities (9) ─────────── provides ns_log(), ns_get_api_key()
    └─ calls: ns_diag_write() [Layer 0]
  S1-G1 Data Model (7) ─────────────── provides CPT, taxonomies, sanitizers
  G2-M02 Feed Config (10) ───────────── provides ns_get_feed_config()

Layer 2 — Security:
  S1.17 Token Vault (30) ──────── provides ns_vault_*() │ depends on: Vault Key (31)
  S1.22 Audit Logging (32) ────── provides ns_audit_log()
  S1.18 Nonce Verification (33) ─ calls: ns_audit_log()
  S1.21 Rate Limiting (34) ────── calls: ns_audit_log()
  S1.19 App Passwords (36) ────── calls: ns_audit_log()
  S1.20 Key Rotation (35) ─────── calls: ns_vault_rotate_key(), ns_audit_log()

Layer 3 — Pipeline:
  G2-M06 Sanitize Stubs (14) ──── provides ns_sanitize_rss_content(), ns_sanitize_llm_input()
  G2-M04 Auto-Tagging (12) ────── provides ns_auto_tag_domain()
  G2-M05 Deduplication (13) ───── provides ns_is_duplicate()
  G2-M03 RSS Importer (11) ────── calls: ns_get_feed_config, ns_auto_tag, ns_is_duplicate
  G2-M07b5 Prompt Builder (24) ── provides ns_build_scoring_prompt()
  G2-M07b2 Claude API Caller (21) provides ns_call_claude_api()
  G2-M07b3 Response Parser (22) ─ provides ns_parse_scoring_response()
  G2-M07b4 Score Meta Saver (23)─ provides ns_save_score_meta()
  G2-M07b1 Score Single Item (20) orchestrates: 24 → 21 → 22 → 23
  G2-M07a Scoring Cron Runner (19) calls: ns_score_single_item()
  G2-M08 Morning Digest (15) ──── standalone (uses WP queries)

Layer 4 — UI:
  S1.12 Editorial Dashboard (25) ─ admin feed list
  S1.13 Promote + Block Assign (26) AJAX handlers
  S1.14 Page Template (27) ─────── front-end 7-block layout
  S1.15 Commentary Cards (28) ──── front-end embeds
  S1.16 Auto-Refresh (29) ──────── AJAX polling + REST endpoint
  G2-M09 Admin Dashboard (16) ──── calls: ns_run_rss_import, ns_run_claude_scoring

Layer 5 — Social APIs (Sprint 2 Gate 4, 21 snippets):
  S2-G4-M01 X OAuth Handler (41)
  S2-G4-M02 X Token Manager (38) ── calls: ns_vault_store/retrieve
  S2-G4-M03 X Tweet Composer (48)
  S2-G4-M04 X Thread Builder (58)
  S2-G4-M05 X Media Uploader (49)
  S2-G4-M06 X Admin Settings (44)
  S2-G4-M07 LinkedIn OAuth Handler (42)
  S2-G4-M08 LinkedIn Token Manager (39) ── calls: ns_vault_store/retrieve
  S2-G4-M09 LinkedIn Post Composer (50)
  S2-G4-M10 LinkedIn Admin Settings (45)
  S2-G4-M11 YouTube OAuth Handler (43)
  S2-G4-M12 YouTube Token Manager (40) ── calls: ns_vault_store/retrieve
  S2-G4-M13 YouTube Community Posts (51)
  S2-G4-M14 YouTube Admin Settings (46)
  S2-G4-M15 Medium API Client (52)
  S2-G4-M16 Medium Admin Settings (47)
  S2-G4-S01 OAuth Callback Validator (53)
  S2-G4-S02 OAuth Callback Admin (54)
  S2-G4-S03 Token Refresh Cron (55)
  S2-G4-S04 Token Refresh Alerter (56)
  S2-G4-S05 Social API Error Handler (57)

Layer 6 — AI + Publish (Sprint 2 Gate 5, 23 snippets):
  S2-G5-M01 AI Content Reformatter (66) ── calls: Claude API
  S2-G5-M02 Platform Prompt Templates (65)
  S2-G5-M03 Daily Summary Generator (70)
  S2-G5-M04 Summary Email Sender (71)
  S2-G5-M05 Arc Config Manager (67)
  S2-G5-M06 Arc Scorer (68)
  S2-G5-M07 Arc Admin Settings (69)
  S2-G5-M08 Quick-Publish Panel UI (76) ─── admin
  S2-G5-M09 Quick-Publish AJAX Handler (77) calls: nonce verify
  S2-G5-M10 Platform Preview Renderer (78)
  S2-G5-M11 Preview AJAX Handler (79)
  S2-G5-M12 Publish Dispatcher (75) ────── calls: social API composers
  S2-G5-M13 Publish REST Routes (72)
  S2-G5-M14 Feed Items REST Extension (73)
  S2-G5-M15 RSS Feed Generator (74) ────── custom feed at /?feed=the-markdown
  S2-G5-S01 AI Output Validator (63)
  S2-G5-S02 AI Length Enforcer (64)
  S2-G5-S03 Quick-Publish CSRF Guard (81)
  S2-G5-S04 Social Output Escaper (80)
  S2-G5-S05 Publish Audit Logger (82) ──── calls: ns_audit_log()
  S2-G5-S06 Publish Audit Viewer (83)
  S2-G5-S07 Platform Rate Tracker (84)
  S2-G5-S08 Publish Queue Manager (85)
```

### Refactoring Backlog

7 critical monoliths (>300 lines) remain from Sprint 1 — refactoring deferred per DR-0023:

| ID | Name | Lines | Functions | Priority |
|----|------|-------|-----------|----------|
| 7 | S1-G1 Data Model | 640 | 16 | HIGH |
| 30 | S1.17 Token Vault | 635 | 16 | HIGH |
| 37 | S1.23 Diagnostic Logger | 436 | 8 | MEDIUM |
| 27 | S1.14 Page Template | 373 | 3 | HIGH |
| 28 | S1.15 Commentary Cards | 373 | 3 | HIGH |
| 25 | S1.12 Editorial Dashboard | 340 | 4 | HIGH |
| 26 | S1.13 Promote + Block Assign | 338 | 5 | HIGH |

Cleanup candidates (can be deleted):
- IDs 1-4: Default Code Snippets samples (inactive)
- ID 8: S1-G2 RSS AI Pipeline (deactivated, replaced by M01-M09)
- IDs 17, 18: WAF test artifacts (inactive)
- IDs 59-62: Temporary X/Twitter token storage (inactive, tokens should be in Vault now)

---

## Section 5 — Architecture Reference

### Lego Block Micro-Snippet Architecture (DR-0016)

All Sprint 2+ code must follow the Lego block pattern: smallest possible composable units, one function per snippet where practical. This was adopted after monolithic snippets triggered WordPress.com WAF blocks during Sprint 1 Gate 3 deployment.

**Reference pattern:** Gate 2 micro-snippets (M01–M09)
- 43–66 lines per snippet
- Clear naming: `G2-M07b2 Claude API Caller`
- Explicit dependency chain via function calls
- One function per snippet where practical

**Current state:**
- Gate 2 snippets: already micro-snippet pattern (the reference)
- Gate 3 snippets: ALL monolithic — working but flagged for Sprint 2 refactoring (DR-0023)
- Gate 1: single monolithic snippet (640 lines, 16 functions) — also flagged

### Architecture Change Freeze Protocol (DR-0018)

When the architecture changes:

1. **STOP all execution immediately.**
2. Update all documentation (system prompts, sprint plans, naming conventions, tracking docs).
3. Verify continuity — ensure every artifact reflects the new architecture.
4. Only then resume building.

This is not overhead — it prevents documentation from drifting away from reality. If docs don't match the real architecture, the next session starts from a wrong baseline.

### Seven Cyber Gates

| Gate | Name | Status |
|------|------|--------|
| G0 | Architecture-Review | ✅ PASSED |
| G1 | Code-Security (Data Model) | ✅ PASSED |
| G2 | Credential-Security (RSS/AI Pipeline) | ✅ PASSED |
| G3 | AI-Pipeline-Security (Foundation) | ✅ PASSED |
| G4 | Publish-Security | ✅ PASSED (Sprint 2) |
| G5 | Deployment-Security | ✅ PASSED (Sprint 2) |
| G6 | Incident-Readiness | ⬜ PENDING (Sprint 3) |

Gates 0–5 passed during Sprints 0–2. Gate 6 is pending for Sprint 3.

---

## Section 6 — Deployment Procedure

### Deploying a New Snippet via REST API

1. **Prepare the snippet code.** Follow Lego block pattern — one function, <80 lines ideally.
2. **Determine priority.** Default is 10. Only change if the snippet must load before others (see Load Order in Section 4).
3. **Determine scope.** `global` (runs everywhere), `admin` (admin pages only), `front-end` (public pages only).
4. **Deploy via REST API:**
   ```
   POST /wp-json/code-snippets/v1/snippets
   Auth: Basic Auth (h3ndriksj + app password)
   Body: { "name": "...", "code": "...", "priority": 10, "scope": "global", "active": false }
   ```
5. **Test before activating.** Deploy as inactive first. Verify no syntax errors.
6. **Activate:** `PUT /wp-json/code-snippets/v1/snippets/{id}` with `"active": true`.
7. **Verify:** Check `ns/v1/diagnostics/health` for any new errors after activation.

### Safe Activation Order for Security Snippets (DR-0013)

When deploying security snippets, follow this order:

1. **Vault Key (ID 31, priority 1)** — must load before Token Vault
2. **Diagnostic Logger (ID 37, priority 2)** — must load before Core Utilities
3. **Audit Logging** — provides `ns_audit_log()` used by other security snippets
4. **Nonce Verification, Rate Limiting, App Passwords** — depend on audit logging
5. **Key Rotation** — depends on vault + audit logging

Run a site health check between each activation. If any snippet causes errors, deactivate it immediately and troubleshoot before continuing.

### WAF Mitigation

- Keep snippets small (target <80 lines, absolute max ~200 lines)
- One function per snippet where practical
- Test individual snippets in isolation before combining
- If WAF blocks a deployment, the snippet is too large — split it

---

## Section 7 — Sprint-Boundary Review Checklist (A18/DR-0021)

At the start of every sprint, run through this checklist and update this playbook:

- [ ] Check for new MCP connectors relevant to the stack (WordPress, GitLab, social APIs)
- [ ] Review WordPress.com platform updates (new features, changed constraints, new deployment options)
- [ ] Reassess tool hierarchy based on current conditions (connectivity, latency, Yeti's travel status)
- [ ] Review snippet inventory — any new, removed, or changed snippets?
- [ ] Verify GitLab-to-production drift status (are all snippets committed?)
- [ ] Check if any new social platform APIs have changed their requirements (relevant for Sprint 2+)
- [ ] Update credential inventory if any new APIs were added
- [ ] Update this Playbook with any changes found
- [ ] Set "Last Reviewed" date at the top of this document

**Last Completed:** 2026-03-10 (Sprint 2.5 boundary)

---

## Section 8 — Known Issues & Operational Gaps

### From Smoke Test (S2.5.1 — 2026-03-10)

| Issue | Impact | Status | Fix |
|-------|--------|--------|-----|
| Claude API key not stored in Token Vault | AI scoring + reformatter inactive — 0/760 items scored | Operational setup needed | Store key via Token Vault admin page (S1.17) |
| Morning digest has no REST endpoint | Cron function — cannot verify via API | Verification limitation | Add digest REST endpoint (Sprint 3 candidate) |
| GitLab snippet drift | Unknown number of 85 snippets committed to GitLab | Needs re-audit | Commit all active snippet code to GitLab |
| Custom RSS feed permalink 404 | `/feed/the-markdown` returns 404; works at `/?feed=the-markdown` | Rewrite flush needed | Save Settings > Permalinks in WP admin |
| Custom RSS feed not discoverable | No `<link rel="alternate">` tag in HTML for custom feed | Sprint 3 fix | Add wp_head action to output feed discovery link |
| Custom RSS feed empty | Zero items (no promoted items exist) | Operational | Execute editorial promotion workflow |
| Health endpoint now requires auth | Returns 401 without credentials (was unauthenticated in S1.5) | Confirmed — security improvement | Update any scripts expecting unauthenticated access |
| All 7 blocks empty | 760 items but none promoted to blocks | Operational | Execute editorial workflow to populate blocks |

### From Security Verification (S2.5.2 — 2026-03-10)

- All 6 security components verified active at 85+ snippet scale.
- 11 new security snippets from Sprint 2 — all active, no errors.
- Rate limit ratio shifted slightly (S1.5: 66% blocked, S2.5: 54% blocked) — enforcement still active.
- Snippets payload size growing (412KB) — monitor at next gate for WAF limits.
- Token Vault encryption key in Code Snippet (DR-0012) is a known and accepted trade-off.

### From Refactor Assessment (deferred from S1.5.4)

- 7 critical monoliths (>300 lines) unchanged — all Sprint 1
- Sprint 2 snippets follow DR-0016 (95% compliance, 42/44 under 150 lines)
- Refactoring still deferred per DR-0023

---

## Section 9 — Integration Test Protocol (DR-0017)

Every sprint close requires a mandatory integration smoke test. This is non-negotiable — building the next sprint on an untested foundation is not an option.

### 10-Step Chain Test

Test the full user workflow end-to-end:

| Step | Test | Method | Pass Criteria |
|------|------|--------|---------------|
| 1 | RSS ingest fires | `GET /wp/v2/feed-items?per_page=5&orderby=date&order=desc` | HTTP 200, items present with metadata |
| 2 | AI scoring runs | Check `relevance_score` meta on recent items | Items have scores > 0 |
| 3 | Editorial dashboard loads | `POST admin-ajax.php (action=ns_heartbeat)` | Nonce-protected response (403 without nonce = handler registered) |
| 4 | Promote action works | `POST admin-ajax.php (action=ns_promote_item)` | Nonce-protected response |
| 5 | Page template renders | `GET https://justin-kuiper.com/` | HTTP 200, NS CSS classes present, block divs rendered |
| 6 | Commentary cards render | Check front page for `ns-cc-tweet`, `ns-cc-pullquote`, `ns-cc-inline` | All 3 embed styles present |
| 7 | Auto-refresh fires | `GET /ns/v1/blocks` | HTTP 200, structured JSON with blocks array + generated timestamp |
| 8 | Digest generation | Verify snippet active + cron registered | Snippet active, cron interval registered |
| 9 | Audit log captures | `GET /ns/v1/diagnostics/health` | HTTP 200, total_entries > 0 |
| 10 | Diagnostic logger works | `GET /ns/v1/diagnostics/health` | HTTP 200, status = "healthy", errors_24h = 0 |

### 6-Component Security Verification

| # | Component | Test | Pass Criteria |
|---|-----------|------|---------------|
| 1 | Token Vault (S1.17) | AJAX call to vault endpoint | Nonce-protected, AES-256-CBC confirmed |
| 2 | Nonce Verification (S1.18) | AJAX without nonce | 403 + structured JSON error |
| 3 | App Passwords (S1.19) | REST call with/without auth | 200 with auth, 401 without |
| 4 | Rate Limiting (S1.21) | Burst 65 parallel requests | 429 responses after threshold |
| 5 | Audit Logging (S1.22) | Trigger actions, check log | Entries captured with correct data |
| 6 | Diagnostic Logger (S1.23) | Health endpoint check | Status "healthy", 0 errors |

### Gate Criteria

- **ALL steps must pass.** Zero tolerance for failures.
- Partial passes (operational gaps like missing API keys) are acceptable IF the underlying code infrastructure is verified working.
- Any code failure triggers a fix before the sprint can close.

### Output Format

Results must be saved as: `S{sprint}_Smoke_Test_Results.md` (e.g., `S1.5.1_Smoke_Test_Results.md`).

---

## Section 10 — File Map & Boot Sequence

### Scrum Master Folder Structure

```
Scrum Master/
├── PROJECT_INDEX.md                    ← Master index — read FIRST
├── Operational_Playbook.md             ← THIS FILE — read SECOND
├── Sprint_Tracker.md                   ← Live task status across all sprints
├── Decision_Register.md                ← All project decisions with context
├── Blocker_Register.md                 ← Open and resolved blockers
├── Scrum_Master_System_Prompt.md       ← Taskmaster identity and boot sequence
├── S1.5.6_Credential_Protocol.md       ← Credential handling protocol (security)
├── Retrospective_S0_S1.md             ← Sprint 0+1 retrospective with 20 action items
├── Prompt_Change_Log.md                ← System prompt revision history
├── Sprint_0_1_Wins.md                 ← Achievements reference
├── S1.5_ClaudeCode_Summary.md         ← Claude Code handoff summary
├── S1.5.1_Smoke_Test_Results.md       ← Integration test results (8 PASS, 2 PARTIAL)
├── S1.5.2_Security_Verification.md    ← Security verification (6/6 PASS at 31 snippets)
├── S1.5.3_Snippet_Inventory.md        ← Snippet inventory baseline (37 snippets)
├── S1.5.4_Refactor_Results.md         ← Refactor assessment (deferred to S2)
├── S1.5.8_Admin_Results.md            ← Admin housekeeping results
├── Retrospective_Sprint2.html         ← Sprint 2 retro — 10 action items
├── Sprint_0_Task_List.md               ← Sprint 0 task detail
├── Sprint_1_Task_List.md               ← Sprint 1 task detail
├── Sprint_1.5_Task_List.md            ← Sprint 1.5 task detail
├── Sprint_2_Task_List.md               ← Sprint 2 task detail
├── Sprint_2.5_Task_List.md            ← Sprint 2.5 task detail
├── Sprint_3_Task_List.md               ← Sprint 3 task detail
├── Scrum_Burndown_Dashboard.html       ← Visual dashboard (update every session)
├── TheMarkdown_Architecture_Diagram.html ← System architecture visualization
├── S1-12 through S1-23 .php files     ← Gate 3 snippet source code (local copies)
└── Transcripts/                        ← Session logs (append-only)
```

### Recommended Boot Sequence for New Sessions

**Step 1 — Read these files (in order):**

1. `PROJECT_INDEX.md` — master index, critical rules, current status
2. `Operational_Playbook.md` — THIS FILE — platform, tools, credentials, architecture
3. `Sprint_Tracker.md` — live task status across all sprints
4. `Decision_Register.md` — all project decisions with context and trade-offs
5. `S1.5.6_Credential_Protocol.md` — credential handling rules
6. `Blocker_Register.md` — open and resolved blockers
7. `Scrum_Master_System_Prompt.md` — Taskmaster identity and protocols

**Step 2 — Request credentials from Yeti** (see Section 3).

**Step 3 — Deliver status brief** (per system prompt boot sequence).

**Step 4 — Begin tasking.**

---

## Appendix — Decisions Referenced in This Playbook

| ID | Decision | Section |
|----|----------|---------|
| DR-0001 | WordPress.com over self-hosted | Section 1 |
| DR-0011 | Deploy via Code Snippets REST API | Section 1, 6 |
| DR-0012 | Vault encryption key in Code Snippet | Section 3, 8 |
| DR-0013 | Safe activation order for security snippets | Section 6 |
| DR-0016 | Lego block micro-snippet architecture | Section 4, 5 |
| DR-0017 | Mandatory integration smoke test every sprint | Section 9 |
| DR-0018 | Architecture change freeze protocol | Section 5 |
| DR-0019 | Sprint 1.5 Integration Gate approved | Section 9 |
| DR-0020 | Living Operational Playbook | This document |
| DR-0021 | Sprint-boundary tool & methodology review | Section 2, 7 |
| DR-0022 | Credential handling protocol | Section 3 |
| DR-0023 | Monolithic snippet refactoring deferred to Sprint 2 | Section 4, 8 |

---

*Created: 2026-03-09 by Taskmaster (Sprint 1.5 — S1.5.5)*
*Updated: 2026-03-10 by DevOps (Sprint 2.5 — S2.5.6)*
*Closes retro actions: A15 (build playbook), A16 (add to boot sequence), A18 (sprint-boundary review), A19 (transparent tool selection), B10 (update playbook for Sprint 3)*
*This is a living document. Update at every sprint boundary per DR-0020.*

### Sprint 2.5 Verification Results (Confirmed Working — 2026-03-10)

| Task | Result | Notes |
|------|--------|-------|
| S2.5.1 Smoke Test | CONDITIONAL PASS (6 PASS, 2 PARTIAL) | Same partials as S1.5 — AI key + digest endpoint |
| S2.5.2 Security | ALL 6/6 PASS at 74 active snippets | No regressions from S1.5 |
| S2.5.3 Inventory | 85 total, 74 active, 0 errors | +48 snippets from S1.5, no conflicts |
| S2.5.4 REST Health | ALL 6/6 endpoints healthy | Response times within tolerance |
| S2.5.5 RSS Feed | PARTIAL PASS | Feed works but permalink needs flush + no auto-discovery |
| S2.5.6 Playbook | UPDATED | This document |

**Verification output files:** `02 DevOps/S2.5.1_*` through `S2.5.5_*`
