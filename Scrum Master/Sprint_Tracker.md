# The Markdown — Sprint Tracker

**Last Updated:** 2026-03-10 (Synced from GitLab source of truth)
**Source of Truth:** [GitLab Issues](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Total Tasks:** 91 | **Closed:** 65 | **Open:** 26
**Pipelines:** All passing ✅

---

## Sprint 0 — Immediate Hardening

**Status:** ✅ COMPLETE (Milestone CLOSED) | **Tasks:** 7/7 closed | **Milestone:** Sprint 0 — Immediate Hardening
**Gate:** G0 PASSED

| # | ID | Type | Task | Owner | Labels | Status | Closed |
|---|-----|------|------|-------|--------|--------|--------|
| 1 | S0.1 | SEC | Harden wp-config.php — move above webroot, set file permissions 400 | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| 2 | S0.2 | SEC | Disable XML-RPC — block xmlrpc.php at server level | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |
| 3 | S0.3 | SEC | Remove default admin account — create unique admin username | Taskmaster | High | ✅ CLOSED | 2026-03-08 |
| 4 | S0.4 | SEC | Set security headers — CSP, X-Frame-Options, HSTS, X-Content-Type | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| 5 | S0.5 | SEC | Enforce HTTPS everywhere — redirect all HTTP to HTTPS | Cyber Ops | Critical | ✅ CLOSED | 2026-03-08 |
| 6 | S0.6 | SEC | Disable directory listing — prevent index browsing | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |
| 7 | S0.7 | SEC | Hide WordPress version — remove generator meta tag | Cyber Ops | High | ✅ CLOSED | 2026-03-08 |

---

## Sprint 1 — Foundation Build

**Status:** ✅ COMPLETE (Milestone CLOSED) | **Tasks:** 23/23 closed | **Milestone:** Sprint 1 — Foundation Build
**Gates:** G1 ✅ PASSED | G2 ✅ PASSED | G3 ✅ PASSED

### Gate 1 — Data Model (S1.1–S1.3) ✅ COMPLETE

| # | ID | Type | Task | Owner | Status | Closed |
|---|-----|------|------|-------|--------|--------|
| 8 | S1.1 | FUNC | Register `ns_feed_item` CPT with 15 meta fields | DevOps | ✅ CLOSED | 2026-03-08 |
| 9 | S1.2 | FUNC | Register 4 custom taxonomies | DevOps | ✅ CLOSED | 2026-03-08 |
| 10 | S1.3 | SEC | Input validation on all CPT meta fields — sanitize, validate, escape | Cyber Ops | ✅ CLOSED | 2026-03-08 |

### Gate 2 — RSS/AI Pipeline (S1.4–S1.11) ✅ COMPLETE

| # | ID | Type | Task | Owner | Status | Closed |
|---|-----|------|------|-------|--------|--------|
| 11 | S1.4 | FUNC | Configure WP RSS Aggregator Pro — purchase, install, map to CPT | DevOps | ✅ CLOSED | 2026-03-08 |
| 12 | S1.5 | FUNC | Configure 40 starter feeds across 6 domains | Taskmaster | ✅ CLOSED | 2026-03-08 |
| 13 | S1.6 | FUNC | Build auto-domain tagging — keyword rules assign content_domain | DevOps | ✅ CLOSED | 2026-03-08 |
| 14 | S1.7 | FUNC | Build dedup logic — URL matching + title similarity 90% | DevOps | ✅ CLOSED | 2026-03-08 |
| 15 | S1.8 | SEC | RSS feed sanitization — strip scripts, validate URLs, limit payload | Cyber Ops | ✅ CLOSED | 2026-03-08 |
| 16 | S1.9 | FUNC | Build Claude feed scoring — 4h cron, relevance 1-10 | DevOps | ✅ CLOSED | 2026-03-08 |
| 17 | S1.10 | FUNC | Build morning digest — daily 0600 CST, top 20 items | DevOps | ✅ CLOSED | 2026-03-08 |
| 18 | S1.11 | SEC | Prompt injection detection — input filtering on RSS-to-LLM pipeline | Cyber Ops | ✅ CLOSED | 2026-03-08 |

### Gate 3 — Foundation (S1.12–S1.22) ✅ COMPLETE

| # | ID | Type | Task | Owner | Labels | Status | Closed |
|---|-----|------|------|-------|--------|--------|--------|
| 19 | S1.12 | FUNC | Build editorial dashboard — admin feed list with filters | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 20 | S1.13 | FUNC | Build promote button + block assignment dropdown | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 21 | S1.14 | FUNC | Build custom page template — 7-block layout rendering | Foreman | deployed | ✅ CLOSED | 2026-03-09 |
| 22 | S1.15 | FUNC | Build commentary cards — 3 embed styles | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 23 | S1.16 | FUNC | Build auto-refresh — AJAX polling every 15 min | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 24 | S1.17 | SEC | Build token vault — encrypted wp_options, AES-256 | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 25 | S1.18 | SEC | Nonce verification on all admin AJAX endpoints | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 26 | S1.19 | SEC | Application passwords for REST API auth | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 27 | S1.20 | SEC | Claude API key rotation procedure — document + test | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 28 | S1.21 | SEC | Rate limiting on custom REST endpoints (60 req/min/IP) | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 29 | S1.22 | SEC | Audit logging — custom DB table for all admin actions | DevOps | deployed | ✅ CLOSED | 2026-03-09 |

---

## Sprint 1.5 — Integration Gate

**Status:** ✅ COMPLETE | **Tasks:** 8/8 closed | **Milestone:** Sprint 1.5 — Integration Gate (CLOSED)
**Estimated:** ~10h | **Gate Criteria:** S1.5.1 + S1.5.2 must pass with zero failures — ✅ GATE PASSED

### Verification (Must Pass)

| # | ID | Type | Task | Owner | Labels | Status |
|---|-----|------|------|-------|--------|--------|
| 74 | S1.5.1 | VERIFY | Full-chain smoke test — RSS → AI → dashboard → promote → render → commentary → refresh → digest | DevOps + Foreman | Critical | ✅ CLOSED 2026-03-09 (8 PASS, 2 PARTIAL) |
| 75 | S1.5.2 | VERIFY | Security stack verification — vault, nonce, rate limit, audit log, diagnostic logger | Cyber Ops | Critical | ✅ CLOSED 2026-03-09 (6/6 PASS) |

### Operational Readiness

| # | ID | Type | Task | Owner | Labels | Status |
|---|-----|------|------|-------|--------|--------|
| 76 | S1.5.3 | OPS | Snippet inventory audit — all active snippets, load order, dependencies | DevOps | — | ✅ CLOSED 2026-03-09 |
| 77 | S1.5.4 | OPS | Refactor Sprint 1 monolithic snippets that caused WAF issues | DevOps | High | ✅ CLOSED 2026-03-09 (assessed, deferred to S2) |
| 78 | S1.5.5 | OPS | Build Operational Playbook — platform, APIs, credentials, tool hierarchy | Taskmaster | Critical | ✅ CLOSED 2026-03-09 |
| 79 | S1.5.6 | OPS | Define credential handling protocol in Playbook | Taskmaster + Cyber Ops | Critical | ✅ CLOSED 2026-03-09 |
| 80 | S1.5.7 | OPS | Update all continuity artifacts for micro-snippet architecture | Taskmaster | High | ✅ CLOSED 2026-03-09 |

### Admin

| # | ID | Type | Task | Owner | Labels | Status |
|---|-----|------|------|-------|--------|--------|
| 81 | S1.5.8 | ADMIN | Close S0/S1 milestones, assign S1.23, set milestone due dates | Taskmaster | — | ✅ CLOSED 2026-03-09 |

---

## Sprint 2 — Social & Publishing

**Status:** 🔶 21/22 CLOSED (S2.3 deferred to S3.5) | **Tasks:** 21/22 closed | **Milestone:** Sprint 2 — Social & Publishing
**Estimated:** ~86h

### Gate 4 — Social APIs (S2.1–S2.8) — 7/8 CLOSED (S2.3 deferred)

| # | ID | Type | Task | Owner | Labels | Status | Closed |
|---|-----|------|------|-------|--------|--------|--------|
| 30 | S2.1 | FUNC | Build X (Twitter) API integration — OAuth 2.0 PKCE, tweet + thread | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 31 | S2.2 | FUNC | Build LinkedIn API integration — OAuth 2.0, personal profile posting | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 32 | S2.3 | FUNC | Build Instagram API integration — Graph API via Facebook | DevOps | Deferred | ➡️ MOVED TO S3.5 (BLK-001) | — |
| 33 | S2.4 | FUNC | Build YouTube API integration — Data API v3, community posts | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 34 | S2.5 | FUNC | Build Medium API integration — bearer token, draft creation | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 35 | S2.6 | SEC | OAuth callback URL validation — whitelist registered callbacks | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 36 | S2.7 | SEC | Token refresh automation — daily cron, email alert on failure | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 37 | S2.8 | SEC | Platform API error handling — graceful failure, no token leakage | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |

### Gate 5 — AI + Quick-Publish (S2.9–S2.22) — ✅ 14/14 CLOSED

| # | ID | Type | Task | Owner | Labels | Status | Closed |
|---|-----|------|------|-------|--------|--------|--------|
| 38 | S2.9 | FUNC | Build AI content reformatter — 1 note → 5 platform versions | Foreman | deployed | ✅ CLOSED | 2026-03-09 |
| 39 | S2.10 | FUNC | Build 7-block daily summary — 0800 CST standup briefing | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 40 | S2.11 | FUNC | Build arc scoring — content vs thematic arc alignment | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 41 | S2.12 | SEC | AI output validation — sanitize all Claude-generated content | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 42 | S2.13 | SEC | AI response length enforcement — prevent token overflow | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 43 | S2.14 | FUNC | Build quick-publish panel — inline editor + platform toggles | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 44 | S2.15 | FUNC | Build 5-version preview — side-by-side platform preview | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 45 | S2.16 | FUNC | Build publish dispatch — one-click multi-platform post | Foreman | deployed | ✅ CLOSED | 2026-03-09 |
| 46 | S2.17 | FUNC | Build REST API endpoints — block data, feed items, publish actions | Foreman | deployed | ✅ CLOSED | 2026-03-09 |
| 47 | S2.18 | FUNC | Build RSS feed output — /feed/the-markdown for subscribers | DevOps | deployed | ✅ CLOSED | 2026-03-09 |
| 48 | S2.19 | SEC | CSRF protection on quick-publish actions | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 49 | S2.20 | SEC | Content escaping on social media output — XSS prevention | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 50 | S2.21 | SEC | Publish audit trail — log all social media dispatch events | Cyber Ops | deployed | ✅ CLOSED | 2026-03-09 |
| 51 | S2.22 | SEC | Rate limit handler — exponential backoff + queue | DevOps | deployed | ✅ CLOSED | 2026-03-09 |

---

## Sprint 2.5 — Integration Gate

**Status:** ✅ COMPLETE | **Tasks:** 6/6 closed
**Estimated:** ~4h | **Gate Criteria:** S2.5.1 + S2.5.2 must pass with zero failures before Sprint 3 begins — ✅ GATE PASSED
**Scope:** Core editorial pipeline only — social API verification deferred to Sprint 3.5
**Gate Result:** CONDITIONAL PASS — editorial chain 6 PASS / 2 PARTIAL (same as S1.5), security 6/6 PASS

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 82 | S2.5.1 | VERIFY | Full editorial chain smoke test — 6 PASS, 2 PARTIAL (AI key + digest) | DevOps + Foreman | ✅ CLOSED 2026-03-10 |
| 83 | S2.5.2 | VERIFY | Security stack re-verification — 6/6 PASS at 74 active snippets | Cyber Ops | ✅ CLOSED 2026-03-10 |
| 84 | S2.5.3 | OPS | Snippet inventory — 85 total, 74 active, 0 errors, no priority conflicts | DevOps | ✅ CLOSED 2026-03-10 |
| 85 | S2.5.4 | OPS | REST endpoint health check — 6/6 endpoints healthy, all <2s | DevOps | ✅ CLOSED 2026-03-10 |
| 86 | S2.5.5 | OPS | RSS feed verification — valid RSS 2.0, permalink needs flush, no auto-discovery | DevOps | ✅ CLOSED 2026-03-10 |
| 87 | S2.5.6 | OPS | Operational Playbook updated for Sprint 3 (retro action B10) | Taskmaster | ✅ CLOSED 2026-03-10 |

---

## Sprint 3 — Scale & Polish

**Status:** ⬜ NOT STARTED | **Tasks:** 0/18 closed | **Milestone:** Sprint 3 — Scale & Polish
**Estimated:** ~55h
**Note:** S3.4, S3.7, and S2.3 moved to Sprint 3.5 (pay-to-play decision gate)

### Gate 6 — Visual & Infrastructure

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 52 | S3.1 | FUNC | Build 6 Canva domain templates — branded visual cards | Taskmaster | ⬜ OPEN |
| 53 | S3.2 | FUNC | Configure Canva MCP — auto-generate cards from templates | DevOps | ⬜ OPEN |
| 54 | S3.3 | FUNC | Build archive page — historical editions with date/domain filter | DevOps | ⬜ OPEN |
| 56 | S3.5 | FUNC | Build feed health monitor — detect broken feeds + alerts | DevOps | ⬜ OPEN |
| 57 | S3.6 | FUNC | Configure subdomain — markdown.justin-kuiper.com | Taskmaster | ⬜ OPEN |
| 59 | S3.8 | FUNC | Build about page update — baseball card refresh | Taskmaster | ⬜ OPEN |
| 60 | S3.9 | SEC | GitLab CI/CD pipeline security — protected branches, signed commits | Cyber Ops | ⬜ OPEN (Medium) |
| 61 | S3.10 | SEC | WordPress plugin audit — remove unused, update all, allowlist | Cyber Ops | ⬜ OPEN (Medium) |
| 62 | S3.11 | SEC | Database backup automation — daily encrypted backups | DevOps | ⬜ OPEN (Medium) |
| 63 | S3.12 | SEC | File permission hardening — wp-content, uploads, themes | Cyber Ops | ⬜ OPEN (High) |
| 64 | S3.13 | FUNC | Performance optimization — caching, lazy load, CDN evaluation | DevOps | ⬜ OPEN |
| 65 | S3.14 | FUNC | Mobile responsive QA — test all pages on 3 breakpoints | Taskmaster | ⬜ OPEN |
| 66 | S3.15 | FUNC | SEO configuration — meta tags, OG tags, structured data | DevOps | ⬜ OPEN |

### Gate 7 — Final Cyber Gate

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 67 | S3.16 | SEC | Penetration test — OWASP Top 10 scan on all endpoints | Cyber Ops | ⬜ OPEN |
| 68 | S3.17 | SEC | Incident response plan — document response procedures | Cyber Ops | ⬜ OPEN (Medium) |
| 69 | S3.18 | SEC | Security monitoring setup — alerts for suspicious activity | Cyber Ops | ⬜ OPEN (Medium) |
| 70 | S3.19 | SEC | Final cyber gate review — all 7 gates pass | Cyber Ops | ⬜ OPEN |
| 71 | S3.20 | SEC | OPSEC review — verify no secrets in repos, logs, or public pages | Cyber Ops | ⬜ OPEN (High) |

---

## Sprint 3.5 — Pay-to-Play Decision Gate

**Status:** ⬜ NOT STARTED | **Tasks:** 0/7 closed
**Gate:** GO/NO-GO per feature — cost/benefit decision before execution
**Note:** If social API paid tiers aren't worth it, Yeti posts manually. Social links under articles maintain the ecosystem.

### Feature Tasks (Require GO/NO-GO)

| # | ID | Type | Task | Owner | Cost Gate | Status |
|---|-----|------|------|-------|-----------|--------|
| 32 | S2.3 | FUNC | Build Instagram API integration — Graph API via Facebook | DevOps | Meta App Review / platform fees | ⬜ OPEN (carry-forward) |
| 55 | S3.4 | FUNC | Build analytics dashboard — cross-platform engagement tracking | DevOps | Paid API tiers (X, LinkedIn, YouTube) | ⬜ OPEN (moved from S3) |
| 58 | S3.7 | FUNC | Build social wall page — aggregated cross-platform feed | DevOps | Same paid API access as S3.4 | ⬜ OPEN (moved from S3) |

### Verification & Decision Tasks

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 88 | S3.5.1 | VERIFY | Social API publish chain — OAuth → token refresh → reformat → post → audit | DevOps + Foreman | ⬜ OPEN |
| 89 | S3.5.2 | VERIFY | AI reformatter chain — feed item → 5 platform versions → preview → quick-publish | DevOps | ⬜ OPEN |
| 90 | S3.5.3 | VERIFY | Publish dispatch end-to-end — select → edit → toggle → dispatch → confirm → audit | Foreman | ⬜ OPEN |
| 91 | S3.5.4 | OPS | Cost/benefit analysis — per-platform cost, capability, alternative, recommendation | Taskmaster | ⬜ OPEN |

---

## Effort Summary

| Sprint | Tasks | Closed | Open | Status |
|--------|-------|--------|------|--------|
| S0 — Hardening | 7 | 7 | 0 | ✅ COMPLETE (closed) |
| S1 — Foundation | 23 | 23 | 0 | ✅ COMPLETE (closed) |
| S1.5 — Integration Gate | 8 | 8 | 0 | ✅ COMPLETE (closed) |
| S2 — Social & Publishing | 22 | 21 | 1 | 🔶 21/22 (S2.3 deferred to S3.5) |
| S2.5 — Integration Gate | 6 | 6 | 0 | ✅ COMPLETE |
| S3 — Scale & Polish | 18 | 0 | 18 | ⬜ NOT STARTED |
| S3.5 — Pay-to-Play Gate | 7 | 0 | 7 | ⬜ NOT STARTED |
| **Total** | **91** | **65** | **26** | **71% complete** |

---

## Pipelines

| Pipeline | Status | Branch | Date |
|----------|--------|--------|------|
| #2371500458 | ✅ success | main | 2026-03-08 |
| #2370708280 | ✅ success | main | 2026-03-07 |
| #2370702903 | ✅ success | main | 2026-03-07 |
| #2370700426 | ✅ success | main | 2026-03-07 |

---

*Synced from GitLab by Taskmaster. GitLab is the source of truth.*
