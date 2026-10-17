<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\Backup;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--force : Run even if automatic backup is switched off}';
    protected $description = 'Create the weekly backup: full copy on the server and a database copy by email';
    public function handle(): int
    {
        $s = Setting::map();
        if (!$this->option('force') && empty($s['auto_backup'])) {
            $this->info('Automatic backup is switched off in Admin > Backup.');
            return 0;
        }
        $r = Backup::run(!empty($s['auto_backup_email']));
        $r['ok'] ? $this->info($r['message']) : $this->error($r['message']);
        return $r['ok'] ? 0 : 1;
    }
}
