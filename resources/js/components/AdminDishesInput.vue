<template>
    <div class="admin-dishes-input">
        <input
            v-for="(dish, index) in inputDishes" :key="index"
            :id="name+'_'+index"
            type="hidden"
            :name="name+'[]'"
            v-model="inputDishes[index]" />
        <div
            v-for="(dish, index) in inputDishes" :key="index" class="flex items-center mb-1">
            <span v-if="specialIndexes && Object.keys(specialIndexes).find(key => inputDishes.length + specialIndexes[key] === index)" class="text-gray-500 text-xs mr-2">
                {{ Object.keys(specialIndexes).find(key => inputDishes.length + specialIndexes[key] === index) }}
            </span>
            <v-select
                v-model="inputDishes[index]"
                taggable
                class="block w-full border border-gray-200 shadow-sm rounded-md text-sm leading-none focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400"
                :options="filteredDishes"
                @search="search">
                <template v-slot:option="option">
                    {{ option.label }}
                </template>
                <template v-slot:no-options>
                    Aucune option trouvée
                </template>
            </v-select>
            <button
                type="button"
                v-if="!dish && index !== inputDishes.length && inputDishes.length !== 1"
                @click="inputDishes.splice(index, 1)"
                class="text-xs ml-2 inline-flex items-center text-red-800 dark:text-red-800 px-1 py-1 border border-gray-200 dark:border-gray-500 leading-4 font-medium rounded-md shadow-sm text-green bg-white dark:bg-gray-700">
                <i class="fas fa-trash-alt"></i>
            </button>
            <button
                type="button"
                v-if="dish"
                @click="inputDishes.splice(index + 1, 0, '')"
                class="text-xs ml-2 inline-flex items-center text-gray-700 dark:text-gray-400 px-1 py-1 border border-gray-200 dark:border-gray-500 leading-4 font-medium rounded-md shadow-sm text-green bg-white dark:bg-gray-700">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
</template>

<script>
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
        dishes: {
            type: Array,
            required: true,
        },
        autocompleteDishes: {
            type: Array,
            required: true,
        },
        specialIndexes: {
            type: Object,
            required: false,
            default: () => {},
        },
    },

    data: function () {
        let filteredDishes = this.autocompleteDishes;
        let inputDishes = this.dishes;
        return {
            filteredDishes: filteredDishes,
            inputDishes: inputDishes.length ? inputDishes : [''],
        }
    },

    methods: {
        search: function (text) {
            this.filteredDishes = this.autocompleteDishes;
        },
    },
};
</script>
<style src="vue-select/dist/vue-select.css"></style>

<style>
.admin-dishes-input .vs__search::placeholder,
.admin-dishes-input .vs__dropdown-toggle,
.admin-dishes-input .vs__selected,
.admin-dishes-input .vs__dropdown-option {
    @apply border-none dark:text-gray-400 text-sm leading-none my-0 py-0.5;
}

.admin-dishes-input .vs__selected-options {
    @apply flex-nowrap;
}

.admin-dishes-input .vs__search {
    @apply bg-white dark:bg-gray-700 text-sm leading-none mt-0 pt-0.5;
}

.admin-dishes-input .vs__dropdown-menu {
    @apply bg-white dark:text-gray-400 dark:bg-gray-800 border border-gray-200 dark:border-gray-50 text-sm leading-none;
}

.admin-dishes-input .vs__dropdown-option {
    @apply py-1;
}

.admin-dishes-input .vs__dropdown-option--selected {
    @apply bg-gray-300 text-black dark:bg-gray-900 dark:text-gray-400 text-sm leading-none;
}

.admin-dishes-input .vs__dropdown-option--highlight {
    @apply bg-gray-200 text-black dark:bg-gray-700 dark:text-gray-400 text-sm leading-none;
}

.admin-dishes-input .vs__actions {
    @apply py-0 pr-1;
}

.admin-dishes-input .vs__open-indicator {
    @apply hidden;
}
.admin-dishes-input .vs__clear {
    @apply dark:fill-gray-400;
}
</style>