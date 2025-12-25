@extends('layouts.app')

@section('title', 'Meeting Statistics')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Meeting Statistics</h1>
    <p class="text-gray-600 dark:text-gray-400">Analisis dan insight tentang pola meeting</p>
</div>

<!-- Basic Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Meetings</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_meetings']) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @if($stats['meetings_last_month'] > 0)
                        @php
                            $growth = (($stats['meetings_this_month'] - $stats['meetings_last_month']) / $stats['meetings_last_month']) * 100;
                        @endphp
                        <span class="{{ $growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                        </span> dari bulan lalu
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Duration</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_duration_hours'] }}h</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Rata-rata {{ $stats['avg_duration_minutes'] }} menit/meeting</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Participants</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_participants'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Peserta aktif</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['meetings_this_month'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Meeting bulan ini</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Monthly Trend -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Monthly Meeting Trend</h3>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Status Distribution</h3>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Room Utilization -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Room Utilization (Last Month)</h3>
        <div class="chart-container">
            <canvas id="roomChart"></canvas>
        </div>
    </div>

    <!-- Time Patterns -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Meeting Time Patterns</h3>
        <div class="chart-container">
            <canvas id="timeChart"></canvas>
        </div>
    </div>
</div>

<!-- Participant Engagement -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Top Participants (Last Month)</h3>
    <div class="chart-container" style="height: 20rem;">
        <canvas id="participantChart"></canvas>
    </div>
</div>

@push('scripts')
<style>
.chart-container {
    position: relative;
    height: 16rem;
    width: 100%;
}
.chart-container canvas {
    max-height: 100% !important;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#f3f4f6' : '#374151';
    const gridColor = isDark ? '#4b5563' : '#e5e7eb';

    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyData['months']),
            datasets: [{
                label: 'Meetings',
                data: @json($monthlyData['counts']),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 2,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { 
                        color: textColor,
                        maxRotation: 45
                    },
                    grid: { color: gridColor }
                },
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: textColor,
                        stepSize: 1
                    },
                    grid: { color: gridColor }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($statusDistribution['statuses']),
            datasets: [{
                data: @json($statusDistribution['counts']),
                backgroundColor: [
                    '#10b981', // SCHEDULED - green
                    '#f59e0b', // DRAFT - yellow
                    '#3b82f6', // ONGOING - blue
                    '#6b7280', // COMPLETED - gray
                    '#ef4444'  // CANCELLED - red
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        color: textColor,
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Room Utilization Chart
    const roomCtx = document.getElementById('roomChart').getContext('2d');
    new Chart(roomCtx, {
        type: 'bar',
        data: {
            labels: @json($roomUtilization['room_names']),
            datasets: [{
                label: 'Meetings',
                data: @json($roomUtilization['meeting_counts']),
                backgroundColor: '#8b5cf6',
                borderColor: '#7c3aed',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { 
                        color: textColor,
                        maxRotation: 45
                    },
                    grid: { color: gridColor }
                },
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: textColor,
                        stepSize: 1
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Time Patterns Chart
    const timeCtx = document.getElementById('timeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: @json($timePatterns['hourly']['hours']).map(h => h + ':00'),
            datasets: [{
                label: 'Meetings by Hour',
                data: @json($timePatterns['hourly']['counts']),
                backgroundColor: '#06b6d4',
                borderColor: '#0891b2',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { 
                        color: textColor,
                        maxRotation: 0,
                        callback: function(value, index) {
                            return index % 2 === 0 ? this.getLabelForValue(value) : '';
                        }
                    },
                    grid: { color: gridColor }
                },
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: textColor,
                        stepSize: 1
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Participant Engagement Chart
    const participantCtx = document.getElementById('participantChart').getContext('2d');
    new Chart(participantCtx, {
        type: 'bar',
        data: {
            labels: @json($participantEngagement['participant_names']),
            datasets: [{
                label: 'Meetings Attended',
                data: @json($participantEngagement['meeting_counts']),
                backgroundColor: '#f97316',
                borderColor: '#ea580c',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                }
            }
        }
    });
});
</script>
@endpush
@endsection