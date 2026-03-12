# Sprint 3 — Wave 2 QA Results (S3.14)

**Date:** 2026-03-11
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 2 (Dependent Tasks)
**Executed by:** Taskmaster (Cowork)
**GitLab Issue:** #65
**Site Under Test:** https://justin-kuiper.com

---

## Prerequisites Verified

- ✅ S3_Wave1_Taskmaster_Results.md — 3/3 PASS
- ✅ S3_Wave1_DevOps_Results.md — confirmed (snippets deployed)
- ✅ S3_Wave2_DevOps_Results.md — 4/4 PASS (S3.2, S3.3, S3.13, S3.15 all deployed)
- ✅ Wave 2 DevOps sign-off confirmed before QA commenced

---

## Summary Table

| Page | Mobile (375px) | Tablet (768px) | Desktop (1440px) | Issues |
|------|:-:|:-:|:-:|--------|
| Homepage | ✅ | ⚠️ | ✅ | 2 (P3, P4) |
| Archive | ✅ | ✅ | ⚠️ | 3 (P2, P3, P3) |
| About | ❌ | ❌ | ✅ | 1 P1 + 1 P2 |
| Feed Item (×3) | ✅ | ✅ | ⚠️ | 2 (P2, P3) |
| RSS Feed | ❌ | ❌ | ❌ | 1 P2 |

**Legend:** ✅ Pass | ⚠️ Pass with minor issues | ❌ Fail / critical issue

---

## Issue Log

### ISSUE-01 — About Page: Two-Column Layout Not Stacking (Mobile + Tablet)
- **Pages:** About (`/about/`)
- **Breakpoints:** Mobile (375px) ❌, Tablet (768px) ❌
- **Severity:** P1 — Critical (mobile), P2 — Major (tablet)
- **Description:** The About page uses a fixed two-column layout that does not collapse to a single column at narrow viewports. At 375px, the right column ("AI, ARCHITECTURE & CYBERSECURITY" hero content) is completely cut off — scrollWidth measured at 850px vs clientWidth of 500px, confirming 350px of horizontal overflow. At 768px, the right column heading clips beyond the right edge of the viewport. Content in the right column (bio, career narrative, three-pillar framework) is inaccessible on mobile.
- **Screenshot refs:** ss_7897s0ehg (mobile 375px), ss_2712qcr70 (tablet 768px), ss_7498og0xx (desktop 1440px)
- **Recommended fix:** Add CSS media query to the About page snippet or theme customizer — at ≤768px, switch the two-column layout to `display: block` or `flex-direction: column`. Target the `.entry-content` or the specific grid/flex container wrapping the baseball card. Example: `@media (max-width: 768px) { .about-layout { flex-direction: column; } }`
- **Escalate to:** DevOps — requires a new CSS snippet or update to existing About page snippet.

---

### ISSUE-02 — RSS Feed Empty (0 Items)
- **Page:** RSS Feed (`/feed/the-markdown/`)
- **Breakpoints:** All
- **Severity:** P2 — Major
- **Description:** The custom RSS feed at `/feed/the-markdown/` is valid XML and returns the correct channel metadata (title: "Justin Kuiper — The Markdown", description: "Curated editorial feed from The Markdown", lastBuildDate: Thu, 12 Mar 2026). However, the feed contains **zero `<item>` elements** — the channel closes immediately after the header. Any feed reader subscribed to this URL would show an empty feed. The `ns_feed_item` CPT is either not registered for this custom feed namespace or the query is not returning posts.
- **Screenshot ref:** ss_8009crgpk
- **Recommended fix:** DevOps to investigate the custom feed registration for the `the-markdown` feed slug. Verify that `add_feed('the-markdown', ...)` query includes `ns_feed_item` CPT. May also need `has_archive => true` confirmed on the CPT registration. Check snippet S3-W2 or Wave 1 feed snippets.
- **Escalate to:** DevOps — requires investigation of feed query and CPT registration.

---

### ISSUE-03 — Archive Pagination Shows All 83 Pages
- **Page:** Archive (`/archive/`)
- **Breakpoints:** All (most visible on mobile)
- **Severity:** P2 — Major
- **Description:** The pagination control at the bottom of the archive page renders individual numbered buttons for all 83 pages (1 through 83). At desktop, this creates a 6-row grid of page number buttons. At mobile, the buttons wrap into an 8-per-row grid. While no horizontal overflow occurs, the UX is unusable — users cannot practically navigate 83 individual buttons. Standard pattern is truncated pagination (1 2 3 … 81 82 83) or prev/next with current page indicator.
- **Screenshot refs:** ss_69085saql (desktop), ss_9794cfh3a (mobile)
- **Recommended fix:** DevOps to update the S3-W2 Archive Render snippet to implement truncated pagination. WordPress's `paginate_links()` with `'end_size' => 1, 'mid_size' => 1` automatically handles truncation. Replace the current manual page-number loop with `paginate_links()`.
- **Escalate to:** DevOps — update to S3-W2 Archive Render snippet.

