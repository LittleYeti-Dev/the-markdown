# Sprint 1 — Agent Task Prompts
## Robo Stack | Epic 1: Local Development Layer

**Version:** 1.0
**Date:** 2026-03-13
**Sprint:** Sprint 1 — Local Development Layer Prototype
**Prepared by:** Taskmaster
**For:** GitHub Copilot (Agent Mode) + Claude Code
**Repo:** https://github.com/LittleYeti-Dev/robo-stack
**Board:** https://github.com/users/LittleYeti-Dev/projects/1

> **Handoff document.** A fresh agent session reads this file and executes. No project history needed.

---

## Agent Routing — Sprint 1

| Story | Task | Agent | Rationale |
|-------|------|-------|-----------|
| S1.1 | Epic 1 Process Design | Claude Code | Architecture + design work |
| S1.2 | Ubuntu Base Config | Copilot Agent Mode | Scaffolding + script generation |
| S1.3 | Local K8s Cluster | Copilot Agent Mode | IaC scripts + Helm charts |
| S1.4 | Git + CI/CD Pipeline | Copilot Agent Mode | GitHub Actions YAML authoring |
| S1.5 | Security Baseline | Copilot Agent Mode | Config files + scanning setup |
| EV1.1 | K3s vs Minikube Eval | Claude Code | Comparative analysis + decision doc |
| S1.6 | Copilot Hands-On Test | Copilot Agent Mode | This IS the test |
| TP1 | Sprint 1 Touchpoint | Claude (Cowork) | Sprint orchestration |

---

## PROMPT 1: S1.1 — Epic 1 Process Design
**Agent:** Claude Code
**GitHub Issue:** #9
**Persona:** Platform Architect

```
You are the Platform Architect for the Robo Stack project — a hybrid AI development stack.

TASK: Design the end-to-end process for Epic 1: Local Development Layer.

CONTEXT:
- Project repo: https://github.com/LittleYeti-Dev/robo-stack
- This is a PROCESS DESIGN story — no build work starts until this is approved
- The Local Dev Layer includes: Ubuntu workstation, local Kubernetes (K3s or Minikube), Git workflow, CI/CD pipeline, and security baseline
- Standing order: every epic begins with a process design story BEFORE build stories execute

DELIVERABLES:
1. Process document in Markdown covering:
   - Stage 1: Workstation Bootstrap (Ubuntu base config, package manifest)
   - Stage 2: Container Runtime (Docker install + config)
   - Stage 3: Local Kubernetes (cluster setup, namespace strategy)
   - Stage 4: Git Workflow (branch strategy, PR rules)
   - Stage 5: CI/CD Pipeline (GitHub Actions, triggers, deployment)
   - Stage 6: Security Baseline (scanning, secrets, access control)

2. For each stage document:
   - Inputs (what's needed before this stage)
   - Process steps (what happens)
   - Outputs (what's produced)
   - Stage gate (what must be true before advancing)
   - Tool requirements (what needs evaluating)

3. Mermaid workflow diagram showing all 6 stages and their dependencies

4. Identify any eval gates needed (tool decisions requiring Yeti sign-off)

OUTPUT FORMAT: Single Markdown file ready for GitHub wiki commit.
FILE: Process-Docs/E1-Local-Dev-Layer-Process.md

CONSTRAINTS:
- All infrastructure must be reproducible from repo (no manual setup)
- Security-first: least privilege, no secrets in code
- Documentation same day as work
- Every tool decision needs an eval gate with Yeti sign-off
```

---

## PROMPT 2: S1.2 — Ubuntu Workstation Base Configuration
**Agent:** Copilot Agent Mode
**GitHub Issue:** #10
**Persona:** Platform Architect

