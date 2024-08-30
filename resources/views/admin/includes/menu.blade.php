
<div class="bg-white dark:bg-gray-700 rounded-lg shadow px-4 py-5 text-center border-t-4 {{ $menu->is_fries_day || $menu->is_burgers_day ? 'border-red-500' : ($menu->event_name ? 'border-green-500' : 'border-blue-500') }}">
    <h1 class="text-2xl xl:hidden 2xl:block mb-1">{{ $menu->date_carbon->translatedFormat('l d F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-1">{{ $menu->date_carbon->translatedFormat('D d M') }}</h1>
    <input id="date[{{ $idx }}]" type="hidden" name="date[{{ $idx }}]" value="{{ $menu->date }}">
    <div class="mb-3 leading-snug text-xs text-gray-500">
        <a href="{{ route('dashboard', ['date' => $menu->date_carbon->format('Y-m-d')]) }}" target="_blank" class="hover:text-indigo-500">
            <i class="fa fa-external-link-alt"></i>
            Aperçu du dashboard pour ce jour
        </a>
    </div>
    <div class="mt-2">
        <label for="information" class="block text-sm font-semibold">ℹ️ Information :</label>
        <textarea id="information[{{ $idx }}]" rows="3" type="text" name="information[{{ $idx }}]" value="{{ old('information') ?? $menu->information }}" class="block w-full py-0.5 border border-gray-200 shadow-sm rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400"></textarea>
        @error('information')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="event_name" class="block font-semibold">🎉 Événement 🎉 :</label>
        <input id="event_name[{{ $idx }}]" type="text" name="event_name[{{ $idx }}]" value="{{ old('event_name') ?? $menu->event_name }}" class="block w-full py-0.5 border border-gray-200 shadow-sm rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400">
        @error('event_name')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    @if($menu->is_fries_day && $menu->is_burgers_day)
        <p class="mt-2 font-semibold">🍔 🍟 Jour des Burgers et des Frites 🍟 🍔</p>
    @elseif($menu->is_fries_day)
        <p class="mt-2 font-semibold">🍟 Jour des Frites 🍟</p>
    @elseif($menu->is_burgers_day)
        <p class="mt-2 font-semibold">🍔 Jour des Burgers 🍔</p>
    @endif
    @if($menu->is_antioxidants_day)
        <p class="mt-2 font-semibold">🏋️ Jour des Antioxydants 🏋️</p>
    @endif
    <div class="mt-2">
        <label for="starters" class="block font-semibold">🥗 Entrées :</label>
        <admin-dishes-input
            name="starters[{{ $idx }}]"
            :dishes='@json(old('starters') ?? $menu->starters ?? [])'
            :autocomplete-dishes='@json($autocompleteDishes['starters'])'>
        </admin-dishes-input>
        @error('starters')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="starters" class="block font-semibold">🍗 Plats :</label>
        <div class="text-gray-500 text-xs">
            L'ordre est important.<br />
            Avant-dernier : plat végé.<br />
            Dernier : plat hallal
        </div>
        <admin-dishes-input
            name="mains[{{ $idx }}]"
            :dishes='@json(old('mains') ?? $menu->mains ?? [])'
            :special-indexes='@json($menu->getSpecialIndexesDefinitionHumanReadable('mains', true))'
            :autocomplete-dishes='@json($autocompleteDishes['mains'])'>
        </admin-dishes-input>
        @error('mains')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="starters" class="block font-semibold">🥬 Garnitures :</label>
        <admin-dishes-input
            name="sides[{{ $idx }}]"
            :dishes='@json(old('sides') ?? $menu->sides ?? [])'
            :autocomplete-dishes='@json($autocompleteDishes['sides'])'>
        </admin-dishes-input>
        @error('sides')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="starters" class="block font-semibold">🧀 Fromages / Laitages :</label>
        <admin-dishes-input
            name="cheeses[{{ $idx }}]"
            :dishes='@json(old('cheeses') ?? $menu->cheeses ?? [])'
            :autocomplete-dishes='@json($autocompleteDishes['cheeses'])'>
        </admin-dishes-input>
        @error('cheeses')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="starters" class="block font-semibold">🍨 Desserts :</label>
        <admin-dishes-input
            name="desserts[{{ $idx }}]"
            :dishes='@json(old('desserts') ?? $menu->desserts ?? [])'
            :autocomplete-dishes='@json($autocompleteDishes['desserts'])'>
        </admin-dishes-input>
        @error('desserts')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>