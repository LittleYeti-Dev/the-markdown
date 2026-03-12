# Sprint 3 — Wave 3 / Gate 7: Final Cyber Gate Execution Prompt (CODE)

**Version:** 1.0
**Date:** 2026-03-10
**Sprint:** Sprint 3 — Scale & Polish
**Wave:** Wave 3 (Gate 7 — Final Cyber Gate, Runs After Everything)
**Prepared by:** Taskmaster
**For:** Claude Code agent (Cyber Ops role)

> **This is a handoff document.** It contains everything a fresh agent session needs to execute Wave 3 / Gate 7 Cyber Ops tasks without re-reading the entire project history. Read this file, then execute.

---

## 0. FIRST — PREREQUISITES

### Waves 1 and 2 Must Be Complete

**Do NOT start Wave 3 until Waves 1 and 2 are signed off.** Gate 7 is the capstone security gate — it validates the entire stack after all functional and security work is deployed.

Verify by checking:
- `02 DevOps/S3_Wave1_DevOps_Results.md` — Wave 1 DevOps done
- `07 Cyber Team/S3_Wave1_CyberOps_Results.md` — Wave 1 Cyber done
- `01 Scrum Master/S3_Wave1_Taskmaster_Results.md` — Wave 1 Taskmaster done
- `02 DevOps/S3_Wave2_DevOps_Results.md` — Wave 2 DevOps done
- `01 Scrum Master/S3_Wave2_QA_Results.md` — Wave 2 QA done

**All Wave 1 SEC tasks (S3.9, S3.10, S3.12) must also be closed.**

### Credentials

**Before touching ANY endpoint or running ANY code, ask Yeti for:**

1. **GitLab PAT** — `api` scope, for issue management and repo scanning
   - Project ID: `80070684`
   - Repo: `gitlab.com/h3ndriks.j/JK.com-ver02`

2. **WordPress Application Password** — for REST API verification
   - Auth method: HTTP Basic (`h3ndriksj:APP_PASSWORD`)
   - Do NOT store it anywhere. Hold in memory only.

**Do not proceed past this section until you have both credentials.**

---

## 1. MISSION

Execute the Gate 7 — Final Cyber Gate for Sprint 3. These are **6 capstone security tasks** that pen-test the full stack, establish monitoring, document incident response, and perform the final OPSEC review. This is the last gate before the project can be declared production-ready.

**Your tasks (6 total):**

| ID | Task | Type | GitLab Issue | Priority |
|----|------|------|-------------|----------|
| S3.16 | Penetration test — OWASP Top 10 scan on all endpoints | SEC | #67 | Critical |
| S3.17 | Incident response plan — document response procedures | SEC | #68 | Medium |
| S3.18 | Security monitoring setup — alerts for suspicious activity | SEC | #69 | Medium |
| S3.19 | Final cyber gate review — all 7 gates pass | SEC | #70 | Critical |
| S3.20 | OPSEC review — verify no secrets in repos, logs, or public pages | SEC | #71 | High |
| S3.21 | Documentation — security architecture doc, runbooks, credential inventory | SEC | #72 | Medium |

**No other owners have Wave 3 tasks.** This wave is entirely Cyber Ops.

---

## 2. PLATFORM CONTEXT

### WordPress.com (NOT Self-Hosted)

- **No SFTP.** No SSH. No server-level access.
- **WAF active** — WordPress.com manages its own WAF.
- **Site URL:** `https://justin-kuiper.com`
- **REST base:** `https://justin-kuiper.com/wp-json/`
- **Code Snippets API:** `code-snippets/v1/snippets`

### GitLab

- **Project ID:** `80070684`
- **Repo:** `gitlab.com/h3ndriks.j/JK.com-ver02`
- **API base:** `https://gitlab.com/api/v4/projects/80070684`

### Cyber Gates History

