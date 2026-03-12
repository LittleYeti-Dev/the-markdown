# Sprint 2 Gate 5 — Code Task Execution Prompt

**Version:** 1.0
**Date:** 2026-03-09
**Sprint:** Sprint 2 — Social & Publishing
**Gate:** Gate 5 — AI + Quick-Publish (S2.9–S2.22)
**Prepared by:** Taskmaster
**For:** Cowork / Claude Code agents (DevOps / Cyber Ops / Foreman roles)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Sprint 2 Gate 5 without re-reading the entire project history. Read this file, then execute.

---

## 1. MISSION

Build the AI content pipeline and quick-publish UI for **The Markdown** — a WordPress.com editorial platform. Gate 5 adds AI-powered content reformatting, a quick-publish panel with platform toggles, and multi-platform dispatch. All code deploys as PHP snippets via REST API.

**Gate 5 scope:** 14 tasks (S2.9–S2.22), 7 FUNC + 7 SEC.

**Prerequisite:** Gate 4 (Social APIs) is COMPLETE. All 21 Gate 4 snippets are deployed and active (IDs 38-58). The social platform posting functions are live and callable.

---

## 2. PLATFORM — READ THIS FIRST

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — large payloads get blocked. Keep snippets <80 lines where possible. One function per snippet.
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
4. **Browser automation** — LAST RESORT. Only for visual QA.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Code

Every snippet must follow the micro-snippet pattern:

- Target <80 lines (comments count). Absolute ceiling ~150 lines for complex UI snippets.
- One function per snippet where practical.
- Clear naming: `S2-G5-M{nn} {Description}` for FUNC, `S2-G5-S{nn} {Description}` for SEC.
- Explicit dependency via `function_exists()` checks.
- Full docblock header with: sprint, gate, GitLab issue, dependencies, acceptance criteria.

### Snippet Template

```php
<?php
/**
 * S2.{N} — {Title}
 * Sprint 2, Gate 5 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S2-G5-M{nn} {Short Title}"
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

### Load Order

- Default priority: **10** (same as all Gate 4 snippets).
- Priority 1: NS Vault Key (ID 31). Priority 2: Diagnostic Logger (ID 37).
- **Do not change existing load order.**

---

## 4. EXISTING INFRASTRUCTURE — What You Can Call

### Layer 0-2 — Foundation + Core + Security (Sprint 1)

| Function | Snippet | What It Does |
|----------|---------|-------------|
| `NS_VAULT_KEY` | ID 31, priority 1 | AES-256 encryption key constant |
| `ns_diag_write($level, $component, $message, $context)` | ID 37, priority 2 | Structured diagnostic logging |
| `ns_log($message, $level)` | ID 9 (Core Utilities) | Forwards to ns_diag_write() |
| `ns_get_api_key($service)` | ID 9 (Core Utilities) | Retrieves decrypted key from vault |
| `ns_vault_store($service, $token)` | ID 30 (Token Vault) | Encrypt + store token |
| `ns_vault_retrieve($service)` | ID 30 (Token Vault) | Decrypt + return stored token |
| `ns_audit_log($action, $target_type, $target_id, $label, $details)` | ID 32 (Audit Log) | Write to audit DB table |
| `ns_call_claude_api($messages, $args)` | ID 21 (Claude API Caller) | Call Anthropic API with messages array |
| `ns_build_scoring_prompt($item)` | ID 24 (Prompt Builder) | Build Claude scoring prompt for a feed item |
| `ns_sanitize_rss_content($content)` | ID 14 (Sanitize Stubs) | Sanitize RSS content |
| `ns_sanitize_llm_input($text)` | ID 14 (Sanitize Stubs) | Sanitize text before sending to LLM |

### Layer 4 — Gate 4 Social API Functions (Sprint 2, just deployed)

**Posting Functions (call these from quick-publish dispatch):**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_twitter_post_tweet($text, $media_ids)` | 48 | Post single tweet |
| `ns_twitter_post_thread($items)` | 58 | Post threaded tweets |
| `ns_twitter_upload_media($image_url)` | 49 | Upload image, get media_id |
| `ns_twitter_count_chars($text)` | 48 | Count characters (280 limit) |
| `ns_linkedin_create_post($text, $image_url)` | 50 | Post to LinkedIn profile |
| `ns_youtube_create_community_post($text, $image_url)` | 51 | Post YouTube community post |
| `ns_medium_create_draft($title, $content, $tags)` | 52 | Create Medium draft |

