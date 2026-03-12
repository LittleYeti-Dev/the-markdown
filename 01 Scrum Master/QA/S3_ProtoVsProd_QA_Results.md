# The Markdown — Prototype vs Production QA

**Date:** 2026-03-11
**Sprint:** Sprint 3 — Scale & Polish
**Executed by:** Taskmaster (Cowork)
**Prototype URL:** https://jk-com-ver02-5480e0.gitlab.io
**Production URL:** https://justin-kuiper.com
**GitLab Project:** h3ndriks.j/JK.com-ver02 (ID: 80070684, last deploy: 2026-03-10)

---

## Executive Summary

The GitLab Pages prototype is a complete static HTML design mock for **The Markdown** as a standalone editorial product — a daily news aggregation homepage with block-based layout, live status indicators, per-article PUSH buttons, and inline Justin Kuiper commentary. The production site at `justin-kuiper.com` is a personal author site for Justin Kuiper. **The Markdown as a dedicated experience does not yet exist in production.** The nav link "The Markdown" resolves to an empty RSS feed XML file, not a rendered page.

The archive page (`/archive/`) is the closest implemented equivalent to the prototype's content grid, but it is structurally and functionally quite different from the prototype design.

---

## Prototype Overview

**URL:** `https://jk-com-ver02-5480e0.gitlab.io`
**Title:** THE MARKDOWN | Non Sequitur News Aggregator
**Last deployed:** 2026-03-10

The prototype is a full-page editorial layout covering one edition of The Markdown. Key design elements:

- **Masthead** — "THE MARKDOWN" (large caps, teal accent on "MARK") + tagline "NON SEQUITUR • CURATED INTELLIGENCE" + live date/time + "LAST UPDATED [TIME] LOCAL"
- **Edition banner** — "YEAR ONE — REINVENTION" (section-level identifier)
- **Domain navigation** — AI | CYBER | INNOVATION | FNW | SPACE | DIGITAL LIFE | BLOG | X | LINKEDIN | INSTAGRAM
- **Block 00 (Lead)** — Full-width hero block: domain badge, headline (ALL CAPS), hero image, excerpt, source+timestamp, Justin Kuiper commentary card
- **Status bar** — "47 ITEMS INGESTED TODAY • 7 BLOCKS FILLED • NEXT REFRESH 0900"
- **Three-column content grid** (Blocks 01–06):
  - Left: Block 01 AI/GEN AI (LEAD STORY), Block 02 CYBER (THREAT INTEL)
  - Center: Block 03 INNOVATION (CENTER STAGE), Block 04 FNW (REFLECTION)
  - Right: Block 05 SPACE/AERO (ORBIT INTEL), Block 06 DIGITAL LIFE (PERSONAL ARC)
- **PUSH buttons** — X | LINKEDIN | INSTAGRAM | MEDIUM | YOUTUBE on every block
- **Justin commentary cards** — Tweet/post-style card with JK avatar, text, timestamp, "VIEW ON X →" or "VIEW ON INSTAGRAM →" link, on each major block
- **Pull-quote styling** — Cyan left-border blockquote for Yeti inline commentary
- **Footer** — "THE MARKDOWN" wordmark + ARCHIVE | RSS | ABOUT | CONTACT + "© 2026 Non Sequitur • Justin Kuiper • Curated daily at justin-kuiper.com"

**Mobile (375px):** Fully responsive. Three-column collapses to single column. No horizontal overflow. Nav wraps to two lines. Status bar wraps to multi-line.

---

## Production Overview

**URL:** `https://justin-kuiper.com`

Production is a personal author/bio site. Key pages:

| Page | URL | Description |
|------|-----|-------------|
| Homepage | `/` | Justin Kuiper personal bio — photo, tagline, stats (20+ yrs cyber, 19 yrs Guard, 33 yrs married, 2 novels), "The Stories Behind the Systems" copy, two CTAs |
| Archive | `/archive/` | Domain dropdown + date range pickers + 2-col card grid (domain label, headline, source+date). 83 pages of pagination shown (all buttons — ISSUE-03) |
| About | `/about/` | Baseball card layout — profile photo, career timeline, bio, three-pillar framework |
| Feed Item | `/feed-item/[slug]/` | Starts with featured image (no headline — ISSUE-05), then excerpt. No PUSH buttons, no commentary. |
| The Markdown nav link | `/the-markdown/` | **Resolves to RSS feed XML** — empty channel, 0 items (ISSUE-02) |
| RSS | `/feed/the-markdown/` | Same as above — valid XML header, 0 items |