| Gate | Name | Sprint | Status |
|------|------|--------|--------|
| G0 | Immediate Hardening | S0 | ✅ PASSED |
| G1 | Data Model Security | S1 | ✅ PASSED |
| G2 | RSS/AI Pipeline Security | S1 | ✅ PASSED |
| G3 | Deployment Security | S1 | ✅ PASSED |
| G4 | Social & Publishing Security | S2 | ✅ PASSED |
| G5 | Integration Gate Security | S2.5 | ✅ PASSED |
| G6 | Visual & Infrastructure Security | S3 W1 | ⬜ PENDING (Wave 1 SEC tasks) |
| **G7** | **Final Cyber Gate** | **S3 W3** | **⬜ THIS WAVE** |

### Tool Hierarchy (DR-0021)

1. **Security scanning tools** — OWASP ZAP, nikto, or equivalent (via CLI)
2. **WordPress REST API** — for endpoint enumeration and testing
3. **GitLab REST API** — for repo scanning, secret detection
4. **Browser automation** — for visual security checks
5. **`curl`/HTTP requests** — for manual endpoint testing

**At the start of every task, state your tool choice and reasoning.**

---

## 3. TASK DETAILS

### Task 1: S3.16 — Penetration Test (OWASP Top 10)

**GitLab Issue:** #67
**Priority:** Critical

**What to do:**

Perform a security scan of all public endpoints against the OWASP Top 10 vulnerability categories.

**OWASP Top 10 (2021) Checklist:**

