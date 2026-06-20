<?php

use App\Services\MysqlImporter;
use Illuminate\Support\Facades\Storage;

// ---------------------------------------------------------------------------
// show:restore-latest command
// ---------------------------------------------------------------------------

it('fails when no backup ZIPs exist on the disk', function (): void {
    Storage::fake('backups');

    $this->artisan('show:restore-latest', ['--no-interaction' => true])
        ->assertFailed()
        ->expectsOutputToContain('No backup ZIP files found');
});

it('fails when the ZIP contains no SQL file', function (): void {
    Storage::fake('backups');

    $zip = new ZipArchive;
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-backup-').'.zip';
    $zip->open($tmpZip, ZipArchive::CREATE);
    $zip->addFromString('readme.txt', 'not a sql file');
    $zip->close();

    Storage::disk('backups')->put('Show/2026-06-20-12-00-00.zip', file_get_contents($tmpZip));
    unlink($tmpZip);

    $this->artisan('show:restore-latest', ['--no-interaction' => true])
        ->assertFailed()
        ->expectsOutputToContain('No .sql dump found');
});

it('selects the newest ZIP by filename when multiple backups exist', function (): void {
    Storage::fake('backups');

    $sqlContent = '-- SQL dump';

    foreach (['2026-06-20-10-00-00', '2026-06-20-11-00-00', '2026-06-20-12-00-00'] as $timestamp) {
        $zip = new ZipArchive;
        $tmpZip = tempnam(sys_get_temp_dir(), 'test-backup-').'.zip';
        $zip->open($tmpZip, ZipArchive::CREATE);
        $zip->addFromString('dump.sql', "-- dump for {$timestamp}");
        $zip->close();

        Storage::disk('backups')->put("Show/{$timestamp}.zip", file_get_contents($tmpZip));
        unlink($tmpZip);
    }

    $capturedSql = null;

    $this->mock(MysqlImporter::class)
        ->shouldReceive('import')
        ->once()
        ->andReturnUsing(function (string $sqlFile) use (&$capturedSql): bool {
            $capturedSql = file_get_contents($sqlFile);

            return true;
        });

    $this->artisan('show:restore-latest', ['--no-interaction' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Database restored successfully');

    // Newest ZIP (2026-06-20-12-00-00) should have been selected
    expect($capturedSql)->toBe('-- dump for 2026-06-20-12-00-00');
});

it('reports success when the import succeeds', function (): void {
    Storage::fake('backups');

    $zip = new ZipArchive;
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-backup-').'.zip';
    $zip->open($tmpZip, ZipArchive::CREATE);
    $zip->addFromString('dump.sql', '-- SQL dump');
    $zip->close();

    Storage::disk('backups')->put('Show/2026-06-20-12-00-00.zip', file_get_contents($tmpZip));
    unlink($tmpZip);

    $this->mock(MysqlImporter::class)->shouldReceive('import')->once()->andReturn(true);

    $this->artisan('show:restore-latest', ['--no-interaction' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Database restored successfully');
});

it('reports failure and returns a failure exit code when the import fails', function (): void {
    Storage::fake('backups');

    $zip = new ZipArchive;
    $tmpZip = tempnam(sys_get_temp_dir(), 'test-backup-').'.zip';
    $zip->open($tmpZip, ZipArchive::CREATE);
    $zip->addFromString('dump.sql', '-- SQL dump');
    $zip->close();

    Storage::disk('backups')->put('Show/2026-06-20-12-00-00.zip', file_get_contents($tmpZip));
    unlink($tmpZip);

    $this->mock(MysqlImporter::class)->shouldReceive('import')->once()->andReturn(false);

    $this->artisan('show:restore-latest', ['--no-interaction' => true])
        ->assertFailed();
});
