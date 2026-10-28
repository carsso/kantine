<template>
    <div ref="frame" class="theme-preview relative aspect-video overflow-hidden bg-[#000E9C]">
        <!-- Zooming crops the center of the dashboard, so particles get bigger without getting denser. -->
        <div class="absolute top-1/2 left-1/2 w-[1920px] h-[1080px]" :style="{ transform: `translate(-50%, -50%) scale(${scale * zoom})` }">
            <vue-particles v-if="visible && options" :id="id" :options="previewOptions" class="w-full h-full" />
            <div class="absolute left-24 top-1/2 -translate-y-1/2 font-['Source_Sans_3'] text-white text-[160px] font-black">Menu</div>
        </div>
    </div>
</template>

<script>
export default {

    name: "ThemePreview",

    props: {
        id: {
            type: String,
            required: true,
        },
        options: {
            type: Object,
            required: false,
            default: null,
        },
        zoom: {
            type: Number,
            required: false,
            default: 1,
        },
    },

    data() {
        return {
            scale: 0,
            visible: false,
        };
    },

    computed: {
        previewOptions() {
            // Drawn at dashboard size then scaled down, so densities and sizes match the real screen.
            return {
                ...this.options,
                fullScreen: { enable: false },
                detectRetina: false,
                fpsLimit: 30,
            };
        },
    },

    mounted() {
        this.resizeObserver = new ResizeObserver(([entry]) => {
            this.scale = entry.contentRect.width / 1920;
        });
        this.resizeObserver.observe(this.$refs.frame);

        // Only the previews on screen are animated.
        this.intersectionObserver = new IntersectionObserver(([entry]) => {
            this.visible = entry.isIntersecting;
        });
        this.intersectionObserver.observe(this.$refs.frame);
    },

    beforeUnmount() {
        this.resizeObserver.disconnect();
        this.intersectionObserver.disconnect();
    },
};
</script>

<style>
.theme-preview canvas {
    width: 100% !important;
    height: 100% !important;
}
</style>
