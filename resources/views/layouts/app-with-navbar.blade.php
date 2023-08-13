@extends('layouts.app')


@section('navbar')
    @php
        $publicRoutes = [
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
                'name' => 'Uploader un menu',
                'route' => route('files'),
                'active' => request()->routeIs('file.*') || request()->routeIs('file') || request()->routeIs('files'),
            ],
        ];
        $authRoutes = [
            [
                'name' => 'Files',
                'route' => route('files'),
                'active' => request()->routeIs('file.*') || request()->routeIs('file') || request()->routeIs('files'),
            ],
        ];
    @endphp
    <Navbar
        app-name="{{ config('app.name') }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        home-route="{{ route('home') }}"
        logout-route="{{ route('home') }}"
        :routes='@json(auth()->check() ? $authRoutes : $publicRoutes)'
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </Navbar>
@endsection
