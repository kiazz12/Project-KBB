<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupRun extends Command
{
    protected $signature = 'backup:run {--frequency=daily : daily|weekly|monthly|yearly}';

    protected $description = 'Backup database using mysqldump';

    private const RETENTION = [
        'daily' => 7,
        'weekly' => 4,
        'monthly' => 12,
        'yearly' => 10,
    ];

    public function handle(): int
    {
        $frequency = $this->option('frequency');
        if (! in_array($frequency, array_keys(self::RETENTION))) {
            $this->error('Invalid frequency. Use: '.implode('|', array_keys(self::RETENTION)));

            return 1;
        }

        $connection = config('database.default');
        if ($connection !== 'mysql') {
            $this->warn("Active connection is '{$connection}', not MySQL. Skipping backup.");

            return 0;
        }

        $config = config('database.connections.mysql');
        $dumpPath = env('DB_DUMP_PATH', 'mysqldump');
        $backupDir = storage_path("backups/{$frequency}");

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0700, true);
        }

        $filename = $config['database'].'_'.now()->format('Y-m-d_His').'.sql';
        $filepath = "{$backupDir}/{$filename}";

        // REA S-11: kirim kredensial lewat option file sementara, bukan
        // argumen CLI, agar password tidak terlihat di daftar proses.
        $defaultsFile = $this->writeCredentialsFile($config);

        try {
            $process = new Process([
                $dumpPath,
                '--defaults-extra-file='.$defaultsFile,
                '--routines',
                '--single-transaction',
                $config['database'],
            ]);

            $process->setTimeout(300);
            $process->setIdleTimeout(120);

            try {
                $process->mustRun();
                file_put_contents($filepath, $process->getOutput());
                @chmod($filepath, 0600);
                $this->writeChecksum($filepath);

                $this->info("Backup saved: {$filepath}");

                $this->rotate($backupDir, self::RETENTION[$frequency]);
            } catch (ProcessFailedException $e) {
                $this->error('mysqldump failed: '.$e->getMessage());

                return 1;
            }
        } finally {
            @unlink($defaultsFile);
        }

        return 0;
    }

    private function writeCredentialsFile(array $config): string
    {
        $path = tempnam(sys_get_temp_dir(), 'kbb-backup-');

        if ($path === false) {
            throw new \RuntimeException('Unable to create temporary MySQL option file.');
        }

        $contents = "[client]\n"
            .'host='.$this->escapeOption($config['host'])."\n"
            .'port='.$this->escapeOption($config['port'])."\n"
            .'user='.$this->escapeOption($config['username'])."\n"
            .'password='.$this->escapeOption($config['password'] ?? '')."\n";

        file_put_contents($path, $contents);
        @chmod($path, 0600);

        return $path;
    }

    private function escapeOption(?string $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value).'"';
    }

    private function writeChecksum(string $filepath): void
    {
        $checksum = hash_file('sha256', $filepath);

        if ($checksum !== false) {
            file_put_contents($filepath.'.sha256', $checksum.'  '.basename($filepath).PHP_EOL);
            @chmod($filepath.'.sha256', 0600);
        }
    }

    private function rotate(string $dir, int $keep): void
    {
        $files = glob("{$dir}/*.sql");
        if (! $files) {
            return;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $file) {
            unlink($file);
            @unlink($file.'.sha256');
            $this->line('Removed old backup: '.basename($file));
        }
    }
}
