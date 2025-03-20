@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl text-center pt-2">Gestion du BOT Webex - {{ $tenant->name }}</h1>

    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-6 mt-6">
        <p class="mb-4">
            Le BOT Webex de cette cantine est <i>{{ $tenant->webex_bot_name }}</i>.<br />
            Sur cette page, vous retrouverez les derniers messages envoyés par le BOT Webex sur les différentes canaux dans lesquels il a été invité.<br />
            Le menu est envoyé automatiquement chaque matin à 10:30.<br />
            Vous pouvez envoyer une mise à jour du menu sur Webex immédiatement en cliquant sur le bouton ci-dessous.
        </p>
        <form action="{{ route('admin.webex.notify', ['tenant' => $tenant->slug]) }}" method="POST">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-gray-500"
                    onclick="return confirm('Êtes-vous sûr de vouloir envoyer/mettre à jour le menu du jour sur Webex ?');">
                <i class="fa-regular fa-paper-plane mr-2"></i>
                Envoyer/mettre à jour le menu sur Webex
            </button>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        @foreach($rooms as $room)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-6 mt-6">
                <h2 class="text-xl mb-1" title="{{ $room['id'] }}">
                    {{ $room['title'] }}
                </h2>

                <div>
                    <div class="bg-gray-800 text-white rounded-lg shadow-sm m-8 p-8 text-left text-sm mt-6">
                        @foreach($room['messages'] as $message)
                            @if($message['personEmail'] == $tenant->webex_bot_name)
                                <div class="text-gray-400 text-xs mb-1 pt-2">
                                    {{ config('app.name') }} - {{ $tenant->name }}
                                    {{ \Carbon\Carbon::parse($message['created'], 'UTC')->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}
                                </div>
                                <div class="border-l-4 border-[#147DE8] pl-2 pb-2">
                                    <div class="text-sm">
                                        {!! $message['html'] !!}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <p class="mt-3">
                    <span>{{ count($room['memberships']) }} membres :</span><br />
                    <ul class="ml-3 list-disc list-inside text-sm">
                        @php $i = 0; $max = 20; @endphp
                        @foreach($room['memberships'] as $membership)
                            @php $i++; @endphp
                            @if($i < $max || $membership['isModerator'] || $membership['personId'] == $room['creatorId'] || str_contains($membership['personEmail'], '@webex.bot'))
                                <li>
                                    <span title="{{ $membership['personId'] }}">
                                        {{ $membership['personDisplayName'] }}
                                        <span class="text-gray-400">
                                            ({{ $membership['personEmail'] }})
                                        </span>
                                    </span>
                                    @if($membership['isModerator'])
                                        <i>(modérateur)</i>
                                    @endif
                                    @if($membership['personId'] == $room['creatorId'])
                                        <i>(créateur)</i>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                        @if($i >= $max)
                            <li class="text-gray-400">... Liste tronquée ...</li>
                        @endif
                    </ul>
                </p>
            </div>
        @endforeach
    </div>
</div>
@endsection
