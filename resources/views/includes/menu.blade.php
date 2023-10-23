
<div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6 border-t-4 {{ $menu->is_fries_day ? 'border-red-500' : ($menu->event_name ? 'border-green-500' : 'border-blue-500') }}">
    <h1 class="text-2xl xl:hidden 2xl:block">{{ $menu->date_carbon->translatedFormat('l d F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden">{{ $menu->date_carbon->translatedFormat('l d M') }}</h1>
    @if($menu->event_name)
        <p class="mt-2">🎉 Événement {{ $menu->event_name }} 🎉</p>
    @endif
    @if($menu->is_fries_day)
        <p class="mt-2">🍟 Jour des Frites 🍟</p>
    @endif
    <p class="mt-2">
        🥗 Entrées : <br />
        @if(!$menu->starters)
            <div class="text-gray-500 leading-snug">Pas d'entrées</div>
        @endif
        @foreach($menu->starters_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->starters_usual as $dish)
            <div class="text-gray-500 text-xs leading-snug">{{ $dish }}</div>
        @endforeach
    </p>
    <p class="mt-2">
        🍗 Plats : <br />
        @if(!$menu->mains)
            <div class="text-gray-500 leading-snug">Pas de plats</div>
        @endif
        @foreach($menu->mains as $idx => $dish)
            <div class="leading-snug">
                {{ $dish }}
                @if($specialName = $menu->getMainSpecialName($idx))
                    <i class="text-gray-500 text-xs">({{ $specialName }})</i>
                @endif
            </div>
        @endforeach
    </p>
    <p class="mt-2">
        🥬 Accompagnements : <br />
        @if(!$menu->sides)
            <div class="text-gray-500 leading-snug">Pas d'accompagnements</div>
        @endif
        @foreach($menu->sides as $dish)
            @if($dish == 'Frites')
                <div class="leading-snug">🍟 {{ $dish }} 🍟</div>
            @else
                <div class="leading-snug">{{ $dish }}</div>
            @endif
        @endforeach
    </p>
    <p class="mt-2">
        🧀 Fromages / Laitages : <br />
        @if(!$menu->cheeses)
            <div class="text-gray-500 leading-snug">Pas de fromages / laitages</div>
        @endif
        @foreach($menu->cheeses_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->cheeses_usual as $dish)
            <div class="text-gray-500 text-xs leading-snug">{{ $dish }}</div>
        @endforeach
    </p>
    <p class="mt-2">
        🍨 Desserts : <br />
        @if(!$menu->desserts)
            <div class="text-gray-500 leading-snug">Pas de desserts</div>
        @endif
        @foreach($menu->desserts_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->desserts_usual as $dish)
            <div class="text-gray-500 text-xs leading-snug">{{ $dish }}</div>
        @endforeach
    </p>
    @auth
        <p class="mt-2">
            <small class="text-gray-500">
                Généré le {{ $menu->updated_at->translatedFormat('d F Y à H:i') }}<br />
                <a href="{{ route('file', $menu->file->hash) }}" class="hover:text-indigo-500">
                    Source :
                    {{ $menu->file->name }} du {{ $menu->file->datetime_carbon->translatedFormat('d F Y') }}
                </a>
            </small>
        </p>
    @endauth
</div>