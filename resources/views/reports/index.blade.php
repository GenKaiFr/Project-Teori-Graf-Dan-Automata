@extends('layouts.app')

@section('title', 'Export Reports')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Export Reports</h1>
    <p class="text-gray-600 dark:text-gray-400">Generate laporan rapat dalam format PDF atau Excel</p>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <form id="reportForm" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ruangan (Opsional)</label>
                <select id="room_id" name="room_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Ruangan</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status (Opsional)</label>
                <select id="status" name="status" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="SCHEDULED">Scheduled</option>
                    <option value="ONGOING">Ongoing</option>
                    <option value="COMPLETED">Completed</option>
                    <option value="CANCELLED">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="flex justify-center space-x-4">
            <button type="button" onclick="exportReport('pdf')" 
                    class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </button>
            <button type="button" onclick="exportReport('excel')" 
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function exportReport(format) {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    
    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Tanggal mulai dan selesai harus diisi!'
        });
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!'
        });
        return;
    }
    
    // Create download link
    const params = new URLSearchParams(formData);
    const url = format === 'pdf' ? 
        '{{ route("reports.exportPdf") }}?' + params.toString() :
        '{{ route("reports.exportExcel") }}?' + params.toString();
    
    // Show loading
    Swal.fire({
        title: 'Generating Report...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Close loading after a short delay
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Report berhasil di-generate!',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}

// Set default dates (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('end_date').value = today.toISOString().split('T')[0];
    document.getElementById('start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
});
</script>
@endpush
@endsection