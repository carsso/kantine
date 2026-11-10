<template>
    <vue-particles v-if="currentOptions" :id="id" :options="currentOptions" />
</template>

<script>
import { resolveParticleIcons } from '../particles/icons';
import { scaleGravity } from '../particles/resolution';

// Under this many frames per second the screen is struggling.
const SLOW_FPS = 20;
// Seconds in a row under SLOW_FPS before degrading one level.
const SLOW_SECONDS = 10;
const STORAGE_KEY = 'kantine.particles.level';
const STORAGE_TTL_MS = 24 * 60 * 60 * 1000;
// Canvas pixels per CSS pixel once the screen struggles: blurrier, but a quarter of the pixels to paint.
const LOW_RESOLUTION = 0.5;

/**
 * Particles of the dashboard, with a watchdog for the weakest screens: when the page stays
 * slow, the particles are halved and drawn at half resolution, then dropped. The level is
 * kept for a day, per style, so a reload does not freeze the screen again for ten seconds.
 * Devices with few cores or little memory start halved. Previews from the admin show the full
 * style: no watchdog, nothing read from or written to the stored level.
 */
export default {
    name: 'DashboardParticles',

    props: {
        id: {
            type: String,
            default: 'tsparticles',
        },
        options: {
            type: Object,
            required: true,
        },
        preview: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            level: this.preview ? 0 : Math.max(storedLevel(this.storageKey()), isWeakDevice() ? 1 : 0),
        };
    },

    computed: {
        currentOptions() {
            if (this.level >= 2) {
                return null;
            }
            const options = resolveParticleIcons(this.options);

            return this.level === 1 ? scaleGravity({ ...halved(options), resolution: LOW_RESOLUTION }, LOW_RESOLUTION) : options;
        },
    },

    mounted() {
        if (!this.preview) {
            this.watchFrameRate();
        }
    },

    beforeUnmount() {
        this.stopped = true;
        document.removeEventListener('visibilitychange', this.onVisibilityChange);
    },

    methods: {
        storageKey() {
            return `${STORAGE_KEY}.${this.options.name}`;
        },

        watchFrameRate() {
            let frames = 0;
            let slowSeconds = 0;
            let windowStart = performance.now();
            // requestAnimationFrame pauses in a hidden tab: that window says nothing about the screen.
            let hiddenDuringWindow = document.hidden;
            this.onVisibilityChange = () => {
                hiddenDuringWindow = true;
            };
            document.addEventListener('visibilitychange', this.onVisibilityChange);

            const tick = (now) => {
                if (this.stopped || this.level >= 2) {
                    return;
                }
                frames++;
                const elapsed = now - windowStart;
                if (elapsed >= 1000) {
                    if (!hiddenDuringWindow) {
                        // A frozen screen shows one frame every few seconds: each of them counts.
                        slowSeconds = frames / (elapsed / 1000) < SLOW_FPS ? slowSeconds + Math.floor(elapsed / 1000) : 0;
                    }
                    hiddenDuringWindow = document.hidden;
                    frames = 0;
                    windowStart = now;
                    if (slowSeconds >= SLOW_SECONDS) {
                        slowSeconds = 0;
                        this.degrade();
                    }
                }
                requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },

        degrade() {
            this.level++;
            console.warn(`Dashboard too slow, particles ${this.level === 1 ? 'halved' : 'dropped'}`);
            try {
                localStorage.setItem(this.storageKey(), JSON.stringify({ level: this.level, at: Date.now() }));
            } catch {
                // Private mode or storage disabled: the level only lasts the page.
            }
        },
    },
};

function isWeakDevice() {
    // deviceMemory only exists in Chromium, in GB and capped at 8.
    return (navigator.hardwareConcurrency ?? 8) <= 2 || (navigator.deviceMemory ?? 8) <= 2;
}

function storedLevel(key) {
    try {
        const stored = JSON.parse(localStorage.getItem(key));
        if (stored && Date.now() - stored.at < STORAGE_TTL_MS) {
            return stored.level;
        }
    } catch {
        // Nothing stored, or storage disabled.
    }

    return 0;
}

function halved(options) {
    const half = (value) => Math.max(1, Math.ceil(value / 2));
    const halveCount = (particles) => {
        if (particles?.number?.value) {
            particles.number.value = half(particles.number.value);
        }
        Object.values(particles?.groups ?? {}).forEach(halveCount);
    };

    halveCount(options.particles);
    // Emitters often send one particle at a time: waiting twice as long halves them all.
    [options.emitters ?? []].flat().forEach((emitter) => {
        const delay = emitter.rate?.delay ?? 0.1;
        emitter.rate = { ...emitter.rate, delay: typeof delay === 'object' ? { min: delay.min * 2, max: delay.max * 2 } : delay * 2 };
    });

    return options;
}
</script>
