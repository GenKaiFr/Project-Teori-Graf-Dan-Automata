<?php

namespace App\Services;

use App\Models\Meeting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ConflictGraphService
{
    private array $graph = [];
    private Collection $meetings;

    public function buildGraph(Collection $meetings): void
    {
        $this->meetings = $meetings;
        $cacheKey = 'conflict_graph_' . md5($meetings->pluck('id')->sort()->implode(','));
        
        $this->graph = Cache::remember($cacheKey, 300, function () use ($meetings) {
            $graph = [];
            
            foreach ($meetings as $meeting) {
                $graph[$meeting->id] = [];
            }

            foreach ($meetings as $meetingA) {
                foreach ($meetings as $meetingB) {
                    if ($meetingA->id !== $meetingB->id && $meetingA->isConflicting($meetingB)) {
                        $graph[$meetingA->id][] = $meetingB->id;
                    }
                }
            }
            
            return $graph;
        });
    }

    public function hasConflict(array $newMeetingData): array|false
    {
        $cacheKey = 'conflict_check_' . md5(serialize($newMeetingData));
        
        return Cache::remember($cacheKey, 60, function () use ($newMeetingData) {
            $conflicts = [];

            foreach ($this->meetings as $existingMeeting) {
                if ($this->isNewMeetingConflicting($newMeetingData, $existingMeeting)) {
                    $conflicts[] = [
                        'meeting_id' => $existingMeeting->id,
                        'title' => $existingMeeting->title,
                        'start_time' => $existingMeeting->start_time->format('Y-m-d H:i:s'),
                        'end_time' => $existingMeeting->end_time->format('Y-m-d H:i:s'),
                        'room_name' => $existingMeeting->room->name ?? 'Unknown Room'
                    ];
                }
            }

            return !empty($conflicts) ? $conflicts : false;
        });
    }

    private function isNewMeetingConflicting(array $newMeeting, Meeting $existing): bool
    {
        $activeStatuses = ['DRAFT', 'SCHEDULED', 'ONGOING'];
        
        if (!in_array($existing->status, $activeStatuses)) {
            return false;
        }

        if ($newMeeting['room_id'] != $existing->room_id) {
            return false;
        }

        $startNew = strtotime($newMeeting['start_time']);
        $endNew = strtotime($newMeeting['end_time']);
        $startExisting = $existing->start_time->timestamp;
        $endExisting = $existing->end_time->timestamp;

        return ($startNew < $endExisting) && ($endNew > $startExisting);
    }

    public function getGraph(): array
    {
        return $this->graph;
    }

    public function getConflictsFor(int $meetingId): array
    {
        return $this->graph[$meetingId] ?? [];
    }

    public function getGraphData(): array
    {
        $nodes = [];
        $links = [];
        $addedLinks = [];

        foreach ($this->meetings as $meeting) {
            $nodes[] = [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'room_name' => $meeting->room->name ?? 'Unknown Room',
                'start_time' => $meeting->start_time->format('Y-m-d H:i:s'),
                'end_time' => $meeting->end_time->format('Y-m-d H:i:s'),
                'status' => $meeting->status
            ];
        }

        foreach ($this->graph as $meetingId => $conflicts) {
            foreach ($conflicts as $conflictId) {
                $linkKey = min($meetingId, $conflictId) . '-' . max($meetingId, $conflictId);
                if (!in_array($linkKey, $addedLinks)) {
                    $links[] = [
                        'source' => $meetingId,
                        'target' => $conflictId
                    ];
                    $addedLinks[] = $linkKey;
                }
            }
        }

        return ['nodes' => $nodes, 'links' => $links];
    }
}