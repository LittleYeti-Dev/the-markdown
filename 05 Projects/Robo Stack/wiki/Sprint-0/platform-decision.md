# EV0.1: Platform Decision — GitLab vs GitHub
## Evaluation Gate | Sprint 0

**Date:** 2026-03-13
**Status:** OPEN — Awaiting Yeti evaluation and sign-off
**Affects:** Both projects (Robo Stack + Content Ops)

---

## Current State
- **Active platform:** GitLab (self-managed org: `gitlab.com/h3ndriks.j`)
- **Existing assets:** JK.com-ver02 repo with CI pipeline (`.gitlab-ci.yml`), wiki, multiple feature branches
- **Git remote:** `gitlab.com/h3ndriks.j/JK.com-ver02.git`

## Evaluation Criteria

| Criteria | GitLab | GitHub | Notes |
|----------|--------|--------|-------|
| AI Agent Integration | GitLab Duo (if available on plan) | GitHub Copilot + Copilot Workspace | — |
| CI/CD | GitLab CI (YAML, built-in) | GitHub Actions (YAML, marketplace) | — |
| Wiki | Built-in Git-backed wiki | Built-in Git-backed wiki | — |
| Project Boards | Issue boards (built-in) | Projects (built-in) | — |
| Security Features | SAST, dependency scanning, container scanning | Dependabot, code scanning, secret scanning | — |
| Cost | Current plan: TBD | Free tier + Copilot subscription | — |
| Migration Effort | N/A (already here) | Repo + wiki + CI migration required | — |
| Copilot/AI Quality | GitLab Duo vs Copilot hands-on test | Copilot hands-on test (min 2 hours) | — |

## Required Hands-On Tests
- [ ] Trial GitHub Copilot on a real Robo Stack task (minimum 2 hours)
- [ ] Trial GitHub Copilot Workspace on a planning/scaffolding task
- [ ] Evaluate GitLab Duo if not already tested
- [ ] Compare CI/CD experience on a real pipeline task

## Decision
**Pending Yeti evaluation and sign-off.**

| Field | Value |
|-------|-------|
| Decision | — |
| Rationale | — |
| Date | — |
| Signed off by | — |

---

*This document will be updated with the decision outcome after Yeti's hands-on evaluation.*
