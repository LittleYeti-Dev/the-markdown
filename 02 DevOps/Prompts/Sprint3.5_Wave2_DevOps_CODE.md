# Sprint 3.5 — Wave 2 DevOps Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-11
**Sprint:** Sprint 3.5 — Stabilization & Markdown Foundation
**Wave:** Wave 2 (Dependent — Requires Wave 1 Complete)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 2 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Wave 1 Must Be Complete

**Do NOT start Wave 2 until Wave 1 is signed off.** Wave 2 builds the Markdown editorial page on top of the fixed, stable baseline that Wave 1 establishes.

Verify by checking:
- `02 DevOps/S3.5_Wave1_DevOps_Results.md` — must exist and show all 7 tasks PASS
- All 7 Wave 1 GitLab issues (#82–#88) must be closed

If Wave 1 is not complete, stop and escalate to Yeti.

### Prototype Reference

The prototype for The Markdown editorial page is live at:

**`https://jk-com-ver02-5480e0.gitlab.io`**

This is the GitLab Pages deploy of the design mock (last deployed: 2026-03-10). Before writing any code, visit the prototype in a browser and study the layout. The prototype is the source of truth for all visual design decisions in this task.

Key prototype sections to study:
- Masthead (THE MARKDOWN wordmark + tagline + live time)
- Edition banner (YEAR ONE — REINVENTION)
- Domain navigation (AI | CYBER | INNOVATION | FNW | SPACE | DIGITAL LIFE)
- Block 00 — Full-width hero lead story
- Status bar (47 ITEMS INGESTED TODAY • 7 BLOCKS FILLED • NEXT REFRESH 0900)
- Blocks 01–06 — Three-column content grid
- PUSH buttons (X | LINKEDIN | INSTAGRAM | MEDIUM | YOUTUBE)
- Justin Kuiper commentary cards (per block)
- Footer

Also read: `01 Scrum Master/S3_ProtoVsProd_QA_Results.md` for the full prototype-vs-production gap analysis.

### Credentials

**Ask Yeti for:**

1. **WordPress Application Password** — for all REST API calls
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

2. **GitLab PAT** — `api` scope, for closing issues
   - Project ID: `80070684`

**Do not proceed until you have both credentials.**

---

## 1. MISSION

Execute the single Wave 2 task: build The Markdown editorial page in production. This is Sprint 3.5's primary build task and the core deliverable of the sprint.

**Your task (1 total):**

| ID | Task | GitLab Issue | Priority |
|----|------|-------------|----------|
| S3.5-MP01 | Build The Markdown editorial page — Block 00–06 layout | #89 | P1 — Critical |

**Not your tasks:**
- S3.5-QA01 (#90) → Taskmaster/Cowork (Wave 2 parallel or post-Wave 2)

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
- **Content domain taxonomy:** `content_domain` (used for domain filtering/labeling)

### Current Production State

- `/the-markdown/` currently resolves to RSS feed XML (empty channel). This URL must be repurposed as the editorial page.
- The archive page (`/archive/`) is a separate page and should remain unchanged.
- The personal bio homepage (`/`) is separate and should remain unchanged.
- Wave 1 (BF02) will have fixed the RSS feed to actually contain items at `/feed/the-markdown/`.

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — for deploying snippets, creating the page, querying data.
2. **GitLab REST API** — for issue management.
3. **Browser automation** — required for visual verification of the layout against the prototype.
4. **Admin AJAX** — last resort only.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3.5 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3.5-W2-M{nn} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, wave, GitLab issue, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.5-MP01 — {Title}
 * Sprint 3.5, Wave 2 | GitLab Issue #89
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S3.5-W2-M{NN} {Short Title}"
 * Scope: {global | admin | front-end}
 * Priority: 10
 * Depends on: {list snippet dependencies or "none"}
 *
 * Acceptance Criteria (GitLab #89):
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
4. **Test visually** — browser screenshot vs prototype comparison
5. **Close GitLab issue** when all acceptance criteria met

---

## 4. TASK DETAILS

### Task 1: S3.5-MP01 — Build The Markdown Editorial Page

**GitLab Issue:** #89
**Priority:** P1 — Critical
**Depends on:** Wave 1 complete (all 7 bug fixes deployed)

**What to build:**

A WordPress page at `/the-markdown/` (repurposing the current RSS XML URL) that renders The Markdown editorial layout — the same design shown in the prototype at `https://jk-com-ver02-5480e0.gitlab.io`.

The page should feel like a curated daily intelligence briefing, not a generic blog list.

---

### 4.1 Implementation Strategy

Build the page as a **WordPress shortcode** backed by a **WP_Query** fetching the latest `ns_feed_item` posts, organized into blocks by `content_domain` taxonomy. The shortcode renders the full editorial layout.

**Snippet decomposition (Lego blocks):**

| Snippet | Responsibility | Scope | Lines |
|---------|---------------|-------|-------|
| S3.5-W2-M01 Markdown Page Register | Register the shortcode + create the WP page | global | ~40 |
| S3.5-W2-M02 Markdown Data Query | WP_Query: fetch items per domain, structure for blocks | global | ~70 |
| S3.5-W2-M03 Markdown Masthead | Render masthead + edition banner + domain nav + status bar | global | ~60 |
| S3.5-W2-M04 Markdown Block 00 | Render lead story (hero full-width block) | global | ~60 |
| S3.5-W2-M05 Markdown Content Grid | Render blocks 01–06 in three-column grid | global | ~80 |
| S3.5-W2-M06 Markdown Block Card | Render an individual block card (reused by 01–06) | global | ~70 |
| S3.5-W2-M07 Markdown Commentary | Render Justin Kuiper commentary card | global | ~50 |
| S3.5-W2-M08 Markdown PUSH Buttons | Render social PUSH button row | global | ~40 |
| S3.5-W2-M09 Markdown Styles | All CSS for the editorial layout | front-end | ~150 |

Deploy in order: M09 (styles) → M08 → M07 → M06 → M02 → M04 → M05 → M03 → M01 (register + page).

---

### 4.2 Page Setup

**Step 1: Check if `/the-markdown/` page exists**

Query `GET /wp-json/wp/v2/pages?slug=the-markdown` to see if a page exists at that slug.

- **If no page exists:** Create one via `POST /wp-json/wp/v2/pages` with:
  - `title`: "The Markdown"
  - `slug`: "the-markdown"
  - `status`: "publish"
  - `content`: `[the_markdown_editorial]`

- **If a page exists but serves RSS:** The `/the-markdown/` URL may be handled by a rewrite rule or a feed redirect, not a page. Check if there's a snippet or rewrite rule causing it. If so, the page creation above should override it after flushing rewrite rules (trigger flush via a snippet using `flush_rewrite_rules(false)` on `init`).

---

### 4.3 Block Structure

The layout mirrors the prototype exactly:

```
┌─────────────────────────────────────────────────────────┐
│  MASTHEAD: THE MARKDOWN | tagline | date/time           │
│  EDITION BANNER: YEAR ONE — REINVENTION                 │
│  DOMAIN NAV: AI | CYBER | INNOVATION | FNW | SPACE...  │
├─────────────────────────────────────────────────────────┤
│  BLOCK 00 — LEAD STORY (full width)                    │
│  [Domain badge] [headline ALL CAPS] [hero image]        │
│  [excerpt] [source + timestamp]                         │
│  [Justin commentary card] [PUSH buttons]                │
├─────────────────────────────────────────────────────────┤
│  STATUS BAR: N ITEMS INGESTED TODAY • N BLOCKS FILLED  │
├─────────────────────────────────────────────────────────┤
│  COL LEFT     │  COL CENTER     │  COL RIGHT            │
│  Block 01     │  Block 03       │  Block 05             │
│  AI/GEN AI    │  INNOVATION     │  SPACE/AERO           │
│  LEAD STORY   │  CENTER STAGE   │  ORBIT INTEL          │
│  ─────────── │  ─────────────  │  ─────────────        │
│  Block 02     │  Block 04       │  Block 06             │
│  CYBER        │  FNW            │  DIGITAL LIFE         │
│  THREAT INTEL │  REFLECTION     │  PERSONAL ARC         │
└─────────────────────────────────────────────────────────┘
```

**Domain → Block mapping:**

| Block | Domain Label | Content Domain Slug | Section Title |
|-------|-------------|---------------------|--------------|
| 00 | LEAD | (most recent item, any domain) | LEAD STORY |
| 01 | AI / GEN AI | `ai` or `tech-and-ai` | LEAD STORY |
| 02 | CYBER | `cyber` | THREAT INTEL |
| 03 | INNOVATION | `innovation` | CENTER STAGE |
| 04 | FNW | `fnw` or `business-and-finance` | REFLECTION |
| 05 | SPACE / AERO | `space` | ORBIT INTEL |
| 06 | DIGITAL LIFE | `digital-life` | PERSONAL ARC |

**Note:** Check the actual `content_domain` term slugs in the database before hardcoding. Query `GET /wp-json/wp/v2/content_domain` to see all registered terms. Map prototype domain labels to actual slugs.

---

### 4.4 Data Query Logic (M02)

```php
function ns_markdown_get_block_data() {
    $domain_map = array(
        'lead'          => null,           // Most recent item, any domain
        'ai'            => 'ai',           // Use actual slug from taxonomy
        'cyber'         => 'cyber',
        'innovation'    => 'innovation',
        'fnw'           => 'fnw',
        'space'         => 'space',
        'digital-life'  => 'digital-life',
    );

    $blocks = array();

    // Block 00: Most recent item overall
    $lead_query = new WP_Query( array(
        'post_type'      => 'ns_feed_item',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    $blocks['lead'] = $lead_query->posts ? $lead_query->posts[0] : null;

    // Blocks 01-06: 1 item per domain (most recent)
    foreach ( array_slice( $domain_map, 1 ) as $block_key => $domain_slug ) {
        $q = new WP_Query( array(
            'post_type'      => 'ns_feed_item',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'tax_query'      => array( array(
                'taxonomy' => 'content_domain',
                'field'    => 'slug',
                'terms'    => $domain_slug,
            ) ),
        ) );
        $blocks[ $block_key ] = $q->posts ? $q->posts[0] : null;
    }

    return $blocks;
}
```

---

### 4.5 Visual Design Requirements

Match the prototype exactly on these elements:

**Color palette (from prototype):**
- Background: `#0a0a0a` (near-black)
- Surface/card: `#111111` or `#141414`
- Borders: `#1e1e1e` or `#222`
- Primary text: `#e0e0e0`
- Secondary text: `#888888`
- Accent/teal: `#00bcd4` (used on "MARK" in masthead, domain badges, status bar)
- Block number color: `#00bcd4` or `#1de9b6` (bright teal/mint)
- Commentary card bg: `#1a1a2e` or similar dark blue-purple

**Typography:**
- Masthead "THE MARKDOWN": large caps, font-weight 900, letter-spacing 0.15em
- "MARK" portion: color `#00bcd4` (teal accent)
- Section labels (LEAD STORY, THREAT INTEL, etc.): small caps, teal, letter-spacing 0.2em
- Headlines: ALL CAPS, font-weight 700, color `#e0e0e0`
- Body/excerpt text: font-weight 400, color `#999`
- Domain badges: uppercase, small, teal bg or teal text

**Block structure:**
- Block number: `00`, `01`, etc. — displayed as a prefix, teal color, monospace
- Section label: e.g. `LEAD STORY`, `THREAT INTEL` — below block number
- Block border: `1px solid #1e1e1e`, subtle
- Card hover: slight border color lift to `#333`

**Commentary card:**
- Styled like a tweet/post card
- Dark background (`#0d1117` or similar)
- JK avatar (use a placeholder `<div>` with initials "JK" if no actual avatar URL is known)
- Display name "Justin Kuiper" + handle "@JustinKuiper"
- Commentary text (use post meta field `jk_commentary` if it exists, otherwise a placeholder)
- Timestamp + "VIEW ON X →" link (placeholder `#` href if no actual URL)

**Status bar:**
- Full-width strip between Block 00 and the three-column grid
- Dark background with teal/bright text
- Dynamic: query total `ns_feed_item` posts published today for "N ITEMS INGESTED TODAY"
- Static "N BLOCKS FILLED" = count of non-null blocks in current edition
- "NEXT REFRESH" = hardcoded or configurable time (default: 0900)

**PUSH buttons:**
- Row of 5 buttons: X | LINKEDIN | INSTAGRAM | MEDIUM | YOUTUBE
- Small, uppercase, border-only style (not filled)
- Teal border and text color
- `href` should be share URLs with the post URL pre-filled where possible:
  - X: `https://twitter.com/intent/tweet?url={post_url}&text={post_title}`
  - LinkedIn: `https://www.linkedin.com/shareArticle?url={post_url}`
  - Others: placeholder `#` for now

**Mobile responsive:**
- Three-column grid collapses to single column at ≤768px
- Masthead font scales down with `clamp()`
- Domain nav wraps to two lines on mobile (do not hide any nav items)
- Status bar wraps to multi-line on narrow viewport
- Block cards stack vertically on mobile with full-width layout

---

### 4.6 Shortcode Registration (M01)

```php
add_shortcode( 'the_markdown_editorial', 'ns_render_markdown_editorial' );

function ns_render_markdown_editorial( $atts ) {
    if ( ! function_exists( 'ns_markdown_get_block_data' ) ) return '';
    if ( ! function_exists( 'ns_render_markdown_masthead' ) ) return '';

    ob_start();
    $blocks = ns_markdown_get_block_data();

    ns_render_markdown_masthead( $blocks );
    ns_render_markdown_block00( $blocks['lead'] );
    ns_render_markdown_status_bar( $blocks );
    ns_render_markdown_content_grid( $blocks );

    return ob_get_clean();
}
```

---

### 4.7 Phase 1 Scope (What to Build Now)

Build a **fully functional Phase 1** that matches the prototype layout and populates with real data. Some elements may be simplified:

**Include in Phase 1:**
- ✅ Full masthead with THE MARKDOWN wordmark + tagline + live JS date/time
- ✅ Edition banner ("YEAR ONE — REINVENTION" — hardcoded for now)
- ✅ Domain navigation (links pointing to archive filtered by domain, or `#` placeholder)
- ✅ Block 00 hero (real data from latest `ns_feed_item`)
- ✅ Status bar (dynamic item count + block fill count)
- ✅ Blocks 01–06 with real data per domain
- ✅ Domain badges and section labels on each block
- ✅ Excerpt/source/timestamp on each block
- ✅ PUSH buttons with X/LinkedIn share URLs using post URL
- ✅ Commentary card (with placeholder text if `jk_commentary` meta doesn't exist)
- ✅ Full responsive CSS (3-col desktop → 1-col mobile)
- ✅ Footer (already added by BF06)

**Defer to future sprint:**
- ⏳ Featured images pulled from actual Canva-generated cards (S3.2)
- ⏳ Edition banner pulling from a custom field or option
- ⏳ Domain nav filtering (requires per-domain archive views)
- ⏳ Live "NEXT REFRESH" countdown timer
- ⏳ JK commentary from actual post meta (use placeholder if meta empty)

---

## 5. EXECUTION ORDER

```
1. Visit prototype (https://jk-com-ver02-5480e0.gitlab.io) + read gap analysis  ~10min
2. Query content_domain taxonomy terms                                            ~5min
3. Query latest ns_feed_item posts to understand data shape                       ~5min
4. Deploy M09 (styles)                                                            ~20min
5. Deploy M08 (PUSH buttons)                                                      ~10min
6. Deploy M07 (commentary card)                                                   ~10min
7. Deploy M06 (block card renderer)                                               ~15min
8. Deploy M02 (data query)                                                        ~15min
9. Deploy M04 (Block 00 hero)                                                     ~15min
10. Deploy M05 (content grid)                                                     ~15min
11. Deploy M03 (masthead + edition banner + nav + status bar)                    ~15min
12. Deploy M01 (shortcode registration + page creation/update)                   ~15min
13. Visual verification — screenshot at 1440px, 768px, 375px                    ~20min
14. Compare screenshots to prototype                                              ~10min
15. Close GitLab issue #89                                                        ~5min
```

Total estimated: ~3 hours

---

## 6. OUTPUT FILES

After completing the task, produce a results file:

**File:** `02 DevOps/S3.5_Wave2_DevOps_Results.md`

Contents:
- Task result (PASS/PARTIAL/FAIL + evidence)
- All snippet IDs deployed (names, IDs, scope)
- Screenshots at all 3 breakpoints with prototype comparison
- URL of live Markdown page
- Data verification: how many blocks populated with real data vs placeholders
- Any issues encountered or deferred to next sprint
- Recommendations for Phase 2 (Wave 3)

---

## 7. DONE CRITERIA — Wave 2 DevOps

Wave 2 DevOps is DONE when:
- [ ] S3.5-MP01: The Markdown editorial page live at `/the-markdown/`
- [ ] Masthead renders with THE MARKDOWN wordmark and teal accent
- [ ] Edition banner visible
- [ ] Domain navigation visible (all 6+ domains)
- [ ] Block 00 hero renders with real data (latest ns_feed_item)
- [ ] Status bar shows dynamic item count
- [ ] Blocks 01–06 render with per-domain data (empty blocks show graceful fallback, not errors)
- [ ] PUSH buttons present on all blocks
- [ ] Commentary cards present on all blocks
- [ ] Layout is three-column at 1440px, single-column at 375px
- [ ] No horizontal overflow at any breakpoint
- [ ] All 9 snippets deployed via REST API with 0 errors
- [ ] GitLab issue #89 closed
- [ ] Results file created

---

## 8. CONSTRAINTS & REMINDERS

- **Prototype is the source of truth** — when in doubt about design, check `https://jk-com-ver02-5480e0.gitlab.io`
- **REST API first** — deploy everything via API, use browser only for visual verification
- **Lego blocks** — <80 lines target, ~150 ceiling, one responsibility per snippet. Split aggressively.
- **WordPress.com** — no SFTP, no wp-config.php, no mysqldump, WAF active on large payloads
- **Credential discipline** — hold credentials in memory, never log them, never commit them
- **Empty block fallback** — if a domain has no items, render a graceful "— No items today —" state, not a PHP error
- **Don't break existing pages** — homepage (`/`) and archive (`/archive/`) must remain unchanged
- **State your tool choice** at the start of every task with reasoning

---

## 9. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `01 Scrum Master/S3_ProtoVsProd_QA_Results.md` | Full prototype gap analysis — read before coding |
| `02 DevOps/S3.5_Wave1_DevOps_Results.md` | Wave 1 sign-off confirmation (verify before starting) |
| `02 DevOps/S3_Wave2_DevOps_Results.md` | Existing snippet inventory |

---

*Prepared by Taskmaster 2026-03-11. Sprint 3.5 Wave 2 is BLOCKED until Wave 1 sign-off. Do not start early.*
