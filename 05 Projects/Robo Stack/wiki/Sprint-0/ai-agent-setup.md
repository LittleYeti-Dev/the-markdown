# EV0.4: AI Agent Integration — Setup & Status
## Sprint 0 | Robo Stack (applies to both projects)

**Date:** 2026-03-13
**Status:** PENDING — To be completed after platform decision (EV0.1)

---

## Current AI Agent Inventory

| Agent | Type | Status | Notes |
|-------|------|--------|-------|
| Claude (Anthropic) | Desktop app / API / Cowork | ACTIVE | Currently driving Sprint 0 execution |
| GitHub Copilot | IDE code completion + chat | TBD | Requires GitHub (EV0.1 dependency) |
| GitLab Duo | IDE code suggestions | TBD | Requires GitLab plan confirmation |
| TabbyML | Local/offline AI code completion | NOT INSTALLED | Planned as offline fallback |
| CodeWhisperer (AWS) | IDE code completion | NOT INSTALLED | Evaluate for AWS integration |
| JetBrains AI Assistant | IDE-integrated AI | NOT INSTALLED | Evaluate if JetBrains IDE used |
| OpenDevin | Autonomous AI agent | NOT EVALUATED | Evaluate for task automation |
| Cursor / Continue.dev | AI-native IDE | NOT INSTALLED | Evaluate as alternative IDE |

## Setup Tasks (post EV0.1)

### If GitHub selected:
- [ ] Install GitHub Copilot extension in VS Code
- [ ] Verify Copilot code completion and Chat
- [ ] Test Copilot Workspace for task scaffolding
- [ ] Install TabbyML as offline fallback
- [ ] Document agent configuration in wiki

### If GitLab selected:
- [ ] Enable GitLab Duo (confirm plan supports it)
- [ ] Install GitLab VS Code extension
- [ ] Verify code suggestions
- [ ] Install TabbyML as offline fallback
- [ ] Evaluate Claude/ChatGPT API for code review

### Cross-platform (regardless):
- [ ] Confirm Claude desktop/API access
- [ ] Install additional AI tools (Cursor, Continue.dev) if warranted
- [ ] Create AI agent usage guide: which agent for which task
- [ ] **Test all agents against a real coding task from the Robo Stack backlog**

## AI Agent Usage Guide (Draft)

| Task Type | Primary Agent | Fallback |
|-----------|--------------|----------|
| Sprint orchestration & planning | Claude (Cowork/Desktop) | — |
| Code completion in IDE | Copilot or GitLab Duo (TBD) | TabbyML (offline) |
| Code review | Claude API or platform-native | — |
| Architecture design | Claude | — |
| Task scaffolding | Copilot Workspace or Claude | — |
| Security scanning | Platform CI/CD native tools | — |

---

*This document will be finalized after EV0.1 platform decision and hands-on agent testing.*
