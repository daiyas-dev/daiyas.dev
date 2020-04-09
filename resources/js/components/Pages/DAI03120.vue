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
            <div class="col-md-5">
                <PopupSelect
                    id="ProductCd"
                    ref="PopupSelect_ProductCd"
                    :vmodel=viewModel
                    bind="ProductCd"
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
                    :onAfterChangedFunc=onProductCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
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
        BushoCdArray: function() {
            var vue = this;
            return vue.viewModel.BushoArray.map(v => v.code);
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 商品売上一覧表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                SummaryKind: null,
                CourseKbn: null,
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
                        width: 100, minWidth: 100,
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
            //TODO:
            // vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            // vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateStart = moment("20190401").format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment("20190430").format("YYYY年MM月DD日");
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
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.ProductCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03120Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.ProductCd != undefined && vue.viewModel.ProductCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.ProductCd * 1 });
                if (crules.length) {
                    rules.push({ dataIndx: "商品ＣＤ", mode: "AND", crules: crules });
                }
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            res.forEach(r => {
                    r.ＧＫ部署 = r.部署ＣＤ + " " + r.部署名;
                    r.平均 = r.数量==0 ? 0 : Math.floor(r.金額 / r.数量);
                });
            return res;
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
                        <h3>* * * コース別入金一覧表 * * *</h3>
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
                table.DAI03120Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI03120Grid1 tr.group-row td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
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
                table.DAI03120Grid1 tr[level="0"].group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI03120Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI03120Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI03120Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI03120Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI03120Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;
            var styleCustomers =`
                table.DAI03120Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
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
                table.DAI03120Grid1 tr[level="0"].group-summary td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI03120Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI03120Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI03120Grid1 tr th:nth-child(1) {
                    width: 4.5%;
                }
                table.DAI03120Grid1 tr th:nth-child(3) {
                    width: 4.5%;
                }
                table.DAI03120Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI03120Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03120Grid1.generateHtml(
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
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
