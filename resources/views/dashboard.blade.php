@extends('layouts.app')

@section('meta')
<meta http-equiv="refresh" content="14400">
@endsection

@section('footer')
@endsection

@section('body')
    <body class="font-['Source_Sans_3'] bg-[#000E9C] text-white h-screen w-screen p-11">
        <main id="app" class="relative flex items-center text-4xl h-full w-full">
            <div class="flex-none p-11 border-r-4 text-center @if($menu) {{ $menu->is_fries_day ? 'border-[#ED733D]' : ($menu->event_name ? 'border-[#A6D64D]' : 'border-[#147DE8]') }} @else border-white @endif">
                <div class="text-8xl mb-5 font-black">Menu</div>
                <div class="text-6xl mb-3 font-black">{{ $day->translatedFormat('l') }}</div>
                <div class="text-[192px] leading-none mb-3 font-black">{{ $day->translatedFormat('d') }}</div>
                <div class="text-8xl mb-3 font-black">{{ $day->translatedFormat('F') }}</div>
                @if($menu && $menu->event_name)
                    <div class="bg-white rounded-lg p-6 mt-16 font-bold text-5xl border-t-4 border-[#147DE8] text-[#000E9C]">
                        <div class="">Événement</div>
                        <div class="mt-4">{{ $menu->event_name }}</div>
                    </div>
                @endif
            </div>
            <div class="grow text-center">
                @if($menu)
                    <div class="flex items-center place-content-center">
                        <div class="mx-8">
                            <div class="font-bold text-5xl mb-2 text-[#ED733D]">🥗 Entrées :</div>
                            @if(!$menu->starters)
                                <div class="font-extralight leading-snug">Pas d'entrée</div>
                            @endif
                            @foreach($menu->starters_without_usual as $dish)
                                <div class="leading-snug">{{ $dish }}</div>
                            @endforeach
                            @if(count($menu->starters_usual))
                                <div class="font-extralight text-m leading-normal">{{ join(', ', $menu->starters_usual) }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex place-content-center mt-12">
                        <div class="mx-12">
                            <div class="font-bold text-5xl mb-2 text-[#A6D64D]">🍗 Plats :</div>
                            @if(!$menu->mains)
                                <div class="font-extralight leading-snug">Pas de plat</div>
                            @endif
                            @foreach($menu->mains as $idx => $dish)
                                <div class="leading-snug">
                                    @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                                        🍔 {{ $dish }} 🍔
                                    @else
                                        {{ $dish }}
                                    @endif
                                    @if($specialName = $menu->getMainSpecialName($idx, false))
                                        <i class="font-extralight text-m">({{ $specialName }})</i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mx-12">
                            <div class="font-bold text-5xl mb-2 text-[#FFD124]">🥬 Garnitures :</div>
                            @if(!$menu->sides)
                                <div class="font-extralight leading-snug">Pas de garniture</div>
                            @endif
                            @foreach($menu->sides as $dish)
                                @if($dish == 'Frites')
                                    <div class="leading-snug">🍟 {{ $dish }} 🍟</div>
                                @else
                                    <div class="leading-snug">{{ $dish }}</div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="flex place-content-center mt-12">
                        <div class="mx-12">
                            <div>
                                @if(count($menu->cheeses) == 1)
                                    <div class="font-bold text-5xl text-[#73E3FF]">🧀 {{ join(', ', $menu->cheeses) }}</div>
                                @else
                                    <div class="font-bold text-5xl mb-2 text-[#73E3FF]">🧀 Fromages / Laitages :</div>
                                    @if(!$menu->cheeses)
                                        <div class="font-extralight leading-snug">Pas de fromage / laitage</div>
                                    @endif
                                    @foreach($menu->cheeses_without_usual as $dish)
                                        <div class="leading-snug">{{ $dish }}</div>
                                    @endforeach
                                    @if(count($menu->cheeses_usual))
                                        <div class="font-extralight text-m leading-normal">{{ join(', ', $menu->cheeses_usual) }}</div>
                                    @endif
                                @endif
                            </div>
                            <div class="mt-12">
                                <div class="font-bold text-5xl mb-2 text-[#147DE8]">🍨 Desserts :</div>
                                @if(!$menu->desserts)
                                    <div class="font-extralight leading-snug">Pas de dessert</div>
                                @endif
                                @foreach($menu->desserts_without_usual as $dish)
                                    <div class="leading-snug">{{ $dish }}</div>
                                @endforeach
                                @if(count($menu->desserts_usual))
                                    <div class="font-extralight text-m leading-normal">{{ join(', ', $menu->desserts_usual) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <h1 class="text-4xl mt-14 text-[#147DE8]">
                        Aucun menu pour aujourd'hui
                    </h1>
                @endif
            </div>
            <div class="absolute right-0 bottom-0 text-xs mt-16 text-[#00279c]">
                {{ $time->translatedFormat('d/m/y H:i:s') }}
            </div>
        </main>
    </body>
@endsection