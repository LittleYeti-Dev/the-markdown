# Sprint 3.8 — Wave 1 DevOps Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-12
**Sprint:** Sprint 3.8 — Editorial Approval Workflow
**Wave:** Wave 1 (Foundation — No Dependencies)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 1 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### No Prior Wave Required

Wave 1 is the foundation build. It depends only on:
- Sprint 3.5/3.6 editorial page being live (CONFIRMED)
- API access restored with correct credentials (CONFIRMED)

### Verify Current State

Before writing any code:

1. `GET /wp-json/code-snippets/v1/snippets?per_page=5&orderby=id&order=desc` — confirm API access works
2. Visit `https://justin-kuiper.com/the-markdown/` — confirm the editorial page loads
3. `GET /wp-json/wp/v2/feed-items?per_page=3` — confirm feed items are queryable
4. Read `01 Scrum Master/Sprints/Sprint_3.8_Task_List.md` — the full sprint plan with slot addressing scheme

### Credentials

**Ask Yeti for:**

1. **WordPress Application Password** — for all REST API calls
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

2. **GitLab PAT** — `api` scope, for issue management
   - Project ID: `80070684`

**Do not proceed until you have both credentials.**

---

## 1. MISSION

Build the data foundation for the editorial approval workflow. Three snippets that establish the board data model, patch the existing editorial shortcode to read from it, and add hero image scraping to the feed pipeline.

**Your tasks (3 total):**

| ID | Task | Snippet | Priority |
|----|------|---------|----------|
| S3.8.1 | Board Data Model + Helper Functions | S121 | P1 — Critical (everything depends on this) |
| S3.8.6 | Editorial Shortcode Patch | S126 | P1 — Critical (connects board to live page) |
| S3.8.7 | Hero Image Pipeline | S127 | P2 — High (required before Wave 2 approval UI) |

**Not your tasks (Wave 2):**
- S3.8.2 Draft Builder → Wave 2
- S3.8.3 Admin Approval UI → Wave 2
- S3.8.4 Email Digest → Wave 2
- S3.8.5 Preference Logger → Wave 2

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — keep snippets <80 lines target, ~150 ceiling. Large payloads will be blocked. Split into multiple snippets.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`
- **Auth:** HTTP Basic — username `h3ndriksj` + Application Password.
- **Custom namespace:** `ns/v1`
- **Custom Post Type:** `ns_feed_item` (rest_base: `feed-items`)
- **Content domain taxonomy:** `content_domain`

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — for deploying snippets, querying data.
2. **GitLab REST API** — for issue management.
3. **Browser automation** — for visual verification.
4. **Admin AJAX** — last resort only.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3.8 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3.8-W1-{NN} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, wave, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.8-W1-{NN} — {Title}
 * Sprint 3.8, Wave 1 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S121 S3.8-W1-01 {Short Title}"
 * Scope: {global | admin | front-end}
 * Priority: 10
 * Depends on: {list snippet dependencies or "none"}
 *
 * Acceptance Criteria:
 *   ✅ {criterion 1}
 *   ✅ {criterion 2}
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// === Implementation ===
```

### Deployment Steps (per snippet)

1. **Write the snippet** — follow template above
2. **Deploy via REST API** — `POST /wp-json/code-snippets/v1/snippets`
3. **Verify deployment** — `GET /wp-json/code-snippets/v1/snippets` and confirm snippet is active
4. **Test functionally** — call the helper functions / verify data structures
5. **Test visually** — confirm the editorial page still renders (no regression)

---

## 4. SLOT ADDRESSING SCHEME

This is the core data model for the editorial board. Memorize this.

```
Box 00 — LEAD    →  slot "00"  (single story + mandatory hero image)
Box 01           →  slots "010", "011", "012"  (max 3 items)
Box 02           →  slots "020", "021", "022"  (max 3 items)
Box 03           →  slots "030", "031", "032"  (max 3 items)
Box 04           →  slots "040", "041", "042"  (max 3 items)
Box 05           →  slots "050", "051", "052"  (max 3 items)
Box 06           →  slots "060", "061", "062"  (max 3 items)

Total: 19 slots (1 + 6×3)
```