**Token Functions:**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_twitter_get_token()` | 38 | Get current X access token |
| `ns_linkedin_get_token()` | 39 | Get current LinkedIn token |
| `ns_youtube_get_token()` | 40 | Get current YouTube token |

**Multi-Publish Helper:**

| Function | ID | What It Does |
|----------|-----|-------------|
| `ns_social_multi_publish($platforms)` | 57 | Dispatch to multiple platforms, isolate failures |
| `ns_social_api_error($platform, $response, $context)` | 57 | Centralized error handler |
| `ns_social_scrub_tokens($text)` | 57 | Remove tokens from text |

---

## 5. GATE 5 TASKS — AI + Quick-Publish

### Execution Order (dependency-resolved)

```
PHASE A — AI Pipeline (S2.9–S2.13):
  S2.9   AI Content Reformatter — 1 note → 5 platform versions
  S2.11  Arc Scoring — content vs thematic arc alignment
  S2.10  7-Block Daily Summary — 0800 CST standup
  S2.12  AI Output Validation (SEC) — sanitize Claude output
  S2.13  AI Response Length Enforcement (SEC) — prevent overflow

PHASE B — Quick-Publish UI (S2.14–S2.18):
  S2.17  REST API Endpoints — block data, feed items, publish actions
  S2.14  Quick-Publish Panel — inline editor + platform toggles
  S2.15  5-Version Preview — side-by-side platform preview
  S2.16  Publish Dispatch — one-click multi-platform post
  S2.18  RSS Feed Output — /feed/the-markdown

PHASE C — Security Hardening (S2.19–S2.22):
  S2.19  CSRF Protection on quick-publish
  S2.20  Content Escaping on social output — XSS prevention
  S2.21  Publish Audit Trail — log all dispatch events
  S2.22  Rate Limit Handler — exponential backoff + queue
