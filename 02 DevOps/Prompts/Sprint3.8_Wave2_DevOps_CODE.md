# Sprint 3.8 — Wave 2 DevOps Execution Prompt (CODE)

**Version:** 1.1
**Date:** 2026-03-12
**Sprint:** Sprint 3.8 — Editorial Approval Workflow
**Wave:** Wave 2 (Pipeline + UI — Requires Wave 1 Complete)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 2 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Wave 1 Must Be Complete

**Do NOT start Wave 2 until Wave 1 is deployed and verified.** Wave 2 builds the editorial pipeline on top of the board data model, shortcode patch, and hero image pipeline that Wave 1 establishes.

Verify by checking:
- `02 DevOps/QA/S3.8_Wave1_DevOps_Results.md` — must exist and show all 3 tasks PASS
- S121 (Board Data Model) is active — test: call `jk_get_draft_board()` via a quick REST endpoint or eval snippet
- S126 (Shortcode Patch) is active — editorial page renders normally (fallback mode)
- S127 (Hero Image Pipeline) is active — feed items have `_jk_hero_image` meta

If Wave 1 is not complete, stop and escalate to Yeti.

### Verify Current State

Before writing any code:

1. `GET /wp-json/code-snippets/v1/snippets?per_page=5&orderby=id&order=desc` — confirm API access works
2. Visit `https://justin-kuiper.com/the-markdown/` — confirm the editorial page loads
3. `GET /wp-json/wp/v2/feed-items?per_page=3&_fields=id,title,meta` — confirm feed items are queryable
4. Verify S121 helpers exist: deploy a quick test snippet that calls `jk_get_draft_board()` and returns the result via REST
5. Read `01 Scrum Master/Sprints/Sprint_3.8_Task_List.md` — the full sprint plan with slot addressing scheme

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

Build the editorial approval pipeline: the AI draft builder, the admin approval UI, the email digest, and the preference learning engine. This is the human-in-the-loop layer that sits between AI curation and the live page.

**Your tasks (4 total):**

