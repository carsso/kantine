<template>
    <div class="page-refresher"></div>
</template>

<script>
    export default {
        name: 'PageRefresher',

        props: {
            date: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
            };
        },
        
        created() {
            Echo.channel(`public`)
                .listen('MenuUpdatedEvent', (e) => {
                    if(e.menu.date == this.date) {
                        window.location.reload();
                    }
                })
                .listen('DashboardRefreshEvent', (e) => {
                    window.location.reload();
                });
        },
    }
</script>