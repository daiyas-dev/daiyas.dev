<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">金融機関</label>
                <PopupSelect
                    id="BankSelect"
                    ref="PopupSelect_Bank"
                    :vmodel=viewModel
                    bind="銀行CD"
                    buddy="銀行名"
                    dataUrl="/Utilities/GetBankList"
                    :params="{ BankCd: viewModel.銀行CD, KeyWord: BankKeyWord }"
                    :SelectorParamsFunc=BankSelectorParamsFunc
                    :isPreload=true
                    title="金融機関一覧"
                    labelCd="金融機関CD"
                    labelCdNm="金融機関名"
                    :isShowName=true
                    :isModal=true
                    :editable=!!viewModel.IsNew
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=235
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=BankAutoCompleteFunc
                    :AutoCompleteMinLength=1
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>支店ＣＤ</label>
                <input class="form-control text-right" type="text"
                    id="BankBranchCd"
                    v-model=viewModel.支店CD
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onBankBranchCdChanged"
                    maxlength="4"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">支店名</label>
                <input type="text" class="form-control"
                    id="BankBranchNm"
                    v-model="viewModel.支店名"
                    maxlength=50
                    v-maxBytes=50
                    v-setKana="res => {viewModel.支店名カナ = (viewModel.支店名カナ || '') + res.toString(); $forceUpdate();}"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">支店名カナ</label>
                <input type="text" class="form-control" style=""
                    v-model="viewModel.支店名カナ"
                    maxlength=50
                    v-maxBytes=50
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>無効</label>
                <VueCheck
                    bind="無効フラグ"
                    :vmodel=viewModel
                    checkedCode="1"
                    :list="[
                        {code: '0', label: 'チェック無し：有効な支店CD'},
                        {code: '1', label: 'チェック有り：無効な支店CD'}
                    ]"
                    customContainerStyle="border-style: none;"
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
#Page_DAI04201 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04201",
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
            ScreenTitle: "金融機関支店名称メンテ詳細",
            noViewModel: true,
            DAI04201Grid1: null,
            BankKeyWord: null,
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

        data.viewModel.無効フラグ = data.viewModel.無効フラグ || "";

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04201_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.clearDetail();
                    }
                },
                { visible: "true", value: "削除", id: "DAI04201_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        var BankCd = vue.viewModel.銀行CD;
                        var BankBranchCd = vue.viewModel.支店CD;
                        if (!BankCd || !BankBranchCd) return;

                        var params = {BankCd: BankCd, BankBranchCd: BankBranchCd};
                        params.noCache = true;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04201/Delete", params)
                                            .then(res => {
                                                DAI04200.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04201を閉じる
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
                { visible: "true", value: "登録", id: "DAI04201Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.viewModel.銀行CD || !vue.viewModel.支店CD || !vue.viewModel.支店名){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.銀行CD ? "銀行CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.支店CD ? "支店CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.支店名 ? "支店名が入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.銀行CD){
                                $(vue.$el).find("#BankCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.支店CD){
                                $(vue.$el).find("#BankBranchCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankBranchCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.支店名){
                                $(vue.$el).find("#BankBranchNm").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BankBranchNm").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //チェックボックス
                        params.無効フラグ = !!params.無効フラグ ? params.無効フラグ : 0;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04201/Save", params)
                            .then(res => {
                                DAI04200.conditionChanged();
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
        BankSelectorParamsFunc: function(params, comp) {
            params.KeyWord = null;
            params.BankCd = null;
            return params;
        },
        // onBankChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
        //     var vue = this;
        //     console.log("onBankChanged", info, comp, isValid);
        //     if (!isValid) {
        //         vue.BankKeyWord = comp.selectValue;
        //     }
        // },
        BankAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "銀行名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BankAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onBankBranchCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByCds();
        },
        searchByCds: function() {
            var vue = this;
            var BankCd = vue.viewModel.銀行CD;
            var BankBranchCd = vue.viewModel.支店CD;
            if (!BankCd || !BankBranchCd) return;

            var params = {BankCd: BankCd, BankBranchCd: BankBranchCd};
            params.noCache = true;

            axios.post("/DAI04201/GetBankBranchListForDetail", params)
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
                                        vue.viewModel.支店CD = "";
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

            _.keys(DAI04201.viewModel).forEach(k => DAI04201.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);
        },
    }
}
</script>
