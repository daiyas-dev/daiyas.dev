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
                    :disabled=SimeKbnDisabled
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
                        {code: '1', name: '今回請求額=0も出力する', label: '今回請求額=0も出力する'},
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
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, tooltip: true, }
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
                    :AutoCompleteNoLimit=true
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
                    dataUrl="/Utilities/GetCustomerListForSelect"
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
            id="DAI02030Grid1"
            ref="DAI02030Grid1"
            dataUrl="/DAI02030/Search"
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
#DAI02030Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI02030Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI02030Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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
    name: "DAI02030",
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
                vue.footerButtons.find(v => v.id == "DAI02030Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "締日処理 > 請求書",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SimeKbn: "0",
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
            pgId: "DAI02030",
            DAI02030Grid1: null,
            SimeDateDisabled: true,
            SimeKbnDisabled: false,
            TargetDateMsg: null,
            TargetDateFormat: "YYYY年MM月DD日",
            TargetDateDayViewHeaderFormat: "YYYY年MM月",
            BushoInfo: null,
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
                        { dataIndx: "請求先ＣＤ", dir: "up" },
                        { dataIndx: "請求日付", dir: "up" },
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
                        title: "コード",
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
                        tooltip: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        tooltip: true,
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
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
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
                { visible: "true", value: "検索", id: "DAI02030Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI02030Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI02030Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI02030Grid1_Print", disabled: true, shortcut: "F11",
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
                "$refs.DAI02030Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI02030Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            vue.changeScreen(vue);

            //初期フィルタ
            vue.filterChanged();
        },
        activatedFunc: function(vue) {
            vue.changeScreen(vue);
        },
        changeScreen(vue) {
            console.log("2030 changescreen")
            var pgId = vue.$route.path == "/DAI07/DAI07080" ? "DAI07080" : vue.$route.path == "/DAI07/DAI07090" ? "DAI07090" : "DAI02030";
            var title = pgId == "DAI07080" ? "個人宅 > 週間請求書" : pgId == "DAI07090" ? "個人宅 > 月間請求書" : "締日処理 > 請求書";
            vue.pgId = pgId;
            vue.$root.$emit("setTitle", title);

            switch (vue.pgId) {
                case "DAI07080":
                    vue.viewModel.SimeKbn = "1";
                    vue.SimeKbnDisabled = true;
                    break;
                case "DAI07090":
                    vue.viewModel.SimeKbn = "2";
                    vue.SimeKbnDisabled = true;
                    break;
                case "DAI02030":
                    vue.SimeKbnDisabled = false;
                    break;
            }
        },
        onBushoChanged: function(code, entity, entities) {
            var vue = this;
            console.log("2030 bushoChanged")

            if (!!entity) {
                vue.BushoInfo = entity.info;
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();

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

            if (vue.viewModel.SimeKbn == "2") {
                vue.viewModel.TargetDateMax = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").endOf("month").format("YYYY年MM月DD日");
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function() {
            var vue = this;

            if (vue.viewModel.SimeKbn == "2") {
                vue.viewModel.TargetDateMax = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").endOf("month").format("YYYY年MM月DD日");
            } else {
                vue.viewModel.TargetDateMax = vue.viewModel.TargetDate;
            }

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
            var grid = vue.DAI02030Grid1;

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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI02030Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate || !vue.viewModel.TargetDateMax) return;

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                console.log("same condition", _.isEqual(grid.prevPostData, vue.searchParams));
                return;
            }

            grid.searchData(vue.searchParams, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI02030Grid1;

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

            //今回請求額ゼロ表示
            if (!vue.viewModel.SearchOptions.length) {
                if (vue.viewModel.BushoCd == 501) {
                    rules.push({ dataIndx: "今回請求額", condition: "great", value: "0" });
                } else {
                    rules.push({ dataIndx: "今回請求額", condition: "notequal", value: "0" });
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

            vue.footerButtons.find(v => v.id == "DAI02030Grid1_Print").disabled = !res.length;

            vue.filterChanged();

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
                .filter(v => (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd) ? (v.部署CD == vue.viewModel.BushoCd && v.コースＣＤ == vue.viewModel.CourseCd) : true)
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
            var grid = vue.DAI02030Grid1;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            if (!!vue.viewModel.BushoCd && !vue.BushoInfo) {
                var entity = vue.$refs.VueSelectBusho.$refs.BushoCd.entities.find(v => v.code == vue.viewModel.BushoCd);

                if (!entity) return
                vue.BushoInfo = entity.info;
            }

            var params = {
                IsSeikyuOutput: true,
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                BushoInfo: vue.BushoInfo,
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: moment(data.請求日付).format("YYYY年MM月DD日"),
                DateStart: moment(data.請求日範囲開始).format("YYYY年MM月DD日"),
                DateEnd: moment(data.請求日範囲終了).format("YYYY年MM月DD日"),
                SimeKbn: vue.viewModel.SimeKbn,
                SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                Data: data,
            };

            PageDialog.show({
                pgId: "DAI02021",
                params: params,
                isModal: true,
                isChild: true,
                width: 900,
                height: 725,
            });
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI02030Grid1;

            if (!!vue.viewModel.BushoCd && !vue.BushoInfo) {
                var entity = vue.$refs.VueSelectBusho.$refs.BushoCd.entities.find(v => v.code == vue.viewModel.BushoCd);

                if (!entity) return
                vue.BushoInfo = entity.info;
            }

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div{
                    margin-bottom: 3px;
                }
                div.header{
                    font-family: "MS UI Gothic";
                    font-size: 10pt;
                    font-weight: normal;
                    justify-content: left;
                    width: 100%;
                }
                .header-title{
                	font-size: 18pt;
                	font-weight: bold;
                	letter-spacing: 16px;
                }
                .header-subtitle{
                	font-size: 14pt;
                	padding-bottom: 10px;
                }
                .header-seikyu-no{
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    margin-top: 3px;
                    padding-top: 3px;

                }
                span {
                    padding-left: 8px;
                }
                div.header-tokuisaki {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    padding-top: 12px;
                    padding-bottom: 12px;
                    margin-top: 3px;
                    margin-bottom: 3px;
                    font-size: 11pt;
                    font-weight: bold;
                }
                #a-box {
                    float: left;
                    width: 58%
                }
                #b-box {
                    float: left;
                    width: 20%;
                }
                #c-box {
                    float: left;
                    width: 22%;
                }
                #d-box {
                    float: left;
                    width: 50%;
                }
                #e-box {
                    width: 6%;
                }
                #f-box {
                    float: right;
                    width: 42%;
                }
                #g-box {
                    clear: both;
                    float: left;
                    width: 58%;
                    padding-top: 45px;
                    letter-spacing: 0.1em;
                }
                #h-box {
                    float: right;
                    width: 42%;
                }
                #i-box {
                    float: left;
                    width: 35%;
                }
                #j-box {
                    float: right;
                    width: 55%;
                }
                #k-box {
                    float: left;
                    width: 90%;
                    padding-bottom: 3px;
                }
                #l-box {
                    float: right;
                    width: 10%;
                    padding-bottom: 8px;
                    text-align: right;
                }
                #f-box > div{
                    padding-left: 14px;
                }
                #i-box {
                    padding-left: 14px;
                }
                table.header-table tbody th {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 12pt;
                    font-weight: normal;
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
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 21px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    width: 12%;
                }
                table.header-table tbody tr th {
                    text-align: right;
                    padding-right: 10px;
                }
                table.header-table thead th {
                    height: 18px;
                }
                table.header-table tbody th {
                    height: 22px;
                }
                table.header-table tr:first-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.header-table tr:first-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.header-table thead:first-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 3px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table thead:first-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 3px;
                    border-right-width: 2px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 3px;
                }
                table.header-table tr:last-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 2px;
                    border-bottom-width: 3px;
                }
            `;

            axios.post("/DAI02030/GetMeisaiList", { SeikyuNoArray: grid.pdata.map(v => v.請求番号 * 1)})
            .then(res => {
                var group = _.groupBy(res.data, v => v.請求先ＣＤ);

                var meisaiGen;
                if (vue.viewModel.BushoCd == 501 && vue.viewModel.SimeKbn == 1) {
                    meisaiGen = (r, pdata) => {
                        // var days = _.range(0, moment(r.請求日範囲終了).diff(moment(r.請求日範囲開始), "days") + 1)
                        //     .map(v => moment(r.請求日範囲開始).add(v, "days").format("D日(dd)"));
                        var days = _.range(-6, 1).map(v => moment(r.請求日範囲終了).add(v, "days").format("D日<br>(dd)"));

                        var target = _(pdata)
                            .groupBy(v => v.商品ＣＤ)
                            .values()
                            .map(g => {
                                return _.reduce(
                                    g,
                                    (a, v, i) => {
                                        if (i == 0) {
                                            a.商品 = v.商品名
                                        }

                                        days.forEach(date => {
                                            var d = moment(v.伝票日付).format("D日<br>(dd)");
                                            a[date] = (a[date] || 0) + (date == d ? v.数量 * 1 : 0);
                                        })

                                        a.食数 = (a.食数 || 0) + (v.数量 || 0) * 1;
                                        a.買上額 = (a.買上額 || 0) + (v.金額 || 0) * 1;
                                        a.入金額 = (a.入金額 || 0) + (v.入金金額 || 0) * 1;

                                        return a;
                                    },
                                    {}
                                );
                            })
                            .value()
                            ;

                        //空行補完
                        target.push(..._.range(0, 8 - target.length)
                            .map(v => {
                                var empty = {};
                                empty.商品 = null;

                                days.forEach(date => {
                                    empty[date] = null;
                                })

                                empty.食数 = null;
                                empty.買上額 = null;
                                empty.入金額 = null;

                                return empty;
                            })
                        );

                        //合計行
                        target.push(_.reduce(
                            pdata,
                            (a, v, i) => {
                                if (i == 0) {
                                    a.商品 = "合計";
                                }

                                days.forEach(date => {
                                    var d = moment(v.伝票日付).format("D日<br>(dd)");
                                    a[date] = (a[date] || 0) + (date == d ? v.数量 * 1 : 0);
                                })

                                a.食数 = (a.食数 || 0) + (v.数量 || 0) * 1;
                                a.買上額 = (a.買上額 || 0) + (v.金額 || 0) * 1;
                                a.入金額 = (a.入金額 || 0) + (v.入金金額 || 0) * 1;

                                return a;
                            },
                            { class: "grandsummary" }
                        ));

                        // 0 to null, value add comma
                        target.forEach(t => {
                            _.forIn(t, (v, k, o) => { o[k] = !!v ? (pq.formatNumber(v, "#,##0") || v) : null; })
                        });

                        return [target];
                    };
                } else {
                    meisaiGen = (r, pdata) => {
                        var target = [];

                        if (_.every(pdata, v => v.得意先ＣＤ == r.請求先ＣＤ || v.得意先ＣＤ == undefined)) {
                            var datas = _.cloneDeep(pdata);

                            var summary = _.reduce(
                                datas,
                                (a, v, k) => {
                                    a.商品名 = "【 合 計 】";
                                    a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                    a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                    a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                    return a;
                                },
                                {}
                            );
                            summary.class = "grandsummary";
                            datas.push(summary);
                            datas.forEach((v, i) => {
                                v.日付 = i == 0 || pdata[i - 1].日付 != v.日付 ? v.日付 : "";
                                v.数量 = pq.formatNumber(v.数量, "#,##0");
                                v.単価 = pq.formatNumber(v.単価, "#,##0");
                                v.金額 = pq.formatNumber(v.金額, "#,##0");
                                v.入金金額 = pq.formatNumber(v.入金金額, "#,##0");
                            });
                            target.push(datas);

                        } else {
                            var tg = _.groupBy(pdata, v => v.得意先ＣＤ);
                            var tk = _.sortBy(_.keys(tg), k => k != r.請求先ＣＤ ? 0 : 1);
                            var tsums = tk.map(k => {
                                var summary = _.reduce(
                                    tg[k],
                                    (a, v) => {
                                        a.商品名 = v.得意先名;
                                        a.区分 = "全";
                                        a.買上小計 = (a.買上小計 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                        a.入金小計 = (a.入金小計 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                        return a;
                                    },
                                    {}
                                );
                                summary.class = "tsums";
                                return summary;
                            });
                            var tgsum = _.reduce(
                                tsums,
                                (a, v) => {
                                    a.商品名 = "【 合 計 】";
                                    a.買上小計 = (a.買上小計 || 0) + v.買上小計;
                                    a.入金小計 = (a.入金小計 || 0) + v.入金小計;
                                    return a;
                                },
                                {}
                            );
                            tgsum.class = "tsums-grandsummary";
                            var sums = tsums.concat(tgsum);
                            sums.forEach((v, i) => {
                                v.買上小計 = pq.formatNumber(v.買上小計, "#,##0");
                                v.入金小計 = pq.formatNumber(v.入金小計, "#,##0");
                            });
                            target = [sums];

                            var tgmeisai = tk.map(k => _.cloneDeep(tg[k])).map((m, i) => {
                                var title = { "商品名": m[0].得意先名 };
                                var summary = _.reduce(
                                    m,
                                    (a, v, k) => {
                                        a.商品名 = "【 小 計 】";
                                        a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                        a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                        a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                        return a;
                                    },
                                    {}
                                );
                                summary.class = "gsummary";

                                m.unshift(title);
                                m.push(summary);

                                if (i == tk.length - 1) {
                                    console.log("gsum");
                                    var gsum = _.reduce(
                                        pdata,
                                        (a, v) => {
                                            a.商品名 = "【 合 計 】";
                                            a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                            a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                            a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                            return a;
                                        },
                                        {}
                                    );
                                    gsum.class = "grandsummary";
                                    m.push(gsum);
                                }
                                m.forEach((v, j) => {
                                    v.日付 = j == 0 || m[j - 1].日付 != v.日付 ? v.日付 : "";
                                    v.数量 = pq.formatNumber(v.数量, "#,##0");
                                    v.単価 = pq.formatNumber(v.単価, "#,##0");
                                    v.金額 = pq.formatNumber(v.金額, "#,##0");
                                    v.入金金額 = pq.formatNumber(v.入金金額, "#,##0");
                                });

                                return m;
                            });

                            target.push(...tgmeisai);
                        }

                        return target;
                    };
                }

                var contents = grid.pdata.map(r => {
                    var pdata = group[r.請求先ＣＤ] || [{}];
                    var target = meisaiGen(r, pdata);

                    //501用レイアウト
                    var styleSeikyuMeisai501 =`
                        body > div > div:nth-of-type(even) > div[style='break-before: page;'] {
                            break-before: auto !important;
                        }
                        div#g-box {
                            padding-top: 0px !important;
                        }
                        div#h-box {
                            display: none;
                        }
                        .header-table th {
                            border-style: solid;
                            border-left-width: 0px;
                            border-top-width: 1px;
                            border-right-width: 1px;
                            border-bottom-width: 0px;
                        }
                        table.DAI02030Grid1 tr th,
                        table.DAI02030Grid1 tr td {
                            height: 18px !important;
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 0px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02030Grid1 tr th {
                            border-top-width: 1px;
                        }
                        table.DAI02030Grid1 thead tr {
                            height: 25px;
                        }
                        table.DAI02030Grid1 tbody tr {
                            height: 20px;
                        }
                        table.DAI02030Grid1 tr > *:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02030Grid1 thead tr th:nth-child(1) {
                            width: 16%;
                        }
                        table.DAI02030Grid1 thead tr th:nth-child(n+2):nth-child(-n+9) {
                            width: 8%;
                        }
                        table.DAI02030Grid1 thead tr th:nth-child(10),
                        table.DAI02030Grid1 thead tr th:nth-child(11) {
                            width: 10%;
                        }
                        table.DAI02030Grid1 tbody tr td:nth-child(1) {
                            text-align: left;
                        }
                        table.DAI02030Grid1 tbody tr.grandsummary td:nth-child(1) {
                            text-align: center;
                        }
                        table.DAI02030Grid1 tbody tr td:nth-child(n+2){
                            text-align: right;
                        }
                        body > div > div:nth-child(odd) > div > div.header {
                            padding-top: 30px;
                            border-style: dashed;
                            border-left-width: 0px;
                            border-top-width: 1px;
                            border-right-width: 0px;
                            border-bottom-width: 0px;
                        }
                        div.header-tokuisaki {
                            padding-top: 3px !important;
                            padding-bottom: 3px !important;
                            margin-top: 3px !important;
                            margin-bottom: 3px !important;
                        }
                        div.header-seikyu-no,
                        div.header-seikyu-date {
                            text-align: center;
                        }
                        div .page_div {
                            margin-bottom: 30px;
                        }
                        div .header-info {
                            color: transparent;
                        }
                        div[style="break-before: page;"],
                        div[style="break-before: auto;"],
                        div[style="page-break-before: always;"] {
                            margin-top: 25px !important;
                            margin-bottom: 25px !important;
                            margin-right: 30px !important;
                            margin-left: 30px !important;
                        }
                    `;
                    var styleSeikyuMeisaiElse =`
                        .header-table th {
                            border-style: solid;
                            border-left-width: 0px;
                            border-top-width: 1px;
                            border-right-width: 1px;
                            border-bottom-width: 0px;
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
                        table.DAI02030Grid1 tr:nth-child(1) th {
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 1px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02030Grid1 tr th:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02030Grid1 tr td {
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 0px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02030Grid1 tr td:nth-child(1),
                        table.DAI02030Grid1 tr td:nth-child(2) {
                            text-align: center;
                        }
                        table.DAI02030Grid1 tr td:nth-child(4),
                        table.DAI02030Grid1 tr td:nth-child(5),
                        table.DAI02030Grid1 tr td:nth-child(6),
                        table.DAI02030Grid1 tr td:nth-child(7) {
                            text-align: right;
                        }
                        table.DAI02030Grid1 tr.gsummary td:nth-child(3),
                        table.DAI02030Grid1 tr.grandsummary td:nth-child(3) {
                            text-align: center;
                        }
                        table.DAI02030Grid1 tr td:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02030Grid1 thead tr th{
                            height: 18px;
                        }
                        table.DAI02030Grid1 tbody tr {
                            height: 27.5px;
                        }
                        th:first-child:nth-last-child(8),
                        th:first-child:nth-last-child(8) ~ th:nth-child(2) {
                            width: 7.0%;
                        }
                        th:first-child:nth-last-child(8) ~ th:nth-child(3),
                        th:first-child:nth-last-child(8) ~ th:nth-child(8) {
                            width: 25.0%;
                        }
                        th:first-child:nth-last-child(8) ~ th:nth-child(4),
                        th:first-child:nth-last-child(8) ~ th:nth-child(5) {
                            width: 8.0%;
                        }
                        th:first-child:nth-last-child(5) {
                            width: 40.0%;
                        }
                        th:first-child:nth-last-child(5) ~ th:nth-child(2) {
                            width: 8.0%;
                        }
                        th:first-child:nth-last-child(5) ~ th:nth-child(5) {
                            width: 22.0%;
                        }
                        tr.tsums td:nth-child(1){
                            text-align: left !important;
                        }
                        tr.tsums td:nth-child(3),
                        tr.tsums td:nth-child(4){
                            text-align: right !important;
                        }
                        tr.tsums-grandsummary td:nth-child(3){
                            text-align: right;
                        }
                        tr.tsums td:nth-child(5),
                        tr.tsums-grandsummary td:nth-child(5){
                            text-align: start !important;
                        }
                        tr.tsums-grandsummary td:nth-child(2){
                            border-left-width: 0;
                        }
                        div[style="break-before: page;"],
                        div[style="break-before: auto;"],
                        div[style="page-break-before: always;"] {
                            margin-top: 20px !important;
                            margin-bottom: 20px !important;
                            margin-right: 30px !important;
                            margin-left: 30px !important;
                        }
                    `;

                    var styleSeikyuMeisai = vue.viewModel.BushoCd == 501 && vue.viewModel.SimeKbn == 1 ? styleSeikyuMeisai501 : styleSeikyuMeisaiElse;


                    var maxPage = _.sum(target.map(t => _.chunk(t, 25).length));
                    var htmls = target.map((json, tIdx) => {

                        var headerFunc = (header, idx, length, chunk, chunks) => {
                            return `
                                <div class="header">
                                    <div>
                                        <div id="k-box">
                                            <div style="float: left">
                                                ｺｰﾄﾞNo.${r.請求先ＣＤ}
                                                <span/>-${r.コースＣＤ != 0 ? r.コースＣＤ : ""}
                                            </div>
                                            <div class="header-info">
                                                <span/>(
                                                <span/>${r.締日１}
                                                <span>- ${r.支払サイト}</span>
                                                <span>- ${r.支払日}</span>
                                                )
                                            </div>
                                        </div>
                                        <div id="l-box">
                                            ${tIdx + idx + 1}
                                            /
                                            <span/>${maxPage}
                                        </div>
                                    </div>
                                    <div>
                                        <div id="a-box">
                                            </br></br>
                                            <div style="margin-bottom: 8px;">
                                                <span/>〒
                                                <span/>${r.郵便番号}
                                            </div>
                                            <div>
                                                ${r.住所１}
                                            </div>
                                            <br>
                                        </div>
                                        <div id="b-box">
                                            <div class="header-title">
                                                請求書
                                            </div>
                                            <div class="header-subtitle">
                                                (軽減税率対象)
                                            </div>
                                            <div style="margin-bottom: 8px;">
                                                株式会社<span/>ダイヤス食品
                                                <br>${vue.viewModel.BushoCd == 501 ? "デイリーボーノ事業所" : ""}
                                            </div>
                                        </div>
                                        <div id="c-box">
                                            <div class="header-seikyu-date">
                                                <span style="white-space: pre;">${vue.viewModel.BushoCd == 501 && vue.viewModel.SimeKbn == 1 ?
                                                    moment(r.請求日付).format("YYYY/MM/DD") : moment(r.請求日付).format("  YY  年  MM  月  DD  日")}</span>
                                            </div>
                                            <div class="header-seikyu-no">
                                                <span/>請求番号
                                                <span/>${r.請求番号}
                                            </div>
                                        </div>
                                    <div>
                                    <div style="clear: both;">
                                        <div id="d-box">
                                            <div class="header-tokuisaki">
                                                ${r.得意先名}
                                                <span>様
                                            </div>
                                            <div>
                                                Tel
                                                <span/><span/>${r.電話番号１ || ""}
                                                <span/><span/>Fax
                                                <span/><span/>${r.ＦＡＸ１ || ""}
                                            </div>
                                            </br>
                                        </div>
                                        <div id="e-box">
                                        </div>
                                        <div id="f-box">
                                            <div>
                                                <span/>〒
                                                <span/>${vue.BushoInfo.郵便番号}
                                            </div>
                                            <div>
                                                ${vue.BushoInfo.住所}
                                            </div>
                                            <div>
                                                Tel
                                                <span/><span/>${vue.BushoInfo.電話番号}
                                            </div>
                                            <div>
                                                Fax
                                                <span/><span/>${vue.BushoInfo.FAX || ""}
                                            </div>
                                        </div>
                                        <div id="g-box">
                                            <div style="margin-bottom: 3px;">
                                                毎度ありがとうございます。
                                            </div>
                                            <div>
                                                下記の通りご請求申し上げます。
                                            </div>
                                        </div>
                                        <div id="h-box">
                                            <div style="margin-bottom: 8px;">
                                                取引金融機関
                                            </div>
                                            <div id="i-box">
                                                <div>
                                                    ${vue.BushoInfo.金融機関1名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座種別1名称}
                                                    <span/><span/>${vue.BushoInfo.口座番号1}
                                                </div>
                                                <div>
                                                    ${!!vue.BushoInfo.金融機関2名称 ? vue.BushoInfo.金融機関2名称 : ""}
                                                </div>
                                                <div>
                                                    ${!!vue.BushoInfo.口座種別2名称 ? vue.BushoInfo.口座種別2名称 : ""}
                                                    <span/><span/>${!!vue.BushoInfo.口座番号2 ? vue.BushoInfo.口座番号2 : ""}
                                                </div>
                                            </div>
                                            <div id="j-box">
                                                <div>
                                                    <span/>${vue.BushoInfo.金融機関支店1名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座名義人1}
                                                </div>
                                                <div>
                                                    <span/>${!!vue.BushoInfo.金融機関支店2名称 ? vue.BushoInfo.金融機関支店2名称 : ""}
                                                </div>
                                                <div>
                                                    ${!!vue.BushoInfo.口座名義人2 ? vue.BushoInfo.口座名義人2 : ""}
                                            </div>
                                        </div>
                                    </div>
                                <table class="header-table" style="border-width: 0px; margin-bottom: 14px;">
                                    <thead>
                                        <tr>
                                            <th>前回請求額</th>
                                            <th>御入金額</th>
                                            <th>繰越金額</th>
                                            <th>御買上金額</th>
                                            <th>消費税</th>
                                            <th>今回請求額</th>
                                        </tr>
                                        <tr>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.前回請求残高, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回入金額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.差引繰越額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回売上額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.消費税額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回請求額, "#,##0") : ""}</th>
                                    </tbody>
                                </table>
                                </div>
                            `;
                        };

                        var html = grid.generateHtmlFromJson(
                            json,
                            styleSeikyuMeisai,
                            headerFunc,
                            25,
                            true,
                            vue.viewModel.BushoCd == 501, //false,
                            vue.viewModel.BushoCd == 501 && vue.viewModel.SimeKbn == 1 ? null :
                            tIdx == 0 && target.length > 1
                                ? [
                                    "商品名",
                                    "区分",
                                    "買上小計",
                                    "入金小計",
                                    "備考",
                                ]
                                : [
                                    "日付",
                                    "食事区分名",
                                    "商品名",
                                    "数量",
                                    "単価",
                                    "金額",
                                    "入金金額",
                                    "備考",
                                ],
                            vue.viewModel.BushoCd == 501 && vue.viewModel.SimeKbn == 1 ? null :
                            tIdx == 0 && target.length > 1
                                ? [
                                    "商品名称",
                                    "区分",
                                    "買上小計",
                                    "入金小計",
                                    "備考",
                                ]
                                : [
                                    "月日",
                                    "区分",
                                    "商品名称",
                                    "食数",
                                    "単価",
                                    "買上額",
                                    "入金額",
                                    "備考",
                                ],
                        );

                        return html;
                    })
                    .map(v => $(v.get(0)).prop("outerHTML"))
                    .join("")
                    ;
                    console.log("htmls", htmls);
                    return htmls;
                });

                var printable = $("<html>")
                    .append($("<head>").append($("<style>").text(globalStyles)))
                    .append(
                        $("<body>")
                            .append(contents)
                    )
                    .prop("outerHTML")
                    ;

                var printOptions = {
                    type: "raw-html",
                    style: "@media print { @page { size: A4; } }",
                    printable: printable,
                };

                printJS(printOptions);
                //印刷用HTMLの確認はデバッグコンソールで以下を実行
                //$("#printJS").contents().find("html").html()
            })
            .catch(err => {
                console.log(err);
                $.dialogErr({
                    title: "印刷失敗",
                    contents: "請求明細の検索に失敗しました" + "<br/>" + err.message,
                });
            });
        },
    }
}
</script>
