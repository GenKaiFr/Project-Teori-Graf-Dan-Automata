@extends('layouts.app')

@section('title', 'Kalender Rapat')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Kalender Rapat</h1>
        <div class="flex space-x-2">
            <a href="{{ route('meetings.index') }}" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                <i class="fas fa-list mr-2"></i>Tampilan Daftar
            </a>
            @if(auth()->user()->canManageMeetings())
                <a href="{{ route('meetings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Rapat
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Calendar Container -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <div id="calendar" class="min-h-96"></div>
</div>

<!-- Legend -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Keterangan Status</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="flex items-center">
            <div class="w-4 h-4 bg-yellow-400 rounded mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Draft</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Scheduled</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Ongoing</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-gray-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Completed</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700 dark:text-gray-300">Cancelled</span>
        </div>
    </div>
</div>

<!-- Meeting Detail Modal -->
<div id="meetingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-xl font-semibold text-gray-800 dark:text-gray-200">Detail Rapat</h3>
                    <button onclick="closeModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="modalContent" class="text-gray-700 dark:text-gray-300">
                    <!-- Content will be loaded here -->
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="closeModal()" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                        Tutup
                    </button>
                    @if(auth()->user()->canManageMeetings())
                        <a id="editMeetingBtn" href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* FullCalendar Dark Mode Styles */
.dark .fc {
    background-color: #1f2937;
    color: #f9fafb;
}

.dark .fc-theme-standard .fc-scrollgrid {
    border-color: #374151;
}

.dark .fc-theme-standard td, 
.dark .fc-theme-standard th {
    border-color: #374151;
}

.dark .fc-button-primary {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #f9fafb !important;
}

.dark .fc-button-primary:hover {
    background-color: #4b5563 !important;
    border-color: #6b7280 !important;
}

.dark .fc-button-primary:focus {
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
}

.dark .fc-button-active {
    background-color: #6b7280 !important;
    border-color: #9ca3af !important;
}

.dark .fc-col-header-cell {
    background-color: #374151;
}

.dark .fc-daygrid-day {
    background-color: #1f2937;
}

.dark .fc-daygrid-day:hover {
    background-color: #374151;
}

.dark .fc-day-today {
    background-color: rgba(59, 130, 246, 0.1) !important;
}

.dark .fc-event {
    border-color: transparent;
}

.dark .fc-popover {
    background-color: #374151;
    border-color: #4b5563;
    color: #f9fafb;
}

.dark .fc-popover-header {
    background-color: #4b5563;
}
</style>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    function getCalendarTheme() {
        return document.documentElement.classList.contains('dark') ? {
            backgroundColor: '#1f2937',
            textColor: '#f9fafb',
            borderColor: '#374151'
        } : {
            backgroundColor: '#ffffff',
            textColor: '#111827',
            borderColor: '#e5e7eb'
        };
    }
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari'
        },
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('{{ route("calendar.data") }}')
                .then(response => response.json())
                .then(meetings => {
                    const events = meetings.map(meeting => ({
                        id: meeting.id,
                        title: meeting.title,
                        start: meeting.start_time,
                        end: meeting.end_time,
                        backgroundColor: getStatusColor(meeting.status),
                        borderColor: getStatusColor(meeting.status),
                        textColor: '#ffffff',
                        extendedProps: {
                            meeting: meeting
                        }
                    }));
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error loading calendar data:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showMeetingModal(info.event.extendedProps.meeting);
        },
        eventMouseEnter: function(info) {
            const meeting = info.event.extendedProps.meeting;
            info.el.title = `${meeting.title}\n${meeting.room ? meeting.room.name : 'N/A'}\nStatus: ${meeting.status}`;
        },
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        moreLinkText: function(num) {
            return '+' + num + ' lainnya';
        },
        // Custom styling for dark mode
        customButtons: {},
        viewDidMount: function() {
            updateCalendarTheme();
        }
    });
    
    function updateCalendarTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        const calendarEl = document.querySelector('#calendar');
        
        if (isDark) {
            calendarEl.style.setProperty('--fc-border-color', '#374151');
            calendarEl.style.setProperty('--fc-button-bg-color', '#374151');
            calendarEl.style.setProperty('--fc-button-border-color', '#4b5563');
            calendarEl.style.setProperty('--fc-button-hover-bg-color', '#4b5563');
            calendarEl.style.setProperty('--fc-button-active-bg-color', '#6b7280');
            calendarEl.style.setProperty('--fc-today-bg-color', 'rgba(59, 130, 246, 0.1)');
        } else {
            calendarEl.style.removeProperty('--fc-border-color');
            calendarEl.style.removeProperty('--fc-button-bg-color');
            calendarEl.style.removeProperty('--fc-button-border-color');
            calendarEl.style.removeProperty('--fc-button-hover-bg-color');
            calendarEl.style.removeProperty('--fc-button-active-bg-color');
            calendarEl.style.removeProperty('--fc-today-bg-color');
        }
    }
    
    calendar.render();
    
    // Update calendar theme when dark mode changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                updateCalendarTheme();
            }
        });
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Initial theme setup
    updateCalendarTheme();
});

function getStatusColor(status) {
    switch(status) {
        case 'SCHEDULED': return '#10b981'; // green-500
        case 'DRAFT': return '#f59e0b'; // yellow-500
        case 'ONGOING': return '#3b82f6'; // blue-500
        case 'COMPLETED': return '#6b7280'; // gray-500
        case 'CANCELLED': return '#ef4444'; // red-500
        default: return '#6b7280';
    }
}

function showMeetingModal(meeting) {
    const modal = document.getElementById('meetingModal');
    const title = document.getElementById('modalTitle');
    const content = document.getElementById('modalContent');
    const editBtn = document.getElementById('editMeetingBtn');
    
    title.textContent = meeting.title;
    if (editBtn) editBtn.href = `/meetings/${meeting.id}/edit`;
    
    const startTime = new Date(meeting.start_time);
    const endTime = new Date(meeting.end_time);
    
    const statusColors = {
        'SCHEDULED': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'DRAFT': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'ONGOING': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'COMPLETED': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'CANCELLED': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    };
    
    content.innerHTML = `
        <div class="space-y-3">
            <div>
                <span class="inline-block px-2 py-1 text-xs rounded-full ${statusColors[meeting.status] || statusColors['COMPLETED']}">
                    ${meeting.status}
                </span>
            </div>
            ${meeting.description ? `<div><strong>Deskripsi:</strong> ${meeting.description}</div>` : ''}
            <div><strong>Waktu:</strong> ${startTime.toLocaleString('id-ID')} - ${endTime.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}</div>
            <div><strong>Ruangan:</strong> ${meeting.room ? meeting.room.name : 'N/A'}</div>
            <div><strong>Peserta:</strong> ${meeting.participants ? meeting.participants.length : 0} orang</div>
            ${meeting.participants && meeting.participants.length > 0 ? 
                `<div><strong>Daftar Peserta:</strong><br>${meeting.participants.map(p => p.name).join(', ')}</div>` : ''}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('meetingModal').classList.add('hidden');
}
</script>
@endpush
@endsection