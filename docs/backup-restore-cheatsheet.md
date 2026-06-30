# Backup & Restore — Operator Cheat-Sheet

Quick-reference for backup, migration, and emergency restore operations.
See `docs/project-phases.md` (Phase 12.1) for the full design rationale.

---

## .env quick-reference

| Variable | Pre-show server | Live server (show day) |
|----------|-----------------|------------------------|
| `BACKUP_MODE` | `pre_show` | `show` |
| `BACKUP_SERVER_HOST` | *(blank)* | LAN IP of backup server e.g. `192.168.1.50` |
| `BACKUP_SERVER_USER` | *(blank)* | SSH username on backup server |
| `BACKUP_SERVER_PRIVATE_KEY_PATH` | *(blank)* | Path to SSH private key on live server |
| `BACKUP_SERVER_PATH` | *(blank)* | `/backups/show` |
| `BACKUP_NOTIFICATION_EMAIL` | admin email | admin email |

---

## Pre-show server (public registration, ~20 days before)

The scheduler runs `backup:run` hourly automatically once the server cron is in place.

```bash
# Verify the cron is driving the scheduler (run once on server setup)
crontab -l
# Should contain: * * * * * cd /path/to/show && php artisan schedule:run >> /dev/null 2>&1

# Confirm what's scheduled
php artisan schedule:list

# Trigger an immediate backup manually
php artisan backup:run --only-db

# List all stored backups (shows size and age)
php artisan backup:list

# Check backup health (exits non-zero and sends an email if unhealthy)
php artisan backup:monitor

# Manually remove backups outside the retention window
php artisan backup:clean
```

Backups are stored in `storage/app/backups/Calverley Show 2026/`.

---

## Migrating from pre-show server to live server (one-time, day before show)

Run these steps in order.

### Step 1 — Create the final snapshot on the pre-show server

```bash
php artisan backup:run --only-db
php artisan backup:list   # note the filename of the newest backup
```

### Step 2 — Transfer the backup to the live server

```bash
# From your local machine (or directly from pre-show server if SSH access allows)
scp user@pre-show-server:/path/to/show/storage/app/backups/"CalverleyShow2026"/YYYY-MM-DD-HH-II-SS.zip \
    user@live-server:/path/to/show/storage/app/backups/"CalverleyShow2026"/

# Alternative: copy to USB, then to live server
```

### Step 3 — Configure and restore on the live server

```bash
# Ensure .env on the live server has:
#   BACKUP_MODE=show
#   BACKUP_SERVER_HOST=192.168.x.x   (backup server LAN IP)
#   BACKUP_SERVER_USER=...
#   BACKUP_SERVER_PRIVATE_KEY_PATH=...

# Run the restore (interactive — picks from last 5 backups)
php artisan show:restore-latest

# Or without prompts (always picks the newest backup)
php artisan show:restore-latest --no-interaction

# Verify the restore was successful
php artisan tinker --execute 'echo "Exhibitors: " . \App\Models\Exhibitor::count();'
php artisan tinker --execute 'echo "Entries: " . \App\Models\Entry::count();'
```

### Step 4 — Confirm the backup schedule is running on the live server

```bash
php artisan schedule:list
# Should show backup:run every 5 minutes

# Trigger one manual backup to seed the backup server before the show starts
php artisan backup:run --only-db
php artisan backup:list  # confirm backup appears on both local and backup-server disks
```

---

## During the show (live server)

The scheduler backs up every 5 minutes and pushes to the backup server automatically.

```bash
# Check the latest backup was taken recently
php artisan backup:list

# Force an immediate backup at any time
php artisan backup:run --only-db

# Check backup health (alert if newest backup > ~15 min old)
php artisan backup:monitor
```

---

## Emergency restore to backup server (live server has failed)

Maximum data loss: 5 minutes (one backup interval).

### On the backup server

```bash
# 1. Confirm recent backups have arrived (should be < 5 min old)
php artisan backup:list

# 2. Restore the latest backup into this server's MySQL database
php artisan show:restore-latest --no-interaction

# 3. Verify row counts look right
php artisan tinker --execute 'echo "Exhibitors: " . \App\Models\Exhibitor::count();'
php artisan tinker --execute 'echo "Entries: " . \App\Models\Entry::count();'
php artisan tinker --execute 'echo "Results: " . \App\Models\Result::count();'
```

### Tell staff the new address

Point browsers and the Flutter app to the **backup server's LAN IP address**.

If the app is configured with a hostname, update the DHCP reservation on the router so the
hostname resolves to the backup server's IP, or update `.env` `APP_URL` and restart the server.

---

## Useful diagnostics

```bash
# Show all scheduled tasks and their next run time
php artisan schedule:list

# Show the last Laravel error log entries
php artisan pail --timeout=0

# Check MySQL connectivity
php artisan db:show

# Count key records to sanity-check a restore
php artisan tinker --execute '
    echo "Sections: "   . \App\Models\ShowSection::count() . "\n";
    echo "Classes: "    . \App\Models\ShowClass::count()   . "\n";
    echo "Exhibitors: " . \App\Models\Exhibitor::count()   . "\n";
    echo "Entries: "    . \App\Models\Entry::count()       . "\n";
    echo "Results: "    . \App\Models\Result::count()      . "\n";
'
```
