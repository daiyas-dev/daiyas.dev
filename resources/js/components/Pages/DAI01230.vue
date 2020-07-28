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
                    :disabled=true
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
            result: [],
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
                        width: 200, minWidth: 200,
                        editable: false,
                        render: ui => {
                            if (!!ui.Export) ui.cls = [];
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
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01230Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01230Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01230Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01230Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01230Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
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

            //配送日からコース区分取得
            axios.post("/Utilities/GetCourseKbnFromDate", { TargetDate: moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") })
                .then(res => {
                    vue.viewModel.CourseKbn = res.data.コース区分;
                })
                .catch(err => {
                    console.log(err);
                }
            );
        },
        onBentoKbnChanged: function(code, entity) {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            if (!!vue.result.length) {
                //結果の再編集
                var ret = vue.editResult(vue.result);

                //pqGridに設定
                grid.options.dataModel.location = "local";
                grid.options.dataModel.data = ret;
                grid.refreshDataAndView();
                grid.options.dataModel.location = "remote";
            }
        },
        onKojoChanged: function(code, entity) {
            var vue = this;

            //列表示切替
            vue.toggleCols();
        },
        toggleCols: function() {
            var vue = this;
            var grid = vue.DAI01230Grid1;
            console.log("1230 toggleCols")

            grid.options.colModel.forEach(c => c.hidden = (!!c.BushoCd && !!c.KojoCd) &&
                !((!vue.BushoCdArray.length || vue.BushoCdArray.includes(c.BushoCd)) && (!vue.KojoCdArray.length || vue.KojoCdArray.includes(c.KojoCd)))
            );

            //表示列数
            var visible = grid.options.colModel.filter(c => !_.has(c, "empIdx") && !c.hidden).length;

            //空列補完
            var base = 9;
            grid.options.colModel.filter(c => _.has(c, "empIdx")).forEach(c => c.hidden = !(c.empIdx < (base - visible)));

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

            //検索結果の保持:
            vue.result = _.cloneDeep(res);

            vue.footerButtons.find(v => v.id == "DAI01230Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01230Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01230Grid1_Print").disabled = !res.length;

            //結果編集
            return vue.editResult(res);
        },
        editResult: function (res) {
            var vue = this;
            var grid = vue.DAI01230Grid1;

            //集計結果をもとに、列の設定
            var bushoCdList= _.keys(_.groupBy(res, v => v.部署ＣＤ));
            var bushoNmList= _.values(_.groupBy(res, v => v.部署ＣＤ)).map(v => v[0].部署名);
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
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: false,
                        render: ui => {
                            if (!ui.cellData) {
                                ui.rowData[ui.dataIndx] = 0;
                                ui.text = 0;
                            }
                            if (!!ui.Export) {
                                return ui.cellData;
                            } else {
                                return ui;
                            }
                        },
                        summary: {
                            type: "TotalInt",
                        },
                        BushoCd: v,
                        KojoCd: kojoCdList[i],
                    };
                }));

            //空列補完
            var empCol = _.range(0, 7)
                .map(v => {
                    return {
                        title: "", dataIndx: "空列" + v,
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                        hidden: true,
                        render: ui => {
                            if (!!ui.Export) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        empIdx: v,
                    };
                });

            newCols.push(...empCol);

            newCols.push(
                {
                    title: "合計",
                    dataIndx: "持出数合計",
                    dataType: "integer",
                    format: "#,###",
                    width: 120, maxWidth: 120, minWidth: 120,
                    editable: false,
                    render: ui => {
                        var grid = eval("this");

                        var sum = _.sum(_.keys(ui.rowData)
                            .filter(k => k.startsWith("持出数_"))
                            .filter(k => {
                                var col = grid.options.colModel.find(c => c.dataIndx == k);
                                return !!col && !col.hidden;
                            })
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
                                acc["主食副食ＣＤ"] = v[targetCd];
                                acc["主食副食名"] = v[targetNm];
                                acc["持出数_" + v.部署ＣＤ] = (acc["持出数_" + v.部署ＣＤ] || 0)
                                     + (v.CHU注文数 == 0 ? v.見込数 * 1 : v.CHU注文数 * 1);
                                acc["商品区分"] = v.商品区分;
                                acc["最小商品区分"] = !acc["最小商品区分"] ? (v.商品区分 * 1)
                                    : (acc["最小商品区分"] > v.商品区分 ? v.商品区分 : acc["最小商品区分"]);
                                acc["最小商品ＣＤ"] = !acc["最小商品ＣＤ"] ? (v.商品ＣＤ * 1)
                                    : (acc["最小商品ＣＤ"] > v.商品ＣＤ ? v.商品ＣＤ : acc["最小商品ＣＤ"]);

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });

            groupings = groupings.filter(v => {
                var vals = _.keys(v).filter(k => k.startsWith("持出数_")).map(k => v[k]);
                return vals.some(val => val != 0);
            });

            groupings = _(groupings).sortBy(["最小商品区分", vue.viewModel.BentoKbn == "1" ? "最小商品ＣＤ" : targetCd]).value();

            return groupings;
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
                    margin-bottom: 10px;
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
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 40px;
                    text-align: center;
                }
                td {
                    height: 40px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (header, idx, length) => {
                return `
                    <div class="title">
                        <h3>* * 部署別製造数一覧表(${vue.viewModel.BentoKbn == 1 ? "主食" : "副食"}) * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 5%;">日付</th>
                                <th style="width: 25%;">${moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY年MM月DD日 ddd曜日")}</th>
                                <th style="width: 33%;"></th>
                                <th style="width: 8%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY/MM/DD")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 6%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var maxRow = 21;
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01230Grid1.generateHtml(
                                `
                                    table.DAI01230Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01230Grid1 tr th:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI01230Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01230Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01230Grid1 tr td:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI01230Grid1 tr:nth-child(odd) td {
                                        background-color: lightgrey;
                                    }
                                    table.DAI01230Grid1 tr:nth-child(even) td {
                                        background-color: white;
                                    }
                                    table.DAI01230Grid1 tr th:nth-child(1) {
                                        width: 20.0%;
                                    }
                                    table.DAI01230Grid1 tr td:nth-child(1) {
                                        padding-left: 10px;
                                    }
                                    table.DAI01230Grid1 tr td:nth-child(n+1) {
                                        padding-right: 10px;
                                    }
                                `,
                                headerFunc,
                                maxRow,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 ; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
