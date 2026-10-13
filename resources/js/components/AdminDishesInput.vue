<template>
    <div class="admin-dishes-input">
        <input
            v-for="(dish, index) in inputDishes" :key="index"
            :id="name+'_'+index"
            type="hidden"
            :name="name+'[]'"
            :value="inputDishes[index]" />
        <input
            v-for="(dish, index) in inputDishesTags" :key="index"
            :id="nameTags+'_'+index"
            type="hidden"
            :name="nameTags+'[]'"
            :value="reduce(inputDishesTags)[index]" />
        <input
            v-for="(dish, index) in inputDishesAllergens" :key="index"
            :id="nameAllergens+'_'+index"
            type="hidden"
            :name="nameAllergens+'[]'"
            :value="reduce(inputDishesAllergens)[index]" />
        <div
            v-for="(dish, index) in inputDishes" :key="index" class="flex items-center mb-1">
            <div
                class="block w-full">
                <v-select
                    v-model="inputDishes[index]"
                    taggable
                    class="admin-dishes-input-dish block w-full border px-2 py-1 border-gray-200 shadow-xs rounded-md text-sm leading-none focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400"
                    :options="filteredDishes"
                    placeholder="Rechercher un plat"
                    label="label"
                    @search="search">
                    <template v-slot:no-options>
                        Aucune option trouvée
                    </template>
                </v-select>
                <div class="w-full flex items-center">
                    <div class="border-l-1 border-gray-300 dark:border-gray-400 ml-0.5 px-2 text-xs text-gray-400 dark:text-gray-500">Tags</div>
                    <div class="block w-full">
                        <v-select
                            v-model="inputDishesTags[index]"
                            multiple
                            class="admin-dishes-input-dish-tag block w-full border px-0.5 py-0.5 border-gray-100 shadow-xs rounded-md text-xs text-[10px] leading-none focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                            :options="filteredDishesTags"
                            label="label"
                            @search="searchTag">
                            <template v-slot:no-options>
                                Aucune option trouvée
                            </template>
                        </v-select>
                    </div>
                </div>
                <div class="w-full flex items-center">
                    <div class="border-l-1 border-gray-300 dark:border-gray-400 ml-0.5 px-2 text-xs text-gray-400 dark:text-gray-500">Allergènes</div>
                    <div class="block w-full">
                        <v-select
                            v-model="inputDishesAllergens[index]"
                            multiple
                            class="admin-dishes-input-dish-tag block w-full border px-0.5 py-0.5 border-gray-100 shadow-xs rounded-md text-xs text-[10px] leading-none focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                            :options="autocompleteDishesAllergens"
                            label="label">
                            <template v-slot:no-options>
                                Aucune option trouvée
                            </template>
                        </v-select>
                    </div>
                </div>
            </div>
            <button
                type="button"
                v-if="!dish && index !== inputDishes.length && inputDishes.length !== 1"
                @click="removeDish(index)"
                class="cursor-pointer text-xs ml-2 inline-flex items-center text-red-800 dark:text-red-800 p-1.5 border border-gray-200 dark:border-gray-500 leading-4 font-medium rounded-md shadow-xs text-green bg-white dark:bg-gray-700">
                <i class="fas fa-trash-alt"></i>
            </button>
            <button
                type="button"
                v-if="dish"
                @click="addDish(index + 1)"
                class="cursor-pointer text-xs ml-2 inline-flex items-center text-gray-700 dark:text-gray-400 p-1.5 border border-gray-200 dark:border-gray-500 leading-4 font-medium rounded-md shadow-xs text-green bg-white dark:bg-gray-700">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
</template>

<script>
import { reduce } from 'lodash';
import vSelect from 'vue-select';

