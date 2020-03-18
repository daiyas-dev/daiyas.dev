<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="BushoCd"
                    ref="VueMultiSelect_Busho"
                    :vmodel=viewModel
                    bind="BushoArray"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
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
            <div class="col-md-5">
                <VueMultiSelect
                    id="KojoCd"
                    ref="VueMultiSelect_Kojo"
                    :vmodel=viewModel
                    bind="KojoArray"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 37 }"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :onChangedFunc=onKojoChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01230Grid1"
            ref="DAI01230Grid1"
            dataUrl="/DAI01230/Search"
            :query=this.searchParams
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
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
        searchParams: function() {
            var date = !!this.viewModel.DeliveryDate ? moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : moment().format("YYYYMMDD");
            return { DeliveryDate: date };
        },
        BushoCdArray: function() {
            var vue = this;
            return vue.viewModel.BushoArray.map(v => v.code);
        },
        KojoCdArray: function() {
            var vue = this;
            return vue.viewModel.KojoArray.map(v => v.code);
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 部署別製造数一覧表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                DeliveryDate: null,
                CourseKbn: null,
                BentoKbn: null,
                KojoArray: [],
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
                rowHtHead: 35,
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
                        width: 150, maxWidth: 200, minWidth: 150,
                        editable: false,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                ui.rowData.CustomerName = "合計";
                                ui.cls.push("justify-content-end");
                                return { text: "合計" };
                            } else {
                                var el = $("<div>").addClass("d-flex")
                                    .append($("<div>").css("margin-left", "4px").text(ui.rowData.主食副食名))
                                    ;
                                return { text: el.prop("outerHTML") };
                            }
                        },
                        fixed: true,
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
            // vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            //TODO: テスト用初期日付
            vue.viewModel.DeliveryDate = moment("20190904").format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //列表示切替
            vue.toggleCols();
        },
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //TODO:不要？
        },
        onBentoKbnChanged: function(code, entity) {
            var vue = this;

            //TODO:表示変更
        },
        onKojoChanged: function(code, entity) {
            var vue = this;

            //列表示切替
            vue.toggleCols();
        },
        toggleCols: function() {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            grid.options.colModel.forEach(c => c.hidden = (!!c.BushoCd && !!c.KojoCd) &&
                !((!vue.BushoCdArray.length || vue.BushoCdArray.includes(c.BushoCd)) && (!vue.KojoCdArray.length || vue.KojoCdArray.includes(c.KojoCd)))
            );
            grid.refreshCM();
            grid.refresh();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01230Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn || !vue.viewModel.DeliveryDate) return;

            var params = { DeliveryDate: vue.viewModel.DeliveryDate };
            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");

            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {

            var vue = this;
            var grid = vue.DAI01230Grid1;

            //TODO:
            window.res = res;

            //集計結果をもとに、列の設定
            var bushoCdList= _.keys(_.groupBy(res, v => v.部署ＣＤ));
            var bushoNmList = _.keys(_.groupBy(res, v => v.部署名));
            var kojoCdList= _.values(_.groupBy(res, v => v.部署ＣＤ)).map(v => v[0].工場ＣＤ);

            //列定義初期化
            grid.options.colModel = grid.options.colModel.filter(c => !!c.fixed);

            var newCols = grid.options.colModel
                .concat(bushoCdList.map((v, i) => {
                    return {
                        title: bushoNmList[i],
                        dataIndx: "持出数_" + v,
                        dataType: "integer",
                        format: "#,###",
                        width: 90, maxWidth: 90, minWidth: 60,
                        editable: false,
                        render: ui => {
                            if (!ui.cellData) {
                                ui.rowData[ui.dataIndx] = 0;
                                ui.text = 0;
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                        BushoCd: v,
                        KojoCd: kojoCdList[i],
                    };
                }));

            newCols.push(
                {
                    title: "合計",
                    dataIndx: "持出数合計",
                    dataType: "integer",
                    format: "#,###",
                    width: 80, maxWidth: 80, minWidth: 60,
                    editable: false,
                    render: ui => {
                        var grid = eval("this");

                        var sum = _.sum(_.keys(ui.rowData)
                            .filter(k => k.startsWith("持出数_"))
                            .filter(k => !grid.options.colModel.find(c => c.dataIndx == k).hidden)
                            .map(k => ui.rowData[k] || 0)
                            .map(v => _.isString(v) ? pq.deFormatNumber(v) : v)
                        );

                        ui.rowData[ui.dataIndx] = sum;
                        return {text: pq.formatNumber(sum, "#,###")};
                    },
                }
            );

            //列定義更新
            grid.options.colModel = newCols;

            //列表示切替
            vue.toggleCols();

            //集計
            var targetCd = vue.viewModel.BentoKbn == "1" ? "主食ＣＤ" : "副食ＣＤ" ;
            var targetNm = vue.viewModel.BentoKbn == "1" ? "主食名" : "副食名" ;
            var groupings = _(res.filter(v => v[targetCd] != 0))
                .groupBy(v => vue.viewModel.BentoKbn == "1" ? v.主食ＣＤ : v.副食ＣＤ)
                .values()
                .value()
                .filter(v => (v.CHU注文数 != 0 || v.見込数 != 0))
                .map(r => {
                    var ret = _.reduce(
                        _.sortBy(r, [targetCd]),
                            (acc, v, j) => {
                                acc["主食副食名"] = v[targetNm];
                                acc["持出数_" + v.部署ＣＤ] = (acc["持出数_" + v.部署ＣＤ] || 0)
                                     + (v.CHU注文数 == 0 ? v.見込数 * 1 : v.CHU注文数 * 1);
                                acc["最小商品ＣＤ"] = !acc["最小商品ＣＤ"] ? (v.商品ＣＤ * 1)
                                    : (acc["最小商品ＣＤ"] > v.商品ＣＤ ? v.商品ＣＤ : acc["最小商品ＣＤ"]);

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });

            groupings = _(groupings).sortBy(["最小商品ＣＤ", targetCd]).value();

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
