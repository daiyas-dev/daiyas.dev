<template>
    <VueSelect
        :id=_id
        :vmodel=_vmodel
        :bind=_bind
        :buddy=_buddy
        uri="/Utilities/GetBushoList"
        :withCode="withCode || true"
        :customStyle=customStyle
        :onChangedFunc=onChangedFunc
        :hasNull="hasNull"
    />
</template>

<style>
</style>

<script>
import VueSelect from "@vcs/VueSelect.vue";

export default {
    name: "VueSelectBusho",
    components: {
        "VueSelect": VueSelect,
    },
    data() {
        return {
            ready: false,
        }
    },
    props: {
        title: String,
        id: String,
        bind: String,
        vmodel: Object,
        hasNull: Boolean,
        uri: String,
        params: Object,
        list: Array,
        func: Function,
        onChangedFunc: Function,
        withCode: Boolean,
        withoutLogin: Boolean,
        customStyle: String,
    },
    computed: {
        _id: function() {
            return this.id || "BushoCd";
        },
        _vmodel: function() {
            return this.vmodel || this.$parent.viewModel;
        },
        _bind: function() {
            return this.bind || "BushoCd";
        },
        _buddy: function() {
            return this.buddy || "BushoNm";
        },
    },
    created: function () {
        var vue = this;

        if (!vue.withoutLogin) {
            vue.$root.$on("logOn", info => {
                vue.ready = true;
                vue._vmodel[vue._bind] = info.user.bushoCd;
            });
            vue.$root.$on("logOff", info => {
                vue.ready = false;
                vue._vmodel[vue._bind] = null;
            });
        }
    },
    mounted: function () {
        var vue = this;

        if (!vue.withoutLogin && window.loginInfo && window.loginInfo.bushoCd) {
            vue._vmodel[vue._bind] = window.loginInfo.bushoCd;
            vue.ready = true;
        }
    },
    methods: {

    }
}
</script>
