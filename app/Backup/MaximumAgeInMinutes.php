<?php

namespace App\Backup;

use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\HealthCheck;

class MaximumAgeInMinutes extends HealthCheck
{
    public function __construct(
        protected int $minutes = 1440
    ) {}

    public function checkHealth(BackupDestination $backupDestination): void
    {
        $this->failIf(
            $backupDestination->backups()->isEmpty(),
            trans('backup::notifications.unhealthy_backup_found_empty')
        );

        $newestBackup = $backupDestination->backups()->newest();

        $this->failIf(
            $newestBackup->date()->lt(now()->subMinutes($this->minutes)),
            trans('backup::notifications.unhealthy_backup_found_old', ['date' => $newestBackup->date()->format('Y/m/d H:i:s')])
        );
    }
}
