<template>
    <header class="TopMenu">
        <nav class="navbar navbar-expand navbar-dark bg-dark w-100 pt-0 pb-0">
            <div id="system-name" class="navbar-brand p-0">
                <label class="sysname badge-primary m-0 pl-0 pr-1" style="cursor: pointer;" @click="goHome">
                    <img src="/images/daiyas256.png" alt="ロゴ" style="width: 30px; height: 30px;">{{systemName}}
                </label>
            </div>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#Navber" aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="Navber">
                <ul class="navbar-nav mr-auto">
                    <template ul v-for="menu in menus">
                        <li v-if="menu.submenus" v-bind:key="menu.title" class="nav-item dropdown">
                            <a :href="menu.target" v-html="menu.title"
                                class="nav-link dropdown-toggle"
                                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            </a>
                            <div class="dropdown-menu bg-dark m-0 p-0 border-0" aria-labelledby="navbarDropdown">
                                <template v-for="submenu in menu.submenus">
                                    <router-link :to="submenu.route" class="dropdown-item" v-bind:key="submenu.title" v-html=submenu.title></router-link>
                                </template>
                            </div>
                        </li>
                        <li v-else class="nav-item" v-bind:key="menu.title">
                            <a v-if="menu.target" class="nav-link" :href="menu.target">{{menu.title}}</a>
                            <template v-if="menu.route">
                                <router-link :to="menu.route" class="nav-link" v-html=menu.title></router-link>
                            </template>
                        </li>
                    </template>
                </ul>
                <ul class="navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link custom-item" @click="logOff">
                            <i class="fas fa-lg" v-bind:class="{ 'fa-sign-out-alt': isLogOn, 'fa-sign-in-alt': !isLogOn }"></i>
                            {{isLogOn ? "ログオフ" : "ログオン"}}
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
</template>

<style scoped>
.navbar-brand {
    font-size: 20px;
}
.nav-item.active {
    background-color: orange;
}
.nav-item a {
    color: white !important;
    font-size: 18px;
}
.nav-item.active a {
    color: black !important;
}
.nav-item:hover {
    background-color: lightskyblue;
}
.nav-item:hover > a {
    color: black !important;
}
.navbar-dark .dropdown-menu {
    border-radius: 0px;
}
.navbar-dark .dropdown-item {
    padding-top: 2px;
    padding-bottom: 2px;
}
.navbar-dark .dropdown-item:hover {
    color: black !important;
    background-color: lightskyblue;
}
.sysname {
    width: 150px;
}
.currentPage {
    color: black !important;
    background-color: orange;
}
</style>

<script>
export default {
    name: "top-menu",
    data() {
        return {
            systemName: null,
            menus: null,
            userId: null,
            userNm: null,
            plant: null,
            isLogOn: null,
            messages: [],
            isAddMessage: false,
            haveSubmenuIcon: "&nbsp<i class='fa fa-caret-down fa-lg'></i>",
        }
    },
    computed: {
    },
    created: function () {
        this.$root.$on("logOn", this.logOn);
        this.$root.$on("resize", this.resize);
        this.$root.$on("addMessage", this.addMessage);
        this.$root.$on("setSystemName", this.setSystemName);
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
                            list
                            .filter(v => !v.enabled || !!(v.enabled * 1))
                            .forEach(function(menu) {
                                menu.target = menu.target
                                    || (menu.programId && menu.programId.trim() == "" ? "#" : null);
                                menu.route = menu.route
                                    || (menu.programId && menu.programId.trim() != "" ? ("/" + menu.functionId + "/" + menu.programId.trim()) : null);

                                var parent = menus.filter(m => m.functionId == menu.functionId && !m.programId);
                                if (parent.length > 0) {
                                    parent[0].submenus = parent[0].submenus || [];
                                    parent[0].submenus.push(menu);
                                } else {
                                    menus.push(menu);
                                }
                            });


                            this.menus = menus;
                            $("#menu-wrapper").css("width", "calc((100% - 350px) / 10 * " + (menus.length > 5 ? 10 : (menus.length + 1)) + ")");


                            //現在のhash(queryは除く)
                            var hash = location.hash.replace(/\?.*$/, "");
                            var params = vue.$route.params;
                            var query = vue.$route.query;

                            //ユーザID変更時はトップへ遷移
                            // if (userId != query.userId) {
                            //     vue.$router.push({ path: "/" });
                            // }

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
            if (hash == "#/" || hash.includes("home") || hash.includes("Exception") || hash.includes("UnderConstruction")) return;

            //hashでlinkを検索
            var link = $("ul a", this.$el).filter((i, v)=>v.href.includes(hash));

            if (link.length == 0) {
                //hashで見つからなかったら、プログラムIDを覗いて検索
                link = $("ul a", this.$el).filter((i, v)=>v.href.includes(hash.replace(/\/[^\/]+$/, "")));
            }

            //再設定
            link.addClass("currentPage");
            link.closest(".dropdown").addClass("currentPage");

            //Windowタイトル
            if (ScreenTitle) {
                window.document.title = this.systemName + "-" + ScreenTitle;
            }
        },
        goHome: function() {
            this.$root.$router.push("/");
        },
    }
}
</script>
