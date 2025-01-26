import { createApp } from "vue";
import GlobalComponents from './globals'
import './bootstrap';
import.meta.glob(["../images/**", "../fonts/**"]);
import Particles from "@tsparticles/vue3";
import { loadFull } from "tsparticles";
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

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

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.VITE_REVERB_APP_KEY,
    wsHost: window.VITE_REVERB_HOST,
    wsPort: window.VITE_REVERB_PORT ?? 80,
    wssPort: window.VITE_REVERB_PORT ?? 443,
    forceTLS: (window.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.EchoMixin = {
    created() {
        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            this.echoState = window.Echo.connector.pusher.connection.state;
        });
        window.Echo.connector.pusher.connection.bind('connected', () => {
            this.echoState = window.Echo.connector.pusher.connection.state;
            this.echoErrors = [];
        });
        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            this.echoState = window.Echo.connector.pusher.connection.state;
            this.echoErrors.push({
                error: 'disconnected',
                context: {
                    datetime: new Date(),
                }
            });
        });
        window.Echo.connector.pusher.bind('subscription_error', (channel, statusCode) => {
            this.echoState = window.Echo.connector.pusher.connection.state;
            this.echoErrors.push({
                error: 'auth_error',
                context: {
                    channel: channel,
                    statusCode: statusCode,
                }
            });
        });
    },

    data() {
        return {
            pusher: window.Echo.connector.pusher,
            echoState: false,
            echoErrors: [],
        };
    },

    computed: {
        echoConnected() {
            return this.echoState === 'connected';
        },

        echoHasErrors() {
            return this.echoErrors.length > 0;
        },

        echoConnectedWihoutErrors() {
            return this.echoConnected && !this.echoHasErrors;
        },
    }
};

app.use(GlobalComponents);
app.use(Particles, {
    init: async engine => {
        await loadFull(engine);
    },
});
app.mixin(window.EchoMixin);
app.mount('#app');