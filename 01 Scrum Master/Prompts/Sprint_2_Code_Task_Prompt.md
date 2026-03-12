# Sprint 2 — Code Task Execution Prompt

**Version:** 1.0
**Date:** 2026-03-09
**Sprint:** Sprint 2 — Social & Publishing
**Prepared by:** Taskmaster
**For:** Claude Code + Cowork agents (DevOps / Cyber Ops roles)

> **This is a handoff document.** It contains everything a fresh agent session needs to begin Sprint 2 execution without re-reading the entire project history. Read this file, then execute.

---

## 1. MISSION

Build the social media API integration layer for **The Markdown** — a WordPress.com-based editorial platform. Sprint 2 adds OAuth-secured connections to 5 social platforms, an AI content reformatter, and a one-click multi-platform publish pipeline. All code deploys as PHP snippets via REST API to WordPress.com.

**Sprint 2 scope:** 22 tasks (~86h), split across Gate 4 (Social APIs) and Gate 5 (AI + Quick-Publish).

**This prompt covers the first execution batch:** Gate 4 Social API integrations (S2.1–S2.8).

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

1. **WordPress REST API** — fastest, most reliable. Use for deploying snippets, reading data, checking health.
2. **GitLab REST API** — for issue tracking, commits. PAT with `api` scope from Yeti.
3. **Admin AJAX** — for nonce-protected admin actions only.
4. **Browser automation** — LAST RESORT. Only for visual QA or nonce-dependent admin actions with no REST equivalent.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 2 Code

Every snippet must follow the micro-snippet pattern established in Gate 2:

```
REFERENCE PATTERN: Gate 2 micro-snippets (IDs 19-24)
- 43-66 lines each
- One function per snippet
- Clear naming: S2-G4-M{nn} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, gate, GitLab issue, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S2.{N} — {Title}
 * Sprint 2, Gate 4 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2.{N} {Short Title}"
 * Scope: {global | admin | front-end}
 * Priority: 10
 * Depends on: {list snippet dependencies}
 *
 * Acceptance Criteria (GitLab #{NN}):
 *   ✅ {criterion 1}
 *   ✅ {criterion 2}
 *
 * @package NonSequitur
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- IMPLEMENTATION ---
```

### Naming Convention

```
Snippet title:  S2-G4-M01 X OAuth Handler
                S2-G4-M02 X Tweet Composer
                S2-G4-M03 X Thread Builder
                S2-G5-M01 AI Content Reformatter
```

Format: `S{sprint}-G{gate}-M{sequence} {Description}`

### Load Order Rules

- Default priority: **10** (same as all Gate 2 snippets).
- Only change priority if the snippet MUST load before others.
- Current priority 1: NS Vault Key (ID 31). Priority 2: Diagnostic Logger (ID 37).
- **Do not change existing load order.**

---

## 4. EXISTING INFRASTRUCTURE — What You Can Call

### Layer 0 — Foundation (always available)

| Function | Snippet | What It Does |
|----------|---------|-------------|
| `NS_VAULT_KEY` | ID 31, priority 1 | AES-256 encryption key constant |
| `ns_diag_write($level, $component, $message, $context)` | ID 37, priority 2 | Structured diagnostic logging to DB |

### Layer 1 — Core (always available)

| Function | Snippet | What It Does |
|----------|---------|-------------|
| `ns_log($message, $level)` | ID 9 (Core Utilities) | Forwards to ns_diag_write() |
| `ns_get_api_key($service)` | ID 9 (Core Utilities) | Retrieves decrypted key from vault |

### Layer 2 — Security (always available)

| Function | Snippet | What It Does |
|----------|---------|-------------|
| `ns_vault_store($service, $token)` | ID 30 (Token Vault) | Encrypt + store token in wp_options |
| `ns_vault_retrieve($service)` | ID 30 (Token Vault) | Decrypt + return stored token |
| `ns_vault_delete($service)` | ID 30 (Token Vault) | Remove a stored token |
| `ns_vault_rotate_key($old, $new)` | ID 30 (Token Vault) | Re-encrypt all tokens with new key |
| `ns_audit_log($action, $target_type, $target_id, $label, $details)` | ID 32 (Audit Log) | Write to ns_audit_log DB table |

