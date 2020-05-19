<style>
</style>

<script>
/** 以下、VueComponent設定 **/
import VueDataList from "@vcs/DataList.vue";
import VueSelect from "@vcs/VueSelect.vue";
import VueSelectBusho from "@vcs/VueSelectBusho.vue";
import VueMultiSelect from "@vcs/VueMultiSelect.vue";
import VueOptions from "@vcs/VueOptions.vue";
import VueCheck from "@vcs/VueCheck.vue";
import VueCheckList from "@vcs/VueCheckList.vue";
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
                    visible: "true", value: "再起動", align: "right", shortcut: "F12",
                    class: "btn-danger",
                    onClick: function() {
                        $.dialogConfirm({
                            title: "確認",
                            contents: "画面を再起動してよろしいですか？(※確認用 本来はシステム終了となります)",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        $(this).dialog("close");
                                        location.reload();
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
                        // $.dialogConfirm({
                        //     title: "確認",
                        //     contents: "終了してよろしいですか？",
                        //     buttons:[
                        //         {
                        //             text: "はい",
                        //             class: "btn btn-primary",
                        //             click: function(){
                        //                 $(this).dialog("close");
                        //                 vue.$root.$emit("execLogOff");
                        //             }
                        //         },
                        //         {
                        //             text: "いいえ",
                        //             class: "btn btn-danger",
                        //             click: function(){
                        //                 $(this).dialog("close");
                        //             }
                        //         },
                        //     ],
                        // });
                    }
                },
            ],
        }
    },
    props: {
        params: Object,
        query: Object,
        vm: Object,
    },
    components: {
        "VueDataList": VueDataList,
        "VueSelect": VueSelect,
        "VueSelectBusho": VueSelectBusho,
        "VueMultiSelect": VueMultiSelect,
        "VueOptions": VueOptions,
        "VueCheck": VueCheck,
        "VueCheckList": VueCheckList,
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
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;

                vue.setFooterButtons();
            },
        },
    },
    created: function () {
        var vue = this;

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

        //vue.setFooterButtons(vue);

        //set dropzone
        $(vue.$el).parent().find(".droppable[data-url]")
            .each((i, v) => {
                var dz = new Dropzone(v,
                    {
                        url: $(v).attr("data-url"),
                        headers: {
                            "X-CSRF-TOKEN": Laravel.csrfToken,
                        },
                        params: {
                        },
                        clickable: true,
                        createImageThumbnails: false,
                        previewTemplate : '<div></div>',
                        dictDefaultMessage: "対象ファイルをドロップ、もしくはここをクリックして選択",
                        uploadMultiple: false,
                        init: function() {
                            this.on("sending", function(event, xhr, formData){
                                if ($(v).attr("data-sending-callback")) {
                                    vue[$(v).attr("data-sending-callback")](event, xhr, formData);
                                }
                            });
                            this.on("addedfile", function(event) {
                                if ($(v).attr("data-addedfile-callback")) {
                                    vue[$(v).attr("data-addedfile-callback")](event);
                                }
                            });
                            this.on("drop", function(event) {
                                if ($(v).attr("data-drop-callback")) {
                                    vue[$(v).attr("data-drop-callback")](event);
                                }
                            });
                            this.on("success", function(event, json) {
                                if ($(v).attr("data-upload-callback")) {
                                    vue[$(v).attr("data-upload-callback")](json);
                                }
                            });
                            this.on("error", async function(event, errorMessage) {
                                if ($(v).attr("data-drop-error-callback")) {
                                    vue[$(v).attr("data-drop-error-callback")](event, errorMessage);
                                }
                            });
                        },
                        addedFile: file => console.log(file),
                    }
                );
            });

        $(vue.$el).parents("div.body-content").removeClass("Scrollable");

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
            if (vue.$attrs.isChild) {
                vue.$parent.$emit("setDialogTitle", {uniqueId: vue.$attrs.uniqueId, title: vue.ScreenTitle});
            } else {
                vue.$root.$emit("setTitle", vue.ScreenTitle);
            }

            //first focus
            if (vue.IsFirstFocus != false) {
                var first = $(vue.$el).find(":input:first")
                if (first.parent().hasClass("DatePickerWrapper")) {
                    first = first.siblings(".calendar-button");
                } else if (first.hasClass("ui-autocomplete-input")) {
                    vue.$refs["PopupSelect_" + first.prop("id")].AutoCompleteFocusSkip = true;
                }
                first.focus();
            }
        },
        resize: function(size) {
            var vue = vue;
            //
        },
        setFooterButtons: function() {
            var vue = this;

            if (vue.$attrs.isChild) {
                vue.$parent.$emit("setDialogButtons", {uniqueId: vue.$attrs.uniqueId, buttons: vue.footerButtons});
            } else {
                vue.$root.$emit("setFooterButtons", vue.footerButtons);
            }
        },
        getLoginInfo: function() {
            return window.loginInfo || { isLogOn: false };
        },
    }
}
</script>
