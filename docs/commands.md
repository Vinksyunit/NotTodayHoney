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

## honey:generate-salt

Generate a random salt for the password detection system. Run this once during setup and store the output in `.env`.

```bash
php artisan honey:generate-salt
```

**Example output:**

```
NOT_TODAY_HONEY_PASSWORD_SALT=a3f8c2e1d9b74056
```

Copy the output line into your `.env` file.

::: warning
Regenerating the salt invalidates all existing custom hashes. Only run this once. If you lose the salt, you must regenerate it and re-hash all your custom passwords.
:::

## honey:hash-password

Generate a truncated SHA256 hash for a password to add to your custom detection list.

```bash
php artisan honey:hash-password {password}
```

**Example:**

```bash
php artisan honey:hash-password "s3cr3t!"
# NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST=7f4a2b1c
```

Add the hash to your existing list in `.env`:

```env
NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST=7f4a2b1c,3d9e1f2a,c8b05a44
```

::: tip
`NOT_TODAY_HONEY_PASSWORD_SALT` must be set before running this command. See [honey:generate-salt](#honey-generate-salt).
:::
