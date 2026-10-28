@spaceless
    <blockquote class="{{ $menu['is_fries_day'] || $menu['is_burgers_day'] ? 'danger' : ($menu['information'] && $menu['information']['event_name'] ? 'success' : 'info') }}">
        <h3>Menu du {{ $date->translatedFormat('l j F Y') }}</h3><br />

        @if($menu['information'] && $menu['information']['information_html'])
            📢 {!! $menu['information']['information_html'] !!}<br />
        @endif
        @if($menu['information'] && $menu['information']['event_name'])
            <strong>🎉 {{ $menu['information']['event_name'] }} 🎉</strong><br />
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
                @php
                    $i = 0;
                @endphp
                @foreach($rootCategory->children as $category)
                    @php
                        $dishes = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? [];
                    @endphp
                    @if($dishes)
                        @if(!$i)
                            <strong>
                                {{ $rootCategory->emoji }} {{ $rootCategory->name }} :
                            </strong><br />
                        @else
                            <span>
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $category->name }} :
                            </span><br />
                        @endif
                        @foreach($dishes as $dish)
                            <span>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-
                                @if($dish['name'] == 'Frites')
                                    🍟
                                @elseif($dish['name'] == 'Burger')
                                    🍔
                                @endif
                                {{ $dish['name'] }}
                                @php
                                    $dishTags = \App\Models\Dish::getTagsLabel($dish['tags'], false);
                                    $dishAllergens = \App\Models\Dish::getAllergensLabel($dish['allergens']);
                                @endphp
                                @if($dishTags || $dishAllergens)
                                    <span>
                                        ({{ $dishTags }}@if($dishTags && $dishAllergens) - @endif<i>{{ $dishAllergens }}</i>)
                                    </span>
                                @endif
                            </span><br />
                        @endforeach
                        @if($category->meta && isset($category->meta['link_url']))
                            <span>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <small>
                                    <a target="_blank"
                                        href="{{ route('menus.categories.link', ['tenantSlug' => $tenant->slug, 'date' => $menu['date'], 'type' => $type, 'parentSlug' => $rootCategory->name_slug, 'childSlug' => $category->name_slug]) }}">
                                        ↗
                                        {{ $category->meta['link_name'] }}
                                    </a>
                                </small>
                            </span><br />
                        @endif
                    @endif
                    @php
                        $i++;
                    @endphp
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
                <span>Prochain 🎉 : </span>
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

        <a href="{{ route('menus', ['tenantSlug' => $tenant->slug, 'date' => $menu['date']]) }}">{{ route('menus', ['tenantSlug' => $tenant->slug, 'date' => $menu['date']]) }}</a>

        @if(!isset($hideMention) || !$hideMention)
            <@personEmail:{{ $tenant->webex_bot_name }}| >
        @endif
    </blockquote>
@endspaceless