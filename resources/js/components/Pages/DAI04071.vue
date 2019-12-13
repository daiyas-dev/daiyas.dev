<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
            <div class="col-md-3">
                <button class="btn btn-warning" @click="toggleButtons">ボタン状態変更テスト</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input class="form-control text-right" type="text"
                    :value=viewModel.部署CD
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
            <div class="col-md-1" />
            <div class="col-md-1">
                <label class="">部署名</label>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" :value="viewModel.部署名">
            </div>
            <div class="col-md-1">
                <label class="">部署名カナ</label>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control" style="font-size: 15px !important;" :value="viewModel.部署名カナ">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">会社名称</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" :value="viewModel.会社名称">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">郵便番号</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.郵便番号>
            </div>
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" :value=viewModel.住所>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">電話番号</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-1" style="width: 120px;" type="text" :value=viewModel.電話番号>
            </div>
            <div class="col-md-1">
                <label class="">FAX</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-1" style="width: 120px;" type="text" :value=viewModel.FAX>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">口座情報１</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">金融機関名</label>
                            <PopupSelect
                                id="BankSelect1"
                                ref="PopupSelect_Bank1"
                                :vmodel=viewModel
                                bind="金融機関CD1"
                                buddy="金融機関名称1"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: null }"
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名称"
                                :showColumns='[
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onAfterChangedFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect1"
                                ref="PopupSelect_BankBranch1"
                                :vmodel=viewModel
                                bind="金融機関支店CD1"
                                buddy="金融機関支店名1"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD1, BranchCd: null, KeyWord: BankBranchKeyWord }"
                                :isPreload=true
                                title="支店一覧"
                                labelCd="支店CD"
                                labelCdNm="支店名"
                                :showColumns='[
                                    { title: "金融機関CD", dataIndx: "銀行CD", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                                    { title: "金融機関名", dataIndx: "銀行名", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 1 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=190
                                :ParamsChangedCheckFunc=BankBranch1ParamsChangedCheckFunc
                                :onAfterChangedFunc=onBankBranchChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="">口座種別</label>
                            <VueSelect
                                id="KouzaKind"
                                :vmodel=viewModel
                                bind="口座種別1"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 7 }"
                                :withCode=true
                                :hasNull=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-3">
                            <label>口座番号</label>
                            <input class="form-control p-1" style="min-width: 125px;" type="text" :value=viewModel.口座番号1>
                        </div>
                        <div class="col-md-1" />
                        <div class="col-md-5">
                            <label class="">口座名義人</label>
                            <input class="form-control" type="text" style="font-size: 15px !important;" :value=viewModel.口座名義人1>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">口座情報２</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">金融機関名</label>
                            <PopupSelect
                                id="BankSelect2"
                                ref="PopupSelect_Bank2"
                                :vmodel=viewModel
                                bind="金融機関CD2"
                                buddy="金融機関名称2"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: null }"
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名称"
                                :showColumns='[
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onAfterChangedFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect2"
                                ref="PopupSelect_BankBranch2"
                                :vmodel=viewModel
                                bind="金融機関支店CD2"
                                buddy="金融機関支店名2"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD2, BranchCd: null, KeyWord: BankBranchKeyWord }"
                                :isPreload=true
                                title="支店一覧"
                                labelCd="支店CD"
                                labelCdNm="支店名"
                                :showColumns='[
                                    { title: "金融機関CD", dataIndx: "銀行CD", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                                    { title: "金融機関名", dataIndx: "銀行名", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 1 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=190
                                :ParamsChangedCheckFunc=BankBranch2ParamsChangedCheckFunc
                                :onAfterChangedFunc=onBankBranchChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="">口座種別</label>
                            <VueSelect
                                id="KouzaKind"
                                :vmodel=viewModel
                                bind="口座種別2"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 7 }"
                                :withCode=true
                                :hasNull=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-3">
                            <label>口座番号</label>
                            <input class="form-control p-1" style="min-width: 125px;" type="text" :value=viewModel.口座番号2>
                        </div>
                        <div class="col-md-1" />
                        <div class="col-md-5">
                            <label class="">口座名義人</label>
                            <input class="form-control" type="text" style="font-size: 15px !important;" :value=viewModel.口座名義人2>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">モバイル連携情報</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">主要商品CD１</label>
                            <PopupSelect
                                id="ProductSelect1"
                                ref="PopupSelect_Product1"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ1"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForSelect"
                                :params="{ ProductCd: null, KeyWord: ProductKeyWord }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="モバイル_主要商品ＣＤ1"
                                labelCdNm="商品名"
                                :showColumns='[
                                    { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                                    { title: "売価単価", dataIndx: "売価単価", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 3 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=235
                                :onChangeFunc=onProductChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=ProductAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">主要商品CD２</label>
                            <PopupSelect
                                id="ProductSelect2"
                                ref="PopupSelect_Product2"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ2"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForSelect"
                                :params="{ ProductCd: null, KeyWord: ProductKeyWord }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="モバイル_主要商品ＣＤ2"
                                labelCdNm="商品名"
                                :showColumns='[
                                    { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                                    { title: "売価単価", dataIndx: "売価単価", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 3 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=235
                                :onChangeFunc=onProductChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=ProductAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>メッセージ</label>
                            <input class="form-control p-1" style="min-width: 125px;" type="text" :value=viewModel.モバイル_メッセージ>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-12">
                        <label class="groupFactory">部署グループ</label>
                        <VueSelect
                            id="BushoGroup"
                            :vmodel=viewModel
                            bind="部署グループ"
                            uri="/Utilities/GetCodeList"
                            :params="{ cd: 18 }"
                            :withCode=true
                            customStyle="{ width: 100px; }"
                        />
                    </div>
                    <div class="col-md-12">
                        <label class="groupFactory">工場</label>
                        <VueSelect
                            id="factory"
                            :vmodel=viewModel
                            bind="工場ＣＤ"
                            uri="/Utilities/GetCodeList"
                            :params="{ cd: 37 }"
                            :withCode=true
                            :hasNull=true
                            customStyle="{ width: 100px; }"
                        />
                    </div>
                </div>
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
#Page_DAI04071 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04071",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
    },
    watch: {
        "viewModel.金融機関CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関CD1 = "";
                }
            },
        },
        "viewModel.金融機関支店CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0" && !vue.viewModel.金融機関支店名称１) {
                    vue.viewModel.金融機関支店CD1 = "";
                }
            },
        },
        "viewModel.金融機関CD2": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関CD2 = "";
                }
            },
        },
        "viewModel.金融機関支店CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関支店CD1 = "";
                }
            },
        },
        "viewModel.金融機関支店CD2": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0" && !vue.viewModel.金融機関支店名称２) {
                    vue.viewModel.金融機関支店CD2 = "";
                }
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "部署マスタメンテ詳細",
            noViewModel: true,
            DAI04071Grid1: null,
            BankKeyWord: null,
            BankBranchKeyWord: null,
            ProductKeyWord: null,
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
                { visible: "true", value: "クリア", id: "DAI04071_Clear", disabled: false, shortcut: "F2",
                    onClick: function (evt) {
                        //TODO: クリア
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "削除", id: "DAI04071_Delete", disabled: false, shortcut: "F3",
                    onClick: function (evt) {
                        //TODO: 削除
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04071Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var params = _.cloneDeep(vue.viewModel);

                        //金融機関CD、金融機関支店CD: nullの0置換
                        params.金融機関CD1 = params.金融機関CD1 || 0;
                        params.金融機関CD2 = params.金融機関CD2 || 0;
                        params.金融機関支店CD1 = params.金融機関支店CD1 || 0;
                        params.金融機関支店CD2 = params.金融機関支店CD2 || 0;

                        //TODO: 登録用controller method call
                        console.log("登録", params);
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04071_Csv", disabled: false, shortcut: "F10",
                    onClick: function () {
                        //TODO: CSV
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
        BankSelectorParamsFunc: function(params, comp) {
            params.KeyWord = null;
            params.BankCd = null;
            return params;
        },
        onBankChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        onProductChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        BankAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["銀行名", "銀行名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.銀行CD.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.銀行CD + " : " + v.銀行名 + "【" + v.銀行名カナ + "】";
                    ret.value = v.銀行CD;
                    ret.text = v.銀行名;
                    return ret;
                })
                ;

            return list;
        },
        BankBranch1ParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0;
            return ret;
        },
        BankBranch2ParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0;
            return ret;
        },
        onBankBranchChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        BankBranchAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "支店名カナ"];

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
                    ret.label = v.Cd + " : " + v.CdNm+ "【" + v.支店名カナ + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            return list;
        },
        ProductAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "商品区分", "各種名称", "売価単価"];

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
                    ret.label = v.Cd + " : " + v.CdNm+ "【 " + v.商品区分 + "：" + v.各種名称 + "】"+ "￥" + v.売価単価 ;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            return list;
        },
        toggleButtons: function() {
            var vue = this;
            vue.footerButtons.forEach(v => v["disabled"] = !v["disabled"]);
        },
    }
}
</script>
