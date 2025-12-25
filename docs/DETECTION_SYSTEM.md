# NotTodayHoney Detection System Documentation

## Overview

NotTodayHoney implements a **rule-based intelligent attacker detection system** designed to identify, classify, and respond to malicious activity targeting your application. The system uses a 3-level alert hierarchy with configurable thresholds, time windows, and automated blocking capabilities.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Honeypot Traps                               │
│     (WordPress, phpMyAdmin, Generic Admin Endpoints)           │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│               AttackerDetectionService                          │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────────────────┐ │
│  │ Record      │  │ Threshold    │  │ Alert                  │ │
│  │ Attempt     │──▶ Checker      │──▶ Dispatcher             │ │
│  │             │  │              │  │                        │ │
│  └─────────────┘  └──────────────┘  └────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                  AttackerDetection Model                        │
│         (Database Storage with IP Hashing & Blocking)          │
└─────────────────────────────────────────────────────────────────┘
```

## Alert Levels

The detection system classifies threats into three escalating levels:

### Level 1: PROBING

**Classification:** Reconnaissance/Exploration

**Triggers:**
- Accessing honeypot trap endpoints (e.g., `/wp-admin`, `/phpmyadmin`, `/admin`)
- Scanning for vulnerable paths

**Default Configuration:**
| Parameter | Default Value | Description |
|-----------|---------------|-------------|
| `threshold` | 3 | Attempts before alert triggers |
| `time_window` | 1440 min (1 day) | Window for counting attempts |
| `duration` | 1440 min (1 day) | Block duration when triggered |
| `notify` | false | Send notifications |

**Event Dispatched:** `AttackerProbingEvent`

---

### Level 2: INTRUSION_ATTEMPT

**Classification:** Active Attack Attempt

**Triggers:**
- Any login attempt on honeypot forms
- Credential submission (regardless of credentials used)

**Default Configuration:**
| Parameter | Default Value | Description |
|-----------|---------------|-------------|
| `threshold` | 1 | Attempts before alert triggers |
| `time_window` | 1440 min (1 day) | Window for counting attempts |
| `duration` | 10080 min (7 days) | Block duration when triggered |
| `notify` | true | Send notifications |
| `channels` | stack, slack | Notification channels |

**Event Dispatched:** `AttackerIntrusionAttemptEvent`

---

### Level 3: ATTACKING

**Classification:** Known Credential Usage

**Triggers:**
- Using a password hash found in known leak databases (e.g., RockYou)
- Indicates attacker has access to leaked credential lists

**Default Configuration:**
| Parameter | Default Value | Description |
|-----------|---------------|-------------|
| `threshold` | 1 | Attempts before alert triggers |
| `time_window` | 1440 min (1 day) | Window for counting attempts |
| `duration` | null (permanent) | Block duration when triggered |
| `notify` | true | Send notifications |
| `channels` | stack, slack, mail | Notification channels |

**Event Dispatched:** `AttackerAttackingEvent`

---

## Core Components

### AttackerDetectionService

Located at: `src/Services/AttackerDetectionService.php`

The main service orchestrating all detection logic.

#### Methods

| Method | Description |
|--------|-------------|
| `recordAttempt(string $ip, AlertLevel $level)` | Records an attack attempt and checks thresholds |
| `isBlocked(string $ip)` | Checks if an IP is currently blocked |
| `getDetection(string $ip)` | Retrieves detection record for an IP |
| `getBlockedIps()` | Returns all currently blocked IPs |
| `getDetectionsByLevel(AlertLevel $level)` | Gets detections filtered by alert level |
| `resetDetection(string $ip)` | Removes detection records for an IP |

#### Detection Algorithm

```php
1. Hash incoming IP address (SHA-256)
2. Look for existing detection within time window
3. If found: increment attempt counter
4. If not found: create new detection record
5. Check if threshold reached
6. If threshold reached: block IP and dispatch event
```

### AttackerDetection Model

Located at: `src/Models/AttackerDetection.php`

Eloquent model for storing detection records.

#### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `ip` | string | Original IP address |
| `ip_hash` | string | SHA-256 hash of IP |
| `attempt_count` | integer | Number of attempts |
| `alert_level` | enum | Current threat level |
| `blocked_at` | timestamp | When block was applied |
| `blocked_until` | timestamp | Block expiration time |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Last update time |

#### Scopes

- `blocked()` - Get currently blocked attackers
- `byAlertLevel(AlertLevel $level)` - Filter by threat level
- `withinTimeWindow(int $minutes)` - Filter by time window
- `forIpAndLevel(string $ipHash, AlertLevel $level, int $timeWindow)` - Find specific detection

### AlertLevel Enum

Located at: `src/Enums/AlertLevel.php`

```php
enum AlertLevel: string
{
    case PROBING = 'probing';
    case INTRUSION_ATTEMPT = 'intrusion_attempt';
    case ATTACKING = 'attacking';
}
```

## Trap Behaviors

When a honeypot trap is triggered, the system can respond with different behaviors:

| Behavior | HTTP Code | Description |
|----------|-----------|-------------|
| `FORBIDDEN` | 403 | Returns "Access Forbidden" response |
| `ERROR` | 500 | Simulates a server error |
| `INFINITE_LOADING` | - | Stalls request until timeout (tarpit) |
| `FAKE_SUCCESS` | 200 | Shows fake dashboard (requires username match) |

## Privacy & Security

### IP Anonymization

All IP addresses are hashed using SHA-256 before storage:

```php
protected function hashIp(string $ip): string
{
    return hash('sha256', $ip);
}
```

This ensures:
- Original IPs cannot be recovered from the database
- Compliance with privacy regulations
- Protection against data breaches

### Credential Matching

The system uses bcrypt password hashes for credential matching, ensuring:
- Monitored passwords remain secure
- Comparison is performed securely using Laravel's `Hash::check()`

## Event System

The detection system dispatches Laravel events for each alert level:

```php
// Listen to detection events in your EventServiceProvider
protected $listen = [
    AttackerProbingEvent::class => [
        LogProbingAttempt::class,
    ],
    AttackerIntrusionAttemptEvent::class => [
        NotifySecurityTeam::class,
    ],
    AttackerAttackingEvent::class => [
        BlockAndAlert::class,
        NotifyAllChannels::class,
    ],
];
```

### Event Interface

All events implement `AttackerAlertEvent`:

```php
interface AttackerAlertEvent
{
    public function getIp(): string;
    public function getAttemptCount(): int;
    public function getAlertLevel(): AlertLevel;
    public function getDetection(): AttackerDetection;
}
```

## Usage Examples

### Recording an Attack

```php
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

