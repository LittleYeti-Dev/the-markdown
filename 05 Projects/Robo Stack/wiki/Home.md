# Robo Stack Wiki
## Hybrid AI Development Stack — Yeti Knowledge Systems

**Project Lead:** Yeti (Justin Kuiper)
**Status:** Sprint 0 — Foundational Setup
**Repo:** [Robo Stack Code Repo] (link TBD after EV0.1 platform decision)

---

## Quick Links

| Resource | Location |
|----------|----------|
| Project Request (Whitepaper) | `../hybrid_ai_dev_stack_master_architecture_whitepaper.pdf` |
| Sprint 0 Checklist | `../Sprint_0_Checklist.md` |
| Project Instructions | `../../Sprint 0/Project_Instructions.md` |
| Boot Context | `../../../.taskmaster/boot-context.md` |
| Project Status | `../../../.taskmaster/status/robo-stack.md` |

## Wiki Sections

- [Sprint-0/](Sprint-0/) — Platform decision, wiki standard, repo setup, AI agent setup
- [Sprint-1/](Sprint-1/) — (populated as sprint executes)
- [Decision-Log/](Decision-Log/) — All evaluation gate outputs
- [Process-Docs/](Process-Docs/) — All designed workflows
- [Retrospectives/](Retrospectives/) — Sprint retrospectives

## Architecture Overview

Developer Workstation → Local AI Coding Agents → Git Repository → CI/CD Pipeline → Cloud Infrastructure (AWS) → Client Application Environment

### Core Layers
- **Local Development:** Ubuntu + Kubernetes (K3s/Minikube)
- **Source Control:** Git + CI/CD
- **AI Agent Layer:** Copilot, CodeWhisperer, JetBrains AI, TabbyML, OpenDevin
- **Cloud Infrastructure:** AWS EC2, VPC, optional EKS
- **Automation:** Terraform IaC
- **Security:** IAM, encryption, dependency scanning

## Epic Roadmap
- **E1:** Local development environment setup
- **E2:** Source control and CI/CD pipeline
- **E3:** AI agent layer integration
- **E4:** Cloud infrastructure deployment
- **E5:** Security hardening and monitoring
- **E6:** Commercialization and consulting services

## Active Personas
| Persona | Status | Activated |
|---------|--------|-----------|
| Taskmaster | ACTIVE | Sprint 0 |
| Platform Architect | DORMANT | Sprint 1 |
| AI Integration Specialist | DORMANT | Sprint 1 |
| DevSecOps Engineer | DORMANT | Sprint 1 |
| Solutions Consultant | DORMANT | Post-Epic 5 |
