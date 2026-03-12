# Sprint 1.5 — Cowork Execution Prompt (S1.5.5 + S1.5.6 + S1.5.7)

**Paste this entire prompt into a new Cowork session. Select the `Scrum Master` folder as your workspace.**

---

## BOOT SEQUENCE — READ BEFORE DOING ANYTHING

You are **Taskmaster** executing the final 3 tasks of Sprint 1.5 (Integration Gate) for **The Markdown** project. These are documentation and continuity tasks — no code deployment, no API calls to WordPress. Your outputs are `.md` files saved to the workspace folder.

### Step 1: Read These Files (in order)

1. `PROJECT_INDEX.md` — master project index, critical rules, file map
2. `Sprint_Tracker.md` — live task status across all sprints
3. `Decision_Register.md` — all 22 decisions (especially DR-0016 through DR-0022)
4. `Blocker_Register.md` — 2 open, 3 resolved
5. `Retrospective_S0_S1.md` — full retro with 20 action items (A1–A20)
6. `S1.5_ClaudeCode_Summary.md` — handoff from Claude Code (5 tasks completed)
7. `S1.5.3_Snippet_Inventory.md` — full snippet inventory (37 snippets, dependency map)
8. `S1.5.1_Smoke_Test_Results.md` — 8 PASS, 2 PARTIAL
9. `S1.5.2_Security_Verification.md` — 6/6 PASS
10. `S1.5.4_Refactor_Results.md` — assessed, deferred to Sprint 2
11. `Scrum_Master_System_Prompt.md` — Taskmaster identity, boot sequence, all protocols

### Step 2: Understand the Handoff

Claude Code already completed 5 of 8 Sprint 1.5 tasks:

| Task | Status | Output |
|------|--------|--------|
| S1.5.8 Admin Housekeeping | ✅ CLOSED | S1.5.8_Admin_Results.md |
| S1.5.3 Snippet Inventory | ✅ CLOSED | S1.5.3_Snippet_Inventory.md |
| S1.5.4 Refactor Assessment | ✅ CLOSED (deferred) | S1.5.4_Refactor_Results.md |
| S1.5.1 Smoke Test | ✅ CLOSED (8/10, 2 partial) | S1.5.1_Smoke_Test_Results.md |
| S1.5.2 Security Verification | ✅ CLOSED (6/6) | S1.5.2_Security_Verification.md |

**Your 3 remaining tasks:**

| Task | ID | Priority | Est |
|------|----|----------|-----|
| Build Operational Playbook | S1.5.5 | **Critical** | 2h |
| Define credential handling protocol | S1.5.6 | **Critical** | 0.5h |
| Update all continuity artifacts | S1.5.7 | High | 1h |

---

## PROJECT CONTEXT (Do Not Skip)

- **Project:** The Markdown — AI-powered editorial news aggregation platform
- **Platform:** WordPress.com (NOT self-hosted) — deploy via Code Snippets plugin
- **GitLab Repo:** `gitlab.com/h3ndriks.j/JK.com-ver02` (Project ID: `80070684`)
- **Architecture:** Lego block micro-snippets (DR-0016) — one function per snippet
- **AI Team Model:** Taskmaster (scrum), Cyber Ops (security), Foreman (integration), DevOps (build)
- **Owner:** Yeti (Justin Kuiper)
- **Sprint 1.5 Gate Criteria:** S1.5.1 + S1.5.2 must pass with zero failures — ✅ MET

### Current Sprint Status
- Sprint 0: ✅ COMPLETE (7/7, milestone closed)
- Sprint 1: ✅ COMPLETE (23/23, milestone closed)
- Sprint 1.5: 🔄 IN PROGRESS (5/8 closed, 3 remaining = YOUR TASKS)
- Sprint 2: ⬜ NOT STARTED (0/22)
- Sprint 3: ⬜ NOT STARTED (0/21)
- Overall: 35/81 tasks closed (43%)

### Open Blockers
- BLK-001: Facebook App Review not submitted (blocks S2.3 only)
- BLK-004: Dev favicon images not committed (cosmetic)

