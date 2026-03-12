# The Markdown — Sprint 0 — Immediate Hardening

**Status:** ✅ COMPLETE — 7/7 closed
**Total Effort:** ~5 hours
**Milestone:** Sprint 0 — Immediate Hardening
**Gate:** G0 ✅ PASSED
**Source of Truth:** [GitLab Issues #1–#7](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Last Synced:** 2026-03-09

---

## Task Status (synced from GitLab)

| GitLab # | Task ID | Title | Owner | Severity | Status | Closed |
|----------|---------|-------|-------|----------|--------|--------|
| #1 | S0.1 | Harden wp-config.php — move above webroot, set file permissions 400 | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| #2 | S0.2 | Disable XML-RPC — block xmlrpc.php at server level | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |
| #3 | S0.3 | Remove default admin account — create unique admin username | Taskmaster | High | ✅ CLOSED | 2026-03-08 |
| #4 | S0.4 | Set security headers — CSP, X-Frame-Options, HSTS, X-Content-Type | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| #5 | S0.5 | Enforce HTTPS everywhere — redirect all HTTP to HTTPS | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| #6 | S0.6 | Disable directory listing — prevent index browsing | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |
| #7 | S0.7 | Hide WordPress version — remove generator meta tag | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |

---

## Cyber Gate 0 — Sprint 0 Exit Criteria ✅ ALL PASSED

- [x] wp-config.php hardened (S0.1)
- [x] XML-RPC disabled (S0.2)
- [x] Default admin account removed (S0.3)
- [x] Security headers deployed (S0.4)
- [x] HTTPS enforced everywhere (S0.5)
- [x] Directory listing disabled (S0.6)
- [x] WordPress version hidden (S0.7)

---

## WordPress.com Platform Notes

These tasks were originally scoped for self-hosted WordPress. On WordPress.com (per DR-0001), some tasks work differently:

- **S0.2 (XML-RPC):** Disabled via Code Snippets: `add_filter('xmlrpc_enabled', '__return_false');`
- **S0.3 (Default admin):** Unique admin username confirmed via WP admin panel.
- **S0.4 (Security headers):** WordPress.com manages most headers. Verified via securityheaders.com scan.
- **S0.7 (WP version):** Hidden via Code Snippet: `remove_action('wp_head', 'wp_generator');`

---

## CI/CD Audit Results

**Audit Date:** 2026-03-08
**Auditor:** Taskmaster + Cyber Ops

### Findings

| # | Category | Finding | Severity | Status |
|---|----------|---------|----------|--------|
| 1 | CI/CD Config | No hardcoded secrets in any .gitlab-ci.yml | ✅ CLEAN | — |
| 2 | Pipeline Logs | No secrets leaked in pipeline job logs | ✅ CLEAN | — |
| 3 | CI/CD Variables | No project or group CI/CD variables set | ✅ CLEAN | — |
| 4 | Deploy Keys | No deploy keys configured | ✅ CLEAN | — |
| 5 | Pipeline Triggers | No external triggers configured | ✅ CLEAN | — |
| 6 | Protected Branches | Main branch protected on all 6 repos | ✅ GOOD | — |
| 7 | Repo Visibility | All 6 repos set to PRIVATE | ✅ GOOD | — |
| 8 | Members | Solo operator only (h3ndriks.j, Owner) | ✅ GOOD | — |
| 9 | PAT Hygiene | 12/13 PATs properly revoked | ✅ GOOD | — |
| 10 | Active PAT | "Alistaair" PAT has overprivileged scopes: api, create_runner, manage_runner, k8s_proxy | ⚠️ MEDIUM | RECOMMEND: Rotate with minimum required scopes |
| 11 | Container Registry | Enabled on all repos but likely unnecessary | ℹ️ LOW | RECOMMEND: Disable if not using containers |
| 12 | Shared Runners | Enabled on all repos (standard) | ℹ️ INFO | No action needed |

### PAT Inventory

| Name | Status | Scopes | Expires |
|------|--------|--------|---------|
| Gunthers Key | REVOKED | 15 scopes (full access) | 2026-04-01 |
| Guntherver2 (x2) | REVOKED | 13 scopes | 2026-04-01 |
| Guntherv03 (x4) | REVOKED | 15 scopes | 2026-04-01 |
| Alistaair (x6) | 5 REVOKED, 1 ACTIVE | 15 scopes (full access) | 2026-04-02 |

**Recommendation:** Rotate active "Alistaair" PAT to minimum scopes: `read_repository, write_repository, read_api` only.

---

*Synced from GitLab by Taskmaster 2026-03-09. GitLab is the source of truth.*
