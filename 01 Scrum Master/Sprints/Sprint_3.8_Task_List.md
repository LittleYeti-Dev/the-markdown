# Sprint 3.8 — Editorial Approval Workflow

**Sprint Goal:** Add a human-in-the-loop editorial approval gate between AI curation and The Markdown's live page. AI proposes, Yeti approves, AI learns.

**Start Date:** TBD (next session)
**Est. Effort:** 6–7 hours across 7–8 snippets
**Dependencies:** S3.5/S3.6 editorial page complete (DONE), API access restored (DONE)

---

## Architecture Overview

**Current flow:**
```
RSS Ingest → Claude Scoring → domain_tag → Shortcode queries live → Page renders
```

**New flow:**
```
RSS Ingest → Claude Scoring → Draft Builder (cron 0800 + on-demand)
  → Draft Board (staging layer)
  → Email Digest to Yeti
  → Admin Approval UI (Yeti reviews, reorders, approves)
  → Preference Logger (captures deltas)
  → Published Board
  → Shortcode reads from Published Board → Page renders
```

---

## Slot Addressing Scheme

Box 00 is the single lead story. Boxes 01–06 hold max 3 items each.

| Block | Slots | Description |
|-------|-------|-------------|
| Box 00 — LEAD | `00` | Single headline story + hero image |
| Box 01 | `010`, `011`, `012` | Up to 3 items |
| Box 02 | `020`, `021`, `022` | Up to 3 items |
| Box 03 | `030`, `031`, `032` | Up to 3 items |
| Box 04 | `040`, `041`, `042` | Up to 3 items |
| Box 05 | `050`, `051`, `052` | Up to 3 items |
| Box 06 | `060`, `061`, `062` | Up to 3 items |

**19 slots total** (1 + 6×3). "Send it to 00" = it's the lead. `011` = Box 01, second position.

### Hero Image for Box 00

The lead story (slot `00`) requires a hero image (688×300 placeholder currently). Three-tier sourcing:

1. **OG Image scrape** — pull `og:image` from source article URL at ingest time, store as post meta
2. **AI-generated** — if no OG image, generate a branded visual from headline/summary via image API or Canva MCP
3. **Manual upload** — prompt Yeti: "No hero available — upload one or approve AI-generated?"

---

## Task List

### S3.8.1 — Board Data Model
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 0.5h | **Depends On:** — | **Gate:** —

**Definition of Done:**
- Custom option or post meta structure stores the editorial board state
- Two layers: `jk_editorial_draft` (AI proposal) and `jk_editorial_published` (approved board)
- Each layer is a JSON object with 7 blocks (00–06), each containing up to 3 feed item IDs + metadata
- Schema:
  ```json
  {
    "generated": "2026-03-13T08:00:00",
    "published": null,
    "blocks": {
      "00": [
        {"slot": "000", "feed_item_id": 1234, "title": "...", "source": "...", "score": 94, "reason": "..."},
        {"slot": "001", "feed_item_id": 1235, ...},
        {"slot": "002", "feed_item_id": 1236, ...}
      ],
      "01": [...],
      ...
    }
  }
  ```
- Helper functions: `jk_get_draft_board()`, `jk_get_published_board()`, `jk_publish_board($board)`, `jk_get_slot($slot_id)`

---

### S3.8.2 — Draft Builder (Cron + On-Demand)
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 1.5h | **Depends On:** S3.8.1 | **Gate:** —

**Definition of Done:**
- WP-Cron job fires daily at 0800 ET (configurable via admin setting)
- Queries all scored feed items from last 48 hours
- Applies learned preference weights (from S3.8.5) to adjust scores
- Proposes a full 7-block layout with up to 3 items per block
- Writes proposal to `jk_editorial_draft` with reasoning per slot
- Also includes a "candidate pool" of next-best alternates (up to 10) not assigned to blocks
- Manual trigger available via admin button (S3.8.3) or WP REST endpoint
- Logs: draft generation timestamp, item count considered, preference adjustments applied

---

### S3.8.3 — Admin Approval UI
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 1.5h | **Depends On:** S3.8.1, S3.8.2 | **Gate:** —

**Definition of Done:**
- WP Admin page under The Markdown menu (or Settings submenu)
- Displays current draft board: 7 blocks, each showing up to 3 items with title, source, score, AI reasoning
- Slot IDs displayed (000–062) for quick reference
- Actions per item: move to different slot (dropdown or drag), remove from board, swap with candidate pool item
- Candidate pool section below the board showing unassigned high-scoring items
- **"Publish Board"** button — copies draft to published, triggers preference logging
- **"Refresh Draft"** button — re-runs draft builder on demand
- **"Clear Board"** button — empties published board (page falls back to live query or shows empty state)
- Visual diff: highlights items that differ from current published board
- Mobile-friendly (Yeti may approve from phone)

---

### S3.8.4 — Email Digest Notification
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 0.5h | **Depends On:** S3.8.2 | **Gate:** —

**Definition of Done:**
- Fires after draft builder completes (cron run only, not on-demand)
- Sends to Yeti's email (h3ndriks.j@gmail.com)
- Subject: `[The Markdown] Daily Board — {date} — {n} candidates`
- Body: plain-text or simple HTML showing proposed board layout
  - Each block with slot IDs, story titles, sources, scores, 1-line AI reasoning
  - Link to Admin Approval UI
  - Candidate pool summary
- "Reply with slot changes or 'approve' to publish" (stretch: parse email replies)
- Uses `wp_mail()` — no external service dependency

---

### S3.8.5 — Preference Logger & Learning Model
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 1h | **Depends On:** S3.8.1, S3.8.3 | **Gate:** —

