<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class MysqlImporter
{
    /**
     * Import a SQL dump file into the configured MySQL database.
     *
     * Credentials are written to a temp option file so the password never
     * appears in the process list.
     *
     * @param  array<string, mixed>  $connection  A database.connections.mysql config array
     */
    public function import(string $sqlFile, array $connection): bool
    {
        $optFile = tempnam(sys_get_temp_dir(), 'mysql-opt-');
        file_put_contents($optFile, sprintf(
            "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
            $connection['host'],
            $connection['port'],
            $connection['username'],
            $connection['password'],
        ));
        chmod($optFile, 0600);

        $process = new Process(['mysql', '--defaults-extra-file='.$optFile, $connection['database']]);
        $process->setInput(fopen($sqlFile, 'r'));
        $process->setTimeout(300);
        $process->run();

        unlink($optFile);

        return $process->isSuccessful();
    }
}
