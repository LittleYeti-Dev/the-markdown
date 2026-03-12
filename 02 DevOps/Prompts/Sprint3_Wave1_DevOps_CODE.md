# Sprint 3 — Wave 1 DevOps Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 1 (Independent Tasks — No Blockers)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 1 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — GET YOUR CREDENTIALS

**⚠️ Before touching ANY endpoint or running ANY code, ask Yeti for:**

1. **WordPress Application Password** — required for all authenticated REST API calls
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.
   - Assume it rotates within hours.

2. **GitLab PAT** — `api` scope, for closing issues and committing results
   - Assume any PAT is short-lived. Yeti rotates multiple times daily.

3. **Claude API Key** (if not yet stored) — for Token Vault storage
   - If Yeti provides one, store it via the Token Vault snippet (S1.17) using `ns_vault_store('claude_api_key', $key)`
   - The 4-hour scoring cron (S1.9) reads from `ns_get_api_key('claude')`

**Do not proceed past this section until you have the Application Password.**

---

## 1. MISSION

Execute the DevOps-owned Wave 1 tasks for Sprint 3. These are **3 new WordPress snippets** + **1 database backup configuration**. All tasks are independent — zero inter-dependencies within this wave.

**Your tasks (4 total):**

| ID | Task | Type | GitLab Issue |
|----|------|------|-------------|
| S3.5 | Build feed health monitor — detect broken feeds + alerts | FUNC | #56 |
| S3.11 | Database backup automation — daily encrypted backups | SEC | #62 |
| CF-3 | RSS auto-discovery snippet — `<link rel="alternate">` in site head | FUNC | — |
| CF-1 | Verify permalink flush (Yeti action) — confirm `/feed/the-markdown` responds | OPS | — |

**Not your tasks (other owners handle these):**
- S3.1, S3.6, S3.8 → Taskmaster
- S3.9, S3.10, S3.12 → Cyber Ops

---

## 2. PLATFORM — READ THIS FIRST

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — large payloads get blocked. Keep snippets <80 lines target, ~150 ceiling for complex ones.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`
- **Auth:** HTTP Basic — username `h3ndriksj` + Application Password (ask Yeti at session start).
- **Custom namespace:** `ns/v1`
- **Custom Post Type:** `ns_feed_item` (rest_base: `feed-items`)
- **Current active snippets:** 74 (of 85 total)
- **RSS feed:** `/?feed=the-markdown` (pretty URL `/feed/the-markdown` needs permalink flush by Yeti)

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — fastest, most reliable. Use for deploying snippets, reading data, checking health.
2. **GitLab REST API** — for issue tracking, commits. PAT with `api` scope from Yeti.
3. **Admin AJAX** — for nonce-protected admin actions only.
4. **Browser automation** — LAST RESORT. Only for visual QA or actions with no REST equivalent.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3-W1-M{nn} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, wave, GitLab issue, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.{N} — {Title}
 * Sprint 3, Wave 1 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S3-W1 {Short Title}"
 * Scope: {global | admin | front-end}
 * Priority: 10
 * Depends on: {list snippet dependencies or "none"}
 *
 * Acceptance Criteria (GitLab #{NN}):
 *   ✅ {criterion 1}
 *   ✅ {criterion 2}
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// === Implementation ===
```

### Deployment Steps (per snippet)

1. **Write the snippet** — follow template above
2. **Deploy via REST API** — `POST /wp-json/code-snippets/v1/snippets`
   ```
   { "name": "S3-W1 {Title}", "code": "{PHP}", "scope": "global", "priority": 10, "active": true }
   ```
3. **Verify deployment** — `GET /wp-json/code-snippets/v1/snippets` and confirm snippet is active with 0 errors
4. **Test functionality** — hit the relevant endpoint or page to confirm the snippet works
5. **Close GitLab issue** (if applicable)

---

## 4. TASK DETAILS

### Task 1: CF-3 — RSS Auto-Discovery Snippet

**Purpose:** Add `<link rel="alternate" type="application/rss+xml">` to the site `<head>` so RSS readers and browsers can auto-discover The Markdown's feed.

**Implementation:**

```php
<?php
/**
 * CF-3 — RSS Auto-Discovery Link
 * Sprint 3, Wave 1 | Carry-forward from S2.5 Retro
 *
 * Adds RSS feed auto-discovery <link> tag to site <head>.
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S3-W1 RSS Auto-Discovery"
 * Scope: front-end
 * Priority: 10
 * Depends on: S2.18 RSS Feed Output (snippet that registers the feed)
 *
 * Acceptance Criteria:
 *   ✅ <link rel="alternate" type="application/rss+xml"> appears in page source
 *   ✅ href points to /feed/the-markdown (or /?feed=the-markdown if permalink not flushed)
 *   ✅ title attribute reads "The Markdown RSS Feed"
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', function() {
    $feed_url = home_url( '/feed/the-markdown' );
    printf(
        '<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />' . "\n",
        esc_attr( 'The Markdown RSS Feed' ),
        esc_url( $feed_url )
    );
}, 5 );
```

**Verification:**
- View page source of `justin-kuiper.com` — confirm `<link rel="alternate">` tag is present
- Validate the `href` URL is reachable

**Done criteria:** Tag appears in `<head>`, feed URL is correct.

---

### Task 2: S3.5 — Feed Health Monitor

