import { createApp } from "vue";
import GlobalComponents from './globals'
import './bootstrap';
import.meta.glob(["../images/**", "../fonts/**"]);
import Particles from "@tsparticles/vue3";
import { loadFull } from "tsparticles";

const app = createApp({
    data() {
        return {
            currentDarkmode: document.documentElement.dataset.bsColorScheme === 'dark',
        }
    },
    watch: {
        currentDarkmode(newValue) {
            document.documentElement.dataset.bsColorScheme = newValue ? 'dark' : 'light';
            document.documentElement.className = newValue ? 'dark' : 'light';
        }
    },

	methods: {
        round(value, decimals) {
            if (!value) {
                value = 0
            }

            if (!decimals) {
                decimals = 0
            }

            value = Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals)
            return value
        }
	}
});

window.Vue = app;

app.config.globalProperties.window = window;

app.use(GlobalComponents);
app.use(Particles, {
    init: async engine => {
        await loadFull(engine);
    },
});
app.mount('#app');