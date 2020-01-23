import _ from "lodash";
window._ = _;
//lodash isEmpty extension
window._.isEmptyEx = v => v == undefined || (v + "").length == 0;

require('./bootstrap');

require("@/common_dialogs.js");
require("@/common_downloads.js");
require("@/common_uploads.js");
require("@/common_pdfviewer.js");

require("jquery-contextmenu");

import Vue from "vue";

import moment from "moment"
moment.locale("ja");
window.moment = moment;

import { Decimal } from "decimal.js";
window.Decimal = Decimal;

import Algebra from "algebra.js";
window.Algebra = Algebra;

import printJS from "./print.js";
window.printJS = printJS;

window.Dropzone = require("dropzone");

window.Moji = require("moji");

//int only directive
import onlyInt from "vue-input-only-number";
Vue.use(onlyInt);

import LogonForm from "@vcs/LogonForm.vue";
import TopMenu      from "@vcs/TopMenu.vue";
import PageDialog   from "@vcs/PageDialog.vue";
import AppHeader from "@vcs/AppHeader.vue";
import AppFooter from "@vcs/AppFooter.vue";
import CtiReceiver from "@vcs/CtiReceiver.vue";

//PqGrid
import pqgrid from "pqgrid";
window.pq = pqgrid;

pq.aggregate.IntegerTotal = function(arr, col) {
    return pq.formatNumber(pq.aggregate.sum(arr, col), "#,##0");
};
pq.aggregate.FloatTotal = function(arr, col) {
    return pq.formatNumber(pq.aggregate.sum(arr, col), "##,###.0");
};
//localize
import "pqgrid/localize/pq-localize-ja.js";

//vue-router
import VueRouter from "vue-router"
Vue.use(VueRouter);

import deparam from "jquery-deparam";
$.deparam = deparam;

import { diff, addedDiff, deletedDiff, updatedDiff, detailedDiff } from "deep-object-diff";
window.diff = diff;
window.addedDiff = addedDiff;
window.deletedDiff = deletedDiff;
window.updatedDiff = updatedDiff;
window.detailedDiff = detailedDiff;

//route定義
import routes from "@/routes.js"
const router = new VueRouter({
    mode: "hash",
    base: __dirname,
    routes: routes,
    stringifyQuery: params => _.isEmpty(params) ? "" : ("?" + $.param(params)),
    parseQuery: query =>  $.deparam(query),
});
router.beforeEach((to, from, next) => {
    if (!!window.VueApp) {
        //ユーザIDを付加
        var userId = window.VueApp.$refs.LogonForm.$data.user.uid;
        if (!!userId && !to.query["userId"]) {
            to.query["userId"] = userId;
            router.push({
                path: to.path,
                query: to.query,
            })
        } else {
            next();
        }
    } else {
        next();
    }
});

//HTML5 tag ignore
Vue.config.ignoredElements = [
    "menu",
    "command",
];

//Vueアプリ生成
var initVueApp = () => {
    const VueApp = new Vue({
        name: "main",
        el: "#vue-app",
        router: router,
        components: {
            "TopMenu": TopMenu,
            "LogonForm": LogonForm,
            "PageDialog": PageDialog,
            "AppHeader": AppHeader,
            "AppFooter": AppFooter,
            "CtiReceiver": CtiReceiver,
        },
        data: {
            //resize検知用
            width: window.innerWidth,
            height: window.innerHeight,
            move: {
                x: null,
                y: null
            },
            breadCrumbs: [],
        },
        methods: {
            createInstance: function (obj) {
                return Vue.extend(obj);
            },
            adjustContentHeight: function () {
                $(".body-content").height(
                    $("#vue-app").height() - _.sum($("header, footer").map((i, el) => $(el).outerHeight(true)))
                );
            },
            setWindowSize: _.debounce(function () {
                //contentのサイズ調整
                this.adjustContentHeight();

                //リサイズ通知
                this.$emit("resize", this.$data);
            }, 0),
            touchStart: _.debounce(function (event) {
                this.move.x = event.clientX;
                this.move.y = event.clientY;
            }),
            touchMove: _.debounce(function (event) {
                if (!this.move.x || !this.move.y) {
                    return;
                }

                var xDis = Math.abs(this.move.x - event.clientX);
                var yDis = Math.abs(this.move.y - event.clientY);

                //TODO: SideMenuの表示判定
                if (this.move.x < 50 && xDis > 20) {
                    //SideMenuへ通知
                    this.$emit("showSideMenu", this.$data);
                }

                this.move.x = null;
                this.move.y = null;
            }),
        },
        created: function () {
            //set event listener
            window.addEventListener("resize", this.setWindowSize, false);
            document.addEventListener("mousedown", this.touchStart, false);
            document.addEventListener("mouseup", this.touchMove, false);
            document.addEventListener("touchstart", this.touchStart, false);
            document.addEventListener("touchmove", this.touchMove, false);
        },
        mounted: function () {
            //contentのサイズ調整
            this.adjustContentHeight();

            //システム名
            this.$emit("setSystemName", "xxxxxxシステム");
        },
        beforeDestroy: function () {
            window.removeEventListener("resize", this.setWindowSize, false);
        },
    });

    window.VueApp = VueApp;
};

//事前データ取得
var loadingContents = $("<div>")
    .append($("<div class='mb-3'>").append('<img src="/images/daiyas256.png" alt="ロゴ" style="width: 60px; height: 60px;">ダイヤスクライアント'))
    .append($("<div>").append("<i class='fa fa-spinner fa-spin fa-lg' style='font-size: 24px; margin-right: 5px;'></i><span class='ml-1'>データ取得中…</span>"))
    .append($("<div>").append('<progress class="w-100" value="0" max="100">'))
    .get(0).outerHTML
    ;

var loadingDialog = $.dialogProgress({
    contents: loadingContents,
    hide: { effect: "fade", duration: 1000 },
});

var watcher = res => {
    var max = loadingDialog.find("progress").prop("max") * 1;
    var val = loadingDialog.find("progress").prop("value") * 1;
    val += max / targets.length;
    loadingDialog.find("progress").prop("value", val);

    return res;
};

var targets = [
    //部署マスタ
    window.axios.post("/Utilities/GetBushoList").then(res => watcher(res)),
    //担当者マスタ
    window.axios.post("/Utilities/GetTantoList").then(res => watcher(res)),
    //各種テーブル
    window.axios.post("/Utilities/GetCodeList").then(res => watcher(res)),
    //金融機関マスタ
    window.axios.post("/Utilities/GetBankList").then(res => watcher(res)),
];

window.axios.all(targets)
.then(
    axios.spread((...res) => {
        loadingDialog.find("i").attr("class", "fa fa-check fa-lg");
        loadingDialog.find("span").text("データ取得完了");

        setTimeout(() => {
            //成功時Vueアプリ生成
            initVueApp();

            loadingDialog.dialog("close");
        }, 1000);
    })
)
.catch(err => {
    console.log(err);
    loadingDialog.dialog("close");

    //完了ダイアログ
    $.dialogErr({
        title: "異常終了",
        contents: "DB検索に失敗しました<br/>",
    });
});
