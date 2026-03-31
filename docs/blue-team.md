# Blue Team Practices

NotTodayHoney is a detection and alerting layer. It tells you an attacker is present and records what they tried. It does not stop packets, enforce firewall rules, or protect your application from vulnerabilities.

Understanding what a honeypot is — and isn't — helps you build a security posture that actually holds.

## Defense in depth

No single tool provides complete security. Defense in depth means stacking independent layers so that the failure of any one layer does not compromise the whole system.

NotTodayHoney fits in the **detection** layer. It should sit alongside:

- An **edge layer** (reverse proxy, CDN, WAF) that handles TLS termination, request filtering, and IP reputation checks before traffic reaches your application
- An **OS/network layer** that acts on blocked IPs at the infrastructure level — before your web server even starts processing a request
- A **hardened application layer** where real admin routes require strong authentication, and attack surface is minimised

The [OWASP Top 10](https://owasp.org/www-project-top-10/) is a good starting point for understanding the most common application-layer risks. The [OWASP Application Security Verification Standard (ASVS)](https://owasp.org/www-project-application-security-verification-standard/) provides a more structured checklist for assessing the security of your application.

## Code reviews

A security-focused code review looks for issues that functional review misses: input handling, authentication logic, authorisation boundaries, direct object references, and dependency risks.

Key practices:
- Review authentication and authorisation code on every change, not just when the feature touches security explicitly
- Look for injection vectors (SQL, command, template) in any code that handles user-supplied data
- Check that sensitive operations (password reset, privilege escalation, data export) require the right level of authentication
- Use automated tooling (static analysis, `composer audit`) to catch known CVEs in dependencies — but treat tool output as a starting point, not a conclusion

The [OWASP Code Review Guide](https://owasp.org/www-project-code-review-guide/) covers this in depth.

## Penetration testing

A pentest is a structured attack against your system by someone whose job is to find what you missed. It is complementary to a honeypot, not a replacement.

**What a pentest finds that a honeypot doesn't:**
- Vulnerabilities in your actual application logic (auth bypass, privilege escalation, IDOR)
- Misconfigurations in your infrastructure (exposed admin ports, overpermissioned services)
- Weaknesses in your API design

**What a honeypot reveals that a pentest doesn't:**
- Real-world attacker behaviour against your production system
- Whether automated scanners are actively targeting your stack
- Credential lists that attackers are currently using in the wild

Black box pentests (no prior knowledge of the system) simulate an opportunistic attacker. Grey box tests (partial knowledge) simulate a more targeted attack. Both are useful at different stages of maturity.

Consider scheduling a pentest after major architecture changes, before public launches, and at regular intervals once the system is in production. The [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/) describes methodologies in detail.

## Monitoring and incident response

Detection without response is just logging. When `AttackerAttackingEvent` fires, it means an automated tool submitted a known leaked password against your honeypot. That is a serious signal — not something to read in a log file the next morning.

Key practices:
- Route high-severity events (Attacking, campaign detection) to an alerting pipeline that pages someone in real time
- Keep a runbook: what does your team do when an attacker is detected? Who is notified? What is the escalation path?
- Feed honeypot events into your SIEM if you have one — correlate them with other signals (failed real logins, unusual traffic patterns)
- Define what "blocked" means operationally: is an IP blocked at the Laravel level only, or does your infrastructure act on it too?

## Dependency hygiene

Attackers scan for known CVEs before trying credentials. A vulnerable dependency advertised in a public file (a `composer.lock` checked into a public repo, or a plugin `readme.txt`) is an invitation.

- Run `composer audit` regularly and treat high-severity findings as blocking issues
- Subscribe to security advisories for your core dependencies (Laravel, database drivers, auth packages)
- Review and remove unused packages — every dependency is a potential attack surface

The [NIST National Vulnerability Database](https://nvd.nist.gov/) and [CVE.mitre.org](https://cve.mitre.org/) are authoritative sources for known vulnerabilities.

---

::: tip Further reading
- [OWASP Top 10](https://owasp.org/www-project-top-10/)
- [OWASP ASVS](https://owasp.org/www-project-application-security-verification-standard/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [OWASP Code Review Guide](https://owasp.org/www-project-code-review-guide/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
:::
