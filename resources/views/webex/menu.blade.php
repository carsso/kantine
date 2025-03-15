@spaceless
    <blockquote class="@if($menu) {{ $menu['is_fries_day'] || $menu['is_burgers_day'] ? 'danger' : ($menu['information'] && $menu['information']['event_name'] ? 'success' : 'info') }} @else warning @endif">
        <h3>Menu du {{ $date->translatedFormat('l j F Y') }}</h3><br />

        @if($menu)
            @if($menu['information'] && $menu['information']['information_html'])
                📢 {!! $menu['information']['information_html'] !!}<br />
            @endif
            @if($menu['information'] && $menu['information']['event_name'])
                <strong>🎉 Événement {{ $menu['information']['event_name'] }} 🎉</strong><br />
            @endif
            @if($menu['is_fries_day'] && $menu['is_burgers_day'])
                <strong>🍔 🍟 Jour des Burgers et des Frites 🍟 🍔</strong><br />
            @elseif($menu['is_fries_day'])
                <strong>🍟 Jour des Frites 🍟</strong><br />
            @elseif($menu['is_burgers_day'])
                <strong>🍔 Jour des Burgers 🍔</strong><br />
            @endif
            @if($menu['is_antioxidants_day'])
                <strong>🏋️ Jour des Antioxydants 🏋️</strong><br />
            @endif

            @if(($menu['information'] && ($menu['information']['information_html'] || $menu['information']['event_name'])) || $menu['is_fries_day'] || $menu['is_burgers_day'] || $menu['is_antioxidants_day'])
                <br />
            @endif


            @foreach($categories as $type => $rootCategories)
                @foreach($rootCategories as $rootCategory)
                    @foreach($rootCategory->children as $category)
                        @php
                            $dishes = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? [];
                        @endphp
                        @if($dishes || !$category->hidden)
                            <strong>
                                {{ $category->emoji }} {{ $category->name }}
                                @if($dishes && (count($dishes) != 1 || strtolower($dishes[0]['name']) != strtolower($category->name)))
                                    :
                                @endif
                            </strong><br />
                            @if(!$dishes)
                                @if($menu['date_carbon']->startOfDay()->isPast())
                                    &nbsp;&nbsp;&nbsp;&nbsp;- Aucun<br />
                                @else
                                    &nbsp;&nbsp;&nbsp;&nbsp;- ...<br />
                                @endif
                            @endif
                            @if($dishes && (count($dishes) != 1 || strtolower($dishes[0]['name']) != strtolower($category->name)))
                                @foreach($dishes as $dish)
                                    <span>
                                        &nbsp;&nbsp;&nbsp;&nbsp;-
                                        @if($dish['name'] == 'Frites')
                                            🍟
                                        @elseif($dish['name'] == 'Burger')
                                            🍔
                                        @endif
                                        {{ $dish['name'] }}
                                        @if($dish['tags'])
                                            <i>
                                                ({{ collect($dish['tags'])->map(fn($tag) => \App\Models\Dish::getTagTranslation($tag))->join(', ') }})
                                            </i>
                                        @endif
                                    </span><br />
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @endforeach
            @endforeach

            <br />

            @if($menu['next_fries_day'] && $menu['next_fries_day']['is_burgers_day'])
                <i>
                    <span>Prochain 🍔 🍟 Jour des Burgers et des Frites : </span>
                    <span>{{ $menu['next_fries_day']['date_carbon']->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @else
                @if($menu['next_fries_day'])
                    <i>
                        <span>Prochain 🍟 Jour des Frites : </span>
                        <span>{{ $menu['next_fries_day']['date_carbon']->translatedFormat('l j F') }}</span>
                    </i>
                    <br />
                @endif
                @if($menu['next_burgers_day'])
                    <i>
                        <span>Prochain 🍔 Jour des Burgers : </span>
                        <span>{{ $menu['next_burgers_day']['date_carbon']->translatedFormat('l j F') }}</span>
                    </i>
                    <br />
                @endif
            @endif

            @if($menu['next_event'])
                <i>
                    <span>Prochain 🎉 Événement : </span>
                    <span>{{ $menu['next_event']['event_name'] }}</span>
                    -
                    <span>{{ $menu['next_event']['date_carbon']->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @endif

            @if($menu['next_antioxidants_day'])
                <i>
                    <span>Prochain 🏋️ Jour des Antioxydants : </span>
                    <span>{{ $menu['next_antioxidants_day']['date_carbon']->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @endif

            <a href="{{ route('menu', $menu['date']) }}">{{ route('menu', $menu['date']) }}</a>
        @else
            <span>Aucun menu pour ce jour</span><br />
            <a href="{{ route('home') }}">{{ route('home') }}</a>
        @endif
        @if(!isset($hideMention) || !$hideMention)
            <@personEmail:{{ config('services.webex.bot_name') }}| >
        @endif
    </blockquote>
@endspaceless