**Definition of Done:**
- On every "Publish Board" action, logs the delta between AI proposal and Yeti's final board
- Stores structured preference data:
  ```json
  {
    "date": "2026-03-13",
    "moves": [
      {"item_id": 1234, "proposed_slot": "020", "final_slot": "000", "signal": "promoted"},
      {"item_id": 1237, "proposed_slot": "010", "final_slot": null, "signal": "rejected"}
    ],
    "approved_as_is": false,
    "approval_time_seconds": 120
  }
  ```
- Builds rolling preference weights from last 30 days of decisions:
  - Source preferences (which sources Yeti consistently promotes/demotes)
  - Domain preferences (which domains Yeti puts in lead vs lower blocks)
  - Topic keywords that correlate with promotion
  - Anti-patterns (story types Yeti consistently rejects)
- Preference weights fed into the draft builder's Claude API prompt as editorial context
- Over time: AI proposals converge toward Yeti's editorial taste → more "approve" clicks, fewer overrides

---

### S3.8.6 — Modified Editorial Shortcode
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 0.5h | **Depends On:** S3.8.1 | **Gate:** —

**Definition of Done:**
- Modify existing `[the_markdown_editorial]` shortcode (snippets 108-118) to read from published board
- If a published board exists and is < 24 hours old → render from board data
- If no published board or stale → fall back to current live query behavior (no regression)
- Each block pulls its 1–3 items from the board's slot data instead of running a fresh WP_Query
- Visual output unchanged — same cards, same layout, same styling
- Items display in slot order (000 first, 001 second, 002 third within each block)

---

### S3.8.7 — Hero Image Pipeline
**Type:** [FUNC] | **Owner:** DevOps | **Est:** 1h | **Depends On:** S3.8.1 | **Gate:** —

**Definition of Done:**
- At feed ingest time (or draft builder time), scrape `og:image` from source article URL
- Store hero image URL as feed item post meta (`_jk_hero_image`)
- If no OG image found, flag the item as `hero_needed`
- In the Admin Approval UI (S3.8.3), items with `hero_needed` show a warning badge
- Options for hero-less items:
  - AI-generate a branded image from headline (Canva MCP or image gen API)
  - Yeti uploads a custom image via the approval UI
  - Use a domain-branded fallback placeholder (e.g., "SPACE.COM" with brand colors)
- Box 00 (lead story) MUST have a hero image before publish — block publishing if missing
- Boxes 01–06 hero images are optional (cards render without them)

---

### S3.8.8 — Operational Playbook Update
**Type:** [FUNC] | **Owner:** Taskmaster | **Est:** 0.25h | **Depends On:** All above | **Gate:** —

**Definition of Done:**
- Update Operational Playbook with:
  - API username corrected: `h3ndriksj` (not `yetisecurity`)
  - New application password documented
  - Editorial approval workflow documented
  - Slot addressing scheme documented
  - Snippet inventory updated with new snippet IDs
- Commit to GitLab

---

## Sprint Exit Criteria

1. Draft builder fires on schedule and produces a valid 7-block proposal
2. Email digest arrives at Yeti's inbox with proposed board
3. Admin UI displays draft, allows reorder/swap, publishes on button click
4. Published board renders correctly on The Markdown page
5. Preference logger captures at least one approval cycle with delta data
6. Fallback works: if no board published, page renders via live query (no regression)
7. All new snippets committed to GitLab with IDs logged

## Estimated Snippet Count

| Snippet | Name | Est. Lines |
|---------|------|-----------|
| S121 | Board Data Model + Helpers | ~60 |
| S122 | Draft Builder (Cron + On-Demand) | ~120 |
| S123 | Admin Approval UI | ~150 |
| S124 | Email Digest Notification | ~80 |
| S125 | Preference Logger | ~100 |
| S126 | Editorial Shortcode Patch | ~40 |
| S127 | Hero Image Pipeline (OG scrape + fallback) | ~80 |

**Total: 7 new snippets, ~630 lines.** S123 (Admin UI) is the largest — may split into S123a (UI render) and S123b (AJAX handlers) to stay within lego block pattern.

---

## Risk Register

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Cron doesn't fire reliably on WP.com | Board never gets built | Add manual trigger as primary, cron as convenience |
| Email lands in spam | Yeti misses board proposals | Test deliverability; add admin UI as primary, email as secondary |
| Preference model overfits to early decisions | AI gets stuck in local optimum | Rolling 30-day window, not all-time. Yeti can reset preferences. |
| Board goes stale (Yeti doesn't approve for days) | Page shows old content | 24-hour staleness check; fall back to live query if board expired |

---

## Future Sprint Backlog (Out of S3.8 Scope)

| ID | Feature | Description | Depends On | Notes |
|----|---------|-------------|-----------|-------|
| BL-01 | Social embed loop | After Yeti posts a tweet via share button, capture the tweet URL and embed the tweet card under the corresponding item on The Markdown page. Twitter oEmbed API or iframe. Closes the curate→share→embed loop. | S3.8 share buttons (DR-0031) | Light lift — tweet ID from intent flow + oEmbed endpoint. No API key needed for public embeds. |
| BL-02 | Phase 2 content items | Commentary placeholder, featured images, PUSH buttons | S3.5/S3.6 editorial page | From S3.7 retro |
| BL-03 | Wave 3 security tasks | S3.12, S3.16–S3.21 | Overwatch gate review | Separate sprint |

## Carry-Forward Reference

Items from S3.7 retro that feed into S3.8:
- Phase 2 content items (commentary placeholder, featured images, PUSH buttons) — NOT in S3.8 scope
- Sprint Tracker reconciliation — do during S3.8 planning session
- Wave 3 security tasks (S3.12, S3.16–S3.21) — separate sprint, NOT in S3.8 scope
