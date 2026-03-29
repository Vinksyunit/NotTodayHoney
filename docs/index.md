---
layout: home

hero:
  name: "NotTodayHoney"
  image:
    src: /logo.svg
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
  - icon: 🪤
    title: Realistic Traps
    details: Fake WordPress wp-login, phpMyAdmin, and generic admin login pages that look authentic to scanners and bots.
  - icon: 🚨
    title: 3-Level Detection
    details: Probing (page visits), Intrusion Attempt (login try), and Attacking (known leaked credentials) alert levels.
  - icon: 🔑
    title: Leaked Credential Detection
    details: Detect automated attacks using bcrypt hash comparison against known leaked password lists like RockYou.
  - icon: 🛡️
    title: Automatic IP Blocking
    details: Block detected attackers for configurable durations — minutes for probing, days for intrusion, weeks for attacking.
  - icon: 📣
    title: Event-Driven Alerts
    details: Laravel events dispatched at each alert level. Wire up Slack, mail, or any notification channel via listeners.
  - icon: 🔒
    title: Middleware Protection
    details: Apply honeypot.block middleware globally or per route group to deny blocked IPs access instantly.
---