---

## Gap Analysis — Feature by Feature

| Feature | Prototype | Production | Status | Priority |
|---------|-----------|------------|--------|----------|
| The Markdown dedicated homepage | ✅ Full page | ❌ Does not exist | **MISSING** | P1 |
| "THE MARKDOWN" masthead + branding | ✅ | ❌ | **MISSING** | P1 |
| Live date/time + "LAST UPDATED" | ✅ | ❌ | **MISSING** | P2 |
| Edition/season banner ("YEAR ONE") | ✅ | ❌ | **MISSING** | P2 |
| Domain navigation (AI, CYBER, etc.) | ✅ 10 items | ❌ Personal site nav only | **MISSING** | P2 |
| Block 00 lead story (hero layout) | ✅ | ❌ | **MISSING** | P1 |
| Status bar (items ingested, next refresh) | ✅ | ❌ | **MISSING** | P2 |
| Three-column block grid (01–06) | ✅ | ❌ — Archive is 2-col card grid | **MISSING** | P1 |
| Block numbering + section labels | ✅ | ❌ | **MISSING** | P2 |
| PUSH buttons (X, LinkedIn, etc.) per article | ✅ | ❌ | **MISSING** | P2 |
| Justin Kuiper commentary cards | ✅ | ❌ | **MISSING** | P2 |
| Pull-quote / Yeti inline commentary | ✅ | ❌ | **MISSING** | P2 |
| Article headline on feed item page | ✅ (implied) | ❌ ISSUE-05 | **BUG** | P2 |
| Featured images (real, not placeholder) | ❌ Placeholders only | ✅ Real images | PROTO INCOMPLETE |
| White-bg featured image mitigation | N/A (placeholders) | ❌ ISSUE-09 | **BUG** | P3 |
| Archive page with filters | ✅ (footer link) | ✅ Implemented | ✅ MATCH | — |
| Domain taxonomy on cards | ✅ | ✅ | ✅ MATCH | — |
| RSS feed link | ✅ (footer link) | ✅ URL exists | ⚠️ BUG (0 items) | P2 |
| About page | ✅ (footer link) | ✅ Implemented | ✅ MATCH | — |
| Dark theme throughout | ✅ | ✅ (except mobile nav) | ⚠️ ISSUE-04 | P2 |
| Footer | ✅ Full footer | ❌ ISSUE-06 | **BUG** | P3 |
| Mobile responsiveness | ✅ No overflow | ⚠️ About page P1 overflow | **BUG** | P1 |

---

## Prototype-Exclusive Features (Not Yet in Production)

These are features designed in the prototype that have no production equivalent yet:

### 1. The Markdown Homepage (P1 — Critical Gap)
The entire premise of the prototype is a dedicated "THE MARKDOWN" homepage at the root. Production has Justin Kuiper's personal bio at the root. The Markdown needs its own page/URL where the editorial layout lives — either replacing the homepage or living at a dedicated URL (e.g. `/the-markdown/` should render the layout, not serve XML).

### 2. Block-Based Editorial Layout (P1)
The prototype's core design pattern — numbered blocks (00–06) with section descriptors (LEAD STORY, THREAT INTEL, CENTER STAGE, etc.) in a three-column grid — does not exist in production. The archive's 2-column card grid is purely chronological and undifferentiated. The block system is what makes The Markdown feel like a curated editorial product, not just a list.

### 3. PUSH Buttons (P2)
Per-article distribution buttons (X, LinkedIn, Instagram, Medium, YouTube) are a key workflow feature in the prototype. Not implemented anywhere in production.

### 4. Justin Kuiper Commentary Cards (P2)
Each major block in the prototype has an inline JK commentary card — styled like an X/LinkedIn post with avatar, text, timestamp, and "VIEW ON X/INSTAGRAM →" link. This is the editorial voice layer. Entirely absent from production.

### 5. Live Operational Status Bar (P2)
The status bar ("47 ITEMS INGESTED TODAY • 7 BLOCKS FILLED • NEXT REFRESH 0900") communicates system health and editorial completeness. Not present in production. This would require dynamic data from the ingestion pipeline.

### 6. Edition Banner (P2)
"YEAR ONE — REINVENTION" — season/edition identifier. No equivalent in production.

