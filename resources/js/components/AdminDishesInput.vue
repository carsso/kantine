<template>
    <div class="admin-dishes-input">
        <div
            class="flex"
            v-for="(dish, index) in inputDishes" :key="index">
            <input
                :id="name+'_'+index"
                type="text"
                :name="name+'[]'"
                v-model="inputDishes[index]"
                v-on:input="updateInputs(index, true)"
                v-on:focus="updateInputs(index, true)"
                v-on:focusout="updateInputs(index, false, 300)"
                class="block w-full py-0.5 my-2 border border-gray-200 shadow-sm rounded-md text-sm focus:z-10 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-500 dark:text-gray-400">

            <aside 
                :id="'aside_'+name+'_'+index"
                class="absolute z-10 flex flex-col items-start w-64 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-500 rounded-md mt-10 text-sm"
                role="menu" aria-labelledby="menu-heading"
                v-if="showAutoComplete[index] === true && filterDishes(inputDishes[index]).length > 0">
                <ul class="flex flex-col w-full">
                    <li
                        class="p-0.5 hover:bg-blue-600 hover:text-white focus:bg-blue-600 focus:text-white focus:outline-none "
                        v-for="(item, _idx) in filterDishes(inputDishes[index])"
                        :key="_idx"
                        v-on:click="setInput(index, item)">
                        {{ item }}
                    </li>
                </ul>
            </aside>
        </div>
    </div>
</template>

<script>

export default {
    
    name: "AdminDishesInput",

    components: {
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
        let inputDishes = this.dishes;
        inputDishes.push('');
        let showAutoComplete = [];
        for(let i = 0; i < inputDishes.length; i++) {
            showAutoComplete[i] = false;
        }
        return {
            inputDishes: inputDishes,
            showAutoComplete: showAutoComplete,
            timeout: null,
        }
    },

    methods: {
        filterDishes: function (value) {
            return this.autocompleteDishes.filter(item => item.toLowerCase().includes(value.toLowerCase())).slice(0, 10);
        },
        
        setInput: function (index, value) {
            this.inputDishes[index] = value;
            this.updateInputs(index);
        },
        updateInputs: function (index, toggle = false, delay = 0) {
            clearTimeout(this.timeout);
            if(delay > 0) {
                this.timeout = setTimeout(() => {
                    this.updateInputs(index, toggle);
                }, delay);
                return;
            }
            if(this.inputDishes[this.inputDishes.length - 1] === ''
                && this.inputDishes[this.inputDishes.length - 2] === '') {
                this.inputDishes.pop()
            }
            if(this.inputDishes[this.inputDishes.length - 1] !== '') {
                this.inputDishes.push('')
            }
            for(let i = 0; i < this.inputDishes.length; i++) {
                if(i !== index) {
                    this.showAutoComplete[i] = false;
                } else {
                    this.showAutoComplete[i] = toggle;
                }
            }
        }
    },
};
</script>