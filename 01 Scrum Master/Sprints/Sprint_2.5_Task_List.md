# The Markdown — Sprint 2.5 — Integration Gate

**Status:** ⬜ NOT STARTED — 0/6 closed
**Total:** 6 tasks | ~4h estimated
**Gate Criteria:** S2.5.1 + S2.5.2 must pass with zero failures before Sprint 3 begins
**Source of Truth:** [GitLab Issues](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Last Updated:** 2026-03-10

---

## Purpose

Verify the core editorial pipeline (RSS → AI → dashboard → page render) and security stack still function correctly with 85+ active snippets before building Sprint 3 on top. Social API publish chain is **excluded** — deferred to Sprint 3.5 pay-to-play gate.

---

## Scope — What Gets Tested

**IN SCOPE (zero-cost editorial stack):**
- RSS ingest → AI scoring → editorial dashboard → promote → 7-block page render
- Commentary cards rendering (3 embed styles)
- Auto-refresh (AJAX polling)
- Token vault + nonce verification + rate limiting + audit logging
- REST endpoints health (/ns/v1/diagnostics/health, /ns/v1/feed-items)
- RSS output feed (/feed/the-markdown)
- Diagnostic logger under current 85+ snippet load
- Snippet load-order verification — no conflicts at current scale

**OUT OF SCOPE (deferred to Sprint 3.5):**
- Social API OAuth flows (X, LinkedIn, YouTube, Medium)
- AI content reformatter (1 → 5 platform versions)
- Quick-publish panel + publish dispatch
- Cross-platform posting + audit trail
- Instagram API (BLK-001)

---

## Tasks

### Verification (Must Pass)

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 1 | S2.5.1 | VERIFY | Full editorial chain smoke test — RSS ingest → AI score → dashboard display → promote → block assign → page render → commentary cards → auto-refresh → digest | DevOps + Foreman | ⬜ OPEN |
| 2 | S2.5.2 | VERIFY | Security stack re-verification — token vault, nonce, rate limit, audit log, diagnostic logger — with 85+ active snippets | Cyber Ops | ⬜ OPEN |

### Operational Readiness

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 3 | S2.5.3 | OPS | Snippet inventory update — current count, load order, dependency check at 85+ scale | DevOps | ⬜ OPEN |
| 4 | S2.5.4 | OPS | REST endpoint health check — all /ns/v1/ endpoints responding, correct payloads | DevOps | ⬜ OPEN |
| 5 | S2.5.5 | OPS | RSS output feed verification — /feed/the-markdown validates, items render correctly | DevOps | ⬜ OPEN |
| 6 | S2.5.6 | OPS | Update Operational Playbook for Sprint 3 — tool landscape review, platform changes, new connectors (retro action B10) | Taskmaster | ⬜ OPEN |

---

## Gate Criteria

Sprint 2.5 passes when:
1. S2.5.1 editorial chain smoke test — **zero failures**
2. S2.5.2 security stack — **all components verified functional**
3. No new blockers surfaced that would prevent Sprint 3 start

If either verification task fails, the defect is fixed and re-tested before Sprint 3 begins.

---

## Owner Summary

| Owner | VERIFY | OPS | Total |
|-------|--------|-----|-------|
| DevOps + Foreman | 1 | 3 | 4 |
| Cyber Ops | 1 | 0 | 1 |
| Taskmaster | 0 | 1 | 1 |

---

*Created by Taskmaster 2026-03-10. This is a verification gate — no new features, just confirming the foundation is solid before Sprint 3 build.*