First two digits = block ID. Third digit (for blocks 01–06) = position within block.
`00` = the lead. `031` = Box 03, second position.

---

## 5. TASK DETAILS

### Task 1: S3.8.1 — Board Data Model + Helper Functions (S121)

**Snippet ID:** S121
**Snippet Title:** `S121 S3.8-W1-01 Board Data Model`
**Scope:** global
**Priority:** 10
**Depends on:** None
**Est. lines:** ~60–80

**What to build:**

A set of helper functions that manage the editorial board state. The board uses WordPress options (`wp_options`) to store two layers:

1. **Draft board** (`jk_editorial_draft`) — AI-proposed layout, not yet approved
2. **Published board** (`jk_editorial_published`) — Yeti-approved layout, rendered on the live page

**Data Schema:**

```json
{
  "generated": "2026-03-13T08:00:00-04:00",
  "published_at": null,
  "version": 1,
  "blocks": {
    "00": [
      {
        "slot": "00",
        "feed_item_id": 1234,
        "title": "James Webb discovers high-redshift galaxy",
        "source": "space.com",
        "domain": "space",
        "score": 94,
        "hero_image": "https://cdn.space.com/og-image.jpg",
        "reason": "High relevance score, trending topic, matches Yeti preference for space lead stories"
      }
    ],
    "01": [
      {
        "slot": "010",
        "feed_item_id": 1235,
        "title": "...",
        "source": "...",
        "domain": "...",
        "score": 88,
        "hero_image": null,
        "reason": "..."
      },
      {
        "slot": "011",
        "feed_item_id": 1236,
        ...
      }
    ],
    ...
  },
  "candidate_pool": [
    {"feed_item_id": 1240, "title": "...", "source": "...", "score": 78, "reason": "Alternate — strong but not top 19"}
  ],
  "preference_context": "Based on last 30 days: Yeti promotes space/cyber to lead 70% of the time..."
}
```

**Helper Functions to Implement:**

```php
// Get the current draft board (returns array or null)
function jk_get_draft_board() { ... }

// Get the current published board (returns array or null)
function jk_get_published_board() { ... }

// Save a draft board
function jk_save_draft_board( $board ) { ... }

// Publish the current draft (copies draft → published, sets published_at timestamp)
function jk_publish_board() { ... }

// Get a specific slot from the published board
// e.g., jk_get_slot('00') returns the lead story array
// e.g., jk_get_slot('01') returns array of up to 3 items for Box 01
function jk_get_slot( $block_id ) { ... }

// Get all items for a block from published board (convenience wrapper)
function jk_get_block_items( $block_id ) { ... }

// Check if published board is stale (older than $hours, default 24)
function jk_board_is_stale( $hours = 24 ) { ... }

// Clear the published board (page falls back to live query)
function jk_clear_board() { ... }

// Get board metadata (generated time, version, whether draft differs from published)
function jk_board_meta() { ... }
```

**Validation rules:**
- Block `00` accepts exactly 1 item
- Blocks `01`–`06` accept 0–3 items each
- `jk_publish_board()` must verify Box 00 has a `hero_image` before allowing publish (return WP_Error if missing)
- All feed_item_ids must reference existing `ns_feed_item` posts

**Acceptance Criteria:**
- ✅ `jk_get_draft_board()` returns null when no draft exists
- ✅ `jk_save_draft_board($board)` saves and `jk_get_draft_board()` retrieves it
- ✅ `jk_publish_board()` copies draft to published with timestamp
- ✅ `jk_publish_board()` rejects if Box 00 has no hero_image (returns WP_Error)
- ✅ `jk_get_slot('00')` returns the lead story
- ✅ `jk_get_slot('03')` returns array of up to 3 items
- ✅ `jk_board_is_stale()` returns true if published board is >24h old
- ✅ No errors on activation, no conflicts with existing snippets

---

