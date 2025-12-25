@extends('layouts.app')

@section('title', 'Daftar Rapat')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Daftar Rapat</h1>
    @if(auth()->user()->canManageMeetings())
        <a href="{{ route('meetings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i>Tambah Rapat
        </a>
    @endif
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <select name="room_id" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Semua Ruangan</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                    {{ $room->name }}
                </option>
            @endforeach
        </select>
        
        <select name="status" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Semua Status</option>
            <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
            <option value="SCHEDULED" {{ request('status') == 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
            <option value="ONGOING" {{ request('status') == 'ONGOING' ? 'selected' : '' }}>Ongoing</option>
            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
            <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
        </select>
        
        <input type="date" name="date" value="{{ request('date') }}" class="border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        
        <button type="submit" class="bg-gray-600 dark:bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-700 dark:hover:bg-gray-600">
            <i class="fas fa-search mr-2"></i>Filter
        </button>
        
        <a href="{{ route('meetings.index') }}" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-500">
            Reset
        </a>
    </form>
</div>

<!-- Meetings Grid -->
@if($meetings->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-400 dark:text-gray-500 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400">Tidak ada rapat yang ditemukan.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($meetings as $meeting)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $meeting->title }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($meeting->status === 'SCHEDULED') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($meeting->status === 'DRAFT') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($meeting->status === 'ONGOING') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif($meeting->status === 'COMPLETED') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ $meeting->status }}
                    </span>
                </div>
                
                @if($meeting->description)
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ Str::limit($meeting->description, 100) }}</p>
                @endif
                
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-clock w-4 mr-2"></i>
                        {{ $meeting->start_time->format('d/m/Y H:i') }} - {{ $meeting->end_time->format('H:i') }}
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-door-open w-4 mr-2"></i>
                        {{ $meeting->room->name }}
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users w-4 mr-2"></i>
                        {{ $meeting->participants->count() }} peserta
                    </div>
                </div>
                
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <a href="{{ route('meetings.show', $meeting) }}" 
                           class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(auth()->user()->canManageMeetings())
                            <a href="{{ route('meetings.edit', $meeting) }}" 
                               class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteMeeting({{ $meeting->id }})" 
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                    
                    @if(auth()->user()->canManageMeetings() && $meeting->status !== 'COMPLETED' && $meeting->status !== 'CANCELLED')
                        <div class="relative">
                            <button onclick="toggleStatusMenu({{ $meeting->id }})" 
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="statusMenu{{ $meeting->id }}" 
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10 hidden">
                                <div class="py-1">
                                    @foreach(\App\Services\MeetingAutomataService::getPossibleTransitions($meeting->status) as $status)
                                        <button onclick="updateStatus({{ $meeting->id }}, '{{ $status }}')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            {{ $status }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
function toggleStatusMenu(meetingId) {
    const menu = document.getElementById('statusMenu' + meetingId);
    menu.classList.toggle('hidden');
}

function updateStatus(meetingId, status) {
    if (confirm(`Ubah status ke ${status}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/meetings/${meetingId}/status`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(csrfToken);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteMeeting(meetingId) {
    Swal.fire({
        title: 'Hapus Rapat?',
        text: 'Rapat yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/meetings/${meetingId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Close status menus when clicking outside
document.addEventListener('click', function(event) {
    const statusMenus = document.querySelectorAll('[id^="statusMenu"]');
    statusMenus.forEach(menu => {
        if (!menu.contains(event.target) && !event.target.closest('button[onclick^="toggleStatusMenu"]')) {
            menu.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection