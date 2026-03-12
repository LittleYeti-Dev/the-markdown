# Sprint 1.5 — Claude Code Execution Prompt

**Paste this entire prompt into Claude Code at session start.**

---

## BOOT SEQUENCE — READ BEFORE DOING ANYTHING

You are DevOps + Cyber Ops + Foreman executing Sprint 1.5 (Integration Gate) for **The Markdown** project. Before touching any code or API:

1. Read `Scrum Master/PROJECT_INDEX.md` — master reference
2. Read `Scrum Master/Sprint_1.5_Task_List.md` — your task list
3. Read `Scrum Master/Decision_Register.md` — all 22 decisions including DR-0016 (Lego blocks), DR-0017 (mandatory smoke test), DR-0022 (credential handling)
4. Read `Scrum Master/Sprint_Tracker.md` — live status

**Ask Yeti for a GitLab PAT (`api` scope) immediately. Do not store it. Assume it rotates within hours.**

---

## PROJECT CONTEXT

- **Platform:** WordPress.com (NOT self-hosted) — deploy via Code Snippets plugin
- **GitLab Repo:** `gitlab.com/h3ndriks.j/JK.com-ver02` (Project ID: `80070684`)
- **Architecture:** Lego block micro-snippets (DR-0016) — one function per snippet
- **Tool Hierarchy:** REST API first → WP-CLI if available → browser automation last
- **Source of Truth:** GitLab (not local files, not chat history)

### Deployed Sprint 1 Snippets (12 total)
| File | Function |
|------|----------|
| `S1-12-Editorial-Dashboard.php` | Admin feed list with filters |
| `S1-13-Promote-Block-Assign.php` | Promote button + block assignment |
| `S1-14-Page-Template.php` | 7-block page layout rendering |
| `S1-15-Commentary-Cards.php` | 3 embed styles for commentary |
| `S1-16-Auto-Refresh.php` | AJAX polling every 15 min |
| `S1-17-Token-Vault.php` | AES-256 encrypted wp_options vault |
| `S1-18-Nonce-Verification.php` | Nonce checks on admin AJAX |
| `S1-19-App-Passwords.php` | REST API application passwords |
| `S1-20-Key-Rotation.php` | Claude API key rotation procedure |
| `S1-21-Rate-Limiting.php` | 60 req/min/IP on REST endpoints |
| `S1-22-Audit-Logging.php` | Custom DB table for admin action audit |
| `S1-23-Diagnostic-Logger.php` | Structured error/warning/info logging |

---

## YOUR 5 TASKS — Execute in This Order

### Task 1: S1.5.8 — Admin Housekeeping (0.5h)
**Owner:** Taskmaster | **Type:** ADMIN

Using the GitLab API (`glab` CLI or REST):
1. Close milestone **Sprint 0 — Hardening** (if not already closed)
2. Close milestone **Sprint 1 — Foundation Build** (if not already closed)
3. Verify issue S1.23 (Diagnostic Logger) is assigned to Sprint 1 milestone
4. Set due dates on milestones:
   - Sprint 1.5: **2026-03-10**
   - Sprint 2: **2026-03-17**
   - Sprint 3: **2026-03-24**
5. Confirm DR-0019 is approved (it is — just verify the milestone exists)

**Output file:** `Scrum Master/S1.5.8_Admin_Results.md`
Include: milestone IDs, status confirmation, any issues found.

---

### Task 2: S1.5.3 — Snippet Inventory Audit (1h)
**Owner:** DevOps | **Type:** OPS

Produce a complete inventory of all active Code Snippets on the WordPress.com site:
1. Query the WordPress.com REST API for all code snippets (or use the Code Snippets plugin endpoint)
2. Cross-reference against the 12 Sprint 1 snippets listed above
3. Check GitLab repo for any snippets that exist in the repo but aren't deployed (or vice versa)
4. Document for each snippet:
   - Snippet name and ID
   - Active/inactive status
   - Load order / priority
   - Dependencies (which snippets call which)
   - Size (line count)
   - Whether it's monolithic or micro (flag anything over ~80 lines)

**Output file:** `Scrum Master/S1.5.3_Snippet_Inventory.md`
Include: full inventory table, dependency map, any drift between GitLab and production, flagged monolithic snippets for S1.5.4.

---

### Task 3: S1.5.4 — Refactor Monolithic Snippets (2h)
**Owner:** DevOps | **Type:** OPS | **Depends on:** S1.5.3

Using the inventory from S1.5.3:
1. Identify any snippet that caused WAF issues or is over ~80 lines
2. Refactor into Lego block micro-snippets per DR-0016
3. Test each refactored snippet individually
4. Deploy via REST API with correct load order
5. Verify no regressions after each deploy

