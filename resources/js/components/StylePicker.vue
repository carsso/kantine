<template>
    <div class="style-picker">
        <div class="flex items-center gap-1">
            <select :id="name" :name="name" v-model="selected" :required="themesOnly" class="block w-full px-1 py-1 border border-gray-200 shadow-xs rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400">
                <option v-if="themesOnly" value="" disabled>Choisir un style…</option>
                <template v-else>
                    <option value="">Automatique</option>
                    <option value="none">Aucun style</option>
                </template>
                <optgroup v-for="category in categories" :key="category.name" :label="category.name">
                    <option v-for="theme in category.themes" :key="theme.key" :value="theme.key">{{ theme.name }}</option>
                </optgroup>
            </select>
            <button type="button" class="flex-none px-2 py-1 border border-gray-200 shadow-xs rounded-md text-sm text-gray-600 hover:text-indigo-600 hover:border-indigo-300 dark:border-gray-500 dark:text-gray-400" title="Galerie des styles" @click="open">
                <i class="fa-solid fa-images"></i>
            </button>
        </div>
        <template v-if="themesOnly"></template>
        <div v-else-if="mode === 'disabled'" class="mt-0.5 text-xs text-red-600">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Styles désactivés pour cette cantine
        </div>
        <div v-else-if="selected === '' && mode === 'manual'" class="mt-0.5 text-xs text-gray-500">
            Pas de style (automatique désactivé pour cette cantine)
        </div>
        <div v-else-if="selected === '' && automaticStyle" class="mt-0.5 text-xs text-gray-500">
            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>{{ themeName(automaticStyle) }}
            <span class="text-gray-400">({{ suggestedStyle ? "d'après l'animation" : 'calendrier' }})</span>
        </div>
        <div v-else-if="selected === ''" class="mt-0.5 text-xs text-gray-400">
            Pas de style automatique ce jour-là
        </div>
        <div v-else-if="suggestedStyle && suggestedStyle !== selected" class="mt-0.5 text-xs text-gray-500">
            Suggestion : {{ themeName(suggestedStyle) }}
            <button type="button" class="ml-1 text-indigo-600 hover:underline" @click="selected = suggestedStyle">Utiliser</button>
        </div>

        <Teleport to="body">
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="close">
                <div class="flex flex-col w-full max-w-7xl max-h-full rounded-lg shadow-xl bg-white dark:bg-gray-800 dark:text-gray-300">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <button v-if="detail" type="button" class="text-gray-500 hover:text-indigo-600" @click="detail = null">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Tous les styles
                        </button>
                        <h2 class="flex-1 text-lg font-semibold">{{ detail ? detail.name : 'Choisir un style visuel' }}</h2>
                        <button type="button" class="text-gray-500 hover:text-gray-800 dark:hover:text-white" title="Fermer" @click="close">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div v-if="detail" class="p-4 overflow-y-auto">
                        <iframe :src="dashboardUrl(detail.key)" class="w-full aspect-video rounded-md border-0 bg-[#000E9C]"></iframe>
                        <div class="flex justify-end gap-2 mt-3">
                            <a :href="dashboardUrl(detail.key)" target="_blank" class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-600 hover:text-indigo-600">
                                <i class="fa-solid fa-up-right-from-square mr-1"></i> Ouvrir dans un onglet
                            </a>
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700" @click="choose(detail.key)">
                                Choisir ce style
                            </button>
                        </div>
                    </div>

                    <div v-else class="p-4 overflow-y-auto">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <template v-if="!themesOnly">
                                <button type="button" class="px-3 py-1 rounded-md text-sm border hover:border-indigo-400" :class="selected === '' ? 'border-indigo-500 text-indigo-600' : 'border-gray-200 dark:border-gray-600'" @click="choose('')">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Automatique<span v-if="automaticStyle"> : {{ themeName(automaticStyle) }}</span>
                                </button>
                                <button type="button" class="px-3 py-1 rounded-md text-sm border hover:border-indigo-400" :class="selected === 'none' ? 'border-indigo-500 text-indigo-600' : 'border-gray-200 dark:border-gray-600'" @click="choose('none')">
                                    Aucun style
                                </button>
                            </template>
                            <label class="relative flex-1 min-w-48 max-w-sm">
                                <i class="fa-solid fa-magnifying-glass absolute left-2 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input ref="search" v-model="search" type="search" placeholder="Rechercher un style, un mot-clé…" class="w-full pl-7 pr-2 py-1 border border-gray-200 rounded-md text-sm dark:bg-gray-700 dark:border-gray-500" @keydown.enter.prevent>
                            </label>
                            <div class="flex items-center gap-1 ml-auto text-sm">
                                <i class="fa-solid fa-magnifying-glass-plus text-gray-500 mr-1" title="Zoom"></i>
                                <button v-for="level in [1, 2, 3]" :key="level" type="button" class="px-2 py-1 rounded-md border" :class="zoom === level ? 'border-indigo-500 text-indigo-600' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-400'" @click="setPreference('zoom', level)">
                                    {{ level }}×
                                </button>
                                <button type="button" class="ml-2 px-2 py-1 rounded-md border" :class="largePreviews ? 'border-indigo-500 text-indigo-600' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-400'" title="Grandes vignettes" @click="setPreference('largePreviews', !largePreviews)">
                                    <i class="fa-solid fa-table-cells-large"></i>
                                </button>
                            </div>
                        </div>
                        <p v-if="filteredCategories.length === 0" class="py-8 text-center text-gray-500">Aucun style ne correspond à « {{ search }} ».</p>
                        <div v-for="category in filteredCategories" :key="category.name" class="mb-6">
                            <h3 class="mb-2 font-semibold text-gray-600 dark:text-gray-400">{{ category.name }}</h3>
                            <div class="grid gap-3" :class="largePreviews ? 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3' : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5'">
                                <button v-for="theme in category.themes" :key="theme.key" type="button"
                                    class="text-left rounded-md overflow-hidden ring-2 transition"
                                    :class="theme.key === selected ? 'ring-indigo-500' : 'ring-transparent hover:ring-indigo-300'"
                                    @click="detail = theme">
                                    <theme-preview :id="previewId(theme.key)" :options="theme.options" :zoom="zoom" />
                                    <div class="px-2 py-1 text-sm truncate">
                                        <i v-if="theme.key === selected" class="fa-solid fa-check text-indigo-500 mr-1"></i>
                                        <i v-else-if="!themesOnly && selected === '' && theme.key === automaticStyle" class="fa-solid fa-wand-magic-sparkles text-indigo-400 mr-1" title="Style automatique"></i>{{ theme.name }}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import ThemePreview from './ThemePreview.vue';

