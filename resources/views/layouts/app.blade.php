<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-color-scheme="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}"
    class="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}">
    

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @if (strtoupper(config('app.env')) != 'PRODUCTION')
        <style>
            body {
                background-image: url('{{ Vite::asset('resources/images/bg_dev.png') }}');
            }
        </style>
    @endif
</head>

<body class="bg-gray-100 dark:bg-gray-900 dark:text-gray-300">
    <div id="app">
        @yield('navbar')
        <main class="py-4">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">@flash</div>
            @yield('content')
            <div class="text-center text-gray-500 mt-6">
                <small>
                    {{ config('app.name') }}
                    -
                    <a href="https://github.com/carsso/kantine" target="_blank"
                        class="underline hover:text-indigo-600"><i class="fab fa-github"></i> Source code available on
                        GitHub</a>
                </small>
            </div>
        </main>
    </div>
</body>

</html>
