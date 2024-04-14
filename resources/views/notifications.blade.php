@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6">
        @if(config('services.webex.bearer_token'))
            <h1 class="text-2xl">Webex</h1>
            <p>
                Recevez le menu chaque matin dans votre espace Webex.
            </p>
            <p>
                Invitez le bot dans votre espace : <i class="text-gray-400">{{ config('services.webex.bot_name') }}</i>
            </p>
        @endif
    </div>
</div>
@endsection