```

---

### S2.9 — AI Content Reformatter

**GitLab Issue:** #38
**Owner:** Foreman
**Type:** FUNC
**Depends on:** S1.9 (Claude API Caller — ID 21)

**What to build:**
- Takes a single editorial note/article and reformats it into 5 platform-optimized versions
- Uses Claude API via `ns_call_claude_api()` to generate platform-specific content
- Output: array with keys `twitter`, `linkedin`, `youtube`, `medium`, `instagram`
- Twitter version respects 280-char limit (or thread-ready chunks)
- LinkedIn version: professional tone, 1300-char target
- YouTube version: community post format, casual
- Medium version: full article with markdown
- Instagram version: caption-ready, hashtag suggestions

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M01 | AI Content Reformatter | Core reformatter: `ns_reformat_for_platforms($content, $context)` | ~70 |
| S2-G5-M02 | Platform Prompt Templates | Per-platform prompt strings | ~60 |

**Acceptance Criteria:**
- ✅ Single input produces 5 platform versions
- ✅ Twitter output within 280 chars (or thread-split)
- ✅ All versions generated via Claude API
- ✅ Output sanitized before storage
- ✅ Logged via ns_audit_log() and ns_diag_write()

---

### S2.10 — 7-Block Daily Summary

**GitLab Issue:** #39
**Owner:** DevOps
**Type:** FUNC
**Depends on:** S1.12 (Dashboard), S1.14 (Page Template)

**What to build:**
- Daily WP-Cron job at 0800 CST
- Reads top-scored feed items from last 24h
- Generates a standup-style briefing with 7-block layout data
- Stores summary as post or transient for dashboard consumption
- Optional: email notification to admin with daily summary

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M03 | Daily Summary Generator | Cron job: compile 7-block summary at 0800 CST | ~75 |
| S2-G5-M04 | Summary Email Sender | Optional: email daily summary to admin | ~40 |

**Acceptance Criteria:**
- ✅ Cron fires at 0800 CST daily
- ✅ Summary includes top items from last 24h
- ✅ Data format compatible with 7-block page template
- ✅ Logged via diagnostic logger

---

### S2.11 — Arc Scoring

**GitLab Issue:** #40
**Owner:** DevOps
**Type:** FUNC
**Depends on:** S1.9 (Claude API)

**What to build:**
- Scoring function that evaluates content against thematic arcs
- Arcs are editorial themes (e.g., "AI governance", "startup culture") defined in config
- Claude analyzes content and scores alignment 1-10 per arc
- Results stored as post meta on `ns_feed_item`
- Admin can define/edit arcs in settings

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M05 | Arc Config Manager | CRUD for thematic arcs (wp_options storage) | ~60 |
| S2-G5-M06 | Arc Scorer | `ns_score_arc_alignment($item_id, $arcs)` via Claude API | ~70 |
| S2-G5-M07 | Arc Admin Settings | Admin page to manage arcs | ~60 |

**Acceptance Criteria:**
- ✅ Admin can define arcs with name + description
- ✅ Content scores against each defined arc (1-10)
- ✅ Scores stored as post meta
- ✅ Claude API used for scoring
- ✅ All operations logged

---

### S2.12 — AI Output Validation (SEC — Critical)

**GitLab Issue:** #41
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.9

**What to build:**
- Central validation function: `ns_validate_ai_output($output, $context)`
- Strip any HTML/script injection from Claude responses
- Validate JSON structure when expected
- Reject outputs that contain suspicious patterns (prompt injection, encoded scripts)
- Sanitize before storage or display

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S01 | AI Output Validator | `ns_validate_ai_output()` — central sanitization | ~70 |

**Acceptance Criteria:**
- ✅ All Claude API responses pass through validator before use
- ✅ HTML/script injection stripped
- ✅ Malformed JSON handled gracefully
- ✅ Suspicious patterns logged and rejected
- ✅ Zero unsafe content reaches storage or display

---

### S2.13 — AI Response Length Enforcement (SEC)

**GitLab Issue:** #42
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.9

**What to build:**
- Enforce max response length on Claude API calls
- Per-context limits: tweet (300 chars), LinkedIn (1500 chars), Medium (10000 chars), summary (5000 chars)
- Truncate gracefully (don't cut mid-word/mid-sentence)
- Log truncation events as warnings

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S02 | AI Length Enforcer | `ns_enforce_ai_length($text, $context)` | ~50 |

**Acceptance Criteria:**
- ✅ All AI output length-checked before use
- ✅ Graceful truncation (word boundary)
- ✅ Context-specific limits applied
- ✅ Truncation events logged as warnings

---

### S2.14 — Quick-Publish Panel

**GitLab Issue:** #43
**Owner:** DevOps
**Type:** FUNC
**Depends on:** Gate 4 social APIs (S2.1–S2.5)

**What to build:**
- Admin panel on the editorial dashboard (S1.12) for quick publishing
- Inline text editor with rich text support
- Platform toggle checkboxes (X, LinkedIn, YouTube, Medium)
- Character count per platform (shows limits)
- "Reformat with AI" button that calls S2.9
- "Publish" button that calls S2.16

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M08 | Quick-Publish Panel UI | Admin panel HTML/JS | ~75 (admin scope) |
| S2-G5-M09 | Quick-Publish AJAX Handler | Server-side AJAX for panel actions | ~70 |

**Acceptance Criteria:**
- ✅ Panel renders in admin dashboard
- ✅ Platform toggles control which platforms receive post
- ✅ Character counts update in real-time
- ✅ AI reformat button works
- ✅ Nonce-protected AJAX calls
- ✅ manage_options capability check

---

### S2.15 — 5-Version Preview

**GitLab Issue:** #44
**Owner:** DevOps
**Type:** FUNC
**Depends on:** S2.9, S2.14

**What to build:**
- Side-by-side preview showing all 5 platform versions
- Renders after AI reformatter runs
- Each version shows: platform icon, formatted text, character count, status
- "Edit" button per version for manual tweaks before publish

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M10 | Platform Preview Renderer | HTML/CSS for 5-version preview | ~75 (admin scope) |
| S2-G5-M11 | Preview AJAX Handler | Fetch/update individual platform versions | ~50 |

**Acceptance Criteria:**
- ✅ All 5 platform versions displayed side-by-side
- ✅ Character counts shown per platform
- ✅ Per-version edit capability
- ✅ Preview updates when AI reformatter runs

---

### S2.16 — Publish Dispatch

**GitLab Issue:** #45
**Owner:** Foreman
**Type:** FUNC
**Depends on:** S2.14

**What to build:**
- Dispatch engine: takes content + selected platforms, posts to each
- Uses `ns_social_multi_publish()` from Gate 4 (ID 57) for isolation
- Tracks success/failure per platform
- Returns consolidated result to UI
- Integrates with audit trail (S2.21)

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M12 | Publish Dispatcher | `ns_quick_publish($content, $platforms, $options)` | ~75 |

**Acceptance Criteria:**
- ✅ Dispatches to all selected platforms
- ✅ Platform failures isolated (one failure doesn't block others)
- ✅ Returns per-platform success/failure status
- ✅ All dispatches logged to audit trail
- ✅ No token leakage in any output

---

### S2.17 — REST API Endpoints

**GitLab Issue:** #46
**Owner:** Foreman
**Type:** FUNC
**Depends on:** S1.1 (Data Model)

**What to build:**
- `GET /ns/v1/blocks` — already exists, verify/extend
- `GET /ns/v1/feed-items` — custom filtered feed item list
- `POST /ns/v1/publish` — trigger publish dispatch via REST
- `GET /ns/v1/publish/status/{id}` — check publish job status
- All endpoints require Application Password auth

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M13 | Publish REST Routes | Register REST endpoints for publish actions | ~75 |
| S2-G5-M14 | Feed Items REST Extension | Extended feed item queries | ~60 |

**Acceptance Criteria:**
- ✅ All endpoints registered under `ns/v1`
- ✅ Auth required on write endpoints
- ✅ JSON responses with correct HTTP status codes
- ✅ Rate limited via existing S1.21 infrastructure
- ✅ All operations logged

---

### S2.18 — RSS Feed Output

**GitLab Issue:** #47
**Owner:** DevOps
**Type:** FUNC
**Depends on:** S1.14 (Page Template)

**What to build:**
- Custom RSS feed at `/feed/the-markdown`
- Outputs published/promoted feed items in RSS 2.0 format
- Includes: title, link, description, pubDate, category (domain)
- Feed limited to 20 most recent promoted items
- Custom feed title and description

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-M15 | RSS Feed Generator | Register + render `/feed/the-markdown` | ~70 |

**Acceptance Criteria:**
- ✅ Feed accessible at `/feed/the-markdown`
- ✅ Valid RSS 2.0 format
- ✅ Only promoted items included
- ✅ Proper XML escaping on all output
- ✅ 20-item limit

---

### S2.19 — CSRF Protection on Quick-Publish (SEC)

**GitLab Issue:** #48
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.14

**What to build:**
- Nonce generation on quick-publish panel load
- Nonce verification on all publish AJAX actions
- Separate nonce for publish dispatch vs preview vs reformat
- Note: S1.18 blanket nonce enforcement already catches missing nonces, but explicit nonces per action provide defense-in-depth

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S03 | Quick-Publish CSRF Guard | Per-action nonces for publish panel | ~55 |

**Acceptance Criteria:**
- ✅ Every publish action requires valid nonce
- ✅ Separate nonces per action type
- ✅ Nonce failures logged to audit log
- ✅ 403 response on nonce mismatch

---

### S2.20 — Content Escaping on Social Output (SEC)

**GitLab Issue:** #49
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.16

**What to build:**
- Platform-specific escaping function: `ns_escape_social_output($text, $platform)`
- Strip HTML for plain-text platforms (X, LinkedIn, YouTube)
- Preserve safe HTML for Medium (allow `<p>`, `<em>`, `<strong>`, `<a>`, `<h1>`-`<h6>`, `<ul>`, `<ol>`, `<li>`, `<code>`, `<pre>`, `<blockquote>`)
- Encode special characters per platform requirements
- Prevent XSS in all output

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S04 | Social Output Escaper | `ns_escape_social_output()` per-platform | ~65 |

**Acceptance Criteria:**
- ✅ All social output passes through escaper before dispatch
- ✅ HTML stripped for plain-text platforms
- ✅ Safe HTML preserved for Medium
- ✅ Zero XSS vectors in output
- ✅ Logged when content is modified by escaping

---

### S2.21 — Publish Audit Trail (SEC)

**GitLab Issue:** #50
**Owner:** Cyber Ops
**Type:** SEC
**Depends on:** S2.16

**What to build:**
- Log every publish dispatch event to `ns_audit_log()` with:
  - Platform, content hash, result (success/fail), timestamp, user
- Custom admin page to view publish history
- Filterable by platform, date, status

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S05 | Publish Audit Logger | Hook into dispatch, log all events | ~45 |
| S2-G5-S06 | Publish Audit Viewer | Admin page: publish history table | ~70 |

**Acceptance Criteria:**
- ✅ Every dispatch event logged (success and failure)
- ✅ Content hash stored (not full content, for privacy)
- ✅ Admin can view publish history with filters
- ✅ Integrates with existing ns_audit_log() infrastructure

---

### S2.22 — Rate Limit Handler (SEC)

**GitLab Issue:** #51
**Owner:** DevOps
**Type:** SEC
**Depends on:** Gate 4 social APIs

**What to build:**
- Per-platform rate tracking in transients
- Exponential backoff on 429 responses
- Queue system for deferred posts when rate limited
- WP-Cron job to retry queued posts
- Dashboard indicator when posts are queued

**Micro-snippet breakdown:**

| Snippet | Name | Purpose | Lines |
|---------|------|---------|-------|
| S2-G5-S07 | Platform Rate Tracker | Track API calls per platform, enforce limits | ~65 |
| S2-G5-S08 | Publish Queue Manager | Queue + retry deferred posts via cron | ~70 |

**Acceptance Criteria:**
- ✅ API calls tracked per platform per time window
- ✅ 429 responses trigger exponential backoff
- ✅ Queued posts retry automatically via cron
- ✅ Dashboard shows queue status
- ✅ All rate limit events logged

---

## 6. DEPLOYMENT PROCEDURE

### For Each Snippet:

```bash
# 1. Deploy as INACTIVE
curl -X POST "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets" \
  -u "h3ndriksj:APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "S2-G5-M01 AI Content Reformatter",
    "code": "<?php\n...",
    "priority": 10,
    "scope": "global",
    "active": false
  }'

