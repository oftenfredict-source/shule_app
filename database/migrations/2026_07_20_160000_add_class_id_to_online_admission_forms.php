<?php

use App\Models\ClassModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('online_admission_form_sections', 'classID')) {
            Schema::table('online_admission_form_sections', function (Blueprint $table) {
                $table->unsignedBigInteger('classID')->nullable()->after('schoolID');
                $table->index(['schoolID', 'classID'], 'oa_form_sections_school_class');
            });
        }

        if (!Schema::hasColumn('online_admission_form_fields', 'classID')) {
            Schema::table('online_admission_form_fields', function (Blueprint $table) {
                $table->unsignedBigInteger('classID')->nullable()->after('schoolID');
                $table->index(['schoolID', 'classID'], 'oa_form_fields_school_class');
            });
        }

        // Drop school-only uniques before cloning per class.
        $this->dropUniqueIfExists('online_admission_form_sections', 'oa_form_sections_school_key_unique');
        $this->dropUniqueIfExists('online_admission_form_fields', 'online_admission_form_fields_schoolid_field_key_unique');

        $this->backfillClassScopedForms();

        if (!$this->uniqueExists('online_admission_form_sections', 'oa_form_sections_school_class_key_unique')) {
            Schema::table('online_admission_form_sections', function (Blueprint $table) {
                $table->unique(['schoolID', 'classID', 'section_key'], 'oa_form_sections_school_class_key_unique');
            });
        }

        if (!$this->uniqueExists('online_admission_form_fields', 'oa_form_fields_school_class_key_unique')) {
            Schema::table('online_admission_form_fields', function (Blueprint $table) {
                $table->unique(['schoolID', 'classID', 'field_key'], 'oa_form_fields_school_class_key_unique');
            });
        }
    }

    public function down(): void
    {
        $this->dropUniqueIfExists('online_admission_form_fields', 'oa_form_fields_school_class_key_unique');
        $this->dropUniqueIfExists('online_admission_form_sections', 'oa_form_sections_school_class_key_unique');

        if (!$this->uniqueExists('online_admission_form_fields', 'online_admission_form_fields_schoolid_field_key_unique')) {
            Schema::table('online_admission_form_fields', function (Blueprint $table) {
                $table->unique(['schoolID', 'field_key'], 'online_admission_form_fields_schoolid_field_key_unique');
            });
        }

        if (!$this->uniqueExists('online_admission_form_sections', 'oa_form_sections_school_key_unique')) {
            Schema::table('online_admission_form_sections', function (Blueprint $table) {
                $table->unique(['schoolID', 'section_key'], 'oa_form_sections_school_key_unique');
            });
        }

        if (Schema::hasColumn('online_admission_form_fields', 'classID')) {
            Schema::table('online_admission_form_fields', function (Blueprint $table) {
                $table->dropIndex('oa_form_fields_school_class');
                $table->dropColumn('classID');
            });
        }

        if (Schema::hasColumn('online_admission_form_sections', 'classID')) {
            Schema::table('online_admission_form_sections', function (Blueprint $table) {
                $table->dropIndex('oa_form_sections_school_class');
                $table->dropColumn('classID');
            });
        }
    }

    private function backfillClassScopedForms(): void
    {
        $schoolIds = DB::table('online_admission_form_sections')
            ->whereNull('classID')
            ->distinct()
            ->pluck('schoolID')
            ->merge(
                DB::table('online_admission_form_fields')
                    ->whereNull('classID')
                    ->distinct()
                    ->pluck('schoolID')
            )
            ->unique()
            ->values();

        foreach ($schoolIds as $schoolID) {
            $schoolID = (int) $schoolID;
            $classIds = ClassModel::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('class_name')
                ->pluck('classID')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (empty($classIds)) {
                DB::table('online_admission_form_fields')->where('schoolID', $schoolID)->whereNull('classID')->delete();
                DB::table('online_admission_form_sections')->where('schoolID', $schoolID)->whereNull('classID')->delete();
                continue;
            }

            $sections = DB::table('online_admission_form_sections')
                ->where('schoolID', $schoolID)
                ->whereNull('classID')
                ->orderBy('id')
                ->get();

            $fields = DB::table('online_admission_form_fields')
                ->where('schoolID', $schoolID)
                ->whereNull('classID')
                ->orderBy('id')
                ->get();

            $firstClassID = $classIds[0];

            DB::table('online_admission_form_sections')
                ->where('schoolID', $schoolID)
                ->whereNull('classID')
                ->update(['classID' => $firstClassID]);

            DB::table('online_admission_form_fields')
                ->where('schoolID', $schoolID)
                ->whereNull('classID')
                ->update(['classID' => $firstClassID]);

            foreach (array_slice($classIds, 1) as $classID) {
                if (
                    DB::table('online_admission_form_sections')
                        ->where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->exists()
                ) {
                    continue;
                }

                $sectionMap = [];
                foreach ($sections as $section) {
                    $newId = DB::table('online_admission_form_sections')->insertGetId([
                        'schoolID' => $schoolID,
                        'classID' => $classID,
                        'section_key' => $section->section_key,
                        'title' => $section->title,
                        'description' => $section->description,
                        'sort_order' => $section->sort_order,
                        'is_system' => $section->is_system,
                        'is_active' => $section->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sectionMap[(int) $section->id] = $newId;
                }

                foreach ($fields as $field) {
                    $oldSectionId = $field->section_id ? (int) $field->section_id : null;
                    DB::table('online_admission_form_fields')->insert([
                        'schoolID' => $schoolID,
                        'classID' => $classID,
                        'section_id' => $oldSectionId ? ($sectionMap[$oldSectionId] ?? null) : null,
                        'field_key' => $field->field_key,
                        'label' => $field->label,
                        'field_type' => $field->field_type,
                        'options' => $field->options,
                        'is_required' => $field->is_required,
                        'is_system' => $field->is_system,
                        'is_active' => $field->is_active,
                        'sort_order' => $field->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // For schools that already have one class assigned but missing other classes,
        // clone from the first configured class template.
        $configuredSchools = DB::table('online_admission_form_sections')
            ->whereNotNull('classID')
            ->distinct()
            ->pluck('schoolID');

        foreach ($configuredSchools as $schoolID) {
            $schoolID = (int) $schoolID;
            $templateClassID = (int) DB::table('online_admission_form_sections')
                ->where('schoolID', $schoolID)
                ->whereNotNull('classID')
                ->orderBy('id')
                ->value('classID');

            if ($templateClassID <= 0) {
                continue;
            }

            $classIds = ClassModel::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('class_name')
                ->pluck('classID')
                ->map(fn ($id) => (int) $id)
                ->all();

            $sections = DB::table('online_admission_form_sections')
                ->where('schoolID', $schoolID)
                ->where('classID', $templateClassID)
                ->orderBy('id')
                ->get();

            $fields = DB::table('online_admission_form_fields')
                ->where('schoolID', $schoolID)
                ->where('classID', $templateClassID)
                ->orderBy('id')
                ->get();

            foreach ($classIds as $classID) {
                if ($classID === $templateClassID) {
                    continue;
                }
                if (
                    DB::table('online_admission_form_sections')
                        ->where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->exists()
                ) {
                    continue;
                }

                $sectionMap = [];
                foreach ($sections as $section) {
                    $newId = DB::table('online_admission_form_sections')->insertGetId([
                        'schoolID' => $schoolID,
                        'classID' => $classID,
                        'section_key' => $section->section_key,
                        'title' => $section->title,
                        'description' => $section->description,
                        'sort_order' => $section->sort_order,
                        'is_system' => $section->is_system,
                        'is_active' => $section->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sectionMap[(int) $section->id] = $newId;
                }

                foreach ($fields as $field) {
                    $oldSectionId = $field->section_id ? (int) $field->section_id : null;
                    DB::table('online_admission_form_fields')->insert([
                        'schoolID' => $schoolID,
                        'classID' => $classID,
                        'section_id' => $oldSectionId ? ($sectionMap[$oldSectionId] ?? null) : null,
                        'field_key' => $field->field_key,
                        'label' => $field->label,
                        'field_type' => $field->field_type,
                        'options' => $field->options,
                        'is_required' => $field->is_required,
                        'is_system' => $field->is_system,
                        'is_active' => $field->is_active,
                        'sort_order' => $field->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function dropUniqueIfExists(string $table, string $index): void
    {
        if ($this->uniqueExists($table, $index)) {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        }
    }

    private function uniqueExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return !empty($rows);
    }
};