```
Create an automated Ubuntu developer workstation setup script for the Robo Stack hybrid AI development stack.

REPO: robo-stack (clone it first if needed)
BRANCH: feature/robo-stack-e1-workstation-setup

REQUIREMENTS:
1. Create `scripts/workstation-setup.sh` — idempotent bash script that:
   - Updates system packages
   - Installs: git, curl, wget, jq, yq, build-essential
   - Installs Docker CE (latest stable) + adds user to docker group
   - Installs kubectl (latest stable)
   - Installs Helm 3
   - Installs Terraform (latest stable)
   - Installs VS Code (via snap or apt)
   - Installs GitHub CLI (gh)
   - Installs Node.js LTS (via nvm)
   - Installs Python 3.x + pip + virtualenv
   - Verifies each tool after install (version check)
   - Outputs a summary table of installed tools and versions

2. Create `scripts/verify-setup.sh` — validation script that:
   - Checks each required tool is installed and accessible
   - Checks Docker daemon is running
   - Checks Git is configured (user.name, user.email)
   - Outputs PASS/FAIL for each check
   - Returns non-zero exit code if any check fails

3. Create `docs/workstation-setup-guide.md` — wiki-ready setup guide with:
   - Prerequisites (Ubuntu version, minimum specs)
   - Step-by-step instructions
   - Troubleshooting section
   - Post-install verification steps

CONSTRAINTS:
- Scripts must be idempotent (safe to run multiple times)
- No hardcoded paths or usernames
- All secrets via environment variables
- Follow shellcheck best practices
- Include error handling and logging

COMMIT MESSAGE: "feat(e1): add workstation setup automation — S1.2"
PR: Create PR to main with description referencing issue #10
```

---

## PROMPT 3: S1.3 — Local Kubernetes Cluster
**Agent:** Copilot Agent Mode
**GitHub Issue:** #11
**Persona:** Platform Architect
**Blocked by:** EV1.1 decision (K3s vs Minikube)

```
Set up a local Kubernetes development cluster for the Robo Stack project.

REPO: robo-stack
BRANCH: feature/robo-stack-e1-local-k8s

NOTE: Use [K3s/Minikube] based on EV1.1 eval gate decision. If no decision yet, implement K3s as default (lighter footprint).

REQUIREMENTS:
1. Create `scripts/k8s-setup.sh` — automated cluster setup:
   - Install K3s (or Minikube per EV1.1)
   - Configure kubectl context for local cluster
   - Create namespaces: dev, staging, monitoring
   - Install Helm and add common repos (bitnami, prometheus-community)
   - Verify cluster is healthy (nodes ready, system pods running)

2. Create `scripts/k8s-validate.sh` — cluster health check:
   - Verify cluster is running
   - Verify all namespaces exist
   - Deploy a test nginx pod, verify it runs, clean up
   - Check Helm is functional
   - Output cluster info summary

3. Create `k8s/namespaces.yaml` — namespace definitions as YAML manifests

4. Create `docs/local-k8s-guide.md` — wiki-ready guide:
   - Architecture overview (why local K8s, namespace strategy)
   - Setup instructions
   - Common operations (deploy, scale, logs, exec)
   - Troubleshooting

CONSTRAINTS:
- Must work on Ubuntu 22.04+ with 8GB+ RAM
- Cluster must start in under 2 minutes
- All config in repo (no manual kubectl apply outside scripts)
- Resource limits set to prevent workstation overload

COMMIT MESSAGE: "feat(e1): add local Kubernetes cluster setup — S1.3"
PR: Create PR to main referencing issue #11
```

---

## PROMPT 4: S1.4 — Git Workflow + CI/CD Pipeline
**Agent:** Copilot Agent Mode
**GitHub Issue:** #12
**Persona:** DevSecOps Engineer

