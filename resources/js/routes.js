import PageSelector from "@vcs/PageSelector.vue";

export default [
    {
        path: "/",
        component: PageSelector,
        props: { pgId: "DAI00010" },
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/PID00/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/PID01/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/PID02/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI01/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI02/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI03/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI04/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI05/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI06/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI07/DAI07070",
        component: PageSelector,
        props: { pgId: "DAI02010" },
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI07/DAI07080",
        component: PageSelector,
        props: { pgId: "DAI02030" },
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI07/DAI07090",
        component: PageSelector,
        props: { pgId: "DAI02030" },
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI07/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/DAI08/:pgId",
        component: PageSelector,
        props: true,
        meta: {
            keepAlive: true,
        },
    },
    {
        path: "/PID/Exceptions",
        component: {
            template: "<div id='exception'>例外発生:{{this.$route.query.message || this.$route.query.statusText}}<div id='stacktrace' v-html=this.stackTrace></div></div>",
            props: {
                message: String,
            },
            computed: {
                stackTrace: function() {
                    var errors = this.$route.query.errors;
                    return errors ?
                        (errors.includes("<") ? errors : errors.replace(/\r\n/g, "<br/>").replace(/\) 場所/g, ")<br/>&nbsp&nbsp場所"))
                        : "";
                },
            },
            created: function () {  //createdは一回きり
                console.log(this.$route.query.errors.replace(/\r\n/g, "<br/>"));
            },
        },
        props: true,
    },
    {
        path: "/UnderConstruction",
        component: {
            template: '<div class="d-flex" style="height: 100%;  justify-content: center; align-items: center;"><i class="fas fa-lg fa-hammer"></i>開発中</div>',
        },
    },
    {
        path: "*",
        component: {
            //TODO: エラー画面へ
            template: "<div id='exception'>Route未定義:{{this.$route.path}}<div id='stacktrace' v-html=this.stackTrace></div></div>",
            activated: function () {
                console.log(this.$route);
            },
        },
    },
]
