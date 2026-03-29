# Artisan Commands

## honey:status

Display all currently blocked IPs and their detection details.

```bash
php artisan honey:status
```

**Example output:**

```
+-------------+---------------------+----------+---------------------+
| IP          | Alert Level         | Attempts | Blocked Until       |
+-------------+---------------------+----------+---------------------+
| 1.2.3.4     | attacking           | 1        | 2026-04-28 12:00:00 |
| 5.6.7.8     | intrusion_attempt   | 3        | 2026-03-30 08:15:00 |
+-------------+---------------------+----------+---------------------+
```

If no IPs are blocked, the command prints `No IPs are currently blocked.`

## honey:unblock

Remove all detection records for a given IP address, immediately lifting the block.

```bash
php artisan honey:unblock {ip}
```

**Example:**

```bash
php artisan honey:unblock 1.2.3.4
# IP 1.2.3.4 has been unblocked.
```

If the IP is not currently blocked:

```bash
php artisan honey:unblock 9.9.9.9
# IP 9.9.9.9 is not currently blocked.
```

::: tip
You can also unblock programmatically via the facade:
```php
NotTodayHoney::unblock('1.2.3.4');
```
:::
