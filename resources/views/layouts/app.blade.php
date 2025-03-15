<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-bs-color-scheme="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}"
    class="{{ Cookie::get('darkmode') ? 'dark' : 'light' }}">
    

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ Vite::asset('resources/images/favicon.png') }}"/>
    <title>
        @if (strtoupper(config('app.env')) != 'PRODUCTION')
            [DEV]
        @endif
        {{ config('app.name') }}
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    <script>
        window.VITE_REVERB_APP_KEY = "{{ env('VITE_REVERB_APP_KEY') }}";
        window.VITE_REVERB_HOST = "{{ env('VITE_REVERB_HOST') }}";
        window.VITE_REVERB_PORT = "{{ env('VITE_REVERB_PORT') }}";
        window.VITE_REVERB_SCHEME = "{{ env('VITE_REVERB_SCHEME') }}";
    </script>
    @vite(['resources/css/base.css', 'resources/scss/app.scss', 'resources/js/app.js'])

    @if (strtoupper(config('app.env')) != 'PRODUCTION')
        <style>
            body {
                background-image: url('{{ Vite::asset('resources/images/bg_dev.png') }}');
            }
        </style>
    @endif
</head>

@section('body')
    <body id="app" class="bg-gray-100 dark:bg-gray-900 dark:text-gray-300">
        <div>
            @yield('navbar')
            <main class="py-4">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">@flash</div>
                @yield('content')

                <div class="text-center text-gray-500 mt-6">
                    <small>
                        {{ config('app.name') }}
                        -
                        <a href="{{ route('legal') }}" class="underline hover:text-indigo-600">Mentions légales</a>
                        -
                        <a href="https://github.com/carsso/kantine" target="_blank" class="underline hover:text-indigo-600">
                            <i class="fab fa-github"></i>
                            Code source disponible sur GitHub
                        </a>
                    </small>
                </div>
            </main>
        </div>
        <echo-state></echo-state>
        <span class="text-[#A6D64D]"></span>
        <span class="text-[#4AB0F5]"></span>
        <span class="text-[#ED733D]"></span>
        <span class="text-[#FFD124]"></span>
        <span class="text-[#73E3FF]"></span>
        <span class="text-[#147DE8]"></span>
    </body>
@endsection
@yield('body')

</html>
