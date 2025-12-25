<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #1f2937;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .kpi-row {
            display: table-row;
        }
        .kpi-cell {
            display: table-cell;
            width: 20%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
        }
        .kpi-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .metric-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .metric-table th,
        .metric-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .metric-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .growth-positive {
            color: #10b981;
        }
        .growth-negative {
            color: #ef4444;
        }
        .recommendation {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 10px;
            margin-bottom: 10px;
        }
        .recommendation.high {
            border-left-color: #ef4444;
            background-color: #fef2f2;
        }
        .recommendation.medium {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }
        .recommendation-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .recommendation-priority {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            display: inline-block;
            margin-bottom: 5px;
        }
        .priority-high {
            background-color: #ef4444;
        }
        .priority-medium {
            background-color: #f59e0b;
        }
        .priority-low {
            background-color: #3b82f6;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Advanced Analytics Report</h1>
        <p>Sistem Penjadwalan Rapat Cerdas</p>
        <p>Generated on: {{ now()->format('d F Y, H:i') }}</p>
    </div>

    <!-- KPI Section -->
    <div class="section">
        <h2>Key Performance Indicators</h2>
        <div class="kpi-grid">
            <div class="kpi-row">
                <div class="kpi-cell">
                    <div class="kpi-value">{{ $insights['productivity_score'] }}</div>
                    <div class="kpi-label">Productivity Score</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-value">{{ $analytics['efficiency']['success_rate'] }}%</div>
                    <div class="kpi-label">Success Rate</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-value">{{ $analytics['efficiency']['resource_utilization'] }}%</div>
                    <div class="kpi-label">Resource Utilization</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-value">{{ number_format($analytics['costs']['avg_per_meeting']/1000, 0) }}K</div>
                    <div class="kpi-label">Avg Cost/Meeting</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-value">{{ $insights['meeting_quality_index'] }}</div>
                    <div class="kpi-label">Quality Index</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Metrics -->
    <div class="section">
        <h2>Growth Analysis</h2>
        <table class="metric-table">
            <tr>
                <th>Period</th>
                <th>Growth Rate</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Monthly Growth</td>
                <td class="{{ $analytics['growth']['monthly'] >= 0 ? 'growth-positive' : 'growth-negative' }}">
                    {{ $analytics['growth']['monthly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['monthly'] }}%
                </td>
                <td>{{ $analytics['growth']['monthly'] >= 0 ? 'Growing' : 'Declining' }}</td>
            </tr>
            <tr>
                <td>Quarterly Growth</td>
                <td class="{{ $analytics['growth']['quarterly'] >= 0 ? 'growth-positive' : 'growth-negative' }}">
                    {{ $analytics['growth']['quarterly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['quarterly'] }}%
                </td>
                <td>{{ $analytics['growth']['quarterly'] >= 0 ? 'Growing' : 'Declining' }}</td>
            </tr>
            <tr>
                <td>Yearly Growth</td>
                <td class="{{ $analytics['growth']['yearly'] >= 0 ? 'growth-positive' : 'growth-negative' }}">
                    {{ $analytics['growth']['yearly'] >= 0 ? '+' : '' }}{{ $analytics['growth']['yearly'] }}%
                </td>
                <td>{{ $analytics['growth']['yearly'] >= 0 ? 'Growing' : 'Declining' }}</td>
            </tr>
        </table>
    </div>

    <!-- Predictions -->
    <div class="section">
        <h2>Predictions & Forecasts</h2>
        <table class="metric-table">
            <tr>
                <th>Metric</th>
                <th>Prediction</th>
                <th>Confidence</th>
            </tr>
            <tr>
                <td>Next Month Meetings</td>
                <td>{{ $predictions['next_month_meetings'] }} meetings</td>
                <td>Based on 3-month trend</td>
            </tr>
            <tr>
                <td>Capacity Status</td>
                <td>{{ $predictions['capacity_planning']['recommendation'] }}</td>
                <td>{{ $predictions['capacity_planning']['current_usage_percent'] }}% current usage</td>
            </tr>
        </table>
    </div>

    <!-- Business Insights -->
    <div class="section">
        <h2>Business Insights</h2>
        <table class="metric-table">
            <tr>
                <th>Insight</th>
                <th>Value</th>
                <th>Impact</th>
            </tr>
            <tr>
                <td>Collaboration Score</td>
                <td>{{ $insights['collaboration_metrics']['collaboration_score'] }}</td>
                <td>{{ $insights['collaboration_metrics']['cross_department_rate'] }}% cross-department meetings</td>
            </tr>
            <tr>
                <td>Time Waste</td>
                <td>{{ $insights['time_waste_analysis']['long_meetings_percent'] }}%</td>
                <td>{{ $insights['time_waste_analysis']['estimated_waste_hours'] }} hours wasted</td>
            </tr>
            <tr>
                <td>Average Duration</td>
                <td>{{ $analytics['efficiency']['avg_duration'] }} minutes</td>
                <td>{{ $analytics['efficiency']['avg_duration'] > 60 ? 'Above optimal' : 'Within optimal range' }}</td>
            </tr>
        </table>
    </div>

    <!-- Peak Times -->
    <div class="section">
        <h2>Peak Usage Times</h2>
        <table class="metric-table">
            <tr>
                <th>Rank</th>
                <th>Time Slot</th>
                <th>Meeting Count</th>
            </tr>
            @foreach($analytics['peak_times'] as $index => $peak)
            <tr>
                <td>#{{ $index + 1 }}</td>
                <td>{{ $peak['hour'] }}</td>
                <td>{{ $peak['count'] }} meetings</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Recommendations -->
    <div class="section">
        <h2>Smart Recommendations</h2>
        @foreach($insights['recommendations'] as $recommendation)
        <div class="recommendation {{ $recommendation['priority'] }}">
            <div class="recommendation-priority priority-{{ $recommendation['priority'] }}">
                {{ strtoupper($recommendation['priority']) }} PRIORITY
            </div>
            <div class="recommendation-title">{{ $recommendation['title'] }}</div>
            <div>{{ $recommendation['description'] }}</div>
            <div style="margin-top: 5px; font-style: italic;">
                💡 Action: {{ $recommendation['action'] }}
            </div>
        </div>
        @endforeach
    </div>

    <!-- Optimization Opportunities -->
    <div class="section">
        <h2>Optimization Opportunities</h2>
        <table class="metric-table">
            <tr>
                <th>Area</th>
                <th>Opportunity</th>
                <th>Potential Saving</th>
                <th>Effort Required</th>
            </tr>
            @foreach($performance['optimization_opportunities'] as $opportunity)
            <tr>
                <td>{{ $opportunity['area'] }}</td>
                <td>{{ $opportunity['opportunity'] }}</td>
                <td>{{ $opportunity['potential_saving'] }}</td>
                <td>{{ $opportunity['effort'] }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by the Meeting Scheduler Analytics System</p>
        <p>© 2024 Meeting Scheduler - Advanced Analytics Dashboard</p>
    </div>
</body>
</html>