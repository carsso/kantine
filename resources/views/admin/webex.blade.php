@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl text-center pt-2">Administration du BOT Webex</h1>
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