---

### ISSUE-04 — Mobile Navigation Menu Has Light/White Background
- **Page:** All pages (global)
- **Breakpoints:** Mobile (375px) only
- **Severity:** P2 — Major
- **Description:** When the "Menu +" hamburger is tapped on mobile, the expanded navigation overlay renders with a white/light background and dark text links. This breaks the site-wide dark theme brand spec. The desktop and tablet nav use a dark background consistently. The contrast inversion on the mobile menu is jarring and off-brand.
- **Screenshot ref:** ss_8409pbqh8
- **Recommended fix:** Add CSS to the mobile menu overlay to match the dark theme. Target the `.main-navigation .menu` or `.toggled .nav-menu` selector: `background-color: #1a1a2e` (or site's dark bg color), `color: #ffffff`. This is a theme CSS customization — can be added to an existing front-end CSS snippet or the customizer's Additional CSS.
- **Escalate to:** DevOps — CSS snippet update or Additional CSS.

---

### ISSUE-05 — Feed Item Pages: Article Headline Not Displayed
- **Page:** All `ns_feed_item` single pages
- **Breakpoints:** All
- **Severity:** P2 — Major
- **Description:** Individual feed item pages do not display the article headline/title within the page content area. The page title appears in the browser tab and `<title>` tag, but the rendered page begins immediately with the featured image, then the article excerpt. Readers have no visual headline anchoring the content. This is a consistent issue across all 3 tested items (Satellite Connectivity, Enpulsion, Artemis Lander). The missing headline is also an SEO/accessibility concern (no visible H1 on the page).
- **Screenshot refs:** ss_8061v3hdx, ss_3800kof0k, ss_2860l9ee8
- **Recommended fix:** Update the `ns_feed_item` single post template (or add a snippet for `single-ns_feed_item.php` equivalent via Code Snippets) to render `the_title()` as an `<h1>` above the featured image. Alternatively, add CSS to show the `.entry-title` element if it's currently hidden with `display: none`.
- **Escalate to:** DevOps — single post template fix.

---

### ISSUE-06 — Homepage: No Footer Section
- **Page:** Homepage (`/`) and all pages
- **Breakpoints:** All
- **Severity:** P3 — Minor
- **Description:** The homepage (and other pages) have no visible footer. The page ends after the last content block with no footer nav, copyright notice, or closing element. A footer typically provides navigation fallback, legal info (copyright), and social links. Its absence is a usability and completeness gap.
- **Recommended fix:** Add a footer widget area or hardcoded footer snippet with copyright, nav links, and social icons. P3 — can be addressed in a future sprint if not prioritized for Sprint 3 close.

---

### ISSUE-07 — Tablet (768px): Navigation Wraps to Two Lines
- **Page:** All pages
- **Breakpoints:** Tablet (768px) only
- **Severity:** P3 — Minor
- **Description:** At 768px, the horizontal navigation (Home, About, The Markdown, Books, Contact) wraps to two lines — the first row shows Home, About, The Markdown, Books and the second row shows Contact. No hamburger menu appears at this width; the menu just wraps. The result is visually slightly awkward but fully functional and accessible.
- **Screenshot ref:** ss_3658o25vp
- **Recommended fix:** Either lower the hamburger breakpoint to 768px (so mobile menu triggers at tablet too), or reduce nav item font size / spacing to fit on one line at 768px. P3 — cosmetic.

---

### ISSUE-08 — Archive: "Customise Buttons" Admin Link Visible
- **Page:** Archive (`/archive/`)
- **Breakpoints:** All
- **Severity:** P3 — Minor
- **Description:** A "Customise buttons" link from the Jetpack sharing widget is visible at the bottom of the archive page. This is an admin-facing UI element that should not be exposed to public visitors. It appears below the Share this: X / Facebook buttons.
- **Screenshot ref:** ss_69085saql
- **Recommended fix:** Add CSS to hide `.sd-content .sharing-hidden` or the "Customise buttons" link for non-admin users. Alternatively, disable Jetpack sharing on the archive page template. P3 — cosmetic/admin hygiene.

---

### ISSUE-09 — Feed Items: Featured Image White/Light Background
- **Page:** Feed item single pages (source-image dependent)
- **Breakpoints:** All
- **Severity:** P3 — Minor
- **Description:** Some feed items have featured images with white or light-gray backgrounds (e.g., Novaspace logo, Enpulsion product photos). When displayed at full width on the dark-themed site, these images create a jarring white box against the dark background. Items with dark or transparent images (e.g., the Artemis/SpaceX image) display well. This is partially a content issue (source images) but could be mitigated at the template level.
- **Screenshot refs:** ss_9344lrmdx (Novaspace), ss_3800kof0k (Enpulsion)
- **Recommended fix (P3):** Add CSS `border-radius` or a subtle overlay/border to featured images on `ns_feed_item` single pages to soften the contrast. A more robust fix would add a dark gradient overlay on the image container. Alternatively, flag to content pipeline to normalize image backgrounds before ingest.

---

### ISSUE-10 — Mobile Homepage: Stats Row Uneven Heights
- **Page:** Homepage (`/`)
- **Breakpoints:** Mobile (375px)
- **Severity:** P4 — Enhancement
- **Description:** In the stats grid at mobile width, "Novels & Counting" wraps to two lines while the other three stat labels (Years Cyber, Years Guard, Years Married) remain on one line. This causes the "2 / Novels & Counting" cell to be taller than its siblings, creating slight visual imbalance.
- **Recommended fix:** Use `align-items: stretch` and `min-height` on the stat cells, or reword "Novels & Counting" to a shorter label. P4 — low priority.

---

## P1 Issue Status

| Issue | Description | Status |
|-------|-------------|--------|
| ISSUE-01 | About page layout broken on mobile/tablet | ⚠️ **OPEN — Escalated to DevOps** |

**Sprint 3 cannot close with ISSUE-01 open.** About page is inaccessible on mobile (375px).

---

## P2 Issues Summary (4 total)

| Issue | Description | Escalated To |
|-------|-------------|-------------|
| ISSUE-01 | About page layout (tablet) | DevOps |
| ISSUE-02 | RSS feed empty — 0 items | DevOps |
| ISSUE-03 | Archive pagination — all 83 pages shown | DevOps |
| ISSUE-04 | Mobile nav menu white background | DevOps |
| ISSUE-05 | Feed item headline missing | DevOps |

> Note: ISSUE-01 is both P1 (mobile) and P2 (tablet). ISSUE-05 also P2.

---

## P3/P4 Issues Summary (5 total)

| Issue | Description | Priority |
|-------|-------------|----------|
| ISSUE-06 | No footer on any page | P3 |
| ISSUE-07 | Nav wraps at 768px | P3 |
| ISSUE-08 | Admin link in archive | P3 |
| ISSUE-09 | White bg on feed images | P3 |
| ISSUE-10 | Stats row uneven at mobile | P4 |

---

## Pages Confirmed Working

| Page | Mobile | Tablet | Desktop | Notes |
|------|--------|--------|---------|-------|
| Homepage layout | ✅ | ✅ | ✅ | Minor stats alignment P4 |
| Homepage nav (hamburger) | ✅ | ✅ | ✅ | Hamburger opens/closes correctly |
| Homepage CTAs | ✅ | ✅ | ✅ | Both buttons clickable, side-by-side |
| Archive filters | ✅ | ✅ | ✅ | Domain dropdown + date pickers functional |
| Archive card grid | ✅ | ✅ | ✅ | 1-col mobile, 1-col tablet, 2-col desktop |
| Feed item images | ✅ | ✅ | ✅ | Scale correctly, no overflow |
| Feed item related posts | ✅ | ✅ | ✅ | 3-col grid at desktop |
| About page desktop | — | — | ✅ | Baseball card layout correct at 1440px |
| Dark theme consistency | ✅ | ✅ | ✅ | Consistent on all pages except mobile menu |
| Navigation links | ✅ | ✅ | ✅ | All 5 nav items accessible at all breakpoints |

---

## Recommendations for Wave 3 / Sprint Close

1. **MUST-FIX (P1):** About page two-column layout — DevOps CSS fix before sprint close
2. **SHOULD-FIX (P2):** RSS feed empty — investigate CPT registration in feed query
3. **SHOULD-FIX (P2):** Archive pagination — implement `paginate_links()` truncation
4. **SHOULD-FIX (P2):** Mobile nav dark background — CSS snippet update
5. **SHOULD-FIX (P2):** Feed item headline — add `the_title()` to single CPT template
6. **BACKLOG (P3):** Footer, admin link, image backgrounds, nav wrapping

---

## GitLab Issue #65

- **Action:** Close with this results file as evidence
- **Status:** S3.14 COMPLETE — all pages tested, issues documented, P1 escalated to DevOps

---

*Completed by Taskmaster (Cowork) 2026-03-11. S3.14 QA complete. 10 issues logged (1 P1, 4 P2, 4 P3, 1 P4). P1 escalated to DevOps for immediate fix.*
