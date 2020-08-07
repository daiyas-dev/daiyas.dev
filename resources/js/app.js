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
var editKeywords = keywords => {
    var ret = _(keywords.filter(k => !!k))
        .map(v => {
            return _.uniq(
                [
                    v,
                    Moji(v).convert("HG", "KK").toString(), // -> カタカナ
                    Moji(v).convert("KK", "HG").toString(), // -> ひらがな
                    Moji(v).convert("HG", "KK").convert("ZK", "HK").toString(), // -> 半角カナ
                    Moji(v).convert('ZE', 'HE').toString(), // -> 英数半角
                    Moji(v).convert('HE', 'ZE').toString(), // -> 英数全角
                ]
            );
        })
        .flatten()
        .uniq()
        .value()
        ;

    return ret;
};
window.editKeywords = editKeywords;
Vue.use(editKeywords);

//int only directive
import onlyInt from "vue-input-only-number";
Vue.use(onlyInt);

//string byte size directive
Vue.directive("maxBytes", {
    inserted(el, binding) {
        el.oninput = event => {
            var ret = event.target.value.split("")
                .reduce(
                    (a, c) => {
                        var b = encodeURIComponent(c).length > 1 ? 2 : 1;

                        if (a.bytes + b <= binding.value) {
                            a.str += c;
                            a.bytes += b;
                        } else {
                            a.bytes = binding.value;
                        }

                        return a;
                    },
                    { str: "", bytes: 0}
                );
            el.value = ret.str;
        };
    }
});

//set Kana directive
Vue.directive("setKana", {
    inserted(el, binding) {
        el.addEventListener(
            "input",
            _.debounce(event => {
                var target = event.data;

                var callback = binding.value;

                var hCnt;
                if (!!target) {
                    hCnt = Moji(target).filter("HE").toString().length + Moji(target).filter("HS").toString().length;

                    if (hCnt == target.length) {
                        console.log("setKana directive", target);
                        callback(target);
                    }
                }

            }, 100)
        );
        el.addEventListener(
            "compositionupdate",
            _.debounce(event => {
                var target = event.data;    //el.value;

                if (Moji(target).filter("HG").toString().length == target.length) {
                    console.log("setKana directive", target);
                    el.setAttribute("toKana", target);
                }
                if (Moji(target).filter("KK").toString().length == target.length) {
                    console.log("setKana directive", target);
                    el.setAttribute("toKana", target);
                }
                if (Moji(target).filter("ZE").toString().length == target.length) {
                    console.log("setKana directive", target);
                    el.setAttribute("toKana", target);
                }

            }, 100)
        );
        el.addEventListener(
            "compositionend",
            event => {
                var target = el.getAttribute("toKana");
                var data = event.data;
                el.setAttribute("toKana", "");

                console.log("setKana directive", target);

                if (target == "") return;

                var callback = binding.value;

                if (!!binding.modifiers.disabled) return;

                window.getKana(data, resData => {
                    console.log("getKana api ret", data, resData, binding);

                    window.getKana(target, resTarget => {
                        console.log("getKana api ret", target, resTarget, binding);

                        var result = !!resData && resData.startsWith(resTarget) ? resData : resTarget;

                        if (/\w/.test(data)){
                            result = "";
                        }
                        if (Moji(data).filter("ZE").toString().length == data.length) {
                            data = Moji(data).convert('ZE', 'HE').toString();
                            result = data;
                        }

                        if (data == "㈱") {
                            result = "ｶﾌﾞ";
                        }

                        result = binding.modifiers.full ? result : window.Moji(result).convert("ZK", "HK");
                        callback(result);
                    });
                });
            }
        );
    }
});

//currency-input
import VueCurrencyInput from "vue-currency-input";

const pluginOptions = {
    globalOptions: {
        currency: null,
        precision: 0,
        "allow-negative": true,
    }
}
Vue.use(VueCurrencyInput, pluginOptions);

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

//画面サイズ対応
var setZoom = () => {
    var baseH = 800;
    var ratio = 1;
    if (window.innerHeight < baseH) {
        ratio = window.innerHeight / baseH;
    }
    console.log("ratio", ratio);
    $("body").css("zoom", ratio * 100 + "%");
    document.body.style.setProperty("--ratio", ratio);
};
setZoom();
$(window).resize(setZoom);

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
            window.addEventListener("dragover", function (e) {
                e = e || event;
                e.preventDefault();
            }, false);
            window.addEventListener("drop", function (e) {
                e = e || event;
                e.preventDefault();
            }, false);
        },
        mounted: function () {
            //contentのサイズ調整
            this.adjustContentHeight();

            //システム名
            this.$emit("setSystemName", "ダイヤスクライアント");
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
    //得意先マスタ
    //window.axios.post("/Utilities/GetCustomerAndCourseList", { targetDate: moment().format("YYYYMMDD") }).then(res => watcher(res)),
    // window.axios.post("/Utilities/GetCustomerListForSelect", { CustomerCd: null, KeyWord: null }).then(res => watcher(res)),
    //メニューリスト
    window.axios.post("/Account/GetMenuList").then(res => watcher(res)),
    //部署マスタ
    window.axios.post("/Utilities/GetBushoList").then(res => watcher(res)),
    //担当者マスタ
    window.axios.post("/Utilities/GetTantoList").then(res => watcher(res)),
    window.axios.post("/Utilities/GetTantoListForSelect").then(res => watcher(res)),
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

// setInterval(
//     () => {
//         //部署マスタ
//         window.axios.post("/Utilities/GetBushoList", { noCache: true, replace: true });
//         //担当者マスタ
//         window.axios.post("/Utilities/GetTantoList", { noCache: true, replace: true });
//         window.axios.post("/Utilities/GetTantoListForSelect", { noCache: true, replace: true });
//         //各種テーブル
//         window.axios.post("/Utilities/GetCodeList", { noCache: true, replace: true });
//         //金融機関マスタ
//         window.axios.post("/Utilities/GetBankList", { noCache: true, replace: true });
//         //得意先マスタ
//         window.axios.post("/Utilities/GetCustomerListForSelect", { CustomerCd: null, KeyWord: null }, { noCache: true, replace: true });
//     }
//     , 60000
// );
