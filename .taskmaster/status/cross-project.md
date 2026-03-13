# Cross-Project Status
**Last Updated:** 2026-03-13

---

## Shared Decisions (Sprint 0)

| Decision | Affects | Status | Outcome |
|----------|---------|--------|---------|
| Platform (GitLab vs GitHub) | Both | OPEN | Current: GitLab |
| Wiki Standard | Both | IN PROGRESS | Markdown in Git |
| PM Tooling | Both | OPEN | — |
| DevOps Standards | Both | ESTABLISHED | See boot-context.md |
| Sprint Cadence | Both | ESTABLISHED | 2-week sprints |
| Naming Convention | Both | ESTABLISHED | E{n}, S{epic}.{seq}, EV{sprint}.{seq}, TP{n} |

## Dependencies

| From | To | Dependency | Status |
|------|----|-----------|--------|
| Sprint 0 | Robo Stack S1 | Sprint 0 must complete with Yeti sign-off | BLOCKING |
| Sprint 0 | Content Ops S1 | Sprint 0 must complete + Robo Stack S1 running | BLOCKING |
| EV0.1 | Both repos | Platform choice determines repo hosting | BLOCKING |
| EV0.5 | Both backlogs | PM tool choice determines where backlogs live | BLOCKING |

## Conflicts
- None identified yet

## Shared Resources
- `.taskmaster/` directory serves both projects
- Wiki standard and DevOps standards apply to both
- Taskmaster persona orchestrates both projects
