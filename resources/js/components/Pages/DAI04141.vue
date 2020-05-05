<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>税区分</label>
                <input class="form-control text-right" type="text"
                    id="TaxKbn"
                    v-model=viewModel.税区分
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onTaxKbnChanged"
                    maxlength="6"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">消費税名称</label>
                <input type="text" id="TaxName" class="form-control"
                    v-model="viewModel.消費税名称"
                    maxlength=30
                    v-maxBytes=30
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="">消費税率</label>
                <input type="text" class="form-control text-right" v-model="viewModel.消費税率"
                    maxlength="3"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>内外区分</label>
                <VueSelect
                    id="NaigaiKbn"
                    ref="NaigaiKbn_Select"
                    :vmodel=viewModel
                    bind="内外区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 20 }"
                    codeName="サブ各種CD1"
                    :withCode=true
                    :hasNull=false
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>適用年月</label>
                <DatePickerWrapper
                    id="TekiyoDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="適用年月"
                    :editable=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">現在利用</label>
                <VueSelect
                    id="RiyoFlg"
                    ref="RiyoFlg_Select"
                    :vmodel=viewModel
                    bind="現在使用FLG"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 21 }"
                    :withCode=true
                    :hasNull=false
                    customStyle="{ width: 100px; }"
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
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
<style>
#Page_DAI04141 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";
import DAI04140Vue from './DAI04140.vue';

export default {
    mixins: [PageBaseMixin],
    name: "DAI04141",
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
            ScreenTitle: "消費税率マスタメンテ詳細",
            noViewModel: true,
            DAI04141Grid1: null,
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
                { visible: "true", value: "クリア", id: "DAI04141_Clear", disabled: false, shortcut: "F2",
                    onClick: function (evt) {
                        vue.clearDetail();
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "削除", id: "DAI04141_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        var cd = vue.viewModel.税区分;
                        if(!cd) return;

                        var params = {TaxCd: cd};
                        params.noCache = true;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04141/Delete", params)
                                            .then(res => {
                                                DAI04140.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04141を閉じる
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
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "登録", id: "DAI04141Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.viewModel.税区分 || !vue.viewModel.消費税名称 || !vue.viewModel.適用年月){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.税区分 ? "税区分が入力されていません。<br/>" : "",
                                    !vue.viewModel.消費税名称 ? "消費税名称が入力されていません。<br/>" : "",
                                    !vue.viewModel.適用年月 ? "適用年月が入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.税区分){
                                $(vue.$el).find("#TaxKbn").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TaxKbn").removeClass("has-error");
                            }
                            if(!vue.viewModel.消費税名称){
                                $(vue.$el).find("#TaxName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TaxName").removeClass("has-error");
                            }
                            if(!vue.viewModel.適用年月){
                                $(vue.$el).find(".TekiyoDate").addClass("has-error");
                            }else{
                                $(vue.$el).find(".TekiyoDate").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.消費税率 = params.消費税率 || 0;
                        params.適用年月 = moment(vue.viewModel.適用年月,"YYYY-MM-DD").format("YYYY-MM-DD");
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04141/Save", params)
                            .then(res => {
                                DAI04140.conditionChanged();
                                //画面04141を閉じる
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            })
                            .catch(err => {
                                console.log(err);
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );

            //適用年月の初期値 -> 当日
            vue.viewModel.適用年月 = vue.params.適用年月 || moment().format("YYYY-MM-DD HH:mm:ss.SSS");
        },
        mountedFunc: function(vue) {

            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onTaxKbnChanged: function(code, entities) {
            var vue = this;

            vue.searchByTaxKbn();
        },
        searchByTaxKbn: function() {
            var vue = this;
            var cd = vue.viewModel.税区分;
            if (!cd) return;

            var params = {TaxCd: cd};
            params.noCache = true;

            axios.post("/DAI04141/GetTaxListForDetail", params)
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
                                        vue.viewModel = res.data[0];
                                        $(this).dialog("close");
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        vue.viewModel.税区分 = "";
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                        });
                        $("[shortcut='F3']").prop("disabled", false);
                    }else{
                        //削除ボタン制御
                        $("[shortcut='F3']").prop("disabled", true);
                        return;
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

            _.keys(DAI04141.viewModel).forEach(k => DAI04141.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            vue.viewModel.適用年月 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.viewModel.内外区分 = vue.$refs.NaigaiKbn_Select.entities[0].code;
            vue.viewModel.現在使用FLG = vue.$refs.RiyoFlg_Select.entities[0].code;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);

        },
    }
}
</script>
