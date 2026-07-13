<?php

namespace App\Console\Commands;

use App\Models\StudentEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillEnrollmentIdsOnModules extends Command
{
    protected $signature = 'students:backfill-enrollment-fks
                            {--school= : Limit to one schoolID}
                            {--dry-run : Preview only}';

    protected $description = 'Backfill enrollmentID on attendance, payments, library, results, black book, store, permissions tables';

    private array $targets = [
        // table => student column
        'attendances' => 'studentID',
        'student_session_attendance' => 'studentID',
        'student_fingerprint_attendance' => 'studentID',
        'payments' => 'studentID',
        'student_fee_payments' => 'studentID',
        'book_borrows' => 'studentID',
        'results' => 'studentID',
        'black_book_reports' => 'studentID',
        'student_permissions' => 'student_id',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $schoolFilter = $this->option('school') ? (int) $this->option('school') : null;

        $enrollmentQuery = StudentEnrollment::query()
            ->where('status', 'Active')
            ->orderByDesc('enrollmentID');

        if ($schoolFilter) {
            $enrollmentQuery->where('schoolID', $schoolFilter);
        }

        $map = $enrollmentQuery->get(['studentID', 'enrollmentID', 'schoolID'])
            ->unique('studentID')
            ->keyBy('studentID');

        $this->info('Active enrollments loaded: ' . $map->count());

        foreach ($this->targets as $table => $studentCol) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'enrollmentID')) {
                $this->warn("Skip {$table}: missing table/column");
                continue;
            }

            $updated = 0;
            $skipped = 0;

            $query = DB::table($table)->whereNull('enrollmentID')->whereNotNull($studentCol);
            if ($schoolFilter && Schema::hasColumn($table, 'schoolID')) {
                $query->where('schoolID', $schoolFilter);
            }

            $query->orderBy($this->guessPrimary($table))->chunkById(500, function ($rows) use (
                $table,
                $studentCol,
                $map,
                $dryRun,
                &$updated,
                &$skipped
            ) {
                foreach ($rows as $row) {
                    $studentID = $row->{$studentCol} ?? null;
                    if (!$studentID || !isset($map[$studentID])) {
                        $skipped++;
                        continue;
                    }

                    $enrollmentID = $map[$studentID]->enrollmentID;
                    $pk = $this->guessPrimary($table);
                    $id = $row->{$pk} ?? null;
                    if (!$id) {
                        $skipped++;
                        continue;
                    }

                    if (!$dryRun) {
                        DB::table($table)->where($pk, $id)->update(['enrollmentID' => $enrollmentID]);
                    }
                    $updated++;
                }
            }, $this->guessPrimary($table));

            $this->line(($dryRun ? '[dry-run] ' : '') . "{$table}: updated={$updated}, skipped={$skipped}");
        }

        // store_requisitions: recipient_type=student, recipient_id=studentID
        if (Schema::hasTable('store_requisitions') && Schema::hasColumn('store_requisitions', 'enrollmentID')) {
            $updated = 0;
            $skipped = 0;
            $q = DB::table('store_requisitions')
                ->where('recipient_type', 'student')
                ->whereNull('enrollmentID')
                ->whereNotNull('recipient_id');

            if ($schoolFilter && Schema::hasColumn('store_requisitions', 'schoolID')) {
                $q->where('schoolID', $schoolFilter);
            }

            $q->orderBy('requisitionID')->chunkById(500, function ($rows) use ($map, $dryRun, &$updated, &$skipped) {
                foreach ($rows as $row) {
                    $studentID = $row->recipient_id;
                    if (!$studentID || !isset($map[$studentID])) {
                        $skipped++;
                        continue;
                    }
                    if (!$dryRun) {
                        DB::table('store_requisitions')
                            ->where('requisitionID', $row->requisitionID)
                            ->update(['enrollmentID' => $map[$studentID]->enrollmentID]);
                    }
                    $updated++;
                }
            }, 'requisitionID');

            $this->line(($dryRun ? '[dry-run] ' : '') . "store_requisitions: updated={$updated}, skipped={$skipped}");
        }

        if ($dryRun) {
            $this->comment('Dry-run only. Re-run without --dry-run to write.');
        }

        return self::SUCCESS;
    }

    private function guessPrimary(string $table): string
    {
        return match ($table) {
            'attendances' => 'attendanceID',
            'student_session_attendance' => 'session_attendanceID',
            'student_fingerprint_attendance' => 'id',
            'payments' => 'paymentID',
            'student_fee_payments' => 'payment_detail_id',
            'book_borrows' => 'borrowID',
            'results' => 'resultID',
            'black_book_reports' => 'reportID',
            'student_permissions' => 'id',
            default => 'id',
        };
    }
}
