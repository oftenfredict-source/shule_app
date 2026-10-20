<?php

namespace App\Support;

use App\Models\StudentPortalQuizQuestion;
use Illuminate\Support\Collection;

class StudentPortalQuizScoring
{
    public static function roman(int $n): string
    {
        $map = [
            10 => 'x', 9 => 'ix', 5 => 'v', 4 => 'iv', 1 => 'i',
        ];
        $n = max(1, min(50, $n));
        $out = '';
        foreach ($map as $value => $numeral) {
            while ($n >= $value) {
                $out .= $numeral;
                $n -= $value;
            }
        }

        return $out;
    }

    /**
     * @param  Collection<int, StudentPortalQuizQuestion>  $questions
     * @param  array<string, mixed>  $answers
     * @return array{score: float, total: float, stored: array<string, mixed>, manual_pending: int}
     */
    public static function grade(Collection $questions, array $answers): array
    {
        $score = 0.0;
        $total = 0.0;
        $manualPending = 0;
        $stored = [];

        foreach ($questions as $question) {
            $type = $question->question_type ?: 'multiple_choice';
            $points = (float) ($question->points ?: 1);
            $raw = $answers[$question->id] ?? $answers[(string) $question->id] ?? null;

            if (in_array($type, ['short_answer', 'essay'], true)) {
                $text = is_string($raw) ? trim($raw) : '';
                $stored[(string) $question->id] = $text;
                $total += $points; // counted in total; teacher awards points later
                $manualPending++;
                continue;
            }

            $total += $points;

            if ($type === 'matching') {
                $map = is_array($raw) ? $raw : [];
                $clean = [];
                foreach ($map as $leftKey => $rightKey) {
                    $clean[(string) $leftKey] = (string) $rightKey;
                }
                $stored[(string) $question->id] = $clean;
                $score += self::scoreMatching($question, $clean) * $points;
                continue;
            }

            if ($type === 'true_false') {
                $picked = strtolower(trim((string) $raw));
                if (!in_array($picked, ['true', 'false', 't', 'f'], true)) {
                    $picked = '';
                }
                if ($picked === 't') {
                    $picked = 'true';
                }
                if ($picked === 'f') {
                    $picked = 'false';
                }
                $stored[(string) $question->id] = $picked;
                $correct = strtolower((string) ($question->correct_option ?: ''));
                if ($correct === 't') {
                    $correct = 'true';
                }
                if ($correct === 'f') {
                    $correct = 'false';
                }
                if ($picked !== '' && $picked === $correct) {
                    $score += $points;
                }
                continue;
            }

            if ($type === 'fill_blank') {
                $picked = trim((string) $raw);
                $stored[(string) $question->id] = $picked;
                if ($picked !== '' && self::fillBlankMatches($question, $picked)) {
                    $score += $points;
                }
                continue;
            }

            // multiple_choice (default)
            $picked = strtoupper(trim((string) $raw));
            if (!in_array($picked, ['A', 'B', 'C', 'D'], true)) {
                $picked = '';
            }
            $stored[(string) $question->id] = $picked;
            if ($picked !== '' && $picked === strtoupper((string) $question->correct_option)) {
                $score += $points;
            }
        }

        return [
            'score' => round($score, 2),
            'total' => round($total, 2),
            'stored' => $stored,
            'manual_pending' => $manualPending,
        ];
    }

    public static function scoreMatching(StudentPortalQuizQuestion $question, array $studentMap): float
    {
        $config = self::config($question);
        $correct = $config['answers'] ?? [];
        if (!is_array($correct) || $correct === []) {
            return 0.0;
        }

        $ok = 0;
        $n = 0;
        foreach ($correct as $leftKey => $rightKey) {
            $n++;
            if (isset($studentMap[(string) $leftKey])
                && (string) $studentMap[(string) $leftKey] === (string) $rightKey) {
                $ok++;
            }
        }

        return $n > 0 ? ($ok / $n) : 0.0;
    }

    public static function fillBlankMatches(StudentPortalQuizQuestion $question, string $picked): bool
    {
        $config = self::config($question);
        $accepted = [];
        if (!empty($question->correct_option)) {
            $accepted[] = (string) $question->correct_option;
        }
        if (!empty($config['answers']) && is_array($config['answers'])) {
            foreach ($config['answers'] as $a) {
                $accepted[] = (string) $a;
            }
        }
        if (!empty($config['answer'])) {
            $accepted[] = (string) $config['answer'];
        }

        $pickedNorm = self::normalizeText($picked);
        foreach ($accepted as $ans) {
            if ($pickedNorm !== '' && $pickedNorm === self::normalizeText($ans)) {
                return true;
            }
        }

        return false;
    }

    public static function config(StudentPortalQuizQuestion $question): array
    {
        $config = $question->config;
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($config) ? $config : [];
    }

    public static function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'multiple_choice' => 'Multiple choice',
            'true_false' => 'True / False',
            'matching' => 'Matching',
            'fill_blank' => 'Fill in the blank',
            'short_answer' => 'Short answer',
            'essay' => 'Essay / Assignment',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    public static function isManualType(string $type): bool
    {
        return in_array($type, ['short_answer', 'essay'], true);
    }

    /**
     * Merge auto score with teacher manual marks for written questions.
     *
     * @param  Collection<int, StudentPortalQuizQuestion>  $questions
     * @param  array<string, mixed>  $storedAnswers
     * @param  array<string, mixed>  $manualScores
     * @return array{score: float, total: float, auto_score: float, manual_awarded: float, pending: int}
     */
    public static function recomputeWithManual(Collection $questions, array $storedAnswers, array $manualScores = []): array
    {
        $graded = self::grade($questions, $storedAnswers);
        $autoScore = (float) $graded['score'];
        $manualAwarded = 0.0;
        $pending = 0;

        foreach ($questions as $question) {
            $type = $question->question_type ?: 'multiple_choice';
            if (!self::isManualType($type)) {
                continue;
            }

            $qid = (string) $question->id;
            $max = (float) ($question->points ?: 1);
            if (array_key_exists($qid, $manualScores) || array_key_exists($question->id, $manualScores)) {
                $raw = $manualScores[$qid] ?? $manualScores[$question->id];
                $pts = is_numeric($raw) ? (float) $raw : 0.0;
                $pts = max(0.0, min($max, $pts));
                $manualAwarded += $pts;
            } else {
                $pending++;
            }
        }

        return [
            'score' => round($autoScore + $manualAwarded, 2),
            'total' => (float) $graded['total'],
            'auto_score' => round($autoScore, 2),
            'manual_awarded' => round($manualAwarded, 2),
            'pending' => $pending,
        ];
    }
}
