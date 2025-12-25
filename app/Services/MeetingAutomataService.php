<?php

namespace App\Services;

class MeetingAutomataService
{
    const DRAFT = 'DRAFT';
    const SCHEDULED = 'SCHEDULED';
    const ONGOING = 'ONGOING';
    const COMPLETED = 'COMPLETED';
    const CANCELLED = 'CANCELLED';

    private static array $transitions = [
        self::DRAFT => [self::SCHEDULED, self::CANCELLED],
        self::SCHEDULED => [self::ONGOING, self::CANCELLED],
        self::ONGOING => [self::COMPLETED],
        self::COMPLETED => [],
        self::CANCELLED => [self::DRAFT]
    ];

    public static function canTransitionTo(string $currentState, string $nextState): bool
    {
        return isset(self::$transitions[$currentState]) && 
               in_array($nextState, self::$transitions[$currentState]);
    }

    public static function getPossibleTransitions(string $currentState): array
    {
        return self::$transitions[$currentState] ?? [];
    }

    public static function transition(string $currentStatus, string $newStatus): bool|string
    {
        if (!self::canTransitionTo($currentStatus, $newStatus)) {
            return "Transisi dari {$currentStatus} ke {$newStatus} tidak diperbolehkan.";
        }

        return true;
    }

    public static function getAllStates(): array
    {
        return [self::DRAFT, self::SCHEDULED, self::ONGOING, self::COMPLETED, self::CANCELLED];
    }
}