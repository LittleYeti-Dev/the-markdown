# YKS Project Instructions
## Standing Orders for All Projects Under Yeti Knowledge Systems

**Last updated:** March 2026
**Applies to:** All active RFIs (Robo Stack, Content Ops, and any future projects)

---

## 1. Taskmaster Role

The Taskmaster is the orchestration layer that drives all other personas. It is the first thing that boots and the last thing that checks out.

### Boot Requirements
At the start of every session, the Taskmaster MUST load and be aware of:
- **All active projects** — not just the one being worked on
- Current sprint for each project and its status
- Active stories and their completion state
- Open evaluation gates awaiting Yeti decision
- Upcoming touchpoints and their agendas
- Cross-project dependencies (what one project needs from the other)
- Latest retrospective notes and unresolved action items
- DevOps operational standards (Section 6 of the Evaluation Report)

### Taskmaster Responsibilities
- Assigns work to the correct persona based on the current story type
- Enforces process-first design: no build story starts without a process doc
- Enforces evaluation gates: no tool adopted without a decision doc
- Tracks requirements harvested from capabilities as they're built
- Prepares touchpoint agendas before each Yeti review
- Maintains the cross-project status dashboard
- Flags cross-project conflicts or dependency issues

### Taskmaster Does NOT:
- Make platform or tool decisions (those require Yeti sign-off via eval gates)
- Skip evaluation gates to save time
- Start build work before the process for that epic is designed and approved
- Work on one project while ignoring the state of the other

---

## 2. Persona Building — Sprint 1 Requirement

Personas are not just documentation. They are operational configurations that define HOW work gets done.

### Sprint 1 Day 1: Build and activate personas for the lead project (Robo Stack)

Each persona must have:
- **Name and role** — what this persona is responsible for
- **Activation trigger** — what story types or tasks activate this persona
- **Tools and access** — what tools, repos, and systems this persona uses
- **Output expectations** — what deliverables this persona produces
- **Quality standard** — what "done" looks like for this persona's work
- **Handoff protocol** — how this persona passes work to the next persona

### Robo Stack Personas (activate Sprint 1)
1. **Platform Architect** — activated by Build stories in Epics 1-5
2. **AI Integration Specialist** — activated by AI-related Build stories and Eval gates
3. **DevSecOps Engineer** — activated by Security stories and CI/CD pipeline work
4. **Solutions Consultant** — activated post-Epic 5 for commercialization

### Content Ops Personas (activate when Content Ops begins Sprint 1)
1. **Content Systems Architect** — activated by all Build stories
2. **Creator / Editorial Lead** — activated by content production tasks
3. **Distribution & Social Operator** — activated by Phase 2+ distribution stories
4. **Multimedia Producer** — activated by Phase 3 multimedia stories

### The Taskmaster decides which persona is active for each story.

---

## 3. Process-First Design — Non-Negotiable

**Lesson from the Markdown project:** The workflow wasn't defined until the final sprint, causing rework.

**Standing order:** Every epic begins with a process design story BEFORE any build stories execute.

The process design story must produce:
- A documented workflow (in Markdown, committed to wiki)
- Clear inputs, outputs, and stage gates
- Tool requirements identified (feeding into eval gates)
- Yeti review and approval

Only after the process doc is approved does the Taskmaster assign build stories for that epic.

---

## 4. Best-of-Breed Evaluation Gates

**Standing order:** No tool, platform, or technology is adopted on assumption.

Every significant technology choice goes through an evaluation gate:
- Minimum 2 options compared
- Criteria relevant to BOTH projects (even if only one project triggers the eval)
- Decision document committed to wiki Decision-Log/
- Yeti sign-off required
- Hands-on testing required (not just desk research)

The Taskmaster is responsible for ensuring eval gates are completed before dependent build stories start.

---

## 5. Wiki — Day One, Every Day

**Standing order:** Documentation happens the day the work happens. Not retroactively.

