# Sprint 3 — Wave 1 Cyber Ops Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 1 (Independent Tasks — No Blockers)
**Prepared by:** Taskmaster
**For:** Claude Code agent (Cyber Ops role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 1 Cyber Ops tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — GET YOUR CREDENTIALS

**Before touching ANY endpoint or running ANY code, ask Yeti for:**

1. **GitLab PAT** — `api` scope, for protected branch config and issue management
   - Project ID: `80070684`
   - Repo: `gitlab.com/h3ndriks.j/JK.com-ver02`
   - Assume any PAT is short-lived. Yeti rotates multiple times daily.

2. **WordPress Application Password** — for REST API verification
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

**Do not proceed past this section until you have both credentials.**

---

## 1. MISSION

Execute the Cyber Ops-owned Wave 1 security hardening tasks for Sprint 3. These are **3 independent security tasks** that harden the GitLab pipeline, WordPress plugin surface, and file permissions. All tasks are independent — zero inter-dependencies within this wave.

**Your tasks (3 total):**

| ID | Task | Type | GitLab Issue | Priority |
|----|------|------|-------------|----------|
| S3.9 | GitLab CI/CD pipeline security — protected branches, signed commits | SEC | #60 | Medium |
| S3.10 | WordPress plugin audit — remove unused, update all, allowlist | SEC | #61 | Medium |
| S3.12 | File permission hardening — wp-content, uploads, themes | SEC | #63 | High |

**Not your tasks (other owners):**
- S3.1, S3.6, S3.8 → Taskmaster (Cowork)
- S3.5, S3.11, CF-1, CF-3 → DevOps (Code)

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No server-level file permissions.
- **File permissions are managed by WordPress.com** — you cannot `chmod` files directly.
- **Plugin management:** Via WordPress admin or REST API (`wp/v2/plugins` if available on plan).
- **WAF active** — WordPress.com manages its own WAF.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Current active snippets:** 74 (of 85 total)

### GitLab

- **Project ID:** `80070684`
- **Repo:** `gitlab.com/h3ndriks.j/JK.com-ver02`
- **API base:** `https://gitlab.com/api/v4/projects/80070684`
- **Current pipelines:** All passing
- **Branches:** `main` (primary)

### Tool Hierarchy (DR-0021)

1. **GitLab REST API** — for branch protection, pipeline config, repo scanning
2. **WordPress REST API** — for plugin inventory, health checks
3. **Browser automation** — LAST RESORT. Only if API genuinely can't do it.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. TASK DETAILS

### Task 1: S3.9 — GitLab CI/CD Pipeline Security

**GitLab Issue:** #60
**Priority:** Medium

**What to do:**

1. **Protected branches** — Configure `main` as a protected branch:
   - No force push allowed
   - Merge requires maintainer approval
   - Use GitLab API: `PUT /projects/:id/protected_branches`

2. **Review .gitlab-ci.yml** — If CI/CD pipeline exists:
   - Verify no secrets in pipeline config
   - Verify pipeline variables are masked
   - Check for any `allow_failure` on security-relevant jobs

3. **Signed commits** — Document the requirement:
   - GPG key setup instructions for Yeti
   - GitLab push rules for signed commits (if available on plan)
   - `PUT /projects/:id/push_rule` with `reject_unsigned_commits: true`

4. **Repository settings review:**
   - Verify project visibility is `private`
   - Check deploy keys — remove any unused
   - Check webhooks — verify all point to expected targets
   - Check project access tokens — audit and document

**Acceptance Criteria:**
- Protected branch rules active on `main`
- No secrets in pipeline config or variables (all masked)
- Signed commit policy documented (enforced if plan supports it)
- Audit results documented in results file

**Implementation approach:**
- Use GitLab REST API for all configuration changes
- `GET /projects/:id` — verify visibility
- `GET /projects/:id/protected_branches` — check current state
- `POST /projects/:id/protected_branches` — protect `main`
- `GET /projects/:id/hooks` — audit webhooks
- `GET /projects/:id/deploy_keys` — audit deploy keys
- `GET /projects/:id/access_tokens` — audit tokens

---

### Task 2: S3.10 — WordPress Plugin Audit

**GitLab Issue:** #61
**Priority:** Medium

**What to do:**

1. **Inventory all plugins** — List every installed plugin with:
   - Name, version, active/inactive status
   - Last update date
   - Whether it's from WordPress.com marketplace or third-party

2. **Remove unused plugins** — Any plugin that is:
   - Inactive AND not required by active functionality → recommend removal
   - Note: On WordPress.com, removal may require admin UI action by Yeti

3. **Update all plugins** — Check for available updates:
   - Document current vs available versions
   - Flag any plugins with known vulnerabilities

4. **Create allowlist** — Document the approved plugin set:
   - Plugin name, purpose, version, update policy
   - Flag any plugins that should be replaced

**WordPress.com constraint:** Plugin management is limited on WordPress.com. Some plugins are managed by the platform. The agent should:
- Use REST API `GET /wp/v2/plugins` (if endpoint available)
- If REST endpoint is not available, use browser automation to read the plugins page
- Document findings — Yeti will execute removals/updates if needed

**Acceptance Criteria:**
- Complete plugin inventory documented
- Unused plugins identified with removal recommendations
- All plugins checked for updates
- Allowlist created with rationale for each plugin

---

### Task 3: S3.12 — File Permission Hardening

**GitLab Issue:** #63
**Priority:** High

**What to do:**

**WordPress.com constraint:** File permissions on WordPress.com are managed by the platform. You cannot SSH in and `chmod`. This task adapts to the platform:

1. **Audit wp-content exposure** via REST API and browser:
   - Can `/wp-content/uploads/` be directory-listed? (Should be blocked — S0.6 should have handled this)
   - Are any uploaded files accessible that shouldn't be?
   - Check for `.htaccess` or equivalent protections

2. **Verify directory listing is blocked** (cross-check S0.6):
   - `GET /wp-content/` — should return 403 or redirect, not a directory listing
   - `GET /wp-content/uploads/` — same check
   - `GET /wp-content/themes/` — same check

3. **Check for exposed sensitive files:**
   - `GET /wp-config.php` — should 403 or 404 (S0.1)
   - `GET /xmlrpc.php` — should be blocked (S0.2)
   - `GET /readme.html` — should be removed or blocked
   - `GET /license.txt` — should be removed or blocked
   - `GET /wp-admin/install.php` — should redirect to login

4. **Verify Code Snippets plugin is not exposing source:**
   - Confirm snippet code is not accessible via any public endpoint
   - Verify `/wp-json/code-snippets/v1/snippets` requires authentication

5. **Document findings** with PASS/FAIL per check and remediation recommendations.

**Acceptance Criteria:**
- All directory listing checks pass (403, not 200)
- No sensitive files exposed publicly
- Code Snippets API requires auth (verified)
- Results documented with evidence (HTTP status codes)

---

## 4. EXECUTION ORDER

```
1. S3.9 (GitLab pipeline security)   — API-heavy, fastest to execute    ~1.5h
2. S3.10 (WordPress plugin audit)    — inventory + analysis              ~1.5h
3. S3.12 (File permission hardening) — verification + documentation      ~1h
```

---

## 5. OUTPUT FILES

After completing all tasks, produce a results file:

**File:** `07 Cyber Team/S3_Wave1_CyberOps_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- GitLab configuration changes made (with API calls used)
- Plugin inventory table
- File permission audit results (HTTP status codes)
- Remediation recommendations for any failures
- Items requiring Yeti action (plugin removal, GPG key setup, etc.)

---

## 6. DONE CRITERIA — Wave 1 Cyber Ops

Wave 1 Cyber Ops is DONE when:
- [ ] S3.9: `main` branch protected, pipeline config reviewed, signed commit policy documented
- [ ] S3.10: Plugin inventory complete, unused plugins flagged, allowlist created
- [ ] S3.12: All directory/file exposure checks pass, Code Snippets auth verified
- [ ] Results file created with evidence
- [ ] GitLab issues #60, #61, #63 closed (or updated with findings)

---

## 7. SECURITY REQUIREMENTS — NON-NEGOTIABLE

1. **No credentials in output files.** Scrub all results before saving.
2. **No tokens in commit messages.** Ever.
3. **Audit everything.** Every configuration change gets documented with before/after state.
4. **If something fails, document it.** Don't silently skip.
5. **State your tool choice** at the start of every task with reasoning.

---

## 8. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `07 Cyber Team/` | Overwatch prompt and existing findings |

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 1 Cyber Ops is unlocked. Execute in order, verify each task, push results.*
