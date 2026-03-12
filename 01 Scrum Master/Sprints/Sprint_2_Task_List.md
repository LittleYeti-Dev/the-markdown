# The Markdown — Sprint 2 — Social & Publishing

**Status:** 🔶 21/22 CLOSED (S2.3 blocked — BLK-001)
**Total:** 22 tasks | ~86h estimated
**Gates:** G4 ✅ (7/8) → G5 ✅ (14/14)
**Source of Truth:** [GitLab Issues #30–#51](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Last Synced:** 2026-03-10

---

## Gate 4 — Social API Integrations (S2.1–S2.8) — 7/8 CLOSED (S2.3 blocked)

| # | ID | Type | Task | Owner | Depends | Status | Closed |
|---|-----|------|------|-------|---------|--------|--------|
| 30 | S2.1 | FUNC | Build X (Twitter) API integration — OAuth 2.0 PKCE, tweet + thread | DevOps | S1 complete | ✅ CLOSED | 2026-03-09 |
| 31 | S2.2 | FUNC | Build LinkedIn API integration — OAuth 2.0, personal profile posting | DevOps | S1 complete | ✅ CLOSED | 2026-03-09 |
| 32 | S2.3 | FUNC | Build Instagram API integration — Graph API via Facebook | DevOps | S1 complete, FB App Review | ⬜ OPEN (BLK-001) | — |
| 33 | S2.4 | FUNC | Build YouTube API integration — Data API v3, community posts | DevOps | S1 complete | ✅ CLOSED | 2026-03-09 |
| 34 | S2.5 | FUNC | Build Medium API integration — bearer token, draft creation | DevOps | S1 complete | ✅ CLOSED | 2026-03-09 |
| 35 | S2.6 | SEC | OAuth callback URL validation — whitelist registered callbacks | Cyber Ops | S2.1–S2.5 | ✅ CLOSED | 2026-03-09 |
| 36 | S2.7 | SEC | Token refresh automation — daily cron, email alert on failure | Cyber Ops | S1.17 (token vault) | ✅ CLOSED | 2026-03-09 |
| 37 | S2.8 | SEC | Platform API error handling — graceful failure, no token leakage | Cyber Ops | S2.1–S2.5 | ✅ CLOSED | 2026-03-09 |

**Gate 4 Blocker:** #32 (Instagram) blocked by Facebook App Review (BLK-001).

---

## Gate 5 — AI & Quick-Publish (S2.9–S2.22) — ✅ 14/14 CLOSED

| # | ID | Type | Task | Owner | Depends | Status | Closed |
|---|-----|------|------|-------|---------|--------|--------|
| 38 | S2.9 | FUNC | Build AI content reformatter — 1 note → 5 platform versions | Foreman | S1.9 | ✅ CLOSED | 2026-03-09 |
| 39 | S2.10 | FUNC | Build 7-block daily summary — 0800 CST standup briefing | DevOps | S1.12, S1.14 | ✅ CLOSED | 2026-03-09 |
| 40 | S2.11 | FUNC | Build arc scoring — content vs thematic arc alignment | DevOps | S1.9 | ✅ CLOSED | 2026-03-09 |
| 41 | S2.12 | SEC | AI output validation — sanitize all Claude-generated content | Cyber Ops | S2.9 | ✅ CLOSED | 2026-03-09 |
| 42 | S2.13 | SEC | AI response length enforcement — prevent token overflow | Cyber Ops | S2.9 | ✅ CLOSED | 2026-03-09 |
| 43 | S2.14 | FUNC | Build quick-publish panel — inline editor + platform toggles | DevOps | S2.1–S2.5 | ✅ CLOSED | 2026-03-09 |
| 44 | S2.15 | FUNC | Build 5-version preview — side-by-side platform preview | DevOps | S2.9, S2.14 | ✅ CLOSED | 2026-03-09 |
| 45 | S2.16 | FUNC | Build publish dispatch — one-click multi-platform post | Foreman | S2.14 | ✅ CLOSED | 2026-03-09 |
| 46 | S2.17 | FUNC | Build REST API endpoints — block data, feed items, publish actions | Foreman | S1.1 | ✅ CLOSED | 2026-03-09 |
| 47 | S2.18 | FUNC | Build RSS feed output — /feed/the-markdown for subscribers | DevOps | S1.14 | ✅ CLOSED | 2026-03-09 |
| 48 | S2.19 | SEC | CSRF protection on quick-publish actions | Cyber Ops | S2.14 | ✅ CLOSED | 2026-03-09 |
| 49 | S2.20 | SEC | Content escaping on social media output — XSS prevention | Cyber Ops | S2.16 | ✅ CLOSED | 2026-03-09 |
| 50 | S2.21 | SEC | Publish audit trail — log all social media dispatch events | Cyber Ops | S2.16 | ✅ CLOSED | 2026-03-09 |
| 51 | S2.22 | SEC | Rate limit handler — exponential backoff + queue | DevOps | S2.1–S2.5 | ✅ CLOSED | 2026-03-09 |

---

## Owner Summary

| Owner | FUNC | SEC | Total |
|-------|------|-----|-------|
| DevOps | 9 | 2 | 11 |
| Cyber Ops | 0 | 8 | 8 |
| Foreman | 3 | 0 | 3 |

---

*Synced from GitLab by Taskmaster 2026-03-09. GitLab is the source of truth.*
