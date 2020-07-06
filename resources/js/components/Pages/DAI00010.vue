<template>
    <form id="this.$options.name">
        <div class="row" style="display: flex; align-items: baseline;">
            <div class="col-md-3">
                <p class="ml-1 mb-0"><a href="./client/win/ダイヤスクライアントSetup.exe" download="ダイヤスクライアントセットアップ.exe">CTI連携用クライアントのダウンロード </a></p>
            </div>
            <div class="col-md-9">
                <label style="width: unset;">※本社CTI連携機能をお使いの方は左記クライアントをインストールして、システムをご利用ください</label>
            </div>
        </div>
        <!-- <div class="row" style="display: flex; align-items: baseline;">
            <div class="col-md-4">
                <button @click="event => testCTI(event)">非通知</button>
                <button @click="event => testCTI(event, '0836585044')">1件該当</button>
                <button @click="event => testCTI(event, '0836313929')">複数該当</button>
                <button @click="event => testCTI(event, '0120444444')">該当なし</button>
                <button @click="event => testCTI(event, '0120030561')">非顧客登録済</button>
            </div>
            <div class="col-md-4">
                <button @click="event => addWebOrder(event)">Web受注ダミー追加</button>
            </div>
        </div> -->
        <div class="row menu-panel">
            <div class="col-md-2 d-flex align-items-stretch justify-content-center" style="border: groove;">
                <div class="btn-group-vertical d-flex" role="group">
                    <template v-for="menu in menus">
                        <button
                            type="button"
                            :key="menu.functionId"
                            :id='"nav_" + menu.functionId'
                            class="btn side-btn mt-2 mb-2 rounded border"
                            :class='selected == menu.functionId ? "btn-primary" : "btn-light"'
                            @click='() => selected = menu.functionId'
                            @focus='() => selected = menu.functionId'
                        >
                            {{menu.title}}
                        </button>
                    </template>
                </div>
            </div>
            <div class="col-md-10 p-2 d-block align-items-stretch justify-content-center" style="border: groove;">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <template v-for="menu in menus">
                            <a
                                class="nav-item nav-link"
                                data-toggle="tab"
                                role="tab"
                                :key="menu.functionId"
                                :id='"tab_" + menu.functionId'
                                :href='"#" + menu.functionId'
                                :aria-controls="menu.functionId"
                                :aria-selected='selected == menu.functionId ? true : false'
                                :class='selected == menu.functionId ? "active" : ""'
                                @click='() => selected = menu.functionId'
                                @focus='() => selected = menu.functionId'
                            >
                                    {{menu.title}}
                            </a>
                        </template>
                    </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                    <template v-for="menu in menus">
                        <div
                            class="tab-pane"
                            role="tabpanel"
                            :key="menu.functionId"
                            :id='"tab_" + menu.functionId'
                            :class='selected == menu.functionId ? "active" : ""'
                            :aria-labelledby='menu.functionId + "-tab"'
                        >
                            <template v-if="menu.submenus">
                                <template v-for="submenu in menu.submenus">
                                    <button
                                        type="button"
                                        :key=submenu.programId
                                        :id='"menu_" + submenu.programId'
                                    >
                                        <router-link :to="submenu.route" class="nav-link" v-html=submenu.title></router-link>
                                    </button>
                                </template>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
