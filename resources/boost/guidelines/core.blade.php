## NotTodayHoney

Laravel honeypot that exposes fake admin pages (`/wp-admin`, `/phpmyadmin`, `/admin`) to detect and auto-block attackers via a 3-level alert pipeline: Probing → Intrusion Attempt → Attacking.

### Critical
- Trap paths must NOT collide with real application routes. Disable any trap whose default path the app already uses (env: `NOT_TODAY_HONEY_{WP,PMA,GENERIC}_ENABLED=false`).
- Never apply auth, CSRF, or rate-limit middleware to the package's trap routes — they must be freely reachable by attackers.
- `NOT_TODAY_HONEY_PASSWORD_SALT` must stay secret and stable. Generate once with `php artisan honey:generate-salt`; rotating it invalidates every custom hash.

Task-specific usage (configuration, events, middleware/facade, CLI) lives in the bundled `using-not-today-honey` skill.
