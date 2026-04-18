import { defineConfig } from 'vitepress'

export default defineConfig({
  base: '/NotTodayHoney/',
  title: 'NotTodayHoney',
  description: 'Laravel honeypot package to detect and block attackers',
  srcExclude: [],
  ignoreDeadLinks: true,
  themeConfig: {
    logo: '/logo-sticker.svg',
    nav: [
      { text: 'Home', link: '/' },
      { text: 'GitHub', link: 'https://github.com/Vinksyunit/NotTodayHoney' },
    ],
    sidebar: [
      { text: 'Getting Started', link: '/getting-started' },
      { text: 'Blue Team Practices', link: '/blue-team' },
      {
        text: 'Guides',
        items: [
          { text: 'Detect & Get Notified', link: '/guides/detect-and-notify' },
          { text: 'Protect Your Features', link: '/guides/protect-features' },
          { text: 'Compromised Passwords', link: '/guides/passwords' },
        ],
      },
      {
        text: 'Components',
        items: [
          { text: 'Traps', link: '/traps' },
          { text: 'Configuration', link: '/configuration' },
          { text: 'Events & Middleware', link: '/events-middleware' },
          { text: 'Artisan Commands', link: '/commands' },
        ],
      },
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/Vinksyunit/NotTodayHoney' },
    ],
  },
})
