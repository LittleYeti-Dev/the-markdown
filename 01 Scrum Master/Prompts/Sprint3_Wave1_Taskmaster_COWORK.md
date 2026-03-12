# Sprint 3 — Wave 1 Taskmaster Execution Prompt (COWORK)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 1 (Independent Tasks — No Blockers)
**Prepared by:** Taskmaster
**For:** Claude Cowork agent (Taskmaster role)

> **This is a handoff document.** It contains everything a fresh Cowork session needs to execute Wave 1 Taskmaster tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — GET YOUR CREDENTIALS

**Before touching ANY endpoint or running ANY code, ask Yeti for:**

1. **GitLab PAT** — `api` scope, for issue management
   - Project ID: `80070684`
   - Repo: `gitlab.com/h3ndriks.j/JK.com-ver02`
   - Assume any PAT is short-lived. Yeti rotates multiple times daily.

2. **WordPress Application Password** — for REST API verification only
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

**Do not proceed past this section until you have credentials.**

---

## 1. MISSION

Execute the Taskmaster-owned Wave 1 tasks for Sprint 3. These are **3 independent tasks** covering Canva template design, subdomain configuration, and about page refresh. All tasks are independent — zero inter-dependencies within this wave.

**Your tasks (3 total):**

| ID | Task | Type | GitLab Issue | Priority |
|----|------|------|-------------|----------|
| S3.1 | Build 6 Canva domain templates — branded visual cards per brand spec | FUNC | #52 | Medium |
| S3.6 | Configure subdomain — feed.justin-kuiper.com | FUNC | #57 | Medium |
| S3.8 | Build about page update — baseball card refresh | FUNC | #59 | Medium |

**Not your tasks (other owners):**
- S3.5, S3.11, CF-1, CF-3 → DevOps (Code)
- S3.9, S3.10, S3.12 → Cyber Ops (Code)

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No server-level file access.
- **Plugin management:** Via WordPress admin or REST API.
- **WAF active** — WordPress.com manages its own WAF.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Current active snippets:** 74 (of 85 total)

### Canva

- Use Canva MCP tools if available in your session
- If no Canva MCP, design in Canva directly via browser or ask Yeti to create manually
- Templates must follow the locked brand spec (see Section 3)

### DNS — Squarespace

- **Domain registrar:** Squarespace (manages DNS for `justin-kuiper.com`)
- DNS records for subdomains must be configured in Squarespace DNS settings
- Changes propagate within minutes to hours

### Tool Hierarchy (DR-0021)

1. **Canva MCP** — for template design (if available)
2. **WordPress REST API** — for about page content verification
3. **Squarespace DNS panel** — for subdomain configuration (browser)
4. **Browser automation** — LAST RESORT for anything else

**At the start of every task, state your tool choice and reasoning.**

---

## 3. BRAND SPEC — The Markdown Visual Identity

Locked for all Sprint 3 visual work. Use these values in all Canva templates.

| Property | Value |
|----------|-------|
| Logo | **Logo D — NON sequitur network nodes** (dark bg, cyan nodes) |
| Logo file | `06 Content Lab/logos/LogoD_badge_opt1.png` (+ opt2–4 variants) |
| Dark background | `#0d1117` → `#1a1a1a` range |
| Primary accent (cyan/blue) | `#06b6d4` → `#4a9eff` |
| Secondary accent (gold) | `#FFB302` → `#FFD700` |
| Text on dark | `#FFFFFF` (headings), `#C5C5C5` (body) |
| Fonts | Albert Sans (primary), DM Sans (secondary), Source Sans 3 (fallback) |
| Source | justin-kuiper.com dark theme + Non Sequitur logo suite |

---

## 4. TASK DETAILS

### Task 1: S3.1 — Build 6 Canva Domain Templates

**GitLab Issue:** #52
**Priority:** Medium

**What to do:**

Build 6 branded visual card templates in Canva — one per content domain in The Markdown. These templates will be used to auto-generate social media cards for each edition.

**The 6 content domains:**

1. **Tech & AI** — technology, artificial intelligence, machine learning
2. **Science** — research, discoveries, space, environment
3. **Business & Finance** — markets, startups, economic trends
4. **Culture & Society** — social trends, education, lifestyle
5. **Politics & Policy** — government, regulation, geopolitics
6. **Sports & Entertainment** — sports, media, gaming, pop culture

**Template requirements:**

- **Size:** Instagram post (1080×1080) or Twitter card (1200×628) — ask Yeti for preference
- **Layout:** Logo D in corner, domain name as header, space for headline text, dark background per brand spec
- **Color coding:** Each domain gets a subtle accent variation within the brand palette
- **Consistent structure:** Same layout across all 6, only domain name and accent color change
- **Text placeholders:** Headline (editable), domain tag, date, "The Markdown" branding

**Implementation approach:**
1. Design Template 1 (Tech & AI) as the master template
2. Duplicate 5 times, changing domain name and accent color
3. Save all 6 as Canva templates in a "The Markdown Templates" folder
4. Document template names and Canva links in results file

