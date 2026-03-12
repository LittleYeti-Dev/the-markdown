# Sprint 3 — Wave 1 Cyber Ops Results

**Date:** 2026-03-11
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 1 (Independent Tasks)
**Executed by:** Claude Code (Cyber Ops role)

---

## Task 1: S3.9 — GitLab CI/CD Pipeline Security

**GitLab Issue:** #60
**Overall Result:** PASS
**Tool Used:** GitLab REST API (per DR-0021 tool hierarchy)

### 1.1 Protected Branches

| Check | Result | Evidence |
|-------|--------|----------|
| `main` is protected | PASS | Branch protection already configured |
| Force push disabled | PASS | `allow_force_push: false` |
| Push access | PASS | Maintainers only (level 40) |
| Merge access | PASS | Maintainers only (level 40) |

**API:** `GET /projects/80070684/protected_branches`
**State:** No changes needed — protection was already correctly configured.

### 1.2 Pipeline Configuration Review (.gitlab-ci.yml)

| Check | Result | Evidence |
|-------|--------|----------|
| No secrets in pipeline config | PASS | Config only copies static HTML files to `public/` |
| No sensitive commands | PASS | Uses `busybox` image, `cp` commands only |
| No `allow_failure` on security jobs | N/A | No security-specific jobs in pipeline |
| Pipeline variables masked | PASS | No CI/CD variables configured (empty) |

**Pipeline overview:** Single `pages` job that deploys static HTML files (prototype, dashboards) to GitLab Pages. Minimal attack surface.

### 1.3 Signed Commits

| Check | Result | Evidence |
|-------|--------|----------|
| Push rules API | N/A | `GET /projects/80070684/push_rule` returns 404 — not available on free tier |
| `reject_unsigned_commits` | N/A | Push rules feature requires Premium/Ultimate plan |

**Signed Commit Policy (documented for Yeti):**

To enable GPG-signed commits:

1. **Generate GPG key:** `gpg --full-generate-key` (RSA 4096-bit recommended)
2. **Export public key:** `gpg --armor --export YOUR_KEY_ID`
3. **Add to GitLab:** Settings > GPG Keys > paste public key
4. **Configure Git locally:**
   ```
   git config --global user.signingkey YOUR_KEY_ID
   git config --global commit.gpgsign true
   ```
5. **Enforce via push rules** (requires GitLab Premium):
   `PUT /projects/80070684/push_rule` with `reject_unsigned_commits: true`

> **Action for Yeti:** Set up GPG key. Push rule enforcement requires plan upgrade.

### 1.4 Repository Settings Review

| Check | Result | Evidence |
|-------|--------|----------|
| Project visibility | PASS | `private` |
| Deploy keys | PASS | None configured (clean) |
| Webhooks | PASS | None configured (clean) |
| Project access tokens | PASS | None configured (clean) |

---

## Task 2: S3.10 — WordPress Plugin Audit

**GitLab Issue:** #61
**Overall Result:** PASS
**Tool Used:** WordPress REST API `GET /wp/v2/plugins` (per DR-0021 tool hierarchy)

### 2.1 Complete Plugin Inventory

| # | Plugin | Version | Status | Managed | Purpose |
|---|--------|---------|--------|---------|---------|
| 1 | Akismet Anti-spam | 5.6 | Active | Yes | Spam protection |
| 2 | Classic Editor | 1.6.7 | **Inactive** | Yes | Legacy editor — not in use |
| 3 | CoBlocks | 3.1.17 | Active | Yes | Page building content blocks |
| 4 | Code Snippets | 3.9.5 | Active | **No** | Custom code execution (core to project) |
| 5 | Crowdsignal Dashboard | 3.1.5 | Active | Yes | Polls and surveys |
| 6 | Crowdsignal Forms | 1.8.0 | Active | Yes | Form blocks for Crowdsignal |
| 7 | Gravatar Enhanced | 0.13.0 | Active | Yes | Avatar functionality |
| 8 | Gutenberg | 22.6.0 | Active | Yes | Block editor (development version) |
| 9 | Jetpack | 15.7-a.1 | Active | Yes | Security, performance, marketing suite |
| 10 | Layout Grid | 1.8.5 | Active | Yes | Grid layout blocks |
| 11 | Page Optimize | 0.6.2 | Active | Yes | JS/CSS performance optimization |

**Total:** 11 plugins (10 active, 1 inactive)
**Platform-managed:** 10 of 11 (managed by WordPress.com)
**User-installed:** 1 (Code Snippets)

### 2.2 Unused Plugin Recommendations

| Plugin | Status | Recommendation | Reason |
|--------|--------|---------------|--------|
| Classic Editor | Inactive | **Remove** | Not in use; site uses Gutenberg block editor |

> **Action for Yeti:** Remove Classic Editor via WordPress admin (Plugins > Delete).

### 2.3 Plugin Review Flags

| Plugin | Flag | Notes |
|--------|------|-------|
| Crowdsignal Dashboard + Forms | Review usage | Two Crowdsignal plugins active — verify if both are needed or if one can be removed |
| Gutenberg | Dev version | Running `22.6.0` (development channel) — managed by WordPress.com, not a concern |
| Code Snippets | Only non-managed plugin | Version 3.9.5 — verify this is the latest stable release |

### 2.4 Approved Plugin Allowlist

