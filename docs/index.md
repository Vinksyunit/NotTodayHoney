---
layout: home

hero:
  name: "NotTodayHoney"
  image:
    src: /logo-sticker.svg
    alt: NotTodayHoney
  text: "Stop attackers before they start"
  tagline: "Laravel honeypot traps with 3-level detection, automatic IP blocking, and event-driven alerts"
  actions:
    - theme: brand
      text: Get Started
      link: /getting-started
    - theme: alt
      text: GitHub
      link: https://github.com/Vinksyunit/NotTodayHoney

features:
  - icon: 🔍
    title: 3-Level Detection
    details: Probing, Intrusion Attempt, and Attacking — each with configurable thresholds, block durations, and log levels.
    link: /guides/detect-and-notify
  - icon: 📣
    title: Campaign Detection
    details: Global rate limiting dispatches TrapCampaignDetectedEvent when a coordinated attack is detected across all traps.
    link: /guides/detect-and-notify
  - icon: 🛡️
    title: Automatic IP Blocking
    details: Detected attackers are blocked for configurable durations — minutes for probing, days for intrusion, weeks for attacking.
    link: /guides/protect-features
  - icon: 🔒
    title: Middleware Protection
    details: Apply nottodayhoney.block globally or per route group to deny blocked IPs access instantly.
    link: /guides/protect-features
  - icon: 🪤
    title: Realistic Decoys
    details: Fake WordPress, phpMyAdmin, and admin pages with HTTP fingerprinting to attract CVE scanners and credential-stuffing bots.
    link: /traps
  - icon: 🔑
    title: Leaked Credential Detection
    details: Truncated SHA256 comparison against known password lists. Immediate escalation to Attacking on match.
    link: /guides/passwords
---

## Sponsors

### Special Sponsors

<p align="center">
  <a href="https://starkado.com/" target="_blank" rel="noopener">
    <span style="display:inline-block;background:#ffffff;border-radius:8px;padding:12px 20px">
      <img src="https://starkado.com/assets/starkado-sticker.svg" alt="Starkado" width="160">
    </span>
  </a>
</p>