### Token Vault — Pre-Registered Services

The vault already allows these Sprint 2 service keys (defined in `ns_vault_allowed_services()`):

```
claude_api_key           ← already registered
twitter_oauth_token      ← Sprint 2
twitter_oauth_secret     ← Sprint 2
twitter_client_id        ← Sprint 2
twitter_client_secret    ← Sprint 2
linkedin_access_token    ← Sprint 2
linkedin_client_id       ← Sprint 2
linkedin_client_secret   ← Sprint 2
instagram_access_token   ← Sprint 2 (blocked by BLK-001)
instagram_app_secret     ← Sprint 2 (blocked by BLK-001)
youtube_api_key          ← Sprint 2
youtube_oauth_token      ← Sprint 2
medium_bearer_token      ← Sprint 2
```

**Use `ns_vault_store()` and `ns_vault_retrieve()` for ALL credential storage.** Never store tokens in wp_options directly. Never hardcode tokens in snippet code.

### Diagnostic Logging Pattern

Every new snippet should log significant events:

```php
if ( function_exists( 'ns_diag_write' ) ) {
    ns_diag_write( 'info', 'twitter', 'OAuth token refreshed', array(
        'expires_in' => $expires,
    ) );
}
```

Log levels: `error`, `warning`, `info`, `debug`.

---

## 5. GATE 4 TASKS — Social API Integrations

### Execution Order (dependency-resolved)

```
PHASE A — Platform Connectors (can run in parallel):
  S2.1  X (Twitter) API — OAuth 2.0 PKCE, tweet + thread
  S2.2  LinkedIn API — OAuth 2.0, personal profile posting
  S2.4  YouTube API — Data API v3, community posts
  S2.5  Medium API — bearer token, draft creation
  S2.3  Instagram API — ⛔ BLOCKED (BLK-001, skip for now)

PHASE B — Security Hardening (after Phase A):
  S2.6  OAuth callback URL validation — whitelist registered callbacks
  S2.7  Token refresh automation — daily cron, email alert on failure
  S2.8  Platform API error handling — graceful failure, no token leakage
```

### S2.1 — X (Twitter) API Integration

**GitLab Issue:** #30
**Owner:** DevOps
**Type:** FUNC
**Estimated:** ~8h
**Depends on:** S1 complete, Token Vault (S1.17)

**What to build:**
- OAuth 2.0 PKCE flow for X API v2
- Admin settings page for app credentials (Client ID, Client Secret) — store in Token Vault
- OAuth callback handler registered at `/ns/v1/oauth/twitter/callback`
- Tweet composition function: `ns_twitter_post_tweet($text, $media_ids = array())`
- Thread composition function: `ns_twitter_post_thread($tweets_array)`
- Character count validation (280 limit, URL shortening accounted for)
- Media upload support (images via media/upload endpoint)

**Micro-snippet breakdown (recommended):**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-M01 | X OAuth Handler | OAuth 2.0 PKCE flow + callback | ~60-70 |
| S2-G4-M02 | X Token Manager | Store/retrieve/refresh tokens via vault | ~40-50 |
| S2-G4-M03 | X Tweet Composer | Compose + post single tweet | ~40-50 |
| S2-G4-M04 | X Thread Builder | Compose + post threads | ~50-60 |
| S2-G4-M05 | X Media Uploader | Upload images to X media endpoint | ~40-50 |
| S2-G4-M06 | X Admin Settings | Settings page for app credentials | ~50-60 |

**Acceptance Criteria:**
- ✅ OAuth 2.0 PKCE flow completes end-to-end
- ✅ Tokens stored encrypted in Token Vault
- ✅ Single tweet posts successfully via API
- ✅ Thread posts with correct reply chaining
- ✅ Media upload works for images
- ✅ All actions logged via ns_audit_log()
- ✅ All errors logged via ns_diag_write()
- ✅ No tokens visible in logs, errors, or admin UI

