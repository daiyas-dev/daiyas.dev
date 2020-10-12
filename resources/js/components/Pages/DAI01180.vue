<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-1">
                <label>入金日付</label>
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
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseCd"
                    ref="PopupSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力区分</label>
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="SummaryKind"
                    ref="VueOptions_SummaryKind"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SummaryKind"
                    :list="[
                        {code: '2', name: 'コース計', label: 'コース計'},
                        {code: '1', name: '得意先別', label: '得意先別'},
                    ]"
                    :onChangedFunc=onSummaryKindChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01180Grid1"
            ref="DAI01180Grid1"
            dataUrl="/DAI01180/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01180Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01180Grid1 .pq-group-icon {
    display: none !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01180",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    watch: {
        "DAI01180Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI01180Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > コース別入金一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                SummaryKind: null,
                CourseKbn: null,
                CourseCd: null,
                ColHeader : [],
            },
            DAI01180Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 6,
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
                    dataIndx: ["日付", "コース"],
                    showSummary: [true, true],
                    collapsed: [false, true],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "顧客名",
                        function(rowData){
                            return _.padStart(rowData.得意先ＣＤ, 6, "0") + " " + rowData.得意先名;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "日付",
                        dataIndx: "日付", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コース",
                        dataIndx: "コース", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "integer",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        align: "left",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "CD",
                        dataIndx: "得意先ＣＤ", dataType: "integer",
                        width: 85, minWidth: 85, maxWidth: 85,
                        fixed: true,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary && !!ui.rowData.pq_gtitle && ui.rowData.pq_level == 1) {
                                if (vue.viewModel.SummaryKind == 2) {
                                    return { text: ui.rowData.コース.split(" ")[0] };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 250, minWidth: 250,
                        fixed: true,
                        render: ui => {
                            if (!!ui.Export) ui.cls = [];
                            if (!!ui.Export && !!ui.rowData.pq_grandsummary) {
                                return { text: "* * 合計" };
                            }
                            if (!ui.rowData.pq_grandsummary && !!ui.rowData.pq_gtitle && ui.rowData.pq_level == 1) {
                                if (vue.viewModel.SummaryKind == 2) {
                                    return { text: ui.rowData.コース.split(" ")[1] };
                                }
                            }
                            if (!ui.rowData.pq_grandsummary && !!ui.rowData.pq_gsummary && ui.rowIndx > 0) {
                                //小計行
                                var pid = ui.rowData.parentId;
                                if (!!ui.cls) {
                                    if (ui.rowData.pq_level == 0) {
                                        ui.cls.push("pq-align-right");
                                    } else {
                                        ui.cls.push("pq-align-center");
                                        pid = pid.split("_")[ui.rowData.pq_level];
                                    }
                                }

                                ui.rowData["得意先名"] = pid;
                                return { text: pid };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "締日",
                        dataIndx: "締日", dataType: "string",
                        align : "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                //総計行
                                if (!!ui.Export) {
                                    ui.rowData["締日"] = "* *";
                                    return { text: "* *" };
                                } else {
                                    ui.rowData["締日"] = "総計";
                                    return { text: "総計" };
                                }
                            }
                            if (!!ui.rowData.pq_gtitle && ui.rowData.pq_level == 1) {
                                if (vue.viewModel.SummaryKind == 2) {
                                    ui.rowData["締日"] = "コース計";
                                    return { text: ui.rowData["締日"] };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                //小計行
                                ui.rowData["締日"] = ui.rowData.pq_level == 0 ? "日計" : "コース計";
                                return { text: ui.rowData["締日"] };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "日締現金",
                        dataIndx: "日締現金", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "バークレー",
                        dataIndx: "バークレー", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "束売り",
                        dataIndx: "束売り", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売掛現金",
                        dataIndx: "売掛現金", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "現金計",
                        dataIndx: "現金計", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込",
                        dataIndx: "振込", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振替",
                        dataIndx: "振替", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "チケット",
                        dataIndx: "チケット", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "調整額",
                        dataIndx: "調整額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "合計",
                        dataIndx: "合計", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            },
        });

        data.grid1Options.filter = this.setNavigator;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01180Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //入金日付を"YYYYMMDD"形式に編集
                        params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        vue.DAI01180Grid1.searchData(params);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01180Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01180Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01180Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01180Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01180Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSummaryKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01180Grid1;

            var isCourseSummary = vue.viewModel.SummaryKind == "2";

            //列タイトル変更
            grid.options.colModel.find(c => c.dataIndx == "得意先名").title = isCourseSummary ? "コース名" : "得意先名";
            grid.options.colModel.find(c => c.dataIndx == "締日").title = isCourseSummary ? "" : "締日";
            grid.refreshCM();
            grid.refresh();

            //集計変更
            grid.Group()[isCourseSummary ? "collapse" : "expand"](1);
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            if (vue.viewModel.DateStart == vue.viewModel.DateEnd) {
                axios.post(
                    "/Utilities/GetCourseKbnFromDate",
                    {TargetDate: moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYYMMDD")}
                )
                    .then(res => {
                        console.log(res);
                        vue.viewModel.CourseKbn = res.data.コース区分;

                        //条件変更ハンドラ
                        vue.conditionChanged();
                    })
                    .catch(err => {
                        console.log(err);
                    });
            } else {
                vue.viewModel.CourseKbn = null;
            }

        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01180Grid1;
            console.log("1180 conditionChanged")

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart) return;

            var params = $.extend(true, {}, vue.viewModel);

            //入金日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //コースコードはフィルタするので除外
            delete params.CourseCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01180Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.CourseCd * 1 });
            }

            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            res.forEach(r => r.コース = r.コースＣＤ==null || r.コース名==null ? "コースなし" : (r.コースＣＤ + " " + r.コース名));

            vue.footerButtons.find(v => v.id == "DAI01180Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01180Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01180Grid1_Print").disabled = !res.length;

            return res;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            return list;
        },
        setNavigator: function(evt, ui) {
            var vue = this;
            console.log("setNavigator", evt, ui);
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
            `;

            var headerFunc = (header, idx, length) => {
                return `
                    <div class="title">
                        <h3> * * コース別入金一覧表 * * </h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 10%; text-align: left;">${vue.viewModel.BushoCd}:${vue.viewModel.BushoNm}</th>
                                <th style="width: 10%;"></th>
                                <th style="width: 41%; text-align: left;">${vue.viewModel.SummaryKind == 2 ? "" : header.コース}</th>
                                <th style="width: 10%;"></th>
                                <th style="width: 15%;"></th>
                                <th style="width: 8%;"></th>
                                <th style="width: 6%;"></th>
                            </tr>
                            <tr>
                                <th style="text-align: left;">${vue.viewModel.SummaryKind == 2 ? header.日付 : header.parentId}</th>
                                <th></th>
                                <th></th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCourseSummary =`
                table.DAI01180Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01180Grid1 tr.group-row td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI01180Grid1 tr[level="0"].group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI01180Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI01180Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI01180Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI01180Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI01180Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI01180Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;
            var styleCustomers =`
                table.DAI01180Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01180Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI01180Grid1 tr[level="0"].group-summary td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI01180Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01180Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI01180Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI01180Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI01180Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI01180Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI01180Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01180Grid1.generateHtml(
                                vue.viewModel.SummaryKind == 2 ? styleCourseSummary : styleCustomers,
                                headerFunc,
                                25,
                                vue.viewModel.SummaryKind == 2 ? [false, true] : false,
                                true,
                                vue.viewModel.SummaryKind == 2 ? [true, false] : [false, true],
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
