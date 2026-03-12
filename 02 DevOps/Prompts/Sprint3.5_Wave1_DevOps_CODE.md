# Sprint 3.5 — Wave 1 DevOps Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-11
**Sprint:** Sprint 3.5 — Stabilization & Markdown Foundation
**Wave:** Wave 1 (Independent Bug Fixes — No Dependencies)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 1 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Confirm Sprint 3 is Complete

Wave 1 bug fixes assume the Sprint 3 codebase is in place. Verify by checking:
- `01 Scrum Master/S3_Wave1_Taskmaster_Results.md`
- `02 DevOps/S3_Wave1_DevOps_Results.md`
- `02 DevOps/S3_Wave2_DevOps_Results.md`

If Sprint 3 Wave 2 DevOps is not complete, do not proceed — escalate to Yeti.

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

Execute all 7 bug fix tasks for Sprint 3.5 Wave 1. These are independent fixes with no inter-dependencies — they can be executed in any order, though the priority order below is recommended.

**Your tasks (7 total):**

| ID | Task | GitLab Issue | Priority |
|----|------|-------------|----------|
| S3.5-BF01 | Fix About page horizontal overflow on mobile | #82 | P1 — Critical |
| S3.5-BF02 | Fix RSS feed — returns 0 items | #83 | P2 — Major |
| S3.5-BF03 | Fix archive pagination — limit visible page buttons | #84 | P2 — Major |
| S3.5-BF04 | Fix mobile nav — enforce dark theme background | #85 | P2 — Major |
| S3.5-BF05 | Fix feed item pages — add H1 article headline | #86 | P2 — Major |
| S3.5-BF06 | Add site footer to all pages | #87 | P3 — Minor |
| S3.5-BF07 | Fix featured image white background on feed items | #88 | P3 — Minor |

