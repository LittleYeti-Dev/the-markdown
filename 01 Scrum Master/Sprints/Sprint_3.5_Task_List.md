# The Markdown — Sprint 3.5 — Pay-to-Play Decision Gate

**Status:** ⬜ NOT STARTED — 0/7 closed
**Total:** 7 tasks (3 features + 4 verification/decision tasks)
**Gate:** GO/NO-GO per feature — each requires cost/benefit decision before execution
**Source of Truth:** [GitLab Issues](https://gitlab.com/h3ndriks.j/JK.com-ver02/-/issues)
**Blocked By:** Sprint 3 close + individual GO/NO-GO decisions
**Last Updated:** 2026-03-10

---

## Purpose

Sprint 3.5 is a **decision gate for paid external integrations**. It holds all features that depend on paid API tiers or external review processes. No feature executes until Yeti makes an explicit GO/NO-GO with full cost visibility.

**Key decision:** If the social API paid tiers aren't worth it, Yeti posts manually and the entire social publish dispatch chain (S2.1–S2.5, S2.9, S2.14–S2.16) becomes optional infrastructure. The core editorial pipeline (RSS → AI → dashboard → page render) operates independently of social APIs.

---

## Feature Tasks (Require GO/NO-GO)

| # | ID | Type | Task | Owner | External Dependency | Cost Gate | Status |
|---|-----|------|------|-------|---------------------|-----------|--------|
| 1 | S2.3 | FUNC | Build Instagram API integration — Graph API via Facebook | DevOps | Facebook App Review (BLK-001, 2–4 week lead time) | Meta platform fees / App Review submission | ⬜ OPEN (carry-forward from S2) |
| 2 | S3.4 | FUNC | Build analytics dashboard — cross-platform engagement tracking | DevOps | Paid API tiers (X Analytics, possibly LinkedIn/YouTube) | Per-platform API subscription costs | ⬜ OPEN (moved from S3) |
| 3 | S3.7 | FUNC | Build social wall page — aggregated cross-platform feed | DevOps | Depends on same paid API access as S3.4 | Same cost gate as S3.4 | ⬜ OPEN (moved from S3) |

---

## Verification Tasks (Run After GO Decisions)

| # | ID | Type | Task | Owner | Status |
|---|-----|------|------|-------|--------|
| 4 | S3.5.1 | VERIFY | Social API publish chain — OAuth flow → token refresh → reformat → post → audit log (X, LinkedIn, YouTube, Medium + Instagram if GO) | DevOps + Foreman | ⬜ OPEN |
| 5 | S3.5.2 | VERIFY | AI reformatter chain — feed item → 5 platform versions → preview → quick-publish panel | DevOps | ⬜ OPEN |
| 6 | S3.5.3 | VERIFY | Publish dispatch end-to-end — select → edit → toggle platforms → dispatch → confirm → audit trail | Foreman | ⬜ OPEN |
| 7 | S3.5.4 | OPS | Cost/benefit analysis document — per-platform cost, capability, alternative, recommendation | Taskmaster | ⬜ OPEN |

---

## GO/NO-GO Criteria

Before each feature is greenlit, S3.5.4 must document:

1. **Monthly/annual cost** of required API tier or subscription
2. **What you get** — specific capabilities unlocked at that tier
3. **What you lose** without it — impact on The Markdown's functionality
4. **Alternative** — manual posting workflow, free tier limitations, or lower-cost workaround
5. **Recommendation** — GO, NO-GO, or DEFER with rationale

Yeti makes the call. Taskmaster documents each decision in the Decision Register.

**If NO-GO on social APIs:** S3.5.1–S3.5.3 verification tasks are cancelled. Social posting becomes a manual workflow. The Sprint 2 social infrastructure remains deployed but unused — no cost to leave it in place, no cost to remove it.

---

## Dependencies

| Task | Depends On |
|------|-----------|
| S2.3 | Facebook App Review submitted and approved (BLK-001) |
| S3.4 | S2.16 (publish dispatch), paid API access to X and other platforms |
| S3.7 | S2.1–S2.5 (social API integrations), same paid API access as S3.4 |
| S3.5.1 | At least one feature task gets GO decision |
| S3.5.2 | S3.5.1 passes |
| S3.5.3 | S3.5.1 passes |
| S3.5.4 | Sprint 3 close (research task, can start during S3) |

---

## Owner Summary

| Owner | FUNC | VERIFY | OPS | Total |
|-------|------|--------|-----|-------|
| DevOps | 3 | 2 | 0 | 5 |
| Foreman | 0 | 1 | 0 | 1 |
| Taskmaster | 0 | 0 | 1 | 1 |

---

*Created by Taskmaster 2026-03-10. This sprint is a cost-decision gate — no feature executes without explicit GO/NO-GO from Yeti. Social posting can always fall back to manual.*
