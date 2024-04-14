@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow px-4 mx-6 py-12">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h1 class="text-center text-2xl leading-9 tracking-tight">{{ __('Verify Your Email Address') }}</h1>
        </div>
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form class="space-y-6" action="{{ route('verification.resend') }}" method="POST">
                @csrf
                @if (session('resent'))
                    <div class="rounded-md bg-red-50 dark:bg-green-800 text-xs font-medium text-green-800 dark:text-green-50 p-4 mb-2">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                <p>
                    {{ __('Before proceeding, please check your email for a verification link.') }}
                </p>

                <p>
                    {{ __('If you did not receive the email') }},
                </p>

                <div class="mb-3">
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ __('click here to request another') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
