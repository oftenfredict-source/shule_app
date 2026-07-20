<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\OnlineAdmissionFormField;
use App\Models\OnlineAdmissionFormSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnlineAdmissionFormService
{
    public const FIELD_TYPES = [
        'text',
        'textarea',
        'select',
        'radio',
        'checkbox',
        'file',
        'phone',
        'gender',
    ];

    public const DEFAULT_SECTIONS = [
        [
            'section_key' => 'student_details',
            'title' => 'Student Details',
            'description' => 'Student personal information required for admission.',
            'sort_order' => 10,
        ],
        [
            'section_key' => 'parent_details',
            'title' => 'Parent / Guardian Details',
            'description' => 'Parent or guardian contact and personal details.',
            'sort_order' => 20,
        ],
    ];

    /** @var array<string, string> field_key => section_key */
    public const SYSTEM_FIELD_SECTION_MAP = [
        'first_name' => 'student_details',
        'middle_name' => 'student_details',
        'last_name' => 'student_details',
        'gender' => 'student_details',
        'student_photo' => 'student_details',
        'parent_phone' => 'parent_details',
        'parent_first_name' => 'parent_details',
        'parent_middle_name' => 'parent_details',
        'parent_last_name' => 'parent_details',
        'parent_gender' => 'parent_details',
        'parent_photo' => 'parent_details',
    ];

    public const SYSTEM_FIELDS = [
        ['field_key' => 'first_name', 'label' => 'First Name', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 20],
        ['field_key' => 'middle_name', 'label' => 'Middle Name', 'field_type' => 'text', 'is_required' => false, 'sort_order' => 30],
        ['field_key' => 'last_name', 'label' => 'Last Name', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 40],
        ['field_key' => 'gender', 'label' => 'Gender', 'field_type' => 'gender', 'is_required' => true, 'sort_order' => 50],
        ['field_key' => 'student_photo', 'label' => 'Student Photo', 'field_type' => 'file', 'is_required' => false, 'sort_order' => 60],
        ['field_key' => 'parent_phone', 'label' => 'Parent Phone', 'field_type' => 'phone', 'is_required' => true, 'sort_order' => 110],
        ['field_key' => 'parent_first_name', 'label' => 'Parent First Name', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 120],
        ['field_key' => 'parent_middle_name', 'label' => 'Parent Middle Name', 'field_type' => 'text', 'is_required' => false, 'sort_order' => 130],
        ['field_key' => 'parent_last_name', 'label' => 'Parent Last Name', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 140],
        ['field_key' => 'parent_gender', 'label' => 'Parent Gender', 'field_type' => 'gender', 'is_required' => true, 'sort_order' => 150],
        ['field_key' => 'parent_photo', 'label' => 'Parent Photo', 'field_type' => 'file', 'is_required' => false, 'sort_order' => 160],
    ];

    /**
     * Seed the shared default form onto every active main class for a school.
     */
    public function ensureDefaultsForSchool(int $schoolID): void
    {
        $classIds = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('classID');

        foreach ($classIds as $classID) {
            $this->ensureDefaults($schoolID, (int) $classID);
        }
    }

    public function ensureDefaults(int $schoolID, int $classID): void
    {
        $sectionsByKey = $this->ensureDefaultSections($schoolID, $classID);

        foreach (self::SYSTEM_FIELDS as $field) {
            $sectionKey = self::SYSTEM_FIELD_SECTION_MAP[$field['field_key']] ?? 'student_details';
            $sectionId = $sectionsByKey[$sectionKey]->id ?? null;

            $row = OnlineAdmissionFormField::firstOrCreate(
                [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'field_key' => $field['field_key'],
                ],
                array_merge($field, [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'section_id' => $sectionId,
                    'is_system' => true,
                    'is_active' => true,
                    'options' => $field['options'] ?? null,
                ])
            );

            if ($sectionId && (int) $row->section_id !== (int) $sectionId) {
                $row->section_id = $sectionId;
                $row->save();
            }
        }

        OnlineAdmissionFormField::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('field_type', 'section')
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * @return array<string, OnlineAdmissionFormSection>
     */
    public function ensureDefaultSections(int $schoolID, int $classID): array
    {
        $map = [];
        foreach (self::DEFAULT_SECTIONS as $section) {
            $row = OnlineAdmissionFormSection::firstOrCreate(
                [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'section_key' => $section['section_key'],
                ],
                array_merge($section, [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'is_system' => true,
                    'is_active' => true,
                ])
            );
            $map[$section['section_key']] = $row;
        }

        return $map;
    }

    public function allSections(int $schoolID, int $classID)
    {
        $this->ensureDefaultSections($schoolID, $classID);

        return OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function activeSections(int $schoolID, int $classID)
    {
        $this->ensureDefaults($schoolID, $classID);

        return OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Active sections with their active question fields (for apply wizard).
     */
    public function activeSectionsWithFields(int $schoolID, int $classID)
    {
        $this->ensureDefaults($schoolID, $classID);

        return OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['fields' => function ($q) use ($schoolID, $classID) {
                $q->where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('is_active', true)
                    ->where('field_type', '!=', 'section')
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->get()
            ->filter(fn ($s) => $s->fields->isNotEmpty())
            ->values();
    }

    public function createSection(int $schoolID, int $classID, array $data): OnlineAdmissionFormSection
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw ValidationException::withMessages(['title' => 'Section title is required.']);
        }

        $key = trim((string) ($data['section_key'] ?? ''));
        if ($key === '') {
            $key = Str::slug($title, '_');
        }
        $key = Str::limit(preg_replace('/[^a-z0-9_]/', '_', Str::lower($key)) ?: 'section', 80, '');

        $base = $key;
        $i = 1;
        while (
            OnlineAdmissionFormSection::where('schoolID', $schoolID)
                ->where('classID', $classID)
                ->where('section_key', $key)
                ->exists()
        ) {
            $key = Str::limit($base . '_' . $i++, 80, '');
        }

        $maxOrder = (int) OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->max('sort_order');

        return OnlineAdmissionFormSection::create([
            'schoolID' => $schoolID,
            'classID' => $classID,
            'section_key' => $key,
            'title' => $title,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? ($maxOrder + 10)),
            'is_system' => false,
            'is_active' => array_key_exists('is_active', $data)
                ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ]);
    }

    public function updateSection(OnlineAdmissionFormSection $section, array $data): OnlineAdmissionFormSection
    {
        if (isset($data['title'])) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                throw ValidationException::withMessages(['title' => 'Section title is required.']);
            }
            $section->title = $title;
        }
        if (array_key_exists('description', $data)) {
            $section->description = $data['description'];
        }
        if (array_key_exists('is_active', $data)) {
            $section->is_active = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['sort_order'])) {
            $section->sort_order = (int) $data['sort_order'];
        }
        $section->save();

        return $section;
    }

    public function deleteSection(OnlineAdmissionFormSection $section): void
    {
        if ($section->is_system) {
            throw ValidationException::withMessages([
                'section' => 'Default sections (Student Details / Parent Details) cannot be deleted.',
            ]);
        }

        $fallback = OnlineAdmissionFormSection::where('schoolID', $section->schoolID)
            ->where('classID', $section->classID)
            ->where('section_key', 'student_details')
            ->first();

        OnlineAdmissionFormField::where('section_id', $section->id)
            ->where('schoolID', $section->schoolID)
            ->where('classID', $section->classID)
            ->update(['section_id' => $fallback?->id]);

        $section->delete();
    }

    public function activeFields(int $schoolID, int $classID)
    {
        $this->ensureDefaults($schoolID, $classID);

        return OnlineAdmissionFormField::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('is_active', true)
            ->where('field_type', '!=', 'section')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function allFields(int $schoolID, int $classID)
    {
        $this->ensureDefaults($schoolID, $classID);

        return OnlineAdmissionFormField::with('section')
            ->where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('field_type', '!=', 'section')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Sync the full form builder payload for one main class.
     *
     * @param  array<int, array<string, mixed>>  $fieldsPayload
     * @return \Illuminate\Support\Collection<int, OnlineAdmissionFormField>
     */
    public function syncFields(int $schoolID, int $classID, array $fieldsPayload)
    {
        $this->ensureDefaults($schoolID, $classID);
        $validSectionIds = OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $defaultSectionId = OnlineAdmissionFormSection::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('section_key', 'student_details')
            ->value('id');

        return DB::transaction(function () use ($schoolID, $classID, $fieldsPayload, $validSectionIds, $defaultSectionId) {
            $keptIds = [];
            $usedKeys = [];

            foreach (array_values($fieldsPayload) as $index => $row) {
                $existing = null;
                if (!empty($row['id'])) {
                    $existing = OnlineAdmissionFormField::where('schoolID', $schoolID)
                        ->where('classID', $classID)
                        ->where('id', (int) $row['id'])
                        ->first();
                }

                $isSystem = $existing && $existing->is_system;
                $type = $isSystem
                    ? (string) $existing->field_type
                    : (string) ($row['field_type'] ?? $row['type'] ?? 'text');

                if ($type === 'section') {
                    continue;
                }

                if (!in_array($type, self::FIELD_TYPES, true)) {
                    throw ValidationException::withMessages([
                        'fields' => "Invalid field type: {$type}",
                    ]);
                }

                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    throw ValidationException::withMessages([
                        'fields' => 'Each question needs a label.',
                    ]);
                }

                if ($isSystem) {
                    $key = (string) $existing->field_key;
                } else {
                    $key = trim((string) ($row['field_key'] ?? ''));
                    if ($key === '') {
                        $key = 'custom_' . Str::slug($label, '_');
                    }
                    $key = Str::limit(
                        preg_replace('/[^a-z0-9_]/', '_', Str::lower($key)) ?: ('field_' . ($index + 1)),
                        80,
                        ''
                    );
                    if (str_starts_with($key, 'custom_') === false && !$existing) {
                        $key = 'custom_' . ltrim($key, '_');
                    }
                }

                if (!$isSystem) {
                    $baseKey = $key;
                    $suffix = 1;
                    while (
                        in_array($key, $usedKeys, true)
                        || OnlineAdmissionFormField::where('schoolID', $schoolID)
                            ->where('classID', $classID)
                            ->where('field_key', $key)
                            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                            ->exists()
                    ) {
                        $key = Str::limit($baseKey . '_' . $suffix++, 80, '');
                    }
                }
                $usedKeys[] = $key;

                $options = $row['options'] ?? null;
                if (is_string($options)) {
                    $options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $options) ?: [])));
                }
                if (!is_array($options)) {
                    $options = null;
                }
                if (in_array($type, ['select', 'radio'], true) && empty($options)) {
                    throw ValidationException::withMessages([
                        'fields' => "Choice field \"{$label}\" needs at least one option.",
                    ]);
                }

                $sectionId = isset($row['section_id']) && $row['section_id'] !== '' && $row['section_id'] !== null
                    ? (int) $row['section_id']
                    : null;
                if ($sectionId && !in_array($sectionId, $validSectionIds, true)) {
                    $sectionId = null;
                }
                if (!$sectionId) {
                    if ($isSystem && isset(self::SYSTEM_FIELD_SECTION_MAP[$key])) {
                        $mapKey = self::SYSTEM_FIELD_SECTION_MAP[$key];
                        $sectionId = OnlineAdmissionFormSection::where('schoolID', $schoolID)
                            ->where('classID', $classID)
                            ->where('section_key', $mapKey)
                            ->value('id');
                    } else {
                        $sectionId = $defaultSectionId;
                    }
                }

                $payload = [
                    'label' => $label,
                    'section_id' => $sectionId,
                    'is_required' => filter_var($row['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'options' => in_array($type, ['select', 'radio'], true) ? array_values($options) : null,
                    'sort_order' => (int) ($row['sort_order'] ?? (($index + 1) * 10)),
                    'is_active' => array_key_exists('is_active', $row)
                        ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN)
                        : true,
                ];

                if ($existing) {
                    if (!$isSystem) {
                        $payload['field_key'] = $key;
                        $payload['field_type'] = $type;
                    }
                    $existing->update($payload);
                    $keptIds[] = $existing->id;
                    continue;
                }

                $created = OnlineAdmissionFormField::create(array_merge($payload, [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'field_key' => $key,
                    'field_type' => $type,
                    'is_system' => false,
                ]));
                $keptIds[] = $created->id;
            }

            OnlineAdmissionFormField::where('schoolID', $schoolID)
                ->where('classID', $classID)
                ->where('is_system', false)
                ->where('field_type', '!=', 'section')
                ->whereNotIn('id', $keptIds)
                ->delete();

            $this->ensureDefaults($schoolID, $classID);

            return $this->allFields($schoolID, $classID);
        });
    }
}