### 7. Domain Navigation (P2)
The prototype nav is topic/domain-focused (AI, CYBER, INNOVATION, FNW, SPACE, DIGITAL LIFE + social links). Production nav is page/section-focused (Home, About, The Markdown, Books, Contact). These serve different purposes — the prototype nav enables domain filtering within The Markdown.

---

## Production Issues Confirmed (Cross-Referenced with S3.14 QA)

| Issue | Description | From S3.14 |
|-------|-------------|-----------|
| ISSUE-01 | About page layout breaks on mobile (P1) | ✅ Confirmed |
| ISSUE-02 | RSS feed 0 items — `/the-markdown/` serves empty XML | ✅ Confirmed |
| ISSUE-04 | Mobile nav white background | ✅ Confirmed |
| ISSUE-05 | Feed item pages: no article headline/H1 | ✅ Confirmed |
| ISSUE-06 | No footer on any page | ✅ Confirmed |
| ISSUE-09 | White/light background on featured images | ✅ Confirmed |

---

## Prototype Issues Found

| Issue | Description | Severity |
|-------|-------------|----------|
| PROTO-01 | All images are placeholders — no real image integration | P2 — Expected (prototype) |
| PROTO-02 | Content is static/hardcoded — no live data | P2 — Expected (prototype) |
| PROTO-03 | Nav links (AI, CYBER, etc.) are non-functional — no destination pages | P2 |
| PROTO-04 | PUSH buttons are non-functional — no actual sharing behavior | P2 |
| PROTO-05 | "VIEW ON X →" and "VIEW ON INSTAGRAM →" links are placeholder hrefs | P2 |
| PROTO-06 | Status bar data is hardcoded ("47 ITEMS INGESTED TODAY") — not live | P2 — Expected |

These are all expected in a prototype. None are blocking.

---

## Mobile Responsiveness — Prototype vs Production

| Breakpoint | Prototype | Production (Homepage) | Production (About) |
|------------|-----------|----------------------|-------------------|
| 375px | ✅ Single column, no overflow | ✅ Pass | ❌ P1 overflow (ISSUE-01) |
| 768px | Not tested | ✅ Pass | ❌ Clips at right edge (ISSUE-01) |
| 1440px | ✅ Three-column grid | ✅ Pass | ✅ Pass |

The prototype's three-column grid collapses correctly to a single column on mobile. Production needs to implement the same responsive behavior for The Markdown layout when it's built.

---

## Recommendations for Next Sprint

### Must-Build (to close the prototype → production gap):

1. **The Markdown Page** — Build a dedicated rendered page for The Markdown editorial layout at a stable URL. The `/the-markdown/` URL currently serves RSS XML; this needs to be a WP page with the block layout shortcode.

2. **Block Layout Template** — Implement the Block 00–06 editorial structure as a WordPress template or shortcode. This is the prototype's primary design pattern. Could be phased: start with a single-column version, evolve to three-column.

3. **The Markdown Navigation** — Either add domain-based nav to The Markdown page, or update the global nav to clearly route "The Markdown" to the rendered page (not the RSS feed).

### Should-Build (P2 fidelity):

4. **PUSH Buttons** — Per-article distribution buttons. Could leverage Jetpack Sharing or custom snippet per content domain.

5. **Justin Commentary Integration** — A way to attach Yeti's commentary to feed items (custom meta field on `ns_feed_item`), surfaced on the The Markdown page layout.

6. **Fix RSS Feed** (ISSUE-02) — Already escalated to DevOps. The `/the-markdown/` URL should render the page; the RSS feed should live at `/feed/the-markdown/` and actually contain items.

### Carry-Forward (P3):

7. Footer (ISSUE-06), image background handling (ISSUE-09), edition/season banner.

---

## Screenshot References

| Screenshot | Description |
|------------|-------------|
| ss_6346ix8le | Prototype homepage at 1440px — masthead + Block 00 |
| ss_65791feoe | Prototype homepage at 375px — mobile layout |
| ss_6030dnk71 | Production homepage at 1440px — personal bio |
| ss_84312u0sg | Production archive at 1440px — card grid + filters |
| ss_61469uv8m | Production archive bottom — all 83 pagination buttons |
| ss_4226ibda4 | Production feed item — Novaspace, no headline, white-bg image |
| ss_6078b9p9d | Production `/the-markdown/` — RSS XML, empty feed |

---

*Completed by Taskmaster (Cowork) 2026-03-11. Prototype URL: jk-com-ver02-5480e0.gitlab.io (deployed 2026-03-10). Production: justin-kuiper.com. The Markdown editorial layout is not yet live in production.*
