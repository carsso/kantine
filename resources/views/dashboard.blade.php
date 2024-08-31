@extends('layouts.app')

@section('meta')
<meta http-equiv="refresh" content="14400">
@endsection

@section('footer')
@endsection

@section('body')
    <body class="font-['Source_Sans_3'] bg-[#000E9C] text-white h-screen w-screen px-11">
        <main id="app" class="relative flex flex-row items-center text-4xl h-full w-full">
            <div class="basis-1/3 py-2 border-r-4 text-center @if($menu) {{ $menu->is_fries_day ? 'border-[#ED733D]' : ($menu->event_name ? 'border-[#FFD124]' : 'border-[#147DE8]') }} @else border-white @endif">
                <div class="text-9xl mb-5 font-black">Menu</div>
                <div class="text-8xl mb-3 font-black">{{ $day->translatedFormat('l') }}</div>
                <div class="text-[196px] leading-none mb-3 font-black">{{ $day->translatedFormat('d') }}</div>
                <div class="text-8xl mb-3 font-black">{{ $day->translatedFormat('F') }}</div>
                @if($menu && $menu->event_name)
                    <div class="flex items-center justify-center">
                        <div class="bg-white rounded-lg px-10 py-6 mt-16 mb-5 mx-auto font-bold text-5xl border-t-4 border-[#147DE8] text-[#000E9C]">
                            <div class="">Événement</div>
                            <div class="mt-4">{{ $menu->event_name }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="basis-2/3 text-center">
                @if($menu)
                    <div class="flex items-center place-content-center">
                        <div class="mx-8">
                            <div class="font-bold text-5xl mb-2 text-[#A6D64D]">
                                <i class="fa-thin fa-salad"></i> Entrées :
                            </div>
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
                            <div class="font-bold text-5xl mb-2 text-[#ED733D]">
                                <i class="fa-thin fa-turkey"></i> Plats :
                            </div>
                            @if(!$menu->mains)
                                <div class="font-extralight leading-snug">Pas de plat</div>
                            @endif
                            @foreach($menu->mains as $idx => $dish)
                                <div class="leading-snug">
                                    @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                                        <i class="fa-thin fa-burger-cheese"></i> {{ $dish }}
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
                            <div class="font-bold text-5xl mb-2 text-[#FFD124]">
                                <i class="fa-thin fa-carrot"></i> Garnitures :
                            </div>
                            @if(!$menu->sides)
                                <div class="font-extralight leading-snug">Pas de garniture</div>
                            @endif
                            @foreach($menu->sides as $dish)
                                @if($dish == 'Frites')
                                    <div class="leading-snug">
                                        <i class="fa-thin fa-french-fries"></i> {{ $dish }}
                                    </div>
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
                                    <div class="font-bold text-5xl text-[#73E3FF]">
                                        <i class="fa-thin fa-cheese-swiss"></i> {{ join(', ', $menu->cheeses) }}
                                    </div>
                                @else
                                    <div class="font-bold text-5xl mb-2 text-[#73E3FF]">
                                        <i class="fa-thin fa-cheese-swiss"></i> Fromages / Laitages :
                                    </div>
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
                                <div class="font-bold text-5xl mb-2 text-[#147DE8]">
                                    <i class="fa-thin fa-cupcake"></i> Desserts :
                                </div>
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
        </main>
    </body>
@endsection