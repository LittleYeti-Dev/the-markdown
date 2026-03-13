# Wiki Standard — Yeti Knowledge Systems
## EV0.2 Output | Applies to Both Projects

**Date:** 2026-03-13
**Status:** Established

---

## Structure (per project)
```
wiki/
  Home.md                    ← Project overview, links to all sections
  Sprint-0/
    platform-decision.md     ← EV0.1 output
    wiki-standard.md         ← This document
    project-management-decision.md  ← EV0.5 output
    ai-agent-setup.md        ← EV0.4 output
    repo-setup.md            ← EV0.3 output
    taskmaster-setup.md      ← EV0.6 output
    touchpoint-0.md          ← TP0 summary
  Sprint-{N}/                ← Populated as sprints execute
  Decision-Log/              ← All eval gate outputs
  Process-Docs/              ← All designed workflows
  Retrospectives/            ← Sprint retrospectives
```

## Naming Conventions

| Item | Format | Example |
|------|--------|---------|
| Sprint folders | `Sprint-{N}/` | `Sprint-0/`, `Sprint-1/` |
| Decision docs | `{topic}-decision.md` | `platform-decision.md` |
| Process docs | `{epic}-{process}-workflow.md` | `e1-local-dev-workflow.md` |
| Retrospectives | `sprint-{N}-retro.md` | `sprint-0-retro.md` |
| Touchpoints | `touchpoint-{N}.md` | `touchpoint-0.md` |

## Commit Message Format
```
{type}({project}): {description}

Types: docs, feat, fix, refactor, test, ci, chore
Projects: robo-stack, content-ops, shared
```

**Examples:**
- `docs(robo-stack): Add platform decision document`
- `feat(shared): Initialize .taskmaster/ boot infrastructure`
- `docs(content-ops): Add Sprint 1 retrospective`

## Rules
1. Documentation happens the day the work happens. Not retroactively.
2. Every decision doc committed same day as decision.
3. Every process design doc committed before build stories execute.
4. Every retrospective committed at sprint end.
5. Every touchpoint summary committed same day (including Yeti sign-off notation).
6. Mermaid diagrams supported for architecture docs.