**Acceptance Criteria:**
- 6 templates created, one per domain
- All follow brand spec (Logo D, dark theme, correct colors)
- Consistent layout across all 6
- Templates saved and shareable

---

### Task 2: S3.6 — Configure Subdomain (feed.justin-kuiper.com)

**GitLab Issue:** #57
**Priority:** Medium

**What to do:**

Configure `feed.justin-kuiper.com` as a subdomain that points to the RSS feed for The Markdown.

**⚠️ CRITICAL: DNS is managed by Squarespace, NOT WordPress.com.**

The domain `justin-kuiper.com` is registered and DNS-managed through Squarespace. All DNS record changes must be made in the **Squarespace DNS settings panel**.

**Implementation steps:**

1. **Squarespace DNS configuration:**
   - Log into Squarespace → Domain settings → DNS settings for `justin-kuiper.com`
   - Add a CNAME record:
     - **Host:** `feed`
     - **Value:** Point to WordPress.com hosting endpoint (e.g., `justin-kuiper.com` or the WordPress.com CNAME target)
   - Alternatively, if a redirect is more appropriate:
     - Configure a URL redirect from `feed.justin-kuiper.com` to `https://justin-kuiper.com/feed/the-markdown`

2. **WordPress.com side:**
   - Verify the subdomain is recognized (may require WordPress.com domain mapping)
   - If WordPress.com doesn't support subdomain mapping on the current plan, document the limitation and use a redirect approach instead

3. **DNS propagation:**
   - After configuring, check propagation via `dig` or online DNS checker
   - Allow up to 48 hours for full propagation (usually much faster)

4. **Verification:**
   - `GET https://feed.justin-kuiper.com` should serve The Markdown RSS feed
   - If using redirect: verify 301/302 redirect to the correct feed URL

**Acceptance Criteria:**
- DNS record configured in Squarespace
- `feed.justin-kuiper.com` resolves (either directly or via redirect)
- RSS feed accessible at the subdomain URL
- Configuration documented in results file

---

### Task 3: S3.8 — About Page Update (Baseball Card Refresh)

**GitLab Issue:** #59
**Priority:** Medium

**What to do:**

Update the about page on `justin-kuiper.com` with a refreshed "baseball card" style layout. This is a content and design update to the existing about page.

**Implementation approach:**

1. **Audit current about page:**
   - View `https://justin-kuiper.com/about` (or equivalent)
   - Document current structure and content
   - Identify what needs updating

2. **Design refresh:**
   - "Baseball card" style — compact, visual, stat-heavy personal profile
   - Use brand spec colors and typography
   - Include: photo/avatar, name, role/title, key stats or highlights, brief bio, social links
   - Keep it concise — this is a personal brand card, not a full CV

3. **Content update:**
   - Ask Yeti for updated bio text, stats, and any new social links
   - Integrate The Markdown / Non Sequitur branding where appropriate

4. **Implementation:**
   - If the about page is a WordPress page → update via REST API or admin
   - If it requires custom HTML/CSS → prepare the code and deploy via Code Snippets or page editor

**Acceptance Criteria:**
- About page updated with baseball card layout
- Follows brand spec (colors, fonts, dark theme)
- Content is current and approved by Yeti
- Page loads correctly on desktop and mobile

---

## 5. EXECUTION ORDER

```
1. S3.6 (subdomain config)     — start early, DNS propagation takes time    ~30 min + wait
2. S3.1 (Canva templates)      — design work, most time-intensive           ~2-3h
3. S3.8 (about page update)    — content + design refresh                   ~1-2h
```

**Note:** Start S3.6 first because DNS propagation can take hours. Work on S3.1 and S3.8 while waiting.

---

## 6. OUTPUT FILES

After completing all tasks, produce a results file:

**File:** `01 Scrum Master/S3_Wave1_Taskmaster_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- Canva template links (6 templates)
- DNS configuration details (record type, values, propagation status)
- About page changes made
- Items requiring Yeti input (bio text, template size preference, etc.)
- Recommendations for Wave 2

---

## 7. DONE CRITERIA — Wave 1 Taskmaster

Wave 1 Taskmaster is DONE when:
- [ ] S3.1: 6 Canva domain templates created per brand spec
- [ ] S3.6: `feed.justin-kuiper.com` DNS configured in Squarespace, resolving or redirect active
- [ ] S3.8: About page updated with baseball card layout
- [ ] Results file created with evidence
- [ ] GitLab issues #52, #57, #59 closed (or updated with findings)

---

## 8. CONSTRAINTS & REMINDERS

- **Squarespace DNS** — the domain registrar is Squarespace, NOT WordPress.com. All DNS changes go through Squarespace.
- **Brand spec is locked** — do not deviate from the colors, fonts, or logo specified in Section 3.
- **Credential discipline** — hold credentials in memory, never log them, never commit them.
- **Ask Yeti** for any content decisions (bio text, template size preference, subdomain approach).
- **State your tool choice** at the start of every task with reasoning.

---

## 9. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `06 Content Lab/logos/` | Logo files for templates |

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 1 Taskmaster is unlocked. Execute in order, verify each task, push results.*
