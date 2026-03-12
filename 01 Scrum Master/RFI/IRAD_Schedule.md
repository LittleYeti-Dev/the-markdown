# IRAD Schedule — Taskmaster

**Purpose:** Unified tracker for all Independent Research & Development efforts across the Gunther portfolio.
**Owner:** Taskmaster (Scrum Master)
**Last Updated:** 2026-03-10

---

## Active IRADs

| IRAD ID | Title | Venture Lane | Lead Agent | Phase | Tasks | Est. Hours | Status | Initiated |
|---------|-------|-------------|------------|-------|-------|------------|--------|-----------|
| IRAD-001 | CLI Workstation Pattern Assessment | Build Stack Workshop | Foreman | 0 of 3 | 0/17 | 36h | ACTIVE — Phase 0 queued | 2026-03-10 |
| IRAD-BF-001 | Solo Entrepreneur AI Web Build Venture | Business Foundry | Strategist | 0 of 5 | 0/5 | TBD | ACTIVE — Awaiting Foundry session | 2026-03-10 |

---

## IRAD-001: CLI Workstation Pattern Assessment

**Initiating Document:** Build Stack Workshop/RFI/RFI-001_CLI_Workstation_Assessment.docx
**Handover Package:** Scrum Master/RFI/IRAD-001_Handover_Package.docx
**GitLab Repo:** gunther-repo/arch-discovery
**Reference Materials:** Build Stack Workshop/RFI_Reference_Packages/

### Phase Summary

| Phase | Name | Lead | Tasks | Est. | Depends On | Status |
|-------|------|------|-------|------|------------|--------|
| 0 | Foreman Assessment | Foreman | 7 | 15h | RFI-001 issued | NEXT |
| 1 | MVP Pilot Build | DevOps + Cyber Ops | 5 | 12h | Phase 0 complete | PENDING |
| 2 | Operationalize | All | 5 | 9h | Phase 1 complete | PENDING |

### Task Register

| ID | Type | Task | Owner | Est. | Depends On | Status |
|----|------|------|-------|------|------------|--------|
| IR.0.1 | ARCH | Read and internalize CLI Whitepaper + AI-DE reference materials | Foreman | 2h | — | NEXT |
| IR.0.2 | ARCH | Complete Capability Mapping Matrix | Foreman | 3h | IR.0.1 | PENDING |
| IR.0.3 | ARCH | Complete Adoption Risk Assessment | Foreman | 2h | IR.0.2 | PENDING |
| IR.0.4 | ARCH | Draft Recommended Adoption Roadmap | Foreman | 2h | IR.0.3 | PENDING |
| IR.0.5 | ARCH | Design Freelance Client Kit concept | Foreman | 3h | IR.0.3 | PENDING |
| IR.0.6 | ARCH | Identify and scope MVP Pilot | Foreman | 1h | IR.0.4, IR.0.5 | PENDING |
| IR.0.7 | ARCH | RFI-001 Response: write ADs and log to registers | Foreman | 2h | IR.0.6 | PENDING |
| IR.1.1 | FUNC | Stand up generator pattern proof-of-concept | DevOps | 4h | IR.0.7 | PENDING |
| IR.1.2 | FUNC | Build parameterized template with client branding variables | DevOps | 3h | IR.1.1 | PENDING |
| IR.1.3 | FUNC | Integrate generator script into GitLab | DevOps | 2h | IR.1.2 | PENDING |
| IR.1.4 | FUNC | End-to-end test: input → generate → output → review | DevOps | 2h | IR.1.3 | PENDING |
| IR.1.5 | SEC | Security review of generator pattern | Cyber Ops | 1h | IR.1.4 | PENDING |
| IR.2.1 | FUNC | Build client kit template library structure in GitLab | Foreman | 2h | IR.1.5 | PENDING |
| IR.2.2 | FUNC | Create second generator for different artifact type | DevOps | 3h | IR.2.1 | PENDING |
| IR.2.3 | FUNC | Document generator pattern playbook | Taskmaster | 2h | IR.2.2 | PENDING |
| IR.2.4 | ARCH | Architecture Decision: adopt/reject generator pattern | Foreman | 1h | IR.2.3 | PENDING |
| IR.2.5 | FUNC | Update PROJECT_INDEX and Architecture_Register with outcomes | Taskmaster | 1h | IR.2.4 | PENDING |

---

## IRAD-BF-001: Solo Entrepreneur AI Web Build Venture

**Initiating Document:** Business Foundry/RFI/RFI-BF-001_Solo_Entrepreneur_AI_Web_Build.docx
**Cross-Reference:** IRAD-001 (technical capability feeds into this venture assessment)

### Phase Summary

| Phase | Name | Lead | Status |
|-------|------|------|--------|
| 1 | Strategist — Venture Design & GO/NO-GO | The Strategist | PENDING |
| 2 | Operator — Financial Model & Execution Plan | The Operator | PENDING |
| 3 | Dealmaker — Sales Pipeline & Client Acquisition | The Dealmaker | PENDING |
| 4 | Analyst — Market Intel, Risk Assessment & Plausibility Score | The Analyst | PENDING |
| 5 | Foundry Review — Unified Recommendation | All Personas | PENDING |

### Deliverables

| # | Deliverable | Owner | Status |
|---|------------|-------|--------|
| 1 | Venture Blueprint with GO/NO-GO | Strategist | PENDING |
| 2 | Financial Model (revenue, costs, break-even, KPIs) | Operator | PENDING |
| 3 | Go-to-Market Playbook (pipeline, portfolio, first engagement) | Dealmaker | PENDING |
| 4 | Market Intelligence Report (TAM/SAM/SOM, competitive, pricing) | Analyst | PENDING |
| 5 | Plausibility Scorecard (weighted 6-dimension score) | Analyst | PENDING |

---

## Scheduling Rules

1. **IRADs run parallel to mainline sprints at lower priority.** Never displace The Markdown or other committed sprint work.
2. **Recommended cadence:** 2–4 hours per week in dedicated sessions.
3. **Phase gates are hard.** Each IRAD phase must complete before the next begins.
4. **Cross-IRAD dependencies noted.** IRAD-BF-001 should incorporate IRAD-001 Phase 0 findings when available.
5. **Status updates in morning scrum.** Chief of Staff includes IRAD status in the Active Workstream section.

---

## Completed IRADs

| IRAD ID | Title | Outcome | Completed | AD Reference |
|---------|-------|---------|-----------|-------------|
| — | No completed IRADs yet | — | — | — |

---

*Maintained by Taskmaster. Updated at each IRAD task status change.*