1. **A01: Broken Access Control**
   - Test all REST endpoints with and without authentication
   - Verify `/wp-json/code-snippets/v1/snippets` requires auth
   - Verify `/wp-json/ns/v1/diagnostics/health` requires auth
   - Test for IDOR on feed items (can you access/modify other users' items?)
   - Check admin endpoints are not publicly accessible

2. **A02: Cryptographic Failures**
   - Verify HTTPS everywhere (no mixed content)
   - Check SSL/TLS configuration (grade via SSL Labs)
   - Verify no sensitive data in URL parameters
   - Check for exposed API keys or tokens in page source

3. **A03: Injection**
   - Test REST API inputs for SQL injection
   - Test search parameters for XSS
   - Test feed item fields for stored XSS
   - Test URL parameters for command injection

4. **A04: Insecure Design**
   - Review rate limiting on API endpoints
   - Check for enumeration vulnerabilities (user IDs, post IDs)
   - Verify error messages don't leak implementation details

5. **A05: Security Misconfiguration**
   - Verify security headers (CSP, HSTS, X-Frame-Options, X-Content-Type)
   - Check for default credentials or configurations
   - Verify WordPress version is not exposed
   - Check for unnecessary HTTP methods (PUT, DELETE on public endpoints)

6. **A06: Vulnerable and Outdated Components**
   - Cross-reference with S3.10 plugin audit results
   - Check for known CVEs on installed components
   - Verify WordPress core version is current

7. **A07: Identification and Authentication Failures**
   - Test Application Password authentication
   - Check for brute force protection
   - Verify session management

8. **A08: Software and Data Integrity Failures**
   - Verify Code Snippets plugin integrity
   - Check for subresource integrity on external scripts
   - Review CI/CD pipeline security (from S3.9)

9. **A09: Security Logging and Monitoring Failures**
   - Document current logging capabilities
   - Identify gaps in security event logging
   - Feed into S3.18 (monitoring setup)

10. **A10: Server-Side Request Forgery (SSRF)**
    - Test RSS feed fetching for SSRF vectors
    - Verify feed URL validation in wp_remote_get() calls
    - Test for internal network scanning via feed URLs

**Implementation approach:**
- Use `curl` for manual endpoint testing
- Use OWASP ZAP or nikto if available in the environment (install via pip/apt if needed)
- Document every test with: endpoint, test performed, result (PASS/FAIL), evidence

**Acceptance Criteria:**
- ✅ All 10 OWASP categories tested
- ✅ All public endpoints tested with and without auth
- ✅ No Critical or High vulnerabilities remaining
- ✅ Medium/Low findings documented with remediation plan
- ✅ SSL/TLS grade documented

---

### Task 2: S3.17 — Incident Response Plan

**GitLab Issue:** #68
**Priority:** Medium

**What to do:**

Create a documented incident response plan covering how to respond to security incidents affecting The Markdown.

**Document structure:**

1. **Incident Classification**
   - Severity levels (P1-P4) with examples
   - Escalation thresholds

2. **Response Procedures**
   - Detection → Triage → Containment → Eradication → Recovery → Lessons Learned
   - Per-severity response timelines

3. **Specific Playbooks**
   - **Compromised credentials:** Steps to rotate all tokens, revoke sessions
   - **Malicious code injection:** Steps to identify, disable, and remove compromised snippets
   - **Data breach:** Steps to assess scope, notify, and remediate
   - **DDoS/availability:** Steps to engage WordPress.com support, enable additional protections
   - **GitLab compromise:** Steps to rotate PATs, audit commits, verify pipeline integrity

4. **Contact Information**
   - Yeti (project owner) — primary responder
   - WordPress.com support — platform issues
   - GitLab support — repo issues

5. **Post-Incident Review Template**
   - Timeline, root cause, impact, remediation, prevention

**Output file:** `07 Cyber Team/Incident_Response_Plan.md`

**Acceptance Criteria:**
- ✅ Incident classification defined with severity levels
- ✅ Response procedures documented for each phase
- ✅ At least 4 specific playbooks written
- ✅ Contact information included
- ✅ Post-incident review template provided

---

### Task 3: S3.18 — Security Monitoring Setup

**GitLab Issue:** #69
**Priority:** Medium

**What to do:**

Configure security monitoring and alerts for suspicious activity.

**WordPress.com constraint:** Limited server-side access. Focus on what we can monitor.

**Implementation approach:**

1. **WordPress health monitoring:**
   - Extend `/ns/v1/diagnostics/health` endpoint to include security checks
   - Monitor: failed auth attempts, snippet error counts, unexpected HTTP status codes
   - Build a snippet that runs on WP-Cron (daily) to check security indicators

2. **GitLab monitoring:**
   - Configure GitLab notifications for: push events, new members, pipeline failures
   - Verify webhook security (from S3.9 audit)

3. **Uptime monitoring:**
   - Verify site uptime monitoring is in place (WordPress.com may handle this)
   - If not, recommend an external uptime monitor (UptimeRobot, Better Stack, etc.)
   - Configure alert for downtime > 5 minutes

4. **Alert configuration:**
   - All security alerts go to admin email via `wp_mail()`
   - Define alert thresholds (e.g., >10 failed auth attempts in 1 hour)
   - Distinguish between informational and actionable alerts

**Snippet structure:**
- **S3-W3 Security Monitor** — daily cron that checks security indicators and emails alerts (~60-80 lines)

**Acceptance Criteria:**
- ✅ Security monitoring snippet deployed and active
- ✅ Daily security check runs via WP-Cron
- ✅ Alert thresholds defined and documented
- ✅ GitLab notifications configured
- ✅ Uptime monitoring documented (active or recommended)

---

### Task 4: S3.19 — Final Cyber Gate Review (Gate 7)

**GitLab Issue:** #70
**Priority:** Critical

**What to do:**

Perform the comprehensive Gate 7 review — verify all 7 cyber gates pass. This is the capstone review that confirms the entire security posture.

**Gate Review Checklist:**

| Gate | What to Verify | Evidence Required |
|------|---------------|-------------------|
| G0 | All S0 hardening still active (headers, HTTPS, wp-config, XML-RPC, dir listing, version hiding) | HTTP response headers, status codes |
| G1 | Data model security intact (input validation, sanitization) | Test with malformed input |
| G2 | RSS pipeline secure (feed sanitization, SSRF protection) | Test feed processing |
| G3 | Deployment security (snippet integrity, API auth) | Auth test on all endpoints |
| G4 | Social/publishing security (OG tags sanitized, no XSS vectors) | Source inspection |
| G5 | Integration gate security (cross-component auth, data flow) | End-to-end data flow test |
| G6 | Visual/infrastructure security (Wave 1 SEC: branches, plugins, file permissions) | Reference Wave 1 Cyber results |
| G7 | Pen test pass, monitoring active, incident plan exists, OPSEC clean | Reference S3.16-S3.18, S3.20-S3.21 |

**For each gate:**
1. Re-run the key verification checks
2. Document PASS/FAIL with evidence (HTTP status codes, response headers, test results)
3. If any gate fails, document the failure and remediation needed

**Output:** Gate 7 review matrix in results file with PASS/FAIL per gate and evidence.

**Acceptance Criteria:**
- ✅ All 7 gates reviewed with evidence
- ✅ No gate failures (or failures documented with remediation plan)
- ✅ Gate review matrix documented
- ✅ Sign-off recommendation (PASS or CONDITIONAL PASS with conditions)

---

### Task 5: S3.20 — OPSEC Review

**GitLab Issue:** #71
**Priority:** High

**What to do:**

Verify no secrets, credentials, or sensitive information are exposed in any public-facing location.

**Locations to check:**

1. **GitLab repository:**
   - Scan all committed files for: API keys, passwords, tokens, secrets
   - Check commit history (not just current state)
   - Check CI/CD variables — all should be masked
   - Check `.env` files, config files, debug output
   - Use GitLab secret detection if available on plan

2. **WordPress public pages:**
   - View source of all public pages — no secrets in HTML/JS
   - Check REST API responses — no credentials in JSON output
   - Verify error pages don't leak stack traces or paths
   - Check `robots.txt` and `sitemap.xml` for sensitive paths

3. **REST API endpoints:**
   - Unauthenticated requests should not return sensitive data
   - Verify auth-required endpoints properly reject anonymous requests
   - Check response headers for sensitive information (server version, etc.)

4. **Code Snippets:**
   - Verify no hardcoded credentials in any snippet code
   - Verify Token Vault encryption is working (credentials stored encrypted, not plaintext)
   - Verify snippet code is not exposed via any public endpoint

5. **DNS and infrastructure:**
   - Check DNS records for information leakage
   - Verify no internal hostnames or IPs exposed
   - Check SSL certificate for sensitive metadata

**Implementation approach:**
- GitLab: `GET /projects/:id/repository/tree` + `GET /projects/:id/repository/files/:file_path/raw` for scanning
- WordPress: `curl` or `wp_remote_get()` for endpoint testing
- Browser: view-source for public pages

**Acceptance Criteria:**
- ✅ GitLab repo scanned — no secrets in code or history
- ✅ WordPress pages clean — no credentials in source
- ✅ REST API responses clean — no sensitive data leakage
- ✅ Code Snippets clean — no hardcoded credentials
- ✅ Infrastructure clean — no internal details exposed

---

### Task 6: S3.21 — Security Documentation

**GitLab Issue:** #72
**Priority:** Medium

**What to do:**

Create comprehensive security documentation: architecture diagram, operational runbooks, and credential inventory.

**Documents to produce:**

1. **Security Architecture Document** (`07 Cyber Team/Security_Architecture.md`)
   - System overview with security boundaries
   - Authentication flow (WordPress App Password, GitLab PAT, Token Vault)
   - Data flow diagram showing trust boundaries
   - Network diagram (WordPress.com → user → GitLab → external feeds)
   - Security controls inventory (WAF, HTTPS, CSP, auth, encryption)

2. **Operational Runbooks** (`07 Cyber Team/Runbooks.md`)
   - Credential rotation procedure (WordPress App Password, GitLab PAT, Claude API key)
   - Snippet deployment security checklist
   - Security monitoring check procedures
   - Backup verification procedure
   - Emergency snippet disable procedure

3. **Credential Inventory** (`07 Cyber Team/Credential_Inventory.md`)
   - List all credentials in use (type, purpose, rotation frequency, storage location)
   - **NO actual credential values** — only metadata
   - Rotation schedule and last-rotated dates
   - Access matrix: who/what has access to each credential

**Acceptance Criteria:**
- ✅ Security architecture document complete with diagrams
- ✅ Operational runbooks cover all routine security procedures
- ✅ Credential inventory complete (no actual secrets!)
- ✅ All documents follow project markdown conventions

---

## 4. EXECUTION ORDER

```
1. S3.20 (OPSEC review)           — scan first to catch issues early       ~1.5h
2. S3.16 (penetration test)       — main security testing                  ~3-4h
3. S3.17 (incident response plan) — documentation task                     ~1h
4. S3.18 (security monitoring)    — snippet deployment + config            ~1.5h
5. S3.21 (security documentation) — architecture + runbooks                ~2h
6. S3.19 (final gate review)      — LAST — validates everything above      ~1.5h
```

**Rationale:** OPSEC review first catches low-hanging fruit. Pen test is the heaviest lift. Documentation tasks (S3.17, S3.21) can be done in parallel with monitoring setup (S3.18). Gate 7 review (S3.19) must be last because it validates all other tasks.

---

## 5. OUTPUT FILES

### Results File
**File:** `07 Cyber Team/S3_Wave3_CyberOps_Results.md`

Contents:
- Per-task result (PASS/PARTIAL/FAIL + evidence)
- OWASP Top 10 scan results matrix
- Gate 7 review matrix (all 7 gates)
- OPSEC findings
- Monitoring configuration details
- Remediation recommendations for any failures

### Security Documents (created as part of S3.21)
- `07 Cyber Team/Security_Architecture.md`
- `07 Cyber Team/Runbooks.md`
- `07 Cyber Team/Credential_Inventory.md`

### Incident Response Plan (created as part of S3.17)
- `07 Cyber Team/Incident_Response_Plan.md`

---

## 6. DONE CRITERIA — Wave 3 / Gate 7

Wave 3 is DONE when:
- [ ] S3.16: OWASP Top 10 pen test complete, no Critical/High findings open
- [ ] S3.17: Incident response plan documented with playbooks
- [ ] S3.18: Security monitoring snippet deployed and running
- [ ] S3.19: All 7 gates pass (or conditional pass documented)
- [ ] S3.20: OPSEC review clean — no secrets exposed anywhere
- [ ] S3.21: Security architecture, runbooks, and credential inventory documented
- [ ] All results and documents committed to GitLab
- [ ] GitLab issues #67, #68, #69, #70, #71, #72 closed (or updated)

**Gate 7 PASSED = Sprint 3 security is complete. Project is eligible for production-ready declaration.**

---

## 7. SECURITY REQUIREMENTS — NON-NEGOTIABLE

1. **No credentials in output files.** Scrub all results before saving.
2. **No tokens in commit messages.** Ever.
3. **Audit everything.** Every configuration change gets documented with before/after state.
4. **If something fails, document it.** Don't silently skip.
5. **Pen test findings with actual exploit steps must be handled carefully** — document the vulnerability and fix, not a reusable exploit guide.
6. **State your tool choice** at the start of every task with reasoning.

---

## 8. FILE REFERENCES

If you need deeper context:

| File | When to Read |
|------|-------------|
| `01 Scrum Master/Operational_Playbook.md` | Full platform details, tool hierarchy |
| `01 Scrum Master/Sprint_Tracker.md` | Current task statuses |
| `01 Scrum Master/Decision_Register.md` | Project decisions and trade-offs |
| `07 Cyber Team/S3_Wave1_CyberOps_Results.md` | Wave 1 Cyber results (S3.9, S3.10, S3.12) |
| `07 Cyber Team/Cyber_Team_Overwatch_Prompt.md` | Overwatch system prompt and gate definitions |

---

*Prepared by Taskmaster 2026-03-10. Sprint 3 Wave 3 / Gate 7 is BLOCKED until Waves 1+2 sign-off. This is the final gate. Execute thoroughly.*
