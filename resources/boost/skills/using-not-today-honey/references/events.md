# Handling NotTodayHoney Events

## The four events

| Event | Fires when |
|---|---|
| `Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent` | IP crosses the probing threshold |
| `Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent` | IP submits a trap login form |
| `Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent` | IP submits a login with a watchlisted password (immediate escalation) |
| `Vinksyunit\NotTodayHoney\Events\TrapCampaignDetectedEvent` | Global rate limit across all traps exceeded (broad-scan signal) |

The first three implement `Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent` and share:

- `getIp(): string`
- `getAttemptCount(): int`
- `getAlertLevel(): \Vinksyunit\NotTodayHoney\Enums\AlertLevel`
- `getDetection(): \Vinksyunit\NotTodayHoney\Models\AttackerDetection`
- `isTest(): bool` — `true` when dispatched for a whitelisted IP (no block, no DB record)

`TrapCampaignDetectedEvent` is standalone: `public readonly int $maxHits`, `public readonly int $decayMinutes`.

## Simple listener

@verbatim
<code-snippet name="Event::listen closure" lang="php">
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;

Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event): void {
    Log::critical('Known leaked password used from honeypot', [
        'ip' => $event->getIp(),
        'attempts' => $event->getAttemptCount(),
    ]);
});
</code-snippet>
@endverbatim

## Queued listener

Route high-severity events through a queue so HTTP response time stays decoupled from webhook latency.

@verbatim
<code-snippet name="Queued listener class" lang="php">
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;

class NotifySecurityTeamOfAttack implements ShouldQueue
{
    public string $queue = 'security';

    public function handle(AttackerAttackingEvent $event): void
    {
        // Post to Slack, open an incident, etc.
    }
}
</code-snippet>
@endverbatim

Register in a provider:

@verbatim
<code-snippet name="Register listener" lang="php">
use Illuminate\Support\Facades\Event;
use App\Listeners\NotifySecurityTeamOfAttack;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;

public function boot(): void
{
    Event::listen(AttackerAttackingEvent::class, NotifySecurityTeamOfAttack::class);
}
</code-snippet>
@endverbatim

## Branching on alert level

@verbatim
<code-snippet name="Match on AlertLevel" lang="php">
use Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

public function handle(AttackerAlertEvent $event): void
{
    match ($event->getAlertLevel()) {
        AlertLevel::PROBING => $this->logOnly($event),
        AlertLevel::INTRUSION_ATTEMPT => $this->ticketize($event),
        AlertLevel::ATTACKING => $this->page($event),
    };
}
</code-snippet>
@endverbatim

## Guidance

- Whitelisted IPs still dispatch events. Make listeners idempotent and safe for internal/CI traffic.
- For `TrapCampaignDetectedEvent`, correlate with CDN/WAF metrics before paging humans — broad internet scanning is noisy.
- Do not call `NotTodayHoney::unblock()` from inside a listener without a human-approval step.
