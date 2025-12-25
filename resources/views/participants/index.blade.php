@extends('layouts.app')

@section('title', 'Manajemen Peserta')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manajemen Peserta</h1>
    <button onclick="showAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-user-plus mr-2"></i>Tambah Peserta
    </button>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rapat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($participants as $participant)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $participant->name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $participant->email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($participant->role === 'manager') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                        {{ ucfirst($participant->role) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    {{ $participant->meetings_count }} rapat
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="editParticipant({{ $participant->id }})" 
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteParticipant({{ $participant->id }})" 
                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div id="participantModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-800 dark:text-gray-200">Tambah Peserta</h3>
                    <button onclick="closeModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="participantForm" method="POST">
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select id="role" name="role" required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="participant">Participant</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
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
    document.getElementById('modalTitle').textContent = 'Tambah Peserta';
    document.getElementById('participantForm').action = '{{ route("participants.store") }}';
    document.getElementById('methodField').value = '';
    document.getElementById('participantForm').reset();
    document.getElementById('participantModal').classList.remove('hidden');
}

function editParticipant(participantId) {
    fetch(`/participants/${participantId}`)
        .then(response => response.json())
        .then(participant => {
            document.getElementById('modalTitle').textContent = 'Edit Peserta';
            document.getElementById('participantForm').action = `/participants/${participantId}`;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('name').value = participant.name;
            document.getElementById('email').value = participant.email;
            document.getElementById('role').value = participant.role;
            document.getElementById('participantModal').classList.remove('hidden');
        });
}

function deleteParticipant(participantId) {
    Swal.fire({
        title: 'Hapus Peserta?',
        text: 'Peserta yang dihapus tidak dapat dikembalikan!',
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
            form.action = `/participants/${participantId}`;
            
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
    document.getElementById('participantModal').classList.add('hidden');
}
</script>
@endpush
@endsection