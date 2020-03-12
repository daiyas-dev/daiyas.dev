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
            <div class="col-md-2">
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
            </div>
            ～
            <div class="col-md-2">
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
            <div class="col-md-3">
                <label>出力区分</label>
                <VueOptions
                    id="SummaryKind"
                    ref="VueOptions_SummaryKind"
                    customLabelStyle="text-align: center;"
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
    data() {
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > コース別入金一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                SummaryKind: null,
                IsIncludeJuchu: "1",
                IsBikoOutput: "0",
                CourseKbn: null,
                CourseCd: null,
                ColHeader : [],
                dd: null,
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
                    indent: 20,
                    dataIndx: ["GroupKey"],
                    showSummary: [true],
                    collapsed: [false],
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
                        title: "GroupKey",
                        dataIndx: "GroupKey", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "integer",
                        width: 100, minWidth: 100, maxWidth: 100,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 250, minWidth: 250, maxWidth: 250,
                        fixed: true,
                    },
                    {
                        title: "日付",
                        dataIndx: "日付", dataType: "string",
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
                        title: "配送コース名",
                        dataIndx: "配送コース名", dataType: "string",
                        width: 300, minWidth: 300, maxWidth: 300,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_gid) {
                                //グループ行
                                ui.rowData["配送コース名"] = ui.rowData.pq_gid;
                                return { text: ui.rowData.pq_gid };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "締日",
                        dataIndx: "締日", dataType: "string",
                        align : "center",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                //総計行
                                ui.rowData["締日"] = "総計";
                                return { text: "総計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                //小計行
                                ui.rowData["締日"] = "コース計";
                                return { text: "コース計" };
                            }
                            if (!!ui.rowData.pq_gid) {
                                //グループ行
                                ui.rowData["締日"] = "コース計";
                                return { text: "コース計" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "日締現金",
                        dataIndx: "日締現金", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "バークレー",
                        dataIndx: "バークレー", dataType: "integer", format: "#,###",　
                        width: 100, minWidth: 100, maxWidth: 100,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "束売り",
                        dataIndx: "束売り", dataType: "integer", format: "#,###",　
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売掛現金",
                        dataIndx: "売掛現金", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "現金計",
                        dataIndx: "現金計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込",
                        dataIndx: "振込", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振替",
                        dataIndx: "振替", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "チケット",
                        dataIndx: "チケット", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        fixed: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "調整額",
                        dataIndx: "調整額", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
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
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //入金日付を"YYYYMMDD"形式に編集
                        params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        vue.DAI01180Grid1.searchData(params, false, null, () => vue.DAI01180Grid1.print(vue.setPrintOptions));
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //入金日付の初期値 -> 当日
            vue.viewModel.DateStart = moment();
            vue.viewModel.DateEnd = moment();
        },
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printHeader =
                `
                    <style>
                        .header-table {

                        }
                        .header-table th {
                            font-family: "MS UI Gothic";
                            font-size: 10pt;
                            font-weight: normal !important;
                            border: solid 1px black !important;
                            white-space: nowrap;
                            overflow: hidden;
                            margin: 0px;
                            padding-left: 3px;
                            padding-right: 3px;
                        }
                        .header-table tr:last-child th{
                            border-bottom-width: 0px !important;
                        }
                    </style>
                    <h3 style="text-align: center; margin: 0px; margin-bottom: 10px;">* * 持ち出し数一覧表 * *</h3>
                    <table style="border-collapse: collapse; width: 100%;" class="header-table">
                        <colgroup>
                                <col style="width:4.58%;"></col>
                                <col style="width:4.60%;"></col>
                                <col style="width:9.00%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th colspan="5">${moment().format("YYYY年MM月DD日 dddd")}</th>
                            </tr>
                            <tr>
                                <th>部署</th>
                                <th>${vue.viewModel.BushoCd}</th>
                                <th colspan="4">${vue.viewModel.BushoNm}</th>
                                <th colspan="6" style="border-top-width: 0px !important;"></th>
                                <th colspan="2">作成日</th>
                                <th colspan="2">${moment().format("YYYY/MM/DD")}</th>
                                <th>PAGE</th>
                                <th>1</th>
                            </tr>
                        </thead>
                    </table>
                `;
            grid.options.printStyles =
                `
                    tr td:nth-child(1) {
                        font-size: 9pt;
                    }
                    tr td:nth-child(n+2) {
                        text-align: right;
                    }
                `;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //列定義更新
            vue.refreshCols();
        },
        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01180Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                //条件変更ハンドラ
                vue.conditionChanged();
            })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: "各種テーブル検索失敗",
                    contents: "各種テーブル検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        onSummaryKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01180Grid1;

            //条件別列変更
            vue.changeColVisible();

            //集計変更
            grid.Group()[vue.viewModel.SummaryKind == "1" ? "expandAll" : "collapseAll"]();
        },
        changeColVisible: function(colModel) {
            var vue = this;
            var grid = vue.DAI01180Grid1;
            var cols = colModel || _.cloneDeep(grid.options.colModel);

            //コースＣＤ、配送コース名は得意先別時は非表示
            cols.filter(v => [/*"コースＣＤ",*/"配送コース名"].includes(v.dataIndx))
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "1";
                });
            //得意先ＣＤ、得意先名は得意先別時は非表示
            cols.filter(v => ["得意先ＣＤ","得意先名"].includes(v.dataIndx))
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2";
                });

            //列定義更新
            grid.options.colModel = cols;
            grid.refreshCM();
            grid.refresh();
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onIncludeJuchuChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01180Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

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

            //集計
            var groupings = _(res)
                .groupBy(v => v.得意先ＣＤ)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["得意先名"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                return acc;
                            },
                            {}
                    );

                    ret.GroupKey = ret.日付 + " " + (ret.コースＣＤ==null ? "コースなし" : ret.コースＣＤ + " " + ret.コース名);
                    return ret;

                })

            groupings = _(groupings).sortBy(v => v.コースＣＤ * 1).sortBy(v => v.日付).sortBy(v => v.順 * 1).value();

            return groupings;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
    }
}
</script>
