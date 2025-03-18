<div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-6 py-5 text-left border-t-4 {{ $menu['is_fries_day'] || $menu['is_burgers_day'] ? 'border-[#ED733D]' : ($menu['information'] && $menu['information']['event_name'] ? 'border-[#FFD124]' : 'border-[#147DE8]') }}">
    <h1 class="text-2xl xl:hidden 2xl:block mb-3">{{ $menu['date_carbon']->translatedFormat('l j F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-3">{{ $menu['date_carbon']->translatedFormat('D j M') }}</h1>
    @if($menu['information'] && $menu['information']['information_html'])
        <p class="mt-2 text-sm leading-snug">
            <i class="fa-solid fa-bullhorn"></i> {!! $menu['information']['information_html'] !!}
        </p>
    @endif
    @if($menu['information'] && $menu['information']['event_name'])
        <div class="mt-2 font-semibold">
            <i class="fa-solid fa-party-horn text-[#FFD124]"></i>
            Événement {{ $menu['information']['event_name'] }}
            <i class="fa-solid fa-party-horn text-[#FFD124]"></i>
        </div>
    @endif
    @if($menu['is_fries_day'] && $menu['is_burgers_day'])
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-burger-fries text-[#ED733D]"></i>
            Jour des Burgers et des Frites
            <i class="fa-solid fa-burger-fries text-[#ED733D]"></i>
        </p>
    @elseif($menu['is_fries_day'])
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
            Jour des Frites
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
        </p>
    @elseif($menu['is_burgers_day'])
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
            <i class="fa-solid fa-burger-cheese"></i> Jour des Burgers
            <i class="fa-solid fa-french-fries text-[#ED733D]"></i>
        </p>
    @endif
    @if($menu['is_antioxidants_day'])
        <p class="mt-2 font-semibold">
            <i class="fa-solid fa-dumbbell"></i>
            Jour des Antioxydants
            <i class="fa-solid fa-dumbbell"></i>
        </p>
    @endif

    @foreach($categories as $type => $rootCategories)
        @foreach($rootCategories as $rootCategory)
            @php
                $i = 0;
            @endphp
            @foreach($rootCategory->children as $category)
                @php
                    $dishes = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? [];
                @endphp
                @if($dishes)
                    <div class="@if(!$i) mt-4 @endif">
                        @if(!$i)
                            <div class="font-semibold text-[{{ $rootCategory->color }}]">
                                <i class="fa-solid {{ $rootCategory->icon }}"></i> {{ $rootCategory->name }} :
                            </div>
                        @else
                            <div class="font-light text-sm text-[{{ $rootCategory->color }}] ml-2">
                                {{ $category->name }} :
                            </div>
                        @endif
                        @foreach($dishes as $dish)
                            <div class="leading-snug ml-4">
                                -
                                @if($dish['name'] == 'Frites')
                                    <i class="fa-solid fa-french-fries"></i>
                                @elseif($dish['name'] == 'Frites')
                                    <i class="fa-solid fa-burger-cheese"></i>
                                @endif
                                {{ $dish['name'] }}
                                @if($dish['tags'])
                                    <i class="text-gray-500 text-xs">
                                        ({{ collect($dish['tags'])->map(fn($tag) => \App\Models\Dish::getTagTranslation($tag))->join(', ') }})
                                    </i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                @php
                    $i++;
                @endphp
            @endforeach
        @endforeach
    @endforeach
    @if($displayDetails ?? false)
        <div class="mt-4 text-sm italic">
            @if($menu['next_fries_day'] && $menu['next_fries_day']->is_burgers_day)
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-burger-fries"></i> Jour des Burgers et des Frites : </div>
                    <div>{{ $menu['next_fries_day']->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @else
                @if($menu['next_fries_day'])
                    <div class="mt-1">
                        <div>Prochain <i class="fa-solid fa-french-fries"></i> Jour des Frites : </div>
                        <div>{{ $menu['next_fries_day']->date_carbon->translatedFormat('l j F') }}</div>
                    </div>
                @endif
                @if($menu['next_burgers_day'])
                    <div class="mt-1">
                        <div>Prochain <i class="fa-solid fa-burger-cheese"></i> Jour des Burgers : </div>
                        <div>{{ $menu['next_burgers_day']->date_carbon->translatedFormat('l j F') }}</div>
                    </div>
                @endif
            @endif

            @if($menu['next_event'])
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-party-horn"></i> Événement : </div>
                    <div>{{ $menu['next_event']->event_name }} - {{ $menu['next_event']->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @endif

            @if($menu['next_antioxidants_day'])
                <div class="mt-1">
                    <div>Prochain <i class="fa-solid fa-dumbbell"></i> Jour des Antioxydants : </div>
                    <div>{{ $menu['next_antioxidants_day']->date_carbon->translatedFormat('l j F') }}</div>
                </div>
            @endif
        </div>
    @endif

    <page-refresher date="{{ $menu['date'] }}"></page-refresher>
</div>