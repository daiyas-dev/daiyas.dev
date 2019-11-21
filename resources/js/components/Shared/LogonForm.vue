<template>
    <div class="modal fade" id="loginDialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ログイン</h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div v-if="message" class="form-group mb-1 has-error">
                            <label class="message" style="width: 250px; white-space: pre;">{{message}}</label>
                        </div>
                        <div :class="'form-group' + (errors.uid ? ' has-error' : '')">
                            <label class="" for="uid">ユーザID</label>
                            <input class="form-control" type="email" id="uid" v-model="user.uid" @keyup.enter="logOn" autocomplete="off">
                            <label class="message">{{errors.uid}}</label>
                        </div>
                        <div :class="'form-group' + (errors.pwd ? ' has-error' : '')">
                            <label class="" for="pwd">パスワード</label>
                            <input class="form-control" type="password" id="pwd" placeholder="********" v-model="user.pwd" @keyup.enter="logOn">
                            <label class="message">{{errors.pwd}}</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-default" @click="logOn">OK</button>
                    <button type="button" id="closeBtn" style="display:none;" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .modal {
        text-align: center;
    }

    @media screen and (min-width: 768px) {
        .modal:before {
            display: inline-block;
            vertical-align: middle;
            content: " ";
            height: 100%;
        }
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    .modal-dialog #uid,
    .modal-dialog #uid
    {
        ime-mode: disabled; /* IE & Edge Only */
    }

    .modal-footer {
        text-align: center;
    }

    button.btn {
        width: 120px;
    }

    #loginDialog .modal-header {
        padding: 5px;
        text-align: center;
        background-color: #325d88;
        border-radius: 4px;
    }

    #loginDialog .modal-title {
        color: white;
        background-color: transparent;
        font-weight: bold;
        font-size: 18px;
    }

    #loginDialog .has-error .message {
        height: auto;
        color: red;
    }

</style>

<script>
import VueDataList from "@vcs/DataList.vue";
import VueSelect from "@vcs/VueSelect.vue";

export default {
    name: "LogonForm",
    data() {
        return {
            isShown: false,
            user: {
                uid: "",
                unm: "",
                pwd: "",
                bushoCd: "",
                bushoNm: "",
                plant: "",
            },
            prevUid: "",
            prevBushoCd: "",
            prevPlant: "",
            errors: {
                uid: null,
                unm: null,
                pwd: null,
            },
            isLogOn: false,
            isCheckOnly: false,
            message: null,
            users: null,
            systemName: null,
        };
    },
    components: {
        "VueDataList": VueDataList,
        "VueSelect": VueSelect,
    },
    created: function () {
        this.$root.$on('showLogOn', this.show);

        this.$root.$on('execLogOn', this.logOn);
        this.$root.$on('logOff', this.logOff);
    },
    mounted: function () {
        //ログオン処理の呼び出し
        this.logOn(true);
    },
    methods: {
        show: function () {
            if (!this.isShown) {
                $("#loginDialog").modal(
                    {
                        backdrop: 'static',
                        keyboard: false
                    }
                );
                this.isShown = true;
                this.user.uid = $("#uid", this.$el).val();
                this.user.pwd = $("#pwd", this.$el).val();
            }
        },
        hide: function () {
            if (this.isShown) {
                $("#closeBtn").click();
                this.isShown = false;
            }
        },
        userIdChanged: function(event, value) {
            this.user.uid = value;
        },
        logOn: function (checkOnly) {
            this.isCheckOnly = checkOnly == true;

            this.user.uid = $("#uid", this.$el).val();
            this.user.pwd = $("#pwd", this.$el).val();

            //ログオン処理のAjax呼び出し
            var loginUrl = "/Account/Login";
            axios.post(
                loginUrl,
                {
                    userid: this.user.uid,
                    password: this.user.pwd,
                    CheckOnly: this.isCheckOnly
                }
            )
                .then(response => {
                    console.log(loginUrl + " completed")
                    console.log(response)
                    var res = response.data;

                    if (res.IsLogin) {
                        //ログイン成功
                        console.log("Login succeed")
                        this.isLogOn = true;
                        this.user.isLogOn = true;
                        this.user.uid = res.UserId;
                        this.user.unm = res.UserNm;
                        this.user.pwd = res.Password;
                        this.user.bushoCd = res.BushoCd;
                        this.user.bushoNm = res.BushoNm;
                        this.errors.uid = null;
                        this.errors.pwd = null;

                        window.loginInfo = this.user;

                        //CSRF Tokenの更新
                        Laravel.csrfToken = res.CsrfToken;
                        $('meta[name="csrf-token"]')[0].content = res.CsrfToken;
                        axios.defaults.headers.common["X-CSRF-TOKEN"] = res.CsrfToken;

                        // //Laravel Echo Au
                        // Echo.options.auth = {
                        //     headers: {
                        //         Authorization: "Bearer xxxx",
                        //     }
                        // }

                        // //public channel
                        // Echo.channel("public-event")
                        //     .listen("PublicEvent", (e) => {
                        //         console.log("Public Channel received");
                        //         console.log(e);
                        //     });

                        // //private channel
                        // Echo.private("message")
                        //     .listen('PrivateEvent', (e) => {
                        //         console.log("Private Channel received");
                        //         console.log(e);
                        //     });

                        if (this.prevUid != res.UserId) {
                            //ユーザ変更を他コンポーネントに通知
                            console.log("accountChanged:" + this.prevUid + " -> " + res.UserId);
                            this.$root.$emit("accountChanged", this.$data);
                        }
                        if (!!this.prevPlant && this.prevPlant != res.Plant) {
                            //プラント変更を他コンポーネントに通知
                            console.log("plantChanged:" + this.prevPlant + " -> " + res.Plant);
                            this.$root.$emit("plantChanged", this.$data);

                            //プラント変更時は、トップページに戻る
                            this.$router.push({ path: "/" });
                        }

                        this.prevUid = res.UserId;
                        this.prevPlant = res.Password;
                        this.prevBushoCd = res.BushoCd;

                        //他コンポーネントに通知
                        this.$root.$emit("logOn", this.$data);

                        //ダイアログ非表示
                        this.hide();
                    } else {
                        //ログイン失敗
                        console.log("Login failed");
                        this.show();
                        this.isLogOn = false;
                        this.message = res.message;
                        if (res.errors) {
                            this.errors.uid = res.errors.UserId || null;
                            this.errors.pwd = res.errors.Password || null;
                        }
                        window.loginInfo = { isLogOn: false };
                    }
                })
                .catch(error => {
                    console.log(loginUrl + " Error!");
                    console.log(error);

                    this.show();
                    this.isLogOn = false;
                    this.message = !!error.response && error.response.status == 422
                        ? "ユーザIDもしくは\r\nパスワードが違います"
                        : "ログインに問題が生じています。\r\n管理者に連絡してください。"
                            + (!!error.message ? ("\r\n" + error.message) : "");

                    window.loginInfo = { isLogOn: false };

                    //他コンポーネントに通知
                    this.$root.$emit("logOn", this.$data);
                });

        },
        logOff: function (info) {
            this.prevUid = this.user.uid;
            this.prevPlant = this.user.plant;
            this.user.uid = "";
            this.user.unm = "";
            this.user.pwd = "";
            this.user.bushoCd = "";
            this.user.bushoNm = "";
            this.user.plant = "";
            this.isLogOn = false;
            this.message = "";
            this.show();
        }
    }
};
</script>
