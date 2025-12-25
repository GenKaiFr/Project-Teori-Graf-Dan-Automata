@extends('layouts.app')

@section('title', 'Bulk Operations')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Bulk Operations</h1>
    <p class="text-gray-600 dark:text-gray-400">Kelola multiple rapat sekaligus</p>
</div>

<!-- Bulk Actions Panel -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Aksi Massal</h2>
        <div class="flex space-x-2">
            <button onclick="selectAll()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-check-square mr-1"></i> Pilih Semua
            </button>
            <button onclick="deselectAll()" class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-square mr-1"></i> Batal Pilih
            </button>
        </div>
    </div>
    
    <div class="flex flex-wrap gap-3">
        <button onclick="bulkUpdateStatus('SCHEDULED')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            <i class="fas fa-calendar-check mr-2"></i>Jadwalkan Terpilih
        </button>
        <button onclick="bulkUpdateStatus('CANCELLED')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            <i class="fas fa-times-circle mr-2"></i>Batalkan Terpilih
        </button>
        <button onclick="bulkDelete()" class="px-4 py-2 bg-red-800 text-white rounded hover:bg-red-900">
            <i class="fas fa-trash mr-2"></i>Hapus Terpilih
        </button>
        <button onclick="exportSelected()" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
            <i class="fas fa-download mr-2"></i>Export Terpilih
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
    <h3 class="font-semibold mb-3 text-gray-800 dark:text-gray-200">Filter Rapat</h3>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <select name="room_id" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Semua Ruangan</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->name }}
                </option>
            @endforeach
        </select>
        
        <select name="status" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Semua Status</option>
            <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
            <option value="SCHEDULED" {{ request('status') == 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
            <option value="ONGOING" {{ request('status') == 'ONGOING' ? 'selected' : '' }}>Ongoing</option>
            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
            <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
        </select>
        
        <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-filter mr-2"></i>Filter
        </button>
    </form>
</div>

<!-- Meetings Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" class="text-blue-600 dark:text-blue-400">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Judul</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ruangan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Peserta</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($meetings as $meeting)
            <tr>
                <td class="px-6 py-4">
                    <input type="checkbox" class="meeting-checkbox text-blue-600 dark:text-blue-400" value="{{ $meeting->id }}">
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $meeting->title }}</div>
                    @if($meeting->description)
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($meeting->description, 50) }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                    {{ $meeting->room->name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                    {{ $meeting->start_time->format('d M Y H:i') }}<br>
                    <span class="text-gray-500 dark:text-gray-400">s/d {{ $meeting->end_time->format('H:i') }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($meeting->status === 'SCHEDULED') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($meeting->status === 'DRAFT') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($meeting->status === 'ONGOING') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif($meeting->status === 'COMPLETED') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ $meeting->status }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                    {{ $meeting->participants->count() }} orang
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function selectAll() {
    document.querySelectorAll('.meeting-checkbox').forEach(cb => cb.checked = true);
    updateSelectAllCheckbox();
}

function deselectAll() {
    document.querySelectorAll('.meeting-checkbox').forEach(cb => cb.checked = false);
    updateSelectAllCheckbox();
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox').checked;
    document.querySelectorAll('.meeting-checkbox').forEach(cb => cb.checked = selectAll);
}

function updateSelectAllCheckbox() {
    const checkboxes = document.querySelectorAll('.meeting-checkbox');
    const checkedBoxes = document.querySelectorAll('.meeting-checkbox:checked');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    if (checkedBoxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedBoxes.length === checkboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

function getSelectedMeetings() {
    return Array.from(document.querySelectorAll('.meeting-checkbox:checked')).map(cb => cb.value);
}

function bulkUpdateStatus(status) {
    const selected = getSelectedMeetings();
    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu rapat', 'warning');
        return;
    }

    const statusText = {
        'SCHEDULED': 'menjadwalkan',
        'CANCELLED': 'membatalkan'
    };

    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${statusText[status]} ${selected.length} rapat?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("meetings.bulkUpdateStatus") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'meeting_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function bulkDelete() {
    const selected = getSelectedMeetings();
    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu rapat', 'warning');
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus ${selected.length} rapat? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("meetings.bulkDelete") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'meeting_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function exportSelected() {
    const selected = getSelectedMeetings();
    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu rapat', 'warning');
        return;
    }

    window.location.href = `{{ route('meetings.export') }}?ids=${selected.join(',')}`;
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('meeting-checkbox')) {
        updateSelectAllCheckbox();
    }
});
</script>
@endpush
@endsection