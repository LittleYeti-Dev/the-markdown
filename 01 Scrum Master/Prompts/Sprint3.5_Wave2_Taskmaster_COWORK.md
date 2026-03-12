# Sprint 3.5 — Wave 2 Taskmaster Execution Prompt (COWORK)

**Version:** 1.0
**Date:** 2026-03-11
**Sprint:** Sprint 3.5 — Stabilization & Markdown Foundation
**Wave:** Wave 2 (Dependent — Requires Wave 1 + Wave 2 DevOps Complete)
**Prepared by:** Taskmaster
**For:** Claude Cowork agent (Taskmaster role)

> **This is a handoff document.** It contains everything a fresh Cowork session needs to execute Wave 2 Taskmaster tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Both Wave 1 DevOps AND Wave 2 DevOps Must Be Complete

**Do NOT start QA until both waves are signed off.**

Verify by checking:
- `02 DevOps/S3.5_Wave1_DevOps_Results.md` — all 7 bug fix tasks PASS
- `02 DevOps/S3.5_Wave2_DevOps_Results.md` — The Markdown editorial page deployed

If either file is missing or shows FAIL/PARTIAL, stop and escalate to Yeti before proceeding.

### Prototype Reference

The design prototype lives at:

**`https://jk-com-ver02-5480e0.gitlab.io`**

This is the source of truth for The Markdown editorial page visual design. You will use this for side-by-side comparison against production.

Also read: `01 Scrum Master/S3_ProtoVsProd_QA_Results.md` — the full prototype-vs-production gap analysis from Sprint 3 (written before Sprint 3.5 was executed). Use it to understand what was missing so you know what to verify is now fixed.

### Credentials

**Ask Yeti for:**

1. **WordPress Application Password** — for REST API page verification
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere.

2. **GitLab PAT** — for closing issues
   - Project ID: `80070684`

**Do not proceed until you have credentials.**

---

## 1. MISSION

Execute the single Taskmaster-owned Wave 2 task: **Sprint 3.5 QA** — verify all 7 bug fixes from Wave 1 and validate The Markdown editorial page from Wave 2 against the prototype design.

**Your task (1 total):**

| ID | Task | Type | GitLab Issue | Depends On |
|----|------|------|-------------|-----------|
| S3.5-QA01 | QA Sprint 3.5 — verify bug fixes and Markdown page | FUNC | #90 | Wave 1 + Wave 2 DevOps complete |

**Not your tasks:**
- All DevOps tasks (S3.5-BF01–BF07, S3.5-MP01) → already done by DevOps

---

## 2. PLATFORM CONTEXT

- **Site URL:** `https://justin-kuiper.com`
- **Prototype URL:** `https://jk-com-ver02-5480e0.gitlab.io`
- **Testing approach:** Browser automation — screenshots, viewport resizing, functional tests
- **Key pages to test:** About, Archive, Feed items, The Markdown editorial, homepage (regression check)

### Tool Hierarchy

1. **Browser automation** — primary tool for all visual QA (screenshot capture, viewport testing, functional checks)
2. **WordPress REST API** — for verifying RSS feed content, page existence, and structured data
3. **JavaScript execution** — for checking scroll overflow, DOM structure, meta tags

---

## 3. TASK DETAILS

### Task 1: S3.5-QA01 — Sprint 3.5 QA

**GitLab Issue:** #90
**Priority:** P1 (gate task — Wave 3 is blocked until this passes)

This is a two-part QA:

**Part A:** Verify all 7 Wave 1 bug fixes are working correctly across 3 breakpoints.

**Part B:** Validate The Markdown editorial page against the prototype design.

---

### Part A: Bug Fix Verification

For each bug fix, verify the fix is in place and document with a screenshot.

#### A1 — About Page Overflow (S3.5-BF01 / Issue #82)

**Test at:** 375px, 768px, 1440px

1. Load `https://justin-kuiper.com/about`
2. At 375px: run `document.body.scrollWidth > document.body.clientWidth` — must return `false`
3. Also check: `document.documentElement.scrollWidth > document.documentElement.clientWidth`
4. Scroll through entire page — no horizontal scroll bar
5. Take screenshot at all 3 breakpoints

