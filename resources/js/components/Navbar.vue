<template>
    <Disclosure as="nav" class="bg-white dark:bg-gray-800 shadow" v-slot="{ open }">
        <div class="container-2xl 2xl:container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center text-gray-900 dark:text-white">
                        <a :href="homeRoute">
                            <strong v-if="isDev" class="bg-red-500 text-white px-2 mr-2">DEV</strong>
                            {{ appName }}
                        </a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a :href="route.route" v-for="(route, routeIdx) in routes" :key="routeIdx" class="border-indigo-500 text-gray-900 dark:text-white hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 text-sm font-medium" :class="route.active ? 'border-b-2' : 'hover:border-b-2'">
                            {{ route.name }}
                        </a>
                    </div>
                </div>
                <div class="hidden md:ml-6 md:flex md:space-x-8">
                    <span class="hidden md:ml-6 md:flex md:items-center">
                        <NavbarDarkmodeToggler :route="homeRoute"></NavbarDarkmodeToggler>
                    </span>
                    <a :href="route.route" v-for="(route, routeIdx) in routesLeft" :key="routeIdx" class="border-indigo-500 text-gray-900 dark:text-white hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 text-sm font-medium" :class="route.active ? 'border-b-2' : 'hover:border-b-2'">
                        <div class="flex items-center" v-if="route.account">
                            <div>
                                <img class="inline-block h-8 w-8 rounded-full" :src="route.account.gravatar_url" alt="">
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">{{ route.account.name }}</p>
                                <p class="text-xs font-medium text-gray-500">{{ route.name }}</p>
                            </div>
                        </div>
                        <span v-else>{{ route.name }}</span>
                    </a>
                </div>
                <div class="-mr-2 flex items-center md:hidden">
                    <!-- Mobile menu button -->
                    <DisclosureButton class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
                        <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
                    </DisclosureButton>
                </div>
            </div>
        </div>

        <DisclosurePanel class="md:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <DisclosureButton as="a" :href="route.route" v-for="(route, routeIdx) in routes" :key="routeIdx" class="border-indigo-500 text-indigo-700 dark:text-white hover:text-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white block pl-3 pr-4 py-2 text-base font-medium" :class="route.active ? 'border-l-4' : ''">
                    {{ route.name }}
                </DisclosureButton>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <NavbarDarkmodeToggler :route="homeRoute"></NavbarDarkmodeToggler>
                </div>
                <div class="mt-3 space-y-1">
                    <DisclosureButton as="a" :href="route.route" v-for="(route, routeIdx) in routesLeft" :key="routeIdx" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-white" :class="route.active ? 'border-l-4' : ''">
                        {{ route.name }}
                    </DisclosureButton>
                </div>
            </div>
        </DisclosurePanel>
    </Disclosure>
</template>

<script>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import { Bars3Icon, XMarkIcon } from "@heroicons/vue/24/outline";


export default {
    name: "Navbar",

    components: {
        Disclosure,
        DisclosureButton,
        DisclosurePanel,
        Menu,
        MenuButton,
        MenuItem,
        MenuItems,
        Bars3Icon,
        XMarkIcon,
    },

    props: {
        appName: {
            type: String,
            default: "",
        },
        isDev: {
            type: Boolean,
            default: false,
        },
        isAuthenticated: {
            type: Boolean,
            default: false,
        },
        routes: {
            type: Array,
            required: true,
        },
        routesLeft: {
            type: Array,
            required: true,
        },
        homeRoute: {
            type: String,
            required: true,
        },
    },
};
</script>
