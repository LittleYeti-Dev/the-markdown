# Sprint 3 — Wave 2 DevOps Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 2 (Dependent Tasks — Require Wave 1 Completions)
**Prepared by:** Taskmaster
**For:** Claude Code agent (DevOps role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 2 DevOps tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Wave 1 Must Be Complete

**Do NOT start Wave 2 until Wave 1 is signed off.** These tasks depend on:
- S3.1 (Canva templates) → needed by S3.2
- All Wave 1 FUNC tasks → needed by S3.13 and S3.15

Verify by checking `01 Scrum Master/S3_Wave1_Taskmaster_Results.md` and `02 DevOps/S3_Wave1_DevOps_Results.md`.

### Credentials

**Before touching ANY endpoint or running ANY code, ask Yeti for:**

1. **WordPress Application Password** — required for all authenticated REST API calls
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

2. **GitLab PAT** — `api` scope, for closing issues and committing results
   - Assume any PAT is short-lived. Yeti rotates multiple times daily.

3. **Canva MCP access** — for S3.2 (Canva MCP configuration)
   - Verify Canva MCP tools are available in your session

**Do not proceed past this section until you have the Application Password.**

---

## 1. MISSION

Execute the DevOps-owned Wave 2 tasks for Sprint 3. These are **4 tasks** covering Canva automation, archive page build, performance optimization, and SEO configuration. All depend on Wave 1 functional work being deployed and verified.

**Your tasks (4 total):**

| ID | Task | Type | GitLab Issue | Depends On |
|----|------|------|-------------|-----------|
| S3.2 | Configure Canva MCP — auto-generate cards from templates | FUNC | #53 | S3.1 templates done |
| S3.3 | Build archive page — historical editions with date/domain filter | FUNC | #54 | S1.14 (done) + Wave 1 design |
| S3.13 | Performance optimization — caching, lazy load, CDN evaluation | FUNC | #64 | All FUNC complete |
| S3.15 | SEO configuration — meta tags, OG tags, structured data | FUNC | #66 | All FUNC complete |

**Not your tasks:**
- S3.14 → Taskmaster (Cowork)

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No wp-config.php writes. No filesystem access.
- **Deploy via Code Snippets plugin** — REST API at `code-snippets/v1/snippets`.
- **WAF active** — large payloads get blocked. Keep snippets <80 lines target, ~150 ceiling.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`
- **Auth:** HTTP Basic — username `h3ndriksj` + Application Password.
- **Custom namespace:** `ns/v1`
- **Custom Post Type:** `ns_feed_item` (rest_base: `feed-items`)
- **Current active snippets:** 74+ (Wave 1 will have added more)

### Tool Hierarchy (DR-0021)

1. **WordPress REST API** — fastest, most reliable. Use for deploying snippets, reading data, checking health.
2. **GitLab REST API** — for issue tracking, commits.
3. **Canva MCP** — for template automation (S3.2).
4. **Admin AJAX** — for nonce-protected admin actions only.
5. **Browser automation** — LAST RESORT.

**At the start of every task, state your tool choice and reasoning.**

---

## 3. ARCHITECTURE — LEGO BLOCK PATTERN (DR-0016)

### Mandatory for ALL Sprint 3 Code

Every snippet must follow the micro-snippet pattern:

```
RULES:
- Target <80 lines, ceiling ~150 for complex UI/logic (DR-0024)
- One function per snippet (or one tightly coupled responsibility)
- Clear naming: S3-W2-M{nn} {Description}
- Explicit dependency via function_exists() checks
- Full docblock header with: sprint, wave, GitLab issue, dependencies, acceptance criteria
```

### Snippet Template

```php
<?php
/**
 * S3.{N} — {Title}
 * Sprint 3, Wave 2 | GitLab Issue #{NN}
 *
 * {One-line description of what this snippet does.}
 *
 * Deploy via: Code Snippets plugin
 * Snippet Title: "S3-W2 {Short Title}"
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
4. **Test functionality** — hit the relevant endpoint or page to confirm the snippet works
5. **Close GitLab issue** (if applicable)

---

## 4. TASK DETAILS

### Task 1: S3.2 — Configure Canva MCP

**GitLab Issue:** #53
**Priority:** Medium
**Depends on:** S3.1 (6 Canva domain templates must exist)

**What to do:**

Configure Canva MCP integration so the system can auto-generate branded visual cards from the domain templates created in S3.1.

**Implementation approach:**

1. **Verify Canva MCP availability:**
   - Check if Canva MCP tools are available in the current session
   - If not available, document what's needed and flag for Yeti

2. **Template mapping:**
   - Get the Canva template IDs/URLs for all 6 domain templates from S3.1 results
   - Map each template to its content domain (Tech & AI, Science, Business & Finance, Culture & Society, Politics & Policy, Sports & Entertainment)

3. **Automation workflow:**
   - Define the card generation flow: input (headline, domain, date) → Canva template → output (image URL)
   - Build a snippet or configuration that ties feed items to template selection based on `content_domain` taxonomy
   - Store generated card URLs as meta on `ns_feed_item` posts

4. **Testing:**
   - Generate a test card for each domain
   - Verify output matches brand spec

**Acceptance Criteria:**
- ✅ Canva MCP configured and operational
- ✅ All 6 domain templates mapped to content domains
- ✅ Auto-generation workflow defined and tested
- ✅ At least one test card generated per domain

---

### Task 2: S3.3 — Build Archive Page

**GitLab Issue:** #54
**Priority:** Medium
**Depends on:** S1.14 (done) + Wave 1 design work

**What to do:**

Build an archive page that displays historical editions of The Markdown with filtering by date and content domain.

**Implementation approach:**

1. **Archive page structure:**
   - Display feed items grouped by edition date
   - Filter controls: date range picker, content domain dropdown
   - Pagination for large result sets
   - Sorted newest-first by default

2. **Data source:**
   - Query `ns_feed_item` CPT via REST API or WP_Query
   - Use existing `content_domain` taxonomy for domain filtering
   - Use `post_date` for date filtering

3. **Implementation options:**
   - **Option A (Preferred):** Build as a WordPress shortcode snippet that renders the archive on a page
   - **Option B:** Build as a custom REST endpoint that returns filtered data, with front-end rendering via JavaScript snippet

4. **Snippet structure (Lego blocks):**
   - **S3-W2 Archive Query** — handles the WP_Query with filters (~60-80 lines)
   - **S3-W2 Archive Render** — renders the HTML output with pagination (~60-80 lines)
   - **S3-W2 Archive Styles** — CSS for the archive layout (front-end scope, ~40 lines)

5. **Create the archive page:**
   - Create a WordPress page titled "Archive" at `/archive`
   - Insert the shortcode on the page

**Acceptance Criteria:**
- ✅ Archive page accessible at `/archive` (or similar)
- ✅ Displays feed items grouped by date
- ✅ Filter by content domain works
- ✅ Filter by date range works
- ✅ Pagination works for large datasets
- ✅ Follows brand spec (dark theme, correct colors/fonts)

---

### Task 3: S3.13 — Performance Optimization

**GitLab Issue:** #64
**Priority:** Medium
**Depends on:** All FUNC tasks complete

**What to do:**

Optimize site performance — caching strategy, lazy loading, and CDN evaluation.

**WordPress.com constraint:** Many performance features are managed by the platform. Focus on what we can control.

**Implementation approach:**

1. **Baseline performance measurement:**
   - Run PageSpeed Insights on key pages (home, archive, about, feed)
   - Document scores: Performance, Accessibility, Best Practices, SEO
   - Identify top opportunities and diagnostics

2. **Lazy loading:**
   - Verify images use `loading="lazy"` attribute
   - If not, build a snippet to add lazy loading to all images in feed item cards
   - WordPress 5.5+ has native lazy loading — verify it's active

3. **Caching headers:**
   - WordPress.com manages server-side caching
   - Verify `Cache-Control` headers on key endpoints
   - If custom endpoints (ns/v1) lack caching headers, add them via snippet

4. **CDN evaluation:**
   - WordPress.com includes its own CDN (Photon for images)
   - Verify Photon is active for image serving
   - Document CDN coverage and any gaps

5. **Code optimization:**
   - Review existing snippets for unnecessary database queries
   - Check if any snippets load on pages where they're not needed (scope optimization)
   - Identify any render-blocking resources

**Snippet structure:**
- **S3-W2 Performance Tweaks** — lazy loading, cache headers, scope optimizations (~60-80 lines)

**Acceptance Criteria:**
- ✅ Baseline PageSpeed scores documented
- ✅ Lazy loading active on all images
- ✅ Caching headers verified on custom endpoints
- ✅ CDN coverage documented
- ✅ Performance recommendations documented (even if WordPress.com limits implementation)

---

### Task 4: S3.15 — SEO Configuration

**GitLab Issue:** #66
**Priority:** Medium
**Depends on:** All FUNC tasks complete

**What to do:**

Configure SEO fundamentals — meta tags, Open Graph tags, and structured data for The Markdown.

**Implementation approach:**

1. **Meta tags:**
   - Verify `<title>`, `<meta name="description">` on all key pages
   - Build a snippet to set custom meta descriptions for feed item pages if not already handled
   - Ensure no duplicate title tags

2. **Open Graph (OG) tags:**
   - Add `og:title`, `og:description`, `og:image`, `og:url`, `og:type` to all pages
   - For feed item pages: use the item's headline, excerpt, and Canva card image (from S3.2)
   - For static pages: use page-specific metadata

3. **Twitter Card tags:**
   - Add `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`
   - Use `summary_large_image` card type for feed items

4. **Structured data (JSON-LD):**
   - Add `NewsArticle` or `Article` schema for feed item pages
   - Add `WebSite` schema for the homepage
   - Add `Person` schema for the about page
   - Validate with Google's Rich Results Test

5. **Sitemap:**
   - Verify XML sitemap is being generated (WordPress.com usually handles this)
   - Confirm `ns_feed_item` CPT is included in sitemap
   - Verify robots.txt allows sitemap access

**Snippet structure (Lego blocks):**
- **S3-W2 OG Tags** — Open Graph + Twitter Card meta tags (~60-80 lines)
- **S3-W2 Structured Data** — JSON-LD schema markup (~60-80 lines)
- **S3-W2 SEO Meta** — custom meta descriptions + title optimization (~40-60 lines)

**Acceptance Criteria:**
- ✅ OG tags present on all pages (verified via view-source)
- ✅ Twitter Card tags present on all pages
- ✅ JSON-LD structured data on feed item pages (validated)
- ✅ Sitemap includes ns_feed_item posts
- ✅ No duplicate meta tags
- ✅ PageSpeed SEO score ≥ 90

---

## 5. EXECUTION ORDER

```
1. S3.2 (Canva MCP config)       — configuration + mapping              ~1.5h
2. S3.3 (archive page)           — main code task — query + render      ~2-3h
3. S3.15 (SEO configuration)     — meta tags + structured data          ~2h
4. S3.13 (performance)           — audit + optimization                 ~1.5h
```

**Rationale:** S3.2 first because it's configuration-heavy and quick. S3.3 next as the main build. S3.15 before S3.13 because SEO tags affect PageSpeed SEO score, so we want those in place before the performance baseline.

---

## 6. OUTPUT FILES

After completing all tasks, produce a results file:

**File:** `02 DevOps/S3_Wave2_DevOps_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- Snippet IDs deployed (with names and scope)
- Canva MCP configuration details
- Archive page URL and filter test results
- PageSpeed scores (before and after)
- SEO validation results (OG tags, structured data, sitemap)
- Any issues encountered
- Recommendations for Wave 3

---

## 7. DONE CRITERIA — Wave 2 DevOps

Wave 2 DevOps is DONE when:
- [ ] S3.2: Canva MCP configured, 6 templates mapped, test cards generated
- [ ] S3.3: Archive page live with date + domain filters working
- [ ] S3.13: Performance baseline documented, lazy loading active, caching verified
- [ ] S3.15: OG tags + Twitter Cards + JSON-LD on all pages, sitemap verified
- [ ] All snippets deployed via REST API with 0 errors
- [ ] Results file committed to GitLab
- [ ] GitLab issues #53, #54, #64, #66 closed (or updated with status)

---

## 8. CONSTRAINTS & REMINDERS

- **REST API first** — do not open a browser unless REST genuinely can't do it
- **Lego blocks** — <80 lines target, ~150 ceiling, one responsibility per snippet
- **WordPress.com** — no SFTP, no wp-config.php, no mysqldump, WAF active
- **Credential discipline** — hold credentials in memory, never log them, never commit them
- **Wave 1 must be done** — verify before starting any Wave 2 task
- **GitLab sync at session end** — gate exit criterion, not optional
- **State your tool choice** at the start of every task with reasoning

---

## 9. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `02 DevOps/S3_Wave1_DevOps_Results.md` | Wave 1 results (what was deployed) |
| `01 Scrum Master/S3_Wave1_Taskmaster_Results.md` | Canva template details (needed for S3.2) |

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 2 DevOps is BLOCKED until Wave 1 sign-off. Do not start early.*
