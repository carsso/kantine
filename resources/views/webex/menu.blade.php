@spaceless
    @if($menu)
        <blockquote class="{{ $menu->is_fries_day || $menu->is_burgers_day ? 'danger' : ($menu->event_name ? 'success' : 'info') }}">
            <h3>Menu du {{ $menu->date_carbon->translatedFormat('l d F Y') }}</h3><br />

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

            <strong>🥗 Entrées :</strong><br />
            @if(!$menu->starters)
                <i>Pas d'entrées</i><br />
            @endif
            @foreach($menu->starters_without_usual as $dish)
                <span> - {{ $dish }}</span><br />
            @endforeach
            @foreach($menu->starters_usual as $dish)
                <span> - <i>{{ $dish }}</i></span><br />
            @endforeach

            <strong>🍗 Plats :</strong><br />
            @if(!$menu->mains)
                <i>Pas de plats</i><br />
            @endif
            @foreach($menu->mains as $idx => $dish)
                <span>
                    @if($dish == 'Burger' && !$menu->getMainSpecialName($idx))
                        - 🍔 {{ $dish }} 🍔
                    @else
                        - {{ $dish }}
                    @endif
                    @if($specialName = $menu->getMainSpecialName($idx))
                        <i>({{ $specialName }})</i>
                    @endif
                </span><br />
            @endforeach

            <strong>🥬 Accompagnements :</strong><br />
            @if(!$menu->sides)
                <i>Pas d'accompagnements</i><br />
            @endif
            @foreach($menu->sides as $dish)
                @if($dish == 'Frites')
                    <span> - 🍟 {{ $dish }} 🍟 <br />
                @else
                    <span> - {{ $dish }}</span><br />
                @endif
            @endforeach

            <strong>🧀 Fromages / Laitages :</strong><br />
            @if(!$menu->cheeses)
                <i>Pas de fromages / laitages</i><br />
            @endif
            @foreach($menu->cheeses_without_usual as $dish)
                <span> - {{ $dish }}</span><br />
            @endforeach
            @foreach($menu->cheeses_usual as $dish)
                <span> - <i>{{ $dish }}</i></span><br />
            @endforeach

            <strong>🍨 Desserts :</strong><br />
            @if(!$menu->desserts)
                <i>Pas de desserts</i><br />
            @endif
            @foreach($menu->desserts_without_usual as $dish)
                <span> - {{ $dish }}</span><br />
            @endforeach
            @foreach($menu->desserts_usual as $dish)
                <span> - <i>{{ $dish }}</i></span><br />
            @endforeach

            <br />

            @if($menu->next_fries_day && $menu->next_fries_day->is_burgers_day)
                <i>
                    <span>Prochain 🍔 🍟 Jour des Burgers et des Frites 🍟 🍔 : </span>
                    <span>{{ $menu->next_fries_day->date_carbon->translatedFormat('l d F') }}</span>
                </i>
                <br />
            @else
                @if($menu->next_fries_day)
                    <i>
                        <span>Prochain 🍟 Jour des Frites 🍟 : </span>
                        <span>{{ $menu->next_fries_day->date_carbon->translatedFormat('l d F') }}</span>
                    </i>
                    <br />
                @endif
                @if($menu->next_burgers_day)
                    <i>
                        <span>Prochain 🍔 Jour des Burgers 🍔 : </span>
                        <span>{{ $menu->next_burgers_day->date_carbon->translatedFormat('l d F') }}</span>
                    </i>
                    <br />
                @endif
            @endif

            @if($menu->next_event)
                <i>
                    <span>Prochain 🎉 Événement 🎉 : </span>
                    <span>{{ $menu->next_event->event_name }}</span>
                    -
                    <span>{{ $menu->next_event->date_carbon->translatedFormat('l d F') }}</span>
                </i>
                <br />
            @endif

            @if($menu->next_antioxidants_day)
                <i>
                    <span>Prochain 🏋️ Jour des Antioxydants 🏋️ : </span>
                    <span>{{ $menu->next_antioxidants_day->date_carbon->translatedFormat('l d F') }}</span>
                </i>
                <br />
            @endif

            <a href="{{ route('menu', $menu->date) }}">{{ route('menu', $menu->date) }}</a>
        </blockquote>
    @else
        <blockquote class="warning">
            <h3>Aucun menu trouvé pour aujourd'hui</h3><br />
            <span>Tu as le menu ? Viens l'ajouter sur le site !</span><br />
            <a href="{{ route('home') }}">{{ route('home') }}</a>
        </blockquote>
    @endif
@endspaceless