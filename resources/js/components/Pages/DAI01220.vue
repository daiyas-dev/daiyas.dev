<!--
TODO: コース担当者が休み等の場合に、コーステーブルに載っていない得意先ＣＤに配送するケースがある。
      集計は、コーステーブルで取得した、コースに属する得意先ＣＤに加え、
      コースに載っていないが配送した得意先ＣＤを加えて表示するように改修する必要があるかもしれない。
      改修の方針はダイヤス殿にて検討中。
-->
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
                <label>配送日付</label>
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
                    :isPreload=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <VueCheck
                    id="VueCheck_IncNoJisseki"
                    ref="VueCheck_IncNoJisseki"
                    :vmodel=viewModel
                    bind="IsIncNoJisseki"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '含む', label: '実績なしは出力しない'},
                        {code: '1', name: '含まない', label: '実績なしは出力しない'},
                    ]"
                    :onChangedFunc=onIncNoJissekiChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01220Grid1"
            ref="DAI01220Grid1"
            dataUrl="/DAI01220/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI01220Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01220Grid1 .pq-group-icon {
    display: none !important;
}
#DAI01220Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI01220Grid1 .pq-td-div span {
    line-height: inherit;
    text-align: center;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01220",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 得意先別実績表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                CourseKbn: null,
                CourseCd: null,
                IsIncNoJisseki:"0",
            },
            DAI01220Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHt: 35,
                freezeCols: 4,
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
                formulas: [
                ],
                colModel: [
                    {
                        title: "GroupKey",
                        dataIndx: "GroupKey", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string", key: true,
                        hidden: true,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string", key: true,
                        width: 60, minWidth: 60, maxWidth: 60,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string", key: true,
                        width: 200, minWidth: 200,
                        tooltip: true,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["得意先名"] = "合計";
                                return { text: "合計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                //小計行
                                ui.rowData["得意先名"] = "小計";
                                return { text: "小計" };
                            }
                            return ui;
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01220Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            //TODO:
            // vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            // vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateStart = moment("20190507").format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment("20190508").format("YYYY年MM月DD日");

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
                    grid = vue.DAI01220Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();
                axios.post("/DAI01220/ColSearch", { BushoCd: vue.viewModel.BushoCd })
                    .then(response => {
                        var res = _.cloneDeep(response.data);
                        vue.ProductList = res;
                        var newCols = grid.options.colModel.filter(v => !!v.fixed);
                        var productCols = res.map((v, i) => {
                            return {
                                title: v.各種名称,
                                custom: true,
                                hasSummary: true,
                                cd: v.商品区分,
                                colModel: [
                                    {
                                        title: "個数",
                                        dataIndx: "個数_" + v.商品区分,
                                        dataType: "integer",
                                        format: "#,###",
                                        width: 60, maxWidth: 60, minWidth: 60,
                                        summary: {
                                            type: "TotalInt",
                                        },
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "0" };
                                            }
                                            return ui;
                                        },
                                    },
                                    {
                                        title: "金額",
                                        dataIndx: "金額_" + v.商品区分,
                                        dataType: "integer",
                                        format: "#,##0",
                                        width: 80, maxWidth: 80, minWidth: 80,
                                        summary: {
                                            type: "TotalInt",
                                        },
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "0" };
                                            }
                                            return ui;
                                        },
                                    },
                                ],
                            };
                        });
                        newCols = newCols.concat(productCols);

                        //みそ汁追加
                        newCols.push(
                            {
                                title: "みそ汁",
                                dataIndx: "みそ汁",
                                dataType: "integer",
                                format: "#,##0",
                                width: 60, maxWidth: 60, minWidth: 60,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "0" };
                                    }
                                    return ui;
                                },
                            }
                        );

                       //値引追加
                        newCols.push(
                            {
                                title: "値引",
                                dataIndx: "値引",
                                dataType: "integer",
                                format: "#,##0",
                                width: 60, maxWidth: 60, minWidth: 60,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "0" };
                                    }
                                    return ui;
                                },
                            }
                        );

                       //実績ありフラグを追加
                        newCols.push(
                            {
                                hidden: true,
                                title: "実績",
                                dataIndx: "実績",
                                dataType: "integer",
                            }
                        );

                        //列定義更新
                        grid.options.colModel = newCols;
                        grid.refreshCM();
                        grid.refresh();

                        if (!!grid) grid.hideLoading();

                        //条件変更ハンドラ
                        vue.conditionChanged();
                    });
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
        onIncNoJissekiChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseEndChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01220Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            }

            var params = $.extend(true, {}, vue.viewModel);

            //配送日を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //コースはフィルタするので除外
            delete params.CourseCd;

            grid.searchData(params, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01220Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.CourseCd});
            }
            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
            }
            //実績なしを除外するか？
            if(vue.viewModel.IsIncNoJisseki == "1")
            {
                var crulesNoJisseki = [];
                crulesNoJisseki.push({ condition: "equal", value: 1});
                rules.push({ dataIndx: "実績", mode: "AND", crules: crulesNoJisseki});
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //集計
            var groupings = _(res)
                .groupBy(v => [v.コースＣＤ,v.得意先ＣＤ])
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["得意先ＣＤ"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                if (v.売掛現金区分 != 4) {
                                    acc["個数_" + v.商品区分] = (acc["個数_" + v.商品区分] || 0) + v.現金個数 * 1;
                                    acc["個数_" + v.商品区分] = (acc["個数_" + v.商品区分] || 0) + v.掛売個数 * 1;
                                    acc["個数_" + v.商品区分] = (acc["個数_" + v.商品区分] || 0) + v.分配元数量 * 1;
                                    acc["金額_" + v.商品区分] = (acc["金額_" + v.商品区分] || 0) + v.現金金額 * 1;
                                    acc["金額_" + v.商品区分] = (acc["金額_" + v.商品区分] || 0) + v.掛売金額 * 1;
                                    acc["値引"] = (acc["値引"] || 0) + v.現金値引 * 1;
                                    acc["値引"] = (acc["値引"] || 0) + v.掛売値引 * 1;
                                    if(acc["個数_" + v.商品区分]>0 || acc["金額_" + v.商品区分]>0 || acc["値引_" + v.商品区分]>0){
                                        acc["実績"] = 1;
                                    }
                                }
                                return acc;
                            },
                            {}
                    );
                    ret.GroupKey = ret.コースＣＤ + " " + ret.コース名;
                    return ret;
                })
            groupings = _(groupings).sortBy(v => v.順 * 1).sortBy(v => v.コースＣＤ * 1).value();
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
        print: function() {
            var vue = this;
            alert("印刷1");

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
            alert("印刷2");

            var headerFunc = (header, idx, length) => {
                return `
                    <div class="title">
                        <h3>* * * 得意先別実績表 * * *</h3>
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
            alert("印刷3");

            var styleCourseSummary =`
                table.DAI01220Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01220Grid1 tr.group-row td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI01220Grid1 tr[level="0"].group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI01220Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI01220Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI01220Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI01220Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI01220Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI01220Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;
            var styleCustomers =`
                table.DAI01220Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01220Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI01220Grid1 tr[level="0"].group-summary td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI01220Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01220Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI01220Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI01220Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI01220Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI01220Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI01220Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;
            alert("印刷4");
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01220Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                25,
                                [false, true],
                                true,
                                vue[true, false],
                            )
                        )
                )
                .prop("outerHTML")
                ;
            alert("印刷5");
            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };
            alert("印刷6");
            printJS(printOptions);
            alert("印刷7");
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
