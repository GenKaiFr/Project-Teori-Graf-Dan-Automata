@extends('layouts.app')

@section('title', 'Advanced Analytics Dashboard')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Advanced Analytics Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">Analisis mendalam dan prediksi untuk optimasi meeting</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="refreshAnalytics()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Refresh Data
            </button>
            <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>Export Report
            </button>
        </div>
    </div>
</div>

<!-- Performance Alerts -->
@if(!empty($performance['alerts']))
<div class="mb-6">
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Performance Alerts</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    @foreach($performance['alerts'] as $alert)
                        <div class="mb-1">• {{ $alert['message'] }}: <strong>{{ $alert['value'] }}</strong></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Productivity Score</p>
                <p class="text-3xl font-bold">{{ $insights['productivity_score'] }}</p>
                <p class="text-blue-100 text-xs">dari 100</p>
            </div>
            <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-medium">Success Rate</p>
                <p class="text-3xl font-bold">{{ $analytics['efficiency']['success_rate'] }}%</p>
                <p class="text-green-100 text-xs">meeting completed</p>
            </div>
            <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-medium">Resource Utilization</p>
                <p class="text-3xl font-bold">{{ $analytics['efficiency']['resource_utilization'] }}%</p>
                <p class="text-purple-100 text-xs">kapasitas terpakai</p>
            </div>
            <div class="p-3 bg-purple-400 bg-opacity-30 rounded-full">
                <i class="fas fa-building text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm font-medium">Avg Cost/Meeting</p>
                <p class="text-3xl font-bold">{{ number_format($analytics['costs']['avg_per_meeting']/1000, 0) }}K</p>
                <p class="text-orange-100 text-xs">rupiah</p>
            </div>
            <div class="p-3 bg-orange-400 bg-opacity-30 rounded-full">
                <i class="fas fa-money-bill-wave text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm font-medium">Quality Index</p>
                <p class="text-3xl font-bold">{{ $insights['meeting_quality_index'] }}</p>
                <p class="text-teal-100 text-xs">dari 100</p>
            </div>
            <div class="p-3 bg-teal-400 bg-opacity-30 rounded-full">
                <i class="fas fa-star text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Growth Metrics -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Growth Trends</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Monthly Growth</span>
                <span class="font-semibold {{ $analytics['growth']['monthly'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $analytics['growth']['monthly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['monthly'] }}%
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Quarterly Growth</span>
                <span class="font-semibold {{ $analytics['growth']['quarterly'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $analytics['growth']['quarterly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['quarterly'] }}%
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Yearly Growth</span>
                <span class="font-semibold {{ $analytics['growth']['yearly'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $analytics['growth']['yearly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['yearly'] }}%
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Peak Times</h3>
        <div class="space-y-3">
            @foreach($analytics['peak_times'] as $index => $peak)
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">#{{ $index + 1 }} {{ $peak['hour'] }}</span>
                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded text-sm">
                    {{ $peak['count'] }} meetings
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Predictions</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Next Month</span>
                <span class="font-semibold text-blue-600">{{ $predictions['next_month_meetings'] }} meetings</span>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Berdasarkan trend 3 bulan terakhir
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Trend Analysis Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Trend Analysis</h3>
        <div class="chart-container">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Room Demand Forecast -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Room Demand Forecast</h3>
        <div class="chart-container">
            <canvas id="demandChart"></canvas>
        </div>
    </div>
</div>

