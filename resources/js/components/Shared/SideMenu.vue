<template>
    <div v-if="_isShow" class="nav-side-menu" @mouseleave="hide">
        <div id="screen-title" class="brand">
            <label class="label label-primary">{{systemName}}</label>
        </div>
        <div class="menu-list" id="menu-list">
            <ul id="menu-content" class="menu-content collapse out" v-if="isLogOn && menus && menus.length">
                <template v-for="(menu, idx) in menus">
                    <template v-if="menu.submenus">
                        <li v-bind:key="idx" data-toggle="collapse" :data-target="'#submenu' + idx" class="collapsed">
                            <i class="fa fa-2x" :class="{ 'fa-home': !!menu.isHome, 'fa-cog': !!menu.isConfig, 'fa-laptop': !menu.isHome && !menu.isConfig }"></i>
                            <a :href="menu.target" v-html=menu.title></a>
                        </li>
                        <ul v-bind:key="idx" class="sub-menu collapse" :id="'submenu' + idx">
                        <li v-for="submenu in menu.submenus" v-bind:key="submenu.title" class="submenu">
                            <a v-if="submenu.target" class="custom-item" :href="submenu.target" v-html=submenu.title></a>
                            <template v-if="submenu.route">
                                <router-link :to="submenu.route" v-html=submenu.title></router-link>
                            </template>
                        </li>
                        </ul>
                    </template>
                    <li v-bind:key="idx" v-else>
                        <i class="fa fa-2x" :class="{ 'fa-home': !!menu.isHome, 'fa-cog': !!menu.isConfig, 'fa-laptop': !menu.isHome && !menu.isConfig }"></i>
                        <a v-if="menu.target" class="custom-item" :href="menu.target" v-html=menu.title></a>
                        <template v-if="menu.route">
                            <router-link :to="menu.route" v-html=menu.title></router-link>
                        </template>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: transform 0.5s
}

.slide-enter {
    transform: translateX(-100%)
}

.slide-leave-active {
    transform: translateX(-100%);
}
</style>

<style>
/* fontawesome override */
.fa-laptop {
    color: dodgerblue;
    background-color: white;
}

.nav-side-menu {
    display: block;
    top: 50px;
    overflow: hidden;
    font-size: 15px;
    font-weight: bold;
    color: white;
    background-color: rgb(63, 81, 181);
    position: fixed;
    width: 250px;
    height: 100%;
    z-index: 1032 !important; /*bootstrap navbarの上位に設定*/
}

.nav-side-menu .brand {
    display: none;
    background-color: #23282e;
    line-height: 50px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}
.brand label {
    font-size: inherit;
    padding-top: 4px;
    padding-bottom: 4px;
}

.nav-side-menu ul {
    list-style: none;
    padding: 0px;
    line-height: 35px;
    cursor: pointer;
}
.nav-side-menu li {
    list-style: none;
    padding-top: 5px;
    padding-bottom: 5px;
    line-height: 35px;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding-left: 0px;
    border-left: 3px solid transparent;
}

.nav-side-menu li:hover {
    border-left: 3px solid orange;
    background-color: cornflowerblue;
    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    -ms-transition: all 0.5s ease;
    transition: all 0.5s ease;
}
.nav-side-menu [data-toggle="collapse"]:not(.collapsed) {
    background-color: rgb(89, 89, 89);
}

.nav-side-menu .sub-menu li {
    background-color: rgb(128, 128, 128);
    border: none;
    line-height: 28px;
    margin-left: 0px;
    padding-left: 30px;
}
.nav-side-menu ul .sub-menu li:hover,
.nav-side-menu li .sub-menu li:hover {
    background-color: #020203;
}
.nav-side-menu ul .sub-menu li:before,
.nav-side-menu li .sub-menu li:before {
    font-family: FontAwesome;
    content: "\f105";
    display: inline-block;
    padding-left: 10px;
    padding-right: 10px;
    vertical-align: middle;
}
.nav-side-menu ul .sub-menu li.active,
.nav-side-menu li .sub-menu li.active {
    color: #d19b3d;
}
.nav-side-menu ul .sub-menu li.active a,
.nav-side-menu li .sub-menu li.active a {
    color: #d19b3d;
}
.nav-side-menu li a {
    text-decoration: none;
    color: #e1ffff;
}
.nav-side-menu li i,
.nav-side-menu li a i {
    width: 40px;
    text-align: center;
    margin-left: 5px;
    margin-right: 10px;
}
@media (max-width: 767px) {
    .nav-side-menu {
        position: relative;
        width: 100%;
        margin-bottom: 10px;
    }
    .nav-side-menu .toggle-btn {
        display: block;
        cursor: pointer;
        position: absolute;
        right: 10px;
        top: 10px;
        z-index: 10 !important;
        padding: 3px;
        background-color: #ffffff;
        color: #000;
        width: 40px;
        text-align: center;
    }
    .brand {
        text-align: left !important;
        font-size: 22px;
        padding-left: 20px;
        line-height: 50px !important;
    }
}
@media (min-width: 767px) {
    .nav-side-menu .menu-list .menu-content {
        display: block;
    }
}

