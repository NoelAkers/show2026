<?php

namespace App\Console\Commands;

use App\Services\MysqlImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class RestoreLatestBackup extends Command
{
    protected $signature = 'show:restore-latest
                            {--disk=backups : Filesystem disk to load the backup ZIP from}';

    protected $description = 'Restore the MySQL database from the most recent backup ZIP. '
        .'Use on the backup server after the live server fails.';

    public function __construct(private MysqlImporter $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $disk = $this->option('disk');

        $zips = $this->listBackupZips($disk);

        if (empty($zips)) {
            $this->error('No backup ZIP files found on the "'.$disk.'" disk.');

            return self::FAILURE;
        }

        $chosen = $this->chooseBackup($zips);

        if ($chosen === null) {
            $this->line('Restore cancelled.');

            return self::SUCCESS;
        }

        $this->info("Restoring from: {$chosen}");

        $tmpDir = $this->extractZip($disk, $chosen);

        if ($tmpDir === null) {
            return self::FAILURE;
        }

        $sqlFile = $this->findSqlFile($tmpDir);

        if ($sqlFile === null) {
            $this->error('No .sql dump found inside the backup ZIP.');
            $this->cleanUp($tmpDir);

            return self::FAILURE;
        }

        $result = $this->importer->import($sqlFile, config('database.connections.mysql'));

        $this->cleanUp($tmpDir);

        if (! $result['success']) {
            $this->error('mysql import failed.');
            if ($result['error'] !== '') {
                $this->line($result['error']);
            }

            return self::FAILURE;
        }

        $this->info('Database restored successfully.');

        return self::SUCCESS;
    }

    /** @return string[] Paths relative to the disk root, newest first */
    protected function listBackupZips(string $disk): array
    {
        return collect(Storage::disk($disk)->allFiles())
            ->filter(fn (string $f) => str_ends_with($f, '.zip'))
            ->sort()
            ->reverse()
            ->values()
            ->all();
    }

    protected function chooseBackup(array $zips): ?string
    {
        $candidates = array_slice($zips, 0, 5);

        if ($this->option('no-interaction')) {
            return $candidates[0];
        }

        $choice = $this->choice(
            'Which backup do you want to restore? (newest first)',
            $candidates,
            0
        );

        if (! $this->confirm("Restore \"{$choice}\"? This will overwrite the current database.", false)) {
            return null;
        }

        return $choice;
    }

    protected function extractZip(string $disk, string $zipPath): ?string
    {
        $tmpDir = sys_get_temp_dir().'/show-restore-'.uniqid();
        mkdir($tmpDir, 0700, true);

        $localPath = Storage::disk($disk)->path($zipPath);

        $zip = new ZipArchive;

        if ($zip->open($localPath) !== true) {
            $this->error("Could not open ZIP: {$localPath}");
            rmdir($tmpDir);

            return null;
        }

        $zip->extractTo($tmpDir);
        $zip->close();

        return $tmpDir;
    }

    protected function findSqlFile(string $dir): ?string
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'sql') {
                return $file->getPathname();
            }
        }

        return null;
    }

    protected function cleanUp(string $dir): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