**Purpose:** Monitor all configured RSS feeds for failures (HTTP errors, timeouts, parse errors) and send admin email alerts when a feed goes unhealthy.

**GitLab Issue:** #56

**Implementation approach:**
- Register a daily WP-Cron event (or hook into existing 4h cron cycle)
- Iterate over all feed sources configured in WP RSS Aggregator
- For each feed: attempt `wp_remote_get()` with 10s timeout
- Check HTTP status code (2xx = healthy, anything else = unhealthy)
- Validate RSS/XML parse with `simplexml_load_string()`
- Store health status in a transient or custom option
- If any feed is unhealthy: send admin email with feed URL, error code, and timestamp
- Expose health status via existing `/ns/v1/diagnostics/health` endpoint (add a `feeds` section)

**Snippet structure:** This may need 2 Lego blocks:
1. **S3-W1 Feed Health Check** — the cron function that checks feeds (~60-80 lines)
2. **S3-W1 Feed Health REST** — extends `/ns/v1/diagnostics/health` to include feed status (~30-40 lines)

**Acceptance criteria:**
- ✅ Daily cron checks all configured feeds
- ✅ Unhealthy feeds trigger admin email notification
- ✅ Health status visible in `/ns/v1/diagnostics/health` response
- ✅ Healthy feeds do NOT trigger alerts
- ✅ Timeout handling — feeds that take >10s are flagged

**Constraints:**
- WordPress.com — use `wp_remote_get()`, not `curl`
- Keep each snippet under 80 lines target
- Use `wp_mail()` for notifications (WordPress.com supports it)
- Store health data in transient (24h TTL) to avoid repeated alerts for known-broken feeds

---

### Task 3: S3.11 — Database Backup Automation

**Purpose:** Configure automated daily encrypted backups of the WordPress database.

**GitLab Issue:** #62

**Implementation notes — WordPress.com constraint:**
- WordPress.com manages its own server-side backups. You do NOT have direct DB access or `mysqldump`.
- **Approach options:**
  - **Option A (Recommended):** Use WordPress.com's built-in backup tools (Jetpack Backup / VaultPress if available on the plan). Verify backup is active and configure email notifications for backup completion/failure. Document the backup schedule and retention policy.
  - **Option B:** Build a snippet that exports critical custom data (CPT `ns_feed_item`, custom options, snippet inventory) via REST API to a structured JSON format, then stores/emails it as a lightweight application-level backup.
  - **Option C:** If neither A nor B is viable, document the limitation and recommend a manual export procedure.

**The agent should:**
1. Check if Jetpack Backup / VaultPress is active on the site
2. If yes → verify schedule, enable notifications, document in a results file
3. If no → implement Option B (REST-based export snippet)
4. Document the chosen approach and any limitations

**Acceptance criteria:**
- ✅ Backup mechanism is active (automated, not manual)
- ✅ Backup covers custom data (CPT, options, snippets)
- ✅ Failure notification exists (email alert if backup fails)
- ✅ Approach documented with rationale

---

### Task 4: CF-1 — Verify Permalink Flush

**Purpose:** Confirm that Yeti has flushed permalinks and `/feed/the-markdown` returns a valid RSS response.

**This is a Yeti action + agent verification:**
1. Remind Yeti: "Please go to WordPress admin → Settings → Permalinks → click Save. This activates the `/feed/the-markdown` pretty URL."
2. After Yeti confirms: `GET https://justin-kuiper.com/feed/the-markdown`
3. Verify response is valid RSS 2.0 XML (not a 404)
4. If 404 persists: fall back to `/?feed=the-markdown` and document

**Done criteria:** Pretty URL returns RSS XML, or fallback documented.

---

## 5. EXECUTION ORDER

```
1. CF-3 (RSS auto-discovery)     — smallest, fastest, deploy + verify in 5 min
2. CF-1 (permalink verification) — ask Yeti, then verify — 2 min
3. S3.5 (feed health monitor)    — main code task — ~2h
4. S3.11 (backup automation)     — investigation + implementation — ~1.5h
```

---

## 6. OUTPUT FILES

After completing all tasks, produce a results file:

**File:** `02 DevOps/S3_Wave1_DevOps_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- Snippet IDs deployed (with names)
- Any issues encountered
- Recommendations for Wave 2

---

## 7. DONE CRITERIA — Wave 1 DevOps

Wave 1 DevOps is DONE when:
- [ ] CF-3: RSS auto-discovery `<link>` tag appears in page source
- [ ] CF-1: `/feed/the-markdown` returns valid RSS (or fallback documented)
- [ ] S3.5: Feed health monitor cron active, at least one check completed, health visible in `/ns/v1/diagnostics/health`
- [ ] S3.11: Backup mechanism documented and active
- [ ] All snippets deployed via REST API with 0 errors
- [ ] Results file committed to GitLab
- [ ] GitLab issues #56 and #62 closed (or updated with status)

---

## 8. CONSTRAINTS & REMINDERS

- **REST API first** — do not open a browser unless REST genuinely can't do it
- **Lego blocks** — <80 lines target, ~150 ceiling, one responsibility per snippet
- **WordPress.com** — no SFTP, no wp-config.php, no mysqldump, WAF active
- **Credential discipline** — hold credentials in memory, never log them, never commit them
- **GitLab sync at session end** — this is a gate exit criterion, not optional
- **State your tool choice** at the start of every task with reasoning

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 1 is unlocked. Execute in order, verify each task, push results.*