### Key Decisions That Affect Your Work
| ID | Decision | Why It Matters |
|----|----------|---------------|
| DR-0001 | WordPress.com over self-hosted | Platform constraints drive the entire tool hierarchy |
| DR-0011 | Deploy via Code Snippets REST API | No SFTP, no wp-config.php access |
| DR-0012 | Vault encryption key stored as Code Snippet | Known trade-off, document in playbook |
| DR-0016 | Lego block snippet architecture | Sprint 2+ standard — playbook must explain this |
| DR-0017 | Mandatory integration smoke test every sprint | Playbook must include the test protocol |
| DR-0018 | Architecture change freeze protocol | Playbook must reference this discipline |
| DR-0019 | Sprint 1.5 Integration Gate approved | You're closing it |
| DR-0020 | Living Operational Playbook | You're building it (S1.5.5) |
| DR-0021 | Sprint-boundary tool & methodology review | Playbook must include review checklist |
| DR-0022 | Credential handling protocol | You're defining it (S1.5.6) |

### Retro Action Items You're Closing
| # | Action | Task |
|---|--------|------|
| A14 | Update continuity artifacts for micro-snippet arch | S1.5.7 |
| A15 | Build living Operational Playbook | S1.5.5 |
| A16 | Add Playbook to boot sequence | S1.5.5 |
| A18 | Sprint-boundary tool & methodology review | S1.5.5 |
| A19 | Transparent tool selection protocol | S1.5.5 |
| A20 | Credential handling protocol | S1.5.6 |

---

## TASK 1: S1.5.5 — Build Operational Playbook (Critical, ~2h)

**Output file:** `Operational_Playbook.md`

This is the single most important deliverable of Sprint 1.5. It addresses the #1 recurring failure from the retro: agents losing operational context at session boundaries and picking wrong tools.

### What the Playbook Must Contain

**Section 1 — Platform Profile**
- WordPress.com (not self-hosted) — what this means for deployments
- Code Snippets plugin as the deployment mechanism
- Known platform constraints: no SFTP, no wp-config.php, WAF thresholds on large payloads
- Site URL: `justin-kuiper.com`
- Admin URL pattern: `*.wordpress.com/wp-admin`
- Custom post type: `ns_feed_item` (rest_base: `feed-items`)
- Custom REST namespace: `ns/v1` with routes: `/blocks`, `/diagnostics/health`
- Code Snippets API: `code-snippets/v1/snippets`

**Section 2 — Tool Hierarchy (ranked by efficiency)**
Use the data from the smoke test and retro to define this:

1. **WordPress REST API** (fastest, most reliable)
   - Custom endpoints: `ns/v1/blocks`, `ns/v1/diagnostics/health`
   - Standard endpoints: `wp/v2/feed-items`, `wp/v2/users/me`
   - Code Snippets: `code-snippets/v1/snippets`
   - Auth: Application Password (HTTP Basic Auth), username `h3ndriksj`

2. **GitLab REST API** (for issue tracking, commits, pipelines)
   - Project ID: `80070684`
   - Auth: PAT with `api` scope (provided by Yeti at session start)
   - CLI: `glab` if available

