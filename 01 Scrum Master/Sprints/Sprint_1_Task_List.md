# The Markdown — Sprint 1 — Foundation Build

**Status:** ✅ COMPLETE — 23/23 closed
**Total:** 23 tasks | ~74h estimated
**Gates:** G1 ✅ | G2 ✅ | G3 ✅
**Source of Truth:** [GitLab Issues #8–#29](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Deployment:** WordPress.com Code Snippets plugin
**Last Synced:** 2026-03-09

---

## Gate 1 — Data Model (S1.1–S1.3) ✅ COMPLETE

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 8 | S1.1 | FUNC | Register `ns_feed_item` CPT with 15 meta fields | DevOps | S0 complete | ✅ Closed |
| 9 | S1.2 | FUNC | Register 4 custom taxonomies (`content_domain`, `thematic_arc`, `item_status`, `platform_published`) | DevOps | S1.1 | ✅ Closed |
| 10 | S1.3 | SEC | Input validation on all CPT meta fields — sanitize, validate, escape | Cyber Ops | S1.1 | ✅ Closed |

**Gate 1 Pass Criteria:** ✅ CPT registered, all 15 meta fields queryable, admin columns visible, all 4 taxonomies assignable, sanitize_callback on all register_meta calls, esc_html/esc_url on output.

---

## Gate 2 — RSS/AI Pipeline (S1.4–S1.11) ✅ COMPLETE

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 11 | S1.4 | FUNC | Configure WP RSS Aggregator Pro — purchase, install, map to CPT | DevOps | S1.1 | ✅ Closed |
| 12 | S1.5 | FUNC | Configure 40 starter feeds across 6 domains | Taskmaster | S1.4 | ✅ Closed |
| 13 | S1.6 | FUNC | Build auto-domain tagging — keyword rules assign `content_domain` | DevOps | S1.2, S1.4 | ✅ Closed |
| 14 | S1.7 | FUNC | Build dedup logic — URL matching + title similarity 90% | DevOps | S1.4 | ✅ Closed |
| 15 | S1.8 | SEC | RSS feed sanitization — strip scripts, validate URLs, limit payload 50KB | Cyber Ops | S1.4 | ✅ Closed |
| 16 | S1.9 | FUNC | Build Claude feed scoring — 4h cron, relevance 1-10 | DevOps | S1.1, S1.4 | ✅ Closed |
| 17 | S1.10 | FUNC | Build morning digest — daily 0600 CST, top 20 items | DevOps | S1.9 | ✅ Closed |
| 18 | S1.11 | SEC | Prompt injection detection — input filtering on RSS-to-LLM pipeline | Cyber Ops | S1.9 | ✅ Closed |

**Gate 2 Pass Criteria:** ✅ RSS imports to CPT, 40 feeds active, auto-domain tagging >90% accuracy, dedup working, all RSS content sanitized, Claude scoring functional, prompt injection test suite (20 payloads) all blocked.

---

## Gate 3 — Foundation (S1.12–S1.22) ✅ COMPLETE

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 19 | S1.12 | FUNC | Build editorial dashboard — admin feed list with filters | DevOps | S1.1, S1.2 | ✅ Closed (deployed) |
| 20 | S1.13 | FUNC | Build promote button + block assignment dropdown | DevOps | S1.12 | ✅ Closed (deployed) |
| 21 | S1.14 | FUNC | Build custom page template — 7-block layout rendering | Foreman | S1.1, S1.2 | ✅ Closed (deployed) |
| 22 | S1.15 | FUNC | Build commentary cards — 3 embed styles | DevOps | S1.14 | ✅ Closed (deployed) |
| 23 | S1.16 | FUNC | Build auto-refresh — AJAX polling every 15 min | DevOps | S1.14 | ✅ Closed (deployed) |
| 24 | S1.17 | SEC | Build token vault — encrypted wp_options, AES-256 | Cyber Ops | S0 complete | ✅ Closed (deployed) |
| 25 | S1.18 | SEC | Nonce verification on all admin AJAX endpoints | Cyber Ops | S1.12 | ✅ Closed (deployed) |
| 26 | S1.19 | SEC | Application passwords for REST API auth | Cyber Ops | S1.17 | ✅ Closed (deployed) |
| 27 | S1.20 | SEC | Claude API key rotation procedure — document + test | Cyber Ops | S1.17 | ✅ Closed (deployed) |
| 28 | S1.21 | SEC | Rate limiting on custom REST endpoints (60 req/min/IP) | DevOps | S1.14 | ✅ Closed (deployed) |
| 29 | S1.22 | SEC | Audit logging — custom DB table for all admin actions | DevOps | S1.12 | ✅ Closed (deployed) |

**Gate 3 Pass Criteria:** ✅ Editorial dashboard functional ✅, promote/block assignment working ✅, 7-block layout rendering ✅, commentary cards responsive ✅, AJAX refresh working ✅, token vault encrypted ✅, nonces verified ✅, app passwords enforced ✅, rate limiting active ✅, audit log capturing all actions ✅, diagnostic logger operational ✅.

### S1.23 (tail task) — Diagnostic Logger ✅

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 73 | S1.23 | FUNC | Application diagnostic logger — structured error/warning/info logging with admin viewer | Foreman | S1.22 | ✅ Closed (deployed) |

---

## Owner Summary

| Owner | FUNC | SEC | Total | Closed | Open |
|-------|------|-----|-------|--------|------|
| DevOps | 10 | 2 | 12 | 12 | 0 |
| Cyber Ops | 0 | 8 | 8 | 8 | 0 |
| Taskmaster | 1 | 0 | 1 | 1 | 0 |
| Foreman | 1 | 0 | 1 | 1 | 0 |

---

## Deployment Notes

- All PHP code deploys via **Code Snippets** plugin on WordPress.com
- Code Snippets execute as mu-plugins (must-use) — loaded before theme
- Each gate's code should be a separate snippet for isolation
- Naming convention: `S1-G1 Data Model`, `S1-G2 RSS Pipeline`, `S1-G3 Foundation`
- Token vault encryption key goes in wp-config.php (Yeti action via WP admin)
- Issues #19–#22 confirmed deployed to production (labeled `deployed` in GitLab)

---

*Synced from GitLab by Taskmaster 2026-03-09. GitLab is the source of truth.*
