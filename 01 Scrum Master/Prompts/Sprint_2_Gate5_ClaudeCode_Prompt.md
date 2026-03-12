# Sprint 2 Gate 5 — Claude Code Execution Prompt

**Version:** 1.1
**Date:** 2026-03-09
**Sprint:** Sprint 2 — Social & Publishing
**Gate:** Gate 5 — AI + Quick-Publish (S2.9–S2.22)
**Prepared by:** Taskmaster
**For:** Claude Code (DevOps / Cyber Ops / Foreman roles)

> **This is a handoff document.** It contains everything you need to execute Sprint 2 Gate 5 without reading the full project history. Read this top-to-bottom, then execute.

---

## 0. WHAT JUST HAPPENED — GATE 4 CONTEXT

Gate 4 (Social API Integrations) was built by Claude Code and verified by Taskmaster:

- **21 PHP snippets deployed** (IDs 38–58), all active, zero code errors
- **Health endpoint:** `"status":"healthy"`, 0 errors, 0 warnings
- **7 GitLab issues closed** (S2.1, S2.2, S2.4, S2.5, S2.6, S2.7, S2.8)
- **S2.3 (Instagram)** remains blocked (BLK-001 — Facebook App Review)
- **DR-0024:** Gate 4 snippets range 95–160 lines. The 80-line Lego block target (DR-0016) was exceeded but all deployed without WAF issues. **For Gate 5: target <80 lines, accept up to ~150 for complex snippets (OAuth, admin UI).** This is the accepted guideline going forward.
- **4 TEMP snippets** (IDs 59–62, inactive) were created for X credential storage — can be cleaned up

The social posting functions are live. Gate 5 wires them into an AI pipeline and quick-publish UI.

---

## 1. MISSION

Build the AI content pipeline and quick-publish UI for **The Markdown** — a WordPress.com editorial platform. Gate 5 adds:

- AI-powered content reformatting (1 note → 5 platform versions)
- Quick-publish panel with platform toggles and preview
- Multi-platform dispatch (one-click publish to X, LinkedIn, YouTube, Medium)
- Arc scoring and daily summaries
- Security hardening (CSRF, XSS, audit trail, rate limiting)

**Gate 5 scope:** 14 tasks (S2.9–S2.22) → ~26 micro-snippets.

---

## 2. PLATFORM — READ THIS FIRST

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — keep snippets <80 lines target, ~150 max (DR-0024).
- **One function per snippet** where practical.

| Property | Value |
|----------|-------|
| Site URL | `https://justin-kuiper.com` |
| REST base | `https://justin-kuiper.com/wp-json/` |
| Code Snippets API | `code-snippets/v1/snippets` |
| Auth | HTTP Basic — username `h3ndriksj` + Application Password |
| Custom namespace | `ns/v1` |
| Custom Post Type | `ns_feed_item` (rest_base: `feed-items`) |
| GitLab Project ID | `80070684` |
| GitLab Repo | `gitlab.com/h3ndriks.j/JK.com-ver02` |

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — for deploying snippets, reading data, health checks
2. **GitLab REST API** — for closing issues, committing code
3. **Admin AJAX** — for nonce-protected admin actions only
4. **Browser** — LAST RESORT, visual QA only

**State your tool choice and reasoning at the start of every task.**

---

## 3. CREDENTIALS — ASK YETI IMMEDIATELY

Before doing anything, request:

1. **WordPress Application Password** — for REST API auth (snippet deploy, health checks)
2. **GitLab PAT** — `api` scope, for closing issues and committing code

**Handling rules:**
- Use in-memory only. NEVER write to disk, git, logs, or output.
- Use placeholders like `[APP_PASSWORD]` in any generated docs.
- If a credential fails mid-session, ask Yeti for a fresh one — don't troubleshoot.
- Platform API creds (X, LinkedIn, YouTube, Medium) are already in Token Vault from Gate 4.

---

## 4. ARCHITECTURE — LEGO BLOCK PATTERN