**Pass criteria:**
- ✅ No horizontal overflow at 375px
- ✅ No horizontal overflow at 768px
- ✅ Desktop layout unchanged at 1440px

---

#### A2 — RSS Feed 0 Items (S3.5-BF02 / Issue #83)

**Test:** `GET https://justin-kuiper.com/feed/the-markdown/`

1. Load the URL in browser — view source or use REST check
2. Count `<item>` elements in the XML
3. Verify each item has `<title>`, `<link>`, `<description>`, `<pubDate>`

**Pass criteria:**
- ✅ At least 10 `<item>` elements present
- ✅ Items are `ns_feed_item` posts (not regular posts only)
- ✅ Items have valid `<link>` URLs pointing to `/feed-item/[slug]/`

---

#### A3 — Archive Pagination (S3.5-BF03 / Issue #84)

**Test at:** 375px, 768px, 1440px

1. Load `https://justin-kuiper.com/archive/`
2. Scroll to pagination at bottom
3. Count visible page number buttons
4. Verify ellipsis (`...`) appears between end pages and current-page neighborhood

**Pass criteria:**
- ✅ ≤9 page buttons visible (not 83)
- ✅ Ellipsis renders correctly
- ✅ Clicking a page button loads the correct page
- ✅ No pagination overflow at 375px

---

#### A4 — Mobile Nav Background (S3.5-BF04 / Issue #85)

**Test at:** 375px only (mobile hamburger menu)

1. Load `https://justin-kuiper.com` at 375px
2. Open mobile navigation (hamburger menu)
3. Screenshot the open nav state

