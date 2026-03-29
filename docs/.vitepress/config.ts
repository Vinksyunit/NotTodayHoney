import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'NotTodayHoney',
  description: 'Laravel honeypot package to detect and block attackers',
  srcExclude: ['superpowers/**'],
  ignoreDeadLinks: true,
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Home', link: '/' },
      { text: 'GitHub', link: 'https://github.com/Vinksyunit/NotTodayHoney' },
    ],
    sidebar: [
      { text: 'Getting Started', link: '/getting-started' },
      { text: 'Configuration', link: '/configuration' },
      { text: 'Traps', link: '/traps' },
      { text: 'Events & Middleware', link: '/events-middleware' },
      { text: 'Artisan Commands', link: '/commands' },
    ],
    socialLinks: [
      { icon: 'github', link: 'https://github.com/Vinksyunit/NotTodayHoney' },
    ],
  },
})
