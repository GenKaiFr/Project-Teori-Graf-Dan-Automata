@extends('layouts.app')

@section('title', 'Buat Template Baru')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Buat Template Baru</h1>
    <p class="text-gray-600 dark:text-gray-400">Buat template untuk meeting yang sering digunakan</p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form method="POST" action="{{ route('templates.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Template</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durasi (menit)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" 
                       min="15" max="480" step="15"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
                @error('duration_minutes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
            <textarea id="description" name="description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Peserta Default</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3">
                @foreach($participants as $participant)
                    <label class="flex items-center">
                        <input type="checkbox" name="default_participants[]" value="{{ $participant->id }}" 
                               class="mr-2 text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $participant->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('templates.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan Template
            </button>
        </div>
    </form>
</div>
@endsection