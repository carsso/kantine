
<div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-5">
    <h1 class="text-2xl xl:hidden 2xl:block mb-1">{{ $menu['date_carbon']->translatedFormat('l j F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-1">{{ $menu['date_carbon']->translatedFormat('D j M') }}</h1>
    <input id="date[{{ $idx }}]" type="hidden" name="date[{{ $idx }}]" value="{{ $menu['date'] }}">
    <div class="mb-3 leading-snug text-xs text-gray-500">
        <a href="{{ route('dashboard', ['date' => $menu['date_carbon']->format('Y-m-d')]) }}" target="_blank" class="hover:text-indigo-500">
            <i class="fa-solid fa-up-right-from-square"></i>
            Aperçu du dashboard
        </a>
    </div>
    <div class="mt-2">
        <label for="information" class="block text-sm font-semibold">Information :</label>
        <textarea id="information[{{ $idx }}]" rows="3" type="text" name="information[{{ $idx }}]" value="{{ $menu['information']['information'] ?? '' }}" class="block w-full px-2 py-0.5 border border-gray-200 shadow-xs rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400"></textarea>
        @error('information')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="style" class="block text-sm font-semibold">Style visuel :</label>
        <div class="text-gray-500 text-xs">
            @foreach(array_keys(config('tsparticles.config', [])) as $style)
                @if(!$loop->first) - @endif
                <a href="{{ route('dashboard', ['date' => $menu['date_carbon']->format('Y-m-d'), 'style' => $style]) }}" target="_blank" class="hover:text-indigo-500">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    {{ config('tsparticles.config.'.$style.'.name', $style) }}
                </a>
            @endforeach
        </div>
        <select id="style[{{ $idx }}]" name="style[{{ $idx }}]" class="block w-full px-1 py-1 border border-gray-200 shadow-xs rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400">
            <option value=""></option>
            @foreach(array_keys(config('tsparticles.config', [])) as $style)
                <option value="{{ $style }}" @if($style == ($menu['information']['style'] ?? '')) selected @endif>
                    {{ config('tsparticles.config.'.$style.'.name', $style) }}
                </option>
            @endforeach
        </select>
        @error('style')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="event_name" class="block font-semibold">Événement :</label>
        <input id="event_name[{{ $idx }}]" type="text" name="event_name[{{ $idx }}]" value="{{ $menu['information']['event_name'] ?? '' }}" class="block w-full px-2 py-0.5 border border-gray-200 shadow-xs rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400">
        @error('event_name')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    @error('dishes')
        <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
            {{ $message }}
        </div>
    @enderror
    @foreach($categories as $type => $rootCategories)
        <div class="mt-2">
            {{ config('kantine.dishes_types.'.$type, $type) }}
            <div class="ml-1 border-l-2 border-[#147DE8] pl-1">
                @foreach($rootCategories as $rootCategory)
                    {{ $rootCategory->name }}
                    <div class="ml-1 border-l-2 border-[#ED733D] pl-1">
                        @foreach($rootCategory->children as $category)
                            @php
                                $dishesCollection = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? null;
                                $dishes = $dishesCollection ? $dishesCollection->pluck('name')->toArray() : [];
                                $dishesTags = $dishesCollection ? $dishesCollection->pluck('tags')->toArray() : [];
                                $inputName = 'dishes['.$idx.']['.$category->id.']';
                                $inputNameTags = 'dishes_tags['.$idx.']['.$category->id.']';
                            @endphp
                            @if($dishes || !$category->hidden)
                                <div class="ml-1">
                                    <label for="{{ $inputName }}" class="block font-semibold">{{ $category->name }} :</label>
                                    <admin-dishes-input
                                        name="{{ $inputName }}"
                                        name-tags="{{ $inputNameTags }}"
                                        placeholder="Entrez un nom..."
                                        :dishes='@json(old($inputName) ?? $dishes)'
                                        :dishes-tags='@json(old($inputNameTags) ?? $dishesTags)'
                                        :autocomplete-dishes='@json($autocompleteDishes)'
                                        :autocomplete-dishes-tags='@json($autocompleteDishesTags)'>
                                    </admin-dishes-input>
                                    @error($inputName)
                                        <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    @error($inputNameTags)
                                        <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>