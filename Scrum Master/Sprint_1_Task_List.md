# Sprint 1 — Foundation Build

**Status:** IN PROGRESS (Gate-by-Gate Execution)
**Total:** 22 tasks | ~70h estimated
**Gates:** G1 (Data Model) → G2 (RSS/AI) → G3 (Foundation)
**Source of Truth:** [GitLab Issues #8–#29](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Deployment:** WordPress.com Code Snippets plugin

---

## Gate 1 — Data Model (S1.1–S1.3)

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 8 | S1.1 | FUNC | Register `ns_feed_item` CPT with 15 meta fields | DevOps | S0 complete | ⬜ |
| 9 | S1.2 | FUNC | Register 4 custom taxonomies (`content_domain`, `thematic_arc`, `item_status`, `platform_published`) | DevOps | S1.1 | ⬜ |
| 10 | S1.3 | SEC | Input validation on all CPT meta fields — sanitize, validate, escape | Cyber Ops | S1.1 | ⬜ |

**Gate 1 Pass Criteria:** CPT registered, all 15 meta fields queryable, admin columns visible, all 4 taxonomies assignable, sanitize_callback on all register_meta calls, esc_html/esc_url on output.

---

## Gate 2 — RSS/AI Pipeline (S1.4–S1.11)

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 11 | S1.4 | FUNC | Configure WP RSS Aggregator Pro — purchase, install, map to CPT | DevOps | S1.1 | ⬜ |
| 12 | S1.5 | FUNC | Configure 40 starter feeds across 6 domains | Taskmaster | S1.4 | ⬜ |
| 13 | S1.6 | FUNC | Build auto-domain tagging — keyword rules assign `content_domain` | DevOps | S1.2, S1.4 | ⬜ |
| 14 | S1.7 | FUNC | Build dedup logic — URL matching + title similarity 90% | DevOps | S1.4 | ⬜ |
| 15 | S1.8 | SEC | RSS feed sanitization — strip scripts, validate URLs, limit payload 50KB | Cyber Ops | S1.4 | ⬜ |
| 16 | S1.9 | FUNC | Build Claude feed scoring — 4h cron, relevance 1-10 | DevOps | S1.1, S1.4 | ⬜ |
| 17 | S1.10 | FUNC | Build morning digest — daily 0600 CST, top 20 items | DevOps | S1.9 | ⬜ |
| 18 | S1.11 | SEC | Prompt injection detection — input filtering on RSS-to-LLM pipeline | Cyber Ops | S1.9 | ⬜ |

**Gate 2 Pass Criteria:** RSS imports to CPT, 40 feeds active, auto-domain tagging >90% accuracy, dedup working, all RSS content sanitized, Claude scoring functional, prompt injection test suite (20 payloads) all blocked.

---

## Gate 3 — Foundation (S1.12–S1.22)

| # | ID | Type | Task | Owner | Depends | Status |
|---|-----|------|------|-------|---------|--------|
| 19 | S1.12 | FUNC | Build editorial dashboard — admin feed list with filters | DevOps | S1.1, S1.2 | ⬜ |
| 20 | S1.13 | FUNC | Build promote button + block assignment dropdown | DevOps | S1.12 | ⬜ |
| 21 | S1.14 | FUNC | Build custom page template — 7-block layout rendering | Foreman | S1.1, S1.2 | ⬜ |
| 22 | S1.15 | FUNC | Build commentary cards — 3 embed styles | DevOps | S1.14 | ⬜ |
| 23 | S1.16 | FUNC | Build auto-refresh — AJAX polling every 15 min | DevOps | S1.14 | ⬜ |
| 24 | S1.17 | SEC | Build token vault — encrypted wp_options, AES-256 | Cyber Ops | S0 complete | ⬜ |
| 25 | S1.18 | SEC | Nonce verification on all admin AJAX endpoints | Cyber Ops | S1.12 | ⬜ |
| 26 | S1.19 | SEC | Application passwords for REST API auth | Cyber Ops | S1.17 | ⬜ |
| 27 | S1.20 | SEC | Claude API key rotation procedure — document + test | Cyber Ops | S1.17 | ⬜ |
| 28 | S1.21 | SEC | Rate limiting on custom REST endpoints (60 req/min/IP) | DevOps | S1.14 | ⬜ |
| 29 | S1.22 | SEC | Audit logging — custom DB table for all admin actions | DevOps | S1.12 | ⬜ |

**Gate 3 Pass Criteria:** Editorial dashboard functional, promote/block assignment working, 7-block layout rendering, commentary cards responsive, AJAX refresh working, token vault encrypted, nonces verified, app passwords enforced, rate limiting active, audit log capturing all actions.

---

## Owner Summary

| Owner | FUNC | SEC | Total |
|-------|------|-----|-------|
| DevOps | 10 | 2 | 12 |
| Cyber Ops | 0 | 8 | 8 |
| Taskmaster | 1 | 0 | 1 |
| Foreman | 1 | 0 | 1 |

---

## Deployment Notes

- All PHP code deploys via **Code Snippets** plugin on WordPress.com
- Code Snippets execute as mu-plugins (must-use) — loaded before theme
- Each gate's code should be a separate snippet for isolation
- Naming convention: `S1-G1 Data Model`, `S1-G2 RSS Pipeline`, `S1-G3 Foundation`
- Token vault encryption key goes in wp-config.php (Yeti action via WP admin)

---

*Managed by Taskmaster. Gate reviews by Cyber Ops.*
