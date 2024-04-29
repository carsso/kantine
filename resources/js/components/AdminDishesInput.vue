<template>
    <div class="admin-dishes-input">
        <AutoComplete
            multiple
            dropdown
            v-model="inputDishes"
            :suggestions="filteredDishes"
            @complete="search"
            :pt="{
                root: { class: 'block w-full' },
                input: { class: 'block w-full py-0.5 border border-gray-200 shadow-sm rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400' },
                loadingicon: { class: 'text-surface-500 dark:text-surface-0/70 absolute bottom-[0.4rem] right-[0.5rem] -mt-2 animate-spin' },
                token: { class: 'block w-full inline-flex flex-row-reverse items-center cursor-default' },
                tokenLabel: { class: 'grow' },
                dropdownButton: { root: { class: 'absolute items-center inline-flex bottom-[0rem] right-[0rem] px-2 pt-2' }, },
                removeTokenIcon: { class: 'shrink rounded-md leading-6 mr-2 w-4 h-4 transition duration-200 ease-in-out cursor-pointer' },
                panel: { class: 'bg-white dark:bg-gray-700 rounded-lg shadow px-4 py-1 text-center border-t-4 border-blue-500 max-h-[200px] overflow-auto' },
                item: { class: 'cursor-pointer' },
            }"
        />
        <input
            v-for="(dish, index) in inputDishes" :key="index"
            :id="name+'_'+index"
            type="hidden"
            :name="name+'[]'"
            v-model="inputDishes[index]" />
    </div>
</template>

<script>
import AutoComplete from 'primevue/autocomplete';

export default {
    
    name: "AdminDishesInput",

    components: {
        AutoComplete,
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
    },

    data: function () {
        let filteredDishes = this.autocompleteDishes;
        let inputDishes = this.dishes;
        return {
            filteredDishes: filteredDishes,
            inputDishes: inputDishes,
        }
    },

    methods: {
        search: function (event) {
            let value = event.query.trim();
            this.filteredDishes = [value].concat(this.autocompleteDishes.filter(item => item.toLowerCase().includes(value.toLowerCase().trim())));
        },
    },
};
</script>