### Task 2: S3.8.6 — Editorial Shortcode Patch (S126)

**Snippet ID:** S126
**Snippet Title:** `S126 S3.8-W1-02 Editorial Shortcode Patch`
**Scope:** global
**Priority:** 10
**Depends on:** S121 (Board Data Model)
**Est. lines:** ~40–60

**What to build:**

Modify the existing `[the_markdown_editorial]` shortcode rendering pipeline to read from the published board when available.

**Current behavior:** Snippets 108–118 query `ns_feed_item` posts live via WP_Query, organized by `domain_tag` post meta and recency.

**New behavior:**
1. Check if a published board exists and is not stale (<24h old) via `jk_get_published_board()` and `jk_board_is_stale()`
2. **If board exists and is fresh:** render blocks from board data. Each block pulls items from `jk_get_block_items($block_id)` instead of running WP_Query
3. **If no board or stale:** fall back to current live query behavior. Zero regression.

**Implementation approach:**

This is a **filter/hook patch**, not a rewrite of snippets 108–118. The cleanest approach:

- Add a high-priority filter or early return check in the data query snippet (likely S3.5-W2-M02 or equivalent) that checks for a published board first
- If board data exists, short-circuit the WP_Query and return the board's feed item IDs instead
- The rendering snippets (M03–M08) continue to work as-is — they just receive feed item data from the board instead of from a fresh query

**Key constraint:** The existing rendering snippets should NOT be modified. Only the data source changes. This keeps the blast radius minimal.

**Acceptance Criteria:**
- ✅ When no published board exists → page renders exactly as before (live query). No visual change.
- ✅ When a published board exists and is fresh → page renders from board data
- ✅ Items display in slot order within each block (slot 0 first, then 1, then 2)
- ✅ Box 00 shows the hero image from board data instead of the placeholder
- ✅ If board goes stale (>24h), page automatically falls back to live query
- ✅ `function_exists('jk_get_published_board')` check — if S121 isn't active, shortcode falls back gracefully

---

### Task 3: S3.8.7 — Hero Image Pipeline (S127)

**Snippet ID:** S127
**Snippet Title:** `S127 S3.8-W1-03 Hero Image Pipeline`
**Scope:** global
**Priority:** 10
**Depends on:** None (operates on feed item ingest pipeline)
**Est. lines:** ~80–100

**What to build:**

A pipeline that scrapes Open Graph hero images from source articles and attaches them to feed items as post meta.

**When it runs:**
- On feed item save/update (`save_post_ns_feed_item` hook)
- Also callable on-demand via a helper function for the draft builder (Wave 2)

**Implementation:**

```php
/**
 * For a given feed item ID, fetch the source URL, scrape og:image,
 * and store it as post meta.
 */
function jk_scrape_hero_image( $feed_item_id ) {
    // 1. Get the source URL from feed item meta (the original article URL)
    $source_url = get_post_meta( $feed_item_id, 'source_url', true );
    // (or whatever meta key stores the original article URL — check existing schema)

    if ( empty( $source_url ) ) return false;

    // 2. Fetch the page (wp_remote_get with 5s timeout)
    $response = wp_remote_get( $source_url, array(
        'timeout' => 5,
        'user-agent' => 'JK-Editorial-Bot/1.0',
    ));

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        update_post_meta( $feed_item_id, '_jk_hero_status', 'failed' );
        return false;
    }

    // 3. Parse og:image from HTML
    $body = wp_remote_retrieve_body( $response );
    // Use regex or DOMDocument to extract <meta property="og:image" content="...">

    // 4. Store result
    if ( $og_image ) {
        update_post_meta( $feed_item_id, '_jk_hero_image', esc_url( $og_image ) );
        update_post_meta( $feed_item_id, '_jk_hero_status', 'scraped' );
        return $og_image;
    } else {
        update_post_meta( $feed_item_id, '_jk_hero_status', 'no_og_image' );
        return false;
    }
}
```

