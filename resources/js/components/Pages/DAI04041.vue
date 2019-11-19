<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
            <div class="col-md-2">
                <label>得意先ＣＤ</label>
                <input class="form-control text-right" type="text"
                    :value=viewModel.得意先ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
            <div class="col-md-2">
                <label>状態</label>
                <VueSelect
                    id="State"
                    :vmodel=viewModel
                    bind="状態区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 12 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-3">
                <label>承認日</label>
                <DatePickerWrapper
                    id="ShoninDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="承認日"
                    :editable=true
                />
            </div>
            <div class="col-md-4">
                <label>承認者</label>
                <VueSelect
                    id="ShoninCd"
                    :vmodel=viewModel
                    bind="承認者ＣＤ"
                    buddy="承認者名"
                    uri="/Utilities/GetTantoList"
                    :params="{ bushoCd: viewModel.部署CD }"
                    :withCode=true
                    customStyle="{ width: 150px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="kihon-info w-100">
                    <legend class="kihon-info">基本情報</legend>
                    <div class="row">
                        <div class="col-md-10">
                            <label class="">得意先名</label>
                            <input type="text" class="form-control" :value="viewModel.得意先名">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">得意先名カナ</label>
                            <input type="text" class="form-control" :value="viewModel.得意先名カナ">
                        </div>
                        <div class="col-md-6">
                            <label class="">得意先名略称</label>
                            <input type="text" class="form-control" :value="viewModel.得意先名略称">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label>部署</label>
                            <VueSelectBusho　/>
                        </div>
                        <div class="col-md-5">
                            <label class="">売掛現金区分</label>
                            <VueSelect
                                id="UriGenKbn"
                                :vmodel=viewModel
                                bind="売掛現金区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 1 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">郵便番号</label>
                            <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.郵便番号>
                        </div>
                        <div class="col-md-9">
                            <label>住所</label>
                            <input class="form-control" type="text" :value=viewModel.住所１>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 offset-md-3">
                            <label></label>
                            <input class="form-control" type="text" :value=viewModel.住所２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">電話番号1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号１>
                        </div>
                        <div class="col-md-3">
                            <label class="">電話番号2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号２>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ１>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">お届け先</legend>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">郵便番号</label>
                                        <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.お届け先郵便番号>
                                    </div>
                                    <div class="col-md-9">
                                        <label>住所</label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所１>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9 offset-md-3">
                                        <label></label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所２>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">電話番号1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">電話番号2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号２>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ２>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3">
                右ペイン
            </div>
            <div class="col-md-9">
                <fieldset class="shiharai-info w-100">
                    <legend class="shiharai-info">支払情報</legend>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">締区分</label>
                            <VueSelect
                                id="SimeKbn"
                                :vmodel=viewModel
                                bind="締区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 3 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">締日1</label>
                            <input class="form-control text-right" style="width: 40px;" type="text" :value=viewModel.締日１>
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">締日2</label>
                            <input class="form-control text-right" style="width: 40px;" type="text" :value=viewModel.締日２>
                        </div>
                        <div class="col-md-3">
                            <label class="">支払サイト</label>
                            <VueSelect
                                id="ShiharaiSite"
                                :vmodel=viewModel
                                bind="支払サイト"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 4 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">支払日</label>
                            <input class="form-control text-right" style="width: 40px;" type="text" :value=viewModel.支払日>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <label class="">請求先CD</label>
                            <PopupSelect
                                id="BillingSelect"
                                ref="PopupSelect_Billing"
                                :vmodel=viewModel
                                bind="請求先ＣＤ"
                                dataUrl="/Utilities/GetCustomerList"
                                :params="{ KeyWord: BillingKeyWord }"
                                :isPreload=true
                                title="請求先一覧"
                                labelCd="請求先CD"
                                labelCdNm="請求先名"
                                :showColumns='[
                                ]'
                                :popupWidth=1000
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=150
                                :nameWidth=400
                                :onChangeFunc=onBillingChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BillingAutoCompleteFunc
                                :AutoCompleteMinLength=1
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 offset-md-3">
                            <label></label>
                            <input class="form-control" type="text" :value=viewModel.住所２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">電話番号1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号１>
                        </div>
                        <div class="col-md-3">
                            <label class="">電話番号2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号２>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ１>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">お届け先</legend>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">郵便番号</label>
                                        <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.お届け先郵便番号>
                                    </div>
                                    <div class="col-md-9">
                                        <label>住所</label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所１>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9 offset-md-3">
                                        <label></label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所２>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">電話番号1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">電話番号2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号２>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ２>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3">
                右ペイン
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
</style>
<style>
#Page_DAI04041 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04041",
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
    watch: {
        "viewModel.IsShowAll": {
            handler: function(newVal) {
                console.log("viewModel.IsShowAll:" + newVal);
                var vue = this;
                var grid = vue.DAI04041Grid1;

                grid.filter({
                    oper: "replace",
                    rules: newVal ? [] : [{ dataIndx: "全表示", condition: "notequal", value: "1" }],
                });
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理->仕出注文入力",
            noViewModel: true,
            DAI04041Grid1: null,
            BillingKeyWord: null,
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
                    [
                        "sortIndx",
                        function(rowData){
                            return (rowData["商品ＣＤ"] * 1) || 99999;
                        }
                    ],
                    [
                        "現金金額",
                        function(rowData){
                            return rowData["単価"] * rowData["現金個数"];
                        }
                    ],
                    [
                        "掛売金額",
                        function(rowData){
                            return rowData["単価"] * rowData["掛売個数"];
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "全表示",
                        dataIndx: "全表示", dataType: "integer",
                        width: 80, maxWidth: 80, minWidth: 80,
                        hidden: true,
                    },
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 120, maxWidth: 120, minWidth: 120,
                        editable: ui => {
                            var vue = this;
                            var grid = DAI04041Grid1;

                            if (grid.getSelectionRowData()) {
                                if (grid.getSelectionRowData().pq_ri == ui.rowData.pq_ri
                                    &&
                                    _(grid.getSelectionData()).keys().first() == "商品ＣＤ"
                                ) {
                                    return true;
                                }
                            }

                            return !ui.rowData[ui.dataIndx];
                        },
                        dataUrl: "/Utilities/GetProductList",
                        buddy: "商品名",
                        selectorTitle: "商品一覧",
                        labelCd: "商品ＣＤ",
                        labelCdNm: "商品名",
                        isModal: true,
                        reuse: true,
                        existsCheck: true,
                        onChangeFunc: function(element, info, comp, isNoMsg, isValid) {
                            console.log("grid popupselect onchange func");

                            comp.ui.rowData["商品名"] = info["商品名"];
                            comp.ui.rowData["単価"] = info["売価単価"];
                            comp.grid.refreshCell({ rowIndx: comp.ui.rowIndx, dataIndx: "商品名"});
                            comp.grid.refreshCell({ rowIndx: comp.ui.rowIndx, dataIndx: "単価"});
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "予定数",
                        dataIndx: "予定数", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["予定数"] = "合計";
                                return { text: "合計" };
                            } else {
                                //0非表示
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "現金",
                        dataIndx: "現金",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "現金個数", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        // if (!(ui.rowData[ui.dataIndx] * 1)) {
                                        //     return { text: "" };
                                        // }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "現金金額", dataType: "integer", format: "#,##0",
                                width: 120, maxWidth: 120, minWidth: 120,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        if (!(ui.rowData[ui.dataIndx] * 1)) {
                                            return { text: "" };
                                        }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    {
                        title: "掛売",
                        dataIndx: "掛売",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "掛売個数", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        // if (!(ui.rowData[ui.dataIndx] * 1)) {
                                        //     return { text: "" };
                                        // }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "掛売金額", dataType: "integer", format: "#,##0",
                                width: 120, maxWidth: 120, minWidth: 120,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        if (!(ui.rowData[ui.dataIndx] * 1)) {
                                            return { text: "" };
                                        }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    // {
                    //     title: "確認",
                    //     dataIndx: "Checked", type: "checkbox",
                    //     width: 40, maxWidth: 40, minWidth: 40,
                    //     align: "center",
                    //     cbId: "CheckState",
                    //     render: ui => {
                    //         if (ui.rowData.summaryRow) {
                    //             //合計行では非表示
                    //             return "";
                    //         }
                    //     },
                    // },
                    // {
                    //     title: "確認チェック状態",
                    //     dataIndx: "CheckState",
                    //     dataType: "bool",
                    //     cb: { header: false },
                    //     hidden: true,
                    //     editable: true,
                    // },
                ],
            },
        });

        //TODO:
        if (!!vue.$route && !!vue.$route.query) {
            data.viewModel = vue.$route.query;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
        onBillingChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBillingChanged", info, comp, isValid);
            if (!isValid) {
                vue.BillingKeyWord = comp.selectValue;
            }
        },
        BillingAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BillingAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
    }
}
</script>
