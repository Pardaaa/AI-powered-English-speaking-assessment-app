<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            
            <!-- Custom Header (Tanpa Navbar Bawaan) -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('My Profile') }}
                    </h2>
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center gap-1 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Dashboard
                    </a>
                </div>
            </header>

            <main class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    <!-- 1. Profile Overview / Header Card -->
                    <div class="p-6 sm:p-10 bg-indigo-700 shadow-md sm:rounded-lg text-white flex items-center justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-3xl font-bold">Welcome back, {{ Auth::user()->name }}!</h3>
                            <p class="text-indigo-200 mt-2 max-w-xl">
                                Manage your account settings, security preferences, and personal information all in one place.
                            </p>
                        </div>
                        <!-- Avatar / Inisial Sederhana -->
                        <div class="hidden sm:flex relative z-10">
                            <div class="h-20 w-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold border-2 border-white/50 text-white shadow-lg">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                        
                        <!-- Dekorasi Background -->
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-indigo-500/30 rounded-full blur-xl"></div>
                    </div>

                    <!-- 2. Grid Layout untuk Form -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Kolom Kiri: Personal Info -->
                        <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg h-fit">
                            <div class="max-w-xl">
                                <div class="mb-4 pb-4 border-b border-gray-100">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Account Details
                                    </h3>
                                </div>
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>

                        <!-- Kolom Kanan: Security -->
                        <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg h-fit">
                            <div class="max-w-xl">
                                <div class="mb-4 pb-4 border-b border-gray-100">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Security
                                    </h3>
                                </div>
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>

                    <!-- 3. Danger Zone (Full Width) -->
                    <div class="bg-white p-4 sm:p-8 shadow sm:rounded-lg border-l-4 border-red-500 mt-8">
                        <div class="max-w-xl">
                            <div class="mb-4">
                                <h3 class="text-lg font-bold text-red-600 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    {{ __('Danger Zone') }}
                                </h3>
                                <p class="text-sm text-gray-500">Irreversible actions related to your account.</p>
                            </div>
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>