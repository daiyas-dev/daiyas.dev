<template>
    <form id="this.$options.name">
        <p><a href="./client/win/DaiyasClientSetup.exe" download="ダイヤスクライアントセットアップ.exe">CTI連携用クライアントのダウンロード</a></p>
    </form>
</template>

<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI00010",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "メニューパネル",
            noViewModel: true,
            viewModel: {
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
        },
    }
}
</script>