**Post meta keys:**
- `_jk_hero_image` — URL of the scraped OG image (or manually uploaded image URL)
- `_jk_hero_status` — one of: `scraped`, `no_og_image`, `failed`, `manual`, `ai_generated`

**Behavior on `save_post_ns_feed_item`:**
- Only run on feed items that don't already have `_jk_hero_image` set (don't re-scrape)
- Don't run on autosaves or revisions
- Run async if possible (wp_schedule_single_event) to avoid slowing down ingest. If WP-Cron is unreliable on WP.com, run inline with a short timeout.

**Important: Check the existing feed item schema first.**
Before writing any code, query a few feed items to see what meta keys exist:
```
GET /wp-json/wp/v2/feed-items?per_page=3&_fields=id,title,meta
```
Find the correct meta key for the source article URL. It may be `source_url`, `feed_item_url`, `link`, or something else. Adapt the code to match the actual schema.

**Acceptance Criteria:**
- ✅ `jk_scrape_hero_image($id)` fetches and stores OG image for a feed item
- ✅ `_jk_hero_image` meta is set on successful scrape
- ✅ `_jk_hero_status` meta tracks the state (scraped/failed/no_og_image)
- ✅ Doesn't re-scrape items that already have a hero image
- ✅ Handles timeouts and missing OG tags gracefully (no fatal errors)
- ✅ Works with the existing `ns_feed_item` CPT — uses correct meta keys from actual schema
- ✅ Can be called standalone (for Wave 2 draft builder to use)

---

## 6. DEPLOYMENT ORDER

Deploy in this order — each snippet depends on the previous:

```
S121 (Board Data Model)  →  S126 (Shortcode Patch)  →  S127 (Hero Image Pipeline)
```

After each deploy:
1. Verify snippet is active via API
2. Test helper functions work (use REST API or browser console)
3. Verify editorial page still loads without errors (regression check)

---

## 7. VERIFICATION CHECKLIST

After all three snippets are deployed:

- [ ] `jk_get_draft_board()` returns null (no draft yet — expected)
- [ ] `jk_get_published_board()` returns null (no board yet — expected)
- [ ] Editorial page loads normally (fallback to live query — no regression)
- [ ] Query a few feed items — `_jk_hero_image` meta should start appearing on new ingests
- [ ] Manually test `jk_scrape_hero_image()` on a feed item that has a source URL with an OG image
- [ ] Manually test `jk_save_draft_board()` with a test board, verify `jk_get_draft_board()` returns it
- [ ] Manually test `jk_publish_board()` — editorial page should now render from board data
- [ ] `jk_clear_board()` — page should fall back to live query again

---

## 8. WHAT NOT TO DO

- **Don't rewrite snippets 108–118.** The existing editorial rendering pipeline is working. Only patch the data source.
- **Don't build the admin UI.** That's Wave 2 (S3.8.3).
- **Don't build the cron job or draft builder.** That's Wave 2 (S3.8.2).
- **Don't build the email digest.** That's Wave 2 (S3.8.4).
- **Don't add learning/preference logic.** That's Wave 2 (S3.8.5).
- **Don't create GitLab issues yet** — Taskmaster will create them.
- **Don't modify the nav menu.** That was fixed in S3.7 (BF-08).

---

## 9. RESULTS DOCUMENTATION

After completing all tasks, create:

**`02 DevOps/QA/S3.8_Wave1_DevOps_Results.md`**

Contents:
- Snippet IDs deployed (API IDs + names)
- Test results for each acceptance criterion
- Any schema discoveries (feed item meta keys, etc.)
- Screenshots of the editorial page (before/after — should be identical since no board is published yet)
- Any issues encountered and how they were resolved
- Recommendations for Wave 2

---

## 10. ESCALATION

If you encounter any of these, stop and escalate to Yeti:

- API returns 401 or 403 on Code Snippets endpoints
- Feed item CPT schema doesn't match expectations (missing meta keys)
- Existing snippets 108–118 use a pattern that can't be cleanly patched
- WAF blocks a snippet deployment
- Any data loss risk
