<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rapat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px 0;
        }
        .meetings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .meetings-table th,
        .meetings-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .meetings-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft { background-color: #fef3c7; color: #92400e; }
        .status-scheduled { background-color: #d1fae5; color: #065f46; }
        .status-ongoing { background-color: #dbeafe; color: #1e40af; }
        .status-completed { background-color: #f3f4f6; color: #374151; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN RAPAT</h1>
        <p>Sistem Penjadwalan Rapat Cerdas</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="150"><strong>Periode:</strong></td>
                <td>{{ $start_date }} s/d {{ $end_date }}</td>
            </tr>
            @if($room)
            <tr>
                <td><strong>Ruangan:</strong></td>
                <td>{{ $room->name }}</td>
            </tr>
            @endif
            @if($status)
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ $status }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total Rapat:</strong></td>
                <td>{{ $meetings->count() }} rapat</td>
            </tr>
            <tr>
                <td><strong>Dibuat pada:</strong></td>
                <td>{{ $generated_at }}</td>
            </tr>
        </table>
    </div>

    @if($meetings->count() > 0)
        <div class="summary">
            <h3>Ringkasan</h3>
            <table width="100%">
                <tr>
                    <td>Draft: {{ $meetings->where('status', 'DRAFT')->count() }}</td>
                    <td>Scheduled: {{ $meetings->where('status', 'SCHEDULED')->count() }}</td>
                    <td>Ongoing: {{ $meetings->where('status', 'ONGOING')->count() }}</td>
                </tr>
                <tr>
                    <td>Completed: {{ $meetings->where('status', 'COMPLETED')->count() }}</td>
                    <td>Cancelled: {{ $meetings->where('status', 'CANCELLED')->count() }}</td>
                    <td>Total Durasi: {{ $meetings->sum(function($m) { return $m->start_time->diffInMinutes($m->end_time); }) }} menit</td>
                </tr>
            </table>
        </div>

        <table class="meetings-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Judul</th>
                    <th width="15%">Waktu</th>
                    <th width="15%">Ruangan</th>
                    <th width="10%">Status</th>
                    <th width="30%">Peserta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meetings as $index => $meeting)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $meeting->title }}</strong>
                        @if($meeting->description)
                            <br><small>{{ Str::limit($meeting->description, 50) }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $meeting->start_time->format('d/m/Y') }}<br>
                        {{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
                    </td>
                    <td>{{ $meeting->room->name ?? '-' }}</td>
                    <td>
                        <span class="status status-{{ strtolower($meeting->status) }}">
                            {{ $meeting->status }}
                        </span>
                    </td>
                    <td>
                        @if($meeting->participants->count() > 0)
                            {{ $meeting->participants->pluck('name')->implode(', ') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <p>Tidak ada data rapat untuk periode yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Penjadwalan Rapat Cerdas</p>
    </div>
</body>
</html>