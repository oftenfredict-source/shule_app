<?php

namespace App\Console\Commands;

use App\Models\SchoolAttendanceSmsSchedule;
use App\Services\AttendanceSmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAutoAttendanceSms extends Command
{
    protected $signature = 'attendance:send-auto-sms';

    protected $description = 'Send attendance SMS automatically based on Super Admin schedule settings';

    public function handle(AttendanceSmsService $attendanceSmsService): int
    {
        $now = Carbon::now();
        $this->info('Checking auto attendance SMS schedules at ' . $now->format('Y-m-d H:i'));

        $configs = SchoolAttendanceSmsSchedule::query()
            ->where('schedule', '!=', SchoolAttendanceSmsSchedule::SCHEDULE_NONE)
            ->where('send_method', SchoolAttendanceSmsSchedule::METHOD_AUTO)
            ->whereNotNull('send_time')
            ->get();

        if ($configs->isEmpty()) {
            $this->info('No auto attendance SMS schedules configured.');

            return self::SUCCESS;
        }

        $processed = 0;

        foreach ($configs as $config) {
            if (!$attendanceSmsService->shouldRunAutoNow($config, $now)) {
                continue;
            }

            $this->line("Running auto SMS for school #{$config->schoolID} ({$config->schedule})...");

            try {
                $stats = $attendanceSmsService->runAutoBatchForSchool($config, $now);
                $processed++;
                $this->info("  Sent: {$stats['sent']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}");
                Log::info('Auto attendance SMS batch completed', [
                    'schoolID' => $config->schoolID,
                    'schedule' => $config->schedule,
                    'stats' => $stats,
                ]);
            } catch (\Throwable $e) {
                Log::error('Auto attendance SMS batch failed', [
                    'schoolID' => $config->schoolID,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Failed for school #{$config->schoolID}: " . $e->getMessage());
            }
        }

        if ($processed === 0) {
            $this->info('No schools due for auto send at this time.');
        }

        return self::SUCCESS;
    }
}