.menu-panel {
    height: calc(100vh - 200px);
}
.side-btn {
    width: 150px !important;
}
.tab-container {
    width: 100%;
    height: calc(100vh - 200px);
}
.tab-container .nav-tabs {
    display: none;
}
button .nav-link {
    color: black;
}
.nav-item.nav-link {
    width: calc(100% / 9 - 0px);
    margin-right: 0px;
    padding-left: 3px;
    padding-right: 3px;
    text-align: center;
    border-style: groove;
    border-width: 3px;
    border-color: inherit;
    border-right-width: 0px;
    color: black;
}
.nav-item.nav-link:last-child {
    border-right-width: 3px;
}
.nav-item.nav-link:hover {
    background-color: cyan;
}
.nav-item.nav-link.active {
    border-bottom-width: 0px;
    color: white;
    background-color: #007BFF;
}
.tab-content {
    height: calc(100% - 40px);
    background-color: white;
    border-style: groove;
    border-top: none;
    margin: 0px;
    padding: 5px;
}
.tab-content .tab-pane {
    position: relative;
    text-align: center;
}
.tab-content .tab-pane button {
    position: absolute;
    width: 275px;
    height: 40px;
    margin-top: 20px;
    margin-left: 15px;
}
.tab-content .tab-pane button a {
    padding-top: 0px;
    padding-left: 0px;
    padding-right: 0px;
    padding-bottom: 0px;
}
.tab-content .tab-pane button {
    border-width: 1px;
}
.tab-content .tab-pane button#menu_DAI01010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01030 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01060 {
    top: 275px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01070 {
    top: 330px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01080 {
    top: 385px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01090 {
    top: 440px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI01100 {
    top: 0px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01110 {
    top: 55px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01120 {
    top: 110px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01130 {
    top: 165px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01140 {
    top: 220px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01150 {
    top: 275px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01160 {
    top: 330px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01170 {
    top: 385px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01180 {
    top: 440px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI01190 {
    top: 0px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01200 {
    top: 55px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01210 {
    top: 110px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01220 {
    top: 165px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01230 {
    top: 220px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01240 {
    top: 275px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01250 {
    top: 330px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI01260 {
    top: 385px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI02010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI02020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI02030 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03030 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03040 {
    top: 165px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03050 {
    top: 220px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03060 {
    top: 275px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03070 {
    top: 330px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI03080 {
    top: 0px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03090 {
    top: 55px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03100 {
    top: 110px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03110 {
    top: 165px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03120 {
    top: 220px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03130 {
    top: 275px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI03140 {
    top: 330px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04030 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04040 {
    top: 165px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04050 {
    top: 220px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04060 {
    top: 275px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04070 {
    top: 330px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI04080 {
    top: 0px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04090 {
    top: 55px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04100 {
    top: 110px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04110 {
    top: 165px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04120 {
    top: 220px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04130 {
    top: 275px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04140 {
    top: 330px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI04150 {
    top: 0px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI04160 {
    top: 55px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI04170 {
    top: 110px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI04180 {
    top: 165px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI04190 {
    top: 220px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI04200 {
    top: 275px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI05010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI05020 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI05030 {
    top: 165px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI05050 {
    top: 330px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI05070 {
    top: 110px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI05080 {
    top: 165px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI05090 {
    top: 0px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI05100 {
    top: 55px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI05110 {
    top: 110px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI05120 {
    top: 165px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI05150 {
    top: 330px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI06010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI06020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI06030 {
    top: 110px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI06040 {
    top: 0px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI06050 {
    top: 55px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI07010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI07020 {
    top: 0px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI07030 {
    top: 55px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI07040 {
    top: 110px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI07050 {
    top: 165px;
    left: 300px;
}
.tab-content .tab-pane button#menu_DAI07070 {
    top: 0px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI07080 {
    top: 55px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI07090 {
    top: 110px;
    left: 600px;
}
.tab-content .tab-pane button#menu_DAI08010 {
    top: 0px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI08020 {
    top: 55px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI08030 {
    top: 165px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI08040 {
    top: 220px;
    left: 0px;
}
.tab-content .tab-pane button#menu_DAI08009 {
    top: 0px;
    left: 300px;
}

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
            IsFirstFocus: false,
            menus: [],
            selected: null,
            systemName: null,
            viewModel: {
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.$root.$on("logOn", this.logOn);
            vue.$root.$on("setSystemName", val => vue.systemName = val);
        },
        mountedFunc: function(vue) {
            vue.setMenus();
        },
        activatedFunc: function(vue) {
            vue.setMenus();
        },
        logOn: function (info) {
            var vue = this;
            vue.setMenus();
        },
        setMenus: function() {
            var vue = this;

            //Windowタイトル
            window.document.title = vue.ScreenTitle;

            //AppHeaderタイトル
            vue.$root.$emit("setTitle", vue.ScreenTitle);

            //TopMenu読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    var TopMenu = vue.$root.$refs.TopMenu;

                    if (vue.getLoginInfo().isLogOn && !!TopMenu && !!TopMenu.menus && !!TopMenu.menus.length) {
                        clearInterval(timer);
                        return resolve(TopMenu.menus);
                    }
                }, 300);
            })
            .then(menus => {
                vue.menus = menus;
                if (!!menus.length && !vue.selected) {
                    vue.selected = menus[0].functionId;
                }
            });
        },
        testCTI: function(event, no) {
            var vue = this;
            vue.$root.$refs.CtiReceiver.checkTelNo(no, moment().format("HH:mm:ss"));

            event.preventDefault();
            return false;
        },
        addWebOrder: function(event, no) {
            var vue = this;

            axios.post("/Utilities/SetWebOrderDummy", { noCache: true })
                .then(res => {
                    console.log("SetWebOrderDummy Complete", res.data);
                })
                .catch(err => {
                    console.log("SetWebOrderDummy Err", err);
                });

            event.preventDefault();
            return false;
        },
    }
}
</script>

