
<div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 text-center mt-6 border-t-4 {{ $menu->is_fries_day ? 'border-red-500' : ($menu->event_name ? 'border-green-500' : 'border-blue-500') }}">
    <h1 class="text-2xl">{{ $menu->date_carbon->translatedFormat('l d F') }}</h1>
    @if($menu->event_name)
        <p class="mt-2">🎉 Événement {{ $menu->event_name }} 🎉</p>
    @endif
    @if($menu->is_fries_day)
        <p class="mt-2">🍟 Jour des Frites 🍟</p>
    @endif
    <p class="mt-2">
        🥗 Entrées : <br />
        @if(!$menu->starters)
            <i class="text-gray-500">Pas d'entrées</i>
        @endif
        @foreach($menu->starters as $starter)
            {{ $starter }} <br />
        @endforeach
    </p>
    <p class="mt-2">
        🍗 Plats : <br />
        @if(!$menu->mains)
            <i class="text-gray-500">Pas de plats</i>
        @endif
        @foreach($menu->mains as $main)
            {{ $main }} <br />
        @endforeach
    </p>
    <p class="mt-2">
        🥬 Accompagnements : <br />
        @if(!$menu->sides)
            <i class="text-gray-500">Pas d'accompagnements</i>
        @endif
        @foreach($menu->sides as $side)
            @if($side == 'Frites')
                🍟 {{ $side }} 🍟 <br />
            @else
                {{ $side }} <br />
            @endif
        @endforeach
    </p>
    <p class="mt-2">
        🧀 Fromages / Laitages : <br />
        @if(!$menu->cheeses)
            <i class="text-gray-500">Pas de fromages / laitages</i>
        @endif
        @foreach($menu->cheeses as $cheese)
            {{ $cheese }} <br />
        @endforeach
    </p>
    <p class="mt-2">
        🍨 Desserts : <br />
        @if(!$menu->desserts)
            <i class="text-gray-500">Pas de desserts</i>
        @endif
        @foreach($menu->desserts as $dessert)
            {{ $dessert }} <br />
        @endforeach
    </p>
    <p class="mt-2">
        <small class="text-gray-500">
            Généré le {{ $menu->updated_at->translatedFormat('d F Y à H:i') }}<br />
            <a href="{{ route('file', $menu->file->hash) }}" class="hover:text-indigo-500">
                Source :
                {{ $menu->file->name }} du {{ $menu->file->datetime_carbon->translatedFormat('d F Y') }}
            </a>
        </small>
    </p>
</div>