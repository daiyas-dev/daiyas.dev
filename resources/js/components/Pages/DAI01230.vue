<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="BushoCdStart"
                    ref="BushoCdSelectStart"
                    :vmodel=viewModel
                    bind="BushoCdStart"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=false
                    :onChangedFunc=onBushoChanged
                />
            </div>
            ～　
            <div class="col-md-2">
                <VueSelect
                    id="BushoCdEnd"
                    ref="BushoCdSelectEnd"
                    :vmodel=viewModel
                    bind="BushoCdEnd"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=false
                    :onChangedFunc=onBushoChanged

                />
            </div>
        </div>
        <div class="row">
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
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="max-width: unset !important; width: 90px">主食副食区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="ShokuKbn"
                    :vmodel=viewModel
                    bind="ShokuKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 39 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onShokuKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>工場ＣＤ</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="FactoryCdStart"
                    :vmodel=viewModel
                    bind="FactoryCdStart"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 37 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onFactoryChanged
                />
            </div>
            ～　
            <div class="col-md-2">
                <VueSelect
                    id="FactoryCdEnd"
                    :vmodel=viewModel
                    bind="FactoryCdEnd"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 37 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onFactoryChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01230Grid1"
            ref="DAI01230Grid1"
            dataUrl="/DAI01230/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01230Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01230Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI01230Grid1 .pq-td-div span {
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
    name: "DAI01230",
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
            ScreenTitle: "日時処理 > 部署別製造数一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                BushoCdStart: null,
                BushoCdEnd: null,
                DeliveryDate: null,
                CourseKbn: null,
                ShokuKbn: null,
                FactoryCdStart: null,
                FactoryCdEnd: null,
            },
            DAI01230Grid1: null,
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
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "string", key: true,
                        editable: false,
                        fixed: true,
                        hidden: true,
                    },
                    {
                        title: "製造品名",
                        dataIndx: "商品名", dataType: "string", key: true,
                        width: 200, minWidth: 200,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["商品名"] = "合計";
                                return { text: "合計" };
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
                { visible: "true", value: "検索", id: "DAI01230Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01230Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.DAI01230Grid1.print(vue.setPrintOptions);
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
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
        onShokuKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onFactoryChanged: function(code, entity) {
            var vue = this;

            //TODO:フィルターではなく条件変更？
            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCdStart || !vue.viewModel.BushoCdEnd ||
                !vue.viewModel.DeliveryDate || !vue.viewModel.CourseKbn || !vue.viewModel.ShokuKbn) return;

            var params = $.extend(true, {}, vue.viewModel);

            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //TODO:sqlで必要？
            //工場ＣＤ 開始/終了はフィルタするので除外？
            // delete params.FactoryCdStart;
            // delete params.FactoryCdEnd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (!!vue.viewModel.FactoryCdStart) {
                crules.push({ condition: "gte", value: vue.viewModel.FactoryCdStart });
            }
            if (!!vue.viewModel.FactoryCdEnd) {
                crules.push({ condition: "lte", value: vue.viewModel.FactoryCdEnd });
            }

            if (crules.length) {
                rules.push({ dataIndx: "工場ＣＤ", mode: "AND", crules: crules });
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
                    dataIndx: "工場ＣＤ_" + v.Cd,
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

            //不足分を空列追加
            grid.options.colModel = grid.options.colModel
                .concat(
                    _.range(16 - grid.options.colModel.filter(c => !c.hidden).length)
                        .map(v => {
                            return {
                                title: "",
                                dataIndx: "empty",
                                dataType: "integer",
                                format: "#,###",
                                width: 60, maxWidth: 60, minWidth: 60,
                                editable: false,
                            };
                        })
                );

            //列定義更新
            grid.refreshCM();

            //集計
            var groupings = _.values(_.groupBy(res, v => v.商品ＣＤ))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.商品ＣＤ = v.商品ＣＤ;
                                acc.商品名 = v.商品名;
                                items.forEach(item => {
                                    acc["工場ＣＤ_" + item.Cd] = (acc["工場ＣＤ_" + item.Cd] || 0)
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
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printOptions.printType = "raw-html";
            grid.options.printOptions.printStyles = "@media print { @page { size: A4; } }";

            var table = $($(grid.exportData({ format: "htm", render: true }))[3]);
            var tableHeaders = table.find("tr").filter((i, v) => !!$(v).find("th").length);
            var tableBodies = table.find("tr").filter((i, v) => !!$(v).find("td").length);

            //optional: generate contents for multipages
            var contents = [];
            var maxRowsPerPage = 35;
            _(tableBodies)
                .groupBy(v => $(v).find("td:first").text())
                .values()
                .reduce((a, v, i, o) => {
                    if (!_.isEmpty(a) && a.find(".data-table tr").length + v.length > maxRowsPerPage) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                        a = {};
                    }

                    if (_.isEmpty(a)) {
                        var pageHeader = `
                                            <div class="title">
                                                <h3>* * 部署別製造数一覧表 ${vue.viewModel.ShokuKbn}* *</h3>
                                            </div>
                                            <table class="header-table">
                                                <colgroup>
                                                        <col style="width:5.0%;"></col>
                                                        <col style="width:5.0%;"></col>
                                                        <col style="width:7.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                        <col style="width:5.5%;"></col>
                                                </colgroup>
                                                <thead>
                                                    <tr>
                                                        <th>日付</th>
                                                        <th colspan="5">${moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY年MM月DD日 dddd")}</th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="6" style="border-top-width: 0px !important;"></th>
                                                        <th colspan="2">作成日</th>
                                                        <th colspan="2">${moment().format("YYYY/MM/DD")}</th>
                                                        <th>PAGE</th>
                                                        <th>${contents.length + 1}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        `;

                        a = $("<div>").css("page-break-before", "always")
                            .append(pageHeader)
                            .append($("<table>").addClass("data-table").append(tableHeaders.prop("outerHTML")));
                    }

                    a.find(".data-table").append(v);

                    if (_.last(o) == v) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                    }

                    return a;
                }, {});

            var styles =  `
                                <style>
                                    .grid-contents .title {
                                        width: 100%;
                                        display: inline-flex;
                                        justify-content: center;
                                        margin-bottom: 10px;
                                    }
                                    .grid-contents .title h3 {
                                        text-align: center;
                                        border: solid 1px black;
                                        border-radius: 4px;
                                        background-color: grey;
                                        margin: 0px;
                                        padding-left: 30px;
                                        padding-right: 30px;
                                    }
                                    .grid-contents table {
                                        width: 100%;
                                        border-collapse:collapse;
                                    }
                                    .grid-contents .header-table tr th {
                                        border-bottom: 0px;
                                    }
                                    .grid-contents tr th,
                                    .grid-contents tr td
                                    {
                                        font-family: "MS UI Gothic" !important;
                                        font-weight: normal !important;
                                        line-height: normal !important;
                                        border: solid 1px black;
                                        margin: 0px;
                                        padding: 0px;
                                        padding-top: 1px;
                                        padding-bottom: 1px;
                                        padding-left: 3px;
                                        padding-right: 3px;
                                    }
                                    .grid-contents tr th:nth-child(1) {
                                        width: 17.5%;
                                    }
                                    .grid-contents tr th:nth-child(n+2) {
                                        width: 5.5%;
                                        text-align: center;
                                    }
                                    .grid-contents tr td:nth-child(1) {
                                        font-size: 9pt !important;
                                    }
                                    .grid-contents tr td:nth-child(n+2) {
                                        text-align: right;
                                    }
                                </style>
                            `;

            var printable = $("<html>")
                .append($("<head>").append(styles))
                .append($("<body>").append($("<div>").addClass("grid-contents").append(contents)));

            grid.options.printOptions.printable = printable.prop("outerHTML");
        },
    }
}
</script>