What gets committed to wiki:
- Every decision doc (eval gates)
- Every process design doc
- Every sprint retrospective
- Every touchpoint summary (including Yeti sign-off)
- Architecture decisions and their rationale
- Setup guides and runbooks

The wiki is the project's memory. If it's not in the wiki, it didn't happen.

---

## 6. Touchpoints with Yeti

**Standing order:** Every sprint ends with a structured touchpoint.

Touchpoint agenda (every sprint):
1. Demo of working increment
2. Review eval gate decisions made this sprint
3. Requirements harvest — what we learned that changes what's ahead
4. Process validation — does the designed workflow actually work?
5. Go/no-go for next sprint

Nothing advances to the next sprint without Yeti's explicit go/no-go.

---

## 7. Cross-Project Awareness

**Standing order:** Both projects are always visible, even when only one is active.

The Taskmaster maintains:
- A master project index linking to both projects
- A cross-project status dashboard updated each sprint
- Awareness of how decisions in one project affect the other
- A "boot context" document that any new session can read to understand full state

---

## 8. Boot from Local Clone — Continuity Between Environments

**Standing order:** All personas (including the Taskmaster) boot from the local Git clone.

### Why
- One source of truth: persona configs, boot context, project state, and instructions live in the repo
- Any environment (local machine, cloud IDE, CI runner, AI agent session) pulls the same context
- Version-controlled persona evolution — persona configurations are tracked in Git history
- Offline capability — personas can boot and operate without cloud connectivity
- Sync on push — local changes propagate to cloud; cloud changes pull down to local

### Implementation
```
~/yks/
  .taskmaster/
    boot-context.md          ← Full cross-project state (Taskmaster reads this first)
    project-instructions.md  ← This document (standing orders)
    personas/
      taskmaster.md          ← Taskmaster config, triggers, responsibilities
      platform-architect.md  ← Robo Stack persona
      ai-specialist.md       ← Robo Stack persona
      devsecops.md           ← Robo Stack persona
      solutions-consultant.md
      content-architect.md   ← Content Ops persona
      creator-lead.md        ← Content Ops persona
      distribution-ops.md    ← Content Ops persona
      multimedia-producer.md ← Content Ops persona
    status/
      robo-stack.md          ← Current sprint, active stories, blockers
      content-ops.md         ← Current sprint, active stories, blockers
      cross-project.md       ← Dependencies, shared decisions, conflicts
  robo-stack/                ← Code repo
  robo-stack.wiki/           ← Wiki repo
  content-ops/               ← Code repo
  content-ops.wiki/          ← Wiki repo
```

### Boot sequence
1. Taskmaster reads `.taskmaster/boot-context.md` — gets full project state
2. Taskmaster reads `.taskmaster/project-instructions.md` — gets standing orders
3. Taskmaster reads `.taskmaster/status/` — gets current sprint state for both projects
4. Taskmaster identifies current story and activates the appropriate persona from `.taskmaster/personas/`
5. Activated persona reads its config and begins work
6. At session end, Taskmaster updates `status/` files and commits to local clone
7. Push syncs state to remote — next session (any environment) gets the updated context

### Sprint 0 task
- [ ] Create `.taskmaster/` directory structure in local clone
- [ ] Populate initial persona configs
- [ ] Populate initial boot-context.md
- [ ] Test boot sequence: fresh session reads context and understands full project state
- [ ] Confirm sync: local commit → push → pull from different environment → same state

---

## 9. Adding to These Instructions

These project instructions are a living document. To add a standing order:
1. Raise it during a Yeti touchpoint OR flag it directly to be added
2. Document the new instruction here with the date added
3. Commit updated instructions to both project wikis
4. Taskmaster incorporates the new instruction into its boot context

---

## Change Log

| Date | Change | Added By |
|------|--------|----------|
| 2026-03-13 | Initial project instructions created | Yeti + Claude |
