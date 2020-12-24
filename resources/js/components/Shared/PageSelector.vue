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
        :selector=this.selector
        :customElement=this.customElement
        :customElementFunc=this.customElementFunc
        :customHeaderObjects=this.customHeaderObjects
        :customHeaderObjectsFunc=this.customHeaderObjectsFunc
        :showBushoSelector=this.showBushoSelector
        :selectBushoDefault=this.selectBushoDefault
        :customParams=this.customParams
        :uniqueId=this.uniqueId
        :isChild=this.isChild
        :isCodeOnly=this.isCodeOnly
        :showColumns=this.showColumns
        :is="this.component"
        :query=this.query
        :params=this.params
        :vm=this.viewModel>
    </div>
</template>

<style scoped>
    .PreLoad {
        display: table-cell;
        height: calc(80vh / var(--ratio));
        width: calc(100vw / var(--ratio));
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

import DAI00010 from "@vcp/DAI00010.vue";
import DAI00020 from "@vcp/DAI00020.vue";
import DAI01010 from "@vcp/DAI01010.vue";
import DAI01020 from "@vcp/DAI01020.vue";
import DAI01030 from "@vcp/DAI01030.vue";
import DAI01031 from "@vcp/DAI01031.vue";
import DAI01032 from "@vcp/DAI01032.vue";
import DAI01040 from "@vcp/DAI01040.vue";
import DAI01060 from "@vcp/DAI01060.vue";
import DAI01061 from "@vcp/DAI01061.vue";
import DAI01070 from "@vcp/DAI01070.vue";
import DAI01080 from "@vcp/DAI01080.vue";
import DAI01090 from "@vcp/DAI01090.vue";
import DAI01100 from "@vcp/DAI01100.vue";
import DAI01101 from "@vcp/DAI01101.vue";
import DAI01110 from "@vcp/DAI01110.vue";
import DAI01120 from "@vcp/DAI01120.vue";
import DAI01130 from "@vcp/DAI01130.vue";
import DAI01140 from "@vcp/DAI01140.vue";
import DAI01150 from "@vcp/DAI01150.vue";
import DAI01160 from "@vcp/DAI01160.vue";
import DAI01170 from "@vcp/DAI01170.vue";
import DAI01180 from "@vcp/DAI01180.vue";
import DAI01200 from "@vcp/DAI01200.vue";
import DAI01210 from "@vcp/DAI01210.vue";
import DAI01220 from "@vcp/DAI01220.vue";
import DAI01230 from "@vcp/DAI01230.vue";
import DAI01250 from "@vcp/DAI01250.vue";
import DAI01260 from "@vcp/DAI01260.vue";

import DAI02010 from "@vcp/DAI02010.vue";
import DAI02020 from "@vcp/DAI02020.vue";
import DAI02021 from "@vcp/DAI02021.vue";
import DAI02030 from "@vcp/DAI02030.vue";

import DAI03010 from "@vcp/DAI03010.vue";
import DAI03020 from "@vcp/DAI03020.vue";
import DAI03021 from "@vcp/DAI03021.vue";
import DAI03060 from "@vcp/DAI03060.vue";
import DAI03070 from "@vcp/DAI03070.vue";
import DAI03080 from "@vcp/DAI03080.vue";
import DAI03090 from "@vcp/DAI03090.vue";
import DAI03120 from "@vcp/DAI03120.vue";
import DAI03130 from "@vcp/DAI03130.vue";
import DAI03140 from "@vcp/DAI03140.vue";

import DAI04010 from "@vcp/DAI04010.vue";
import DAI04020 from "@vcp/DAI04020.vue";
import DAI04021 from "@vcp/DAI04021.vue";
import DAI04030 from "@vcp/DAI04030.vue";
import DAI04031 from "@vcp/DAI04031.vue";
import DAI04040 from "@vcp/DAI04040.vue";
import DAI04041 from "@vcp/DAI04041.vue";
import DAI04042 from "@vcp/DAI04042.vue";
import DAI04043 from "@vcp/DAI04043.vue";
import DAI04050 from "@vcp/DAI04050.vue";
import DAI04051 from "@vcp/DAI04051.vue";
import DAI04070 from "@vcp/DAI04070.vue";
import DAI04071 from "@vcp/DAI04071.vue";
import DAI04080 from "@vcp/DAI04080.vue";
import DAI04081 from "@vcp/DAI04081.vue";
import DAI04090 from "@vcp/DAI04090.vue";
import DAI04091 from "@vcp/DAI04091.vue";
import DAI04120 from "@vcp/DAI04120.vue";
import DAI04130 from "@vcp/DAI04130.vue";
import DAI04140 from "@vcp/DAI04140.vue";
import DAI04141 from "@vcp/DAI04141.vue";
import DAI04160 from "@vcp/DAI04160.vue";
import DAI04161 from "@vcp/DAI04161.vue";
import DAI04170 from "@vcp/DAI04170.vue";
import DAI04171 from "@vcp/DAI04171.vue";
import DAI04180 from "@vcp/DAI04180.vue";
import DAI04190 from "@vcp/DAI04190.vue";
import DAI04191 from "@vcp/DAI04191.vue";
import DAI04200 from "@vcp/DAI04200.vue";
import DAI04201 from "@vcp/DAI04201.vue";
import DAI04210 from "@vcp/DAI04210.vue";

import DAI05010 from "@vcp/DAI05010.vue";
import DAI05020 from "@vcp/DAI05020.vue";
import DAI05030 from "@vcp/DAI05030.vue";
import DAI05050 from "@vcp/DAI05050.vue";
import DAI05070 from "@vcp/DAI05070.vue";
import DAI05071 from "@vcp/DAI05071.vue";
import DAI05080 from "@vcp/DAI05080.vue";
import DAI05090 from "@vcp/DAI05090.vue";
import DAI05100 from "@vcp/DAI05100.vue";
import DAI05110 from "@vcp/DAI05110.vue";
import DAI05120 from "@vcp/DAI05120.vue";

import DAI05140 from "@vcp/DAI05140.vue";
import DAI05150 from "@vcp/DAI05150.vue";

import DAI06010 from "@vcp/DAI06010.vue";
import DAI06020 from "@vcp/DAI06020.vue";
import DAI06030 from "@vcp/DAI06030.vue";
import DAI06040 from "@vcp/DAI06040.vue";
import DAI06050 from "@vcp/DAI06050.vue";

import DAI07010 from "@vcp/DAI07010.vue";
import DAI07020 from "@vcp/DAI07020.vue";
import DAI07030 from "@vcp/DAI07030.vue";
import DAI07040 from "@vcp/DAI07040.vue";
import DAI07050 from "@vcp/DAI07050.vue";

import DAI08009 from "@vcp/DAI08009.vue";
import DAI08010 from "@vcp/DAI08010.vue";
import DAI08020 from "@vcp/DAI08020.vue";
import DAI08030 from "@vcp/DAI08030.vue";
import DAI08040 from "@vcp/DAI08040.vue";

import DAI10010 from "@vcp/DAI10010.vue";
import DAI10011 from "@vcp/DAI10011.vue";

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
        vm: Object,
        dataUrl: String,
        title: String,
        labelCd: String,
        labelCdNm: String,
        callback: Function,
        selector: Function,
        customElement: String,
        customElementFunc: Function,
        customHeaderObjects: Array,
        customHeaderObjectsFunc: Function,
        showBushoSelector: Boolean,
        selectBushoDefault: Boolean,
        customParams: Object,
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
        DAI00010,
        DAI00020,
        DAI01010,
        DAI01020,
        DAI01030,
        DAI01031,
        DAI01032,
        DAI01040,
        DAI01060,
        DAI01061,
        DAI01070,
        DAI01080,
        DAI01090,
        DAI01100,
        DAI01101,
        DAI01110,
        DAI01120,
        DAI01130,
        DAI01140,
        DAI01150,
        DAI01160,
        DAI01170,
        DAI01180,
        DAI01200,
        DAI01210,
        DAI01220,
        DAI01230,
        DAI01250,
        DAI01260,
        DAI02010,
        DAI02020,
        DAI02021,
        DAI02030,
        DAI03010,
        DAI03020,
        DAI03021,
        DAI03060,
        DAI03070,
        DAI03080,
        DAI03090,
        DAI03120,
        DAI03130,
        DAI03140,
        DAI04010,
        DAI04020,
        DAI04021,
        DAI04030,
        DAI04031,
        DAI04040,
        DAI04041,
        DAI04042,
        DAI04043,
        DAI04050,
        DAI04051,
        DAI04070,
        DAI04071,
        DAI04080,
        DAI04081,
        DAI04090,
        DAI04091,
        DAI04120,
        DAI04130,
        DAI04140,
        DAI04141,
        DAI04160,
        DAI04161,
        DAI04170,
        DAI04171,
        DAI04180,
        DAI04190,
        DAI04191,
        DAI04200,
        DAI04201,
        DAI04210,
        DAI05010,
        DAI05020,
        DAI05030,
        DAI05050,
        DAI05070,
        DAI05071,
        DAI05080,
        DAI05090,
        DAI05100,
        DAI05110,
        DAI05120,
        DAI05140,
        DAI05150,
        DAI06010,
        DAI06020,
        DAI06030,
        DAI06040,
        DAI06050,
        DAI07010,
        DAI07020,
        DAI07030,
        DAI07040,
        DAI07050,
        DAI08009,
        DAI08010,
        DAI08020,
        DAI08030,
        DAI08040,
        DAI10010,
        DAI10011,
        //共通画面用Selector
        CommonSelector,
        PID0001,
        PID0002,
        PID0101,
        PID0102,
        PID0201,
    },
    created: function () {
        var vue = this;
        vue.$on("setDialogTitle", args => vue.$parent.$emit("setDialogTitle", args));
        vue.$on("setDialogButtons", args => vue.$parent.$emit("setDialogButtons", args));
    },
    mounted: function () {
        var vue = this;
        var component = vue.isSelector ? "CommonSelector" : vue._pgId;
        var componentVue = vue.$options.components[component];

        if (!componentVue) {
            vue.$root.$emit("setTitle", "開発中");
            vue.$router.push("/UnderConstruction");
            return;
        }

        //tooltip消去
        $("body").find("[id^=tooltip]").tooltip("hide");

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
            vue.viewModel = vue.vm;
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
