@extends('layouts.app')


@section('navbar')
    @php
        $routes = [];
        if (request()->tenant) {
            $routes = [
                [
                    'name' => 'Menus',
                    'route' => route('menus', ['tenant' => request()->tenant->slug]),
                    'active' => request()->routeIs('menus') || request()->routeIs('menu') || request()->routeIs('tenant.home'),
                ],
                [
                    'name' => 'Notifications',
                    'route' => route('notifications', ['tenant' => request()->tenant->slug]),
                    'active' => request()->routeIs('notifications.*') || request()->routeIs('notifications'),
                ],
                [
                    'name' => 'Dashboard',
                    'route' => route('dashboard', ['tenant' => request()->tenant->slug]),
                    'active' => request()->routeIs('dashboard.*') || request()->routeIs('dashboard'),
                    'target' => '_blank',
                ],
                [
                    'name' => 'API',
                    'route' => route('api.home', ['tenant' => request()->tenant->slug]),
                    'active' => request()->routeIs('api.*') || request()->routeIs('api'),
                    'target' => '_blank',
                ],
            ];
        } else {
            $tenants = \App\Models\Tenant::where('is_active', true)->get();
            foreach($tenants as $tenant) {
                $routes[] = [
                    'name' => $tenant->name,
                    'route' => route('tenant.home', ['tenant' => $tenant->slug]),
                ];
            }
        } 

        $leftRoutes = [
            [
                'name' => 'Connexion',
                'route' => route('login'),
                'active' => request()->routeIs('login.*') || request()->routeIs('login'),
            ],
        ];
        if(auth()->check()) {
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
                    'name' => '🔐 Administration',
                    'route' => route('admin'),
                    'active' => request()->routeIs('admin.*') || request()->routeIs('admin'),
                ];
            }
        }
    @endphp
    <Navbar
        app-name="{{ config('app.name') }}{{ request()->tenant ? ' - ' . request()->tenant->name : '' }}"
        :is-dev="{{ strtoupper(config('app.env')) != 'PRODUCTION' ? 'true' : 'false' }}"
        home-route="{{ route('home') }}"
        login-route="{{ route('login') }}"
        account-route="{{ route('account') }}"
        :routes='@json($routes)'
        :routes-left='@json($leftRoutes)'
        :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    </Navbar>
@endsection
