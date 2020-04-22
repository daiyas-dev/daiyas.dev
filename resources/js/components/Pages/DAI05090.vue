<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>対象日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
            <div class="col-md-1">
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="Customer"
                    ref="VueOptions_Customer"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="Customer"
                    :list="[
                        {code: '0', name: '全顧客', label: '0:全顧客'},
                        {code: '1', name: '新規顧客', label: '1:新規顧客のみ'},
                    ]"
                    :onChangedFunc=onCustomerChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>新規登録日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="SaveDateStart"
                    ref="DatePicker_SaveDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="SaveDateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="SaveDateEnd"
                    ref="DatePicker_SaveDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="SaveDateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
            <div class="col-md-1">
            </div>
            <VueCheck
                id="VueCheck_ShowSyonin"
                ref="VueCheck_ShowSyonin"
                :vmodel=viewModel
                bind="ShowSyonin"
                checkedCode="1"
                customContainerStyle="border: none;"
                :list="[
                    {code: '0', name: 'しない', label: '承認・仮承認'},
                    {code: '1', name: 'する', label: '承認・仮承認'},
                ]"
                :onChangedFunc=onShowSyoninChanged
            />
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="Busho"
                    ref="VueOptions_Busho"
                    customItemStyle="text-align: center; margin-right: 10px; border: none;"
                    :vmodel=viewModel
                    bind="Busho"
                    :list="[
                        {code: '0', name: '部署なし', label: '部署なし'},
                        {code: '1', name: '全社', label: '全社　　'},
                        {code: '2', name: '部署', label: '部署'},
                    ]"
                    :onChangedFunc=onBushoChanged
                />
                <VueSelectBusho
                    :hasNull=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>営業担当者</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="EigyoTantoCd"
                    ref="PopupSelect_EigyoTantoCd"
                    :vmodel=viewModel
                    bind="EigyoTantoCd"
                    dataUrl="/Utilities/GetTantoList"
                    :params='{ bushoCd: viewModel.BushoCd }'
                    :dataListReset=true
                    title="営業担当者"
                    labelCd="営業担当者CD"
                    labelCdNm="営業担当者名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onEigyoTantoCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>獲得営業者</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="GetEigyoTantoCd"
                    ref="PopupSelect_GetEigyoTantoCd"
                    :vmodel=viewModel
                    bind="GetEigyoTantoCd"
                    dataUrl="/Utilities/GetTantoList"
                    :params='{ bushoCd: viewModel.BushoCd = viewModel.Busho == "2" ? viewModel.BushoCd : 0 }'
                    :dataListReset=true
                    title="獲得営業担当者"
                    labelCd="獲得営業担当者CD"
                    labelCdNm="獲得営業担当者名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onEigyoTantoCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <div class="row">
            <div class="Grid1Container">
                <PqGridWrapper
                    id="DAI05090Grid1"
                    ref="DAI05090Grid1"
                    dataUrl="/DAI05090/Search"
                    :query=this.viewModel
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=this.grid1Options
                    :autoToolTipDisabled=true
                    :onAfterSearchFunc=this.onAfterSearchFunc
                    :resizeFunc=this.resizeGrid
                    classes="ml-0 mr-0 mt-2"
                />
            </div>
            <div class="Grid2Container">
                <PqGridWrapper
                    id="DAI05090Grid2"
                    ref="DAI05090Grid2"
                    :options=this.grid2Options
                    :autoToolTipDisabled=true
                    :resizeFunc=this.resizeGrid
                    :setCustomTitle=this.setSummaryGridTitle
                    classes="ml-0 mr-0 mt-2"
                />
            </div>
        </div>
    </form>
</template>

