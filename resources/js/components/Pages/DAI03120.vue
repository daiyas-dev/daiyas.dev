<!--
TODO: 部署は1件(部署未指定(=全部署)あり、商品はマルチセレクト
-->
<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配達日付</label>
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
                <label>商品</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="ProductCd"
                    ref="VueMultiSelect_Product"
                    :vmodel=viewModel
                    bind="ProductArray"
                    uri="/Utilities/GetProductList"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :onChangedFunc=onProductCdChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI03120Grid1"
            ref="DAI03120Grid1"
            dataUrl="/DAI03120/Search"
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
#DAI03120Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03120Grid1 .pq-group-icon {
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
    name: "DAI03120",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        ProductCdArray: function() {
            var vue = this;
            return vue.viewModel.ProductArray.map(v => v.code);
        },
    },
    watch: {
        "DAI03120Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI03120Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 商品売上一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                ProductArray: [],
                DateStart: null,
                DateEnd: null,
                ProductCd: null,
            },
            DAI03120Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 0,
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
                    dataIndx: ["ＧＫ部署"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "ＧＫ部署",
                        dataIndx: "ＧＫ部署", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 250, minWidth: 250, maxWidth: 250,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "合　計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                return { text: "小　計" };
                            }
                            return { text:ui };
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "数量", dataType: "integer", format: "#,###",
                        width: 90, minWidth: 90, maxWidth: 90,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary && !ui.rowData.pq_gsummary) {
                                //集計行以下、0非表示
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "金額",
                        dataIndx: "金額", dataType: "integer", format: "#,###",
                        width: 90, minWidth: 90, maxWidth: 90,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "平均",
                        dataIndx: "平均", dataType: "integer", format: "#,###",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "備考",
                        dataIndx: "備考", dataType: "string",
                        width: 100, minWidth: 100,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03120Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI03120Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI03120Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI03120Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI03120Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03120Grid1_Print", disabled: true, shortcut: "F11",
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
        onDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onProductCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03120Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //配達日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //フィルタするパラメータは除外
            delete params.ProductArray;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03120Grid1;
            console.log('filterChanged');

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.ProductArray != undefined && vue.viewModel.ProductArray.length>0) {
                vue.ProductCdArray.map(r=>{
                    var productCd = r * 1;
                    crules.push({ condition: "equal", value: productCd });
                });
            }
            if (crules.length) {
                rules.push({ dataIndx: "商品ＣＤ", mode: "OR", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI03120Grid1_CSV").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI03120Grid1_Excel").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI03120Grid1_Print").disabled = false;

            res.forEach(r => {
                    r.ＧＫ部署 = r.部署ＣＤ + " " + r.部署名;
                    r.平均 = r.数量==0 ? 0 : Math.floor(r.金額 / r.数量);
                });
            return res;
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
                    margin-top: 5px;
                    margin-bottom: 25px;
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
                    height: 19px;
                    text-align: center;
                }
                td {
                    height: 19px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                div.header-table div.blank-cell {
                    border:none;
                    color: transparent;

                }
            `;

            var headerFunc = (header, idx, length) => {
                var bushoCd = header.ＧＫ部署.split(" ")[0];
                var bushoNm = header.ＧＫ部署.split(" ")[1];
                return `
                    <div class="title">
                        <h3>* * * 商品別売上一覧表 * * *</h3>
                    </div>
                    <div class="header-table" style="border-width: 0px">
                        <div>
                            <div style="width: 8%;">部署</div>
                            <div style="width: 8%; text-align: right;">${bushoCd}</div>
                            <div style="width: 20%; padding-left: 10px;">${bushoNm}</div>
                            <div style="width: 50%;" class="blank-cell">blank-cell</div>
                        </div>
                        <div>
                            <div style="width: 14%; text-align: right;">${moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYY/MM/DD")}</div>
                            <div style="width: 4%; text-align: center;">～</div>
                            <div style="width: 14%; text-align: right;">${moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYY/MM/DD")}</div>
                            <div style="width: 28.1%;" class="blank-cell">blank-cell</div>
                            <div style="width: 5.5%;">作成日</div>
                            <div style="width: 15%; text-align: right;">${moment().format("YYYY年MM月DD日")}</div>
                            <div style="width: 5%;">PAGE</div>
                            <div style="width: 6.2%; text-align: right;">${idx + 1}/${length}</div>
                        </div>
                    </div>
                `;
            };

            var styleCustomers =`
                div.header-table{
                    width: 100%
                }
                div.header-table > div {
                    clear: left;
                    width: 100%
                }
                div.header-table > div > div {
                    float: left;
                }
                div.header-table > div > div {
                    font-size: 9pt;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                div.header-table > div:nth-child(1) > div {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                div.header-table > div:nth-child(2) > div:nth-child(1) ,
                div.header-table > div:nth-child(2) > div:nth-child(2) ,
                div.header-table > div:nth-child(2) > div:nth-child(3) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                div.header-table > div:nth-child(2) > div:nth-child(5) ,
                div.header-table > div:nth-child(2) > div:nth-child(6) ,
                div.header-table > div:nth-child(2) > div:nth-child(7) ,
                div.header-table > div:nth-child(2) > div:nth-child(8) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                div.header-table > div > div:nth-child(3),
                div.header-table > div > div:last-child {
                    border-right-width: 1px !important;
                }
                table.DAI03120Grid1 tr:nth-child(1) th ,
                table.DAI03120Grid1 tr td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr:nth-child(1) th:last-child ,
                table.DAI03120Grid1 tr td:last-child {
                    border-right-width: 1px;
                }
                table.DAI03120Grid1 tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr.group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 50px;
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td{
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td:nth-child(2) ,
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 15px;
                    letter-spacing: 0.3em;
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td:nth-child(2):before ,
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2):before {
                    content: "* * ";
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td:nth-child(2):after ,
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2):after {
                    content: " * *";
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(1) {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr.grand-summary td:not(:nth-child(1)) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI03120Grid1 tr td:last-child {
                    border-right-width: 1px !important;
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI03120Grid1 tr th:nth-child(1) {
                    width: 10%;
                }
                table.DAI03120Grid1 tr th:nth-child(2) {
                    width: 30%;
                }
                table.DAI03120Grid1 tr th:nth-child(3) {
                    width: 11%;
                }
                table.DAI03120Grid1 tr th:nth-child(4) {
                    width: 13%;
                }
                table.DAI03120Grid1 tr th:nth-child(5) {
                    width: 12%;
                }
                table.DAI03120Grid1 tr td:nth-child(1) ,
                table.DAI03120Grid1 tr td:nth-child(3) ,
                table.DAI03120Grid1 tr td:nth-child(4) ,
                table.DAI03120Grid1 tr td:nth-child(5) {
                   text-align: right;
                    padding-right: 10px;
                }
                table.DAI03120Grid1 tr td:nth-child(2) {
                    padding-left: 10px;
                }
                table.DAI03120Grid1 tr:last-child:not(.grand-summary):not(.group-summary) td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-top: 30px !important;
                    margin-bottom: 30px !important;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03120Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                42,
                                false,
                                true,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 portrait; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
