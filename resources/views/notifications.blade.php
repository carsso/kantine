@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-4 text-center mt-6">
        @if(config('services.webex.bearer_token'))
            <h1 class="text-2xl">Notifications Webex - {{ $tenant->name }}</h1>
            <p>
                Recevez le menu chaque matin dans votre espace Webex.
            </p>
            <p>
                @if($tenant->webex_bot_name)
                    Invitez le bot dans votre espace : <i class="text-gray-400">{{ $tenant->webex_bot_name }}</i>
                @else
                    <i>Le bot Webex n'est pas activé pour cette cantine actuellement.</i>
                @endif
            </p>
            <div class="m-auto lg:w-2/3">
                <div class="bg-gray-800 text-white rounded-lg shadow-sm m-8 p-8 text-left text-sm mt-6">
                    <div class="text-gray-400 text-xs mb-1">
                        {{ config('app.name') }} - {{ $tenant->name }} 9:30
                    </div>
                    <div class="border-l-4 border-[#147DE8] pl-2">
                        @include('webex.menu', ['tenant' => $tenant, 'menu' => $menu, 'date' => $date, 'hideMention' => true, 'categories' => $categories])
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
