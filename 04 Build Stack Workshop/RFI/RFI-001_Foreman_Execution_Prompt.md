# RFI-001 — Foreman Execution Prompt

**Version:** 1.0
**Date:** 2026-03-10
**Task:** Execute CLI Workstation Pattern Assessment (RFI-001)
**Role:** Foreman — Build Stack Workshop Architect
**Issued By:** Gunther — Chief of Staff
**Priority:** ROUTINE — No sprint impact until assessed
**Response Due:** NLT 2026-03-14

> **This is a handoff document.** It contains everything a fresh Foreman session needs to execute RFI-001 without re-reading the entire project history. Read this file, then execute.

---

## 0. CONTEXT — READ FIRST

You are Foreman, the Build Stack Workshop Architect in the Gunther multi-agent system (AD-0010). You've been issued RFI-001 by Gunther (Chief of Staff) to assess JD Longmire's Claude CLI AI-Augmented Engineering Workstation pattern against the current Gunther deployment.

**This is NOT a code deployment task.** This is an architecture assessment that produces documents, decisions, and a pilot recommendation.

---

## 1. MISSION

Assess five capability areas from JD's CLI Workstation whitepaper, evaluate adoption potential against Gunther's constraints, and produce five deliverables. The primary business driver is **freelance productization** — can we build reusable, client-brandable artifact pipelines?

**Your five deliverables:**

| # | Deliverable | Format | Output Location |
|---|-------------|--------|-----------------|
| 1 | Capability Mapping Matrix | Table: JD capability → Gunther current → gap → recommendation | Architecture Register (new ADs) |
| 2 | Adoption Risk Assessment | Per-capability: complexity, effort, risk, ROI | Decision Register |
| 3 | Recommended Adoption Roadmap | Phased plan: quick wins → medium-term → long-term | Architecture Register |
| 4 | Freelance Client Kit Concept | Architecture proposal for parameterized deliverable pipeline | New ADR (proposed) |
| 5 | MVP Pilot Recommendation | One artifact type, one use case, end-to-end scope | Session transcript |

---

## 2. REFERENCE MATERIALS

Read these before beginning assessment:

### Primary Input
- **CLI Whitepaper:** `04 Build Stack Workshop/RFI_Reference_Packages/Claude_CLI_Whitepaper/Claude-CLI-AI-Augmented-Engineering-Workstation.md`
  - Key sections: §2 Architecture, §3 Capability Demos, §4 Workflow Patterns, §5 Tool Inventory

### Current Architecture
- **Architecture Register:** `04 Build Stack Workshop/Architecture_Register.md` — AD-0001 through AD-0010
- **Decision Register:** `04 Build Stack Workshop/Decision_Register.md` — TD-001 through TD-005
- **Operational Playbook:** `01 Scrum Master/Operational_Playbook.md` — micro-snippet architecture, tool hierarchy
- **Sprint Tracker:** `01 Scrum Master/Sprint_Tracker.md` — current project state

### RFI Source
- **RFI Document:** `04 Build Stack Workshop/RFI/RFI-001_CLI_Workstation_Assessment.docx`

---

## 3. CAPABILITY AREAS TO ASSESS

### 3.1 Generator Pattern
**What JD does:** Python scripts (`build_deck.py`, `build_whitepaper.py`) that produce PPTX/DOCX artifacts. Scripts are version-controlled, parameterizable, reproducible.

**Assess against Gunther:**
- Currently: Cowork creates docs directly via docx-js skills. No parameterized scripts.
- Question: Should we adopt generator scripts for artifact production?
- Evaluate: Applicability to freelance work, GitLab storage, which artifact types benefit most

### 3.2 Parallel Agent Execution
**What JD does:** Multiple Claude agents run simultaneously within a single CLI session (primary + background agents).

**Assess against Gunther:**
- Currently: Four-function team (Overwatch/Taskmaster/Foreman/Developer) in separate Claude Projects/Cowork sessions (AD-0010)
- Question: Does CLI-level parallelism offer meaningful throughput gain?
- Apply solo-operator ceiling test

### 3.3 Visual Feedback Loop
**What JD does:** Generate → render to image → Claude reviews screenshot → fix script → rebuild. Tight iterative refinement.

**Assess against Gunther:**
- Currently: Cowork previews .html/.md/.jsx. Limited for PPTX/SVG.
- Question: Does CLI rendering add meaningful quality for complex visual work?
- Evaluate hybrid approach: Cowork for simple, CLI for complex

### 3.4 Open-Source Toolchain Consolidation
**What JD does:** Unified tool inventory (python-pptx, python-docx, diagrams, Graphviz, Inkscape, librsvg) on local Ubuntu.

**Assess against Gunther:**
- Map which tools are available in Cowork VM vs. need separate install
- Evaluate whether Build Stack Workshop should maintain a tool manifest in GitLab
- Test: `pip list`, `which graphviz`, `which inkscape` to determine current VM state

### 3.5 Freelance Productization
**Primary business driver.** Can we build reusable, client-brandable artifact pipelines?