let themesCache = null;

const PREFERENCES_KEY = 'style-picker-preferences';

/**
 * Gallery display preferences, kept in the browser only.
 */
function loadPreferences() {
    try {
        return JSON.parse(localStorage.getItem(PREFERENCES_KEY)) ?? {};
    } catch {
        return {};
    }
}

/**
 * The themes are printed once per page (they weigh ~100 KB) instead of once per day.
 */
function loadThemes() {
    themesCache ??= JSON.parse(document.getElementById('particle-themes')?.textContent ?? '{}');
    return themesCache;
}

/**
 * Same rules as App\Lib\DashboardStyle: lowercase, no accents nor punctuation.
 */
function normalize(text) {
    return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
}

/**
 * Same rules as App\Lib\DashboardStyle::fromEventName(): the longest keyword found wins.
 */
function styleFromEventName(eventName) {
    const text = ` ${normalize(eventName)} `;
    let bestStyle = null;
    let bestLength = 0;
    for (const [key, options] of Object.entries(loadThemes())) {
        for (const keyword of options.keywords ?? []) {
            const needle = ` ${keyword}${keyword.length <= 3 ? ' ' : ''}`;
            if (keyword.length > bestLength && text.includes(needle)) {
                bestStyle = key;
                bestLength = keyword.length;
            }
        }
    }
    return bestStyle;
}

export default {

    name: "StylePicker",

    components: {
        ThemePreview,
    },

    props: {
        name: {
            type: String,
            required: true,
        },
        value: {
            type: String,
            required: false,
            default: '',
        },
        previewUrl: {
            type: String,
            required: true,
        },
        calendarStyle: {
            type: String,
            required: false,
            default: '',
        },
        eventInput: {
            type: String,
            required: false,
            default: '',
        },
        mode: {
            type: String,
            required: false,
            default: 'automatic',
        },
        // Only real themes can be picked: no "Automatique" nor "Aucun style".
        themesOnly: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            selected: this.value,
            isOpen: false,
            detail: null,
            eventName: '',
            search: '',
            zoom: loadPreferences().zoom ?? 1,
            largePreviews: loadPreferences().largePreviews ?? false,
        };
    },

    computed: {
        suggestedStyle() {
            return styleFromEventName(this.eventName);
        },

        automaticStyle() {
            return this.suggestedStyle ?? this.calendarStyle;
        },

        categories() {
            const categories = [];
            for (const [key, options] of Object.entries(loadThemes())) {
                const categoryName = options.category ?? 'Autres';
                let category = categories.find(c => c.name === categoryName);
                if (!category) {
                    category = { name: categoryName, themes: [] };
                    categories.push(category);
                }
                category.themes.push({ key, name: options.name ?? key, options, searchText: normalize([options.name ?? key, ...(options.keywords ?? [])].join(' ')) });
            }
            return categories;
        },

        // Themes whose name or keywords contain every word typed, even partly.
        filteredCategories() {
            const words = normalize(this.search).split(' ').filter(Boolean);
            if (words.length === 0) {
                return this.categories;
            }
            return this.categories
                .map(category => ({ ...category, themes: category.themes.filter(theme => words.every(word => theme.searchText.includes(word))) }))
                .filter(category => category.themes.length > 0);
        },
    },

    mounted() {
        this.eventField = this.eventInput ? document.getElementById(this.eventInput) : null;
        if (this.eventField) {
            this.eventName = this.eventField.value;
            this.eventField.addEventListener('input', this.updateEventName);
        }
    },

    methods: {
        updateEventName() {
            this.eventName = this.eventField.value;
        },

        setPreference(name, value) {
            this[name] = value;
            try {
                localStorage.setItem(PREFERENCES_KEY, JSON.stringify({ zoom: this.zoom, largePreviews: this.largePreviews }));
            } catch {
                // Private browsing: the preference only lasts for the page.
            }
        },

        themeName(key) {
            return loadThemes()[key]?.name ?? key;
        },

        open() {
            this.detail = null;
            this.search = '';
            this.isOpen = true;
            document.addEventListener('keydown', this.closeOnEscape);
            this.$nextTick(() => this.$refs.search?.focus());
        },

        close() {
            this.isOpen = false;
            document.removeEventListener('keydown', this.closeOnEscape);
        },

        closeOnEscape(event) {
            if (event.key === 'Escape') {
                this.close();
            }
        },

        choose(key) {
            this.selected = key;
            this.close();
        },

        dashboardUrl(key) {
            const url = new URL(this.previewUrl);
            url.searchParams.set('style', key);
            return url.toString();
        },

        previewId(key) {
            return `preview-${this.name.replace(/\W/g, '-')}-${key}`;
        },
    },

    beforeUnmount() {
        document.removeEventListener('keydown', this.closeOnEscape);
        this.eventField?.removeEventListener('input', this.updateEventName);
    },
};
</script>
