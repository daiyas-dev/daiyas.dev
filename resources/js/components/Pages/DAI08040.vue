<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :hasNull=true
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-5">
                <label>配達日付</label>
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配達区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="DeliveryKbn"
                    :hasNull=true
                    :vmodel=viewModel
                    bind="DeliveryKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 31 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onDeliveryKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: viewModel.CustomerCd, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=400
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>エリア</label>
            </div>
            <div class="col-md-3">
                <PopupSelect
                    id="AreaSelect"
                    ref="PopupSelect_Area"
                    :vmodel=viewModel
                    bind="AreaCd"
                    dataUrl="/DAI08040/GetCourseList"
                    :params="{ BushoCd: viewModel.BushoCd, WithZero: true }"
                    :dataListReset=true
                    :exceptCheck="[{ Cd: 0 }]"
                    title="エリア一覧"
                    labelCd="エリアCD"
                    labelCdNm="エリア名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=250
                    :onAfterChangedFunc=onAreaChanged
                    :isShowAutoComplete=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力順</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="PrintOrder"
                    ref="VueOptions_PrintOrder"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintOrder"
                    :list="[
                        {code: '0', name: '配達順', label: '0:配達順'},
                        {code: '1', name: '受注Ｎｏ順', label: '1:受注Ｎｏ順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
                />
            </div>
            <div class="col-md-2">
               <VueCheck
                    id="VueCheck_PrintOut"
                    ref="VueCheck_PrintOut"
                    :vmodel=viewModel
                    bind="IsPrintOut"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: '預り金有りのみ出力する'},
                        {code: '1', name: 'する', label: '預り金有りのみ出力する'},
                    ]"
                    :onChangedFunc=onPrintoutChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI08040Grid1"
            ref="DAI08040Grid1"
            dataUrl="/DAI08040/GetHaisoYoteiHyo"
            :query=searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI08040"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI08040"] .top-wrap {
    align-items: flex-start;
    white-space: pre-wrap !important;
}
form[pgid="DAI08040"] .pq-grid-header-table .pq-td-div {
    height: 50px;
}
form[pgid="DAI08040"] .pq-group-toggle-none {
    display: none !important;
}
form[pgid="DAI08040"] .pq-group-icon {
    display: none !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08040",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI08040Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        searchParams: function() {
            var vue = this;
            var ms = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日");
            return {
                BushoCd: vue.viewModel.BushoCd,
                AreaCd: vue.viewModel.AreaCd,
                CustomerCd: vue.viewModel.CustomerCd,
                DeliveryDate: ms.isValid() ? ms.format("YYYYMMDD") : null,
                DeliveryKbn: vue.viewModel.DeliveryKbn,
                IsPrintOut: vue.viewModel.IsPrintOut,
                PrintOrder: vue.viewModel.PrintOrder,
            };
        },
    },
    watch: {
        "DAI08040Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI08040Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理 > 配送予定表",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                AreaCd: null,
                CustomerCd: null,
                CustomerNm: null,
                DeliveryDate: null,
                FilterMode: "AND",
                DeliveryKbn: null,
                IsPrintOut: "0",
                PrintOrder: "0",
            },
            DAI08040Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead:50,
                rowHt: 35,
                freezeCols: 6,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                trackModel: { on: false },
                historyModel: { on: false },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["ＧＫエリア", "ＧＫ受注Ｎｏ"],
                    showSummary: [true, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "ＧＫエリア",
                        dataIndx: "ＧＫエリア",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "ＧＫ受注Ｎｏ",
                        dataIndx: "ＧＫ受注Ｎｏ",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "受渡<br/>時間",
                        dataIndx: "配達時間",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                        render: ui => {
                            switch (ui.rowData.gidx) {
                                case 0:
                                    return ui;
                                    break;
                                case 2:
                                    return { text: ui.rowData.売掛現金区分名称 };
                                    break;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "受注Ｎｏ<br/>連絡/配達",
                        dataIndx: "受注連絡",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                        render: ui => {
                            switch (ui.rowData.gidx) {
                                case 0:
                                    return { text: ui.rowData.受注Ｎｏ };
                                    break;
                                case 1:
                                    return { text: ui.rowData.連絡区分名称 };
                                    break;
                                case 2:
                                    return { text: ui.rowData.配達区分名称 };
                                    break;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                    },
                    {
                        title: "得意先名<br/>住所/配達先",
                        dataIndx: "得意先",
                        dataType: "string",
                        width: 300, minWidth: 300, maxWidth: 300,
                        render: ui => {
                            switch (ui.rowData.gidx) {
                                case 0:
                                    return { text: _.padStart(ui.rowData.得意先ＣＤ, 8, " ") + ui.rowData.得意先名 };
                                    break;
                                case 1:
                                    return { text: ui.rowData.住所 };
                                    break;
                                case 2:
                                    return { text: ui.rowData.配達先 };
                                    break;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "エリアＣＤ",
                        dataIndx: "エリアＣＤ",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号１",
                        dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        render: ui => {
                            switch (ui.rowData.gidx) {
                                case 0:
                                    return ui;
                                    break;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "地域<br/>AMPM",
                        dataIndx: "地域区分",
                        dataType: "string",
                        width: 70, minWidth: 70, maxWidth: 70,
                        render: ui => {
                            switch (ui.rowData.gidx) {
                                case 0:
                                    return { text: ui.rowData.地域区分名称 };
                                    break;
                                case 1:
                                    return { text: ui.rowData.AMPM区分名称 };
                                    break;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "ＣＤ",
                        dataIndx: "商品ＣＤ",
                        dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "＊ ＊　合　　計　＊ ＊" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 0:
                                        return { text: "＊ ＊　エリア合計　＊ ＊" };
                                    case 1:
                                        return { text: "＊ ＊　伝票合計　＊ ＊" };
                                    default:
                                        return { text: "" };
                                }
                            }
                            return { text:ui };
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "数量",
                        dataType: "integer",
                        format: "#,##0",
                        width: 50, minWidth: 50, maxWidth: 50,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "単価",
                        dataIndx: "単価",
                        dataType: "integer",
                        format: "#,##0",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "金額",
                        dataIndx: "金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "消費税",
                        dataIndx: "消費税",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "合計",
                        dataIndx: "合計",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "預り金",
                        dataIndx: "預り金",
                        dataType: "integer",
                        format: "#,##0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "提げ袋",
                        dataIndx: "提げ袋",
                        dataType: "integer",
                        format: "#,##0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "風呂敷",
                        dataIndx: "風呂敷",
                        dataType: "integer",
                        format: "#,##0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData.pq_level == 2 && ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI08040Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI08040Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI08040Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI08040Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI08040Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI08040Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI08040Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            if (!vue.params) {
                vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            } else {
                vue.viewModel.BushoCd = vue.params.BushoCd;
                vue.viewModel.AreaCd = vue.params.AreaCd;
                vue.viewModel.CustomerCd = vue.params.CustomerCd;
                vue.viewModel.DeliveryDate = moment(vue.params.DeliveryDate, "YYYY年MM月DD日").add(-1, "month").startOf("month").format("YYYY年MM月DD日");
                vue.viewModel.DeliveryKbn = vue.params.DeliveryKbn;
            }

            //for Child mode
            if (!!vue.params && !!vue.params.IsChild) {
                vue.DAI08040Grid1 = vue.$refs.DAI08040Grid1.grid;
            }

            //watcher
            vue.$watch(
                "$refs.DAI08040Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI08040Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];
            }

            //フィルタ変更
            vue.filterChanged();
        },
        onAreaChanged: function(code, entities) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];
            }

            //フィルタ変更
            vue.filterChanged();
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onDeliveryKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onPrintoutChanged: function(code, entity) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08040Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!vue.searchParams.DeliveryDate || !vue.searchParams.IsPrintOut || !vue.searchParams.PrintOrder) return;
            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams);
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI08040Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            if (!!vue.viewModel.AreaCd) {
                rules.push({ dataIndx: "エリアＣＤ", condition: "equal", value: vue.viewModel.AreaCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI08040Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI08040Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI08040Grid1_Print").disabled = !res.length;

            res.forEach(r => {
                    r.ＧＫエリア = r.エリアＣＤ + " " + r.コース名;
                    r.ＧＫ受注Ｎｏ = "受注No: " + r.受注Ｎｏ;

                    //税関連
                    r.合計 = _.cloneDeep(r.金額);
                    r.金額 = r.税区分 == 0 ? r.数量 * r.単価 : r.金額;
                    r.消費税 = r.税区分 == 0 ? r.消費税 : 0;
                });

            res = _(res)
                .groupBy(r => r.ＧＫ受注Ｎｏ)
                .values()
                .map(g => {
                    if (g.length < 3) {
                        g.push(..._.range(0, 3 - g.length).map(v => {
                            var clone = _.cloneDeep(g[0]);
                            clone.商品ＣＤ = "";
                            clone.商品名 = "";
                            clone.数量 = "";
                            clone.単価 = "";
                            clone.金額 = "";
                            clone.合計 = "";
                            clone.消費税 = "";
                            clone.預り金 = "";
                            clone.提げ袋 = "";
                            clone.風呂敷 = "";

                            return clone;
                        }));
                    }
                    g.forEach((r, i) => r.gidx = i);

                    return g;
                })
                .flatten()
                .value();

            return res;
        },
        print: function() {
            var vue = this;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.title {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                }
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border: solid 0px black;
                }
                th, td {
                    font-family: "MS UI Gothic";
                    font-size: 8pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 12px;
                    text-align: center;
                }
                td {
                    height: 12px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 1px black;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
                div.report-title-area{
                    width:400px;
                    height:35px;
                    text-align: center;
                    display:table-cell;
                    vertical-align: middle;
                    background-color: #c0ffff;
                    border: 2px solid #000000;
                    border-radius: 5px;
                }
            `;
            var busyoCd;
            var busyoNm;
            var courseCd;
            var courseNm;
            var headerFunc = (header, idx, length) => {
                if (header.pq_level == 0)
                {
                    busyoCd = header.children[0].children[0].部署ＣＤ;
                    busyoNm = header.children[0].children[0].部署名;
                    courseCd = header.ＧＫエリア.split(" ")[0];
                    courseNm = header.ＧＫエリア.split(" ")[1];
                }
                return `
                    <div class="header">
                        <div class="title" style="float: left; width: 100%">
                            <h3>＊ ＊ ＊ 仕出し配送予定表 ＊ ＊ ＊</h3>
                        </div>
                        <div style="float: left; top: 30px; width: 100%;">
                            <div style="float: left; width: 8%; text-align: center;">部署</div>
                            <div style="float: left; width: 7%; text-align: center;">${busyoCd}</div>
                            <div style="float: left; width: 15%; text-align: left;">${busyoNm}</div>
                            <div style="float: left; width: 70%;">&nbsp</div>

                            <div style="float: left; width: 8%; text-align: center;">配達日付</div>
                            <div style="float: left; width: 15%; text-align: center;">${moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY/MM/DD(dd曜日)")}</div>
                            <div style="float: left; width: 77%;">&nbsp</div>

                            <div style="float: left; width: 5%; text-align: center;">${courseCd}</div>
                            <div style="float: left; width: 20%; text-align: left;">${courseNm}</div>
                            <div style="float: left; width: 43%;">&nbsp</div>
                            <div style="float: left; width: 7%; text-align: right;">作成日</div>
                            <div style="float: left; width: 15%; text-align: center;">${moment().format("YYYY年MM月DD日")}</div>
                            <div style="float: left; width: 5%; text-align: center;">PAGE</div>
                            <div style="float: left; width: 5%; text-align: right;">${idx + 1}/${length}</div>
                        </div>
                    </div>
                `;
            };

            var styleCustomers =`
                div.header div:not(.title) {
                    font-size: 12px;
                }
                table.DAI08040Grid1 {
                    border-collapse:collapse;
                }
                table.DAI08040Grid1 thead tr {
                    border-top: solid 1px black;
                    border-bottom: solid 1px black;
                }
                table.DAI08040Grid1 tbody tr.grand-summary {
                    border-top: solid 1px black;
                }
                table.DAI08040Grid1 tbody tr.group-summary td:nth-child(-n+5) {
                    border-top: solid 0px black;
                }
                table.DAI08040Grid1 tbody tr.group-summary td:nth-child(n+6) {
                    border-top: solid 1px black;
                }
                table.DAI08040Grid1 tbody tr.group-summary[level="1"] + tr:not(.group-summary) {
                    border-top: solid 1px black;
                }
                table.DAI08040Grid1 th {
                    text-align: center;
                }
                table.DAI08040Grid1 th:nth-child(n+8) {
                    text-align: right;
                }
                table.DAI08040Grid1 tr th:nth-child(1) {
                    width: 4%;
                }
                table.DAI08040Grid1 tr th:nth-child(2) {
                    width: 6%;
                }
                table.DAI08040Grid1 tr th:nth-child(3) {
                    width: 20%;
                }
                table.DAI08040Grid1 tr th:nth-child(4) {
                    width: 8%;
                }
                table.DAI08040Grid1 tr th:nth-child(5) {
                    width: 5%;
                }
                table.DAI08040Grid1 tr th:nth-child(6) {
                    width: 5%;
                }
                table.DAI08040Grid1 tr th:nth-child(7) {
                    width: 10%;
                }
                table.DAI08040Grid1 tr th:nth-child(n+8) {
                    width: 4%;
                }
                table.DAI08040Grid1 tr td:nth-child(3) {
                    white-space: pre-wrap;
                }
                table.DAI08040Grid1 tr td:nth-child(5) {
                    text-align: center;
                }
                table.DAI08040Grid1 tr td:nth-child(6) {
                    text-align: center;
                }
            `;
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI08040Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                36,
                                false,
                                [true, true],
                                [true, false],
                            )
                        )
                )
                .prop("outerHTML")
                ;
            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };
            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
