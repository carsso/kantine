@extends('layouts.app')


@section('navbar')
    @php
        $routes = [
            [
                'name' => 'Menus',
                'route' => route('menu.week'),
                'active' => request()->routeIs('menu.week') || request()->routeIs('menu.day') || request()->routeIs('home'),
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
                'target' => '_blank',
            ],
            [
                'name' => 'Envoyer un menu',
                'route' => route('files'),
                'active' => request()->routeIs('file.*') || request()->routeIs('file') || request()->routeIs('files'),
            ],
        ];
        $leftRoutes = [
            [
                'name' => 'Connexion',
                'route' => route('login'),
                'active' => request()->routeIs('login.*') || request()->routeIs('login'),
            ],
        ];
        if(auth()->check()) {
            $routes[] = [
                'name' => 'Dashboard',
                'route' => route('dashboard'),
                'active' => request()->routeIs('dashboard.*') || request()->routeIs('dashboard'),
                'target' => '_blank',
            ];
            $leftRoutes = [
                [
                    'name' => 'Compte',
                    'route' => route('account'),
                    'account' => auth()->user(),
                    'active' => request()->routeIs('account.*') || request()->routeIs('account')
                ]
            ];
            if(auth()->user()->hasRole('Super Admin')) {
                $routes[] = [
                    'name' => 'Administration',
                    'route' => route('admin'),
                    'active' => request()->routeIs('admin.*') || request()->routeIs('admin'),
                ];
            }
        }
    @endphp
    <Navbar
        app-name="{{ config('app.name') }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        home-route="{{ route('home') }}"
        login-route="{{ route('login') }}"
        account-route="{{ route('account') }}"
        :routes='@json($routes)'
        :routes-left='@json($leftRoutes)'
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </Navbar>
@endsection
