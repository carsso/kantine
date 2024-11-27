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
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @if (strtoupper(config('app.env')) != 'PRODUCTION')
        <style>
            body {
                background-image: url('{{ Vite::asset('resources/images/bg_dev.png') }}');
            }
        </style>
    @endif

    @if (config('sentry.dsn'))
        <script
            src="{{ config('app.sentry_cdn') }}/7.69.0/bundle.tracing.replay.min.js"
            integrity="sha384-6ZlY7nOHgnD0vXeSWEgeSHy/+WXQkLYa52vA7d20SFsyRhhCU9mGOIGSgNlbzdSS"
            crossorigin="anonymous"></script>

        <script>
            Sentry.init({
                dsn: "{{ config('sentry.dsn') }}",
                tunnel: "/sentry",
                integrations: [
                    new Sentry.BrowserTracing(),
                    new Sentry.Replay({
                        maskAllText: false,
                        maskAllInputs: false,
                        blockAllMedia: false,
                    })
                ],
                tracesSampleRate: 1.0,
                replaysSessionSampleRate: 1.0,
                replaysOnErrorSampleRate: 1.0,
            });

            @if (auth()->check())
                Sentry.setTag("user_id", "{{ Auth::user()->id }}");
                Sentry.setUser({
                    id: "{{ Auth::user()->id ?? 0 }}",
                    email: "{{ Auth::user()->email ?? '' }}",
                });
            @endif
        </script>
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
    </body>
@endsection
@yield('body')

</html>
