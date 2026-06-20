<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Backup schedule — frequency is controlled by BACKUP_MODE in .env:
 *
 *   pre_show (default) — hourly backup; daily cleanup and health monitor.
 *                        Suitable for the public-facing registration server running
 *                        in the ~20 days before the show.
 *
 *   show               — backup every 5 minutes; hourly cleanup; health monitor every
 *                        15 minutes. Suitable for the LAN live server on show day.
 *                        Backups are also pushed to the backup server when
 *                        BACKUP_SERVER_HOST is configured in .env.
 *
 * A server-level cron entry is required to drive the Laravel scheduler:
 *   * * * * * cd /path/to/show && php artisan schedule:run >> /dev/null 2>&1
 * (Laravel Cloud and Forge configure this automatically.)
 */
if (env('BACKUP_MODE') === 'show') {
    Schedule::command('backup:run --only-db --disable-notifications')->everyFiveMinutes();
    Schedule::command('backup:clean')->hourly();
    Schedule::command('backup:monitor')->everyFifteenMinutes();
} else {
    Schedule::command('backup:run --only-db --disable-notifications')->hourly();
    Schedule::command('backup:clean')->daily()->at('01:00');
    Schedule::command('backup:monitor')->daily()->at('01:30');
}
