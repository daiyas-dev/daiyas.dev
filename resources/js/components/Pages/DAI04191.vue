<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label>銀行ＣＤ</label>
                <input class="form-control text-right" type="text"
                    id="BankCd"
                    v-model=viewModel.銀行CD
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">銀行名</label>
                <input type="text" class="form-control" v-model="viewModel.銀行名" id="BankName">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">銀行名カナ</label>
                <input type="text" class="form-control" style="" v-model="viewModel.銀行名カナ">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>郵便フラグ</label>
                <VueCheck
                    bind="郵便フラグ"
                    :vmodel=viewModel
                    checkedCode="1"
                    customContainerStyle="border-style: none;"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>無効フラグ</label>
                <VueCheck
                    bind="無効フラグ"
                    :vmodel=viewModel
                    checkedCode="1"
                    customContainerStyle="border-style: none;"
                    :disabled=true
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
span.ModeLabel {
    font-size: 15px;
}
.row {
    margin-bottom: 2px;
}
div[class^="col-md"] > label {
    min-width: 80px;
}
fieldset div[class^="col-md"] label {
    min-width: 90px;
}
fieldset fieldset label {
    margin-right: -5px;
}
fieldset {
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    padding-right: 5px;
    margin: 0;
}
fieldset * {
    font-size: 14px !important;
}
legend {
    font-size: 15px;
    width: auto;
    padding: 0;
    margin: 0;
    margin-left: 5px;
    border-bottom:none;
}
fieldset .row {
    margin-left: 10px;
    margin-right: 0px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
.groupFactory{
    width: 100px;
    text-align: left !important;
    margin-left: 30px;
}
</style>
<style>
#Page_DAI04191 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04191",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "金融機関名称メンテ詳細",
            noViewModel: true,
            DAI04191Grid1: null,
            IsChecked: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 200,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                ],
                colModel: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04191_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04191Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                        if(!vue.viewModel.銀行CD){
                            $.dialogErr({
                                title: "登録不可",
                                contents: "銀行CDが入力されていません",
                            })
                            $(vue.$el).find("#BankCd").addClass("has-error");
                            return;
                        }
                        //TODO:下記作業途中
                        if(!vue.viewModel.銀行CD || !vue.viewModel.銀行名){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.銀行CD ? "銀行CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.銀行名 ? "銀行名が入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.銀行CD){
                                $(vue.$el).find("#BankCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.銀行名){
                                $(vue.$el).find("#BankName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankName").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.修正担当者ＣＤ = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //チェックボックス
                        params.郵便フラグ = !!params.郵便フラグ ? params.郵便フラグ : 0;
                        // params.無効フラグ = !!params.無効フラグ ? params.無効フラグ : 0;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //TODO: 登録用controller method call
                        axios.post("/DAI04191/Save", params)
                            .then(res =gyou> {
                                //TODO:途中
                            })
                            .catch(err => {
                                console.log(error);
                                //TODO: エラー
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
    }
}
</script>
