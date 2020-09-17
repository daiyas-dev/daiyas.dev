<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-3">
                <label>締区分</label>
                <VueOptions
                    id="SimeKbn"
                    ref="VueOptions_SimeKbn"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SimeKbn"
                    buddy="SimeKbnNm"
                    :list="[
                        {code: '0', name: '日締', label: '日締'},
                        {code: '1', name: '週締', label: '週締'},
                        {code: '2', name: '月締', label: '月締'},
                    ]"
                    :onChangedFunc=onSimeKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_TargetDate"
                    :format=TargetDateFormat
                    :dayViewHeaderFormat=TargetDateDayViewHeaderFormat
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
            <div class="col-md-4">
                <label style="width: unset;">{{TargetDateMsg}}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="SimeDate"
                    ref="VueMultiSelect_SimeDate"
                    :vmodel=viewModel
                    bind="SimeDateArray"
                    uri="/DAI02030/GetSimeDateList"
                    :params='{ BushoCd: searchParams.BushoCd, TargetDate: searchParams.TargetDate }'
                    :hasNull=true
                    :onChangedFunc=onSimeDateChanged
                    :ParamsChangedCheckFunc=SimeDateParamsChangedCheckFunc
                    :disabled=SimeDateDisabled
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日範囲</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDateMax"
                    ref="DatePicker_TargetDateMax"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDateMax"
                    :editable=true
                    :onChangedFunc=onTargetDateMaxChanged
                />
                <label class="text-left pl-1">迄</label>
            </div>
            <div class="col-md-3">
                <label>出力順</label>
                <VueOptions
                    id="PrintOrder"
                    ref="VueOptions_PrintOrder"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintOrder"
                    :list="[
                        {code: '0', name: '得意先順', label: '0:得意先順'},
                        {code: '1', name: 'コース順', label: '1:コース順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-11">
                <VueCheckList
                    id="SearchOptions"
                    :vmodel=viewModel
                    bind="SearchOptions"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '1', name: '残高無しも出力', label: '残高無しも出力'},
                        {code: '2', name: '残高ゼロは出力しない', label: '残高ゼロは出力しない'},
                        {code: '3', name: 'ルートが無い得意先を出力', label: 'ルートが無い得意先を出力'},
                    ]"
                    :onChangedFunc=onSearchOptionsChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ UserBushoCd: getLoginInfo().bushoCd }'
                    :isPreload=true
                    :noResearch=true
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "部署ＣＤ", dataIndx: "部署ＣＤ", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 150, maxWidth: 150, minWidth: 150, colIndx: 1 },
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteNoLimit=false
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-11">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerAbbListForSelect"
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI02020Grid1"
            ref="DAI02020Grid1"
            dataUrl="/DAI02020/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI02020Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI02020Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI02020Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI02020",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                SimeKbn: vue.viewModel.SimeKbn,
                TargetDate: vue.calcTargetDate(),
                TargetDateMax: moment(vue.viewModel.TargetDateMax, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: vue.viewModel.CourseCd,
                CustomerCd: vue.viewModel.CustomerCd,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI02020Grid1_Search").disabled = disabled;
            },
        },
        "DAI02020Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI02020Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "締日処理 > 請求一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SimeKbn: 0,
                SimeKbnNm: null,
                TargetDate: null,
                TargetDateMax: null,
                SimeDateArray: [],
                CourseCd: null,
                CourseNm: null,
                CustomerCd: null,
                CustomerNm: null,
                SearchOptions: [],
            },
            DAI02020Grid1: null,
            SimeDateDisabled: true,
            TargetDateMsg: null,
            TargetDateFormat: "YYYY年MM月DD日",
            TargetDateDayViewHeaderFormat: "YYYY年MM月",
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 25,
                rowHt: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45 },
                autoRow: false,
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
                    number: false,
                    type: "local",
                    sorter: [
                        { dataIndx: "請求日付", dir: "up" },
                        { dataIndx: "請求先ＣＤ", dir: "up" },
                    ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [true, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "コース",
                        function(rowData) {
                            return rowData.コースＣＤ + ":" + rowData.コース名 + ":" + rowData.担当者ＣＤ + ":" + rowData.担当者名;
                        }
                    ],
                    [
                        "前回請求残高",
                        function(rowData) {
                            return (rowData["３回前残高"] || 0) * 1 + (rowData["前々回残高"] || 0) * 1 + (rowData["前回残高"] || 0) * 1;
                        }
                    ],
                    [
                        "残高有無判定",
                        function(rowData) {
                            return (rowData["３回前残高"] || 0) * 1 != 0
                                || (rowData["前々回残高"] || 0) * 1 != 0
                                || (rowData["前回残高"] || 0) * 1 != 0
                                || (rowData["今回入金額"] || 0) * 1 != 0
                                || (rowData["今回売上額"] || 0) * 1 != 0
                                ;
                        }
                    ],
                    [
                        "締日",
                        function(rowData) {
                            return "/"
                                 + (rowData["締日１"] || "")
                                 + "/"
                                 + (rowData["締日２"] || "")
                                 + "/"
                                 ;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コース",
                        dataIndx: "コース",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "<span>コード</span>",
                        dataIndx: "請求先ＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "請求日付",
                        dataIndx: "請求日付",
                        dataType: "date",
                        hidden: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "得意先名略称",
                        dataIndx: "得意先名略称",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        hidden: false,
                        hiddenOnExport: true,
                    },
                    {
                        title: "<span>コード</span>",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        render: ui => {
                            if (!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "ＳＥＱ",
                        dataIndx: "ＳＥＱ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "支払方法",
                        dataIndx: "支払方法",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "集金日",
                        dataIndx: "集金日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                        render: ui => {
                            if (!!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: vue.viewModel.SimeKbn == "2" ? (ui.rowData.pq_level == 0 ? "請求日計" : "コース計") : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "前回請求残高",
                        dataIndx: "前回請求残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回入金額",
                        dataIndx: "今回入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回売上額",
                        dataIndx: "今回売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回請求額",
                        dataIndx: "今回請求額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "残高有無判定",
                        dataIndx: "残高有無判定",
                        dataType: "bool",
                        hidden: true,
                    },
                    {
                        title: "締日",
                        dataIndx: "締日",
                        dataType: "string",
                        hidden: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI02020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI02020Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "売上入力", id: "DAI02020Grid1_Uriage", disabled: true, shortcut: "F7",
                    onClick: function () {
                        vue.showUriage();
                    }
                },
                { visible: "true", value: "入金入力", id: "DAI02020Grid1_Nyukin", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showNyukin();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI02020Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI02020Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI02020Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI02020Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI02020Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.TargetDateMax = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI02020Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI02020Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI02020Grid1_Uriage").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI02020Grid1_Nyukin").disabled = cnt == 0 || cnt > 1;
                }
            );

            //初期フィルタ
            vue.filterChanged();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimeKbnChanged: function() {
            var vue = this;

            //請求日付DatePicker option変更
            vue.TargetDateFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年MM月" : "YYYY年MM月DD日";
            vue.TargetDateDayViewHeaderFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年" : "YYYY年MM月";

            //締日MultiSelect状態変更
            vue.SimeDateDisabled = vue.viewModel.SimeKbn != "2";

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function() {
            var vue = this;

            vue.viewModel.TargetDateMax = vue.viewModel.TargetDate;

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateMaxChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimeDateChanged: function(entity, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        SimeDateParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BushoCd && !!newVal.TargetDate;
            return ret;
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            //ソート順変更
            var sorter = [
                { dataIndx: "請求日付", dir: "up" },
            ];

            if (vue.viewModel.PrintOrder == "1") {
                sorter.push({ dataIndx: "コースＣＤ", dir: "up" });
                sorter.push({ dataIndx: "ＳＥＱ", dir: "up" });
            }
            sorter.push({ dataIndx: "請求先ＣＤ", dir: "up" });

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //ソート変更
            if (!!grid) {
                grid.sort( { sorter: sorter });
            }
        },
        onSearchOptionsChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署ＣＤ) {
                    vue.viewModel.BushoCd = entity.部署ＣＤ;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署CD) {
                    vue.viewModel.BushoCd = entity.部署CD;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate || !vue.viewModel.TargetDateMax) return;

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!force && !!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                return;
            }

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            if (!grid) return;

            var rules = [];

            //請求日付
            if (vue.viewModel.SimeKbn == "2") {
                if (!!vue.viewModel.SimeDateArray.length) {
                    var crules = vue.viewModel.SimeDateArray.map(d => {
                        var date;
                        if (d.code == 99) {
                            date = moment(vue.searchParams.TargetDate).endOf("month").format("YYYY-MM-DD 00:00:00.000");
                        } else {
                            date = moment(vue.searchParams.TargetDate).startOf("month").add(d.code - 1, "day").format("YYYY-MM-DD 00:00:00.000");
                        }
                        return { condition: "equal", mode: "OR", value: date };
                    });
                    rules.push({ dataIndx: "請求日付", crules: crules });
                } else {
                    var date = moment(vue.searchParams.TargetDate).endOf("month").format("YYYY-MM-DD 00:00:00.000");
                    rules.push({ dataIndx: "請求日付", condition: "equal", value: date });
                }
            }

            //残高無し表示
            if (!vue.viewModel.SearchOptions.includes("1")) {
                rules.push({ dataIndx: "残高有無判定", condition: "equal", value: true });
            }

            //残高ゼロ非表示
            if (vue.viewModel.SearchOptions.includes("2")) {
                rules.push({ dataIndx: "今回請求額", condition: "notequal", value: "0" });
            }

            //ルート無し表示
            if (vue.viewModel.SearchOptions.includes("3")) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: "0" });
            } else {
                if (vue.viewModel.PrintOrder == "1") {
                    rules.push({ dataIndx: "コースＣＤ", condition: "notequal", value: "0" });
                }
            }

            //コース
            if (!!vue.viewModel.CourseCd && vue.$refs.PopupSelect_Course.isValid) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }

            //請求先
            if (!!vue.viewModel.CustomerCd && vue.$refs.PopupSelect_Customer.isValid) {
                rules.push({ dataIndx: "請求先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            grid.filter({ oper: "replace", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI02020Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI02020Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI02020Grid1_Print").disabled = !res.length;

            return res;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = "[" + (v.部署名 || "部署無し") + "]" + v.Cd + " : " + v.CdNm + "【" + v.担当者名 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
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
                .filter(v => (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd) ? (v.部署CD == (vue.viewModel.BushoCd || vue.getLoginInfo.bushoCd) && v.コースＣＤ == vue.viewModel.CourseCd) : true)
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
        calcTargetDate: function() {
            var vue = this;

            var ret;
            vue.TargetDateMsg = null;

            switch (vue.viewModel.SimeKbn)
            {
                case "0":   //日締
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    break;
                case "1":   //週締
                    //該当週の土曜
                    var mt = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6);
                    ret = mt.format("YYYYMMDD");
                    if (ret != moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")) {
                        vue.TargetDateMsg = mt.format("YYYY年MM月DD日") + "扱いで処理されます";
                    }
                    break;
                case "2":
                    //該当月
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月").startOf("month").format("YYYYMMDD");
                    break;
            }

            return ret;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: moment(data.請求日付).format("YYYY年MM月DD日"),
                DateStart: moment(data.請求日範囲開始).format("YYYY年MM月DD日"),
                DateEnd: moment(data.請求日範囲終了).format("YYYY年MM月DD日"),
                SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CourseKbn: data.コース区分,
            };

            PageDialog.show({
                pgId: "DAI02021",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
            });
        },
        showUriage: function() {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            var selection = grid.SelectRow().getSelection();
            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                Parent: vue,
                ParentGrid: grid,
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: moment().format("YYYY年MM月DD日"),
                CourseKbn: data.コース区分,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CustomerCd: data.請求先ＣＤ,
                PaymentCd: data.得意先支払方法 == 5 ? 2 : data.得意先売掛現金区分,
                onSaveFunc: () => {
                    var grid = vue.DAI02020Grid1;
                    grid.refreshDataAndView();
                },
            };

            PageDialog.show({
                pgId: "DAI10010",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1000,
                height: 600,
            });
        },
        showNyukin: function() {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            var selection = grid.SelectRow().getSelection();
            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment().format("YYYY年MM月DD日"),
                CustomerCd: data.請求先ＣＤ,
                onSaveFunc: () => {
                    var grid = vue.DAI02020Grid1;
                    grid.refreshDataAndView();
                },
            };

            PageDialog.show({
                pgId: "DAI01130",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
                onBeforeClose: (event, ui) => {
                    if ($(window.event.target).attr("shortcut") == "ESC") return true;

                    var dlg = $(event.target);
                    var editting = dlg.find(".pq-grid")
                        .map((i, v) => $(v).pqGrid("getInstance").grid)
                        .get()
                        .some(g => !_.isEmpty(g.getEditCell()));
                    var isEscOnEditor = !!window.event && window.event.key == "Escape"
                        && (
                            $(window.event.target).hasClass("target-input") ||
                            $(window.event.target).hasClass("pq-cell-editor")
                        );

                    var canClose = !editting && !isEscOnEditor;

                    return canClose;
                }
            });
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI02020Grid1;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                h3 {
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
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (header, idx, length) => {
                var TargetDate = vue.viewModel.SimeKbn == "2"
                    ? moment(header.pq_level == 0 ? header.請求日付 : header.parentId).format("YYYY年MM月DD日")
                    : vue.viewModel.SimeKbn == "1"
                        ? moment(vue.searchParams.TargetDate).format("YYYY年MM月DD日")
                        : vue.viewModel.TargetDate
                    ;
                var GroupInfo = vue.viewModel.PrintOrder == "1"
                    ? (header.pq_level == 0 ? (!!header.children.length ? header.children[0].コース : "") : header.コース).split(":")
                    : []
                    ;
                var CourseCd = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[0] || "");
                var CourseNm = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[1] || "");
                var TantoCd = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[2] || "");
                var TantoNm = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[3] || "");

                return `
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 6%;">部署</th>
                                <th style="width: 6%;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 15%;" colspan="2">${vue.viewModel.BushoNm}</th>
                                <th style="width: 31%; vertical-align: top;" rowspan="3" colspan="6">
                                    <h3 style="font-size: 16pt; text-align: right;">* * * 請求一覧表 * * *</h3>
                                </th>
                                <th style="width: 12%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                            </tr>
                            <tr>
                                <th>請求日付</th>
                                <th colspan="2">${TargetDate}</th>
                            </tr>
                            <tr>
                                <th>コース</th>
                                <th>${CourseCd}</th>
                                <th colspan="2">${CourseNm}</th>
                            </tr>
                            <tr>
                                <th>担当者</th>
                                <th>${TantoCd}</th>
                                <th colspan="2">${TantoNm}</th>
                                <th>締区分</th>
                                <th>${vue.viewModel.SimeKbnNm}</th>
                                <th colspan="3"></th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th>時間</th>
                                <th>${moment().format("HH:mm:ss")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right; padding-right: 10px;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var keys = [];
            if (vue.viewModel.SimeKbn == "2") {
                keys.push("請求日付");
            }
            if (vue.viewModel.PrintOrder == "1") {
                keys.push("コース");
            }
            if (!!keys.length) {
                grid.Group().option({ "dataIndx": keys});
            }

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    .header-table th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr:nth-child(1) th {
                                        border-bottom-width: 1px;
                                    }
                                    .header-table tr:nth-child(2) th {
                                        border-top-width: 0px;
                                    }
                                    .header-table tr th:first-child {
                                        border-left-width: 1px;
                                    }
                                    .header-table tr:nth-child(1) th:nth-child(n+4) {
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr:nth-child(4) th:nth-child(6) {
                                        border-top-width: 0px;
                                    }
                                    .header-table tr th:nth-child(3) {
                                        text-align: left;
                                    }
                                    table.DAI02020Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(1) {
                                        width: 6.0%
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(2) {
                                        width: 20.0%
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(3) {
                                        width: 4.0%
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(4) {
                                        width: 14.0%
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(5) {
                                        width: 6.0%
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(6) {
                                        width: 8.0%
                                    }
                                    table.DAI02020Grid1 tr th:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI02020Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02020Grid1 tr th span {
                                        color: transparent;
                                    }
                                    table.DAI02020Grid1 tr th:nth-child(2),
                                    table.DAI02020Grid1 tr th:nth-child(4) {
                                        border-left-width: 0px;
                                        text-align: left;
                                        padding-left: 28px;
                                    }
                                    table.DAI02020Grid1 tr td:nth-child(2),
                                    table.DAI02020Grid1 tr td:nth-child(4) {
                                        border-left-width: 0px;
                                        padding-left:3px;
                                    }
                                    table.DAI02020Grid1 tr td:nth-child(6) {
                                        text-align: left;
                                        padding-left:5px;
                                    }
                                    table.DAI02020Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02020Grid1 tr td:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI02020Grid1 tr.group-summary td:nth-child(2),
                                    table.DAI02020Grid1 tr.group-summary td:nth-child(3),
                                    table.DAI02020Grid1 tr.group-summary td:nth-child(4),
                                    table.DAI02020Grid1 tr.group-summary td:nth-child(5),
                                    table.DAI02020Grid1 tr.group-summary td:nth-child(6) {
                                        border-left-width: 0px;
                                    }
                                    table.DAI02020Grid1 tr.grand-summary td:nth-child(2),
                                    table.DAI02020Grid1 tr.grand-summary td:nth-child(3),
                                    table.DAI02020Grid1 tr.grand-summary td:nth-child(4),
                                    table.DAI02020Grid1 tr.grand-summary td:nth-child(5),
                                    table.DAI02020Grid1 tr.grand-summary td:nth-child(6) {
                                        border-left-width: 0px;
                                    }
                                `,
                                headerFunc,
                                32,
                                !!keys.length ?  keys.map(v => false) : null,
                                [true, true],
                                !!keys.length ?  keys.map(v => true) : null,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "dataIndx": [] });

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