export default {
    
    name: "AdminDishesInput",

    components: {
        vSelect,
    },

    props: {
        name: {
            type: String,
            required: true,
        },
        nameTags: {
            type: String,
            required: true,
        },
        nameAllergens: {
            type: String,
            required: true,
        },
        dishes: {
            type: Array,
            required: true,
        },
        dishesTags: {
            type: Array,
            required: true,
        },
        dishesAllergens: {
            type: Array,
            required: true,
        },
        autocompleteDishes: {
            type: Array,
            required: true,
        },
        autocompleteDishesTags: {
            type: Object,
            required: true,
        },
        autocompleteDishesAllergens: {
            type: Array,
            required: true,
        },
        placeholder: {
            type: String,
            required: false,
            default: '',
        },
    },

    data: function () {
        let filteredDishes = this.autocompleteDishes;
        let filteredDishesTags = this.autocompleteDishesTags;
        let inputDishes = this.dishes.length ? this.dishes : [''];
        let inputDishesTags = this.dishesTags.length ? this.dishesTags : [];
        inputDishesTags = inputDishesTags.map((tags) => {
            return tags.map((tag) => {
                return filteredDishesTags.find(t => t.value === tag)
            });
        });
        let inputDishesAllergens = this.dishesAllergens.map((allergens) => {
            return allergens.map((allergen) => {
                return this.autocompleteDishesAllergens.find(a => a.value === allergen)
            }).filter(Boolean);
        });
        return {
            filteredDishes: filteredDishes,
            filteredDishesTags: filteredDishesTags,
            inputDishes: inputDishes,
            inputDishesTags: inputDishesTags,
            inputDishesAllergens: inputDishesAllergens,
        }
    },

    methods: {
        search: function (text) {
            this.filteredDishes = this.autocompleteDishes;
        },
        searchTag: function (text) {
            this.filteredDishesTags = this.autocompleteDishesTags;
        },
        // Tags and allergens are matched to dishes by index, so they must move together
        alignDishesAttributes: function () {
            [this.inputDishesTags, this.inputDishesAllergens].forEach((list) => {
                for (let i = 0; i < this.inputDishes.length; i++) {
                    if (!list[i]) {
                        list[i] = [];
                    }
                }
            });
        },
        addDish: function (index) {
            this.alignDishesAttributes();
            this.inputDishes.splice(index, 0, '');
            this.inputDishesTags.splice(index, 0, []);
            this.inputDishesAllergens.splice(index, 0, []);
        },
        removeDish: function (index) {
            this.alignDishesAttributes();
            this.inputDishes.splice(index, 1);
            this.inputDishesTags.splice(index, 1);
            this.inputDishesAllergens.splice(index, 1);
        },
        reduce: function (input) {
            return input.map((tags) => {
                return tags.map((tag) => {
                    return tag.value;
                });
            });
        },
    },
};
</script>
<style src="vue-select/dist/vue-select.css"></style>

<style>
@reference "tailwindcss";

.admin-dishes-input .vs__search::placeholder,
.admin-dishes-input .vs__dropdown-toggle,
.admin-dishes-input .vs__selected,
.admin-dishes-input .vs__dropdown-option {
    @apply border-none dark:text-gray-400 text-sm leading-none my-0 py-0.5;
}

.admin-dishes-input .admin-dishes-input-dish-tag .vs__selected-options :not(#\#).vs__selected {
    @apply mt-0 text-[10px];
}

.admin-dishes-input .vs__selected-options {
    @apply flex-nowrap;
}

.admin-dishes-input .vs--multiple .vs__selected-options :not(#\#).vs__selected {
    @apply mx-0.5 px-0.5 bg-gray-200 text-gray-600 dark:bg-gray-400 dark:text-gray-800;
}

.admin-dishes-input .vs__selected-options :not(#\#).vs__selected .vs__deselect {
    @apply ml-1;
}

.admin-dishes-input .vs__search {
    @apply bg-white dark:bg-gray-700 text-sm leading-none mt-0 pt-0.5;
}

.admin-dishes-input .vs__dropdown-menu {
    @apply bg-white dark:text-gray-400 dark:bg-gray-800 border border-gray-200 dark:border-gray-50 text-sm leading-none;
}

.admin-dishes-input .vs__dropdown-option,
.admin-dishes-input *:not(#\#).vs__dropdown-option {
    @apply py-1 px-2;
}

.admin-dishes-input .vs__dropdown-option--selected {
    @apply bg-gray-300 text-black dark:bg-gray-900 dark:text-gray-400 text-sm leading-none;
}

.admin-dishes-input .vs__dropdown-option--highlight {
    @apply bg-gray-200 text-black dark:bg-gray-700 dark:text-gray-400 text-sm leading-none;
}

.admin-dishes-input .vs__actions,
.admin-dishes-input *:not(#\#).vs__actions {
    @apply py-0 pl-1;
}

.admin-dishes-input .vs__open-indicator,
.admin-dishes-input svg:not(#\#).vs__open-indicator {
    @apply hidden
}
.admin-dishes-input .vs__clear {
    @apply dark:fill-gray-400;
}
</style>