### Rules for ALL Gate 5 Code (DR-0016, updated DR-0024)

- **Target <80 lines.** Accept up to ~150 for complex UI/admin snippets.
- **One function per snippet** where practical.
- Naming: `S2-G5-M{nn} {Description}` (FUNC) or `S2-G5-S{nn} {Description}` (SEC).
- Explicit dependency via `function_exists()` checks.
- Full docblock header.

### Snippet Template

```php
<?php
/**
 * S2.{N} — {Title}
 * Sprint 2, Gate 5 | GitLab Issue #{NN}
 *
 * {One-line description.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G5-M{nn} {Short Title}"
 * Scope: {global | admin | front-end}
 * Priority: 10
 * Depends on: {list}
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

### Load Order

- Default priority: **10** for everything.
- Priority 1: NS Vault Key (ID 31). Priority 2: Diagnostic Logger (ID 37).
- **Do not change existing load order.**

---

## 5. EXISTING INFRASTRUCTURE — Functions You Can Call

### Layer 0-2 — Foundation + Core + Security (Sprint 1)

| Function | Snippet ID | What It Does |
|----------|-----------|-------------|
| `NS_VAULT_KEY` | 31 (p1) | AES-256 encryption key constant |
| `ns_diag_write($level, $component, $message, $context)` | 37 (p2) | Structured diagnostic logging to DB |
| `ns_log($message, $level)` | 9 | Forwards to ns_diag_write() |
| `ns_get_api_key($service)` | 9 | Retrieves decrypted key from vault |
| `ns_vault_store($service, $token)` | 30 | Encrypt + store token in wp_options |
| `ns_vault_retrieve($service)` | 30 | Decrypt + return stored token |
| `ns_vault_delete($service)` | 30 | Remove a stored token |
| `ns_audit_log($action, $target_type, $target_id, $label, $details)` | 32 | Write to ns_audit_log DB table |
| `ns_call_claude_api($messages, $args)` | 21 | Call Anthropic API with messages array |
| `ns_build_scoring_prompt($item)` | 24 | Build Claude scoring prompt for a feed item |
| `ns_sanitize_rss_content($content)` | 14 | Sanitize RSS content |
| `ns_sanitize_llm_input($text)` | 14 | Sanitize text before sending to LLM |

### Layer 4 — Gate 4 Social Posting Functions (IDs 38-58, all active)

**These are the functions Gate 5 wires into the publish pipeline:**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_twitter_post_tweet($text, $media_ids = array())` | 48 | Post single tweet |
| `ns_twitter_post_thread($items)` | 58 | Post threaded tweets |
| `ns_twitter_upload_media($image_url)` | 49 | Upload image, get media_id |
| `ns_twitter_count_chars($text)` | 48 | Count characters (280 limit) |
| `ns_linkedin_create_post($text, $image_url = '')` | 50 | Post to LinkedIn profile |
| `ns_linkedin_get_person_urn()` | 50 | Get authenticated user's LinkedIn URN |
| `ns_youtube_create_community_post($text, $image_url = '')` | 51 | Post YouTube community post |
| `ns_youtube_get_channel()` | 51 | Get YouTube channel info |
| `ns_medium_create_draft($title, $content, $tags = array())` | 52 | Create Medium draft |
| `ns_medium_get_user_id()` | 52 | Get Medium user ID |

**Token Functions:**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_twitter_get_token()` | 38 | Get current X access token |
| `ns_linkedin_get_token()` | 39 | Get current LinkedIn token |
| `ns_youtube_get_token()` | 40 | Get current YouTube token |

**Error Handling & Multi-Publish:**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_social_multi_publish($platforms)` | 57 | Dispatch to multiple platforms, isolate failures |
| `ns_social_api_error($platform, $response, $context)` | 57 | Centralized error handler with token scrubbing |
| `ns_social_scrub_tokens($text)` | 57 | Remove tokens from any text |
| `ns_social_map_error_code($platform, $http_code, $error_code)` | 57 | Structured error codes |

