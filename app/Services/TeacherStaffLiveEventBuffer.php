<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TeacherStaffLiveEventBuffer
{
    private const MAX_EVENTS = 40;

    public static function cacheKey(int $schoolID): string
    {
        return 'ts_live_events:' . $schoolID . ':' . Carbon::today()->toDateString();
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    public static function push(int $schoolID, array $event): array
    {
        $action = (string) ($event['action'] ?? '');
        if ($action === 'Ignored') {
            return $event;
        }

        $event['id'] = $event['id'] ?? self::makeEventId($event);
        $event['at'] = $event['at'] ?? now()->toIso8601String();

        $key = self::cacheKey($schoolID);
        $events = Cache::get($key, []);
        if (!is_array($events)) {
            $events = [];
        }

        array_unshift($events, $event);
        $events = array_slice($events, 0, self::MAX_EVENTS);

        Cache::put($key, $events, Carbon::today()->endOfDay()->addHour());

        return $event;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(int $schoolID): array
    {
        $events = Cache::get(self::cacheKey($schoolID), []);

        return is_array($events) ? $events : [];
    }

    /**
     * @param array<string, mixed> $event
     */
    private static function makeEventId(array $event): string
    {
        return sha1(
            ($event['user_id'] ?? '0') . '|' .
            ($event['record_time'] ?? '') . '|' .
            ($event['action'] ?? '') . '|' .
            microtime(true)
        );
    }
}
