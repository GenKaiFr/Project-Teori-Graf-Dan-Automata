@extends('layouts.app')

@section('title', 'Meeting Templates')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Meeting Templates</h1>
            <p class="text-gray-600 dark:text-gray-400">Kelola template untuk meeting yang sering digunakan</p>
        </div>
        @if(auth()->user()->canManageMeetings())
            <a href="{{ route('templates.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Template
            </a>
        @endif
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    @if($templates->isEmpty())
        <div class="p-8 text-center">
            <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">Belum ada template yang dibuat.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Peserta Default</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($templates as $template)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</div>
                                @if($template->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($template->description, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $template->duration_minutes }} menit
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $template->default_participants ? count($template->default_participants) : 0 }} peserta
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $template->creator->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if(auth()->user()->canManageMeetings())
                                    <a href="{{ route('templates.edit', $template) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('templates.destroy', $template) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus template ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection