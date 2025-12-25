@extends('layouts.app')

@section('title', 'Tambah Rapat')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Tambah Rapat Baru</h1>
        </div>
        
        <form action="{{ route('meetings.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gunakan Template (Opsional)</label>
                <select id="template_id" name="template_id" 
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <option value="">Pilih Template...</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" data-duration="{{ $template->duration_minutes }}" 
                                data-description="{{ $template->description }}" 
                                data-participants='@json($template->default_participants)'>
                            {{ $template->name }} ({{ $template->duration_minutes }} menit)
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Judul Rapat</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" 
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                       required>
                @error('title')
                    <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
                <textarea id="description" name="description" rows="3" 
                          class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Waktu Mulai</label>
                    <input type="datetime-local" id="start_time" name="start_time" value="{{ old('start_time') }}" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                           required>
                    @error('start_time')
                        <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Waktu Selesai</label>
                    <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time') }}" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                           required>
                    @error('end_time')
                        <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ruangan</label>
                <select id="room_id" name="room_id" 
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                        required>
                    <option value="">Pilih Ruangan</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }} (Kapasitas: {{ $room->capacity }})
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Peserta</label>
                <div class="max-h-40 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-700">
                    @foreach($participants as $participant)
                        <label class="flex items-center mb-2">
                            <input type="checkbox" name="participants[]" value="{{ $participant->id }}" 
                                   class="mr-2" {{ in_array($participant->id, old('participants', [])) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $participant->name }} ({{ $participant->email }})</span>
                        </label>
                    @endforeach
                </div>
                @error('participants')
                    <p class="text-red-500 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Conflict Alert -->
            <div id="conflictAlert" class="hidden mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded-lg">
                <h4 class="font-semibold mb-2">Konflik Terdeteksi!</h4>
                <div id="conflictDetails"></div>
            </div>

            @if(session('conflicts'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded-lg">
                    <h4 class="font-semibold mb-2">Konflik Terdeteksi!</h4>
                    <ul class="list-disc list-inside">
                        @foreach(session('conflicts') as $conflict)
                            <li>{{ $conflict['title'] }} ({{ $conflict['room_name'] }}) - {{ $conflict['start_time'] }} s/d {{ $conflict['end_time'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex justify-between">
                <a href="{{ route('meetings.index') }}" 
                   class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <button type="submit" id="submitBtn"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Simpan Rapat
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const roomInput = document.getElementById('room_id');
    const templateSelect = document.getElementById('template_id');
    const descriptionInput = document.getElementById('description');
    
    // Template selection handler
    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const duration = parseInt(selectedOption.dataset.duration);
            const description = selectedOption.dataset.description;
            const participants = JSON.parse(selectedOption.dataset.participants || '[]');
            
            // Fill description
            if (description) {
                descriptionInput.value = description;
            }
            
            // Auto-calculate end time based on duration
            if (startTimeInput.value && duration) {
                const startTime = new Date(startTimeInput.value);
                const endTime = new Date(startTime.getTime() + duration * 60000);
                endTimeInput.value = endTime.toISOString().slice(0, 16);
            }
            
            // Select default participants
            if (participants.length > 0) {
                const checkboxes = document.querySelectorAll('input[name="participants[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = participants.includes(parseInt(checkbox.value));
                });
            }
        }
    });
    
    // Auto-calculate end time when start time changes and template is selected
    startTimeInput.addEventListener('change', function() {
        const selectedOption = templateSelect.options[templateSelect.selectedIndex];
        if (selectedOption.value && this.value) {
            const duration = parseInt(selectedOption.dataset.duration);
            const startTime = new Date(this.value);
            const endTime = new Date(startTime.getTime() + duration * 60000);
            endTimeInput.value = endTime.toISOString().slice(0, 16);
        }
    });
    
    // Check conflict when inputs change
    [startTimeInput, endTimeInput, roomInput].forEach(input => {
        input.addEventListener('change', checkConflict);
    });
    
    function checkConflict() {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const roomId = roomInput.value;
        
        if (!startTime || !endTime || !roomId) {
            return;
        }
        
        fetch('{{ route("meetings.checkConflict") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                start_time: startTime,
                end_time: endTime,
                room_id: roomId
            })
        })
        .then(response => response.json())
        .then(data => {
            const conflictAlert = document.getElementById('conflictAlert');
            const conflictDetails = document.getElementById('conflictDetails');
            const submitBtn = document.getElementById('submitBtn');
            
            if (data.has_conflict) {
                conflictAlert.classList.remove('hidden');
                conflictDetails.innerHTML = '<ul class="list-disc list-inside">' +
                    data.conflicts.map(conflict => 
                        `<li>${conflict.title} (${conflict.room_name}) - ${conflict.start_time} s/d ${conflict.end_time}</li>`
                    ).join('') + '</ul>';
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                conflictAlert.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        })
        .catch(error => {
            console.error('Error checking conflict:', error);
        });
    }
});
</script>
@endpush
@endsection