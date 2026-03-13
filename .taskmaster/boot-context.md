# YKS Boot Context
## Cross-Project State Document
**Last updated:** 2026-03-13
**Sprint 0 Status:** COMPLETE — Signed off by Yeti 2026-03-13

---

## GitHub Infrastructure

| Resource | URL |
|----------|-----|
| Robo Stack Repo | https://github.com/LittleYeti-Dev/robo-stack |
| Content Ops Repo | https://github.com/LittleYeti-Dev/content-ops |
| The Markdown Repo | https://github.com/LittleYeti-Dev/the-markdown |
| Robo Stack Wiki | https://github.com/LittleYeti-Dev/robo-stack/wiki |
| Content Ops Wiki | https://github.com/LittleYeti-Dev/content-ops/wiki |
| The Markdown Wiki | https://github.com/LittleYeti-Dev/the-markdown/wiki |
| Project Board: Robo Stack | https://github.com/users/LittleYeti-Dev/projects/1 |
| Project Board: Content Ops | https://github.com/users/LittleYeti-Dev/projects/2 |
| Project Board: YKS Master | https://github.com/users/LittleYeti-Dev/projects/3 |

## Active Projects

| Project | Current Sprint | Status | Lead Persona |
|---------|---------------|--------|-------------|
| Robo Stack | Sprint 1 | ACTIVE — Epic 1: Local Dev Layer | Platform Architect |
| Content Ops | Sprint 0 | Complete — awaiting Robo Stack S1 progress | Taskmaster |

## Sprint 1 — Robo Stack (ACTIVE)

**Sprint Goal:** Working local development environment with K8s, CI/CD, and security baseline — all reproducible from repo.
**Duration:** 2 weeks (2026-03-13 to 2026-03-27)
**Epic:** E1 — Local Development Layer

### Stories (GitHub Issues #9–#16)

| Story | Type | Agent | Status |
|-------|------|-------|--------|
| S1.1: Process Design | Process | Claude Code | TODO — gates all build stories |
| S1.2: Workstation Setup | Build | Copilot | TODO (blocked by S1.1) |
| S1.3: Local K8s Cluster | Build | Copilot | TODO (blocked by S1.1 + EV1.1) |
| S1.4: CI/CD Pipeline | Build | Copilot | TODO (blocked by S1.1) |
| S1.5: Security Baseline | Build | Copilot | TODO (blocked by S1.1) |
| EV1.1: K3s vs Minikube | Eval | Claude Code | TODO |
| S1.6: Copilot Test | Build | Copilot | TODO |
| TP1: Touchpoint | Touchpoint | Cowork | TODO |

### Agent Prompts
All Sprint 1 prompts written: `wiki/Sprint-1/Sprint_1_Agent_Prompts.md`

### Personas Activated
- Platform Architect (Sprint 1)
- AI Integration Specialist (Sprint 1)
- DevSecOps Engineer (Sprint 1)

## Open Items Awaiting Yeti

1. **S1.1 — Process Design approval**: Must approve Epic 1 process doc before build starts
2. **EV1.1 — K3s vs Minikube**: Needs Yeti sign-off on selection
3. **Content Ops Sprint 1 scope**: Define PoC scope (blog-based Signal workflow)

## AI Agent Workflow

| Task Type | Primary Agent | Fallback |
|-----------|--------------|----------|
| Sprint orchestration & planning | Claude (Cowork/Desktop) | — |
| Code completion in IDE | GitHub Copilot | TabbyML (offline) |
| Code review | Copilot Chat / Claude API | — |
| Architecture design | Claude Code | — |
| Agentic coding tasks | Copilot Agent Mode | Claude Code |
| Issue triage & backlog grooming | Claude (Cowork) | Copilot |
| CI/CD pipeline authoring | Copilot + GitHub Actions | Claude |
| Security scanning | GitHub native (Dependabot, code scanning) | — |

## Cross-Project Dependencies

- Platform decision (EV0.1) — **RESOLVED** (GitHub)
- Wiki standard (EV0.2) — **RESOLVED**
- PM tooling (EV0.5) — **RESOLVED** (GitHub Issues + Projects)
- Persona boot infra (EV0.7) — **COMPLETE**
- Sprint 0 → Robo Stack S1: **RESOLVED** — Yeti signed off 2026-03-13
- Sprint 0 → Content Ops S1: Sprint 0 complete + Robo Stack S1 running — **IN PROGRESS**

## Sprint Sequence

1. **Robo Stack Sprint 1** — ACTIVE (2026-03-13 to 2026-03-27)
2. **Content Ops Sprint 1** — begins after Robo Stack S1 is running

## DevOps Standards (Established Sprint 0)

| Standard | Rule |
|----------|------|
| Documentation | Markdown in Git. Wiki updated same day as work. |
| Source control | GitHub. Branch protection on main. Feature branches: `feature/{project}-{epic}-{desc}` |
| Infrastructure | All infra as code. No manual setup. |
| CI/CD | GitHub Actions. Every project maintains automated pipeline in YAML. |
| Security | Least-privilege. Secrets in env vars / managers. Dependency scanning via Dependabot. |
| Sprint cadence | 2-week sprints. Demo-able increment each sprint. |
| Naming | Epics: E1-En. Stories: S{epic}.{seq}. Eval gates: EV{sprint}.{seq}. Touchpoints: TP{n}. |
| Quality gates | No "Done" without acceptance criteria met. |
| Wiki | Updated from Day 1. Decision docs committed same day. |
| PM Tooling | GitHub Issues (backlog) + GitHub Projects (boards). YKS label taxonomy enforced. |

## Latest Retrospective Notes
- Sprint 0 retrospective: smooth foundation setup. Key learning: process-first design avoids rework (lesson from The Markdown project).

## Unresolved Action Items
- Complete S1.1 (Epic 1 process design) — gates all build work
- Complete EV1.1 (K3s vs Minikube eval) — gates S1.3
- Begin Content Ops Sprint 1 scoping after Robo Stack S1 is underway
