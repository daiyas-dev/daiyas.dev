<template>
    <header class="AppHeader mt-2 mb-2">
        <div class="row ml-2 mr-2">
            <div class="col-md-6">
                <span :class="['badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{title}}</span>
            </div>
            <div class="col-md-6 justify-content-end">
                <span :class="['ml-1', 'badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{nowDate}}</span>
                <span :class="['ml-1', 'badge', this.isLogOn == true ? 'badge-success' : 'badge-danger']">{{isLogOn == true ? userNm : "未ログイン"}}</span>
            </div>
        </div>
    </header>
</template>

<style scoped>
.badge {
    font-size: 15px;
    font-weight: normal;
}
</style>

<script>
    export default {
        name: "AppHeader",
        data() {
            return {
                title: null,
                userId: null,
                userNm: null,
                isLogOn: null,
            }
        },
        computed: {
            nowDate: function () {
                return moment().format('YYYY/MM/DD', new Date());
            }
        },
        created: function () {
        },
        mounted: function () {
            var vue = this;

            //logOn/logOff handler
            vue.$root.$on("logOn", vue.logOn);
            vue.$root.$on("logOff", vue.logOff);

            //title handler
            vue.$root.$on("setTitle", vue.setTitle);

            //ログイン済みの場合はその内容を表示
            var info = vue.$root.$refs.TopMenu.$data;
            vue.userId = info.userId;
            vue.userNm = info.userNm;
            vue.isLogOn = info.isLogOn;
        },
        methods: {
            logOn: function (info) {
                var vue = this;
                vue.userId = info.user.uid;
                vue.userNm = info.user.unm;
                vue.isLogOn = info.isLogOn;
            },
            logOff: function (info) {
                var vue = this;
                vue.userId = "";
                vue.userNm = "";
                vue.isLogOn = false;
            },
            setTitle: function(title) {
                var vue = this;
                vue.title = title;
            }
        }
    }
</script>
