# YKS Boot Context
## Cross-Project State Document
**Last updated:** 2026-03-13
**Sprint 0 Status:** IN PROGRESS

---

## Active Projects

| Project | Current Sprint | Status | Lead Persona |
|---------|---------------|--------|-------------|
| Robo Stack | Sprint 0 | In Progress — foundational setup | Taskmaster |
| Content Ops | Sprint 0 | In Progress — foundational setup (follows Robo Stack) | Taskmaster |

## Sprint 0 — Shared Foundation

Sprint 0 gates both projects. No project advances to Sprint 1 without Sprint 0 completion and Yeti sign-off.

### Evaluation Gates

| Gate | Description | Status | Decision |
|------|-------------|--------|----------|
| EV0.1 | Platform Decision (GitLab vs GitHub) | OPEN — Awaiting Yeti evaluation | Current: GitLab |
| EV0.2 | Wiki Standard & Day-One Setup | IN PROGRESS | — |
| EV0.3 | Local Repository + Sync Setup | IN PROGRESS | Current remote: GitLab |
| EV0.4 | AI Agent Integration | PENDING | — |
| EV0.5 | Project Management Tooling | OPEN — Awaiting Yeti evaluation | — |
| EV0.6 | Taskmaster Cross-Project Awareness | IN PROGRESS | — |
| EV0.7 | Persona Boot Infrastructure | IN PROGRESS | — |

### Upcoming Touchpoint
- **TP0: Sprint 0 Touchpoint with Yeti** — scheduled after all eval gates complete
- Agenda: platform decision review, repo/wiki demo, AI agent demo, board review, boot context review, go/no-go for Robo Stack Sprint 1

## Open Decisions Awaiting Yeti

1. **EV0.1 — GitLab vs GitHub**: Decision doc needs Yeti hands-on evaluation and sign-off
2. **EV0.5 — Project Management Tooling**: GitHub/GitLab Issues vs Notion vs Linear

## Cross-Project Dependencies

- Platform decision (EV0.1) affects both projects equally
- Wiki standard (EV0.2) must be consistent across both projects
- Project management tooling (EV0.5) must support unified taxonomy for both
- Persona boot infrastructure (EV0.7) serves both projects from shared `.taskmaster/`

## Sprint Sequence After Sprint 0

1. **Robo Stack Sprint 1** — Day 1: activate personas, begin Epic 1 process design
2. **Content Ops Sprint 1** — begins after Robo Stack is running; activates Content Ops personas

## DevOps Standards (Established Sprint 0)

| Standard | Rule |
|----------|------|
| Documentation | Markdown in Git. Wiki updated same day as work. |
| Source control | Branch protection on main. Feature branches: `feature/{project}-{epic}-{desc}` |
| Infrastructure | All infra as code. No manual setup. |
| CI/CD | Every project maintains automated pipeline in YAML. |
| Security | Least-privilege. Secrets in env vars / managers. Dependency scanning. |
| Sprint cadence | 2-week sprints. Demo-able increment each sprint. |
| Naming | Epics: E1-En. Stories: S{epic}.{seq}. Eval gates: EV{sprint}.{seq}. Touchpoints: TP{n}. |
| Quality gates | No "Done" without acceptance criteria met. |
| Wiki | Updated from Day 1. Decision docs committed same day. |

## Latest Retrospective Notes
- None yet (Sprint 0 in progress)

## Unresolved Action Items
- Complete all EV0.x gates
- Prepare TP0 touchpoint agenda
- Get Yeti sign-off on platform and PM tooling decisions
