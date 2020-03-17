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
                <label>出力日付</label>
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
        <PqGridWrapper
            id="DAI01250Grid1"
            ref="DAI01250Grid1"
            dataUrl="/DAI01250/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
        />
    </form>
</template>

<style>
#DAI01250Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01250Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI01250Grid1 .pq-td-div span {
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
    name: "DAI01250",
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
            ScreenTitle: "日時処理 > 売上月計日計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
            },
            DAI01250Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
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
                },
                summaryData: [
                    { 日付:'合計', summaryRow: true, pq_fn:{TotalNum:'sum(B:B)', TotalPrice:'sum(C:C)'}},
                ],
                formulas: [
                    [
                        "TotalNum",function(rowData){
                            return rowData.現金個数*1 + rowData.売掛個数*1　+ rowData.チケット個数*1
                        }
                    ],
                    [
                        "TotalPrice",function(rowData){
                            return rowData.現金金額*1 + rowData.売掛金額*1　+ rowData.チケット金額*1
                        }
                    ]
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string", key: true,
                        hidden: ui => !ui.Export,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string", key: true,
                        hidden: ui => !ui.Export,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "日付",
                        dataIndx: "日付", dataType: "date", key: true, format: "yy/mm/dd",
                        hidden: false,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["日付"] = "合計";
                                return { text: "合計" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "合計",
                        dataIndx: "合計",
                        colModel: [
                        {
                                title: "個数",
                                dataIndx: "TotalNum", dataType: "integer", key: true, format: "#,###",
                                hidden: false,
                                editable: false,
                                fixed: true,
                                summary: {
                                        type: "TotalInt",
                                    },
                        },
                        {
                            title: "金額",
                            dataIndx: "TotalPrice", dataType: "integer", key: true, format: "#,###",
                            hidden: false,
                            editable: false,
                            fixed: true,
                            summary: {
                                    type: "TotalInt",
                                },
                        },
                        ],
                    },

                    {
                        title: "現金売上",
                        dataIndx: "現金売上",
                        colModel: [
                        {
                            title: "個数",
                            dataIndx: "現金個数", dataType: "integer", key: true, format: "#,###",
                            hidden: false,
                            editable: false,
                            fixed: true,
                            summary: {
                                    type: "TotalInt",
                                },
                        },
                        {
                                title: "金額",
                                dataIndx: "現金金額", dataType: "integer", key: true, format: "#,###",
                                hidden: false,
                                editable: false,
                                fixed: true,
                                summary: {
                                        type: "TotalInt",
                                    },
                        },
                        ],
                    },
                    {
                        title: "売掛売上",
                        dataIndx: "売掛売上",
                        colModel: [
                        {
                                title: "個数",
                                dataIndx: "売掛個数", dataType: "integer", key: true, format: "#,###",
                                hidden: false,
                                editable: false,
                                fixed: true,
                                summary: {
                                        type: "TotalInt",
                                    },
                            },
                        {
                            title: "金額",
                            dataIndx: "売掛金額", dataType: "integer", key: true, format: "#,###",
                            hidden: false,
                            editable: false,
                            fixed: true,
                            summary: {
                                    type: "TotalInt",
                                },
                        },
                        ],
                    },
                    {
                        title: "チケット売上",
                        dataIndx: "チケット売上",
                        colModel: [
                        {
                            title: "個数",
                            dataIndx: "チケット個数", dataType: "integer", key: true, format: "#,###",
                            hidden: false,
                            editable: false,
                            fixed: true,
                            summary: {
                                    type: "TotalInt",
                                },
                        },
                        {
                            title: "金額",
                            dataIndx: "チケット金額", dataType: "integer", key: true, format: "#,###",
                            hidden: false,
                            editable: false,
                            fixed: true,
                            summary: {
                                    type: "TotalInt",
                                },
                        },
                        ],
                    }
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01250Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.DAI01250Grid1.print(vue.setPrintOptions);
                        //TODO: 印刷の追加

                    }
                },
                { visible: "true", value: "CSV出力", id: "DAI01020Grid1_Csvout", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI01250Grid1.print(vue.setPrintOptions);
                        //TODO: ＣＳＶ出力追加

                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
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
                    <h3 style="text-align: center; margin: 0px; margin-bottom: 10px;">* * 売上日計月計表 * *</h3>
                    <div style="text-align: center; >対象期間( ${vue.viewModel.DateStart} ~ ${vue.viewModel.DateEnd})</div>
                    <!--<table style="border-collapse: collapse; width: 100%;" class="header-table">
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
                    </table>-->
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

            //検索条件変更
            vue.conditionChanged();
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },

        conditionChanged: _.debounce(function(callback) {
            var vue = this;
            var grid = vue.DAI01250Grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01250Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 500);
            })
            .then((grid) => {
                var params = $.extend(true, {}, vue.viewModel);

                //日付区間を"YYYYMMDD"形式に編集
                params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;

                grid.searchData(params, false, null, callback);
            });
        }, 300),
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01250Grid1;

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
            //集計単位取得
            var items = _(res
                .filter(v => v.CHU注文数 || v.見込数)
                .map(v => [
                    { Cd: v.主食ＣＤ * 1, Nm: v.主食略称 },
                    { Cd: v.副食ＣＤ * 1, Nm: v.副食略称 }
                ])
            )
            .flatten().uniqBy("Cd").sortBy("Cd").value()
            .filter(v => !!v.Nm);

            //列定義初期化
            grid.options.colModel = grid.options.colModel.filter(c => c.fixed);

            //列定義に集計単位を設定
            grid.options.colModel = grid.options.colModel.concat(items.map(v => {
                return {
                    title: v.Nm,
                    dataIndx: "商品ＣＤ_" + v.Cd,
                    dataType: "integer",
                    format: "#,###",
                    width: 60, maxWidth: 60, minWidth: 60,
                    editable: false,
                    render: ui => {
                        if (!ui.rowData[ui.dataIndx]) {
                            return { text: "" };
                        }
                        return ui;
                    },
                    summary: {
                        type: "TotalInt",
                    },
                };
            }));


            //列定義更新
            grid.refreshCM();

            //集計
            var groupings = _.values(_.groupBy(res, v => v.コースＣＤ))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.コースＣＤ = v.コースＣＤ;
                                acc.コース名 = v.コース名;
                                items.forEach(item => {
                                    acc["商品ＣＤ_" + item.Cd] = (acc["商品ＣＤ_" + item.Cd] || 0)
                                                           + ([v.主食ＣＤ * 1, v.副食ＣＤ * 1].includes(item.Cd) ? (v.CHU注文数 * 1 || v.見込数 * 1) : 0);
                                });

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });

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
