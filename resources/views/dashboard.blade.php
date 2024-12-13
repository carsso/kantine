@extends('layouts.app')

@section('meta')
<meta http-equiv="refresh" content="3600">
<meta name="google" content="notranslate">
@endsection

@section('footer')
@endsection

@section('body')
    <body id="app" class="font-['Source_Sans_3'] bg-[#000E9C] text-white h-screen w-screen px-8">
        @if($particlesOptions)
            <div>
                <vue-particles
                    id="tsparticles"
                    :options='@json($particlesOptions)'>
                </vue-particles>
            </div>
        @endif
        <main class="relative flex flex-row items-center text-5xl text-center h-full w-full leading-[1.2]">
            <div class="basis-1/4 px-8">
                <div class="text-8xl font-black">Menu</div>
                @if($diff)
                    <div class="text-4xl font-extralight italic">{{ $diff }}</div>
                @endif
                <div class="text-7xl mt-8">{{ $day->translatedFormat('l') }}</div>
                <div class="text-[256px] leading-none font-black">{{ $day->translatedFormat('j') }}</div>
                <div class="text-7xl">{{ $day->translatedFormat('F') }}</div>
                @if($menu && $menu->event_name)
                    <div class="flex items-center justify-center">
                        <div class="bg-white rounded-lg px-10 py-6 mt-16 mb-5 mx-auto font-bold text-5xl border-t-4 border-[#147DE8] text-[#000E9C]">
                            <div>Événement</div>
                            <div class="mt-4">{{ $menu->event_name }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="basis-3/4 border-l-4 @if($menu) {{ $menu->is_fries_day ? 'border-[#ED733D]' : ($menu->event_name ? 'border-[#FFD124]' : 'border-[#147DE8]') }} @else border-white @endif">
                @if($menu)
                    <div class="flex flex-row place-content-center">
                        <div class="basis-1/2 px-8 flex flex-col place-content-center">
                            <div>
                                <div class="font-bold text-6xl mb-2 text-[#A6D64D]">
                                    <i class="fa-thin fa-salad"></i> Entrées :
                                </div>
                                @if(!$menu->starters)
                                    <div class="font-extralight">Pas d'entrée</div>
                                @endif
                                @foreach($menu->starters_without_usual as $dish)
                                    <div>{{ $dish }}</div>
                                @endforeach
                                @if(count($menu->starters_usual))
                                    <div class="font-extralight text-4xl">{{ join(', ', $menu->starters_usual) }}</div>
                                @endif
                            </div>
                            <div class="mt-12">
                                <div class="font-bold text-6xl mb-2 text-[#ED733D]">
                                    <i class="fa-thin fa-turkey"></i> Plats :
                                </div>
                                @if(!$menu->mains)
                                    <div class="font-extralight">Pas de plat</div>
                                @endif
                                @foreach($menu->mains as $idx => $dish)
                                    <div>
                                        @if($specialName = $menu->getMainSpecialName($idx, true))
                                            <i class="font-extralight">{{ $specialName }}</i> : 
                                        @endif
                                        @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                                            <i class="fa-thin fa-burger-cheese"></i> {{ $dish }}
                                        @else
                                            {{ $dish }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-12">
                                @if(count($menu->cheeses) == 1)
                                    <div class="font-bold text-6xl text-[#73E3FF]">
                                        <i class="fa-thin fa-cheese-swiss"></i> {{ join(', ', $menu->cheeses) }}
                                    </div>
                                @else
                                    <div class="font-bold text-6xl mb-2 text-[#73E3FF]">
                                        <i class="fa-thin fa-cheese-swiss"></i> Fromages / Laitages :
                                    </div>
                                    @if(!$menu->cheeses)
                                        <div class="font-extralight">Pas de fromage / laitage</div>
                                    @endif
                                    @foreach($menu->cheeses_without_usual as $dish)
                                        <div>{{ $dish }}</div>
                                    @endforeach
                                    @if(count($menu->cheeses_usual))
                                        <div class="font-extralight text-3xl">{{ join(', ', $menu->cheeses_usual) }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="basis-1/2 px-8 flex flex-col place-content-center">
                            <div>
                                <div class="font-bold text-6xl mb-2 text-[#4AB0F5]">
                                    <i class="fa-thin fa-pan-frying"></i> Libéro :
                                </div>
                                @if(!$menu->liberos)
                                    <div class="font-extralight">Pas de Libéro</div>
                                @else
                                    @foreach($menu->liberos as $dish)
                                        <div>{{ $dish }}</div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="mt-12">
                                <div class="font-bold text-6xl mb-2 text-[#FFD124]">
                                    <i class="fa-thin fa-carrot"></i> Garnitures :
                                </div>
                                @if(!$menu->sides)
                                    <div class="font-extralight">Pas de garniture</div>
                                @endif
                                @foreach($menu->sides as $dish)
                                    @if($dish == 'Frites')
                                        <div>
                                            <i class="fa-thin fa-french-fries"></i> {{ $dish }}
                                        </div>
                                    @else
                                        <div>{{ $dish }}</div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="mt-12">
                                <div class="font-bold text-6xl mb-2 text-[#147DE8]">
                                    <i class="fa-thin fa-cupcake"></i> Desserts :
                                </div>
                                @if(!$menu->desserts)
                                    <div class="font-extralight">Pas de dessert</div>
                                @endif
                                @foreach($menu->desserts_without_usual as $dish)
                                    <div>{{ $dish }}</div>
                                @endforeach
                                @if(count($menu->desserts_usual))
                                    <div class="font-extralight text-4xl">{{ join(', ', $menu->desserts_usual) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <h1 class="text-4xl mt-14 text-[#147DE8]">
                        Aucun menu pour ce jour
                    </h1>
                @endif
            </div>
        </main>
        <div class="fixed bottom-10 right-10 text-[#002f9c]">
            {{ $generationDate->translatedFormat('j F Y à H:i:s') }}
        </div>
    </body>
@endsection