**OAuth & Token Management (reference only — don't need to call these from Gate 5):**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_twitter_refresh_token()` | 38 | Refresh X OAuth token |
| `ns_linkedin_refresh_token()` | 39 | Refresh LinkedIn token |
| `ns_youtube_refresh_token()` | 40 | Refresh YouTube token |
| `ns_oauth_validate_callback($url)` | 53 | Validate OAuth callback URL |
| `ns_token_refresh_run()` | 55 | Daily cron: refresh all platform tokens |
| `ns_token_refresh_alert($failures)` | 56 | Email admin on refresh failure |

---

## 6. GATE 5 TASKS — Full Breakdown

### Execution Order (dependency-resolved)

```
PHASE A — AI Pipeline (build first, no UI dependency):
  S2.12  AI Output Validation (SEC/Critical) — must wrap all AI calls
  S2.13  AI Response Length Enforcement (SEC) — must cap all AI output
  S2.9   AI Content Reformatter — core: 1 note → 5 platform versions
  S2.11  Arc Scoring — content vs thematic arc alignment
  S2.10  7-Block Daily Summary — 0800 CST standup

PHASE B — Infrastructure (REST + RSS):
  S2.17  REST API Endpoints — publish actions, extended feed queries
  S2.18  RSS Feed Output — /feed/the-markdown

PHASE C — Quick-Publish UI (depends on A + B):
  S2.14  Quick-Publish Panel — inline editor + platform toggles
  S2.15  5-Version Preview — side-by-side platform preview
  S2.16  Publish Dispatch — one-click multi-platform post

PHASE D — Security Hardening (depends on C):
  S2.19  CSRF Protection on quick-publish
  S2.20  Content Escaping — XSS prevention on social output
  S2.21  Publish Audit Trail — log all dispatch events
  S2.22  Rate Limit Handler — exponential backoff + queue
```

---

### S2.12 — AI Output Validation (SEC — Critical) ⚡ BUILD FIRST

**GitLab Issue:** #41 | **Owner:** Cyber Ops

Central validation for all Claude API responses. Every other AI task depends on this.

**Build:** `ns_validate_ai_output($output, $context)`
- Strip HTML/script injection from Claude responses
- Validate JSON structure when expected
- Reject outputs with suspicious patterns (prompt injection attempts, encoded scripts)
- Sanitize before storage or display
- Return clean output or WP_Error

**Snippet:** S2-G5-S01 AI Output Validator (~70 lines)

**Acceptance:**
- ✅ All Claude responses pass through validator before use
- ✅ HTML/script stripped, malformed JSON handled gracefully
- ✅ Suspicious patterns logged and rejected
- ✅ Zero unsafe content reaches storage/display

---

### S2.13 — AI Response Length Enforcement (SEC)

**GitLab Issue:** #42 | **Owner:** Cyber Ops

**Build:** `ns_enforce_ai_length($text, $context)`
- Per-context max lengths: tweet (300), linkedin (1500), medium (10000), summary (5000), default (3000)
- Graceful truncation at word/sentence boundary
- Log truncation events as warnings

**Snippet:** S2-G5-S02 AI Length Enforcer (~50 lines)

**Acceptance:**
- ✅ All AI output length-checked before use
- ✅ Truncation at word boundary (not mid-word)
- ✅ Context-specific limits applied
- ✅ Truncation warnings logged

---

### S2.9 — AI Content Reformatter

**GitLab Issue:** #38 | **Owner:** Foreman

Core value: takes one editorial note and produces 5 platform-optimized versions.

**Build:** `ns_reformat_for_platforms($content, $context = array())`
- Calls `ns_call_claude_api()` with platform-specific prompts
- Returns associative array: `['twitter' => ..., 'linkedin' => ..., 'youtube' => ..., 'medium' => ..., 'instagram' => ...]`
- Twitter: respects 280-char limit (or provides thread-ready chunks)
- LinkedIn: professional tone, ~1300 chars
- YouTube: community post format, casual
- Medium: full article with markdown formatting
- Instagram: caption-ready with hashtag suggestions
- All output through `ns_validate_ai_output()` and `ns_enforce_ai_length()`
- Cache results in post meta to avoid redundant API calls

**Snippets:**
- S2-G5-M01 AI Content Reformatter (~75 lines) — core function
- S2-G5-M02 Platform Prompt Templates (~60 lines) — per-platform prompt strings

**Acceptance:**
- ✅ Single input → 5 platform versions
- ✅ Twitter within 280 chars (or thread-split)
- ✅ All output validated + length-enforced
- ✅ Results cached in post meta
- ✅ Audit + diagnostic logging

---

### S2.11 — Arc Scoring

**GitLab Issue:** #40 | **Owner:** DevOps

Evaluate content against editorial thematic arcs.

**Build:**
- `ns_get_arcs()` — retrieve defined arcs from wp_options
- `ns_save_arcs($arcs)` — save arc definitions
- `ns_score_arc_alignment($item_id, $arcs)` — Claude scores content vs each arc (1-10)
- Arcs are editorial themes (e.g., "AI governance", "startup culture")
- Results stored as post meta on `ns_feed_item`

**Snippets:**
- S2-G5-M05 Arc Config Manager (~60 lines) — CRUD for arcs
- S2-G5-M06 Arc Scorer (~70 lines) — scoring via Claude API
- S2-G5-M07 Arc Admin Settings (~60 lines) — admin page to manage arcs

**Acceptance:**
- ✅ Admin can define arcs (name + description)
- ✅ Content scores 1-10 per arc via Claude
- ✅ Scores saved as post meta
- ✅ All output validated

---

### S2.10 — 7-Block Daily Summary

**GitLab Issue:** #39 | **Owner:** DevOps

Daily standup briefing via WP-Cron.

**Build:**
- WP-Cron at 0800 CST daily
- Reads top-scored feed items from last 24h
- Compiles standup-style summary for 7-block layout
- Stores as transient for dashboard consumption
- Optional email digest to admin

**Snippets:**
- S2-G5-M03 Daily Summary Generator (~75 lines) — cron + compilation
- S2-G5-M04 Summary Email Sender (~40 lines) — email notification

**Acceptance:**
- ✅ Cron fires at 0800 CST
- ✅ Summary includes top items from last 24h
- ✅ Format compatible with 7-block page template
- ✅ Diagnostic logging

---

### S2.17 — REST API Endpoints

**GitLab Issue:** #46 | **Owner:** Foreman

Extend the `ns/v1` namespace with publish-related endpoints.

**Build:**
- `GET /ns/v1/blocks` — already exists (verify, extend if needed)
- `GET /ns/v1/feed-items` — custom filtered feed item list (scores, domains, promoted status)
- `POST /ns/v1/publish` — trigger publish dispatch via REST
- `GET /ns/v1/publish/status/{id}` — check publish job status
- All write endpoints require Application Password auth
- All operations logged

**Snippets:**
- S2-G5-M13 Publish REST Routes (~75 lines) — register publish endpoints
- S2-G5-M14 Feed Items REST Extension (~60 lines) — extended feed queries

**Acceptance:**
- ✅ All endpoints under `ns/v1`
- ✅ Auth required on writes
- ✅ Proper HTTP status codes
- ✅ Rate limited (existing S1.21)

---

### S2.18 — RSS Feed Output

**GitLab Issue:** #47 | **Owner:** DevOps

Custom RSS feed for subscribers.

**Build:**
- Register custom feed at `/feed/the-markdown`
- Output promoted feed items in RSS 2.0 format
- Fields: title, link, description, pubDate, category (domain)
- 20 most recent promoted items
- Proper XML escaping

**Snippet:** S2-G5-M15 RSS Feed Generator (~70 lines)

**Acceptance:**
- ✅ Accessible at `/feed/the-markdown`
- ✅ Valid RSS 2.0
- ✅ Only promoted items
- ✅ Proper XML escaping
- ✅ 20-item limit

---

### S2.14 — Quick-Publish Panel

**GitLab Issue:** #43 | **Owner:** DevOps

Admin panel integrated into the editorial dashboard (S1.12).

**Build:**
- Inline text editor (textarea with rich-text-like toolbar or plain)
- Platform toggle checkboxes: X, LinkedIn, YouTube, Medium (Instagram disabled until BLK-001 resolves)
- Character count per platform with limit indicators
- "Reformat with AI" button → calls `ns_reformat_for_platforms()`
- "Publish" button → calls S2.16 dispatch
- AJAX handlers for all panel actions

**Snippets:**
- S2-G5-M08 Quick-Publish Panel UI (~75 lines, admin scope) — HTML/JS
- S2-G5-M09 Quick-Publish AJAX Handler (~70 lines) — server-side handlers

**Acceptance:**
- ✅ Panel renders in admin dashboard
- ✅ Platform toggles work
- ✅ Character counts update
- ✅ AI reformat button works
- ✅ Nonce-protected AJAX
- ✅ manage_options capability check

---

### S2.15 — 5-Version Preview

**GitLab Issue:** #44 | **Owner:** DevOps

Side-by-side preview of all platform versions.

**Build:**
- Shows all 5 platform versions after AI reformatter runs
- Each shows: platform icon/label, formatted text, character count, status
- Per-version edit capability (manual tweaks before publish)
- Updates when reformatter re-runs

**Snippets:**
- S2-G5-M10 Platform Preview Renderer (~75 lines, admin scope)
- S2-G5-M11 Preview AJAX Handler (~50 lines)

**Acceptance:**
- ✅ All 5 versions displayed side-by-side
- ✅ Character counts per platform
- ✅ Per-version edit works
- ✅ Preview refreshes after reformat

---

### S2.16 — Publish Dispatch

**GitLab Issue:** #45 | **Owner:** Foreman

The dispatch engine: content + selected platforms → post to each.

**Build:** `ns_quick_publish($content_versions, $platforms, $options = array())`
- Uses `ns_social_multi_publish()` from Gate 4 (ID 57) for platform isolation
- Accepts per-platform content from the preview panel (not raw content)
- Tracks success/failure per platform
- Returns consolidated result to UI
- Integrates with audit trail (S2.21)

**Snippet:** S2-G5-M12 Publish Dispatcher (~75 lines)

**Acceptance:**
- ✅ Dispatches to all selected platforms
- ✅ Failures isolated per platform
- ✅ Per-platform success/failure returned
- ✅ All dispatches audit-logged
- ✅ No token leakage

---

### S2.19 — CSRF Protection (SEC)

**GitLab Issue:** #48 | **Owner:** Cyber Ops

Defense-in-depth nonces for the quick-publish panel.

**Build:**
- Per-action nonces: `ns_qp_reformat`, `ns_qp_preview`, `ns_qp_publish`
- Nonce generation on panel load
- Verification on all AJAX handlers
- Note: S1.18 blanket enforcement already catches missing nonces — this adds per-action granularity

**Snippet:** S2-G5-S03 Quick-Publish CSRF Guard (~55 lines)

**Acceptance:**
- ✅ Every publish action requires valid nonce
- ✅ Separate nonces per action type
- ✅ Failures logged, 403 returned

---

### S2.20 — Content Escaping (SEC)

**GitLab Issue:** #49 | **Owner:** Cyber Ops

Platform-specific XSS prevention on all social output.

**Build:** `ns_escape_social_output($text, $platform)`
- Strip ALL HTML for plain-text platforms (X, LinkedIn, YouTube)
- Preserve safe HTML for Medium: `<p>`, `<em>`, `<strong>`, `<a>`, `<h1>`-`<h6>`, `<ul>`, `<ol>`, `<li>`, `<code>`, `<pre>`, `<blockquote>`
- Encode special characters per platform
- Log when content is modified by escaping

**Snippet:** S2-G5-S04 Social Output Escaper (~65 lines)

**Acceptance:**
- ✅ All social output escaper-filtered before dispatch
- ✅ HTML stripped for plain-text platforms
- ✅ Safe HTML preserved for Medium
- ✅ Zero XSS vectors

---

### S2.21 — Publish Audit Trail (SEC)

**GitLab Issue:** #50 | **Owner:** Cyber Ops

Log every publish event. Admin viewer for history.

**Build:**
- Hook into dispatch, log via `ns_audit_log()`: platform, content hash (not full text), result, timestamp, user
- Admin page: filterable publish history (by platform, date, status)

**Snippets:**
- S2-G5-S05 Publish Audit Logger (~45 lines) — hook + logging
- S2-G5-S06 Publish Audit Viewer (~70 lines) — admin page with filters

**Acceptance:**
- ✅ Every dispatch logged (success + failure)
- ✅ Content hash stored (not full content)
- ✅ Admin can view + filter publish history
- ✅ Uses existing ns_audit_log() infrastructure

---

### S2.22 — Rate Limit Handler (SEC)

**GitLab Issue:** #51 | **Owner:** DevOps

Per-platform rate tracking with backoff and queue.

**Build:**
- Track API calls per platform in transients
- Exponential backoff on 429 responses
- Queue system for deferred posts (wp_options or custom table)
- WP-Cron job to retry queued posts
- Dashboard indicator when posts are queued

**Known platform limits:** X: 200/15min, LinkedIn: 100/day, YouTube: 10k quota/day, Medium: undocumented (conservative backoff)

**Snippets:**
- S2-G5-S07 Platform Rate Tracker (~65 lines) — track + enforce limits
- S2-G5-S08 Publish Queue Manager (~70 lines) — queue + retry via cron

**Acceptance:**
- ✅ Calls tracked per platform per time window
- ✅ 429 triggers exponential backoff
- ✅ Queued posts retry via cron
- ✅ Dashboard shows queue status
- ✅ All events logged

---

## 7. DEPLOYMENT PROCEDURE

### For Each Snippet:

```bash
# 1. Deploy as INACTIVE
curl -X POST "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets" \
  -u "h3ndriksj:[APP_PASSWORD]" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "S2-G5-M01 AI Content Reformatter",
    "code": "... escaped PHP ...",
    "priority": 10,
    "scope": "global",
    "active": false
  }'

# 2. Verify no syntax errors (check response)

# 3. Activate
curl -X PUT "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets/{ID}" \
  -u "h3ndriksj:[APP_PASSWORD]" \
  -H "Content-Type: application/json" \
  -d '{"active": true}'

# 4. Health check after EVERY activation
curl -u "h3ndriksj:[APP_PASSWORD]" \
  "https://justin-kuiper.com/wp-json/ns/v1/diagnostics/health"
# Must return: {"status":"healthy","errors_24h":0,...}
```

### Safe Activation Order:

```
WAVE 1 — AI Security (no external deps except Claude API):
  S2-G5-S01 AI Output Validator
  S2-G5-S02 AI Length Enforcer

WAVE 2 — AI Pipeline:
  S2-G5-M01 AI Content Reformatter
  S2-G5-M02 Platform Prompt Templates
  S2-G5-M05 Arc Config Manager
  S2-G5-M06 Arc Scorer
  S2-G5-M07 Arc Admin Settings
  S2-G5-M03 Daily Summary Generator
  S2-G5-M04 Summary Email Sender

WAVE 3 — REST + RSS:
  S2-G5-M13 Publish REST Routes
  S2-G5-M14 Feed Items REST Extension
  S2-G5-M15 RSS Feed Generator

WAVE 4 — Quick-Publish UI:
  S2-G5-M08 Quick-Publish Panel UI
  S2-G5-M09 Quick-Publish AJAX Handler
  S2-G5-M10 Platform Preview Renderer
  S2-G5-M11 Preview AJAX Handler
  S2-G5-M12 Publish Dispatcher

WAVE 5 — Security Hardening:
  S2-G5-S03 Quick-Publish CSRF Guard
  S2-G5-S04 Social Output Escaper
  S2-G5-S05 Publish Audit Logger
  S2-G5-S06 Publish Audit Viewer
  S2-G5-S07 Platform Rate Tracker
  S2-G5-S08 Publish Queue Manager
```

**Health check between every activation. If errors appear, deactivate immediately.**

---

## 8. SECURITY REQUIREMENTS — NON-NEGOTIABLE

1. **All tokens in Token Vault.** `ns_vault_store()` / `ns_vault_retrieve()`. Never wp_options directly.
2. **No tokens in logs.** Use `ns_social_scrub_tokens()` on all error output.
3. **Nonce on all admin AJAX.** S1.18 blanket enforcement + explicit per-action nonces.
4. **All AI output through `ns_validate_ai_output()`.** (S2.12 — build first!)
5. **All AI output length-checked via `ns_enforce_ai_length()`.** (S2.13)
6. **All social output through `ns_escape_social_output()`.** (S2.20)
7. **HTTPS only.** All API calls must be HTTPS.
8. **Capability checks.** `manage_options` on all admin endpoints and AJAX handlers.
9. **Audit everything.** Every publish, AI call, and error → `ns_audit_log()`.
10. **Diagnostic logging.** Every significant event → `ns_diag_write()`.

---

## 9. BLOCKERS & CONSTRAINTS

| ID | Blocker | Impact | Action |
|----|---------|--------|--------|
| BLK-001 | Facebook App Review | Instagram toggle disabled in UI; reformatter still generates Instagram version | Build but disable in UI |
| DR-0024 | Lego block line limit | Target <80, accept up to ~150 | Don't force-split functional code just to hit 80 |
| CONSTRAINT | WordPress.com WAF | Blocks large payloads | If a deploy fails, the snippet is too large — split it |
| CONSTRAINT | Claude API cost | Each reformat = 1 API call | Cache reformatted versions in post meta |
| CONSTRAINT | Platform rate limits | X: 200/15min, LinkedIn: 100/day | S2.22 handles backoff + queue |

---

## 10. DEFINITION OF DONE — PER TASK

A Gate 5 task is DONE when:

1. ✅ All micro-snippets deployed and active on production
2. ✅ Health check returns `"status":"healthy"` with 0 errors after activation
3. ✅ Functional test passes (AI reformat returns 5 versions / publish dispatches / etc.)
4. ✅ Security checks pass (nonces verified, output escaped, tokens scrubbed)
5. ✅ All actions captured in audit log
6. ✅ Diagnostic logger shows no errors from new snippets
7. ✅ GitLab issue closed with "deployed" label and verification comment

---

## 11. GATE 5 EXIT CRITERIA

Gate 5 passes when ALL of these are true:

- [ ] S2.9 (AI Content Reformatter) — DONE
- [ ] S2.10 (Daily Summary) — DONE
- [ ] S2.11 (Arc Scoring) — DONE
- [ ] S2.12 (AI Output Validation) — DONE
- [ ] S2.13 (AI Length Enforcement) — DONE
- [ ] S2.14 (Quick-Publish Panel) — DONE
- [ ] S2.15 (5-Version Preview) — DONE
- [ ] S2.16 (Publish Dispatch) — DONE
- [ ] S2.17 (REST Endpoints) — DONE
- [ ] S2.18 (RSS Feed Output) — DONE
- [ ] S2.19 (CSRF Protection) — DONE
- [ ] S2.20 (Content Escaping) — DONE
- [ ] S2.21 (Publish Audit Trail) — DONE
- [ ] S2.22 (Rate Limit Handler) — DONE
- [ ] All ~26 snippets deployed and active
- [ ] Health check clean (0 errors, 0 warnings)
- [ ] Integration test: reformat content → preview 5 versions → publish to 3+ platforms → verify audit trail
- [ ] All 14 GitLab issues closed with "deployed" label

---

## 12. CONTINUITY & RESILIENCE — DO NOT STOP ON FAILURE

**Standing order from Yeti:** *"Keep building in continuity, even if something breaks."*

This means:

### 12.1 If a snippet fails to deploy:
1. Log the failure (snippet ID, error message, HTTP status) in a running status block at the bottom of this file or in a `gate5_build_log.md`.
2. **Move to the next snippet.** Do not halt the gate.
3. After completing all other tasks, circle back to retry failed items.

### 12.2 If a dependency is unavailable:
1. Skip the blocked task entirely.
2. Build everything that CAN be built without it.
3. Log what was skipped and why.

### 12.3 Incremental tracking:
- After each successful deployment, **immediately** update the GitLab issue and Sprint Tracker. Do not batch updates to the end — if the session runs out of context, partial progress must be preserved.
- Maintain a running log: `gate5_build_log.md` with one line per snippet:
  ```
  [TIMESTAMP] ID XX — snippet_name — DEPLOYED / FAILED (reason) / SKIPPED (reason)
  ```

### 12.4 Session continuity:
- If the session is approaching context limits, **stop building and write a handoff summary** covering:
  - What was deployed (snippet IDs, task IDs closed)
  - What failed and needs retry
  - What was not attempted yet
  - Next action for a fresh session
- Save this summary to `gate5_handoff.md` so a new session can pick up without loss.

### 12.5 Wave deployment resilience:
- Deploy each wave independently. A failure in Wave 1 does not block Wave 2 unless there is a true code dependency.
- If a wave partially fails, deploy the successful snippets and log the failures for retry.

**The goal:** At any point where the session ends — gracefully or not — there is a clear record of what shipped and what remains.

---

## 13. CLAUDE CODE-SPECIFIC NOTES

- **Working directory:** `~/Documents/00 Notebooks/Gunther/Scrum Master/`
- **Git available:** Native git push to repo if PAT set as remote credential.
- **File I/O:** Direct filesystem access to all project files.
- **REST API:** Use `curl` or write helper scripts.
- **After completing each task:** Close the GitLab issue with comment: "Verified deployed to production. Snippet ID(s) [X] active with zero code errors. Closed by [agent]."
- **After completing all tasks:** Update `Sprint_Tracker.md` — mark S2.9–S2.22 as CLOSED.

---

## 15. FILE REFERENCES

Read these if you need deeper context (priority order):

| File | When to Read |
|------|-------------|
| `Operational_Playbook.md` | Full platform details, tool hierarchy, 4-layer dependency map |
| `Sprint_2_Code_Task_Prompt.md` | Gate 4 prompt — reference for patterns used |
| `snippets/sprint2-gate4/` | Gate 4 source code — see how the agent built last time |
| `Decision_Register.md` | All 24 project decisions |
| `Blocker_Register.md` | Active blockers |
| `S1.5.3_Snippet_Inventory.md` | Full pre-Gate4 snippet inventory |
| `S1.5.6_Credential_Protocol.md` | Credential handling rules |

---

## 16. RECOMMENDED START

Fastest path to first value:

1. **Ask Yeti for WordPress Application Password + GitLab PAT**
2. **S2.12 + S2.13 (AI Security)** — these must exist before any AI call
3. **S2.9 (AI Reformatter)** — core value: 1 note → 5 versions
4. **S2.17 (REST endpoints)** — infrastructure for the UI
5. **S2.14 + S2.15 (Quick-Publish Panel + Preview)** — the admin UI
6. **S2.16 (Publish Dispatch)** — wires UI to Gate 4 posting functions
7. **S2.11 (Arc Scoring) + S2.10 (Daily Summary)** — AI enhancements
8. **S2.18 (RSS Feed)** — subscriber feed
9. **S2.19–S2.22 (Security)** — CSRF, escaping, audit, rate limits

**Total: ~26 snippets across 14 tasks.**

---

*Prepared by Taskmaster — 2026-03-09. Gate 4 verified deployed. Standing by for Gate 5 execution.*
*v1.1 — Added Section 12 (Continuity & Resilience) per Yeti standing order.*
