# YKS Master Project Index
## Yeti Knowledge Systems — All Projects

**Last Updated:** 2026-03-13
**Current Phase:** Sprint 0 — Foundational Setup

---

## Projects

### 1. Robo Stack — Hybrid AI Development Stack
| Field | Value |
|-------|-------|
| Status | Sprint 0 — IN PROGRESS (Lead Project) |
| Wiki | [Robo Stack Wiki](Robo%20Stack/wiki/Home.md) |
| Project Request | [Whitepaper PDF](Robo%20Stack/hybrid_ai_dev_stack_master_architecture_whitepaper.pdf) |
| Status File | [.taskmaster/status/robo-stack.md](../.taskmaster/status/robo-stack.md) |
| Code Repo | TBD (pending EV0.1 platform decision) |
| Sprint 1 Start | After Sprint 0 completion + Yeti sign-off |

### 2. Content Ops — Non Sequitur Content Refinery Platform
| Field | Value |
|-------|-------|
| Status | Sprint 0 — IN PROGRESS (Follows Robo Stack) |
| Wiki | [Content Ops Wiki](Content%20Ops/wiki/Home.md) |
| Project Request | [Content Refinery PDF](Content%20Ops/nonsequitur_content_refinery_project_request.pdf) |
| Status File | [.taskmaster/status/content-ops.md](../.taskmaster/status/content-ops.md) |
| Code Repo | TBD (pending EV0.1 platform decision) |
| Sprint 1 Start | After Robo Stack Sprint 1 is running |

---

## Cross-Project Resources

| Resource | Location |
|----------|----------|
| Boot Context | [.taskmaster/boot-context.md](../.taskmaster/boot-context.md) |
| Project Instructions (Standing Orders) | [Sprint 0/Project_Instructions.md](Sprint%200/Project_Instructions.md) |
| Sprint 0 Checklist | [Sprint 0/Sprint_0_Checklist.md](Sprint%200/Sprint_0_Checklist.md) |
| Cross-Project Status | [.taskmaster/status/cross-project.md](../.taskmaster/status/cross-project.md) |
| Evaluation Scorecard | [Project_Evaluation_Scorecard.xlsx](../Project_Evaluation_Scorecard.xlsx) |
| Evaluation Report | [Project_Evaluation_Report.pdf](../Project_Evaluation_Report.pdf) |
| DevOps Standards | See Sprint 0 Checklist, Section: DevOps Standards |

---

## Sprint 0 Decision Log

| Gate | Decision | Status | Doc |
|------|----------|--------|-----|
| EV0.1 | Platform (GitLab vs GitHub) | OPEN | [platform-decision.md](Robo%20Stack/wiki/Sprint-0/platform-decision.md) |
| EV0.2 | Wiki Standard | ESTABLISHED | [wiki-standard.md](Robo%20Stack/wiki/Sprint-0/wiki-standard.md) |
| EV0.3 | Repo Setup | IN PROGRESS | — |
| EV0.4 | AI Agent Integration | DOCUMENTED | [ai-agent-setup.md](Robo%20Stack/wiki/Sprint-0/ai-agent-setup.md) |
| EV0.5 | PM Tooling | OPEN | [project-management-decision.md](Robo%20Stack/wiki/Sprint-0/project-management-decision.md) |
| EV0.6 | Taskmaster Awareness | IN PROGRESS | This document + boot context |
| EV0.7 | Persona Boot Infra | COMPLETE | [.taskmaster/](../.taskmaster/) |

---

## Sprint Sequence

```
Sprint 0 (now) ──→ Robo Stack Sprint 1 ──→ Content Ops Sprint 1
     │                    │                        │
     └── Shared foundation   └── Personas activate    └── CO personas activate
         for both projects      Epic 1 process design    PoC: blog Signal workflow
```

## Open Decisions Awaiting Yeti
1. **EV0.1** — GitLab vs GitHub (hands-on evaluation needed)
2. **EV0.5** — Project management tooling (GitHub/GitLab Issues vs Notion vs Linear)