# 2. Verify no syntax errors

# 3. Activate
curl -X PUT "https://justin-kuiper.com/wp-json/code-snippets/v1/snippets/{ID}" \
  -u "h3ndriksj:APP_PASSWORD" \
  -H "Content-Type: application/json" \
  -d '{"active": true}'

# 4. Health check
curl -u "h3ndriksj:APP_PASSWORD" \
  "https://justin-kuiper.com/wp-json/ns/v1/diagnostics/health"
# Expected: {"status":"healthy","errors_24h":0,...}
```

### Safe Activation Order for Gate 5:

```
1. AI Pipeline (no external dependencies except Claude API):
   S2-G5-M01 AI Content Reformatter
   S2-G5-M02 Platform Prompt Templates
   S2-G5-S01 AI Output Validator (SEC)
   S2-G5-S02 AI Length Enforcer (SEC)
   S2-G5-M05 Arc Config Manager
   S2-G5-M06 Arc Scorer
   S2-G5-M07 Arc Admin Settings
   S2-G5-M03 Daily Summary Generator
   S2-G5-M04 Summary Email Sender

2. REST + RSS (infrastructure):
   S2-G5-M13 Publish REST Routes
   S2-G5-M14 Feed Items REST Extension
   S2-G5-M15 RSS Feed Generator