// Record a probing attempt
NotTodayHoney::recordAttempt($request->ip(), AlertLevel::PROBING);

// Record an intrusion attempt
NotTodayHoney::recordAttempt($request->ip(), AlertLevel::INTRUSION_ATTEMPT);

// Record an attacking attempt (known credentials used)
NotTodayHoney::recordAttempt($request->ip(), AlertLevel::ATTACKING);
```

### Checking Block Status

```php
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;

if (NotTodayHoney::isBlocked($request->ip())) {
    abort(403, 'Access denied');
}
```

### Middleware Integration

```php
public function handle($request, Closure $next)
{
    if (NotTodayHoney::isBlocked($request->ip())) {
        return response('Forbidden', 403);
    }

    return $next($request);
}
```

## Configuration Reference

Full configuration in `config/not-today-honey.php`:

```php
return [
    'whitelist' => ['127.0.0.1'],  // Never blocked IPs

    'credentials' => [
        'usernames' => ['admin', 'root'],
        'passwords' => [
            ['id' => 'rockyou_top_1', 'hash' => '...'],
        ],
    ],

    'alerts' => [
        'probing' => [...],
        'intrusion_attempt' => [...],
        'attacking' => [...],
    ],

    'traps' => [
        'wordpress' => [...],
        'phpmyadmin' => [...],
        'generic_admin' => [...],
    ],
];
```

## Best Practices

1. **Monitor Events:** Set up listeners for all alert events to track attacks
2. **Review Thresholds:** Adjust thresholds based on your traffic patterns
3. **Use Notifications:** Enable notifications for `intrusion_attempt` and `attacking` levels
4. **Whitelist Testing IPs:** Add development/testing IPs to whitelist
5. **Regular Review:** Periodically review blocked IPs and detection logs
6. **Extend Traps:** Add custom traps for paths specific to your application

## Threat Intelligence Flow

```
Attacker Request
       │
       ▼
┌──────────────────┐
│   Hits Trap?     │──No──▶ Normal Application Flow
└────────┬─────────┘
         │ Yes
         ▼
┌──────────────────┐
│ Record Attempt   │
│ (Hash IP)        │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Threshold Met?   │──No──▶ Continue Monitoring
└────────┬─────────┘
         │ Yes
         ▼
┌──────────────────┐
│ Block IP         │
│ Dispatch Event   │
│ Send Notification│
└──────────────────┘
```

## Version History

- **v0.1.0** - Initial release with 3-level alert system
