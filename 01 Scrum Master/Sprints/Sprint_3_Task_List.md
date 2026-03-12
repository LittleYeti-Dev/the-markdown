# The Markdown — Sprint 3 — Scale & Polish

**Status:** ⬜ NOT STARTED — 0/20 closed
**Total:** 20 tasks (18 original + CF-1 permalink flush + CF-3 RSS auto-discovery) | ~55h estimated
**Gates:** G6 (Visual & Infrastructure) → G7 (Final Cyber Gate) → S3.5 Integration Gate
**Source of Truth:** [GitLab Issues](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Last Updated:** 2026-03-10
**Execution:** Wave-based (3 waves), execution prompts per wave, Claude Code for code, Cowork for scrum

**Note:** S3.4 (analytics dashboard) and S3.7 (social wall) moved to Sprint 3.5 — pay-to-play gate. S2.3 (Instagram) also deferred to Sprint 3.5. See Sprint_3.5_Task_List.md.

---

## Brand Spec — The Markdown Visual Identity

Locked for S3.1 Canva template design and all Sprint 3 visual work.

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

## Carry-Forward Items (from Sprint 2.5 Retro)

| ID | Item | Action | Status |
|----|------|--------|--------|
| CF-1 | Permalink flush | Save Settings → Permalinks to activate `/feed/the-markdown` | ⬜ Wave 1 |
| CF-2 | Claude API key | Yeti provisioning — store in Token Vault via `ns_get_api_key('claude')` | ⬜ Yeti action (key obtained, needs vault storage) |
| CF-3 | RSS auto-discovery | Add `<link rel="alternate" type="application/rss+xml">` snippet | ⬜ Wave 1 |
| CF-4 | BLK-001 Instagram | Facebook App Review — deferred to Sprint 3.5 | ➡️ S3.5 |
| CF-5 | GitLab sync discipline | GitLab push is now a gate exit criterion, not optional | ✅ Process change |

---

## Wave 1 — Independent Tasks (No Blockers, Start Immediately)

All tasks have zero inter-dependencies. Can run in parallel. Target: complete before Wave 2 starts.

**Execution:** Single execution prompt covering all Wave 1 tasks. Claude Code for snippet deployment, Cowork for design/scrum work.

### Functional

| # | ID | Type | Task | Owner | Notes | Status |
|---|-----|------|------|-------|-------|--------|
| 52 | S3.1 | FUNC | Build 6 Canva domain templates — branded visual cards per brand spec above | Taskmaster | Logo D, dark theme, 6 content domains | ⬜ OPEN |
| 56 | S3.5 | FUNC | Build feed health monitor — detect broken feeds + alerts | DevOps | Depends only on S1.4 (done) | ⬜ OPEN |
| 57 | S3.6 | FUNC | Configure subdomain — markdown.justin-kuiper.com | Taskmaster | **DNS via Squarespace** — CNAME to WordPress.com. Start early for propagation. | ⬜ OPEN |
| 59 | S3.8 | FUNC | Build about page update — baseball card refresh | Taskmaster | Standalone, no dependencies | ⬜ OPEN |
| — | CF-1 | OPS | Flush permalinks — Settings → Permalinks → Save | Yeti | 30-second manual action | ⬜ OPEN |
| — | CF-3 | FUNC | RSS auto-discovery snippet — `<link rel="alternate">` in site head | DevOps | New snippet, Lego block pattern | ⬜ OPEN |

### Security

| # | ID | Type | Task | Owner | Priority | Status |
|---|-----|------|------|-------|----------|--------|
| 60 | S3.9 | SEC | GitLab CI/CD pipeline security — protected branches, signed commits | Cyber Ops | Medium | ⬜ OPEN |
| 61 | S3.10 | SEC | WordPress plugin audit — remove unused, update all, allowlist | Cyber Ops | Medium | ⬜ OPEN |
| 62 | S3.11 | SEC | Database backup automation — daily encrypted backups | DevOps | Medium | ⬜ OPEN |
| 63 | S3.12 | SEC | File permission hardening — wp-content, uploads, themes | Cyber Ops | High | ⬜ OPEN |

**Wave 1 total: 10 tasks** (6 FUNC + 4 SEC)

---

## Wave 2 — Dependent Tasks (Require Wave 1 Completions)

These depend on Wave 1 FUNC tasks being done. Performance, QA, and SEO run after all functional work is deployed.

**Execution:** Single execution prompt. Starts after Wave 1 sign-off.

| # | ID | Type | Task | Depends On | Owner | Status |
|---|-----|------|------|-----------|-------|--------|
| 53 | S3.2 | FUNC | Configure Canva MCP — auto-generate cards from templates | S3.1 templates done | DevOps | ⬜ OPEN |
| 54 | S3.3 | FUNC | Build archive page — historical editions with date/domain filter | S1.14 (done) + Wave 1 design | DevOps | ⬜ OPEN |
| 64 | S3.13 | FUNC | Performance optimization — caching, lazy load, CDN evaluation | All FUNC complete | DevOps | ⬜ OPEN |
| 65 | S3.14 | FUNC | Mobile responsive QA — test all pages on 3 breakpoints | All FUNC complete | Taskmaster | ⬜ OPEN |
| 66 | S3.15 | FUNC | SEO configuration — meta tags, OG tags, structured data | All FUNC complete | DevOps | ⬜ OPEN |

**Wave 2 total: 5 tasks** (5 FUNC)

---

## Wave 3 — Gate 7: Final Cyber Gate (Runs After Everything)

The capstone security gate. Pen test and OPSEC review validate the entire stack. Must pass before project can be declared production-ready.

**Execution:** Single execution prompt. Starts after Wave 2 sign-off + all SEC tasks closed.

| # | ID | Type | Task | Owner | Priority | Status |
|---|-----|------|------|-------|----------|--------|
| 67 | S3.16 | SEC | Penetration test — OWASP Top 10 scan on all endpoints | Cyber Ops | Critical | ⬜ OPEN |
| 68 | S3.17 | SEC | Incident response plan — document response procedures | Cyber Ops | Medium | ⬜ OPEN |
| 69 | S3.18 | SEC | Security monitoring setup — alerts for suspicious activity | Cyber Ops | Medium | ⬜ OPEN |
| 70 | S3.19 | SEC | Final cyber gate review — all 7 gates pass | Cyber Ops | Critical | ⬜ OPEN |
| 71 | S3.20 | SEC | OPSEC review — verify no secrets in repos, logs, or public pages | Cyber Ops | High | ⬜ OPEN |

**Wave 3 total: 5 tasks** (5 SEC)

---

## Execution Approach

**Same pattern as Sprint 2 — validated and proven.**

1. **Execution prompts per wave** — single-document handoff with status, file list, task definitions, done criteria, constraints
2. **Claude Code for code tasks** — snippet deployment via REST API (Rank 1 tool), WordPress admin via browser only if REST can't do it
3. **Cowork for scrum artifacts** — tracker updates, retros, dashboards, GitLab sync
4. **Agents prompt for credentials at session start** — GitLab PAT + WordPress App Password + Claude API key (now provisioned)
5. **GitLab sync is a gate exit criterion** — not optional, not deferred, not "when asked"

### Wave Execution Sequence

```
Wave 1 (10 tasks) → Wave 1 sign-off
    ↓
Wave 2 (5 tasks) → Wave 2 sign-off
    ↓
Wave 3 / Gate 7 (5 tasks) → Gate 7 sign-off
    ↓
Sprint 3.5 Integration Gate (6 tasks) → Project production-ready
```

### Key Constraints

- **Lego block architecture** — all new snippets target <80 lines, ceiling ~150 for complex UI (DR-0016, DR-0024)
- **REST API-first deployment** — no browser automation unless REST genuinely can't do it
- **Operational Playbook v2.0** — boot-time reference for all agents
- **WordPress.com platform** — no SFTP, no wp-config.php, WAF active, deploy via Code Snippets

---

## Owner Summary

| Owner | Wave 1 | Wave 2 | Wave 3 | Total |
|-------|--------|--------|--------|-------|
| DevOps | 3 | 3 | 0 | 6 |
| Cyber Ops | 3 | 0 | 5 | 8 |
| Taskmaster | 4 | 1 | 0 | 5 |
| Yeti | 1 (CF-1) | 0 | 0 | 1 |

---

## Sprint 2 Retro Action Items Addressed in Sprint 3

| ID | Action | Addressed By |
|----|--------|-------------|
| B1 | Submit Facebook App Review | Deferred to S3.5 — decision: provision or descope |
| B2 | Set GitLab milestone due dates | Wave 1 prep task |
| B3 | Scripted sprint close-out | Sprint 3 candidate |
| B4 | Sprint 2.5 integration gate | ✅ DONE |
| B5 | Enforce GitLab sync | Process change — gate exit criterion |
| B6 | Refine Lego block size guidance | Document in Wave 1 execution prompt |
| B7 | Review monolithic Sprint 1 snippets | Refactor only what Wave 1/2 touches |
| B8 | Push dashboards to GitLab Pages | Wave 2 candidate |
| B9 | External dependency forcing function | Applied to BLK-001 in S3.5 |
| B10 | Update Operational Playbook | ✅ DONE (v2.0) |

---

*Updated by Taskmaster 2026-03-10. Sprint 3 is UNLOCKED. Wave 1 is ready to execute. GitLab is the source of truth.*
