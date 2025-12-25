@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manajemen Ruangan</h1>
    <button onclick="showAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>Tambah Ruangan
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($rooms as $room)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $room->name }}</h3>
            <div class="flex space-x-2">
                <button onclick="editRoom({{ $room->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteRoom({{ $room->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center">
                <i class="fas fa-users w-4 mr-2"></i>
                Kapasitas: {{ $room->capacity }} orang
            </div>
            @if($room->facilities)
            <div class="flex items-start">
                <i class="fas fa-cogs w-4 mr-2 mt-1"></i>
                <span>{{ $room->facilities }}</span>
            </div>
            @endif
            <div class="flex items-center">
                <i class="fas fa-calendar w-4 mr-2"></i>
                {{ $room->meetings_count }} rapat terjadwal
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Add/Edit Modal -->
<div id="roomModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-800 dark:text-gray-200">Tambah Ruangan</h3>
                    <button onclick="closeModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="roomForm" method="POST">
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Ruangan</label>
                        <input type="text" id="name" name="name" required
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kapasitas</label>
                        <input type="number" id="capacity" name="capacity" min="1" required
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label for="facilities" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fasilitas</label>
                        <textarea id="facilities" name="facilities" rows="3"
                                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                            Batal
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Ruangan';
    document.getElementById('roomForm').action = '{{ route("rooms.store") }}';
    document.getElementById('methodField').value = '';
    document.getElementById('roomForm').reset();
    document.getElementById('roomModal').classList.remove('hidden');
}

function editRoom(roomId) {
    fetch(`/rooms/${roomId}`)
        .then(response => response.json())
        .then(room => {
            document.getElementById('modalTitle').textContent = 'Edit Ruangan';
            document.getElementById('roomForm').action = `/rooms/${roomId}`;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('name').value = room.name;
            document.getElementById('capacity').value = room.capacity;
            document.getElementById('facilities').value = room.facilities || '';
            document.getElementById('roomModal').classList.remove('hidden');
        });
}

function deleteRoom(roomId) {
    Swal.fire({
        title: 'Hapus Ruangan?',
        text: 'Ruangan yang dihapus tidak dapat dikembalikan!',
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
            form.action = `/rooms/${roomId}`;
            
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

function closeModal() {
    document.getElementById('roomModal').classList.add('hidden');
}
</script>
@endpush
@endsection