**Not your tasks:**
- S3.5-MP01 (#89) → Wave 2 DevOps (depends on Wave 1 complete)
- S3.5-QA01 (#90) → Taskmaster/Cowork

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — keep snippets <80 lines target, ~150 ceiling. Large payloads get blocked.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`
- **Auth:** HTTP Basic — username `h3ndriksj` + Application Password.
- **Custom namespace:** `ns/v1`
- **Custom Post Type:** `ns_feed_item` (rest_base: `feed-items`)

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — fastest, most reliable. Use for deploying snippets, reading data, checking health.
2. **GitLab REST API** — for issue tracking.
3. **Browser automation** — use for visual verification of CSS fixes after deployment.
4. **Admin AJAX** — for nonce-protected admin actions only. Last resort.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3.5 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3.5-W1-BF{nn} {Description}
- Explicit dependency via function_exists() checks where applicable
- Full docblock header with: sprint, wave, GitLab issue, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.5-BF{NN} — {Title}
 * Sprint 3.5, Wave 1 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S3.5-W1-BF{NN} {Short Title}"
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
3. **Verify deployment** — `GET /wp-json/code-snippets/v1/snippets` and confirm snippet is active with 0 errors
4. **Test functionality** — browser screenshot or endpoint check to confirm the fix works
5. **Close GitLab issue** — `PUT /api/v4/projects/80070684/issues/{iid}` with `state_event=close`

---

## 4. TASK DETAILS

### Task 1: S3.5-BF01 — Fix About Page Horizontal Overflow (P1)

**GitLab Issue:** #82
**Priority:** P1 — Critical (page broken on mobile)
**Scope:** front-end CSS

**Root cause:** A fixed-width element in the baseball card layout (likely the profile photo container or career timeline grid) is wider than the viewport at 375px, causing `scrollWidth:850px` vs `clientWidth:500px`. This creates horizontal scroll on the About page.

**Implementation approach:**

1. **Audit via browser:** Load `/about` at 375px. Use DevTools or JavaScript `document.querySelectorAll('*')` with `scrollWidth > clientWidth` check to identify the offending element.

2. **Write a targeted CSS fix** — likely one of:
   - Add `max-width: 100%; overflow-x: hidden;` to the baseball card container
   - Fix the profile photo/image column to use `width: 100%` instead of fixed px at mobile breakpoint
   - Ensure the career timeline grid switches to `grid-template-columns: 1fr` at ≤480px
   - Add `box-sizing: border-box` if padding is pushing elements outside bounds

3. **Snippet structure:**
   - One front-end snippet: `S3.5-W1-BF01 About Page Mobile Fix`
   - Contains only the targeted CSS `<style>` block via `wp_enqueue_style` or `wp_head` hook

4. **Verify:** Screenshot at 375px and 768px — no horizontal scroll, all content visible.

**Acceptance Criteria:**
- ✅ About page has no horizontal overflow at 375px
- ✅ About page has no horizontal overflow at 768px
- ✅ Desktop layout (1440px) unchanged
- ✅ All content visible without horizontal scrolling

---

### Task 2: S3.5-BF02 — Fix RSS Feed Returns 0 Items (P2)

**GitLab Issue:** #83
**Priority:** P2 — Major
**Scope:** global (feed hook)

**Root cause:** WordPress's built-in RSS query uses `post_type = 'post'` by default. The `ns_feed_item` custom post type is not included, so `/feed/the-markdown/` returns an empty channel. The CPT needs to be added to the feed query.

**Implementation approach:**

1. **Hook into `pre_get_posts`** to modify the main feed query when `is_feed()` is true and the feed is `the-markdown`.

2. **Snippet:**
```php
add_action( 'pre_get_posts', function( $query ) {
    if ( $query->is_main_query() && $query->is_feed() ) {
        $query->set( 'post_type', array( 'post', 'ns_feed_item' ) );
    }
});
```

3. **Alternative approach** if scoping to `/feed/the-markdown/` only is needed: check `$query->get('feed')` value and apply only to the `the-markdown` feed slug.

4. **Verify:** `GET /feed/the-markdown/` should return populated `<item>` elements. Also verify `/the-markdown/` URL behavior — it currently resolves to RSS XML. If that URL should render a page (not XML), that is addressed in Wave 2 (MP01), not here.

**Acceptance Criteria:**
- ✅ `/feed/the-markdown/` returns at least the 10 most recent `ns_feed_item` posts as RSS items
- ✅ Each item has `<title>`, `<link>`, `<description>`, `<pubDate>`
- ✅ Main site RSS feed (`/feed/`) is not negatively affected

---

### Task 3: S3.5-BF03 — Fix Archive Pagination (P2)

**GitLab Issue:** #84
**Priority:** P2 — Major
**Scope:** front-end (archive page shortcode)

**Root cause:** The archive page pagination renders all page numbers (83+ buttons), making the page unusable. The `paginate_links()` call needs `end_size` and `mid_size` constraints.

**Implementation approach:**

1. **Locate the archive pagination snippet** — find the existing S3-W2 Archive snippet by querying `GET /wp-json/code-snippets/v1/snippets` and searching for the archive render snippet by name/title.

2. **Update the `paginate_links()` call** with:
```php
echo paginate_links( array(
    'total'     => $wp_query->max_num_pages,
    'current'   => $paged,
    'end_size'  => 1,   // Show 1 page at each end
    'mid_size'  => 2,   // Show 2 pages around current
    'prev_text' => '&laquo; Prev',
    'next_text' => 'Next &raquo;',
) );
```

3. **Deploy as a patch** — either update the existing snippet via `PUT /wp-json/code-snippets/v1/snippets/{id}` or create a new override snippet titled `S3.5-W1-BF03 Archive Pagination Fix`.

4. **Verify:** Load `/archive` and confirm pagination shows at most ~7-9 page buttons (first, prev, ..., current±2, ..., last, next). Screenshot at 375px, 768px, 1440px.

**Acceptance Criteria:**
- ✅ Archive pagination shows ≤9 visible page buttons regardless of total page count
- ✅ Ellipsis (`...`) appears between end pages and middle pages
- ✅ Pagination works correctly — clicking a page loads that page's content
- ✅ Layout does not overflow at any breakpoint

---

### Task 4: S3.5-BF04 — Fix Mobile Nav Dark Theme Background (P2)

**GitLab Issue:** #85
**Priority:** P2 — Major
**Scope:** front-end CSS

**Root cause:** The mobile navigation menu container renders with a white or light background instead of the site's dark theme. This happens when the mobile hamburger menu opens — the dropdown or slide-out menu inherits default WordPress/theme styles instead of the brand dark background.

**Implementation approach:**

1. **Identify the offending selector** via browser DevTools at 375px — open the mobile nav, inspect the menu container. Common selectors:
   - `.nav-menu` when `display: block`
   - `.menu-toggle + .nav-menu`
   - `.main-navigation .menu`
   - `#primary-menu`

2. **Write targeted CSS:**
```css
@media (max-width: 768px) {
    .main-navigation .menu,
    .main-navigation ul,
    .nav-menu,
    #primary-menu {
        background-color: #0a0a0a !important;
        color: #e0e0e0 !important;
    }
    .main-navigation a {
        color: #e0e0e0 !important;
    }
}
```

3. **Snippet:** `S3.5-W1-BF04 Mobile Nav Dark Theme` — front-end scope, outputs CSS via `wp_head`.

4. **Verify:** Screenshot mobile nav open at 375px — background must be dark (#0a0a0a or close), text legible.

**Acceptance Criteria:**
- ✅ Mobile nav dropdown/menu has dark background matching site theme
- ✅ Nav link text is legible (light color on dark background)
- ✅ Desktop nav is not affected
- ✅ No white flash when nav opens

---

### Task 5: S3.5-BF05 — Add H1 Headline to Feed Item Pages (P2)

**GitLab Issue:** #86
**Priority:** P2 — Major
**Scope:** front-end (single post template hook)

**Root cause:** Individual feed item pages (`/feed-item/[slug]/`) display the featured image then the excerpt, but no article headline (H1). The post title is not being rendered before the featured image.

**Implementation approach:**

1. **Hook into `the_content` or use `the_post`** to inject the H1 before the featured image on `ns_feed_item` single pages:

```php
add_filter( 'the_content', function( $content ) {
    if ( is_singular( 'ns_feed_item' ) ) {
        $headline = '<h1 class="feed-item-headline">' . esc_html( get_the_title() ) . '</h1>';
        return $headline . $content;
    }
    return $content;
});
```

2. **Add supporting CSS** to style the H1:
```css
.feed-item-headline {
    font-size: clamp(1.5rem, 4vw, 2.5rem);
    font-weight: 700;
    color: #e0e0e0;
    margin: 1.5rem 0 1rem;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
```

3. **Snippet:** `S3.5-W1-BF05 Feed Item Headline` — front-end scope.

4. **Verify:** Load an individual feed item page — confirm H1 with the article title appears above the featured image.

**Acceptance Criteria:**
- ✅ H1 with article title visible on all `ns_feed_item` single pages
- ✅ H1 renders above the featured image
- ✅ H1 styled to match brand spec (dark theme, correct color/font weight)
- ✅ No duplicate titles (check `<title>` tag is not repeated as visible H1 twice)
- ✅ Other post types are not affected

---

### Task 6: S3.5-BF06 — Add Site Footer to All Pages (P3)

**GitLab Issue:** #87
**Priority:** P3 — Minor
**Scope:** global (footer hook)

**Root cause:** No footer renders on any page. The site is missing a `wp_footer` hook or the theme's footer template is empty/missing.

**Implementation approach:**

1. **Hook into `wp_footer`** to output the footer markup:

```php
add_action( 'wp_footer', function() {
    ?>
    <footer id="site-footer" class="jk-footer">
        <div class="footer-inner">
            <p class="footer-wordmark">THE MARKDOWN</p>
            <nav class="footer-nav">
                <a href="/archive/">ARCHIVE</a>
                <a href="/feed/the-markdown/">RSS</a>
                <a href="/about/">ABOUT</a>
                <a href="/contact/">CONTACT</a>
            </nav>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> Non Sequitur &bull; Justin Kuiper &bull; Curated daily at justin-kuiper.com</p>
        </div>
    </footer>
    <?php
});
```

2. **Add CSS** for footer styling (dark bg, centered, brand colors):
```css
.jk-footer {
    background-color: #0a0a0a;
    border-top: 1px solid #1e1e1e;
    padding: 2rem 1.5rem;
    text-align: center;
    margin-top: 3rem;
}
.footer-wordmark {
    font-size: 1.5rem;
    font-weight: 900;
    letter-spacing: 0.15em;
    color: #00bcd4;
    margin-bottom: 1rem;
}
.footer-nav a {
    color: #888;
    text-decoration: none;
    margin: 0 0.75rem;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
}
.footer-nav a:hover { color: #e0e0e0; }
.footer-copy {
    color: #555;
    font-size: 0.7rem;
    margin-top: 1rem;
}
```

3. **Snippet:** `S3.5-W1-BF06 Site Footer` — global scope (renders on all pages).

4. **Verify:** Screenshot homepage, archive, about, and a feed item page — all should show the footer.

**Acceptance Criteria:**
- ✅ Footer renders on all pages (homepage, archive, about, feed items)
- ✅ Footer contains: "THE MARKDOWN" wordmark, ARCHIVE | RSS | ABOUT | CONTACT nav, copyright line
- ✅ Footer matches brand dark theme
- ✅ Footer is responsive (no overflow at 375px)

---

### Task 7: S3.5-BF07 — Fix Featured Image White Background (P3)

**GitLab Issue:** #88
**Priority:** P3 — Minor
**Scope:** front-end CSS

**Root cause:** Some `ns_feed_item` posts have featured images with white or light backgrounds (e.g., logos, diagrams). On the dark-themed site these look jarring. The fix is a CSS `mix-blend-mode` or container background treatment that masks or integrates white-bg images.

**Implementation approach:**

1. **CSS approach (preferred, non-destructive):**
```css
/* On cards/feed item pages, blend white-bg images into dark bg */
.feed-item-card .wp-post-image,
.entry-content .wp-post-image,
.featured-image img {
    mix-blend-mode: luminosity;
    opacity: 0.9;
}
```

2. **Alternative if blend-mode is too heavy:** Add a dark overlay wrapper:
```css
.featured-image-wrap {
    position: relative;
    background-color: #111;
}
.featured-image-wrap img {
    display: block;
    width: 100%;
}
```

3. **Verify:** Load a feed item known to have a white-background image (screenshot reference: `ss_4226ibda4` from S3 QA). Confirm the image integrates with the dark theme without looking like a white rectangle.

4. **Snippet:** `S3.5-W1-BF07 Featured Image Dark Theme` — front-end scope.

**Note:** This is a CSS mitigation, not a permanent solution. If images are replaced with dark-background versions in future, this CSS can be removed.

**Acceptance Criteria:**
- ✅ White-background featured images no longer appear as jarring white rectangles on dark bg
- ✅ Image content is still visible and recognizable
- ✅ Fix applies to both feed item cards (archive/homepage) and single feed item pages
- ✅ Images that already have dark backgrounds are not negatively affected

---

## 5. EXECUTION ORDER

```
1. S3.5-BF01 (About page overflow)    — P1 — fix immediately         ~30min
2. S3.5-BF05 (Feed item H1)           — P2 — PHP hook, straightforward ~20min
3. S3.5-BF02 (RSS 0 items)            — P2 — pre_get_posts hook       ~20min
4. S3.5-BF04 (Mobile nav dark bg)     — P2 — CSS fix                  ~20min
5. S3.5-BF03 (Archive pagination)     — P2 — patch existing snippet   ~30min
6. S3.5-BF06 (Site footer)            — P3 — new snippet              ~30min
7. S3.5-BF07 (Image white bg)         — P3 — CSS mitigation           ~20min
```

**Rationale:** P1 first. Then PHP/logic fixes before CSS. Archive pagination last among P2s because it requires locating and patching an existing snippet.

---

## 6. OUTPUT FILES

After completing all tasks, produce a results file:

**File:** `02 DevOps/S3.5_Wave1_DevOps_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- Snippet IDs deployed (name, ID, scope)
- Screenshots confirming each fix (reference IDs)
- Any issues encountered or deferred
- Sign-off statement: "Wave 1 complete — Wave 2 unblocked"

---

## 7. DONE CRITERIA — Wave 1 DevOps

Wave 1 DevOps is DONE when:
- [ ] S3.5-BF01: About page no horizontal overflow at 375px and 768px
- [ ] S3.5-BF02: `/feed/the-markdown/` returns populated RSS items
- [ ] S3.5-BF03: Archive pagination shows ≤9 buttons with ellipsis
- [ ] S3.5-BF04: Mobile nav has dark background
- [ ] S3.5-BF05: H1 headline visible on all feed item pages
- [ ] S3.5-BF06: Footer renders on all pages
- [ ] S3.5-BF07: White-bg images mitigated on dark theme
- [ ] All 7 GitLab issues (#82–#88) closed
- [ ] Results file committed to GitLab

---

## 8. CONSTRAINTS & REMINDERS

- **REST API first** — do not open a browser unless visual verification genuinely requires it
- **Lego blocks** — <80 lines target, ~150 ceiling, one responsibility per snippet
- **WordPress.com** — no SFTP, no wp-config.php, no mysqldump, WAF active
- **Credential discipline** — hold credentials in memory, never log them, never commit them
- **Screenshot evidence** — take a before/after screenshot for every visual fix
- **State your tool choice** at the start of every task with reasoning

---

## 9. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `01 Scrum Master/S3_Wave2_QA_Results.md` | Full QA issue log with screenshots + measurements |
| `02 DevOps/S3_Wave2_DevOps_Results.md` | Current snippet inventory (deployed snippets) |

---

*Prepared by Taskmaster 2026-03-11. Sprint 3.5 Wave 1 is immediately executable — no prior wave dependency.*
