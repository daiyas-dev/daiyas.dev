import _ from "lodash";
window._ = _;

require('./bootstrap');

require("@/common_dialogs.js");
require("@/common_downloads.js");
require("@/common_pdfviewer.js");

require("jquery-contextmenu");

import Vue from "vue";

import moment from "moment"
window.moment = moment;

import { Decimal } from "decimal.js";
window.Decimal = Decimal;

import Algebra from "algebra.js";
window.Algebra = Algebra;

import printJS from "print-js";
window.printJS = printJS;

import LogonForm    from "@vcs/LogonForm.vue";
import TopMenu      from "@vcs/TopMenu.vue";
import PageDialog   from "@vcs/PageDialog.vue";
import AppHeader from "@vcs/AppHeader.vue";
import AppFooter from "@vcs/AppFooter.vue";

//vue-router
import VueRouter from "vue-router"
Vue.use(VueRouter);

//route定義
import routes from "@/routes.js"
const router = new VueRouter({
    mode: "hash",
    base: __dirname,
    routes: routes,
});
router.beforeEach((to, from, next) => {
    if (!!window.VueApp) {
        //ユーザIDを付加
        var userId = window.VueApp.$refs.LogonForm.$data.user.uid;
        if (!!userId && !to.query["userId"]) {
            to.query["userId"] = userId;
            next(to);
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
        createInstance: function(obj) {
            return Vue.extend(obj);
        },
        adjustContentHeight: function() {
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
        touchStart: _.debounce(function(event) {
            this.move.x = event.clientX;
            this.move.y = event.clientY;
        }),
        touchMove: _.debounce(function(event) {
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
