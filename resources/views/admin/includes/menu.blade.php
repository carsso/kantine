
<div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm px-4 py-5">
    <h1 class="text-2xl xl:hidden 2xl:block mb-1">{{ $menu['date_carbon']->translatedFormat('l j F') }}</h1>
    <h1 class="text-2xl hidden xl:block 2xl:hidden mb-1">{{ $menu['date_carbon']->translatedFormat('D j M') }}</h1>
    <input id="date[{{ $idx }}]" type="hidden" name="date[{{ $idx }}]" value="{{ $menu['date'] }}">
    <div class="mb-3 leading-snug text-xs text-gray-500">
        <a href="{{ route('dashboard', ['tenantSlug' => request()->tenant->slug, 'date' => $menu['date_carbon']->format('Y-m-d')]) }}" target="_blank" class="hover:text-indigo-500">
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
        <label for="style[{{ $idx }}]" class="block text-sm font-semibold">Style visuel :</label>
        <style-picker
            name="style[{{ $idx }}]"
            value="{{ $menu['information']['style'] ?? '' }}"
            preview-url="{{ route('dashboard', ['tenantSlug' => request()->tenant->slug, 'date' => $menu['date_carbon']->format('Y-m-d')]) }}"
            calendar-style="{{ \App\Lib\DashboardStyle::fromCalendar($menu['date_carbon'], request()->tenant) }}"
            event-input="event_name[{{ $idx }}]"
            mode="{{ request()->tenant->dashboard_style_mode->value }}">
        </style-picker>
        @error('style')
            <div class="rounded-md bg-red-50 dark:bg-red-800 text-xs font-medium text-red-800 dark:text-red-50 p-2 mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
    <div class="mt-2">
        <label for="event_name" class="block font-semibold">Événement / animation :</label>
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
    @php
        $j = 0;
    @endphp
    @foreach($categories as $type => $rootCategories)
        <div class="mt-2">
            {{ config('kantine.dishes_types.'.$type, $type) }}
            <div class="ml-1 border-l-2 border-gray-300 dark:border-gray-400 pl-1">
                @foreach($rootCategories as $rootCategory)
                    @php
                        $j++;
                    @endphp
                    @if(!isset($menu['dishes'][$type][$rootCategory->name_slug]) && $rootCategory->hidden)
                        @continue
                    @endif
                    {{ $rootCategory->name }}
                    @if($rootCategory->hidden_from_dashboard)
                        <i class="text-xs">
                            (Non visible sur le dashboard)
                        </i>
                    @endif
                    <div class="ml-1 border-l-2 {{ ['border-[#A6D64D]', 'border-[#FFD124]', 'border-[#4AB0F5]', 'border-[#ED733D]'][$j % 4] }} pl-1">
                        @foreach($rootCategory->children as $category)
                            @php
                                $dishesCollection = $menu['dishes'][$type][$rootCategory->name_slug][$category->name_slug] ?? null;
                                $dishes = $dishesCollection ? $dishesCollection->pluck('name')->toArray() : [];
                                $dishesTags = $dishesCollection ? $dishesCollection->pluck('tags')->toArray() : [];
                                $dishesAllergens = $dishesCollection ? $dishesCollection->pluck('allergens')->map(fn ($allergens) => $allergens ?? [])->toArray() : [];
                                $inputName = 'dishes['.$idx.']['.$category->id.']';
                                $inputNameTags = 'dishes_tags['.$idx.']['.$category->id.']';
                                $inputNameAllergens = 'dishes_allergens['.$idx.']['.$category->id.']';
                            @endphp
                            @if($dishes || !$category->hidden)
                                <div class="ml-1">
                                    @if($category->name != $rootCategory->name)
                                        <label for="{{ $inputName }}" class="block font-semibold">
                                            {{ $category->name }} 
                                            @if($category->hidden_from_dashboard)
                                                <i class="text-xs">
                                                    (Non visible sur le dashboard)
                                                </i>
                                            @endif
                                        </label>
                                    @endif
                                    <admin-dishes-input
                                        name="{{ $inputName }}"
                                        name-tags="{{ $inputNameTags }}"
                                        name-allergens="{{ $inputNameAllergens }}"
                                        placeholder="Entrez un nom..."
                                        :dishes='@json(old($inputName) ?? $dishes)'
                                        :dishes-tags='@json(old($inputNameTags) ?? $dishesTags)'
                                        :dishes-allergens='@json(old($inputNameAllergens) ?? $dishesAllergens)'
                                        :autocomplete-dishes='@json($autocompleteDishes)'
                                        :autocomplete-dishes-tags='@json($autocompleteDishesTags)'
                                        :autocomplete-dishes-allergens='@json($autocompleteDishesAllergens)'>
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
                                    @error($inputNameAllergens)
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