3. **Admin AJAX** (when REST can't do it — requires nonce/browser session)
   - All `ns_*` actions require nonce verification (S1.18)
   - Structured JSON error on nonce failure: `{"code":"missing_nonce","message":"..."}`

4. **Browser Automation** (last resort only — slowest, most fragile)
   - Only needed for: nonce-protected admin actions, visual QA, screenshots
   - Never use for: reading data, deploying snippets, checking status

Include the transparent tool selection protocol (A19/DR-0021):
> "At the start of every task, state your tool choice and reasoning. Surface alternatives. Flag new connectors or platform changes. Pattern: 'I'm using [tool] because [reason]. Alternatives: [list]. New since last review: [any].'"

**Section 3 — Authentication & Credentials** (cross-reference S1.5.6)
- Which APIs need which tokens/scopes
- Who provides them (Yeti, at session start)
- Handling rules: in-memory only, never persisted, never committed
- Rotation expectations: Yeti rotates PATs multiple times daily
- Reference `S1.5.6_Credential_Protocol.md` for full protocol

**Section 4 — Active Snippet Inventory**
Pull directly from `S1.5.3_Snippet_Inventory.md`:
- 37 total snippets (31 active, 6 inactive)
- Load order: Vault Key (p1) → Diagnostic Logger (p2) → everything else (p10)
- 4-layer dependency map (Foundation → Core → Security → Pipeline → UI)
- 7 critical monoliths flagged for Sprint 2 refactor
- 3 cleanup candidates (IDs 8, 17, 18)
- GitLab drift status: only 1 of 37 snippets in repo

**Section 5 — Architecture Reference**
- Lego block micro-snippet architecture (DR-0016)
- Gate 2 micro-snippets as the reference pattern (M01-M09)
- Gate 3 monoliths — working but flagged for refactoring
- Architecture change freeze protocol (DR-0018): stop building → update all docs → verify continuity → resume
- 7 Cyber Gates: Architecture-Review, Code-Security, Credential-Security, AI-Pipeline-Security, Publish-Security, Deployment-Security, Incident-Readiness
- Gates 0–3 PASSED, Gates 4–7 pending (Sprint 2+)

**Section 6 — Deployment Procedure**
- How to deploy a new snippet (REST API to Code Snippets endpoint)
- Safe activation order for security snippets (DR-0013)
- Vault Key (ID 31, priority 1) must load before Token Vault (ID 30)
- Diagnostic Logger (ID 37, priority 2) must load before Core Utilities (ID 9)
- WAF mitigation: keep snippets small, one function per snippet, test individually

**Section 7 — Sprint-Boundary Review Checklist** (A18/DR-0021)
At the start of every sprint:
- [ ] Check for new MCP connectors relevant to the stack
- [ ] Review WordPress.com platform updates (new features, changed constraints)
- [ ] Reassess tool hierarchy based on current conditions (connectivity, latency, travel)
- [ ] Review snippet inventory — any new, removed, or changed?
- [ ] Verify GitLab-to-production drift status
- [ ] Update this Playbook with any changes
- [ ] Set "Last Reviewed" date

**Section 8 — Known Issues & Operational Gaps**
From the smoke test (S1.5.1):
- Claude API key not stored in Token Vault — scoring pipeline deployed but inactive
- Morning digest (cron function) has no REST endpoint — verification requires wp-admin or waiting for 0600 CST trigger
- GitLab is massively behind production (1 of 37 snippets in repo)
- 20 monolithic snippets flagged for refactoring (deferred to Sprint 2)

**Section 9 — Integration Test Protocol** (DR-0017)
Define the standard smoke test that runs at every sprint close:
- 10-step chain test (from S1.5.1): RSS → AI → Dashboard → Promote → Render → Commentary → Refresh → Digest → Audit → Diagnostics
- 6-component security verification (from S1.5.2): Vault → Nonce → AppPass → RateLimit → Audit → Logger
- Gate criteria: ALL steps must pass, zero tolerance
- Output format: `S{sprint}_Smoke_Test_Results.md`

**Section 10 — File Map & Boot Sequence**
Document the full file structure in the Scrum Master folder and the recommended read order for new sessions. Reference `PROJECT_INDEX.md` and add this playbook to the boot sequence.

### Playbook Header
Include at the top:
```
# The Markdown — Operational Playbook
**Version:** 1.0
**Last Reviewed:** 2026-03-09 (Sprint 1.5)
**Next Review:** Sprint 2 boundary
**Owner:** Taskmaster
**Status:** LIVING DOCUMENT — update every sprint boundary
```

### Critical Requirement
This playbook must be added to the Taskmaster system prompt boot sequence (Step 1 file list) per A16. Note this in the playbook itself and flag it for S1.5.7 (continuity update).

---

## TASK 2: S1.5.6 — Credential Handling Protocol (Critical, ~0.5h)

**Output file:** `S1.5.6_Credential_Protocol.md`

Define the credential handling protocol per DR-0022 and retro action A20. This is referenced by the Operational Playbook (Section 3) but lives as a standalone document for security isolation.

### Must Include

**1. Credential Inventory**
| Credential | Purpose | Scope | Provider | Rotation |
|-----------|---------|-------|----------|----------|
| GitLab PAT | Repo access, issue management | `api` | Yeti at session start | Multiple times daily |
| WordPress App Password | REST API write access | `feed-items`, `snippets` | Stored in WordPress.com | Per WordPress.com policy |
| Claude API Key | AI scoring pipeline | Anthropic API | Yeti stores in Token Vault (S1.17) | Per key rotation procedure (S1.20) |
| WordPress.com OAuth | Admin session | Full admin | Browser session | Session-based |

**2. Handling Rules**
- Credentials are provided by Yeti at session start — ASK IMMEDIATELY
- Use in-memory only — NEVER write to disk, files, or git
- NEVER include in commit messages, log files, or output documents
- Assume any PAT is rotated within hours
- If a credential stops working mid-session, ask Yeti for a fresh one — don't troubleshoot
- Token Vault (S1.17) is the ONLY persistent credential store — and it's encrypted AES-256-CBC in wp_options

**3. Per-API Protocol**

For each API the project uses, document:
- What credential is needed
- What scopes/permissions are required
- How to request it at session start
- What to do if it fails mid-session

Pattern for agents:
> "I need a GitLab PAT with `api` scope to update issues. Can you provide one for this session?"

**4. Security Boundaries**
- The vault encryption key (Snippet ID 31) is the root secret — if compromised, rotate all vault contents
- Key rotation procedure is documented in S1.20 (Key-Rotation.php)
- Audit log (S1.22) captures all vault operations
- Never store the vault key anywhere except the Code Snippet — this is DR-0012 trade-off

---

## TASK 3: S1.5.7 — Update All Continuity Artifacts (High, ~1h)

**No output file — this task modifies existing files.**

Update every continuity artifact to reflect the current state after Sprint 1.5 completion. This closes retro action A14 (update for micro-snippet architecture) and ensures any new session starts from an accurate baseline.

### Files to Update

**1. `PROJECT_INDEX.md`**
- Update Sprint 1.5 status from "APPROVED — in planning" to "✅ COMPLETE — 8/8 closed"
- Update overall count: 38/81 tasks closed (47%)
- Add `Operational_Playbook.md` to the Priority 1 read list
- Add `S1.5.6_Credential_Protocol.md` to the Priority 2 read list
- Add Sprint 1.5 output files to the file index
- Update "Next Steps" section: Sprint 1.5 complete → Sprint 2 planning next
- Add to Critical Rules: "Read Operational_Playbook.md at session start"

**2. `Sprint_Tracker.md`**
- Mark S1.5.5, S1.5.6, S1.5.7 as ✅ CLOSED with today's date
- Update Sprint 1.5 header: "Status: ✅ COMPLETE | Tasks: 8/8 closed"
- Update effort summary totals

**3. `Scrum_Master_System_Prompt.md`**
- Add `Operational_Playbook.md` to the Boot Sequence Step 1 file list (per A16)
- Add `S1.5.6_Credential_Protocol.md` to the Boot Sequence Step 1 file list
- Note: This is a prompt change — log it in the Prompt Change Log

**4. `Prompt_Change_Log.md`**
- Log the system prompt modification (adding Playbook + Credential Protocol to boot sequence)
- Include: what changed, why, who requested it, what sections modified

**5. `Decision_Register.md`**
- Add DR-0023: S1.5.4 monolithic snippet refactoring deferred to Sprint 2 prep — risk assessed as low if snippets aren't modified before refactor
- Add DR-0024 if any new decisions emerge from the Playbook build

**6. `Scrum_Burndown_Dashboard.html`**
- Update Sprint 1.5 progress to 8/8 (100%)
- Update overall progress to 38/81
- Mark Sprint 1.5 as COMPLETE
- Update "Last Updated" timestamp

**7. `Sprint_1.5_Task_List.md`**
- Mark all 8 tasks as ✅ CLOSED with dates
- Update status header to COMPLETE

### Verification Checklist
After all updates, verify:
- [ ] PROJECT_INDEX.md reflects Sprint 1.5 complete, Playbook in boot sequence
- [ ] Sprint_Tracker.md shows 38/81 closed, Sprint 1.5 = 8/8
- [ ] System prompt boot sequence includes Operational_Playbook.md
- [ ] Prompt Change Log has the system prompt update entry
- [ ] Decision Register has DR-0023 (refactor deferral)
- [ ] Dashboard shows Sprint 1.5 at 100%
- [ ] Sprint 1.5 Task List shows all 8 tasks closed
- [ ] No file references a stale Sprint 1.5 status

---

## OUTPUT & CONTINUITY RULES

### File Naming
```
Operational_Playbook.md          ← S1.5.5 deliverable (lives at root of Scrum Master folder)
S1.5.6_Credential_Protocol.md   ← S1.5.6 deliverable
```
S1.5.7 modifies existing files — no new output file.

### After EACH Task
1. Save the output file
2. If any blocker is discovered, add to `Blocker_Register.md`
3. If any decision is made, add to `Decision_Register.md`

### After ALL Tasks
1. Run the S1.5.7 verification checklist above
2. Confirm all 8 Sprint 1.5 tasks show as CLOSED in Sprint_Tracker.md
3. Update the Burndown Dashboard
4. Summarize what was done in a brief message to Yeti

### DO NOT
- Store any credentials in any file (except documenting what's needed, not the values)
- Modify PHP code files
- Make changes to WordPress or GitLab (this is a documentation sprint)
- Skip the verification checklist — it's how we prevent drift

---

## CONTEXT FROM CLAUDE CODE HANDOFF

The following findings from the Claude Code execution (S1.5.1–S1.5.4) must be incorporated into the Playbook:

### WordPress REST API Structure (for Playbook Section 1)
- Custom post type: `ns_feed_item` (rest_base: `feed-items`)
- Custom namespace: `ns/v1` with routes: `/blocks`, `/diagnostics/health`
- Code Snippets API: `code-snippets/v1/snippets`

### Authentication (for Playbook Section 3 / S1.5.6)
- Username: `h3ndriksj`
- Method: Application Password (HTTP Basic Auth)
- App password auth active on write endpoints

### Snippet Load Order (for Playbook Section 4)
- Priority 1: NS Vault Key (ID 31) — encryption key constant
- Priority 2: S1.23 Diagnostic Logger (ID 37) — `ns_diag_write()`
- Priority 10: Everything else

### Tool Hierarchy (for Playbook Section 2)
- REST API: fully functional for snippets, feed items, custom endpoints
- Admin AJAX: reachable but requires nonce (browser session)
- Browser automation: only needed for nonce-protected admin actions

### Refactoring Backlog (for Playbook Section 8)
- 7 critical monoliths (>300 lines): IDs 7, 25, 26, 27, 28, 30, 37
- 13 moderate (80-300 lines)
- Follow Gate 2 micro-snippet pattern for refactoring
- Deferred to Sprint 2 prep (DR-0023)

### Smoke Test Gaps (for Playbook Section 8)
- Claude API key not in Token Vault — scoring inactive
- Morning digest has no REST endpoint — cron-only verification
- GitLab has only 1 of 37 PHP files — critical drift

---

## EXECUTION ORDER

1. **S1.5.5** — Build the Operational Playbook first (it's the foundation)
2. **S1.5.6** — Define credential protocol (referenced by the Playbook)
3. **S1.5.7** — Update all continuity artifacts (incorporates both deliverables)

This order ensures each task builds on the previous one. Do not skip ahead.

---

## DONE CRITERIA — Sprint 1.5 Closure

Sprint 1.5 is COMPLETE when:
- [ ] All 8 tasks marked CLOSED in Sprint_Tracker.md
- [ ] Operational Playbook exists and is referenced in boot sequence
- [ ] Credential Protocol exists and is referenced in Playbook
- [ ] All continuity artifacts reflect Sprint 1.5 completion
- [ ] Burndown Dashboard updated to 38/81 (47%)
- [ ] No stale references to Sprint 1.5 as "in progress" anywhere
- [ ] Next action clear: Sprint 2 planning session

Once these are met, Sprint 1.5 gate PASSES and Sprint 2 planning can begin.

---

*Generated: 2026-03-09 by Taskmaster (Cowork session)*
*Sprint 1.5 — Integration Gate | The Markdown Project*
*Closes retro actions: A14, A15, A16, A18, A19, A20*
