@extends('layouts.app')


@section('navbar')
    @php
        $routes = [
            [
                'name' => 'Menus',
                'route' => route('home'),
                'active' => request()->routeIs('menu.*') || request()->routeIs('menu') || request()->routeIs('home'),
            ],
            [
                'name' => 'Notifications',
                'route' => route('notifications'),
                'active' => request()->routeIs('notifications.*') || request()->routeIs('notifications'),
            ],
            [
                'name' => 'API',
                'route' => route('api.home'),
                'active' => request()->routeIs('api.*') || request()->routeIs('api'),
            ],
            [
                'name' => 'Envoyer un menu',
                'route' => route('files'),
                'active' => request()->routeIs('file.*') || request()->routeIs('file') || request()->routeIs('files'),
            ],
        ];
        $publicRoutes = [
            [
                'name' => 'Connexion',
                'route' => route('login'),
                'active' => request()->routeIs('login.*') || request()->routeIs('login'),
            ],
        ];
        $authRoutes = [
            [
                'name' => 'Compte',
                'route' => route('account'),
                'account' => auth()->check() ? auth()->user() : null,
                'active' => request()->routeIs('account.*') || request()->routeIs('account')
            ],
        ];
    @endphp
    <Navbar
        app-name="{{ config('app.name') }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        home-route="{{ route('home') }}"
        login-route="{{ route('login') }}"
        account-route="{{ route('account') }}"
        :routes='@json($routes)'
        :routes-left='@json(auth()->check() ? $authRoutes : $publicRoutes)'
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </Navbar>
@endsection