**Rules:**
- One function per snippet where practical
- Clear naming convention: `S1-XX-FunctionName.php`
- Document load order dependencies
- If a snippet is fine as-is, note it and move on — don't refactor for the sake of it

**Output file:** `Scrum Master/S1.5.4_Refactor_Results.md`
Include: what was refactored, before/after line counts, new snippet names, load order, test results.

---

### Task 4: S1.5.1 — Full-Chain Smoke Test (2h)
**Owner:** DevOps + Foreman | **Type:** VERIFY | **CRITICAL — Must pass with zero failures**

Test the ENTIRE chain end-to-end. Every step must succeed:

| Step | Test | Expected Result |
|------|------|-----------------|
| 1 | RSS ingest fires | Feed items appear in `ns_feed_item` CPT |
| 2 | AI scoring runs | Items get `ns_ai_score` meta field populated |
| 3 | Editorial dashboard loads | `/wp-admin` feed list renders with filters |
| 4 | Promote action works | Item status changes, block assignment saves |
| 5 | Page template renders | 7-block layout displays promoted content |
| 6 | Commentary cards render | All 3 embed styles work |
| 7 | Auto-refresh fires | AJAX poll returns fresh data |
| 8 | Digest generation | Digest endpoint returns formatted output |
| 9 | Audit log captures | Admin actions appear in audit table |
| 10 | Diagnostic logger works | Errors/warnings route to log table |

**Gate Criteria:** ALL 10 steps must pass. Any failure = stop and fix before continuing.

**Output file:** `Scrum Master/S1.5.1_Smoke_Test_Results.md`
Include: pass/fail for each step, response times, any errors encountered, screenshots or API responses as evidence.

---

### Task 5: S1.5.2 — Security Stack Verification (1h)
**Owner:** Cyber Ops | **Type:** VERIFY | **CRITICAL — Must pass with zero failures**

Verify every security component is active and functioning:

| Component | Test Method | Expected |
|-----------|------------|----------|
| Token Vault (S1-17) | Read encrypted option, verify AES-256 | Encrypted value in wp_options |
| Nonce Verification (S1-18) | Send AJAX without nonce | 403 rejection |
| App Passwords (S1-19) | Auth REST call with app password | 200 success |
| Rate Limiting (S1-21) | Burst 65+ requests to REST endpoint | 429 after 60 |
| Audit Logging (S1-22) | Perform admin action, check DB table | Row appears with correct data |
| Diagnostic Logger (S1-23) | Trigger a warning, check log table | Entry with level/source/message |

**Gate Criteria:** ALL 6 components must pass. Zero tolerance.

**Output file:** `Scrum Master/S1.5.2_Security_Verification.md`
Include: pass/fail matrix, test evidence (API responses, status codes), any vulnerabilities found.

---

## CRITICAL — OUTPUT & CONTINUITY RULES

### All output files MUST be saved to the local `Scrum Master/` folder.
This is the shared persistence layer between Claude Code and Cowork. The Taskmaster (running in Cowork) will:
- Read your output files to update the Sprint Tracker
- Incorporate results into the Operational Playbook (S1.5.5)
- Update the Burndown Dashboard
- Close tasks in the tracker as you complete them

### File naming convention:
```
Scrum Master/S1.5.X_TaskName_Results.md
```

### After EACH task completes:
1. Save the output file to `Scrum Master/`
2. Update `Scrum Master/Sprint_Tracker.md` — change the task status from `⬜ OPEN` to `✅ CLOSED` with today's date
3. If any blocker is discovered, add it to `Scrum Master/Blocker_Register.md` with a new BLK-ID

### After ALL tasks complete:
1. Save a summary file: `Scrum Master/S1.5_ClaudeCode_Summary.md` with:
   - Tasks completed (pass/fail)
   - Total time spent
   - Blockers discovered
   - Items flagged for the Operational Playbook
   - Any decisions that need to go in the Decision Register
2. Commit all output files to GitLab in a single commit: `sprint-1.5: integration gate results`

### DO NOT:
- Store any credentials in files
- Modify files outside `Scrum Master/` without asking Yeti
- Skip a failed test — fix it or flag it as a blocker
- Assume anything works — verify everything

---

## HANDOFF PROTOCOL

When you're done, the Taskmaster in Cowork will pick up your output files and:
- Build the Operational Playbook (S1.5.5) using your smoke test + security results
- Define the credential handling protocol (S1.5.6) based on what you documented
- Update all continuity artifacts (S1.5.7) with Sprint 1.5 outcomes
- Update the Burndown Dashboard

**This is a relay, not a silo. Your output is their input.**

---

*Generated: 2026-03-09 by Taskmaster (Cowork session)*
*Sprint 1.5 — Integration Gate | The Markdown Project*