<style>
#DAI05090Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05090Grid1 .pq-group-icon {
    display: none !important;
}
#DAI05090Grid2 .pq-group-toggle-none {
    display: none !important;
}
#DAI05090Grid2 .pq-group-icon {
    display: none !important;
}
label{
    width: 80px;
}
</style>
<style scoped>
.Grid1Container {
    width: calc(100vw - 425px);
    max-width: unset;
}
.Grid2Container {
    position: absolute;
    right: 18px;
    width: auto;
}
</style>
<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05090",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        ProductCdArray: function() {
            var vue = this;
            return vue.viewModel.ProductArray.map(v => v.code);
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 顧客売上推移表",
            noViewModel: true,
            viewModel: {
                Busho: null,
                BushoCd: null,
                ProductArray: [],
                DateStart: null,
                DateEnd: null,
                ProductCd: null,
                Customer: "0",
                ShowSyonin: "0",
            },
            DAI05090Grid1: null,
            DAI05090Grid2: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: true,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 7,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    showSummary: [false, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: ["a", "b"
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者ＣＤ",
                        dataIndx: "営業担当者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "獲得営業者ＣＤ",
                        dataIndx: "獲得営業者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者",
                        dataIndx: "ＧＫ営業担当者", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                        hidden: true,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_level != 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "獲得営業者",
                        dataIndx: "ＧＫ獲得営業者", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                        hidden: true,
                        fixed: true,
                        render: ui => {
                            switch (ui.rowData.pq_level) {
                                case 0:
                                    return { text: "" };
                                case 1:
                                    return ui;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "顧客ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 70, minWidth: 70, maxWidth: 70, align: "right",
                        fixed: true,
                    },
                    {
                        title: "顧客名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 300, minWidth: 300, maxWidth: 300,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "総合計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        return { text: "合計" };
                                    default:
                                        return { text: "" };
                                }
                            }
                            return { text:ui };
                        },
                    },
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
            },
            grid2Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                numberCell: { show: false },
                strNoRows: "",
                autoRow: true,
                rowHtHead: 35,
                rowHt: 35,
                width: 410,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [],
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 0,
                    dataIndx: ["ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    showSummary: [false, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者ＣＤ",
                        dataIndx: "営業担当者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "獲得営業者ＣＤ",
                        dataIndx: "獲得営業者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者",
                        dataIndx: "ＧＫ営業担当者", dataType: "string",
                        hidden: true,
                        render: ui => {
                            return { text: "" };
                        },
                    },
                    {
                        title: "獲得営業者",
                        dataIndx: "ＧＫ獲得営業者", dataType: "string",
                        hidden: true,
                        render: ui => {
                            return { text: "" };
                        },
                    },
                    {
                        title: "合計" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        title: "平日日数",
                        dataIndx: "得意先平日日数", dataType: "integer", format: "#,###",
                        hidden: true,
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
                        title: "平均" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                        {
                                            return { text: "" };
                                        }
                                        else
                                        {
                                            var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                            var avgValFmt = avgVal.toFixed(1).toString();
                                            return { text: avgValFmt };
                                        }
                                    default:
                                        return { text: "" };
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "合計",
                        dataIndx: "得意先合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        title: "平均",
                        dataIndx: "得意先平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                        {
                                            return { text: "" };
                                        }
                                        else
                                        {
                                            var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                            var avgValFmt = avgVal.toFixed(1).toString();
                                            return { text: avgValFmt };
                                        }
                                    default:
                                        return { text: "" };
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "売上日数",
                        dataIndx: "得意先売上日数", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        title: "売上金額",
                        dataIndx: "得意先売上金額", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05090Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI05090Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05090Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI05090Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI05090Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05090Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日moment(vue.viewModel.TargetDate, "YYYYMM01").endOf('month')
            vue.viewModel.DateStart = moment("20190430").format("YYYY年MM月01日");
            vue.viewModel.DateEnd = moment("20190430").endOf('month').format("YYYY年MM月DD日");
            vue.viewModel.SaveDateStart = moment("20190430").format("YYYY年MM月01日");
            vue.viewModel.SaveDateEnd = moment("20190430").endOf('month').format("YYYY年MM月DD日");

            vue.refreshCols();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //部署コード未選択
            vue.viewModel.BushoCd = 0;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoCdChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.filterChanged();
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //列定義変更 + 条件変更ハンドラ
            //vue.refreshCols(vue.conditionChanged);

            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        onShowSyoninChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onEigyoTantoCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["担当者名", "部署.部署名", "担当者名カナ"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.担当者ＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.担当者ＣＤ + " : " + v.担当者名 + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.担当者ＣＤ;
                    ret.text = v.担当者名;
                    return ret;
                })
                ;

            return list;
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid1 = vue.DAI05090Grid1;
            var grid2 = vue.DAI05090Grid2;

            if (!grid1 || !grid2 || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //配達日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月01日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").endOf('month').format("YYYYMMDD") : null;

            //フィルタするパラメータは除外
            delete params.ProductArray;

            grid1.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid1 = vue.DAI05090Grid1;
            var grid2 = vue.DAI05090Grid2;

            if (!grid1 || !grid2) return;

            var rules = [];

            if (!!vue.viewModel.BushoCd && vue.viewModel.Busho == 2) {
                rules.push({ dataIndx: "部署ＣＤ", condition: "equal", value: vue.viewModel.BushoCd });
            }
            if (!!vue.viewModel.EigyoTantoCd) {
                rules.push({ dataIndx: "営業担当者ＣＤ", condition: "equal", value: vue.viewModel.EigyoTantoCd });
            }
            if (!!vue.viewModel.GetEigyoTantoCd) {
                rules.push({ dataIndx: "獲得営業者ＣＤ", condition: "equal", value: vue.viewModel.GetEigyoTantoCd });
            }

            grid1.filter({ oper: "replace", mode: "AND", rules: rules });
            grid2.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        checkGridChangedFunc: function(grid) {
            var vue = this;
            var grid2 = vue.DAI05090Grid2;

            if (!grid2) return false;

            return grid2.isChanged();
        },
        resizeGrid: function(grid) {
            var vue = this;
            var widget = grid.widget();

            var oldH = widget.outerHeight();
            var containerH = widget.closest(".body-content").outerHeight(true);
            var otherH = _.sum(widget.closest(".row").siblings(".row").map((i, el) => $(el).outerHeight(true)));

            var newH = containerH - otherH - 5;

            if (_.round(newH) != _.round(oldH)) {
                grid.options.height = newH;
                grid.refresh();
            }
        },
        setSummaryGridTitle: function(title) {
            return "　";
        },
        syncScroll: _.debounce(function(val) {
            var vue = this;
            var grid1 = vue.DAI05090Grid1;
            var grid2 = vue.DAI05090Grid2;

            if (grid1.scrollY() != val) grid1.scrollY(val);
            if (grid2.scrollY() != val) grid2.scrollY(val);
        }, 0),
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_CSV").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_Excel").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_Print").disabled = false;

            res.forEach(r => {
                    r.ＧＫ営業担当者 = r.営業担当者ＣＤ + " " + r.営業担当者名;
                    r.ＧＫ獲得営業者 = r.獲得営業者ＣＤ + " " + r.獲得営業者名;
                });

            var grid2 = vue.DAI05090Grid2;
            grid2.options.dataModel.data = res;
            grid2.refreshDataAndView();

            return res;
        },
        refreshCols: function(callback) {
            var vue = this;
            var grid1, grid2;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid1 = vue.DAI05090Grid1;
                    grid2 = vue.DAI05090Grid2;
                    if (!!grid1 && !!grid2 && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid1, grid2);
                    }
                }, 100);
            })
            .then((grid1, grid2) => {
                grid1.showLoading();

                var mt = moment(vue.viewModel.DateStart, "YYYY年MM月");
                var days = _.range(1, mt.endOf("month").format("D") * 1 + 1);
                var max = 31;
                days = days.length == max ? days : days.concat(_.range(0, days.length - max).fill(null));

                var newCols = grid1.options.colModel
                    .filter(v => !!v.fixed)
                    .concat(
                        ...days.map((d, i) => {
                            var date = mt.startOf("month").add("days", i);

                            return {
                                title: !!d ? (date.format("ddd") + "<br>" + d) : "<br>",
                                dataIndx: !!d ? date.format("D") : ("empty" + i),
                                dataType: "integer",
                                format: "#,##0",
                                width: 50, maxWidth: 50, minWidth: 50,
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
                            }
                        })
                    );

                newCols.push(...[
                    {
                        title: "合計" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平日日数",
                        dataIndx: "得意先平日日数", dataType: "integer", format: "#,###",
                        hidden: true,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平均" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                        {
                                            return { text: "" };
                                        }
                                        else
                                        {
                                            var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                            var avgValFmt = avgVal.toFixed(1).toString();
                                            return { text: avgValFmt };
                                        }
                                    default:
                                        return { text: "" };
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "合計",
                        dataIndx: "得意先合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平均",
                        dataIndx: "得意先平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                        {
                                            return { text: "" };
                                        }
                                        else
                                        {
                                            var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                            var avgValFmt = avgVal.toFixed(1).toString();
                                            return { text: avgValFmt };
                                        }
                                    default:
                                        return { text: "" };
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "売上日数",
                        dataIndx: "得意先売上日数", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "売上金額",
                        dataIndx: "得意先売上金額", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                ]);

                //列定義更新
                grid1.options.colModel = newCols;
                grid1.refreshCM();
                grid1.refresh();

                if (!!grid1) grid1.hideLoading();
                if (!!grid2) grid2.hideLoading();

                if (!!callback) callback();
            })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();
            });
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
                    font-size: 10pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 25px;
                    text-align: center;
                }
                td {
                    height: 22px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
            `;

            var headerFunc = (header, idx, length) => {
                var eigyoCd = header.営業担当者.split(" ")[0];
                var eigyoNm = header.営業担当者.split(" ")[1];
                var eigyoCd2 = header.獲得営業者.split(" ")[0];
                var eigyoNm2 = header.獲得営業者.split(" ")[1];
                return `
                    <div class="title">
                        <h3>* * * 顧客売上推移表 * * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 10%;">部署</th>
                                <th style="width: 10%; text-align: right;">${bushoCd}</th>
                                <th style="width: 20%;">${bushoNm}</th>
                                <th style="width: 10%;" class="blank-cell"></th>
                                <th style="width: 10%;" class="blank-cell"></th>
                                <th style="width: 10%;" class="blank-cell"></th>
                                <th style="width: 10%;" class="blank-cell"></th>
                                <th style="width: 10%;" class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>${vue.viewModel.DateStart}</th>
                                <th>～</th>
                                <th>${vue.viewModel.DateEnd}</th>
                                <th class="blank-cell"></th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right;">${idx + 1}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI05090Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI05090Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05090Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI05090Grid1 tr[level="0"].group-summary td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05090Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI05090Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05090Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI05090Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI05090Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI05090Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI05090Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI05090Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI05090Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                36,
                                false,
                                true,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 portrait; } }",
                printable: printable,
            };

            printJS(printOptions);
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
