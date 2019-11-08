<template>
    <div v-if="this.loading" :id=this.pageId>
        <div  class="PreLoad Loading" v-if="this.failed == null">
            <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>ロード中...
        </div>
        <div  class="PreLoad Failed" v-if="this.failed">
            <i class="fa fa-ban" style="font-size:24px"></i>アクセスエラー:{{this.uri}}
            <p v-show="this.message">{{message}}</p>
        </div>
    </div>
    <div v-else
        :id=this.pageId
        :pgId=this._pgId
        :dataUrl=this.dataUrl
        :title=this.title
        :labelCd=this.labelCd
        :labelCdNm=this.labelCdNm
        :callback=this.callback
        :uniqueId=this.uniqueId
        :isChild=this.isChild
        :isCodeOnly=this.isCodeOnly
        :showColumns=this.showColumns
        :is="this.component"
        :query=this.query
        :vm=this.viewModel></div>
</template>

<style scoped>
    .PreLoad {
        display: table-cell;
        height: 80vh;
        width: 100vw;
        text-align: center;
        vertical-align: middle;
    }

    .Loading {
        font-size: 24px;
        color: black;
    }

    .Failed {
        font-size: 24px;
        color: red;
        background-color: yellow;
    }
</style>

<script>
//ページコンポーネントをimport
import PID0001 from "@vcp/PID0001.vue";
import PID0002 from "@vcp/PID0002.vue";
import PID0101 from "@vcp/PID0101.vue";
import PID0102 from "@vcp/PID0102.vue";
import PID0201 from "@vcp/PID0201.vue";
import DAI01010 from "@vcp/DAI01010.vue";
import DAI01020 from "@vcp/DAI01020.vue";
import DAI01030 from "@vcp/DAI01030.vue";
import DAI01040 from "@vcp/DAI01040.vue";

import DAI07010 from "@vcp/DAI07010.vue";

//子画面表示確認
import CommonSelector from "@vcs/CommonSelector.vue";

export default {
    name: "PageSelector",
    data() {
        return {
            loading: true,
            uri: null,
            message: null,
            failed: null,
            component: null,
            viewModel: null,
        }
    },
    computed: {
        _pgId: function(){
            return this.pgId || this.$route.params.pgId || this.$route.path.replace(/^\/[^\/]+\//g, "");
        },
        pageId: function(){
            return "Page_" + (this.uniqueId || this._pgId)
        },
        query: function() {
            return $.extend(true, {}, this.$route.query, this.params);
        },
    },
    props: {
        pgId: String,
        dataUrl: String,
        title: String,
        labelCd: String,
        labelCdNm: String,
        callback: Function,
        uniqueId: String,
        isSelector: Boolean,
        isChild: Boolean,
        isCodeOnly: Boolean,
        showColumns: Array,
        noViewModel: Boolean,
        params: Object,
    },
    components: {
        //ページコンポーネントを登録
        DAI01010,
        DAI01020,
        DAI01030,
        DAI01040,
        DAI07010,
        //共通画面用Selector
        CommonSelector,
        //TODO: dummy || sample
        PID0001,
        PID0002,
        PID0101,
        PID0102,
        PID0201,
    },
    created: function () {
    },
    mounted: function () {
        var vue = this;
        var component = vue.isSelector ? "CommonSelector" : vue._pgId;
        var componentVue = vue.$options.components[component];

        //ページ切替通知
        if (vue.isChild != true && componentVue && componentVue.data().ScreenTitle) {
            vue.$root.$emit("setCurrentPage", componentVue.data().ScreenTitle);
        }

        if (component != "CommonSelector" && componentVue.data().noViewModel != true) {
            //ViewModelの構成を取得
            var uri = location.href.replace(location.hash, "") + vue._pgId + "/GetViewModel";
            axios.post(uri)
                .then(response => {
                    vue.viewModel = $.extend(true, vue.viewModel, response.data, vue.params);
                    vue.component = component;
                    vue.loading = false;
                })
                .catch(error => {
                    console.log(uri + " Error!");
                    console.log(error);
                    vue.failed = true;
                    vue.message = "(" + error + ")";
                });
        } else {
            vue.component = component;
            vue.loading = false;
        }
    },
    updated: function () {
        var vue = this;

        //ページ切替通知
        if (vue.isChild != true && vue.component && window[vue.component] && window[vue.component].ScreenTitle) {
            vue.$root.$emit("setCurrentPage", window[vue.component].ScreenTitle);
        }
    },
}
</script>
