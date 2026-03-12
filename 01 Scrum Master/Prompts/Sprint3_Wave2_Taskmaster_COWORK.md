# Sprint 3 — Wave 2 Taskmaster Execution Prompt (COWORK)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 2 (Dependent Tasks — Require Wave 1 Completions)
**Prepared by:** Taskmaster
**For:** Claude Cowork agent (Taskmaster role)

> **This is a handoff document.** It contains everything a fresh Cowork session needs to execute Wave 2 Taskmaster tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Wave 1 Must Be Complete

**Do NOT start Wave 2 until Wave 1 is signed off.** This task depends on all Wave 1 FUNC tasks being deployed and verified.

Verify by checking:
- `01 Scrum Master/S3_Wave1_Taskmaster_Results.md`
- `02 DevOps/S3_Wave1_DevOps_Results.md`

### Credentials

**Ask Yeti for:**

1. **WordPress Application Password** — for REST API access to verify pages
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere.

2. **GitLab PAT** — for issue management
   - Project ID: `80070684`

**Do not proceed until you have credentials.**

---

## 1. MISSION

Execute the single Taskmaster-owned Wave 2 task for Sprint 3: **Mobile Responsive QA** across all site pages on 3 breakpoints. This is a testing and documentation task — no code deployment.

**Your task (1 total):**

| ID | Task | Type | GitLab Issue | Depends On |
|----|------|------|-------------|-----------|
| S3.14 | Mobile responsive QA — test all pages on 3 breakpoints | FUNC | #65 | All FUNC complete |

**Not your tasks:**
- S3.2, S3.3, S3.13, S3.15 → DevOps (Code)

---

## 2. PLATFORM CONTEXT

- **Site URL:** `https://justin-kuiper.com`
- **Testing approach:** Browser automation for screenshots, manual viewport testing
- **Key pages to test:** Homepage, archive page, about page, individual feed item pages, RSS feed page

### Tool Hierarchy

1. **Browser automation** — primary tool for this task (screenshot capture, viewport resizing)
2. **WordPress REST API** — for identifying all pages/routes to test
3. **PageSpeed Insights** — for mobile performance scores

---

## 3. TASK DETAILS

### Task 1: S3.14 — Mobile Responsive QA

**GitLab Issue:** #65
**Priority:** Medium
**Depends on:** All FUNC tasks complete (Wave 1 + Wave 2 DevOps)

**What to do:**

Systematically test every page on the site at 3 standard breakpoints and document any layout issues, overflow problems, or usability concerns.

**The 3 breakpoints:**

| Breakpoint | Width | Device Class |
|-----------|-------|-------------|
| Mobile | 375px | iPhone SE / standard mobile |
| Tablet | 768px | iPad / standard tablet |
| Desktop | 1440px | Standard desktop / laptop |

**Pages to test:**

1. **Homepage** (`/`) — hero, feed blocks, navigation
2. **Archive page** (`/archive`) — filters, pagination, feed item cards
3. **About page** (`/about`) — baseball card layout, bio content
4. **Individual feed item** (pick 3 representative items) — content display, metadata
5. **RSS feed page** (`/feed/the-markdown`) — XML rendering (verify not broken)
6. **Navigation** — menu, mobile hamburger, footer links
7. **Any new pages from Wave 1/2** — subdomain landing, etc.

**Testing procedure per page:**

1. Load the page at each breakpoint (resize browser or use device emulation)
2. Check for:
   - **Layout:** Content overflow, horizontal scrolling, overlapping elements
   - **Typography:** Text readable at all sizes, no text truncation
   - **Images:** Properly scaled, no overflow, lazy loading working
   - **Navigation:** Mobile menu functional, all links accessible
   - **Interactive elements:** Buttons clickable, filters working, forms usable
   - **Brand spec compliance:** Colors, fonts, logo display correct at all sizes
3. Take a screenshot at each breakpoint
4. Document any issues found with severity rating

**Severity ratings:**

| Rating | Meaning | Action |
|--------|---------|--------|
| P1 — Critical | Page broken, unusable | Must fix before sprint close |
| P2 — Major | Significant layout issue, content hidden | Should fix before sprint close |
| P3 — Minor | Cosmetic issue, slight misalignment | Fix if time permits |
| P4 — Enhancement | Not broken, but could be better | Backlog for future sprint |

**Acceptance Criteria:**
- ✅ All pages tested at all 3 breakpoints
- ✅ Screenshots captured for each page × breakpoint combination
- ✅ Issues documented with severity, description, and screenshot
- ✅ No P1 issues remaining (or escalated to DevOps for immediate fix)
- ✅ Summary table of pass/fail per page per breakpoint

---

## 4. OUTPUT FILES

**File:** `01 Scrum Master/S3_Wave2_QA_Results.md`

Contents:

### Summary Table
```
| Page | Mobile (375) | Tablet (768) | Desktop (1440) | Issues |
|------|:---:|:---:|:---:|--------|
| Homepage | ✅/⚠️/❌ | ✅/⚠️/❌ | ✅/⚠️/❌ | count |
| Archive | ... | ... | ... | ... |
| About | ... | ... | ... | ... |
| Feed Item | ... | ... | ... | ... |
```

### Issue Log
Per issue:
- Page, breakpoint, severity (P1-P4)
- Description of the issue
- Screenshot reference
- Recommended fix

### Screenshots
Save to: `01 Scrum Master/QA_Screenshots/` (one per page × breakpoint)

---

## 5. DONE CRITERIA — Wave 2 Taskmaster

Wave 2 Taskmaster is DONE when:
- [ ] S3.14: All pages tested at 3 breakpoints
- [ ] Screenshots captured and saved
- [ ] QA results file created with issue log
- [ ] No P1 issues remaining (or escalated)
- [ ] GitLab issue #65 closed (or updated with findings)

---

## 6. CONSTRAINTS & REMINDERS

- **Wave 1 + Wave 2 DevOps must be done first** — you're testing the final state
- **Screenshot everything** — evidence-based QA, not vibes-based
- **Be thorough** — check every interactive element, not just visual layout
- **Credential discipline** — hold credentials in memory only
- **State your tool choice** at the start with reasoning

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 2 Taskmaster is BLOCKED until Wave 2 DevOps sign-off.*