</style>
<script>
export default {
    name: "side-menu",
    data() {
        return {
            systemName: null,
            title: {
                name: "",
                back: ""
            },
            menus: null,
            userId: null,
            userNm: null,
            plant: null,
            isLogOn: null,
            messages: [],
            isAddMessage: false,
            haveSubmenuIcon: "&nbsp<i class='fa fa-caret-down fa-lg'></i>",
            isShow: true,
        }
    },
    computed: {
        _isShow: function () {
            return true;    //this.isShow;
        }
    },
    created: function () {
        this.$root.$on("logOn", this.logOn);
        this.$root.$on("resize", this.resize);
        this.$root.$on("addMessage", this.addMessage);
        this.$root.$on("setTitle", this.setTitle);
        this.$root.$on("setSystemName", this.setSystemName);
        this.$root.$on("showSideMenu", this.show);
        this.$root.$on("hideSideMenu", this.hide);
        this.$root.$on("setCurrentPage", this.setCurrentPage);
    },
    mounted: function () {
    },
    beforeUpdated: function () {
    },
    updated: function () {
        if (this.isAddMessage) {
            this.isAddMessage = false;
        } else {
            //リサイズ通知
            this.$root.$emit("resize", this.$data);
        }

        //現在ページの設定
        this.setCurrentPage();
    },
    destroyed: function () {
    },
    methods: {
        logOn: function (info) {
            this.isLogOn = info.isLogOn;
            this.userId = info.user.uid;
            this.userNm = info.user.unm;
            this.plant = info.user.plant;

            this.addMessage(info.isCheckOnly ? null : info.isLogOn ? "ログイン成功" : ("ログイン失敗" + "[" + info.message + "]"));

            if (info.isLogOn) {
                //メニューリスト取得
                this.getMenuList(info.user.uid);
            }
        },
        logOff: function () {
            //ログオフ処理のAjax呼び出し
            var logoutUrl = "/Account/Logout";
            axios.post(
                logoutUrl
            )
                .then(response => {
                    var res = response.data;

                    if (!res.IsLogin) {
                        //ログアウト成功
                        this.isLogOn = false;
                        this.menus = [];
                        this.addMessage("ログアウト成功");
                    } else {
                        //ログアウト失敗
                        this.isLogOn = false;
                        this.addMessage("ログアウト失敗" + "[" + res.message + "]");
                    }

                    //他コンポーネントに通知
                    this.$root.$emit("logOff", this.$data);
                })
                .catch(error => {
                    this.isLogOn = false;
                    this.addMessage("ログアウト失敗" + "[" + logoutUrl + "で例外発生" + "]");

                    //他コンポーネントに通知
                    this.$root.$emit("logOff", this.$data);
                });

        },
        getMenuList: function (userId) {
            //メニュー取得処理のAjax呼び出し
            var url = "/Account/GetMenuList";
            var vue = this;
            axios.post(url, userId)
                .then(response => {
                    var list = response.data;

                    if (list) {
                        if (list && list.length) {
                            //取得結果を基にメニュー用配列の生成
                            var menus = [];

                            list.forEach(function (v) {
                                v.Title = v.Title.replace(/<br>/i, "");
                                v.FunctionId = v.FunctionId.replace(/SIP/g, "SMDB");
                                v.ProgramId = v.ProgramId.replace(/SIP/g, "SMDB");

                                var menu = {
                                    functionId: v.FunctionId,
                                    programId: v.ProgramId.trim(),
                                    title: v.Title.trim(),
                                    target: v.ProgramId.trim() == "" ? "#" : null,
                                    route:  v.ProgramId.trim() != "" ? ("/" + v.FunctionId + "/" + v.ProgramId.trim()) : null,
                                };

                                var parent = menus.filter(m => m.functionId == menu.functionId && m.programId == "");
                                if (parent.length > 0) {
                                    parent[0].submenus = parent[0].submenus || [];
                                    parent[0].submenus.push(menu);
                                } else {
                                    menus.push(menu);
                                }
                            });

                            //TODO: ダッシュボードを仮追加
                            var dashboard = {
                                isHome: true,
                                functionId: "SMDB00",
                                programId: "",
                                title: "ダッシュボード",
                                target: "#",
                                route: null,
                            };
                            var item_info = {
                                functionId: "SMDB00",
                                programId: "SMDB0002",
                                title: "お知らせ",
                                target: null,
                                route:  "/SMDB00/SMDB0002",
                            };
                            var item_pending = {
                                functionId: "SMDB00",
                                programId: "SMDB0002",
                                title: "承認待ちの案件",
                                target: null,
                                route:  "/SMDB00/SMDB0002",
                            };

                            dashboard.submenus = [item_info, item_pending];
                            menus.unshift(dashboard);

                            //TODO: 設定を仮追加
                            var config = {
                                isConfig: true,
                                functionId: "SMDB00",
                                programId: "SMDB0002",
                                title: "設定",
                                target: null,
                                route:  "SMDB00/SMDB0002",
                            };
                            menus.push(config);

                            this.menus = menus;
                            $("#menu-wrapper").css("width", "calc((100% - 350px) / 10 * " + (menus.length > 5 ? 10 : (menus.length + 1)) + ")");


                            //現在のhash(queryは除く)
                            var hash = location.hash.replace(/\?.*$/, "");
                            var params = vue.$route.params;
                            var query = vue.$route.query;

                            //ユーザID変更時はトップへ遷移
                            if (userId != query.userId) {
                                vue.$router.push({ path: "/" });
                            }

                            //TODO: 現在の画面表示権限をメニューの有無から判断 => 現在のセキュリティマスタ及びメニュー構成では実現不可の為停止
                            //if (!menus.some(m => m.programId == params.pgId) && !menus.some(m => m.submenus && m.submenus.some(sm => sm.programId == params.pgId))) {
                            //    vue.$router.push({ path: "/" });
                            //}

                        } else {
                            this.addMessage("メニュー取得失敗[利用可能なメニューがありません]");
                            this.$router.push({ path: "/" });
                        }
                    } else {
                        this.addMessage("メニュー取得失敗[利用可能なメニューがありません]");
                        this.$router.push({ path: "/" });
                    }
                })
                .catch(error => {
                    console.log(error);
                    this.addMessage("メニュー取得失敗" + "[" + url + "で例外発生" + "]");
                });

        },
        addMessage: function (message) {
            if (!message) return;
            this.isAddMessage = true;
            this.messages.unshift(moment().format("YYYY-MM-DD HH:mm:ss", new Date()) + " " + message);
        },
        setSystemName: function (name) {
            this.systemName = name;
        },
        resize: function() {
        },
        setCurrentPage: function (ScreenTitle) {

            //classを除外
            $(".currentPage").removeClass("currentPage");

            //現在のhash(queryは除く)
            var hash = location.hash.replace(/\?.*$/, "");

            //homeと例外画面は除外
            if (hash == "#/" || hash.includes("home") || hash.includes("Exception")) return;

            //hashでlinkを検索
            var link = $("ul a", this.$el).filter((i, v)=>v.href.includes(hash));

            if (link.length == 0) {
                //hashで見つからなかったら、プログラムIDを覗いて検索
                link = $("ul a", this.$el).filter((i, v)=>v.href.includes(hash.replace(/\/[^\/]+$/, "")));
            }

            //再設定
            link.closest("ul.navbar-nav").addClass("currentPage");
            link.closest("li.submenu").addClass("currentPage");

            //Windowタイトル
            if (ScreenTitle) {
                window.document.title = this.systemName + "-" + ScreenTitle;
            }
        },
        goHome: function() {
            this.$root.$router.push("/");
        },
        setTitle: function (info) {
            this.title = info;
        },
        show: function() {
            this.isShow = true;
        },
        hide: function() {
            this.isShow = false;
        },
        closeApp: function () {
            //$emitで"closeApp"を飛ばして、各コンポーネントでの終了処理をさせる
        }
    }
}
</script>
