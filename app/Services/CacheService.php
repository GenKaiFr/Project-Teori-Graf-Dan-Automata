<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Meeting;
use Carbon\Carbon;

class CacheService
{
    const CACHE_TTL = 300; // 5 minutes
    const MEETINGS_CACHE_KEY = 'meetings_data';
    const CONFLICT_CACHE_KEY = 'conflict_check_';
    const STATS_CACHE_KEY = 'meeting_stats';

    public static function getMeetingsData()
    {
        return Cache::remember(self::MEETINGS_CACHE_KEY, self::CACHE_TTL, function () {
            return Meeting::with(['room', 'participants'])->get();
        });
    }

    public static function getConflictCheck($startTime, $endTime, $roomId, $excludeId = null)
    {
        $cacheKey = self::CONFLICT_CACHE_KEY . md5($startTime . $endTime . $roomId . $excludeId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startTime, $endTime, $roomId, $excludeId) {
            $query = Meeting::with('room')
                ->where('room_id', $roomId)
                ->whereIn('status', ['DRAFT', 'SCHEDULED', 'ONGOING'])
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($subQ) use ($startTime, $endTime) {
                        $subQ->where('start_time', '<', $endTime)
                             ->where('end_time', '>', $startTime);
                    });
                });

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            return $query->get();
        });
    }

    public static function getStatistics()
    {
        return Cache::remember(self::STATS_CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'total_meetings' => Meeting::count(),
                'meetings_this_month' => Meeting::whereMonth('start_time', now()->month)->count(),
                'meetings_today' => Meeting::whereDate('start_time', today())->count(),
                'upcoming_meetings' => Meeting::where('start_time', '>', now())
                    ->whereIn('status', ['DRAFT', 'SCHEDULED'])->count(),
            ];
        });
    }

    public static function clearMeetingsCache()
    {
        Cache::forget(self::MEETINGS_CACHE_KEY);
        Cache::forget(self::STATS_CACHE_KEY);
        
        // Clear conflict cache patterns - handle different cache drivers
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $keys = Cache::getRedis()->keys('*' . self::CONFLICT_CACHE_KEY . '*');
                if (!empty($keys)) {
                    Cache::getRedis()->del($keys);
                }
            }
        } catch (\Exception $e) {
            // Fallback for non-Redis cache drivers (like array for testing)
            // Just ignore the error as cache will expire naturally
        }
    }

    public static function clearConflictCache($roomId = null)
    {
        if ($roomId) {
            $pattern = '*' . self::CONFLICT_CACHE_KEY . '*' . $roomId . '*';
        } else {
            $pattern = '*' . self::CONFLICT_CACHE_KEY . '*';
        }
        
        $keys = Cache::getRedis()->keys($pattern);
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
}