<!-- Insights and Recommendations -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Business Insights -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Business Insights</h3>
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h4 class="font-medium text-blue-800 dark:text-blue-200">Collaboration Score</h4>
                <p class="text-2xl font-bold text-blue-600">{{ $insights['collaboration_metrics']['collaboration_score'] }}</p>
                <p class="text-sm text-blue-600 dark:text-blue-400">
                    {{ $insights['collaboration_metrics']['cross_department_rate'] }}% cross-department meetings
                </p>
            </div>
            
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <h4 class="font-medium text-red-800 dark:text-red-200">Time Waste Analysis</h4>
                <p class="text-2xl font-bold text-red-600">{{ $insights['time_waste_analysis']['long_meetings_percent'] }}%</p>
                <p class="text-sm text-red-600 dark:text-red-400">
                    meetings lebih dari 2 jam ({{ $insights['time_waste_analysis']['estimated_waste_hours'] }} jam terbuang)
                </p>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Smart Recommendations</h3>
        <div class="space-y-3">
            @foreach($insights['recommendations'] as $recommendation)
            <div class="p-3 border-l-4 {{ $recommendation['priority'] === 'high' ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : ($recommendation['priority'] === 'medium' ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20' : 'border-blue-400 bg-blue-50 dark:bg-blue-900/20') }} rounded-r">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-medium {{ $recommendation['priority'] === 'high' ? 'text-red-800 dark:text-red-200' : ($recommendation['priority'] === 'medium' ? 'text-yellow-800 dark:text-yellow-200' : 'text-blue-800 dark:text-blue-200') }}">
                            {{ $recommendation['title'] }}
                        </h4>
                        <p class="text-sm {{ $recommendation['priority'] === 'high' ? 'text-red-600 dark:text-red-400' : ($recommendation['priority'] === 'medium' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400') }} mt-1">
                            {{ $recommendation['description'] }}
                        </p>
                        <p class="text-xs {{ $recommendation['priority'] === 'high' ? 'text-red-500 dark:text-red-500' : ($recommendation['priority'] === 'medium' ? 'text-yellow-500 dark:text-yellow-500' : 'text-blue-500 dark:text-blue-500') }} mt-2">
                            <i class="fas fa-lightbulb mr-1"></i>{{ $recommendation['action'] }}
                        </p>
                    </div>
                    <span class="ml-2 px-2 py-1 text-xs rounded {{ $recommendation['priority'] === 'high' ? 'bg-red-200 text-red-800' : ($recommendation['priority'] === 'medium' ? 'bg-yellow-200 text-yellow-800' : 'bg-blue-200 text-blue-800') }}">
                        {{ ucfirst($recommendation['priority']) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Optimization Opportunities -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Optimization Opportunities</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Area</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Opportunity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Potential Saving</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Effort</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($performance['optimization_opportunities'] as $opportunity)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $opportunity['area'] }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $opportunity['opportunity'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                        {{ $opportunity['potential_saving'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $opportunity['effort'] === 'Low' ? 'bg-green-100 text-green-800' : 
                               ($opportunity['effort'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $opportunity['effort'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Capacity Planning -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Capacity Planning</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $predictions['capacity_planning']['current_capacity'] }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Current Capacity (hours)</p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $predictions['capacity_planning']['current_usage_percent'] }}%</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Current Usage</p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $predictions['capacity_planning']['projected_growth_percent'] }}%</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Projected Growth</p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-2xl font-bold {{ $predictions['capacity_planning']['recommendation'] === 'Tambah ruangan' ? 'text-red-600' : 'text-green-600' }}">
                {{ $predictions['capacity_planning']['recommendation'] }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Recommendation</p>
        </div>
    </div>
</div>

@push('scripts')
<style>
.chart-container {
    position: relative;
    height: 20rem;
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

    // Trend Analysis Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['7 Days', '30 Days', '90 Days', '365 Days'],
            datasets: [{
                label: 'Meetings per Day',
                data: [
                    {{ $trends['7_days']['meetings_per_day'] }},
                    {{ $trends['30_days']['meetings_per_day'] }},
                    {{ $trends['90_days']['meetings_per_day'] }},
                    {{ $trends['365_days']['meetings_per_day'] }}
                ],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
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
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Room Demand Forecast Chart
    const demandCtx = document.getElementById('demandChart').getContext('2d');
    new Chart(demandCtx, {
        type: 'bar',
        data: {
            labels: @json(array_column($predictions['room_demand'], 'room')),
            datasets: [{
                label: 'Current Usage',
                data: @json(array_column($predictions['room_demand'], 'current')),
                backgroundColor: '#10b981',
                borderColor: '#059669',
                borderWidth: 1
            }, {
                label: 'Predicted Usage',
                data: @json(array_column($predictions['room_demand'], 'predicted')),
                backgroundColor: '#f59e0b',
                borderColor: '#d97706',
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
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                }
            }
        }
    });
});

function refreshAnalytics() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Refreshing...';
    button.disabled = true;
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function exportReport() {
    // Show export options modal
    Swal.fire({
        title: 'Export Analytics Report',
        html: `
            <div class="text-left">
                <p class="mb-4 text-gray-600">Pilih format export yang diinginkan:</p>
                <div class="space-y-2">
                    <button onclick="exportToPDF()" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export ke PDF
                    </button>
                    <button onclick="exportToExcel()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors">
                        <i class="fas fa-file-excel mr-2"></i>Export ke Excel
                    </button>
                    <button onclick="exportToJSON()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                        <i class="fas fa-file-code mr-2"></i>Export ke JSON
                    </button>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        width: '400px'
    });
}

function exportToPDF() {
    Swal.close();
    window.open('/analytics/export-pdf', '_blank');
}

function exportToExcel() {
    Swal.close();
    window.open('/analytics/export-excel', '_blank');
}

function exportToJSON() {
    Swal.close();
    const reportData = {
        timestamp: new Date().toISOString(),
        kpis: {
            productivity_score: {{ $insights['productivity_score'] }},
            success_rate: {{ $analytics['efficiency']['success_rate'] }},
            resource_utilization: {{ $analytics['efficiency']['resource_utilization'] }},
            quality_index: {{ $insights['meeting_quality_index'] }}
        },
        growth: @json($analytics['growth']),
        predictions: @json($predictions),
        recommendations: @json($insights['recommendations'])
    };
    
    const dataStr = JSON.stringify(reportData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `analytics-report-${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    URL.revokeObjectURL(url);
    
    Swal.fire('Success!', 'Analytics report exported successfully!', 'success');
}
</script>
@endpush
@endsection