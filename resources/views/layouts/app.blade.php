<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sistem Penjadwalan Rapat Cerdas')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/keyboard-shortcuts.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .dark-mode-toggle {
            cursor: pointer !important;
            user-select: none;
        }
        .dark-mode-toggle:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <nav class="bg-blue-600 dark:bg-gray-800 text-white shadow-lg transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold">
                        <i class="fas fa-calendar-alt"></i> Meeting Scheduler
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Dashboard</a>
                    <a href="{{ route('meetings.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Meetings</a>
                    <a href="{{ route('calendar') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Calendar</a>
                    <a href="{{ route('meetings.graph') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Graf Konflik</a>
                    @if(auth()->user()->canManageMeetings())
                        <a href="{{ route('templates.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Templates</a>
                        <a href="{{ route('statistics.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Statistics</a>
                        <a href="{{ route('analytics.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Analytics</a>
                        <a href="{{ route('reports.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Reports</a>
                        <a href="{{ route('meetings.bulk') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Bulk Ops</a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('rooms.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Rooms</a>
                        <a href="{{ route('participants.index') }}" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors">Participants</a>
                    @endif
                    
                    <!-- Dark Mode Toggle -->
                    <button type="button" id="darkModeToggle" class="dark-mode-toggle hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors" title="Toggle Dark Mode (Ctrl+Shift+D)">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                    
                    <!-- Keyboard Shortcuts Help -->
                    <button type="button" onclick="document.getElementById('shortcutsModal').classList.remove('hidden')" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded transition-colors" title="Keyboard Shortcuts (Ctrl+Shift+/)">
                        <i class="fas fa-keyboard"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 ml-4">
                        <span class="text-sm">{{ auth()->user()->name }}</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if(auth()->user()->isAdmin()) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif(auth()->user()->isManager()) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:bg-blue-700 dark:hover:bg-gray-700 px-3 py-2 rounded text-sm transition-colors">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-4 sm:py-8">
        @if(session('success'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Setup CSRF token for AJAX
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Setup AJAX headers when jQuery is available
        window.addEventListener('load', function() {
            if (typeof $ !== 'undefined' && $.ajaxSetup) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            }
            
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.onclick = function() {
                    mobileMenu.classList.toggle('hidden');
                };
            }
        });

        // Dark Mode Toggle
        function initDarkMode() {
            const html = document.documentElement;
            const savedTheme = localStorage.getItem('theme');
            
            // Apply saved theme immediately
            if (savedTheme === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        }
        
        // Initialize immediately
        initDarkMode();
        
        // Setup toggle when DOM is ready
        window.addEventListener('load', function() {
            const toggle = document.getElementById('darkModeToggle');
            const icon = document.getElementById('darkModeIcon');
            const html = document.documentElement;
            
            if (toggle && icon) {
                // Set initial icon
                icon.className = html.classList.contains('dark') ? 'fas fa-sun' : 'fas fa-moon';
                
                // Add click handler
                toggle.onclick = function() {
                    if (html.classList.contains('dark')) {
                        html.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                        icon.className = 'fas fa-moon';
                    } else {
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                        icon.className = 'fas fa-sun';
                    }
                };
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>