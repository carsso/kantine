@if($menu)
    <h3>Menu du {{ $menu->date_carbon->translatedFormat('l d F Y') }}</h3>
    <br />
    @if($menu->event_name)
        <strong>🎉 Événement {{ $menu->event_name }} 🎉</strong><br />
    @endif
    @if(str_contains(join(', ', $menu->sides), 'Frites'))
        <strong>🍟 Jour des Frites 🍟</strong><br />
    @endif

    <strong>🥗 Entrées :</strong><br />
    @if(!$menu->starters)
        <i>Pas d'entrées</i><br />
    @endif
    @foreach($menu->starters as $starter)
        - {{ $starter }} <br />
    @endforeach

    <strong>🍗 Plats :</strong><br />
    @if(!$menu->mains)
        <i>Pas de plats</i><br />
    @endif
    @foreach($menu->mains as $main)
        - {{ $main }} <br />
    @endforeach

    <strong>🥬 Accompagnements :</strong><br />
    @if(!$menu->sides)
        <i>Pas d'accompagnements</i><br />
    @endif
    @foreach($menu->sides as $side)
        @if($side == 'Frites')
            - 🍟 {{ $side }} 🍟 <br />
        @else
            - {{ $side }} <br />
        @endif
    @endforeach

    <strong>🧀 Fromages / Laitages :</strong><br />
    @if(!$menu->cheeses)
        <i>Pas de fromages / laitages</i><br />
    @endif
    @foreach($menu->cheeses as $cheese)
        - {{ $cheese }} <br />
    @endforeach

    <strong>🍨 Desserts :</strong><br />
    @if(!$menu->desserts)
        <i>Pas de desserts</i><br />
    @endif
    @foreach($menu->desserts as $dessert)
        - {{ $dessert }} <br />
    @endforeach

    <a href="{{ route('menu.date', $menu->date) }}">{{ route('menu.date', $menu->date) }}</a>
@else
    <h3>Aucun menu trouvé pour aujourd'hui</h3>
    <br />
    Tu as le menu ? Viens l'ajouter sur le site !
    <a href="{{ route('home') }}">{{ route('menu') }}</a>
@endif