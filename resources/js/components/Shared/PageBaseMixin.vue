<style>
</style>

<script>
/** 以下、VueComponent設定 **/
import VueDataList from "@vcs/DataList.vue";
import VueSelect from "@vcs/VueSelect.vue";
import VueSelectBusho from "@vcs/VueSelectBusho.vue";
import PopupSelect from "@vcs/PopupSelect.vue";
import PqGridWrapper from "@vcs/PqGridWrapper.vue";
import DatePickerWrapper from "@vcs/DatePickerWrapper.vue";

export default {
    name: "PageBaseMixin",
    data() {
        return {
            ScreenTitle: "XX",
            viewModel: {},
            footerButtons: [
                {
                    visible: "true", value: "終了", align: "right", shortcut: "F12",
                    class: "btn-danger",
                    onClick: function() {
                        $.dialogConfirm({
                            title: "確認",
                            contents: "終了してよろしいですか？",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        $(this).dialog("close");
                                        vue.$root.$emit("execLogOff");
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                        });
                    }
                },
            ],
        }
    },
    props: {
        query: Object,
        vm: Object,
    },
    components: {
        "VueDataList": VueDataList,
        "VueSelect": VueSelect,
        "VueSelectBusho": VueSelectBusho,
        "PopupSelect": PopupSelect,
        "PqGridWrapper": PqGridWrapper,
        "DatePickerWrapper": DatePickerWrapper,
    },
    computed: {
        userInfo: function () {
            var vue = this;
            return vue.$root.$refs.LogonForm.user;
        },
    },
    watch: {
        footerButtons: {
            handler: function(newVal) { this.$root.$emit("setFooterButtons", newVal); },
        },
    },
    created: function () {
        var vue = this;

        //HTML5 menu polyfill
        $.contextMenu("html5");

        //ViewModel設定
        $.extend(true, vue.viewModel, vue.vm);

        vue.$root.$on("resize", vue.resize);

        vue.createdFunc(vue);

        //TODO: Web Push test
        // Push.create(vue.ScreenTitle, {
        //     body: JSON.stringify(vue.viewModel),
        //     timeout: 1000000,
        //     silent: true,
        //     onClick: function () {
        //         console.log("push clicked");
        //         window.focus();
        //         this.close();
        //     }
        // });
    },
    mounted: function () {
        var vue = this;

        //Vueコンポーネント参照
        window[vue.$options.name] = vue;

        vue.setFooterButtons(vue);
        vue.focused();
        vue.mountedFunc(vue);
    },
    beforeUpdated: function () {
        var vue = this;

        vue.beforeUpdatedFunc(vue);
    },
    updated: function () {
        var vue = this;

        vue.updatedFunc(vue);
    },
    activated: function () {    //画面再表示時はこのイベント
        var vue = this;

        //Vueコンポーネント参照
        window[vue.$options.name] = vue;

        vue.setFooterButtons(vue);
        vue.focused();
        vue.activatedFunc(vue);
    },
    deactivated: function () {
        var vue = this;
        vue.deactivatedFunc(vue);
    },
    destroyed: function () {
        var vue = this;
        vue.destroyedFunc(vue);
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
        },
        beforeUpdatedFunc: function(vue) {
        },
        activatedFunc: function(vue) {
        },
        updatedFunc: function(vue) {
        },
        deactivatedFunc: function(vue) {
        },
        destroyedFunc: function(vue) {
        },
        focused: function() {
            var vue = this;

            //リサイズ
            vue.resize();

            //画面タイトル設定
            vue.$root.$emit("setTitle", vue.ScreenTitle);
        },
        resize: function(size) {
            var vue = vue;
            //
        },
        setFooterButtons: function() {
            this.$root.$emit("setFooterButtons", this.footerButtons);
        },
        getLoginInfo: function() {
            return window.loginInfo || { isLogOn: false };
        },
    }
}
</script>
