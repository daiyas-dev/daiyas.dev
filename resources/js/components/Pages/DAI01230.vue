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
                    id="BentoKbn"
                    :vmodel=viewModel
                    bind="BentoKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 39 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onBentoKbnChanged
                />
            </div>
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
                DeliveryDate: null,
                CourseKbn: null,
                BentoKbn: null,
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
                        title: "製造品名",
                        dataIndx: "主食副食名", dataType: "string",
                        width: 200, maxWidth: 250, minWidth: 200,
                        editable: false,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                ui.rowData.CustomerName = "合計";
                                ui.cls.push("justify-content-end");
                                return { text: "合計" };
                            } else {
                                var el = $("<div>").addClass("d-flex")
                                    .append($("<label>").css("width", "75px").css("text-align", "right").text(ui.rowData.主食副食名))
                                    .append($("<div>").css("margin-left", "4px").text(ui.rowData.主食副食名))
                                    ;
                                return { text: el.prop("outerHTML") };
                            }
                        },
                        fixed: true,
                    },
                    {
                        title: "合計",
                        dataIndx: "持出数合計", dataType: "integer",
                        width: 150, maxWidth: 150, minWidth: 150,
                        editable: false,
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

            //TODO:列定義更新
            vue.refreshCols();

            // //条件変更ハンドラ
            // vue.conditionChanged();
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
        onBentoKbnChanged: function(code, entity) {
            var vue = this;

            //TODO:列定義更新
            vue.refreshCols();

            // //条件変更ハンドラ
            // vue.conditionChanged();
        },
        onFactoryChanged: function(code, entity) {
            var vue = this;

            //TODO:列定義更新
            vue.refreshCols();

            // //条件変更ハンドラ
            // vue.conditionChanged();
        },
        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01230Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();
                //TODO:作業途中
                var params = {BushoCd: vue.viewModel.BushoCd, FactoryCdStart: vue.viewModel.FactoryCdStart, FactoryCdEnd: vue.viewModel.FactoryCdEnd}

                axios.post("/DAI01230/ColSearch", params)
                    .then(response => {
                        var res = _.cloneDeep(response.data);

                         var newCols = grid.options.colModel
                            .filter(v => !!v.fixed)
                            .concat(
                                res.map((v, i) => {
                                    return {
                                        title: v.部署名,
                                        dataIndx: "持出数_" + v.主食ＣＤ,
                                        dataType: "integer",
                                        format: "#,###",
                                        width: 75, maxWidth: 75, minWidth: 75,
                                        //editable: ui => !ui.rowData[ui.dataIndx + "_実績"],
                                        render: ui => {
                                            if (!ui.cellData) {
                                                ui.rowData[ui.dataIndx] = 0;
                                                ui.text = 0;
                                            }
                                            if (ui.rowData[ui.dataIndx + "_注文"] > 0) {
                                                ui.cls.push("order-value");
                                            }
                                            return ui;
                                        },
                                        summary: {
                                            type: "TotalInt",
                                        },
                                        custom: true,
                                    };
                                }))

                            newCols.push(
                                {
                                    title: "合計",
                                    dataIndx: "持出数合計",
                                    dataType: "integer",
                                    format: "#,###",
                                    width: 150, maxWidth: 150, minWidth: 150,
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.CourseKbn || !vue.viewModel.BentoKbn ||
                !vue.viewModel.FactoryCdStart || !vue.viewModel.FactoryCdEnd) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

            var params = $.extend(true, {}, vue.viewModel);
            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {

            //TODO:01080を参考にする
            var vue = this;
            var bentoKbn = vue.viewModel.BentoKbn;

            //集計
            var groupings = _(res)
                .groupBy(v => v.主食ＣＤ)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["主食ＣＤ"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? _.cloneDeep(v) : acc;

                                //delete acc.部署ＣＤ;
                                //delete acc.部署名;
                                delete acc.得意先ＣＤ;
                                delete acc.得意先名;
                                delete acc.商品ＣＤ;
                                delete acc.見込数;
                                delete acc.規定製造パターン;
                                //delete acc.副食ＣＤ;
                                //delete acc.主食ＣＤ;
                                delete acc.CHU商品ＣＤ;
                                delete acc.CHU注文数;
                                delete acc.部署グループ;
                                //delete acc.工場ＣＤ;
                                delete acc.主食名;
                                delete acc.副食名;

                                if(!!acc.主食ＣＤ && acc.主食ＣＤ != 0 && vue.viewModel.BentoKbn == 1){
                                    acc["主食副食名"] = acc.主食名;
                                    acc["持出数_" + v.主食ＣＤ] = !!v.CHU注文数 ? (acc["持出数_" + v.主食ＣＤ] || 0) + v.CHU注文数 * 1 : (acc["持出数_" + v.主食ＣＤ] || 0) + v.見込数 * 1;
                                }
                                if(!!acc.副食ＣＤ && acc.副食ＣＤ != 0 && vue.viewModel.BentoKbn == 2){
                                    acc["主食副食名"] = acc.副食名;
                                    acc["持出数_" + v.副食ＣＤ] = !!v.CHU注文数 ? (acc["持出数_" + v.副食ＣＤ] || 0) + v.CHU注文数 * 1 : (acc["持出数_" + v.副食ＣＤ] || 0) + v.見込数 * 1;
                                }

                                return acc;
                            },
                            {}
                    )

                    // _.keys(ret).forEach(k => {
                    //     if (k.startsWith("持出数_")) {
                    //         var cd = k.replace("持出数_", "");

                    //         ret["持出数_" + cd] = (ret["部署_" + cd + "_注文"] > 0 ? ret["部署_" + cd + "_注文"] : ret["部署_" + cd + "_見込"]) + "";
                    //         ret["部署_" + cd + "_実績"] = ret["部署_" + cd + "_注文"] > 0;
                    //     }
                    // });

                    return ret;
                })

            //groupings = _(groupings).sortBy(v => v.主食ＣＤ * 1).sortBy(v => v.副食ＣＤ * 1).value();

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
                                                <h3>* * 部署別製造数一覧表 ${vue.viewModel.BentoKbn}* *</h3>
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
