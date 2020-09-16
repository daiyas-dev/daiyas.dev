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
            :autoToolTipDisabled=true
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
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 未分配一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
            },
            DAI01250Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                freezeCols: false,
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
                    grandSummary: false,
                },
                summaryData: [
                ],
                formulas: [
                    [
                        "得意先名",
                        function(rowData){
                            return _.padStart(rowData.得意先ＣＤ, 7, "0") + " " + rowData.得意先名;
                        }
                    ],
                    [
                        "コース",
                        function(rowData){
                            return _.padStart(rowData.コースＣＤ, 4, "0") + " " + rowData.コース名;
                        }
                    ],
                    [
                        "担当者名",
                        function(rowData){
                            return _.padStart(rowData.担当者ＣＤ, 4, "0") + " " + rowData.担当者名;
                        }
                    ]
                ],
                colModel: [
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string", key: true,
                        width: 200, minWidth: 200,
                        hidden: false,
                        editable: false,
                        fixed: false,
                    },
                    {
                        title: "日付",
                        dataIndx: "日付", dataType: "date", key: true, format: "yy/mm/dd",
                        width: 100, maxWidth: 100, minWidth: 100,
                        hidden: false,
                        editable: false,
                        fixed: false,
                    },
                    {
                        title: "コース",
                        dataIndx: "コース", dataType: "string", key: true,
                        width: 200, maxWidth: 200, minWidth: 200,
                        hidden: false,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "担当者",
                        dataIndx: "担当者名", dataType: "string", key: true,
                        width: 170, maxWidth: 170,
                        hidden: false,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "商品",
                        dataIndx: "商品名", dataType: "string", key: true,
                        width: 160, maxWidth: 160, minWidth: 160,
                        hidden: false,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "数量",
                        dataIndx: "数量", dataType: "integer", key: true, format: "#,###",
                        width: 80, maxWidth: 80, minWidth: 80,
                        hidden: false,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金額", dataType: "integer", key: true, format: "#,###",
                        width: 90, maxWidth: 90, minWidth: 90,
                        hidden: false,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "備考",
                        dataIndx: "備考", dataType: "string", key: true,
                        width: 120, minWidth: 120,
                        hidden: false,
                        editable: false,
                        fixed: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showBunpai(ui.rowData);
                },
            },
        });

        if (!!vue.params) {
            data.viewModel = $.extend(true, data.viewModel, _.omit(vue.params, ["Parent"]));
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01250Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01250Grid1_CSV", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01250Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01250Grid1_Excel", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01250Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01250Grid1_Print", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "分配入力", id: "DAI01250Grid1_Bunpai", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.showBunpai();
                    }
                },
           );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            if (!vue.params || !vue.params.DateStart) {
                vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
                vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            } else {
                vue.conditionChanged();
            }


            //watcher
            vue.$watch(
                "$refs.DAI01250Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01250Grid1_Bunpai").disabled = cnt != 1;
                }
            );
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

        conditionChanged: _.debounce(function(force) {
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

                grid.searchData(params, false, null);
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
                                acc.コース区分 = v.コース区分;
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
                div.header-table {
                    font-family: "MS UI Gothic";
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    height: 22px;
                }
                div.header-table span {
                    margin-right: 25px;
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
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 21px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3>* * * 未分配一覧表 * * *</h3>
                    </div>
                    <div class="header-table" style="border-width: 0px">
                        <tr>
                            <th style="width: 5.0%;">日付</th>
                            <th style="width: 8.5%; text-align: center;">${moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYY/MM/DD")}</th>
                            <th style="width: 3.0%;">～</th>
                            <th style="width: 8.5%; text-align: center;">${moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYY/MM/DD")}</th>
                        </tr>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 6.5%; text-align: left;">部署</th>
                                <th style="width: 5.0%; text-align: left;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 16.5%; text-align: left;">${vue.viewModel.BushoNm}</th>
                                <th style="width: 46.0%;"></th>
                                <th style="width: 6.0%;">作成日</th>
                                <th style="width: 10.0%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 5.0%;">PAGE</th>
                                <th style="width: 5.0%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01250Grid1.generateHtml(
                                `
                                    table.DAI01250Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01250Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01250Grid1 tr th:nth-child(1) {
                                        width: 22.5%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(2) {
                                        width: 7.5%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(3) {
                                        width: 13.5%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(4) {
                                        width: 12.0%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(5) {
                                        width: 11.5%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(6) {
                                        width: 6.5%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(7) {
                                        width: 8%;
                                    }

                                    table.DAI01250Grid1 tr th:nth-child(8) {
                                        width: 18.5%;
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01250Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01250Grid1 tr td:nth-child(8) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.header-table th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(4) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(8) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    div[style="break-before: page;"],
                                    div[style="break-before: auto;"],
                                    div[style="page-break-before: always;"] {
                                        margin-top: 40px !important;
                                        margin-bottom: 40px !important;
                                    }
                                `,
                                headerFunc,
                                25,
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
        showBunpai: function(rowData) {
            var vue = this;
            var grid = vue.DAI01250Grid1;

            var selection = grid.SelectRow().getSelection();

            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                BushoCd: data.部署ＣＤ,
                BushoNm: data.部署名,
                TargetDate: moment(data.日付).format("YYYY年MM月DD日"),
                CourseKbn: data.コース区分,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CustomerCd: data.得意先ＣＤ,
                Parent: vue,
            };

            PageDialog.show({
                pgId: "DAI01101",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1175,
                height: 600,
            });
        },
    }
}
</script>