**Pass criteria:**
- ✅ Nav menu background is dark (close to #0a0a0a — black/near-black)
- ✅ Nav link text is light/readable
- ✅ No white or light background visible behind nav items
- ✅ Desktop nav at 1440px is unchanged

---

#### A5 — Feed Item H1 Headline (S3.5-BF05 / Issue #86)

**Test:** Load 3 different feed item pages

1. Pick 3 representative feed items from the archive
2. For each: load the `/feed-item/[slug]/` URL
3. Check for visible H1 above the featured image
4. Run `document.querySelector('h1').textContent` — should match the post title

**Pass criteria:**
- ✅ H1 headline visible on all 3 feed item pages tested
- ✅ H1 renders above the featured image
- ✅ H1 text matches the article title
- ✅ H1 styled correctly (dark theme, readable)

---

#### A6 — Site Footer (S3.5-BF06 / Issue #87)

**Test pages:** Homepage, Archive, About, Feed item (one)

For each page:
1. Scroll to bottom
2. Verify footer is present
3. Check footer contains: THE MARKDOWN wordmark, nav links (ARCHIVE, RSS, ABOUT, CONTACT), copyright line

**Pass criteria:**
- ✅ Footer present on all 4 pages tested
- ✅ Footer contains THE MARKDOWN wordmark
- ✅ Footer navigation links are present and clickable
- ✅ Copyright text present
- ✅ Footer matches dark theme

---

#### A7 — Featured Image White Background (S3.5-BF07 / Issue #88)

**Test:** Load a feed item page known to have a white-background image

1. Find a feed item with a white-background image (reference: Novaspace article from S3 QA, screenshot `ss_4226ibda4`)
2. Load its feed item page
3. Compare featured image rendering against a known-white-bg image on a non-fixed version

**Pass criteria:**
- ✅ White-background images do not render as jarring white rectangles on dark bg
- ✅ Image content is still visible and recognizable
- ✅ Fix applies on archive cards AND on the single feed item page

---

### Part B: The Markdown Editorial Page Validation

**Primary URL:** `https://justin-kuiper.com/the-markdown/`
**Prototype:** `https://jk-com-ver02-5480e0.gitlab.io`

This is the main deliverable of Sprint 3.5. Test at all 3 breakpoints and validate each section against the prototype.

#### B1 — Page Accessibility

1. Load `https://justin-kuiper.com/the-markdown/`
2. Confirm it renders as a styled HTML page — NOT RSS XML

**Pass criteria:**
- ✅ Page renders as HTML (not XML)
- ✅ No PHP errors visible
- ✅ Page title is "The Markdown" or similar (not "RSS Feed")

---

#### B2 — Masthead

Load the page at 1440px. Visually compare masthead against prototype.

**Pass criteria:**
- ✅ "THE MARKDOWN" wordmark visible, large caps
- ✅ Teal/cyan accent on the "MARK" portion of the wordmark
- ✅ Tagline visible (e.g., "NON SEQUITUR • CURATED INTELLIGENCE")
- ✅ Live date/time or static date present
- ✅ Edition banner visible (e.g., "YEAR ONE — REINVENTION")

---

#### B3 — Domain Navigation

**Pass criteria:**
- ✅ Domain nav links visible: AI (or TECH), CYBER, INNOVATION, FNW (or BUSINESS), SPACE, DIGITAL LIFE
- ✅ At least 6 domain links present
- ✅ Nav wraps correctly on mobile (no overflow, no hidden items)

---

#### B4 — Block 00 — Lead Story

**Pass criteria:**
- ✅ Full-width hero block renders
- ✅ Real data: headline from actual `ns_feed_item` post (not placeholder text)
- ✅ Domain badge visible
- ✅ Excerpt/description visible
- ✅ Source + timestamp visible
- ✅ Featured image present (placeholder acceptable if no image set)
- ✅ PUSH buttons row present (X, LinkedIn, Instagram, Medium, YouTube)
- ✅ Commentary card present (placeholder text acceptable)

---

#### B5 — Status Bar

**Pass criteria:**
- ✅ Status bar visible between Block 00 and the content grid
- ✅ Shows item count (e.g., "N ITEMS INGESTED TODAY")
- ✅ Shows block fill count
- ✅ Shows next refresh time
- ✅ Styled correctly (dark bg, bright/teal text, full width)

---

#### B6 — Blocks 01–06 Content Grid

**Pass criteria:**
- ✅ Three-column grid visible at 1440px desktop
- ✅ All 6 domain blocks present (01 AI, 02 CYBER, 03 INNOVATION, 04 FNW, 05 SPACE, 06 DIGITAL LIFE)
- ✅ Each block has: block number, section label, domain badge, headline, excerpt
- ✅ Each block has PUSH buttons row
- ✅ Each block has a commentary card
- ✅ Blocks with no domain items show a graceful fallback (not a PHP error or blank space with broken layout)
- ✅ Real data (at least 4 of 6 blocks should have actual `ns_feed_item` content)

---

#### B7 — Responsive Layout

**Test at:** 375px, 768px, 1440px

**Pass criteria:**
- ✅ 1440px: Three-column grid (matches prototype)
- ✅ 768px: Two-column or single-column (no overflow)
- ✅ 375px: Single-column, all content accessible (matches prototype mobile behavior)
- ✅ No horizontal overflow at any breakpoint
- ✅ Masthead font scales appropriately on mobile
- ✅ Domain nav wraps (not cut off) on mobile

---

#### B8 — Prototype Comparison (Side-by-Side)

Take a full-page screenshot of production at 1440px. Compare against the prototype at 1440px.

**Score the following (pass/partial/fail):**

| Element | Production | Prototype Match? |
|---------|-----------|-----------------|
| Masthead wordmark | | |
| Teal accent color | | |
| Edition banner | | |
| Domain nav | | |
| Block 00 hero layout | | |
| Status bar | | |
| Three-column grid | | |
| Block numbering | | |
| PUSH buttons | | |
| Commentary cards | | |
| Dark theme consistency | | |
| Mobile layout | | |

Document what matches, what is close but not exact, and what is missing entirely.

---

### Part C: Regression Check

Verify that Sprint 3.5 changes did not break anything that was working before.

**Pages to check (1440px + 375px):**

1. **Homepage** (`/`) — personal bio, no changes expected
2. **Archive** (`/archive/`) — filters + cards still working, pagination now fixed

**Pass criteria:**
- ✅ Homepage renders correctly, no broken layout
- ✅ Archive filters (domain dropdown + date range) still functional
- ✅ Archive cards still display correctly
- ✅ No new issues introduced by Sprint 3.5 changes

---

## 4. QA SUMMARY MATRIX

Complete this matrix in the results file:

### Part A: Bug Fix Verification

| Issue | Fix | 375px | 768px | 1440px | Status |
|-------|-----|:-----:|:-----:|:------:|--------|
| #82 About overflow | BF01 | ✅/❌ | ✅/❌ | ✅/❌ | PASS/FAIL |
| #83 RSS 0 items | BF02 | N/A | N/A | ✅/❌ | PASS/FAIL |
| #84 Archive pagination | BF03 | ✅/❌ | ✅/❌ | ✅/❌ | PASS/FAIL |
| #85 Mobile nav bg | BF04 | ✅/❌ | N/A | N/A | PASS/FAIL |
| #86 Feed item H1 | BF05 | ✅/❌ | ✅/❌ | ✅/❌ | PASS/FAIL |
| #87 Footer | BF06 | ✅/❌ | ✅/❌ | ✅/❌ | PASS/FAIL |
| #88 Image white bg | BF07 | N/A | N/A | ✅/❌ | PASS/FAIL |

### Part B: Markdown Page Validation

| Section | Status | Notes |
|---------|--------|-------|
| B1 Page loads (not XML) | ✅/❌ | |
| B2 Masthead | ✅/⚠️/❌ | |
| B3 Domain nav | ✅/⚠️/❌ | |
| B4 Block 00 lead | ✅/⚠️/❌ | |
| B5 Status bar | ✅/⚠️/❌ | |
| B6 Content grid (01–06) | ✅/⚠️/❌ | |
| B7 Responsive layout | ✅/⚠️/❌ | |
| B8 Prototype match score | /12 | |

### Part C: Regression

| Page | Status | Notes |
|------|--------|-------|
| Homepage | ✅/❌ | |
| Archive | ✅/❌ | |

---

## 5. OUTPUT FILE

**File:** `01 Scrum Master/S3.5_Wave2_QA_Results.md`

Contents:
- Summary matrix (Parts A, B, C)
- Per-issue verification result + screenshot reference
- Markdown page section-by-section evaluation
- Prototype comparison notes
- New issues found (if any) with severity (P1–P4)
- Escalation list (any P1/P2 failures that block Wave 3)
- Screenshots saved to: `01 Scrum Master/QA_Screenshots/S3.5/`
- Sign-off statement: "Sprint 3.5 QA complete — Wave 3 [UNBLOCKED / BLOCKED pending fixes]"

---

## 6. DONE CRITERIA — Wave 2 Taskmaster

Wave 2 Taskmaster is DONE when:
- [ ] All 7 bug fixes verified (Pass/Fail per issue)
- [ ] The Markdown editorial page validated against prototype
- [ ] Responsive testing complete at all 3 breakpoints
- [ ] Regression check passed (homepage + archive unbroken)
- [ ] QA results file created with screenshots
- [ ] Any new P1 issues escalated to DevOps immediately
- [ ] GitLab issue #90 closed (or updated with blocking findings)
- [ ] Sprint 3.5 sign-off decision made: Wave 3 UNBLOCKED or BLOCKED

---

## 7. CONSTRAINTS & REMINDERS

- **Screenshot everything** — evidence-based QA
- **Compare against prototype** for The Markdown page — the prototype is the spec, not your memory
- **Escalate P1s immediately** — if you find a P1 (broken/unusable), stop and notify Yeti before continuing
- **Credential discipline** — hold credentials in memory only
- **State your tool choice** at the start of each test section with reasoning
- **Real data matters** — check that The Markdown page shows real `ns_feed_item` content, not hardcoded placeholders

---

## 8. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/S3_Wave2_QA_Results.md` | Sprint 3 QA results (original issue descriptions) |
| `01 Scrum Master/S3_ProtoVsProd_QA_Results.md` | Prototype vs production gap analysis (Sprint 3) |
| `02 DevOps/S3.5_Wave1_DevOps_Results.md` | Wave 1 sign-off + snippet list |
| `02 DevOps/S3.5_Wave2_DevOps_Results.md` | Wave 2 sign-off + Markdown page details |

---

*Prepared by Taskmaster 2026-03-11. Sprint 3.5 Wave 2 QA is BLOCKED until both Wave 1 DevOps and Wave 2 DevOps are signed off.*
