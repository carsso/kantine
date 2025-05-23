@extends('layouts.app')

@section('meta')
<meta name="google" content="notranslate">
@endsection

@section('footer')
@endsection

@section('body')
    <body id="app" class="font-['Source_Sans_3'] bg-[#000E9C] text-white h-screen w-screen">
        @if($particlesOptions)
            <div>
                <vue-particles
                    id="tsparticles"
                    :options='@json($particlesOptions)'>
                </vue-particles>
            </div>
        @endif
        <div id="dashboard-container" class="w-screen h-screen overflow-hidden flex items-center justify-center">
            <div id="dashboard-scale" class="">
                <div id="dashboard-wrapper" class="w-[1920px] h-[1080px]">
                    <main class="p-8 relative flex flex-row items-center text-[3rem] text-left h-full w-full leading-[1.2]">
                        <div class="basis-1/6 pr-12 text-right">
                            <div class="text-8xl font-black">Menu</div>
                            <div class="text-4xl mb-2 font-extralight italic">{{ $tenant->name }}</div>
                            @if($diff)
                                <div class="text-4xl font-extralight italic">{{ $diff }}</div>
                            @endif
                            <div class="text-5xl mt-8">{{ $day->translatedFormat('l') }}</div>
                            <div class="text-[200px] leading-none font-black">{{ $day->translatedFormat('j') }}</div>
                            <div class="text-5xl">{{ $day->translatedFormat('F') }}</div>
                            @if($menu && $menu['information'] && $menu['information']['event_name'])
                                <div class="flex items-center justify-center">
                                    <div class="bg-white rounded-lg px-8 py-5 mt-16 mb-5 mx-auto font-bold text-4xl border-t-4 border-[#147DE8] text-[#000E9C]">
                                        <div>Événement</div>
                                        <div class="mt-4">{{ $menu['information']['event_name'] }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="basis-5/6 pl-8 border-l-4 @if($menu) {{ $menu['is_fries_day'] ? 'border-[#ED733D]' : ($menu['information'] && $menu['information']['event_name'] ? 'border-[#FFD124]' : 'border-[#147DE8]') }} @else border-white @endif">
                            @if($menu)
                                <div class="flex flex-row place-content-center">
                                    @foreach([0, 1] as $colidx)
                                        <div class="basis-1/2 px-4 flex flex-col place-content-center">
                                            @php
                                                $j = 0;
                                                $is_first_element = true;
                                            @endphp
                                            @foreach($categories as $type => $rootCategories)
                                                @foreach($rootCategories as $rootCategory)
                                                    @if($rootCategory->hidden_from_dashboard)
                                                        @continue
                                                    @endif
                                                    @php
                                                        $i = 0;
                                                        $has_dishes = false;
                                                    @endphp
                                                    @foreach($rootCategory->children as $category)
                                                        @if($category->hidden_from_dashboard)
                                                            @continue
                                                        @endif
                                                        @php
                                                            $dishes = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? [];
                                                        @endphp
                                                        @if($dishes)
                                                            @php
                                                                $has_dishes = true;
                                                            @endphp
                                                            @if($j % 2 == $colidx)
                                                                <div class="@if(!$is_first_element) @if(!$i) mt-11 @else mt-2 @endif @endif">  
                                                                    @if(!$i && $rootCategory->name == $category->name && count($dishes) == 1)
                                                                        @php
                                                                            $dish = $dishes[0];
                                                                        @endphp
                                                                        @if(strtolower($dishes[0]['name']) == strtolower($category->name))
                                                                            <div class="font-bold text-[3.25rem] mb-2 text-[{{ $rootCategory->color }}]">
                                                                                <i class="fa-thin {{ $rootCategory->icon }}"></i> {{ $rootCategory->name }}
                                                                            </div>
                                                                        @else
                                                                            <div>
                                                                                <span class="font-bold text-[3.25rem] mb-2 text-[{{ $rootCategory->color }}]">
                                                                                    <i class="fa-thin {{ $rootCategory->icon }}"></i> {{ $rootCategory->name }} :
                                                                                </span>
                                                                                {{ $dish['name'] }}
                                                                                @if($dish['tags'])
                                                                                    <i class="font-extralight text-4xl">
                                                                                        ({{ collect($dish['tags'])->map(fn($tag) => \App\Models\Dish::getTagShortName($tag))->join(', ') }})
                                                                                    </i>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        @if(!$i)
                                                                            <div class="font-bold text-[3.25rem] mb-2 text-[{{ $rootCategory->color }}]">
                                                                                <i class="fa-thin {{ $rootCategory->icon }}"></i> {{ $rootCategory->name }} :
                                                                            </div>
                                                                        @else
                                                                            <div class="font-normal text-[{{ $rootCategory->color }}]">
                                                                                {{ $category->name }}
                                                                            </div>
                                                                        @endif
                                                                        @foreach($dishes as $dish)
                                                                            <div>
                                                                                -
                                                                                @if($dish['name'] == 'Frites')
                                                                                    <i class="fa-solid fa-french-fries"></i>
                                                                                @elseif($dish['name'] == 'Frites')
                                                                                    <i class="fa-solid fa-burger-cheese"></i>
                                                                                @endif
                                                                                {{ $dish['name'] }}
                                                                                @if($dish['tags'])
                                                                                    <i class="font-extralight text-4xl">
                                                                                        ({{ collect($dish['tags'])->map(fn($tag) => \App\Models\Dish::getTagShortName($tag))->join(', ') }})
                                                                                    </i>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                                @php
                                                                    $is_first_element = false;
                                                                @endphp
                                                            @endif
                                                        @endif
                                                        @php
                                                            $i++;
                                                        @endphp
                                                    @endforeach
                                                    @php
                                                        $has_dishes && $j++;
                                                    @endphp
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
                </div>
            </div>
        </div>
        <div class="fixed bottom-10 right-10 text-[#002f9c]">
            {{ $generationDate->translatedFormat('j F Y à H:i:s') }}
        </div>
        <echo-state></echo-state>
        <page-refresher date="{{ $menu['date'] }}"></page-refresher>
        <script>
            function scaleDashboard() {
              const baseWidth = 1920;
              const baseHeight = 1080;
              const scaleX = window.innerWidth / baseWidth;
              const scaleY = window.innerHeight / baseHeight;
              const scale = Math.min(scaleX, scaleY);
              const scaleContainer = document.getElementById('dashboard-scale');
              scaleContainer.style.transform = `scale(${scale})`;
            }
            window.addEventListener('load', scaleDashboard);
            window.addEventListener('resize', scaleDashboard);
        </script>
    </body>
@endsection