```
Establish the Git branching strategy and CI/CD pipeline for the Robo Stack repository.

REPO: robo-stack
BRANCH: feature/robo-stack-e1-cicd

REQUIREMENTS:
1. Create `.github/workflows/ci.yml` — CI pipeline:
   - Triggers: on PR to main, on push to main
   - Jobs:
     a. Lint: shellcheck on all .sh files, yamllint on all .yml/.yaml
     b. Test: run verify-setup.sh and k8s-validate.sh in CI (use Docker-based runner)
     c. Security: run trivy or similar for container scanning
   - Status checks required to pass before merge

2. Create `.github/workflows/deploy-local.yml` — deployment pipeline:
   - Triggers: on merge to main (manual dispatch also)
   - Runs deployment scripts against local K8s cluster
   - Posts deployment status to PR/commit

3. Create `.github/PULL_REQUEST_TEMPLATE.md`:
   - Checklist: tests pass, docs updated, security reviewed
   - Link to related issue
   - Description of changes

4. Create `.github/CODEOWNERS`:
   - Default reviewers for key paths (scripts/, k8s/, .github/)

5. Create `docs/git-workflow-guide.md` — wiki-ready:
   - Branch naming: feature/{project}-{epic}-{desc}
   - PR process and review requirements
   - CI/CD pipeline overview
   - Merge strategy (squash merge to main)

CONSTRAINTS:
- All pipeline config in YAML, in repo
- No manual GitHub settings changes needed (use branch protection API if needed)
- Pipeline must complete in under 5 minutes
- Follow GitHub Actions best practices (pinned action versions, minimal permissions)

COMMIT MESSAGE: "feat(e1): add CI/CD pipeline and Git workflow — S1.4"
PR: Create PR to main referencing issue #12
```

---

## PROMPT 5: S1.5 — Security Baseline
**Agent:** Copilot Agent Mode
**GitHub Issue:** #13
**Persona:** DevSecOps Engineer

```
Establish the security baseline for the Robo Stack repository and development environment.

REPO: robo-stack
BRANCH: feature/robo-stack-e1-security

REQUIREMENTS:
1. Create `.github/dependabot.yml`:
   - Enable dependency scanning for: GitHub Actions, Docker, npm, pip
   - Weekly schedule, auto-create PRs for updates
   - Group minor/patch updates

2. Create `.github/workflows/codeql.yml`:
   - CodeQL analysis on PR and weekly schedule
   - Languages: javascript, python (add more as needed)

3. Create `.github/workflows/security-scan.yml`:
   - Container image scanning with Trivy
   - Secret detection with truffleHog or gitleaks
   - SBOM generation

4. Create `.env.template`:
   - Template showing all required environment variables
   - No real values — placeholder descriptions only
   - Comments explaining each variable

5. Create `docs/security-baseline.md` — wiki-ready:
   - Security architecture overview
   - Dependency scanning policy
   - Secret management rules (env vars only, never in code)
   - Incident response basics
   - Access control model (least privilege)

6. Create `.gitignore` additions:
   - Ensure .env, *.key, *.pem, credentials.* are ignored

CONSTRAINTS:
- Zero secrets in repository (verified by scanning)
- All security config as code in repo
- Dependabot must be active and creating PRs
- Documentation committed same day

COMMIT MESSAGE: "feat(e1): add security baseline — S1.5"
PR: Create PR to main referencing issue #13
```

---

## PROMPT 6: EV1.1 — K3s vs Minikube Evaluation
**Agent:** Claude Code
**GitHub Issue:** #14
**Persona:** Platform Architect + AI Integration Specialist

```
You are evaluating K3s vs Minikube for the Robo Stack local Kubernetes development environment.

TASK: Produce a hands-on evaluation and decision document.

CONTEXT:
- Robo Stack is a hybrid AI development stack running on Ubuntu developer workstations
- The local K8s cluster will run: development workloads, AI agent containers, monitoring stack
- Target hardware: Ubuntu 22.04, 16GB RAM, 8-core CPU (minimum viable: 8GB RAM, 4-core)
- Must be reproducible from scripts (IaC)

EVALUATION CRITERIA (test both hands-on):
1. Resource footprint — RAM and CPU at idle and under load
2. Startup time — cold start and warm restart
3. API compatibility — standard K8s API coverage, CRD support
4. Helm chart compatibility — deploy prometheus-community/kube-prometheus-stack
5. Multi-node simulation — can it simulate multi-node for testing?
6. Developer experience — ease of setup, debugging, log access
7. CI/CD integration — can it run in GitHub Actions for testing?
8. Documentation and community support

FORMAT: Decision document in Markdown with:
- Executive summary (recommendation + rationale in 3 sentences)
- Comparison table (all 8 criteria, scored 1-5)
- Hands-on test results (what you actually ran and observed)
- Recommendation with trade-offs acknowledged
- Yeti sign-off section (blank — Yeti fills in)

OUTPUT FILE: Decision-Log/EV1.1-K3s-vs-Minikube.md

CONSTRAINT: Both options must be tested hands-on. No desk research only.
```

