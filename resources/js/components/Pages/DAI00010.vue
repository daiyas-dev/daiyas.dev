<template>
    <form id="this.$options.name">
        <p><a href="./client/win/DaiyasClientSetup.exe" download="ダイヤスクライアントセットアップ.exe">CTI連携用クライアントのダウンロード</a></p>
        <p><a href="#">受電</a></p><input typete="text" class="telno">

        <br>
        <button @click="setMenus">パネル更新</button>
        <div class="row">
            <div class="col-md-2 d-flex align-items-stretch justify-content-center" style="border: groove;">
                <div class="btn-group-vertical d-flex" role="group">
                    <template v-for="(menu, idx) in menus">
                        <button
                            type="button"
                            :key="menu.functionId"
                            class="btn side-btn mt-2 mb-2 rounded border"
                            @click="selectMenu(menu.functionId)"
                            :class='idx == 0 ? "btn-primary" : "btn-light"'
                        >
                            {{menu.title}}
                        </button>
                    </template>
                </div>
            </div>
            <div class="col-md-10">
                <div class="tab-container p-2" style="border: groove;">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <template v-for="(menu, idx) in menus">
                                <a
                                    :key="menu.functionId"
                                    class="nav-item nav-link"
                                    :class='idx == 0 ? "active" : ""'
                                    data-toggle="tab"
                                    :href='"#" + menu.functionId'
                                    role="tab"
                                >{{menu.title}}</a>
                            </template>
                        </div>
                    </nav>
                    <div class="w-100 tab-content">
                        <template v-for="(menu, idx) in menus">
                            <div
                                :key="menu.title"
                                :id=menu.functionId
                                class="tab-pane"
                                :class='idx == 0 ? "active" : ""'
                                 role="tabpanel"
                            >
                                <template v-if="menu.submenus">
                                    <template v-for="(submenu, idx) in menu.submenus">
                                        <button
                                            type="button"
                                            :key=submenu.programId
                                            @click="$router.push(submenu.route)">{{submenu.title}}</button>
                                    </template>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
.side-btn {
    width: 150px !important;
}
.tab-content {
    background-color: white;
    border: groove;
    border: none;
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
            menus: [],
            viewModel: {
            },
        });
    },
    updated: function () {
        var vue = this;
        vue.setCurrentPage();
    },
    activated: function() {
        var vue = this;
        vue.setMenus();
    },
    methods: {
        createdFunc: function(vue) {
            vue.$root.$on("logOn", this.logOn);
        },
        mountedFunc: function(vue) {
        },
        logOn: function (info) {
            var vue = this;
            vue.setMenus();
        },
        setMenus: function() {
            var vue = this;

            //TopMenu読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    var TopMenu = vue.$root.$refs.TopMenu;

                    if (vue.getLoginInfo().isLogOn && !!TopMenu && !!TopMenu.menus && !!TopMenu.menus.length) {
                        clearInterval(timer);
                        return resolve(TopMenu.menus);
                    }
                }, 500);
            })
            .then(menus => {
                console.log("DAI00010 setMenus", menus);
                vue.menus = menus;
            });
        },
        setCurrentPage: function() {

        },
    }
}
</script>

