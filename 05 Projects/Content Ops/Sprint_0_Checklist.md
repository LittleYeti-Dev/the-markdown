# Sprint 0 — Foundational Setup Checklist
## Yeti Knowledge Systems | Gates Both Projects

**Duration:** 1 week
**Outcome:** Both project repos initialized, wikis live, local dev environment configured, AI agents integrated, platform decisions documented, taskmaster has full cross-project visibility.

---

## EV0.1: Platform Decision — GitLab vs GitHub

- [ ] Document current GitLab setup (repos, CI pipelines, wikis, integrations)
- [ ] Create GitHub organization or confirm existing account structure
- [ ] Trial GitHub Copilot on a real Robo Stack task (minimum 2-hour hands-on)
- [ ] Trial GitHub Copilot Workspace on a planning/scaffolding task
- [ ] Evaluate GitLab Duo if not already tested
- [ ] Complete comparison decision doc (use template in Decision-Log/)
- [ ] **Yeti sign-off on platform choice**

**Output:** `Sprint-0/platform-decision.md` committed to wiki

---

## EV0.2: Wiki Standard & Day-One Setup

### Wiki structure (per project repo)
```
wiki/
  Home.md                    ← Project overview, links to all sections
  Sprint-0/
    platform-decision.md
    wiki-standard.md
    project-management-decision.md
    ai-agent-setup.md
  Sprint-1/                  ← Populated as sprint executes
  Decision-Log/              ← All eval gate outputs
  Process-Docs/              ← All designed workflows
  Retrospectives/
```

### Setup tasks
- [ ] Initialize wiki repo for Robo Stack project
- [ ] Initialize wiki repo for Content Ops project
- [ ] Create Home.md for each with project overview and links
- [ ] Create wiki contribution guide (naming, commit message format, PR process)
- [ ] Confirm wiki supports Mermaid diagrams (architecture docs will need this)
- [ ] Set up local clone of wiki repos alongside code repos
- [ ] **Test round-trip: local edit → commit → push → verify on platform**

**Output:** `Sprint-0/wiki-standard.md` committed to both wikis

---

## EV0.3: Local Repository + GitHub Sync Setup

### Local environment
- [ ] Confirm Git installed and configured (name, email, SSH keys)
- [ ] Set up local directory structure:
  ```
  ~/yks/
    robo-stack/           ← Code repo
    robo-stack.wiki/      ← Wiki repo (separate Git repo)
    content-ops/          ← Code repo
    content-ops.wiki/     ← Wiki repo (separate Git repo)
  ```
- [ ] Configure Git remotes for both code and wiki repos
- [ ] Set up branch protection rules on main (both repos)
- [ ] Configure `.gitignore` templates for each project
- [ ] Test push/pull cycle for both code and wiki repos
- [ ] Set up Git hooks if needed (commit message format, pre-push checks)

### Sync verification
- [ ] Push a test commit to Robo Stack code repo → verify on GitHub/GitLab
- [ ] Push a test commit to Robo Stack wiki repo → verify wiki page renders
- [ ] Push a test commit to Content Ops code repo → verify on GitHub/GitLab
- [ ] Push a test commit to Content Ops wiki repo → verify wiki page renders

**Output:** All repos live and syncing. `Sprint-0/repo-setup.md` in wiki.

---

## EV0.4: AI Agent Integration — Ready to Go

### If GitHub selected:
- [ ] Install GitHub Copilot extension in VS Code
- [ ] Verify Copilot code completion is active
- [ ] Verify Copilot Chat is functional
- [ ] Test Copilot Workspace for task scaffolding
- [ ] Install and configure TabbyML as local/offline fallback
- [ ] Document agent configuration in wiki

### If GitLab selected:
- [ ] Enable GitLab Duo if available on plan
- [ ] Install GitLab VS Code extension
- [ ] Verify code suggestions are active
- [ ] Install and configure TabbyML as local/offline fallback
- [ ] Evaluate Claude/ChatGPT API integration for code review
- [ ] Document agent configuration in wiki

### Cross-platform (regardless of choice):
- [ ] Install Claude desktop app / API access configured
- [ ] Install and verify any additional AI tools (Cursor, Continue.dev, etc.)
- [ ] Create AI agent usage guide: which agent for which task
- [ ] **Test all agents against a real coding task from the Robo Stack backlog**

**Output:** `Sprint-0/ai-agent-setup.md` committed to Robo Stack wiki

---

## EV0.5: Project Management Tooling

- [ ] Evaluate GitHub/GitLab Issues + Boards vs Notion vs Linear
- [ ] Confirm tooling supports both projects with unified taxonomy
- [ ] Set up Kanban boards for both projects
- [ ] Import backlog items from Project_Evaluation_Scorecard.xlsx into boards
- [ ] Label all items with epic IDs (E1, E2, etc.) and story types (Build, Eval, Touchpoint)
- [ ] **Yeti sign-off on project management approach**

