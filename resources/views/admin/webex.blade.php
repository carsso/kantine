@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl text-center pt-2">Administration du BOT Webex</h1>
    <form id="notify-form" class="text-center mt-4 mb-4" action="{{ route('admin.webex.notify') }}" method="POST">
        @csrf
        <button
            type="submit"
            class="rounded-md bg-red-600 dark:bg-red-800 py-1 px-2 text-xs leading-1 text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
            onclick="return confirm('Êtes-vous sûr de vouloir envoyer/mettre à jour le menu du jour sur Webex ?');">
            <i class="fa-regular fa-paper-plane"></i> Envoyer/mettre à jour le menu sur Webex
        </button>
    </form>
    <div class="grid grid-cols-2 gap-4 mb-6">
        @foreach($rooms as $room)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 mt-6">
                <h2 class="text-xl mb-1" title="{{ $room['id'] }}">
                    {{ $room['title'] }}
                </h2>
                <p class="mt-3">
                    <span>Messages :</span><br />
                    @foreach($room['messages'] as $message)
                        @if($message['personEmail'] == config('services.webex.bot_name'))
                            <div class="ml-3 rounded-lg p-1 border border-gray-300 mt-1" title="{{ $message['id'] }}">
                                <div class="text-sm">
                                    {{ $message['personEmail'] }} 
                                    <span class="text-xs text-gray-400">
                                        {{ $message['created'] }} 
                                    </span>
                                </div>
                                <div class="text-sm text-gray-400">
                                    {!! $message['text'] !!}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </p>
                <p class="mt-3">
                    <span>Membres :</span><br />
                    <ul class="ml-3 list-disc list-inside text-sm">
                        @foreach($room['memberships'] as $membership)
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
                        @endforeach
                    </ul>
                </p>
            </div>
        @endforeach
    </div>
</div>
@endsection
