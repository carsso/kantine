@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 mt-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h1 class="text-center text-2xl leading-9 tracking-tight">{{ __('Reset Password') }}</h1>
        </div>
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form class="space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf
                @if (session('status'))
                    <div class="rounded-md bg-red-50 dark:bg-green-800 text-xs font-medium text-green-800 dark:text-green-50 p-4 mb-2">
                        {{ session('status') }}
                    </div>
                @endif

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="block text-sm font-medium leading-6">{{ __('Email Address') }}</label>
                    <div class="mt-2">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="block w-full rounded-md border-0 py-1.5 dark:bg-white/5 text-gray-900 dark:text-white shadow-xs ring-1 ring-inset @error('email') ring-red-700 @else ring-gray-300 dark:ring-white/10 @enderror placeholder:text-gray-400 dark:placeholder:text-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">

                        @error('email')
                            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="block text-sm font-medium leading-6">{{ __('Password') }}</label>
                    <div class="mt-2">
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="block w-full rounded-md border-0 py-1.5 dark:bg-white/5 text-gray-900 dark:text-white shadow-xs ring-1 ring-inset @error('password') ring-red-700 @else ring-gray-300 dark:ring-white/10 @enderror placeholder:text-gray-400 dark:placeholder:text-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">

                        @error('password')
                            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password-confirm" class="block text-sm font-medium leading-6">{{ __('Confirm Password') }}</label>
                    <div class="mt-2">
                        <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password" class="block w-full rounded-md border-0 py-1.5 dark:bg-white/5 text-gray-900 dark:text-white shadow-xs ring-1 ring-inset @error('password') ring-red-700 @else ring-gray-300 dark:ring-white/10 @enderror placeholder:text-gray-400 dark:placeholder:text-white focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500 sm:text-sm sm:leading-6">

                        @error('password')
                            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
