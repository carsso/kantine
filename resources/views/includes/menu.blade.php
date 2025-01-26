
<div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-5 text-center border-t-4 {{ $menu->is_fries_day || $menu->is_burgers_day ? 'border-[#ED733D]' : ($menu->event_name ? 'border-[#FFD124]' : 'border-[#147DE8]') }}">
    <h1 class="text-2xl xl:hidden 2xl:block mb-3">{{ $menu->date_carbon->translatedFormat('l j F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-3">{{ $menu->date_carbon->translatedFormat('D j M') }}</h1>
    @if($menu->information)
        <p class="mt-2 text-sm leading-snug">
            <i class="fa-solid fa-bullhorn"></i> {!! $menu->information_html !!}
        </p>
    @endif
    @if($menu->event_name)
        <div class="mt-2 font-semibold">
            <i class="fa-solid fa-party-horn text-[#FFD124]"></i>
            Événement {{ $menu->event_name }}
            <i class="fa-solid fa-party-horn text-[#FFD124]"></i>
        </div>
    @endif
    @if($menu->is_fries_day && $menu->is_burgers_day)
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-burger-fries text-[#ED733D]"></i>
            Jour des Burgers et des Frites
            <i class="fa-solid fa-burger-fries text-[#ED733D]"></i>
        </p>
    @elseif($menu->is_fries_day)
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
            Jour des Frites
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
        </p>
    @elseif($menu->is_burgers_day)
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
            <i class="fa-solid fa-burger-cheese"></i> Jour des Burgers
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
        </p>
    @endif
    @if($menu->is_antioxidants_day)
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-dumbbell"></i>
            Jour des Antioxydants
            <i class="fa-solid fa-dumbbell"></i>
        </p>
    @endif
    <div class="mt-2">
        <div class="font-semibold text-[#A6D64D]">
            <i class="fa-solid fa-salad"></i> Entrées :
        </div>
        @if(!$menu->starters)
            <div class="text-gray-500 leading-snug">Pas d'entrée</div>
        @endif
        @foreach($menu->starters_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @if(count($menu->starters_usual))
            <div class="text-gray-500 text-xs leading-normal">{{ join(', ', $menu->starters_usual) }}</div>
        @endif
    </div>
    <div class="mt-2">
        <div class="font-semibold text-[#4AB0F5]">
            <i class="fa-solid fa-pan-frying"></i> Libéro :
        </div>
        @if(!$menu->liberos)
            @if($menu->date_carbon->startOfDay()->isPast())
                <div class="text-gray-500 leading-snug">Pas de Libéro</div>
            @else
                <div class="text-gray-500 leading-snug">...</div>
            @endif
        @else
            @foreach($menu->liberos as $dish)
                <div class="leading-snug">{{ $dish }}</div>
            @endforeach
        @endif
    </div>
    <div class="mt-2">
        <div class="font-semibold text-[#ED733D]">
            <i class="fa-solid fa-turkey"></i> Plats :
        </div>
        @if(!$menu->mains)
            <div class="text-gray-500 leading-snug">Pas de plat</div>
        @endif
        @foreach($menu->mains as $idx => $dish)
            <div class="leading-snug">
                @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                    <i class="fa-solid fa-burger-cheese"></i> {{ $dish }}
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
        <div class="font-semibold text-[#FFD124]">
            <i class="fa-solid fa-carrot"></i> Garnitures :
        </div>
        @if(!$menu->sides)
            <div class="text-gray-500 leading-snug">Pas de garniture</div>
        @endif
        @foreach($menu->sides as $dish)
            @if($dish == 'Frites')
                <div class="leading-snug">
                    <i class="fa-solid fa-french-fries"></i> {{ $dish }}
                </div>
            @else
                <div class="leading-snug">{{ $dish }}</div>
            @endif
        @endforeach
    </div>
    <div class="mt-2">
        @if(count($menu->cheeses) == 1)
            <div class="font-semibold text-[#73E3FF]">
                <i class="fa-solid fa-cheese-swiss"></i> {{ join(', ', $menu->cheeses) }}
            </div>
        @else
            <div class="font-semibold text-[#73E3FF]">
                <i class="fa-solid fa-cheese-swiss"></i> Fromages / Laitages :
            </div>
            @if(!$menu->cheeses)
                <div class="text-gray-500 leading-snug">Pas de fromage / laitage</div>
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
        <div class="font-semibold text-[#147DE8]">
            <i class="fa-solid fa-cupcake"></i> Desserts :
        </div>
        @if(!$menu->desserts)
            <div class="text-gray-500 leading-snug">Pas de dessert</div>
        @endif
        @foreach($menu->desserts_without_usual as $dish)
            <div class="leading-snug">{{ $dish }}</div>
        @endforeach
        @if(count($menu->desserts_usual))
            <div class="text-gray-500 text-xs leading-normal">{{ join(', ', $menu->desserts_usual) }}</div>
        @endif
    </div>

    @if($displayDetails ?? false)
        <div class="mt-4 text-sm italic">
            @if($menu->next_fries_day && $menu->next_fries_day->is_burgers_day)
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-burger-fries"></i> Jour des Burgers et des Frites : </div>
                    <div>{{ $menu->next_fries_day->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @else
                @if($menu->next_fries_day)
                    <div class="mt-1">
                        <div>Prochain <i class="fa-solid fa-french-fries"></i> Jour des Frites : </div>
                        <div>{{ $menu->next_fries_day->date_carbon->translatedFormat('l j F') }}</div>
                    </div>
                @endif
                @if($menu->next_burgers_day)
                    <div class="mt-1">
                        <div>Prochain <i class="fa-solid fa-burger-cheese"></i> Jour des Burgers : </div>
                        <div>{{ $menu->next_burgers_day->date_carbon->translatedFormat('l j F') }}</div>
                    </div>
                @endif
            @endif

            @if($menu->next_event)
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-party-horn"></i> Événement : </div>
                    <div>{{ $menu->next_event->event_name }} - {{ $menu->next_event->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @endif

            @if($menu->next_antioxidants_day)
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-dumbbell"></i> Jour des Antioxydants : </div>
                    <div>{{ $menu->next_antioxidants_day->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @endif
        </div>
    @endif

    @auth
        <div class="mt-4 leading-snug text-xs text-gray-500">
            Généré le {{ $menu->updated_at->translatedFormat('j F Y à H:i') }}<br />
            @if($menu->file)
                <a href="{{ route('file', $menu->file->hash) }}" class="hover:text-indigo-500">
                    Source :
                    {{ $menu->file->name }} du {{ $menu->file->datetime_carbon->translatedFormat('d F Y') }}
                </a>
            @endif
        </div>
    @endauth

    <page-refresher date="{{ $menu->date }}"></page-refresher>
</div>