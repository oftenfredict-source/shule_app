<?php

namespace App\Services;

use App\Models\Student;

/**
 * Shared NECTA secondary grading: O-Level + A-Level Form 5/6, duo collapse.
 * Used by ManageResults and Parent portal so logic stays in one place.
 */
class SecondaryResultGradingService
{
    public function isALevelClassName(?string $className): bool
    {
        return app(SelectedAcademicYearService::class)->isALevelClassName(
            $className,
        );
    }

    /**
     * O-Level: A 75-100 (1) … F (5)
     * A-Level Form 5/6: A 80-100 (1) … S 35-39 (6), F (7)
     */
    public function getSecondaryHardcodedGrade(
        $marks,
        ?string $className = null,
    ): array {
        if ($marks === null || $marks === "") {
            return ["grade" => null, "points" => null, "remark" => null];
        }

        $marksNum = round((float) $marks, 2);

        if ($this->isALevelClassName($className)) {
            if ($marksNum >= 80) {
                return ["grade" => "A", "points" => 1, "remark" => "Excellent"];
            }
            if ($marksNum >= 70) {
                return ["grade" => "B", "points" => 2, "remark" => "Very Good"];
            }
            if ($marksNum >= 60) {
                return ["grade" => "C", "points" => 3, "remark" => "Good"];
            }
            if ($marksNum >= 50) {
                return ["grade" => "D", "points" => 4, "remark" => "Average"];
            }
            if ($marksNum >= 40) {
                return ["grade" => "E", "points" => 5, "remark" => "Satisfactory"];
            }
            if ($marksNum >= 35) {
                return ["grade" => "S", "points" => 6, "remark" => "Subsidiary"];
            }

            return ["grade" => "F", "points" => 7, "remark" => "Fail"];
        }

        if ($marksNum >= 75) {
            return ["grade" => "A", "points" => 1, "remark" => "Excellent"];
        }
        if ($marksNum >= 65) {
            return ["grade" => "B", "points" => 2, "remark" => "Very Good"];
        }
        if ($marksNum >= 45) {
            return ["grade" => "C", "points" => 3, "remark" => "Good"];
        }
        if ($marksNum >= 30) {
            return ["grade" => "D", "points" => 4, "remark" => "Pass"];
        }

        return ["grade" => "F", "points" => 5, "remark" => "Fail"];
    }