| Plugin | Purpose | Update Policy | Keep/Replace |
|--------|---------|---------------|-------------|
| Akismet Anti-spam | Spam protection | Auto (managed) | Keep |
| CoBlocks | Content blocks | Auto (managed) | Keep |
| Code Snippets | Custom code execution | Manual — monitor releases | Keep (core to project) |
| Crowdsignal Dashboard | Polls/surveys | Auto (managed) | Keep (verify usage) |
| Crowdsignal Forms | Form blocks | Auto (managed) | Keep (verify usage) |
| Gravatar Enhanced | Avatars | Auto (managed) | Keep |
| Gutenberg | Block editor | Auto (managed) | Keep |
| Jetpack | Security suite | Auto (managed) | Keep |
| Layout Grid | Grid layouts | Auto (managed) | Keep |
| Page Optimize | Performance | Auto (managed) | Keep |

---

## Task 3: S3.12 — File Permission Hardening

**GitLab Issue:** #63
**Overall Result:** PARTIAL
**Tool Used:** HTTP requests via curl (per DR-0021 — direct API/HTTP checks before browser)

### 3.1 Directory Listing Checks

| Path | HTTP Status | Result | Notes |
|------|------------|--------|-------|
| `/wp-content/` | 200 | **ADVISORY** | Returns HTML page (not directory listing). WordPress.com platform behavior — no file list exposed, but path returns content rather than 403. Platform-managed, cannot change. |
| `/wp-content/uploads/` | 403 | **PASS** | Blocked — Forbidden |
| `/wp-content/themes/` | 200 | **ADVISORY** | Same as `/wp-content/` — returns HTML page, not a directory listing. Platform-managed. |

### 3.2 Sensitive File Exposure

| File | HTTP Status | Result | Notes |
|------|------------|--------|-------|
| `/wp-config.php` | 403 | **PASS** | Blocked — Forbidden |
| `/xmlrpc.php` (GET) | 405 | **PARTIAL** | Method Not Allowed for GET |
| `/xmlrpc.php` (POST) | 200 | **FAIL** | POST method accepted — xmlrpc is accessible. S0.2 may not have fully blocked this. |
| `/readme.html` | 200 | **FAIL** | Publicly accessible — exposes WordPress version info and branding |
| `/license.txt` | 200 | **FAIL** | Publicly accessible — exposes license text |
| `/wp-admin/install.php` | 403 | **PASS** | Blocked — Forbidden |

### 3.3 Code Snippets API Access

| Check | HTTP Status | Result | Notes |
|-------|------------|--------|-------|
| Unauthenticated access | 401 | **PASS** | Returns Unauthorized — requires authentication |
| Authenticated access | 200 | **PASS** | Returns snippet data (expected for authenticated users) |

**Code Snippets API is properly secured** — requires valid credentials to access snippet source code.

### 3.4 Remediation Recommendations

**HIGH Priority:**

1. **`/xmlrpc.php` POST accessible (FAIL)**
   - XML-RPC should be fully blocked, not just GET-restricted
   - S0.2 may have addressed GET but not POST
   - **Remediation:** Add Code Snippet to block XML-RPC entirely:
     ```php
     add_filter('xmlrpc_enabled', '__return_false');
     ```
   - Or add `.htaccess` equivalent if WordPress.com supports it
   - **Action for Yeti:** Verify S0.2 implementation; add snippet if missing

2. **`/readme.html` accessible (FAIL)**
   - Exposes WordPress version info — useful for attackers fingerprinting the site
   - **Remediation:** On WordPress.com, this file is platform-managed and cannot be deleted directly
   - **Action for Yeti:** Check if WordPress.com support can remove or block access, or add redirect via snippet

3. **`/license.txt` accessible (FAIL)**
   - Low severity but unnecessary information disclosure
   - **Remediation:** Same as readme.html — platform-managed
   - **Action for Yeti:** Same as above

**LOW Priority (Advisory):**

4. **`/wp-content/` and `/wp-content/themes/` return 200**
   - These return rendered HTML pages (not directory listings) on WordPress.com
   - No actual file enumeration is possible
   - **No action required** — this is expected WordPress.com platform behavior

---

## Summary

| Task | Issue | Result | Findings |
|------|-------|--------|----------|
| S3.9 — GitLab Pipeline Security | #60 | **PASS** | Branch protected, no secrets, clean config. Signed commits need GPG setup + plan upgrade for enforcement. |
| S3.10 — Plugin Audit | #61 | **PASS** | 11 plugins inventoried, 1 inactive flagged for removal (Classic Editor), allowlist created. |
| S3.12 — File Permission Hardening | #63 | **PARTIAL** | 3 failures: xmlrpc POST accessible, readme.html exposed, license.txt exposed. Code Snippets API properly secured. |

---

## Action Items for Yeti

| # | Action | Priority | Task |
|---|--------|----------|------|
| 1 | Set up GPG key for signed commits | Medium | S3.9 |
| 2 | Consider GitLab Premium for push rule enforcement | Low | S3.9 |
| 3 | Remove Classic Editor plugin | Low | S3.10 |
| 4 | Verify both Crowdsignal plugins are needed | Low | S3.10 |
| 5 | Block xmlrpc.php POST — add `xmlrpc_enabled` filter snippet or verify S0.2 | High | S3.12 |
| 6 | Request WordPress.com support to block readme.html | Medium | S3.12 |
| 7 | Request WordPress.com support to block license.txt | Low | S3.12 |

---

*Results compiled 2026-03-11 by Claude Code (Cyber Ops). All API calls used authenticated tokens held in memory only. No credentials stored in this file.*