**Output:** `Sprint-0/project-management-decision.md` committed to wiki

---

## EV0.6: Taskmaster Cross-Project Awareness

**Requirement:** Whoever (or whatever) is driving sprint execution must have full visibility into BOTH projects at boot — not just the active one.

### Setup tasks
- [ ] Create a master project index page in wiki (or shared location) that links to:
  - Robo Stack backlog, wiki, repo, current sprint
  - Content Ops backlog, wiki, repo, current sprint
  - Sprint 0 decision log
  - DevOps operational standards
  - Evaluation scorecard
- [ ] Create a cross-project status dashboard (can be a simple Markdown table updated each sprint)
- [ ] Define the "boot context" — what information does the taskmaster need to see at the start of every session:
  - Current sprint for each project
  - Active stories and their status
  - Open evaluation gates awaiting decision
  - Upcoming touchpoints
  - Cross-project dependencies
  - Latest retrospective notes
- [ ] Store boot context in a consistent, findable location (e.g., `Home.md` of a shared wiki or root-level `STATUS.md`)
- [ ] **Test: Can a fresh session understand the full state of both projects from the boot context alone?**

**Output:** Master index + boot context document. `Sprint-0/taskmaster-setup.md` in wiki.

---

## EV0.7: Persona Boot Infrastructure (Local Clone)

**All personas boot from the local Git clone for environment continuity.**

### Setup tasks
- [ ] Create `.taskmaster/` directory in local workspace root
- [ ] Create `boot-context.md` — cross-project state document
- [ ] Create `project-instructions.md` — copy of standing orders (this will be the live version)
- [ ] Create `personas/` directory with initial configs:
  - [ ] `taskmaster.md` — orchestration config, boot sequence, responsibilities
  - [ ] `platform-architect.md` — Robo Stack lead persona
  - [ ] `ai-specialist.md` — AI integration persona
  - [ ] `devsecops.md` — security and ops persona
  - [ ] `solutions-consultant.md` — commercialization persona (dormant until Epic 6)
  - [ ] `content-architect.md` — Content Ops lead persona (dormant until CO Sprint 1)
  - [ ] `creator-lead.md` — editorial persona (dormant)
  - [ ] `distribution-ops.md` — social distribution persona (dormant)
  - [ ] `multimedia-producer.md` — multimedia persona (dormant)
- [ ] Create `status/` directory with initial state files:
  - [ ] `robo-stack.md` — Sprint 0 complete, Sprint 1 ready
  - [ ] `content-ops.md` — in planning, Sprint 1 not yet started
  - [ ] `cross-project.md` — shared decisions from Sprint 0
- [ ] Commit `.taskmaster/` to local clone
- [ ] Push to remote repo
- [ ] **Test: Pull from a different environment and verify full context loads**

**Sprint 1 Day 1:** Activate Robo Stack personas (Platform Architect, AI Specialist, DevSecOps). Taskmaster drives persona assignment per story type.

---

## TP0: Sprint 0 Touchpoint with Yeti

### Agenda
1. Platform decision review (GitLab vs GitHub) — confirm selection
2. Demo: local repos syncing with remote, wikis live and editable
3. Demo: AI agents active and functional in IDE
4. Review wiki structure and documentation standard
5. Review project management board with imported backlogs
6. Review taskmaster boot context — confirm cross-project visibility
7. **Go/no-go for Robo Stack Sprint 1**

### Acceptance criteria for Sprint 0 completion
- [ ] Platform selected and documented
- [ ] Both project repos live with syncing wikis
- [ ] AI agents installed, configured, and tested
- [ ] Project management boards set up with backlogs imported
- [ ] Wiki structure initialized for both projects
- [ ] Taskmaster boot context created and tested
- [ ] DevOps operational standards documented
- [ ] Yeti sign-off recorded in `Sprint-0/touchpoint-0.md`

---

## DevOps Standards (Established in Sprint 0)

These standards apply to both projects from this point forward:

| Standard | Rule |
|----------|------|
| Documentation | Markdown in Git. Wiki updated same day as work. No retroactive documentation. |
| Source control | Branch protection on main. Feature branches: `feature/{project}-{epic}-{desc}` |
| Infrastructure | All infra as code. No manual setup. Reproducible from repo. |
| CI/CD | Every project maintains automated pipeline. Defined in YAML. In repo. |
| Security | Least-privilege. Secrets in env vars / managers. Dependency scanning on build. |
| Sprint cadence | 2-week sprints. Demo-able increment each sprint. Retro in Markdown. |
| Naming | Epics: E1–En. Stories: S{epic}.{seq}. Eval gates: EV{sprint}.{seq}. Touchpoints: TP{n}. |
| Quality gates | No "Done" without acceptance criteria met. No epic closed without demo artifact. |
| Wiki | Updated from Day 1. Decision docs committed same day as decision. |
