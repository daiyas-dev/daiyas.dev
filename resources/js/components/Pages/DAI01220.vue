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
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onCourseKbnChanged
                />
            </div>
            <div class="col-md-1">
                <label>コース開始</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseStart"
                    ref="PopupSelect_CourseStart"
                    :vmodel=viewModel
                    bind="CourseStart"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
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
                    :onAfterChangedFunc=onCourseStartChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 offset-md-3">
                <label>コース終了</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseEnd"
                    ref="PopupSelect_CourseEnd"
                    :vmodel=viewModel
                    bind="CourseEnd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 9999 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseEndChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
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
                DeliveryDate: null,
                CourseKbn: null,
                CourseStart: null,
                CourseEnd: null,
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
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 1,
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
                        vue.DAI01220Grid1.print(vue.setPrintOptions);
                        //TODO: 検索 + 印刷とするか？
                        //vue.conditionChanged(() => vue.DAI01220Grid1.print(vue.setPrintOptions));
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment();
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
                            }
                        );

                       //値引追加
                        newCols.push(
                            {
                                title: "値引",
                                dataIndx: "値引",
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
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseStartChanged: function(code, entity) {
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
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            }

            var params = $.extend(true, {}, vue.viewModel);

            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //コース開始/終了はフィルタするので除外
            delete params.CourseStart;
            delete params.CourseEnd;

            grid.searchData(vue.searchParams, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01220Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (!!vue.viewModel.CourseStart) {
                crules.push({ condition: "gte", value: vue.viewModel.CourseStart });
            }
            if (!!vue.viewModel.CourseEnd) {
                crules.push({ condition: "lte", value: vue.viewModel.CourseEnd });
            }

            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
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
                                }
                                return acc;
                            },
                            {}
                    );

                    ret.GroupKey = ret.コースＣＤ + " " + ret.コース名;
                    //ret.配送コース名 = ret.コースＣＤ + " " + ret.コース名;

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
    }
}
</script>
