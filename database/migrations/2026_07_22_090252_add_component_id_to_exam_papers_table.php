<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exam_papers')) {
            return;
        }

        if (!Schema::hasTable('school_subject_components')) {
            throw new RuntimeException(
                'school_subject_components table is missing; run duo-subjects migration first.',
            );
        }

        // Ensure parent PK is InnoDB + indexed (required for FK)
        try {
            DB::statement(
                'ALTER TABLE `school_subject_components` ENGINE=InnoDB',
            );
        } catch (\Throwable $e) {
            // ignore if no privilege / already InnoDB
        }

        // Add column if missing (safe if a previous attempt added it before FK failed)
        if (!Schema::hasColumn('exam_papers', 'componentID')) {
            Schema::table('exam_papers', function (Blueprint $table) {
                if (Schema::hasColumn('exam_papers', 'class_subjectID')) {
                    $table
                        ->unsignedBigInteger('componentID')
                        ->nullable()
                        ->after('class_subjectID');
                } else {
                    $table->unsignedBigInteger('componentID')->nullable();
                }
            });
        }

        // Match exact parent column type (avoids errno 150 type mismatch)
        $parentType = $this->columnType(
            'school_subject_components',
            'componentID',
        );
        if ($parentType) {
            DB::statement(
                "ALTER TABLE `exam_papers` MODIFY `componentID` {$parentType} NULL",
            );
        } else {
            DB::statement(
                'ALTER TABLE `exam_papers` MODIFY `componentID` BIGINT UNSIGNED NULL',
            );
        }

        if (!$this->foreignKeyExists('exam_papers', 'exam_papers_component_fk')) {
            try {
                Schema::table('exam_papers', function (Blueprint $table) {
                    $table
                        ->foreign('componentID', 'exam_papers_component_fk')
                        ->references('componentID')
                        ->on('school_subject_components')
                        ->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // Last resort: keep the column without FK so migrate can finish.
                // App still works; FK can be added manually later if needed.
                if (!$this->indexExists('exam_papers', 'exam_papers_component_id_idx')) {
                    Schema::table('exam_papers', function (Blueprint $table) {
                        $table->index('componentID', 'exam_papers_component_id_idx');
                    });
                }
            }
        }

        if (!$this->indexExists('exam_papers', 'exam_papers_exam_cs_comp_idx')) {
            Schema::table('exam_papers', function (Blueprint $table) {
                $table->index(
                    ['examID', 'class_subjectID', 'componentID'],
                    'exam_papers_exam_cs_comp_idx',
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_papers')) {
            return;
        }

        Schema::table('exam_papers', function (Blueprint $table) {
            if ($this->foreignKeyExists('exam_papers', 'exam_papers_component_fk')) {
                $table->dropForeign('exam_papers_component_fk');
            }
            if ($this->indexExists('exam_papers', 'exam_papers_exam_cs_comp_idx')) {
                $table->dropIndex('exam_papers_exam_cs_comp_idx');
            }
            if ($this->indexExists('exam_papers', 'exam_papers_component_id_idx')) {
                $table->dropIndex('exam_papers_component_id_idx');
            }
            if (Schema::hasColumn('exam_papers', 'componentID')) {
                $table->dropColumn('componentID');
            }
        });
    }

    private function columnType(string $table, string $column): ?string
    {
        $rows = DB::select(
            'SELECT COLUMN_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [$table, $column],
        );

        return !empty($rows) ? (string) $rows[0]->COLUMN_TYPE : null;
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?
               AND CONSTRAINT_NAME = ?
             LIMIT 1',
            [$table, 'FOREIGN KEY', $constraintName],
        );

        return !empty($rows);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select(
            'SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?
             LIMIT 1',
            [$table, $indexName],
        );

        return !empty($rows);
    }
};
