@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow px-4 mt-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="text-center text-2xl font-bold leading-9 tracking-tight">{{ __('Dashboard') }}</h2>
        </div>
        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-sm text-center">
            @if (session('status'))
                <div class="rounded-md bg-red-50 dark:bg-green-800 text-xs font-medium text-green-800 dark:text-green-50 p-4 mb-2">
                    {{ session('status') }}
                </div>
            @endif
            <div class="text-center mb-3">
                <img class="inline-block h-15 w-15 rounded-full" src="{{ auth()->user()->gravatar_url }}" alt="">
                <div class="mt-2">
                    <p class="font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs font-medium text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <p>
                {{ __('You are logged in!') }}
            </p>
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <div class="mt-6">
                    <button type="submit" class="rounded-md bg-red-600 dark:bg-red-800 px-2 py-0.5 text-xs leading-6 text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        {{ __('Logout') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
