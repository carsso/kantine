@spaceless
    <blockquote class="@if($menu) {{ $menu->is_fries_day || $menu->is_burgers_day ? 'danger' : ($menu->event_name ? 'success' : 'info') }} @else warning @endif">
        <h3>Menu du {{ $date->translatedFormat('l j F Y') }}</h3><br />

        @if($menu)
            @if($menu->information)
                📢 {!! $menu->information_html !!}<br />
            @endif
            @if($menu->event_name)
                <strong>🎉 Événement {{ $menu->event_name }} 🎉</strong><br />
            @endif
            @if($menu->is_fries_day && $menu->is_burgers_day)
                <strong>🍔 🍟 Jour des Burgers et des Frites 🍟 🍔</strong><br />
            @elseif($menu->is_fries_day)
                <strong>🍟 Jour des Frites 🍟</strong><br />
            @elseif($menu->is_burgers_day)
                <strong>🍔 Jour des Burgers 🍔</strong><br />
            @endif
            @if($menu->is_antioxidants_day)
                <strong>🏋️ Jour des Antioxydants 🏋️</strong><br />
            @endif

            @if($menu->information || $menu->event_name || $menu->is_fries_day || $menu->is_burgers_day || $menu->is_antioxidants_day)
                <br />
            @endif

            @if(count($menu->starters_without_usual) == 1)
                <strong>🥗 Entrée :</strong> {{ join(', ', $menu->starters_without_usual) }} <i>(ou {{ join(', ', $menu->starters_usual) }})</i><br />
            @else
                <strong>🥗 Entrées </strong> <i>(ou {{ join(', ', $menu->starters_usual) }})</i> <strong> :</strong><br />
                @if(!$menu->starters)
                    <i>Pas d'entrée</i><br />
                @endif
                @foreach($menu->starters_without_usual as $dish)
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}</span><br />
                @endforeach
            @endif

            @if(!$menu->liberos)
                <strong>🍳 Libéro : </strong> <i>Pas de Libéro</i><br />
            @else
                @if(count($menu->liberos) == 1)
                    <strong>🍳 Libéro :</strong> {{ join(', ', $menu->liberos) }}<br />
                @else
                    <strong>🍳 Libéro :</strong><br />
                    @foreach($menu->liberos as $dish)
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}</span><br />
                    @endforeach
                @endif
            @endif

            <strong>🍗 Plats :</strong><br />
            @if(!$menu->mains)
                <i>Pas de plat</i><br />
            @endif
            @foreach($menu->mains as $idx => $dish)
                <span>
                    @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                        &nbsp;&nbsp;&nbsp;&nbsp;- 🍔 {{ $dish }}
                    @else
                        &nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}
                    @endif
                    @if($specialName = $menu->getMainSpecialName($idx, false))
                        <i>({{ $specialName }})</i>
                    @endif
                </span><br />
            @endforeach

            <strong>🥬 Garnitures :</strong><br />
            @if(!$menu->sides)
                <i>Pas de garniture</i><br />
            @endif
            @foreach($menu->sides as $dish)
                @if($dish == 'Frites')
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- 🍟 {{ $dish }}<br />
                @else
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}</span><br />
                @endif
            @endforeach

            @if(count($menu->cheeses) == 1)
                <strong>🧀 {{ join(', ', $menu->cheeses) }}</strong><br />
            @else
                <strong>🧀 Fromages / Laitages :</strong><br />
                @if(!$menu->cheeses)
                    <i>Pas de fromage / laitage</i><br />
                @endif
                @foreach($menu->cheeses_without_usual as $dish)
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}</span><br />
                @endforeach
                @foreach($menu->cheeses_usual as $dish)
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- <i>{{ $dish }}</i></span><br />
                @endforeach
            @endif

            @if(count($menu->desserts_without_usual) == 1)
                <strong>🍨 Dessert :</strong> {{ join(', ', $menu->desserts_without_usual) }} <i>(ou {{ join(', ', $menu->desserts_usual) }})</i><br />
            @else
                <strong>🍨 Desserts </strong> <i>(ou {{ join(', ', $menu->desserts_usual) }})</i> <strong> :</strong><br />
                @if(!$menu->desserts)
                    <i>Pas de dessert</i><br />
                @endif
                @foreach($menu->desserts_without_usual as $dish)
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;- {{ $dish }}</span><br />
                @endforeach
            @endif

            <br />

            @if($menu->next_fries_day && $menu->next_fries_day->is_burgers_day)
                <i>
                    <span>Prochain 🍔 🍟 Jour des Burgers et des Frites : </span>
                    <span>{{ $menu->next_fries_day->date_carbon->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @else
                @if($menu->next_fries_day)
                    <i>
                        <span>Prochain 🍟 Jour des Frites : </span>
                        <span>{{ $menu->next_fries_day->date_carbon->translatedFormat('l j F') }}</span>
                    </i>
                    <br />
                @endif
                @if($menu->next_burgers_day)
                    <i>
                        <span>Prochain 🍔 Jour des Burgers : </span>
                        <span>{{ $menu->next_burgers_day->date_carbon->translatedFormat('l j F') }}</span>
                    </i>
                    <br />
                @endif
            @endif

            @if($menu->next_event)
                <i>
                    <span>Prochain 🎉 Événement : </span>
                    <span>{{ $menu->next_event->event_name }}</span>
                    -
                    <span>{{ $menu->next_event->date_carbon->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @endif

            @if($menu->next_antioxidants_day)
                <i>
                    <span>Prochain 🏋️ Jour des Antioxydants : </span>
                    <span>{{ $menu->next_antioxidants_day->date_carbon->translatedFormat('l j F') }}</span>
                </i>
                <br />
            @endif

            <a href="{{ route('menu', $menu->date) }}">{{ route('menu', $menu->date) }}</a>
        @else
            <span>Aucun menu pour ce jour</span><br />
            <a href="{{ route('home') }}">{{ route('home') }}</a>
        @endif
        @if(!isset($hideMention) || !$hideMention)
            <@personEmail:{{ config('services.webex.bot_name') }}| >
        @endif
    </blockquote>
@endspaceless