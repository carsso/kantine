@spaceless
    @if($menu)
        <blockquote class="{{ $menu->is_fries_day ? 'danger' : ($menu->event_name ? 'success' : 'info') }}">
            <h3>Menu du {{ $menu->date_carbon->translatedFormat('l d F Y') }}</h3>

            @if($menu->event_name)
                <strong>🎉 Événement {{ $menu->event_name }} 🎉</strong><br />
            @endif
            @if($menu->is_fries_day)
                <strong>🍟 Jour des Frites 🍟</strong><br />
            @endif

            <strong>🥗 Entrées :</strong><br />
            @if(!$menu->starters)
                <i>Pas d'entrées</i><br />
            @endif
            @foreach($menu->starters as $starter)
                <span> - {{ $starter }}</span><br />
            @endforeach

            <strong>🍗 Plats :</strong><br />
            @if(!$menu->mains)
                <i>Pas de plats</i><br />
            @endif
            @foreach($menu->mains as $main)
                <span> - {{ $main }}</span><br />
            @endforeach

            <strong>🥬 Accompagnements :</strong><br />
            @if(!$menu->sides)
                <i>Pas d'accompagnements</i><br />
            @endif
            @foreach($menu->sides as $side)
                @if($side == 'Frites')
                    <span> - 🍟 {{ $side }} 🍟 <br />
                @else
                    <span> - {{ $side }}</span><br />
                @endif
            @endforeach

            <strong>🧀 Fromages / Laitages :</strong><br />
            @if(!$menu->cheeses)
                <i>Pas de fromages / laitages</i><br />
            @endif
            @foreach($menu->cheeses as $cheese)
                <span> - {{ $cheese }}</span><br />
            @endforeach

            <strong>🍨 Desserts :</strong><br />
            @if(!$menu->desserts)
                <i>Pas de desserts</i><br />
            @endif
            @foreach($menu->desserts as $dessert)
                <span> - {{ $dessert }}</span><br />
            @endforeach

            <a href="{{ route('menu.date', $menu->date) }}">{{ route('menu.date', $menu->date) }}</a>
        </blockquote>
    @else
        <blockquote class="warning">
            <h3>Aucun menu trouvé pour aujourd'hui</h3>
            <span>Tu as le menu ? Viens l'ajouter sur le site !</span><br />
            <a href="{{ route('home') }}">{{ route('home') }}</a>
        </blockquote>
    @endif
@endspaceless