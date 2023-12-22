
<div class="bg-white dark:bg-gray-700 rounded-lg shadow px-4 py-5 text-center mt-6 border-t-4 {{ $menu->is_fries_day ? 'border-red-500' : ($menu->event_name ? 'border-green-500' : 'border-blue-500') }}">
    <h1 class="text-2xl xl:hidden 2xl:block mb-3">{{ $menu->date_carbon->translatedFormat('l d F Y') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-3">{{ $menu->date_carbon->translatedFormat('l d M Y') }}</h1>
    @if($menu->event_name)
        <div class="mt-2 font-semibold">🎉 Événement {{ $menu->event_name }} 🎉</div>
    @endif
    @if($menu->is_fries_day)
        <p class="mt-2 font-semibold">🍟 Jour des Frites 🍟</p>
    @endif
    <div class="mt-2">
        <div class="font-semibold">🥗 Entrées :</div>
        @if(!$menu->starters)
            <div class="text-gray-500 leading-snug">Pas d'entrées</div>
        @endif
        @foreach($menu->starters_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->starters_usual as $dish)
            <div class="text-gray-500 text-xs leading-normal">{{ $dish }}</div>
        @endforeach
    </div>
    <div class="mt-2">
        <div class="font-semibold">🍗 Plats :</div>
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
    </div>
    <div class="mt-2">
        <div class="font-semibold">🥬 Accompagnements :</div>
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
    </div>
    <div class="mt-2">
        <div class="font-semibold">🧀 Fromages / Laitages :</div>
        @if(!$menu->cheeses)
            <div class="text-gray-500 leading-snug">Pas de fromages / laitages</div>
        @endif
        @foreach($menu->cheeses_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->cheeses_usual as $dish)
            <div class="text-gray-500 text-xs leading-normal">{{ $dish }}</div>
        @endforeach
    </div>
    <div class="mt-2">
        <div class="font-semibold">🍨 Desserts :</div>
        @if(!$menu->desserts)
            <div class="text-gray-500 leading-snug">Pas de desserts</div>
        @endif
        @foreach($menu->desserts_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @foreach($menu->desserts_usual as $dish)
            <div class="text-gray-500 text-xs leading-normal">{{ $dish }}</div>
        @endforeach
    </div>
    @auth
        <div class="mt-2">
            <small class="text-gray-500">
                Généré le {{ $menu->updated_at->translatedFormat('d F Y à H:i') }}<br />
                @if($menu->file)
                    <a href="{{ route('file', $menu->file->hash) }}" class="hover:text-indigo-500">
                        Source :
                        {{ $menu->file->name }} du {{ $menu->file->datetime_carbon->translatedFormat('d F Y') }}
                    </a>
                @endif
            </small>
        </div>
    @endauth
</div>