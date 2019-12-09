<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
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
                                id="BankSelect"
                                ref="PopupSelect_Bank"
                                :vmodel=viewModel
                                bind="金融機関CD1"
                                buddy="金融機関名称1"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: null, KeyWord: null }"
                                :SelectorParamsFunc=BankSelectorParamsFunc
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名称1"
                                :showColumns='[
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
                                :onChangeFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                                :AutoCompleteMinLength=1
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect"
                                ref="PopupSelect_BankBranch"
                                :vmodel=viewModel
                                bind="金融機関支店CD1"
                                buddy="金融機関支店名"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD1, BranchCd: viewModel.金融機関支店CD1, KeyWord: BankBranchKeyWord1 }"
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
                                :ParamsChangedCheckFunc=BankBranchParamsChangedCheckFunc
                                :onChangeFunc=onBankBranchChanged1
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                :AutoCompleteMinLength=1
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
                                id="BankSelect"
                                ref="PopupSelect_Bank"
                                :vmodel=viewModel
                                bind="金融機関CD2"
                                buddy="金融機関名"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: viewModel.金融機関CD2, KeyWord: BankKeyWord }"
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名"
                                :showColumns='[
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
                                :onChangeFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                                :AutoCompleteMinLength=1
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect"
                                ref="PopupSelect_BankBranch"
                                :vmodel=viewModel
                                bind="金融機関支店CD2"
                                buddy="金融機関支店名"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD2, BranchCd: viewModel.金融機関支店CD2, KeyWord: BankBranchKeyWord2 }"
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
                                :ParamsChangedCheckFunc=BankBranchParamsChangedCheckFunc
                                :onChangeFunc=onBankBranchChanged2
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                :AutoCompleteMinLength=1
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
                                ref="PopupSelect_Product"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ1"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForMaint"
                                :params="{ ProductCd: null }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="モバイル_主要商品ＣＤ1"
                                labelCdNm="商品名"
                                :showColumns='[
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
                                ref="PopupSelect_Product"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ2"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForMaint"
                                :params="{ ProductCd: null }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="モバイル_主要商品ＣＤ2"
                                labelCdNm="商品名"
                                :showColumns='[
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
        FormattedDeliveryDate: function() {
            var vue = this;
            return vue.viewModel.DeliveryDate ? moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 部署マスタメンテ > 部署マスタメンテ詳細",
            noViewModel: true,
            DAI04071Grid1: null,
            BankKeyWord: null,
            BankBranchKeyWord1: null,
            BankBranchKeyWord2: null,
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

        if (!!vue.$route && !!vue.$route.query) {
            data.viewModel = vue.$route.query;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04071_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04071_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04071Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                        console.log("登録");
                        return;

                        //var targets = $.extend(true, {}, grid.createSaveParams());
                        var targets = grid.getCellsByClass({cls: "pq-cell-dirty"})
                            .map(v => {
                                return {
                                    "部署CD": v.rowData["部署CD"],
                                    "コースＣＤ": v.rowData["コースＣＤ"],
                                    "商品CD": v.dataIndx,
                                    "個数": v.rowData[v.dataIndx],
                                    "対象日付": v.rowData["対象日付"],
                                };
                            });
                        var conditions = $.extend(true, {}, vue.viewModel);

                        vue.DAI01020Grid1.saveData(
                            {
                                uri: "/DAI01020/Save",
                                params: { targets: targets },
                                //optional: { conditions: conditions },
                                // done: {
                                //     callback: (gridVue, grid, res) => {
                                //         vue.DAI01020Grid1.searchData(params);
                                //     },
                                // },
                            }
                        );
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
            console.log("onBankChanged", info, comp, isValid);
            if (!isValid) {
                vue.BankKeyWord = comp.selectValue;
            }
        },
        onProductChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onProductChanged", info, comp, isValid);
            if (!isValid) {
                vue.ProductKeyWord = comp.selectValue;
            }
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
        BankBranchParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd;
            console.log("BankBranchParamsChangedCheckFunc", ret);
            return ret;
        },
        onBankBranchChanged1: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankBranchChanged1", info, comp, isValid);
            if (!isValid) {
                vue.BankBranchKeyWord1 = comp.selectValue;
            }
        },
        onBankBranchChanged2: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankBranchChanged2", info, comp, isValid);
            if (!isValid) {
                vue.BankBranchKeyWord2 = comp.selectValue;
            }
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
            console.log("BankBranchAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        ProductAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "商品略称"];

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
                    ret.label = v.Cd + " : " + v.CdNm+ "【" + v.商品略称 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("ProductAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
    }
}
</script>
