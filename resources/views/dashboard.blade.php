@extends('layouts.app')

@section('meta')
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
                @if($menu && $menu['information'] && $menu['information']['event_name'])
                    <div class="flex items-center justify-center">
                        <div class="bg-white rounded-lg px-10 py-6 mt-16 mb-5 mx-auto font-bold text-5xl border-t-4 border-[#147DE8] text-[#000E9C]">
                            <div>Événement</div>
                            <div class="mt-4">{{ $menu['information']['event_name'] }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="basis-3/4 border-l-4 @if($menu) {{ $menu['is_fries_day'] ? 'border-[#ED733D]' : ($menu['information'] && $menu['information']['event_name'] ? 'border-[#FFD124]' : 'border-[#147DE8]') }} @else border-white @endif">
                @if($menu)
                    <div class="flex flex-row place-content-center">
                        @foreach([0, 1] as $i)
                            <div class="basis-1/2 px-8 flex flex-col place-content-center">
                                @php
                                    $count = 0;
                                @endphp

                                @foreach($categories as $type => $rootCategories)
                                    @foreach($rootCategories as $rootCategory)
                                        @foreach($rootCategory->children as $category)
                                            @php
                                                $dishes = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? [];
                                            @endphp
                                            @if($dishes || !$category->hidden)
                                                @if($count % 2 == $i)
                                                    <div class="{{ $count <= 1 ? '' : 'mt-12' }}">
                                                        <div class="font-bold text-6xl mb-2 text-[{{ $category->color }}]">
                                                            <i class="fa-thin {{ $category->icon }}"></i> {{ $category->name }}
                                                            @if($dishes && (count($dishes) != 1 || strtolower($dishes[0]['name']) != strtolower($category->name)))
                                                                :
                                                            @endif
                                                        </div>
                                                        @if(!$dishes)
                                                            @if($menu['date_carbon']->startOfDay()->isPast())
                                                                <div class="font-extralight">Aucun</div>
                                                            @else
                                                                <div class="font-extralight">...</div>
                                                            @endif
                                                        @endif
                                                        @if($dishes && (count($dishes) != 1 || strtolower($dishes[0]['name']) != strtolower($category->name)))
                                                            @foreach($dishes as $dish)
                                                                <div>
                                                                    @if($dish['name'] == 'Frites')
                                                                        <i class="fa-solid fa-french-fries"></i>
                                                                    @elseif($dish['name'] == 'Frites')
                                                                        <i class="fa-solid fa-burger-cheese"></i>
                                                                    @endif
                                                                    {{ $dish['name'] }}
                                                                    @if($dish['tags'])
                                                                        <i class="font-extralight text-4xl">
                                                                            ({{ collect($dish['tags'])->map(fn($tag) => \App\Models\Dish::getTagTranslation($tag))->join(', ') }})
                                                                        </i>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endif
                                                @php
                                                    $count++;
                                                @endphp
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </div>
                        @endforeach
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
        <echo-state></echo-state>
        <page-refresher date="{{ $menu['date'] }}"></page-refresher>
    </body>
@endsection