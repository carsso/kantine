@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 mt-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h1 class="text-center text-2xl leading-9 tracking-tight">Administration</h1>
        </div>
        <div class="mt-8 text-center">
            <div class="flex flex-row">
                <div class="basis-1/4 text-center mb-3">
                    <img class="inline-block h-15 w-15 rounded-full" src="{{ auth()->user()->gravatar_url }}" alt="">
                    <div class="mt-2">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs font-medium text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <div class="mt-6">
                            <button type="submit" class="rounded-md bg-red-600 dark:bg-red-800 px-2 py-0.5 text-xs leading-6 text-white shadow-xs hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                {{ __('Logout') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="basis-3/4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    @foreach($tenants as $tenant)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold mb-4">{{ $tenant->name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $tenant->description }}</p>
                            
                            <div class="flex flex-col space-y-4">
                                <a href="{{ route('admin.menu', ['tenant' => $tenant->slug]) }}" 
                                   class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <i class="fa-solid fa-utensils mr-2"></i>
                                    Modification du menu
                                </a>
                                
                                @if($tenant->webex_bearer_token)
                                    <a href="{{ route('admin.webex', ['tenant' => $tenant->slug]) }}"
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
                            </div>

                            @if(!$tenant->is_active)
                                <p class="text-red-700 dark:text-red-800 mb-4">
                                    <i class="fa-solid fa-ban mr-2"></i>
                                    <i>Cette cantine est actuellement désactivée et n'est pas visible publiquement.</i>
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