**Assess:**
- Generator scripts + templates → "client kit" pattern (brand colors, logos, fonts, boilerplate in parameterized scripts)
- ROI of generator library vs. ad-hoc creation
- Minimum viable pilot: one artifact type, one client use case, end-to-end proof of concept

---

## 4. ASSESSMENT CONSTRAINTS (HARD RULES)

Every recommendation must pass ALL five tests:

1. **Solo-operator ceiling.** No dedicated DevOps maintenance, no cron jobs to monitor, no silent failures. If it needs babysitting → NO.
2. **No wholesale migration.** Cowork/Desktop Projects continue. Identify complements, not replacements.
3. **Protect The Markdown.** WordPress.com deployment, micro-snippet architecture, and sprint cadence are OUT OF SCOPE for modification.
4. **GitLab-native.** All new patterns (scripts, templates, configs) live in GitLab following AD-0009 local-first sync.
5. **Cost-neutral preferred.** Open-source tools and existing subscriptions. New paid tooling needs explicit justification.

---

## 5. EXECUTION STEPS

### Phase 1: Read & Internalize (~30 min)
1. Read the CLI Whitepaper (full document)
2. Read the Architecture Register (AD-0001 to AD-0010)
3. Read the Decision Register (TD-001 to TD-005)
4. Read the Operational Playbook (tool hierarchy, deployment constraints)
5. Note key patterns and constraints

### Phase 2: Assess & Map (~1 hour)
6. For each of the 5 capability areas:
   - Document current Gunther state
   - Identify gap or overlap
   - Apply the 5 constraint tests
   - Determine verdict: ADOPT / PARTIAL ADOPT / DEFER / REJECT
7. Build the Capability Mapping Matrix (Deliverable 1)
8. Score each capability on complexity, effort, risk, ROI (Deliverable 2)

### Phase 3: Design & Propose (~1 hour)
9. Build the phased Adoption Roadmap (Deliverable 3)
   - Quick wins (this sprint, <4h)
   - Medium-term (Sprint 3.5–4)
   - Long-term (post-Sprint 4)
10. Design the Freelance Client Kit architecture (Deliverable 4)
    - Brand config schema (brand.json)
    - Generator contract (standard interface)
    - Directory structure
    - Proposed AD-0014
11. Define MVP Pilot scope (Deliverable 5)
    - One artifact type (recommend: PPTX proposal deck)
    - One use case (technology consulting proposal)
    - Success criteria, effort estimate, timeline

### Phase 4: Document (~30 min)
12. Produce consolidated response document (DOCX preferred)
    - All 5 deliverables in one document
    - Solo-operator ceiling test results table
    - Proposed new Architecture Decisions (AD-0011 through AD-0014)
13. Update Architecture Register with proposed ADs (status: PROPOSED)
14. Update Decision Register if new TDs are warranted
15. Save response to `04 Build Stack Workshop/RFI/RFI-001_Response_*.docx`

### Phase 5: Verify (~15 min)
16. Cross-reference every recommendation against the 5 constraints
17. Verify no recommendation touches The Markdown's WordPress deployment
18. Confirm all proposed ADs are self-consistent with existing AD-0001 through AD-0010
19. Verify response answers all 5 requested deliverables from the RFI

---

## 6. OUTPUT FILES

| File | Location | Purpose |
|------|----------|---------|
| RFI Response Document | `04 Build Stack Workshop/RFI/RFI-001_Response_CLI_Workstation_Assessment.docx` | Consolidated 5-deliverable response |
| Architecture Register (updated) | `04 Build Stack Workshop/Architecture_Register.md` | AD-0011 through AD-0014 added as PROPOSED |
| Decision Register (if needed) | `04 Build Stack Workshop/Decision_Register.md` | Any new technical decisions |

---

## 7. DONE CRITERIA

RFI-001 is DONE when:
- [ ] All 5 deliverables produced and documented
- [ ] Every recommendation passes the 5 constraint tests
- [ ] Proposed Architecture Decisions (AD-0011+) logged in register
- [ ] Freelance Client Kit concept includes brand.json schema and generator contract
- [ ] MVP Pilot has specific artifact type, use case, effort estimate, and success criteria
- [ ] Response document saved to Build Stack Workshop/RFI/
- [ ] No recommendation modifies The Markdown's active deployment

---

## 8. NOTES

- **Lead with freelance productization.** This is the primary business driver per the RFI. Every other capability assessment should be evaluated through the lens of "does this help produce client deliverables faster?"
- **Be specific about the pilot.** Yeti needs a concrete next action, not a strategy deck. The MVP Pilot should be executable in a single session.
- **Generator scripts are the big win.** JD's whitepaper demonstrates that python-pptx + parameterization is battle-tested. This is the lowest-risk, highest-ROI adoption target.
- **Don't over-engineer.** A brand.json + one generator script that works > an elaborate framework that doesn't ship.

---

*Prepared by Taskmaster 2026-03-10. RFI-001 assessment is a standalone task — no sprint blocking dependencies. Execute, document, deliver.*
