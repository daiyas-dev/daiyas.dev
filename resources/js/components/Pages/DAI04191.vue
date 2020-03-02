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
                    @change="onBankCdChanged"
                    maxlength="4"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">銀行名</label>
                <input type="text" class="form-control"
                    v-model="viewModel.銀行名"
                    id="BankNm"
                    maxlength=50
                    v-maxBytes=50
                    v-setKana.disabled="res => viewModel.銀行名カナ = res.toString()"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">銀行名カナ</label>
                <input type="text" class="form-control" style=""
                    v-model="viewModel.銀行名カナ"
                    maxlength=30
                    v-maxBytes=30
                >
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
                        vue.clearDetail();
                    }
                },
                { visible: "true", value: "削除", id: "DAI04191_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        var cd = vue.viewModel.銀行CD;
                        if(!cd) return;

                        var params = {BankCd: cd};
                        params.noCache = true;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04191/Delete", params)
                                            .then(res => {
                                                DAI04190.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04191を閉じる
                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            })
                                            .catch(err => {
                                                console.log(err);
                                            }
                                        );
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
                        console.log("削除", params);
                    }
                },
                { visible: "true", value: "登録", id: "DAI04191Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
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
                                $(vue.$el).find("#BankNm").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankNm").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.修正担当者ＣＤ = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //チェックボックス
                        params.郵便フラグ = !!params.郵便フラグ ? params.郵便フラグ : 0;
                        //TODO:無効フラグは追加カラムのため未完了
                        // params.無効フラグ = !!params.無効フラグ ? params.無効フラグ : 0;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04191/Save", params)
                            .then(res => {
                                DAI04190.conditionChanged();
                                $(this).dialog("close");
                            })
                            .catch(err => {
                                console.log(err);
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onBankCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByBankCd();
        },
        searchByBankCd: function() {
            var vue = this;
            var cd = vue.viewModel.銀行CD;
            if (!cd) return;

            var params = {BankCd: cd};
            params.noCache = true;

            axios.post("/DAI04191/GetBankListForDetail", params)
                .then(res => {
                    if (res.data.length == 1) {
                        $.dialogConfirm({
                            title: "マスタ編集確認",
                            contents: "マスタを編集しますか？",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        $(vue.$el).find(".has-error").removeClass("has-error");
                                        vue.viewModel = res.data[0];
                                        $(this).dialog("close");

                                        //新規 -> 修正：　ボタン制御
                                        $("[shortcut='F3']").prop("disabled", false);
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        vue.viewModel.銀行CD = "";
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                        });
                    }else{
                    }
                })
                .catch(err => {
                    console.log(err);
                }
            )
        },
        clearDetail: function(){
            var vue = this;

            $(vue.$el).find(".has-error").removeClass("has-error");

            _.keys(DAI04191.viewModel).forEach(k => DAI04191.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);
        },
    }
}
</script>