    /**
     * @return list<array{subjectID:int,subject_name:string,class_subjectID:mixed,marks:float|null,is_duo:bool,papers:list}>
     */
    public function collapseResultsToSubjectMarks($results): array
    {
        $groups = [];

        foreach ($results as $result) {
            $cs = $result->classSubject ?? null;
            $subject = $cs->subject ?? null;
            if (!$subject) {
                continue;
            }

            $sid = (int) $subject->subjectID;
            if (!isset($groups[$sid])) {
                if (
                    ($subject->is_duo ?? false) &&
                    !$subject->relationLoaded("components")
                ) {
                    $subject->load("components");
                }
                $groups[$sid] = [
                    "subject" => $subject,
                    "class_subjectID" => $result->class_subjectID,
                    "rows" => collect(),
                ];
            }
            $groups[$sid]["rows"]->push($result);
        }

        $out = [];
        foreach ($groups as $sid => $group) {
            $subject = $group["subject"];
            $components = $subject->components ?? collect();
            $isDuo =
                (bool) ($subject->is_duo ?? false) && $components->count() > 0;

            if ($isDuo) {
                $raw = 0.0;
                $max = 0;
                $any = false;
                $papers = [];
                foreach ($components as $comp) {
                    $compMax = max(1, (int) $comp->max_marks);
                    $max += $compMax;
                    $row = $group["rows"]->first(function ($r) use ($comp) {
                        return (int) ($r->componentID ?? 0) ===
                            (int) $comp->componentID &&
                            $r->marks !== null &&
                            $r->marks !== "";
                    });
                    $paperMarks = $row ? (float) $row->marks : null;
                    if ($row) {
                        $raw += $paperMarks;
                        $any = true;
                    }
                    $papers[] = [
                        "componentID" => (int) $comp->componentID,
                        "component_name" => $comp->component_name ?? "Paper",
                        "marks" => $paperMarks,
                        "max_marks" => $compMax,
                    ];
                }
                $marks =
                    $any && $max > 0
                        ? round(($raw / $max) * 100, 2)
                        : null;
            } else {
                $papers = [];
                $marked = $group["rows"]->filter(
                    fn($r) => $r->marks !== null && $r->marks !== "",
                );
                if ($marked->count() > 1) {
                    $marks = round((float) $marked->avg("marks"), 2);
                } elseif ($marked->count() === 1) {
                    $marks = (float) $marked->first()->marks;
                } else {
                    $marks = null;
                }
            }

            $out[] = [
                "subjectID" => $sid,
                "subject_name" => $subject->subject_name ?? "N/A",
                "class_subjectID" => $group["class_subjectID"],
                "marks" => $marks,
                "is_duo" => $isDuo,
                "papers" => $papers,
            ];
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    public function getCombinationSubjectIdsForStudent($student): array
    {
        if (!$student) {
            return [];
        }

        $subclass = $student->subclass ?? ($student->oldSubclass ?? null);
        if (!$subclass) {
            return [];
        }

        if (!$subclass->relationLoaded("combie")) {
            $subclass->load("combie.subjects");
        } elseif (
            $subclass->combie &&
            !$subclass->combie->relationLoaded("subjects")
        ) {
            $subclass->combie->load("subjects");
        }

        $combie = $subclass->combie ?? null;
        if (!$combie || !$combie->subjects) {
            return [];
        }

        return $combie->subjects
            ->pluck("subjectID")
            ->map(fn($id) => (int) $id)
            ->filter()
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * @param  list<array{points: int|float, marks: float, grade?: string|null, subjectID?: int|null}>  $subjectPoints
     * @return array{total_points: int, grades: list<string>, counted_subjects: int}
     */
    public function resolveSecondaryBestSubjectsForDivision(
        array $subjectPoints,
        string $className,
        string $schoolType,
        ?array $combinationSubjectIds = null,
    ): array {
        $empty = [
            "total_points" => 0,
            "grades" => [],
            "counted_subjects" => 0,
        ];

        if (
            empty($subjectPoints) ||
            strtolower(trim($schoolType)) !== "secondary"
        ) {
            return $empty;
        }

        $classNameLower = strtolower(
            preg_replace("/[\s\-]+/", "_", $className),
        );

        if (
            in_array($classNameLower, [
                "form_one",
                "form_two",
                "form_three",
                "form_four",
                "form_1",
                "form_2",
                "form_3",
                "form_4",
            ], true) ||
            preg_match("/form\s*(one|two|three|four|1|2|3|4)/i", $className)
        ) {
            usort($subjectPoints, function ($a, $b) {
                if ($a["points"] != $b["points"]) {
                    return $a["points"] <=> $b["points"];
                }

                return $b["marks"] <=> $a["marks"];
            });

            $bestSubjects = array_slice(
                $subjectPoints,
                0,
                min(7, count($subjectPoints)),
            );
            $totalPoints = 0;
            foreach ($bestSubjects as $subject) {
                $totalPoints += (int) $subject["points"];
            }
            if (count($bestSubjects) < 7) {
                $totalPoints += (7 - count($bestSubjects)) * 5;
            }

            $grades = array_values(
                array_filter(
                    array_map(
                        fn($s) => strtoupper(
                            trim((string) ($s["grade"] ?? "")),
                        ),
                        $bestSubjects,
                    ),
                    fn($g) => $g !== "",
                ),
            );

            return [
                "total_points" => $totalPoints,
                "grades" => $grades,
                "counted_subjects" => count($bestSubjects),
            ];
        }

        if ($this->isALevelClassName($className)) {
            $combinationSubjectIds = array_values(
                array_filter(array_map("intval", $combinationSubjectIds ?? [])),
            );

            if (!empty($combinationSubjectIds)) {
                $bySubjectId = [];
                foreach ($subjectPoints as $entry) {
                    $sid = (int) ($entry["subjectID"] ?? 0);
                    if ($sid > 0) {
                        $bySubjectId[$sid] = $entry;
                    }
                }

                $totalPoints = 0;
                $grades = [];
                $counted = 0;
                foreach ($combinationSubjectIds as $sid) {
                    if (
                        isset($bySubjectId[$sid]) &&
                        isset($bySubjectId[$sid]["points"]) &&
                        $bySubjectId[$sid]["points"] !== null
                    ) {
                        $totalPoints += (int) $bySubjectId[$sid]["points"];
                        $g = strtoupper(
                            trim((string) ($bySubjectId[$sid]["grade"] ?? "")),
                        );
                        if ($g !== "") {
                            $grades[] = $g;
                        }
                        $counted++;
                    } else {
                        $totalPoints += 7;
                        $grades[] = "F";
                    }
                }

                return [
                    "total_points" => $totalPoints,
                    "grades" => $grades,
                    "counted_subjects" => $counted,
                ];
            }

            usort($subjectPoints, function ($a, $b) {
                if ($a["points"] != $b["points"]) {
                    return $a["points"] <=> $b["points"];
                }

                return $b["marks"] <=> $a["marks"];
            });

            $bestSubjects = array_slice(
                $subjectPoints,
                0,
                min(3, count($subjectPoints)),
            );
            $totalPoints = 0;
            foreach ($bestSubjects as $subject) {
                $totalPoints += (int) $subject["points"];
            }
            if (count($bestSubjects) < 3) {
                $totalPoints += (3 - count($bestSubjects)) * 7;
            }

            $grades = array_values(
                array_filter(
                    array_map(
                        fn($s) => strtoupper(
                            trim((string) ($s["grade"] ?? "")),
                        ),
                        $bestSubjects,
                    ),
                    fn($g) => $g !== "",
                ),
            );

            return [
                "total_points" => $totalPoints,
                "grades" => $grades,
                "counted_subjects" => count($bestSubjects),
            ];
        }

        $totalPoints = 0;
        $grades = [];
        foreach ($subjectPoints as $subject) {
            $totalPoints += (int) $subject["points"];
            $g = strtoupper(trim((string) ($subject["grade"] ?? "")));
            if ($g !== "") {
                $grades[] = $g;
            }
        }

        return [
            "total_points" => $totalPoints,
            "grades" => $grades,
            "counted_subjects" => count($subjectPoints),
        ];
    }

    public function calculateDivisionFromPoints(
        int $totalPoints,
        string $schoolType,
        ?string $className = null,
    ): array {
        if (strtolower(trim($schoolType)) !== "secondary") {
            return ["grade" => null, "division" => null];
        }

        if ($this->isALevelClassName($className)) {
            // NECTA ACSEE: I 3-9, II 10-12, III 13-17, IV 18-19, 0 from 20
            if ($totalPoints >= 3 && $totalPoints <= 9) {
                return ["grade" => null, "division" => "I." . $totalPoints];
            }
            if ($totalPoints >= 10 && $totalPoints <= 12) {
                return ["grade" => null, "division" => "II." . $totalPoints];
            }
            if ($totalPoints >= 13 && $totalPoints <= 17) {
                return ["grade" => null, "division" => "III." . $totalPoints];
            }
            if ($totalPoints >= 18 && $totalPoints <= 19) {
                return ["grade" => null, "division" => "IV." . $totalPoints];
            }

            return ["grade" => null, "division" => "0." . $totalPoints];
        }

        // O-Level
        if ($totalPoints >= 7 && $totalPoints <= 17) {
            return ["grade" => null, "division" => "I." . $totalPoints];
        }
        if ($totalPoints >= 18 && $totalPoints <= 21) {
            return ["grade" => null, "division" => "II." . $totalPoints];
        }
        if ($totalPoints >= 22 && $totalPoints <= 25) {
            return ["grade" => null, "division" => "III." . $totalPoints];
        }
        if ($totalPoints >= 26 && $totalPoints <= 33) {
            return ["grade" => null, "division" => "IV." . $totalPoints];
        }

        return ["grade" => null, "division" => "0." . $totalPoints];
    }

    public function subjectRemark(?string $grade, ?string $className = null): string
    {
        $g = strtoupper(trim((string) $grade));
        if ($g === "" || $g === "INCOMPLETE" || $g === "N/A") {
            return "Incomplete";
        }

        if ($this->isALevelClassName($className)) {
            return match ($g) {
                "A" => "Excellent",
                "B" => "Very Good",
                "C" => "Good",
                "D" => "Average",
                "E" => "Satisfactory",
                "S" => "Subsidiary",
                "F" => "Fail",
                default => "Fail",
            };
        }

        return match ($g) {
            "A" => "Excellent",
            "B" => "Very Good",
            "C" => "Good",
            "D" => "Pass",
            "F" => "Fail",
            default => "Fail",
        };
    }
}
