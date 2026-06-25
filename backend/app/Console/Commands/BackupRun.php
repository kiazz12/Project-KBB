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
            mkdir($backupDir, 0755, true);
        }

        $filename = $config['database'].'_'.now()->format('Y-m-d_His').'.sql';
        $filepath = "{$backupDir}/{$filename}";

        $process = new Process([
            $dumpPath,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.($config['password'] ?? ''),
            '--routines',
            '--single-transaction',
            $config['database'],
        ]);

        $process->setTimeout(300);
        $process->setIdleTimeout(120);

        try {
            $process->mustRun();
            file_put_contents($filepath, $process->getOutput());
            $this->info("Backup saved: {$filepath}");

            $this->rotate($backupDir, self::RETENTION[$frequency]);
        } catch (ProcessFailedException $e) {
            $this->error('mysqldump failed: '.$e->getMessage());

            return 1;
        }

        return 0;
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
            $this->line('Removed old backup: '.basename($file));
        }
    }
}
