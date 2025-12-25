@extends('layouts.app')

@section('title', 'Detail Rapat')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $meeting->title }}</h1>
                    <span class="inline-block mt-2 px-3 py-1 text-sm rounded-full 
                        @if($meeting->status === 'SCHEDULED') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($meeting->status === 'DRAFT') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($meeting->status === 'ONGOING') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif($meeting->status === 'COMPLETED') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ $meeting->status }}
                    </span>
                </div>
                @if(auth()->user()->canManageMeetings())
                <div class="flex space-x-2">
                    <a href="{{ route('meetings.edit', $meeting) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <button onclick="deleteMeeting()" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </div>
                @endif
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Meeting Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Detail Rapat</h3>
                    
                    @if($meeting->description)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Deskripsi</label>
                        <p class="text-gray-800 dark:text-gray-200">{{ $meeting->description }}</p>
                    </div>
                    @endif
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Waktu</label>
                        <div class="flex items-center text-gray-800 dark:text-gray-200">
                            <i class="fas fa-clock mr-2 text-blue-600 dark:text-blue-400"></i>
                            {{ $meeting->start_time->format('d M Y, H:i') }} - {{ $meeting->end_time->format('H:i') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Durasi: {{ $meeting->start_time->diffInMinutes($meeting->end_time) }} menit
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Ruangan</label>
                        <div class="flex items-center text-gray-800 dark:text-gray-200">
                            <i class="fas fa-door-open mr-2 text-blue-600 dark:text-blue-400"></i>
                            {{ $meeting->room->name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Kapasitas: {{ $meeting->room->capacity }} orang
                            @if($meeting->room->facilities)
                                | Fasilitas: {{ $meeting->room->facilities }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Dibuat</label>
                        <p class="text-gray-800 dark:text-gray-200">{{ $meeting->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    
                    @if($meeting->updated_at != $meeting->created_at)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Terakhir Diupdate</label>
                        <p class="text-gray-800 dark:text-gray-200">{{ $meeting->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                    @endif
                </div>
                
                <!-- Participants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                        Peserta ({{ $meeting->participants->count() }})
                    </h3>
                    
                    @if($meeting->participants->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 italic">Belum ada peserta yang ditambahkan.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($meeting->participants as $participant)
                            <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                    {{ strtoupper(substr($participant->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ $participant->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $participant->email }}</p>
                                    <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full 
                                        @if($participant->role === 'manager') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @endif">
                                        {{ ucfirst($participant->role) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Status Actions -->
            @if(auth()->user()->canManageMeetings() && $meeting->status !== 'COMPLETED' && $meeting->status !== 'CANCELLED')
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Aksi Status</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach(\App\Services\MeetingAutomataService::getPossibleTransitions($meeting->status) as $status)
                        <button onclick="updateStatus('{{ $status }}')" 
                                class="px-4 py-2 rounded-lg transition
                                @if($status === 'SCHEDULED') bg-green-600 text-white hover:bg-green-700
                                @elseif($status === 'ONGOING') bg-blue-600 text-white hover:bg-blue-700
                                @elseif($status === 'COMPLETED') bg-gray-600 text-white hover:bg-gray-700
                                @elseif($status === 'CANCELLED') bg-red-600 text-white hover:bg-red-700
                                @else bg-yellow-600 text-white hover:bg-yellow-700 @endif">
                            <i class="fas fa-{{ $status === 'SCHEDULED' ? 'calendar-check' : ($status === 'ONGOING' ? 'play' : ($status === 'COMPLETED' ? 'check' : 'times')) }} mr-2"></i>
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif
            
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('meetings.index') }}" 
                   class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateStatus(status) {
    Swal.fire({
        title: 'Ubah Status?',
        text: `Ubah status rapat ke ${status}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("meetings.updateStatus", $meeting) }}';
            
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
    });
}

function deleteMeeting() {
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
            form.action = '{{ route("meetings.destroy", $meeting) }}';
            
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
</script>
@endpush
@endsection