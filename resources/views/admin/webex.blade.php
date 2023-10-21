@extends('layouts.app-with-navbar')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl text-center pt-2">Admin: Webex Rooms</h1>
    @foreach($rooms as $room)
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 mt-6">
            <h2 title="{{ $room['id'] }}" class="text-2xl mb-1">
                <span class="text-gray-400">[{{ ucfirst($room['type']) }}]</span>
                {{ $room['title'] }}
            </h2>
            <p class="mt-3">
                <span>Membres :</span><br />
                <ul class="ml-3 list-disc list-inside">
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
@endsection
