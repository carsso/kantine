
<div class="bg-white dark:bg-gray-700 rounded-lg shadow px-4 py-5 text-center border-t-4 {{ $menu->is_fries_day ? 'border-red-500' : ($menu->event_name ? 'border-green-500' : 'border-blue-500') }}">
    <h1 class="text-2xl xl:hidden 2xl:block mb-3">{{ $menu->date_carbon->translatedFormat('l d F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-3">{{ $menu->date_carbon->translatedFormat('D d M') }}</h1>
    @if($menu->information)
        <p class="mt-2 text-sm leading-snug">ℹ️ {!! $menu->information_html !!}</p>
    @endif
    @if($menu->event_name)
        <div class="mt-2 font-semibold">🎉 Événement {{ $menu->event_name }} 🎉</div>
    @endif
    @if($menu->is_fries_day && $menu->is_burgers_day)
        <p class="mt-2 font-semibold">🍔 🍟 Jour des Burgers et des Frites 🍟 🍔</p>
    @elseif($menu->is_fries_day)
        <p class="mt-2 font-semibold">🍟 Jour des Frites 🍟</p>
    @elseif($menu->is_burgers_day)
        <p class="mt-2 font-semibold">🍔 Jour des Burgers 🍔</p>
    @endif
    @if($menu->is_antioxidants_day)
        <p class="mt-2 font-semibold">🏋️ Jour des Antioxydants 🏋️</p>
    @endif
    <div class="mt-2">
        <div class="font-semibold">🥗 Entrées :</div>
        @if(!$menu->starters)
            <div class="text-gray-500 leading-snug">Pas d'entrées</div>
        @endif
        @foreach($menu->starters_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @if(count($menu->starters_usual))
            <div class="text-gray-500 text-xs leading-normal">{{ join(', ', $menu->starters_usual) }}</div>
        @endif
    </div>
    <div class="mt-2">
        <div class="font-semibold">🍗 Plats :</div>
        @if(!$menu->mains)
            <div class="text-gray-500 leading-snug">Pas de plats</div>
        @endif
        @foreach($menu->mains as $idx => $dish)
            <div class="leading-snug">
                @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                    🍔 {{ $dish }} 🍔
                @else
                    {{ $dish }}
                @endif
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
        @if(count($menu->cheeses) == 1)
            <div class="font-semibold">🧀 {{ join(', ', $menu->cheeses) }}</div>
        @else
            <div class="font-semibold">🧀 Fromages / Laitages :</div>
            @if(!$menu->cheeses)
                <div class="text-gray-500 leading-snug">Pas de fromages / laitages</div>
            @endif
            @foreach($menu->cheeses_without_usual as $dish)
                <div class="leading-snug">{{ $dish }}</div>
            @endforeach
            @if(count($menu->cheeses_usual))
                <div class="text-gray-500 text-xs leading-normal">{{ join(', ', $menu->cheeses_usual) }}</div>
            @endif
        @endif
    </div>
    <div class="mt-2">
        <div class="font-semibold">🍨 Desserts :</div>
        @if(!$menu->desserts)
            <div class="text-gray-500 leading-snug">Pas de desserts</div>
        @endif
        @foreach($menu->desserts_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @if(count($menu->desserts_usual))
            <div class="text-gray-500 text-xs leading-normal">{{ join(', ', $menu->desserts_usual) }}</div>
        @endif
    </div>

    @if($displayDetails ?? false)
        <div class="mt-4 text-xs">
            @if($menu->next_fries_day && $menu->next_fries_day->is_burgers_day)
                <div class="mt-1">
                    <div>Prochain 🍔 🍟 Jour des Burgers et des Frites 🍟 🍔 : </div>
                    <div>{{ $menu->next_fries_day->date_carbon->translatedFormat('l d F') }}</div>
                </div>
            @else
                @if($menu->next_fries_day)
                    <div class="mt-1">
                        <div>Prochain 🍟 Jour des Frites 🍟 : </div>
                        <div>{{ $menu->next_fries_day->date_carbon->translatedFormat('l d F') }}</div>
                    </div>
                @endif
                @if($menu->next_burgers_day)
                    <div class="mt-1">
                        <div>Prochain 🍔 Jour des Burgers 🍔 : </div>
                        <div>{{ $menu->next_burgers_day->date_carbon->translatedFormat('l d F') }}</div>
                    </div>
                @endif
            @endif

            @if($menu->next_event)
                <div class="mt-1">
                    <div>Prochain 🎉 Événement 🎉 : </div>
                    <div>{{ $menu->next_event->event_name }} - {{ $menu->next_event->date_carbon->translatedFormat('l d F') }}</div>
                </div>
            @endif

            @if($menu->next_antioxidants_day)
                <div class="mt-1">
                    <div>Prochain 🏋️ Jour des Antioxydants 🏋️ : </div>
                    <div>{{ $menu->next_antioxidants_day->date_carbon->translatedFormat('l d F') }}</div>
                </div>
            @endif
        </div>
    @endif

    @auth
        <div class="mt-4 leading-snug text-xs text-gray-500">
            Généré le {{ $menu->updated_at->translatedFormat('d F Y à H:i') }}<br />
            @if($menu->file)
                <a href="{{ route('file', $menu->file->hash) }}" class="hover:text-indigo-500">
                    Source :
                    {{ $menu->file->name }} du {{ $menu->file->datetime_carbon->translatedFormat('d F Y') }}
                </a>
            @endif
        </div>
    @endauth
</div>