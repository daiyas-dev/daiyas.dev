<template>
    <VueSelect
        :id=_id
        :ref=_id
        :vmodel=_vmodel
        :bind=_bind
        :buddy=_buddy
        uri="/Utilities/GetBushoList"
        :withCode="withCode || true"
        :customStyle=customStyle
        :onChangedFunc=onChangedFunc
        :hasNull=hasNull
        :disabled=disabled
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
        disabled: Boolean,
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

                if (info.isLogOn && (!vue.ready || vue._vmodel[vue._bind] != info.user.bushoCd)) {
                    vue.ready = info.isLogOn;
                    vue._vmodel[vue._bind] = info.user.bushoCd;
                    vue._vmodel[vue._buddy] = info.user.bushoNm;

                    var entity = _.find(vue.$refs.BushoCd.$data.entities, v => v.code == info.user.bushoCd);
                    if (vue.onChangedFunc) {
                        vue.onChangedFunc(info.user.bushoCd, entity);
                    }
                }
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
            vue.ready = true;
            if (vue._vmodel[vue._bind] != window.loginInfo.bushoCd) {
                vue._vmodel[vue._bind] = window.loginInfo.bushoCd;
                vue._vmodel[vue._buddy] = window.loginInfo.bushoNm;
                var entity = _.find(vue.$refs.BushoCd.$data.entities, v => v.code == info.user.bushoCd);
                if (vue.onChangedFunc) {
                    vue.onChangedFunc(window.loginInfo.bushoCd, entity);
                }
            }
        }
    },
    methods: {
        focus: function() {
            var vue = this;

            var select = $(vue.$el).find("select");
            if (select.is(":disabled")) {
                return false;
            } else {
                select.focus();
                return true;
            }
        },
    }
}
</script>
