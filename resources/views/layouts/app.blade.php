<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mini CRM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Scripts -->
    <script>
        // Simple sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
        }
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });
        
        // Global phone mask function
        function applyPhoneMask(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-digits
            let formattedValue = '';
            
            if (value.length <= 2) {
                formattedValue = `(${value}`;
            } else if (value.length <= 6) {
                formattedValue = `(${value.slice(0, 2)}) ${value.slice(2)}`;
            } else if (value.length <= 10) {
                formattedValue = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`;
            } else {
                formattedValue = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7, 11)}`;
            }
            
            input.value = formattedValue;
        }
        
        // Initialize phone masks globally
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInputs = document.querySelectorAll('input[name="phone"]');
            phoneInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    applyPhoneMask(this);
                });
                
                // Apply mask to existing value if any
                if (input.value) {
                    applyPhoneMask(input);
                }
            });
        });
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
            
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 bg-indigo-600">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="ml-2 text-xl font-semibold text-white">Mini CRM</span>
                </div>
                
                <!-- Mobile close button -->
                <button onclick="toggleSidebar()" class="lg:hidden text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <div class="px-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <a href="{{ route('contacts.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('contacts.*') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Contatos
                    </a>
                    
                    <a href="{{ route('logs.index') }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('logs.*') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Logs
                    </a>
                </div>
            </nav>
        </div>

        <!-- Mobile menu button -->
        <div class="lg:hidden">
            <button onclick="toggleSidebar()" class="fixed top-4 left-4 z-50 p-2 bg-white rounded-lg shadow-lg">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Main content -->
        <div class="lg:ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-6">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- User menu -->
                        <div class="relative" id="user-menu">
                            <button onclick="toggleUserMenu()" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 hidden md:block mr-2">{{ auth()->user() ? auth()->user()->name : 'Usuário' }}</span>
                                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium">{{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'U' }}</span>
                                </div>
                            </button>
                            
                            <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                                <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Handle mobile sidebar toggle
        document.addEventListener('toggle-sidebar', function() {
            const sidebar = document.querySelector('[x-data*="open"]');
            if (sidebar) {
                const xData = sidebar.__x.$data;
                xData.open = !xData.open;
            }
        });
        
        // User menu functionality
        function toggleUserMenu() {
            const dropdown = document.getElementById('user-menu-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-menu-dropdown');
            
            if (userMenu && dropdown && !userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Close user menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('user-menu-dropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    </script>
</body>
</html> 