3. Quick-Publish UI (depends on AI + REST):
   S2-G5-M08 Quick-Publish Panel UI
   S2-G5-M09 Quick-Publish AJAX Handler
   S2-G5-M10 Platform Preview Renderer
   S2-G5-M11 Preview AJAX Handler
   S2-G5-M12 Publish Dispatcher

4. Security Hardening (depends on UI + dispatch):
   S2-G5-S03 Quick-Publish CSRF Guard
   S2-G5-S04 Social Output Escaper
   S2-G5-S05 Publish Audit Logger
   S2-G5-S06 Publish Audit Viewer
   S2-G5-S07 Platform Rate Tracker
   S2-G5-S08 Publish Queue Manager
```

Health check between each activation.

---

## 7. SECURITY REQUIREMENTS — NON-NEGOTIABLE

1. **All tokens in Token Vault.** Use `ns_vault_store()` / `ns_vault_retrieve()`. Never wp_options directly.
2. **No tokens in logs.** Use `ns_social_scrub_tokens()` on all error output.
3. **Nonce on all admin AJAX.** Blanket enforcement (S1.18) catches missing nonces, but add them explicitly.
4. **All AI output through `ns_validate_ai_output()`.** (S2.12)
5. **All AI output length-checked.** (S2.13)
6. **All social output through `ns_escape_social_output()`.** (S2.20)
7. **HTTPS only.** All API calls must be HTTPS.
8. **Capability checks.** Only `manage_options` users can access publish panel.
9. **Audit everything.** Every publish dispatch, AI call, and error gets an `ns_audit_log()` entry.
10. **Diagnostic logging.** Every significant event gets an `ns_diag_write()` entry.

---

## 8. BLOCKERS & CONSTRAINTS

| ID | Blocker | Impact | Action |
|----|---------|--------|--------|
| BLK-001 | Facebook App Review | Instagram version in reformatter can be generated but not posted | Generate anyway, post when unblocked |
| CONSTRAINT | WordPress.com WAF | Large snippets blocked | Keep <80 lines target, <150 max |
| CONSTRAINT | Claude API cost | Each reformat = 1 API call | Cache reformatted versions, don't re-call for same content |
| CONSTRAINT | Platform rate limits | X: 200/15min, LinkedIn: 100/day, YouTube: 10k quota/day | S2.22 handles backoff + queue |

---

## 9. CREDENTIALS NEEDED AT SESSION START

Ask Yeti for these before doing anything:

1. **WordPress Application Password** — for REST API auth (snippet deploy, health checks)
2. **GitLab PAT** (optional) — `api` scope, for closing issues and committing code

Note: Platform API credentials (X, LinkedIn, YouTube, Medium) are already stored in Token Vault via Gate 4. No need to re-enter them.

---

## 10. DEFINITION OF DONE — PER TASK

A Gate 5 task is DONE when:

1. ✅ All micro-snippets deployed and active on production
2. ✅ Health check returns `"status":"healthy"` after activation
3. ✅ Functional test passes (e.g., AI reformat returns 5 versions, publish dispatches to platforms)
4. ✅ Security checks pass (nonces verified, output escaped, tokens scrubbed)
5. ✅ All actions captured in audit log
6. ✅ Diagnostic logger shows no errors from new snippets
7. ✅ Snippet code committed to GitLab (if PAT provided)

---

## 11. GATE 5 EXIT CRITERIA

Gate 5 is ready for review when:

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
- [ ] All snippets deployed and active
- [ ] Health check clean (0 errors)
- [ ] Integration test: reformat content → preview 5 versions → publish to 3+ platforms → verify audit trail

---

## 12. FILE REFERENCES

| File | When to Read |
|------|-------------|
| `Operational_Playbook.md` | Full platform details, tool hierarchy, snippet inventory |
| `Sprint_2_Code_Task_Prompt.md` | Gate 4 prompt (reference for patterns used) |
| `Sprint_Tracker.md` | Live task status |
| `Decision_Register.md` | All 23 project decisions |
| `Blocker_Register.md` | Active blockers |
| `snippets/sprint2-gate4/` | Gate 4 source code (reference implementations) |

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
- **Dashboard updates:** After completing tasks, update `Sprint_Tracker.md`.
- **Transcript:** Append to `Transcripts/YYYY-MM-DD_session.md`.

---

## 14. RECOMMENDED START

Fastest path to first value:

1. **Ask Yeti for WordPress Application Password**
2. **Start with S2.12 + S2.13 (AI Security)** — security-first, these must wrap all AI calls
3. **Then S2.9 (AI Reformatter)** — core value: 1 note → 5 versions
4. **Then S2.17 (REST endpoints)** — infrastructure for UI
5. **Then S2.14 + S2.15 (Quick-Publish Panel + Preview)** — the UI
6. **Then S2.16 (Publish Dispatch)** — wires UI to Gate 4 posting functions
7. **Then S2.11 (Arc Scoring) + S2.10 (Daily Summary)** — AI enhancements
8. **Then S2.18 (RSS Feed)** — subscriber feed
9. **Last: S2.19–S2.22 (Security hardening)** — CSRF, escaping, audit, rate limits

Total estimated: ~26 snippets across 14 tasks.

---

*Prepared by Taskmaster — 2026-03-09. Gate 4 verified deployed. Standing by for Gate 5 tasking.*
