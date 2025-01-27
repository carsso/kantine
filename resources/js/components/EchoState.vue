<template>
    <div class="echo-state fixed right-3 bottom-3 z-9999 text-xs"
        :title="'État du websocket : ' + $root.echoState">
        <i v-if="$root.echoState === 'connected'"
            class="fa-regular fa-circle text-[#A6D64D]"></i>
        <i v-else-if="$root.echoState === 'connecting'"
            class="fa-duotone fa-solid fa-spinner-third fa-spin text-[#FFD124]"></i>
        <i v-else
            class="fa-regular fa-circle text-[#ED733D]"></i>
    </div>
</template>

<script>
    export default {
        name: 'EchoState',
    }
</script>