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
                <div class="basis-3/4 text-center mb-3">
                    <a href="{{ route('admin.menu') }}" class="p-2 m-4 border border-transparent text-sm leading-10 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Modification manuelle du menu
                    </a>
                    <a href="{{ route('admin.webex') }}" class="p-2 m-4 border border-transparent text-sm leading-10 font-medium rounded-md shadow-xs text-white bg-gray-600 hover:bg-gray-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Administration du BOT Webex
                    </a>
                    <form id="notify-form" class="text-center mt-8 mb-4" action="{{ route('admin.webex.notify') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-md bg-red-600 dark:bg-red-800 py-1 px-2 text-xs leading-1 text-white shadow-xs hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                            onclick="return confirm('Êtes-vous sûr de vouloir envoyer/mettre à jour le menu du jour sur Webex ?');">
                            <i class="fa-regular fa-paper-plane"></i> Envoyer/mettre à jour le menu sur Webex
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