| ID | Task | Snippet(s) | Priority |
|----|------|-----------|----------|
| S3.8.2 | Draft Builder (Cron + On-Demand) | S122 | P1 — Critical (generates the board) |
| S3.8.3 | Admin Approval UI | S123a, S123b | P1 — Critical (Yeti's control panel) |
| S3.8.4 | Email Digest Notification | S124 | P2 — High (daily notification) |
| S3.8.5 | Preference Logger & Learning Model | S125 | P2 — High (AI learns from editorial decisions) |

**Not your tasks (Wave 1 — already deployed):**
- S3.8.1 Board Data Model (S121) → Wave 1 ✅
- S3.8.6 Editorial Shortcode Patch (S126) → Wave 1 ✅
- S3.8.7 Hero Image Pipeline (S127) → Wave 1 ✅

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
3. **Browser automation** — for visual verification of admin UI.
4. **Admin AJAX** — for approval UI AJAX handlers.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3.8 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3.8-W2-{NN} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, wave, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.8-W2-{NN} — {Title}
 * Sprint 3.8, Wave 2 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S{NNN} S3.8-W2-{NN} {Short Title}"
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
4. **Test functionally** — call the functions / verify behavior
5. **Test visually** — confirm the editorial page still renders (no regression)

---

## 4. SLOT ADDRESSING SCHEME (Reference)

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

### Task 1: S3.8.2 — Draft Builder (S122)

**Snippet ID:** S122
**Snippet Title:** `S122 S3.8-W2-01 Draft Builder`
**Scope:** global
**Priority:** 10
**Depends on:** S121 (Board Data Model), S127 (Hero Image Pipeline)
**Est. lines:** ~120–150 (may split into S122a + S122b if over ceiling)

**What to build:**

An automated draft builder that proposes a full 19-slot editorial board by scoring and ranking feed items. Runs on WP-Cron daily at 0800 ET and on-demand via admin button or REST endpoint.

**Execution flow:**

```
1. Query all scored feed items from last 48 hours
2. Load preference weights from S125 (if available — graceful fallback if not)
3. Load topic interests from admin settings (see Topic Steering below)
4. Apply preference adjustments to base scores
5. Rank items by adjusted score
6. Assign top items to 19 slots:
   - Box 00: highest-scoring item with hero_image available
   - Boxes 01–06: next 18 items, distributed 3 per block
7. Build candidate pool: next 15 items NOT assigned to slots (deep bench for rejections)
8. Write full board to jk_editorial_draft via jk_save_draft_board()
9. Log generation metadata
10. Trigger email digest (S124) if this was a cron run
```

**Slot Assignment Logic:**

The draft builder fills all 19 slots to produce a complete board. Distribution strategy:

- **Box 00 (lead):** Highest adjusted score + must have `_jk_hero_image`. If top item has no hero, scan down until one is found.
- **Boxes 01–06:** Fill sequentially. For each block, assign the next 3 highest-scoring unassigned items. Optionally bias by content domain (space items toward Box 05, cyber toward Box 02, etc.) — use the domain→block mapping from the existing shortcode.

**Candidate Pool (Critical Feature):**

The candidate pool is NOT just "nice to have" — it's essential for the rejection workflow.

When Yeti rejects an item from the board in the Admin UI (S3.8.3), the empty slot must be auto-filled from the candidate pool. The pool must be deep enough to handle multiple rejections.

```
Board:          19 items assigned to slots
Candidate Pool: 15 items ranked by adjusted score, ready to fill any slot
Total scored:   34+ items considered per draft
```

The candidate pool is stored as part of the draft board JSON (see S121 schema — `candidate_pool` array).

**Auto-replacement on rejection:**
When an item is removed from a slot, the highest-scoring candidate from the pool fills the vacancy. The replaced item moves to a `rejected` list (for preference logging). The pool shrinks by one. If the pool is empty, the slot stays vacant and a warning badge appears in the Admin UI.

**Topic Steering (Active Editorial Direction):**

In addition to passive preference learning (S3.8.5), Yeti wants to actively steer the AI toward topics of interest. This is a simple admin setting:

```php
// Stored as wp_option: jk_editorial_topic_interests
$topic_interests = array(
    array(
        'keywords' => array( 'breach', 'data breach', 'lessons learned', 'incident response' ),
        'weight'   => 1.5,  // 50% score boost
        'label'    => 'Breaches & Lessons Learned',
    ),
    array(
        'keywords' => array( 'ev', 'evtol', 'electric vehicle', 'flying car', 'air taxi' ),
        'weight'   => 1.3,  // 30% score boost
        'label'    => 'EV / EVTOL',
    ),
    array(
        'keywords' => array( 'innovation', 'breakthrough', 'disruption', 'emerging tech' ),
        'weight'   => 1.2,  // 20% score boost
        'label'    => 'Innovation',
    ),
);
```

**How topic steering works:**
1. Before ranking, check each item's title + excerpt against topic interest keywords
2. If a keyword match is found, multiply the item's base score by the weight
3. This pushes matching items up the ranking, making them more likely to land on the board
4. Yeti can add/edit/remove topic interests from the Admin UI (S3.8.3 — stretch section)

The draft builder's Claude API prompt (if using Claude for scoring) should also include topic interests as editorial context:
```
"Editorial direction: Yeti is currently interested in: breaches & lessons learned (high priority), EV/EVTOL space (medium), innovation stories (medium). Weight these topics accordingly."
```

**Cron Setup:**

```php
// Register the cron event
if ( ! wp_next_scheduled( 'jk_editorial_build_draft' ) ) {
    // Schedule for 0800 ET (12:00 UTC during EDT, 13:00 UTC during EST)
    $next_run = strtotime( 'tomorrow 12:00:00 UTC' ); // Adjust for ET
    wp_schedule_event( $next_run, 'daily', 'jk_editorial_build_draft' );
}
add_action( 'jk_editorial_build_draft', 'jk_build_editorial_draft' );
```

**On-demand trigger:** Also register a REST endpoint for manual triggering:
```php
register_rest_route( 'ns/v1', '/editorial/build-draft', array(
    'methods'             => 'POST',
    'callback'            => 'jk_build_editorial_draft',
    'permission_callback' => function() {
        return current_user_can( 'manage_options' );
    },
));
```

**Logging:**
Store generation metadata in the board JSON:
```json
{
  "generated": "2026-03-13T08:00:00-04:00",
  "items_considered": 87,
  "items_with_hero": 42,
  "preference_adjustments": 12,
  "topic_boosts_applied": 5,
  "trigger": "cron"
}
```

**Acceptance Criteria:**
- ✅ `jk_build_editorial_draft()` produces a valid 19-slot board with candidate pool
- ✅ Box 00 always has an item with `_jk_hero_image` (or board flags a warning)
- ✅ Candidate pool contains 15 items ranked by adjusted score
- ✅ Topic interest keywords boost matching items' scores
- ✅ Preference weights from S125 are applied (graceful fallback if S125 not yet active)
- ✅ WP-Cron fires daily at ~0800 ET
- ✅ `POST /wp-json/ns/v1/editorial/build-draft` triggers on-demand build (admin only)
- ✅ Generation metadata is logged in the board JSON
- ✅ No errors on activation, no conflicts with existing snippets

---

### Task 2: S3.8.3 — Admin Approval UI (S123a + S123b)

**Snippet IDs:** S123a (UI Render) + S123b (AJAX Handlers)
**Snippet Titles:**
- `S123a S3.8-W2-02a Admin Approval UI`
- `S123b S3.8-W2-02b Admin Approval AJAX`
**Scope:** admin
**Priority:** 10
**Depends on:** S121 (Board Data Model), S122 (Draft Builder)
**Est. lines:** S123a ~120–150, S123b ~100–130

**Why two snippets:** The admin UI is the largest piece in this sprint. Split into:
- **S123a** — PHP/HTML rendering: admin page registration, board display, candidate pool display
- **S123b** — AJAX handlers: approve, reject, reorder, swap, publish, refresh, topic interest management

**What to build:**

A WordPress admin page under The Markdown menu that displays the current draft board and lets Yeti review, reorder, reject, and approve before publishing.

**Admin Page Registration:**

```php
add_action( 'admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=ns_feed_item', // Parent menu (or use a custom top-level)
        'Editorial Board',
        'Editorial Board',
        'manage_options',
        'jk-editorial-board',
        'jk_render_editorial_board_page'
    );
});
```

**UI Layout:**

```
┌─────────────────────────────────────────────────────────────┐
│  EDITORIAL BOARD — Draft generated [timestamp]              │
│  [Refresh Draft] [Publish Board] [Clear Board]              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  BOX 00 — LEAD STORY                                       │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ [00] "James Webb discovers..." — space.com          │   │
│  │      Score: 94 | Hero: ✅ | AI: "High relevance..." │   │
│  │      [Move ▼] [Reject ✕] [Share: 𝕏 | in]           │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  BOX 01                          BOX 02                     │
│  ┌────────────────────────┐     ┌────────────────────────┐ │
│  │ [010] "AI headline..."  │     │ [020] "Cyber story..." │ │
│  │ [011] "Second story..." │     │ [021] "Breach news..." │ │
│  │ [012] "Third story..."  │     │ [022] "Threat intel..."│ │
│  │ [Move] [Reject] [Share] │     │ [Move] [Reject] [Share]│ │
│  └────────────────────────┘     └────────────────────────┘ │
│  ... (Boxes 03–06 same pattern)                             │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  CANDIDATE POOL (15 items)                                  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ "Story A" — source.com | Score: 78 | [Add to slot ▼]│   │
│  │ "Story B" — source.com | Score: 76 | [Add to slot ▼]│   │
│  │ ... (up to 15 candidates)                            │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  REJECTED THIS SESSION (feedback for preference learning)   │
│  "Rejected story C" — was slot 011 | "Rejected story D"    │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  TOPIC INTERESTS (Active Editorial Direction)               │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Breaches & Lessons Learned  [1.5x] [Edit] [Remove]  │   │
│  │ EV / EVTOL                  [1.3x] [Edit] [Remove]  │   │
│  │ Innovation                  [1.2x] [Edit] [Remove]  │   │
│  │ [+ Add Topic Interest]                               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  PUBLISHED BOARD STATUS                                     │
│  Last published: [timestamp] | Items: 19/19 | Stale: No    │
│  Visual diff: 3 items changed since last publish ⚠         │
└─────────────────────────────────────────────────────────────┘
```

**Per-Item Display:**
- Slot ID (e.g., `010`) — displayed prominently
- Story title (linked to source article)
- Source domain
- AI score (base + adjusted)
- AI reasoning (1 line)
- Hero image status: ✅ (has image), ⚠ (no image — blocks publish for Box 00)
- Action buttons:
  - **Move** — dropdown to select a new slot (e.g., "Move to 00", "Move to 031")
  - **Reject** — removes from board, auto-fills from candidate pool, logs for preference learning
  - **Share** — per-item social share icons (see Social Sharing below)

**Social Sharing Per Item (DR-0031: All shares drive traffic to The Markdown):**

Each item card gets a small share icon row. These are simple web intent URLs — no API calls needed. **All share links point back to The Markdown page, NOT to source articles.** This is a deliberate ecosystem decision: every social share funnels traffic to Yeti's property.

```php
// Share URL generators — ALL links point back to The Markdown with block anchor
$markdown_url = 'https://justin-kuiper.com/the-markdown/';

// Build the share URL for a given item
function jk_get_share_urls( $item_title, $block_id ) {
    $page_url = 'https://justin-kuiper.com/the-markdown/#box-' . $block_id;
    $text     = $item_title . ' — The Markdown';

    return array(
        'x'        => 'https://twitter.com/intent/tweet?url=' . urlencode( $page_url ) . '&text=' . urlencode( $text ),
        'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $page_url ) . '&title=' . urlencode( $text ),
        'instagram' => 'https://www.instagram.com/',
        // NOTE: Instagram has no intent/share URL like X or LinkedIn.
        // Button opens IG web app. Yeti creates a story/post manually and
        // pastes The Markdown URL. If Yeti has a Meta Business Suite account,
        // swap this to: 'https://business.facebook.com/latest/home'
        // for desktop posting with link support.
    );
}
```

When Yeti clicks the X icon on item `020` ("Major healthcare breach"), the browser opens:
```
https://twitter.com/intent/tweet?url=https://justin-kuiper.com/the-markdown/%23box-02&text=Major+healthcare+breach+exposes+2M+records+%E2%80%94+The+Markdown
```

Twitter's web UI loads with the post pre-filled. Yeti logs in (or is already logged in), hits post. The tweet links back to The Markdown page anchored to Box 02. Visitors land on the curated board, not on the source article.

**Same pattern for LinkedIn.** Instagram button also present on every card — opens IG web app for Yeti to post manually (IG has no intent URL). All three buttons (X, LinkedIn, Instagram) appear in the Admin Approval UI on every item card during the review/approve flow. Yeti promotes or rejects items AND distributes them to social — all from the same screen. That's the human-in-the-loop: curation + distribution in one workflow.

**For the front-end rendering** (Box cards on the live page), add the same share icons to each card so site visitors can also share items. This is a minor patch to the existing card renderer (M06 from S3.5-W2). Create a small supplemental snippet if needed. The front-end share buttons use the same `jk_get_share_urls()` helper — all traffic flows back to The Markdown.

**The existing site-level PUSH buttons** (snippets 108–118) remain unchanged — they share The Markdown page itself. The per-item buttons share specific blocks/stories within the page.

**Rejection + Auto-Fill Flow:**

This is critical — the board must stay full after rejections.

```
1. Yeti clicks [Reject] on item in slot 011
2. AJAX call: POST /wp-admin/admin-ajax.php
   action: jk_editorial_reject_item
   slot: "011"
   feed_item_id: 1235
3. Server:
   a. Remove item 1235 from slot 011
   b. Add item 1235 to rejected[] list with metadata:
      { "feed_item_id": 1235, "was_slot": "011", "signal": "rejected" }
   c. Pop highest-scoring item from candidate_pool
   d. Insert it into slot 011
   e. If candidate_pool is empty → leave slot vacant + add warning
   f. Return updated board JSON
4. UI re-renders Box 01 with the replacement item in slot 011
5. Badge shows: "Replaced: [old title] → [new title]"
```

**Important:** The candidate pool must be deep enough. Require minimum 15 candidates in the pool. If the draft builder can't find 15 candidates beyond the 19 board items, log a warning but proceed.

**Publish Flow:**

```
1. Yeti clicks [Publish Board]
2. Validation:
   a. Box 00 must have hero_image → block if missing
   b. All 19 slots should be filled (warn if any vacant, allow publish anyway)
3. Call jk_publish_board() from S121
4. Trigger preference logger (S125) to capture the delta
5. Show success message with timestamp
6. Editorial page now renders from published board
```

**Refresh Draft:** Re-runs `jk_build_editorial_draft()` (on-demand trigger). Replaces current draft. Shows confirmation.

**Clear Board:** Calls `jk_clear_board()`. Page falls back to live query. Shows confirmation.

**Topic Interests Section:**

A simple CRUD panel at the bottom of the admin page:

- **Display:** List of current topic interests with label, keywords, and weight multiplier
- **Add:** Form with: label (text), keywords (comma-separated), weight (slider or number input: 1.0–2.0)
- **Edit:** Inline edit on each topic interest row
- **Remove:** Delete button per row
- **Storage:** `wp_option: jk_editorial_topic_interests` (JSON array)

These feed into the draft builder (S122) during the scoring phase.

**Mobile-Friendly:**

Yeti may approve from his phone. Key requirements:
- Single-column layout on mobile
- Large touch targets on [Reject] and [Publish] buttons
- Candidate pool collapsible/expandable on mobile
- Slot IDs visible and readable at small sizes

**AJAX Handlers (S123b):**

Register these AJAX actions:

```php
// All handlers require manage_options capability + nonce verification

add_action( 'wp_ajax_jk_editorial_reject_item', 'jk_handle_reject_item' );
// Reject item from slot → auto-fill from candidate pool → return updated board

add_action( 'wp_ajax_jk_editorial_move_item', 'jk_handle_move_item' );
// Move item from one slot to another → return updated board

add_action( 'wp_ajax_jk_editorial_swap_candidate', 'jk_handle_swap_candidate' );
// Replace a board item with a specific candidate pool item

add_action( 'wp_ajax_jk_editorial_publish_board', 'jk_handle_publish_board' );
// Validate → publish → trigger preference logger → return result

add_action( 'wp_ajax_jk_editorial_refresh_draft', 'jk_handle_refresh_draft' );
// Re-run draft builder → return new board

add_action( 'wp_ajax_jk_editorial_clear_board', 'jk_handle_clear_board' );
// Clear published board → return result

add_action( 'wp_ajax_jk_editorial_save_topic_interests', 'jk_handle_save_topic_interests' );
// Save topic interest settings
```

**Every AJAX handler must:**
1. Verify nonce (`check_ajax_referer`)
2. Check `current_user_can('manage_options')`
3. Return `wp_send_json_success()` or `wp_send_json_error()`
4. Log the action for the preference logger

**Acceptance Criteria:**
- ✅ Admin page renders under The Markdown menu in WP Admin
- ✅ Draft board displays all 19 slots with item details
- ✅ Each item shows: slot ID, title, source, score, AI reasoning, hero status
- ✅ [Reject] removes item and auto-fills from candidate pool
- ✅ [Move] allows repositioning items between slots
- ✅ [Publish Board] validates (Box 00 hero check) and publishes
- ✅ [Refresh Draft] re-runs draft builder
- ✅ [Clear Board] removes published board (page falls back to live query)
- ✅ Candidate pool displays with [Add to slot] dropdown
- ✅ Rejected items tracked in a session list (for preference logging)
- ✅ Topic Interests section: add, edit, remove topic interest entries
- ✅ Per-item share icons (X, LinkedIn, Instagram) present on every item card
- ✅ X and LinkedIn generate intent URLs pointing to The Markdown page with block anchor (NOT source articles)
- ✅ Instagram button opens IG web app (no intent URL available — Yeti posts manually with credentials)
- ✅ Visual diff highlights items that differ from current published board
- ✅ Mobile-friendly: single column, large touch targets at ≤768px
- ✅ All AJAX handlers verify nonce + capabilities
- ✅ No errors on activation, no conflicts with existing snippets

---

### Task 3: S3.8.4 — Email Digest Notification (S124)

**Snippet ID:** S124
**Snippet Title:** `S124 S3.8-W2-03 Email Digest`
**Scope:** global
**Priority:** 10
**Depends on:** S122 (Draft Builder)
**Est. lines:** ~80–100

**What to build:**

After the draft builder completes a scheduled (cron) run, send an email digest to Yeti summarizing the proposed board.

**Trigger:** Called at the end of `jk_build_editorial_draft()` when `$trigger === 'cron'` (not on on-demand builds).

**Recipient:** `h3ndriks.j@gmail.com` (configurable via `wp_option: jk_editorial_digest_email`)

**Email Format:**

```
Subject: [The Markdown] Daily Board — Mar 13, 2026 — 34 candidates scored

Body (HTML or plain-text):

THE MARKDOWN — EDITORIAL BOARD PROPOSAL
Generated: Mar 13, 2026 at 08:00 AM ET
Items scored: 34 | Board slots: 19/19 | Candidate pool: 15

─── BOX 00 — LEAD ───────────────────────────
[00] "James Webb discovers high-redshift galaxy"
     space.com | Score: 94 | Hero: ✅
     AI: "High relevance, trending topic, matches space lead preference"

─── BOX 01 ──────────────────────────────────
[010] "GPT-5 benchmark results leaked"
      techcrunch.com | Score: 91
[011] "AI regulation bill passes committee"
      reuters.com | Score: 87
[012] "New open-source LLM beats Claude on math"
      arxiv.org | Score: 84

─── BOX 02 ──────────────────────────────────
[020] "Major healthcare breach: 2M records exposed"
      krebsonsecurity.com | Score: 89 | ⭐ TOPIC BOOST: Breaches
[021] "CISA releases new ICS advisory"
      cisa.gov | Score: 82
[022] "Ransomware group claims utility attack"
      bleepingcomputer.com | Score: 79

... (Boxes 03–06 same format)

─── CANDIDATE POOL (top 5 of 15) ────────────
"Story X" — source.com | Score: 77
"Story Y" — source.com | Score: 75
... (+10 more)

─── TOPIC BOOSTS APPLIED ────────────────────
• "Major healthcare breach..." +50% (Breaches & Lessons Learned)
• "EV startup raises $2B..." +30% (EV / EVTOL)

─────────────────────────────────────────────
REVIEW & APPROVE:
https://justin-kuiper.com/wp-admin/admin.php?page=jk-editorial-board

Or reply "approve" to publish as-is.
(Email reply parsing is a stretch goal — primary workflow is the admin UI.)
```

**Implementation:**

```php
function jk_send_editorial_digest( $board ) {
    $to = get_option( 'jk_editorial_digest_email', 'h3ndriks.j@gmail.com' );
    $subject = sprintf(
        '[The Markdown] Daily Board — %s — %d candidates',
        date( 'M j, Y' ),
        $board['items_considered'] ?? 0
    );

    // Build the email body from board data
    $body = jk_format_digest_body( $board );

    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    wp_mail( $to, $subject, $body, $headers );
}
```

**Important notes:**
- Uses `wp_mail()` — no external service dependency
- Test email deliverability. If emails land in spam, document the issue (Gmail filters may be aggressive on WP.com-originated emails)
- Email is the SECONDARY notification. The Admin UI is the PRIMARY workflow. If email fails, board review still works.

**Acceptance Criteria:**
- ✅ Email fires after scheduled (cron) draft builder run
- ✅ Email does NOT fire on on-demand/manual draft builds
- ✅ Subject line includes date and candidate count
- ✅ Body shows all 19 slots with title, source, score
- ✅ Body includes link to Admin Approval UI
- ✅ Topic boosts are flagged in the email (⭐ marker)
- ✅ Candidate pool summary included (top 5 + count of remaining)
- ✅ Uses `wp_mail()` — no external dependencies
- ✅ Recipient email is configurable via wp_option

---

### Task 4: S3.8.5 — Preference Logger & Learning Model (S125)

**Snippet ID:** S125
**Snippet Title:** `S125 S3.8-W2-04 Preference Logger`
**Scope:** global
**Priority:** 10
**Depends on:** S121 (Board Data Model), S123b (AJAX Handlers)
**Est. lines:** ~100–130

**What to build:**

A preference learning engine that captures the delta between what the AI proposed and what Yeti actually published. Over time, this data trains the draft builder to better match Yeti's editorial taste.

**When it fires:** On every "Publish Board" action (called from S123b's publish handler).

**What it captures:**

```json
{
  "date": "2026-03-13",
  "session_id": "2026-03-13-0823",
  "approval_time_seconds": 120,
  "approved_as_is": false,
  "moves": [
    {
      "feed_item_id": 1234,
      "proposed_slot": "020",
      "final_slot": "00",
      "signal": "promoted",
      "title": "Major breach exposes 2M records",
      "source": "krebsonsecurity.com",
      "domain": "cyber"
    },
    {
      "feed_item_id": 1237,
      "proposed_slot": "010",
      "final_slot": null,
      "signal": "rejected",
      "title": "Boring AI story nobody cares about",
      "source": "mediocre-blog.com",
      "domain": "ai"
    },
    {
      "feed_item_id": 1240,
      "proposed_slot": null,
      "final_slot": "010",
      "signal": "pulled_from_pool",
      "title": "EVTOL startup gets FAA approval",
      "source": "theverge.com",
      "domain": "innovation"
    }
  ],
  "topic_boosts_active": [
    {"label": "Breaches & Lessons Learned", "weight": 1.5},
    {"label": "EV / EVTOL", "weight": 1.3}
  ]
}
```

**Signal types:**
- `promoted` — item moved to a higher-priority slot (e.g., from 020 to 00)
- `demoted` — item moved to a lower-priority slot
- `rejected` — item removed from board entirely
- `pulled_from_pool` — item added from candidate pool to fill a vacancy
- `kept` — item stayed in its proposed slot (no change)
- `approved_as_is` — entire board published without changes (strong positive signal)

**Storage:**

```php
// Individual session logs stored as wp_option array
// Key: jk_editorial_pref_log
// Value: array of session entries (rolling 90-day window)

// Computed preference weights stored separately
// Key: jk_editorial_pref_weights
// Value: computed weights object
```

**Preference Weight Computation:**

From the rolling log of the last 30 days, compute:

```php
function jk_compute_preference_weights() {
    $log = get_option( 'jk_editorial_pref_log', array() );
    $cutoff = strtotime( '-30 days' );

    // Filter to last 30 days
    $recent = array_filter( $log, function( $entry ) use ( $cutoff ) {
        return strtotime( $entry['date'] ) >= $cutoff;
    });

    $weights = array(
        'source_preferences'  => array(),  // source.com → +0.3 (promoted often)
        'domain_preferences'  => array(),  // cyber → lead 60% of the time
        'promoted_keywords'   => array(),  // "breach" appears in 5 promoted items
        'rejected_keywords'   => array(),  // "opinion" appears in 3 rejected items
        'approval_rate'       => 0.0,      // % of boards approved as-is
        'avg_changes'         => 0.0,      // average moves per session
    );

    // Analyze each session's moves to build preference signals
    foreach ( $recent as $session ) {
        foreach ( $session['moves'] as $move ) {
            // Source preferences
            if ( $move['signal'] === 'promoted' ) {
                $weights['source_preferences'][ $move['source'] ] =
                    ( $weights['source_preferences'][ $move['source'] ] ?? 0 ) + 1;
            }
            if ( $move['signal'] === 'rejected' ) {
                $weights['source_preferences'][ $move['source'] ] =
                    ( $weights['source_preferences'][ $move['source'] ] ?? 0 ) - 1;
            }

            // Domain preferences for lead slot
            if ( $move['final_slot'] === '00' ) {
                $weights['domain_preferences'][ $move['domain'] ] =
                    ( $weights['domain_preferences'][ $move['domain'] ] ?? 0 ) + 1;
            }

            // Keyword extraction from promoted/rejected titles
            // Simple word tokenization — not NLP, just frequency counting
            $words = array_unique( explode( ' ', strtolower( $move['title'] ) ) );
            $target = ( $move['signal'] === 'promoted' || $move['signal'] === 'pulled_from_pool' )
                ? 'promoted_keywords' : 'rejected_keywords';
            if ( in_array( $move['signal'], array( 'promoted', 'pulled_from_pool', 'rejected' ) ) ) {
                foreach ( $words as $w ) {
                    if ( strlen( $w ) > 3 ) {  // Skip short words
                        $weights[ $target ][ $w ] = ( $weights[ $target ][ $w ] ?? 0 ) + 1;
                    }
                }
            }
        }

        // Approval rate
        if ( $session['approved_as_is'] ) {
            $weights['approval_rate'] += 1;
        }
        $weights['avg_changes'] += count( $session['moves'] );
    }

    $session_count = max( count( $recent ), 1 );
    $weights['approval_rate'] = round( $weights['approval_rate'] / $session_count, 2 );
    $weights['avg_changes'] = round( $weights['avg_changes'] / $session_count, 1 );

    // Save computed weights
    update_option( 'jk_editorial_pref_weights', $weights );

    return $weights;
}
```

**How the Draft Builder Uses Preference Weights:**

The draft builder (S122) calls `jk_get_preference_weights()` and adjusts scores:

```
For each feed item:
  adjusted_score = base_score
  + source_preference_bonus (if source has positive history)
  - source_preference_penalty (if source has negative history)
  + topic_interest_boost (from active topic steering)
  + promoted_keyword_bonus (if title contains frequently-promoted words)
  - rejected_keyword_penalty (if title contains frequently-rejected words)
```

**The goal:** Over 30+ approval cycles, the AI's proposals converge toward Yeti's taste. The `approval_rate` metric tracks this — climbing approval rate = model is learning. If approval rate drops, check if Yeti's interests have shifted (new topic interests should be added).

**Preference Reset:**

Add a "Reset Preferences" button in the Admin UI (S3.8.3 — topic interests section) that:
- Clears `jk_editorial_pref_log`
- Clears `jk_editorial_pref_weights`
- Keeps `jk_editorial_topic_interests` (active steering stays)
- Logs the reset event

This prevents the model from getting stuck in a local optimum (Risk Register item from S3.8 task list).

**Acceptance Criteria:**
- ✅ On every "Publish Board," the delta between AI proposal and Yeti's final board is logged
- ✅ Each move is logged with: item ID, proposed slot, final slot, signal type, title, source, domain
- ✅ `approved_as_is` flag is set when Yeti publishes without changes
- ✅ Rolling 30-day preference weights are computed after each session
- ✅ Source preferences: sources that are consistently promoted/rejected get score adjustments
- ✅ Domain preferences: domains that frequently appear in lead position get lead-slot bias
- ✅ Keyword signals: frequently promoted/rejected title words influence scoring
- ✅ `approval_rate` metric tracks convergence (available in Admin UI dashboard)
- ✅ Preference weights are retrievable by the draft builder via `jk_get_preference_weights()`
- ✅ "Reset Preferences" clears logs and weights but keeps topic interests
- ✅ Preference data stored in wp_options (rolling 90-day log, 30-day computation window)
- ✅ No errors on activation, no conflicts with existing snippets

---

## 6. DEPLOYMENT ORDER

Deploy in this order — each snippet depends on the previous:

```
S122 (Draft Builder)  →  S125 (Preference Logger)  →  S123a (Admin UI)  →  S123b (AJAX Handlers)  →  S124 (Email Digest)
```

**Rationale:**
1. S122 first — generates the draft board that everything else reads
2. S125 second — the preference logger needs to exist before the admin UI calls it
3. S123a third — renders the admin page (reads from S122's output, depends on S125 for stats)
4. S123b fourth — AJAX handlers (calls S125 on publish, calls S122 on refresh)
5. S124 last — email digest hooks into S122's cron path, which is already working

After each deploy:
1. Verify snippet is active via API
2. Test the specific functionality (see verification checklist)
3. Verify editorial page still loads without errors (regression check)

---

## 7. VERIFICATION CHECKLIST

After all snippets are deployed:

**Draft Builder (S122):**
- [ ] Manually trigger `POST /wp-json/ns/v1/editorial/build-draft`
- [ ] `jk_get_draft_board()` returns a board with 19 items + candidate pool
- [ ] Box 00 item has `hero_image` set
- [ ] Candidate pool has ≥10 items
- [ ] Generation metadata present (timestamp, items_considered, trigger)

**Admin UI (S123a + S123b):**
- [ ] Admin page loads at `/wp-admin/admin.php?page=jk-editorial-board`
- [ ] Draft board displays all 19 slots with correct data
- [ ] [Reject] removes an item and auto-fills from candidate pool
- [ ] [Move] repositions an item to a different slot
- [ ] [Publish Board] validates Box 00 hero and publishes
- [ ] [Refresh Draft] re-runs draft builder
- [ ] [Clear Board] removes published board
- [ ] Topic interests section: can add, edit, remove topic interests
- [ ] Per-item share icons generate correct URLs
- [ ] Page is usable on mobile (test at 375px width)

**Email Digest (S124):**
- [ ] Trigger a cron-mode draft build — email arrives at h3ndriks.j@gmail.com
- [ ] Email shows all 19 slots with titles, sources, scores
- [ ] Email includes link to admin UI
- [ ] No email sent on on-demand builds

**Preference Logger (S125):**
- [ ] Reject 2 items and publish — preference log captures the moves
- [ ] `jk_compute_preference_weights()` returns a weights object
- [ ] Weights include source_preferences, domain_preferences, promoted_keywords
- [ ] "Reset Preferences" clears the log and weights

**End-to-End Flow:**
- [ ] Draft builder generates a board (manual trigger)
- [ ] Admin UI shows the draft
- [ ] Reject 1 item → candidate auto-fills the slot
- [ ] Move 1 item to a different slot
- [ ] Publish the board
- [ ] Editorial page at `/the-markdown/` renders from published board data
- [ ] Preference log captures all moves with correct signals
- [ ] Clear the board → page falls back to live query (no regression)

---

## 8. WHAT NOT TO DO

- **Don't rewrite snippets 108–118.** The existing editorial rendering pipeline is working. S126 (Wave 1) handles the board→shortcode bridge.
- **Don't modify S121 (Board Data Model).** If you need additional helpers, create a supplemental snippet.
- **Don't build a full drag-and-drop UI.** Simple dropdowns and buttons are fine. Drag-and-drop is a future enhancement.
- **Don't parse email replies.** The "reply approve" feature is a stretch goal, not Wave 2 scope.
- **Don't use external email services.** `wp_mail()` only.
- **Don't create GitLab issues yet** — Taskmaster will create them.
- **Don't over-engineer the preference model.** Simple frequency counting is correct for v1. ML/NLP is a future sprint.

---

## 9. RESULTS DOCUMENTATION

After completing all tasks, create:

**`02 DevOps/QA/S3.8_Wave2_DevOps_Results.md`**

Contents:
- Snippet IDs deployed (API IDs + names)
- Test results for each acceptance criterion
- Screenshots of the Admin Approval UI (desktop + mobile)
- Email digest screenshot or content dump
- Preference logger output from test cycle
- End-to-end flow test results
- Any issues encountered and how they were resolved
- Recommendations for Wave 3 (if any)

---

## 10. ESCALATION

If you encounter any of these, stop and escalate to Yeti:

- API returns 401 or 403 on Code Snippets endpoints
- WP-Cron doesn't fire (test with a simple scheduled event first)
- S121 helper functions are not available (Wave 1 not deployed)
- WAF blocks a snippet deployment (split into smaller snippets)
- `wp_mail()` fails silently (check return value, test with a simple mail first)
- Any data loss risk
- Feed item schema has changed since Wave 1

---

## 11. KEY DEPENDENCIES MAP

```
S121 (Board Data Model) ─── Wave 1 ✅
  │
  ├──→ S122 (Draft Builder) ← reads/writes board, reads preference weights
  │      │
  │      ├──→ S124 (Email Digest) ← fires after cron-triggered draft build
  │      │
  │      └──→ S123a (Admin UI) ← displays draft board
  │             │
  │             └──→ S123b (AJAX Handlers) ← reject/move/publish/refresh
  │                    │
  │                    └──→ S125 (Preference Logger) ← captures publish delta
  │                           │
  │                           └──→ S122 (Draft Builder) ← uses computed weights
  │                                  (circular — weights feed back into next build)
  │
  ├──→ S126 (Shortcode Patch) ─── Wave 1 ✅
  │
  └──→ S127 (Hero Image Pipeline) ─── Wave 1 ✅
```

---

*Prepared by Taskmaster 2026-03-12. Sprint 3.8 Wave 2 is BLOCKED until Wave 1 sign-off. Do not start early.*
