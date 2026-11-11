<?php

namespace App\Console\Commands;

use App\Http\Controllers\SMS_InformationController;
use App\Models\ScheduledSms;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledSms extends Command
{
    protected $signature = 'sms:send-scheduled';

    protected $description = 'Send due scheduled SMS notifications';

    public function handle(): int
    {
        $due = ScheduledSms::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();

        if ($due->isEmpty()) {
            return self::SUCCESS;
        }

        /** @var SMS_InformationController $controller */
        $controller = app(SMS_InformationController::class);

        foreach ($due as $job) {
            $job->update(['status' => 'processing']);

            try {
                $summary = $controller->dispatchScheduledSms($job);
                $job->update([
                    'status' => ($summary['success'] ?? false) ? 'sent' : 'failed',
                    'result_summary' => json_encode($summary),
                ]);
            } catch (\Throwable $e) {
                Log::error('Scheduled SMS failed: ' . $e->getMessage(), [
                    'scheduled_sms_id' => $job->id,
                ]);
                $job->update([
                    'status' => 'failed',
                    'result_summary' => $e->getMessage(),
                ]);
            }
        }

        $this->info('Processed ' . $due->count() . ' scheduled SMS job(s).');

        return self::SUCCESS;
    }
}