---

## PROMPT 7: S1.6 — Copilot Agent Mode Hands-On Test
**Agent:** Copilot Agent Mode
**GitHub Issue:** #15
**Persona:** AI Integration Specialist

```
This prompt IS the hands-on test for GitHub Copilot Agent Mode.

TASK: Use Copilot Agent Mode to accomplish one of the Sprint 1 build tasks (S1.2, S1.3, S1.4, or S1.5). Document the experience.

OBJECTIVES:
1. Complete a real Sprint 1 coding task using Copilot Agent Mode
2. Document what worked, what didn't, and where human intervention was needed
3. Validate the agent task routing table from Sprint 0

DOCUMENT YOUR EXPERIENCE:
After completing the task, create `docs/copilot-agent-test-report.md`:
- Task attempted and complexity level
- Time to completion (with and without Copilot)
- Quality of generated code (did it need heavy editing?)
- Where Copilot excelled vs where it struggled
- Comparison to Claude Code for similar tasks
- Updated recommendations for agent task routing
- Score: 1-5 (would you use Copilot Agent Mode for this type of task again?)

Also test Copilot Workspace:
- Try scaffolding a new feature using Copilot Workspace
- Document the experience similarly

COMMIT: Results to wiki Sprint-1/Sprint-1-AI-Agent-Testing.md
```

---

## PROMPT 8: TP1 — Sprint 1 Touchpoint Prep
**Agent:** Claude (Cowork)
**GitHub Issue:** #16
**Persona:** Taskmaster

```
Prepare the Sprint 1 touchpoint agenda for Yeti review.

GATHER:
1. Status of all Sprint 1 stories (check GitHub Issues #9-#15)
2. EV1.1 decision document (K3s vs Minikube)
3. Demo artifacts: working local dev environment, K8s cluster, CI/CD pipeline
4. AI agent test results (Copilot hands-on report)
5. Any blockers or scope changes discovered during Sprint 1

PRODUCE:
Sprint 1 Touchpoint agenda document:
1. Demo of working increment
2. Review EV1.1 decision
3. Requirements harvest — what changed
4. Process validation — does the E1 workflow work?
5. AI agent testing results
6. Go/no-go for Sprint 2

Commit to wiki: Retrospectives/TP1-Sprint-1-Touchpoint.md
```

---

## Execution Order

Per process-first standing order, stories execute in this sequence:

1. **S1.1** (Process Design) — Claude Code — MUST complete and get Yeti approval first
2. **EV1.1** (K3s vs Minikube) — Claude Code — can run in parallel with S1.1
3. **S1.2** (Workstation Setup) — Copilot — after S1.1 approved
4. **S1.3** (Local K8s) — Copilot — after S1.1 + EV1.1 approved
5. **S1.4** (CI/CD) — Copilot — after S1.1 approved
6. **S1.5** (Security) — Copilot — after S1.1 approved
7. **S1.6** (Copilot Test) — Copilot — runs alongside any build story
8. **TP1** (Touchpoint) — Cowork — after all stories complete

---

## Quick Reference

| Agent | Stories | Est. Tasks |
|-------|---------|------------|
| Claude Code | S1.1, EV1.1 | 2 (architecture + eval) |
| Copilot Agent Mode | S1.2, S1.3, S1.4, S1.5, S1.6 | 5 (build + test) |
| Claude Cowork | TP1 | 1 (touchpoint prep) |

**Sprint duration:** 2 weeks
**Sprint goal:** Working local development environment with K8s, CI/CD, and security baseline — all reproducible from repo.