**X API v2 Reference:**
- Base URL: `https://api.twitter.com/2/`
- Auth: OAuth 2.0 with PKCE (Authorization Code Flow)
- Scopes needed: `tweet.read`, `tweet.write`, `users.read`, `offline.access`
- Tweet endpoint: `POST /2/tweets`
- Thread: chain tweets via `reply.in_reply_to_tweet_id`
- Media: `POST https://upload.twitter.com/1.1/media/upload.json` (still v1.1)
- Rate limits: 200 tweets/15min per user, 300 tweets/15min per app

---

### S2.2 — LinkedIn API Integration

**GitLab Issue:** #31
**Owner:** DevOps
**Type:** FUNC
**Estimated:** ~6h
**Depends on:** S1 complete, Token Vault (S1.17)

**What to build:**
- OAuth 2.0 Authorization Code flow for LinkedIn
- Admin settings page for app credentials — store in Token Vault
- OAuth callback handler at `/ns/v1/oauth/linkedin/callback`
- Post composition function: `ns_linkedin_create_post($text, $image_url = '')`
- Author URN resolution (get authenticated user's person URN)
- Image sharing support via registerUpload + upload flow

**Micro-snippet breakdown (recommended):**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-M07 | LinkedIn OAuth Handler | OAuth 2.0 flow + callback | ~60-70 |
| S2-G4-M08 | LinkedIn Token Manager | Store/retrieve/refresh via vault | ~40-50 |
| S2-G4-M09 | LinkedIn Post Composer | Create text + image posts | ~50-60 |
| S2-G4-M10 | LinkedIn Admin Settings | Settings page for app creds | ~40-50 |

**Acceptance Criteria:**
- ✅ OAuth 2.0 flow completes with correct scopes
- ✅ Tokens stored in Token Vault (encrypted)
- ✅ Text post publishes to personal profile
- ✅ Image post publishes with thumbnail
- ✅ All actions audit-logged
- ✅ No tokens in logs or error output

**LinkedIn API Reference:**
- Base URL: `https://api.linkedin.com/v2/`
- Auth: OAuth 2.0 (3-legged)
- Scopes: `w_member_social`, `r_liteprofile`
- Post endpoint: `POST /ugcPosts` (or `POST /rest/posts` for new Community API)
- Image upload: `POST /assets?action=registerUpload` then binary upload
- Rate limits: 100 API calls/day for most endpoints

---

### S2.4 — YouTube API Integration

**GitLab Issue:** #33
**Owner:** DevOps
**Type:** FUNC
**Estimated:** ~5h
**Depends on:** S1 complete, Token Vault (S1.17)

**What to build:**
- Google OAuth 2.0 for YouTube Data API v3
- Admin settings page for Google API credentials
- OAuth callback handler at `/ns/v1/oauth/youtube/callback`
- Community post function: `ns_youtube_create_community_post($text, $image_url = '')`
- Channel info retrieval for verification

**Micro-snippet breakdown (recommended):**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-M11 | YouTube OAuth Handler | Google OAuth 2.0 + callback | ~60-70 |
| S2-G4-M12 | YouTube Token Manager | Store/retrieve/refresh via vault | ~40-50 |
| S2-G4-M13 | YouTube Community Posts | Create text/image community posts | ~50-60 |
| S2-G4-M14 | YouTube Admin Settings | Settings page for API creds | ~40-50 |

**Acceptance Criteria:**
- ✅ Google OAuth 2.0 completes with YouTube scopes
- ✅ Tokens in Token Vault
- ✅ Community post publishes
- ✅ Channel verification succeeds
- ✅ Audit + diagnostic logging on all operations

**YouTube Data API v3 Reference:**
- Base URL: `https://www.googleapis.com/youtube/v3/`
- Auth: Google OAuth 2.0
- Scopes: `https://www.googleapis.com/auth/youtube`, `https://www.googleapis.com/auth/youtube.force-ssl`
- Community posts: `POST /activities` (type: bulletin) or Channels API
- Note: Community posts API has limited availability — verify current status before building
- Rate limits: 10,000 quota units/day (default)

---

### S2.5 — Medium API Integration

**GitLab Issue:** #34
**Owner:** DevOps
**Type:** FUNC
**Estimated:** ~3h
**Depends on:** S1 complete, Token Vault (S1.17)

**What to build:**
- Bearer token auth (no OAuth flow — Medium uses integration tokens)
- Admin settings page for token entry — store in Token Vault
- Draft creation function: `ns_medium_create_draft($title, $content, $tags = array())`
- Markdown-to-HTML conversion for Medium's content format
- Author ID resolution

**Micro-snippet breakdown (recommended):**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-M15 | Medium API Client | Bearer auth + create drafts | ~50-60 |
| S2-G4-M16 | Medium Admin Settings | Token entry + author verification | ~40-50 |

**Acceptance Criteria:**
- ✅ Bearer token stored in Token Vault
- ✅ Draft creation works (title, content, tags)
- ✅ Content formats correctly (HTML)
- ✅ Audit + diagnostic logging

**Medium API Reference:**
- Base URL: `https://api.medium.com/v1/`
- Auth: Bearer token (integration token from Medium settings)
- User endpoint: `GET /me`
- Post endpoint: `POST /users/{userId}/posts`
- Content formats: `html` or `markdown`
- Posts are created as drafts by default (publishStatus: "draft")
- Rate limits: Undocumented — use conservative backoff

---

### S2.3 — Instagram API Integration

**GitLab Issue:** #32
**Status:** ⛔ BLOCKED by BLK-001 (Facebook App Review)
**Action:** SKIP until BLK-001 resolves. Build everything else first. Instagram integration uses the same pattern — add later.

---

### S2.6 — OAuth Callback URL Validation

**GitLab Issue:** #35
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.1–S2.5

**What to build:**
- Central callback URL whitelist: `ns_oauth_validate_callback($url)`
- Registered callback URLs stored in wp_options (not hardcoded)
- Validation on all OAuth callback handlers — reject unregistered URLs
- Admin UI to manage registered callback URLs

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-S01 | OAuth Callback Validator | Whitelist check on all callbacks | ~50-60 |
| S2-G4-S02 | OAuth Callback Admin | Admin page to manage whitelisted URLs | ~50-60 |

**Acceptance Criteria:**
- ✅ All callback handlers validate against whitelist
- ✅ Unregistered callbacks are rejected with 403
- ✅ Failed validation logged to audit log + diagnostic logger

---

### S2.7 — Token Refresh Automation

**GitLab Issue:** #36
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** Token Vault (S1.17)

**What to build:**
- Daily WP-Cron job: check all OAuth tokens for expiration
- Auto-refresh tokens that support refresh_token flow (X, LinkedIn, YouTube)
- Email alert to admin on refresh failure
- Refresh history logged to audit log

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-S03 | Token Refresh Cron | Daily cron to check + refresh OAuth tokens | ~60-70 |
| S2-G4-S04 | Token Refresh Alerter | Email admin on refresh failure | ~30-40 |

**Acceptance Criteria:**
- ✅ Cron fires daily and checks all platform tokens
- ✅ Tokens with refresh support auto-refresh before expiry
- ✅ Failed refresh sends email alert to admin
- ✅ All refresh attempts audit-logged

---

### S2.8 — Platform API Error Handling

**GitLab Issue:** #37
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.1–S2.5

**What to build:**
- Centralized error handler: `ns_social_api_error($platform, $response, $context)`
- Token scrubbing from all error output (logs, admin notices, AJAX responses)
- Graceful degradation — failed post to one platform doesn't block others
- Structured error codes per platform
- Retry logic with exponential backoff (link to S2.22 for full rate limit handling)

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G4-S05 | Social API Error Handler | Centralized error handling + token scrub | ~60-70 |

**Acceptance Criteria:**
- ✅ All API errors routed through centralized handler
- ✅ Zero token leakage in any error output (logs, admin UI, AJAX responses)
- ✅ Platform failure is isolated — other platforms continue working
- ✅ All errors logged via ns_diag_write() with structured context

---

## 6. DEPLOYMENT PROCEDURE

### For Each Snippet:

```bash
# 1. Deploy as INACTIVE
curl -X POST "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets" \
  -u "h3ndriksj:APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "S2-G4-M01 X OAuth Handler",
    "code": "<?php\n...",
    "priority": 10,
    "scope": "global",
    "active": false
  }'

# 2. Verify no syntax errors (check response for errors)

# 3. Activate
curl -X PUT "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets/{ID}" \
  -u "h3ndriksj:APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"active": true}'

# 4. Health check after activation
curl "https://justin-kuiper.com/wp-json/ns/v1/diagnostics/health"
# Expected: {"status":"healthy","errors_24h":0,...}
```

### Safe Activation Order for Gate 4:

1. **Token Managers first** (M02, M08, M12) — they use existing vault, low risk
2. **OAuth Handlers second** (M01, M07, M11) — register REST routes
3. **Admin Settings third** (M06, M10, M14, M16) — UI only, no external calls
4. **API Clients / Composers last** (M03-M05, M09, M13, M15) — call external APIs
5. **Security snippets after all FUNC** (S01-S05) — validate existing handlers

Run a health check between each activation. If errors appear, deactivate immediately.

---

## 7. SECURITY REQUIREMENTS — NON-NEGOTIABLE

1. **All tokens in Token Vault.** Use `ns_vault_store()` / `ns_vault_retrieve()`. Never wp_options directly.
2. **No tokens in logs.** Scrub all output with the error handler (S2.8 / S2-G4-S05).
3. **Nonce on all admin AJAX.** The blanket enforcement (S1.18) catches missing nonces, but add them explicitly too.
4. **OAuth state parameter.** All OAuth flows must use a random state parameter stored in transient, verified on callback.
5. **HTTPS only.** All callback URLs and API calls must be HTTPS.
6. **Capability checks.** Only `manage_options` users can access social API settings.
7. **Audit everything.** Every OAuth flow start, callback, token store, token refresh, post dispatch, and error gets an `ns_audit_log()` entry.
8. **Diagnostic logging.** Every significant event gets an `ns_diag_write()` entry.

---

## 8. BLOCKERS & CONSTRAINTS

| ID | Blocker | Impact | Action |
|----|---------|--------|--------|
| BLK-001 | Facebook App Review not submitted | Blocks S2.3 (Instagram only) | Skip S2.3, build everything else |
| BLK-004 | Dev favicon commit error | Cosmetic, non-blocking | Ignore for Sprint 2 |
| CONSTRAINT | WordPress.com WAF | Blocks large snippets | Keep all snippets <80 lines |
| CONSTRAINT | No wp-config.php | Can't store secrets in config | Token Vault (S1.17) is the credential store |
| CONSTRAINT | Code Snippets is deploy mechanism | All PHP deploys as snippets | Use REST API to deploy |

---

## 9. CREDENTIALS NEEDED AT SESSION START

Ask Yeti for these before doing anything:

1. **WordPress Application Password** — for REST API auth (Code Snippets deploy, health checks)
2. **GitLab PAT** (optional) — `api` scope, for closing issues and committing code
3. **X (Twitter) Developer App credentials** — Client ID + Client Secret (for S2.1)
4. **LinkedIn App credentials** — Client ID + Client Secret (for S2.2)
5. **Google/YouTube API credentials** — Client ID + Client Secret (for S2.4)
6. **Medium Integration Token** — from Medium settings (for S2.5)

**Handling rules:** Use in-memory only. Never write to disk or git. Never include in logs or commit messages.

---

## 10. DEFINITION OF DONE — PER TASK

A Gate 4 task is DONE when:

1. ✅ All micro-snippets are deployed and active on production
2. ✅ Health check returns `"status":"healthy"` after activation
3. ✅ OAuth flow completes end-to-end (for OAuth-based platforms)
4. ✅ At least one test post/draft succeeds via the API
5. ✅ All tokens stored in Token Vault (verified via vault admin page)
6. ✅ No tokens visible in any log output, error message, or admin UI
7. ✅ All actions captured in audit log
8. ✅ Diagnostic logger shows no errors from the new snippets
9. ✅ Snippet code committed to GitLab (if PAT provided)
10. ✅ Sprint Tracker updated (local file)

---

## 11. GATE 4 EXIT CRITERIA

Gate 4 (Publish-Security) is ready for review when:

- [ ] S2.1 (X/Twitter) — DONE
- [ ] S2.2 (LinkedIn) — DONE
- [ ] S2.4 (YouTube) — DONE
- [ ] S2.5 (Medium) — DONE
- [ ] S2.6 (OAuth callback validation) — DONE
- [ ] S2.7 (Token refresh automation) — DONE
- [ ] S2.8 (Error handling / no token leakage) — DONE
- [ ] S2.3 (Instagram) — BLOCKED (deferred, does not block gate)
- [ ] All snippets deployed and active
- [ ] Health check clean (0 errors)
- [ ] Integration test: post to at least 3 platforms from WordPress admin

**Gate 4 pass does NOT require Instagram (BLK-001).** All other platforms must work.

---

## 12. FILE REFERENCES

If you need deeper context, read these files (in priority order):

| File | When to Read |
|------|-------------|
| `Operational_Playbook.md` | Full platform details, tool hierarchy, snippet inventory |
| `S1.5.3_Snippet_Inventory.md` | All 37 deployed snippets with line counts and dependencies |
| `S1-17-Token-Vault.php` | Vault API reference (encrypt/decrypt/store/retrieve) |
| `S1-18-Nonce-Verification.php` | Nonce enforcement pattern to follow |
| `S1-22-Audit-Logging.php` | Audit log API reference |
| `Decision_Register.md` | All 23 project decisions with trade-offs |
| `Blocker_Register.md` | Active blockers |
| `Sprint_2_Task_List.md` | Full Gate 4 + Gate 5 task list with dependencies |

---

## 13. COWORK-SPECIFIC NOTES

When running in **Cowork** (not Claude Code):

- **Workspace folder:** The mounted folder IS the project folder. Read/write directly.
- **REST API calls:** Use `curl` via Bash tool. The VM has network access.
- **File creation:** Write snippet .php files to the workspace folder before deploying.
- **No native git push.** Use GitLab REST API (Commits endpoint) to push code:
  ```
  POST https://gitlab.com/api/v4/projects/80070684/repository/commits
  ```
- **Dashboard updates:** After completing tasks, update `Sprint_Tracker.md` and `Scrum_Burndown_Dashboard.html`.
- **Transcript:** Append to `Transcripts/YYYY-MM-DD_session.md` with actions taken.

---

## 14. CLAUDE CODE-SPECIFIC NOTES

When running in **Claude Code**:

- **Working directory:** `~/Documents/00 Notebooks/Gunther/Scrum Master/`
- **Git available:** Can do native git push to `gunther-ops` repo if PAT is set.
- **File I/O:** Direct filesystem access to all project files.
- **REST API:** Use `curl` or write helper scripts. No browser automation.
- **After completion:** Update Sprint_Tracker.md, close GitLab issues, log transcript.

---

## 15. RECOMMENDED START

If you're reading this fresh, here's the fastest path to first value:

1. **Ask Yeti for credentials** (WordPress App Password + platform API creds)
2. **Start with S2.5 (Medium)** — simplest integration (bearer token, no OAuth flow), fastest win
3. **Then S2.1 (X/Twitter)** — highest-visibility platform, OAuth 2.0 PKCE
4. **Then S2.2 (LinkedIn)** — standard OAuth 2.0
5. **Then S2.4 (YouTube)** — Google OAuth (most complex)
6. **After all FUNC tasks:** Build S2.6, S2.7, S2.8 security tasks
7. **Close gate:** Run integration test, request Cyber Ops gate review

---

*Prepared by Taskmaster — 2026-03-09. Standing by for tasking.*
