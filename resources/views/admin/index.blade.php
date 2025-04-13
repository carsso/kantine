@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 mt-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h1 class="text-center text-2xl leading-9 tracking-tight">Administration</h1>
        </div>
        <div class="mt-8 text-center">
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-1/4 text-center mb-6 md:mb-0 md:pr-4">
                    <img class="inline-block h-15 w-15 rounded-full" src="{{ auth()->user()->gravatar_url }}" alt="">
                    <div class="mt-2">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs font-medium text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.jobs') }}" 
                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fa-solid fa-gears mr-2"></i>
                            Monitoring des jobs
                        </a>
                    </div>
                </div>
                <div class="w-full md:w-3/4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    @foreach($tenants as $tenant)
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">{{ $tenant->name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $tenant->description }}</p>
                            
                            <div class="flex flex-col space-y-4">
                                @can('tenant-admin-' . $tenant->slug)
                                    <a href="{{ route('admin.menu', ['tenantSlug' => $tenant->slug]) }}" 
                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                        <i class="fa-solid fa-utensils mr-2"></i>
                                        Modification du menu
                                    </a>
                                
                                    @if($tenant->webex_bearer_token)
                                        <a href="{{ route('admin.webex', ['tenantSlug' => $tenant->slug]) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            <i class="fa-solid fa-robot mr-2"></i>
                                            Gestion du BOT Webex
                                        </a>
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">
                                            <i>Pour envoyer une mise à jour forcée du menu sur Webex, rendez-vous sur la page de gestion du BOT Webex.</i>
                                        </p>
                                    @else
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                                            <i class="fa-solid fa-robot mr-2"></i>
                                            <i>Pas de BOT Webex pour cette cantine</i>
                                        </p>
                                    @endif
                                @else
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                                        <i class="fa-solid fa-lock mr-2"></i>
                                        <i>Vous n'avez pas les droits pour modifier le menu de cette cantine</i>
                                    </p>
                                @endcan
                            </div>

                            @if(!$tenant->is_active)
                                <p class="text-red-700 dark:text-red-800 mb-4">
                                    <i class="fa-solid fa-ban mr-2"></i>
                                    <i>Cette cantine est actuellement désactivée et n'est pas visible publiquement.</i>
                                </p>
                            @endif

                            @php
                                $tenantAdmins = \App\Models\User::permission('tenant-admin-' . $tenant->slug)->get();
                            @endphp
                            @if($tenantAdmins->count() > 0)
                                <div class="mt-8 mb-4 text-gray-600 dark:text-gray-400">
                                    <p class="font-medium mb-2">Administrateurs :</p>
                                    <ul class="text-sm">
                                        @foreach($tenantAdmins as $admin)
                                            <li class="flex items-center">
                                                <i class="fa-solid fa-user-shield mr-2"></i>
                                                {{ $admin->name }} ({{ $admin->email }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="mt-8 mb-4 text-gray-600 dark:text-gray-400">
                                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                                    Aucun administrateur assigné
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
