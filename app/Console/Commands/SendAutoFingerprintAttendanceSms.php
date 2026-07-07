<?php

namespace App\Console\Commands;

use App\Models\SchoolFingerprintAttendanceSmsSchedule;
use App\Services\StudentFingerprintAttendanceSmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAutoFingerprintAttendanceSms extends Command
{
    protected $signature = 'attendance:send-auto-fingerprint-sms';

    protected $description = 'Send fingerprint attendance SMS automatically based on schedule settings';

    public function handle(StudentFingerprintAttendanceSmsService $smsService): int
    {
        $now = Carbon::now();
        $this->info('Checking auto fingerprint attendance SMS at '.$now->format('Y-m-d H:i'));

        $configs = SchoolFingerprintAttendanceSmsSchedule::query()
            ->where('schedule', '!=', SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE)
            ->where('send_method', SchoolFingerprintAttendanceSmsSchedule::METHOD_AUTO)
            ->whereNotNull('send_time')
            ->get();

        if ($configs->isEmpty()) {
            $this->info('No auto fingerprint SMS schedules configured.');

            return self::SUCCESS;
        }

        foreach ($configs as $config) {
            if (! $smsService->shouldRunAutoNow($config, $now)) {
                continue;
            }

            $this->line("Running fingerprint SMS for school #{$config->schoolID}...");

            try {
                $stats = $smsService->runAutoBatchForSchool($config, $now);
                $this->info("  Sent: {$stats['sent']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}");
                Log::info('Auto fingerprint attendance SMS completed', [
                    'schoolID' => $config->schoolID,
                    'stats' => $stats,
                ]);
            } catch (\Throwable $e) {
                Log::error('Auto fingerprint SMS failed', [
                    'schoolID' => $config->schoolID,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Failed: ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
