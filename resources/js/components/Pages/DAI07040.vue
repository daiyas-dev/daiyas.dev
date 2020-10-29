<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-1">
                <label>配送日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="CourseCd"
                    ref="VueMultiSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCdArray"
                    uri="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd }'
                    :hasNull=true
                    :withCode=true
                    :onChangedFunc=onCourseCdChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI07040Grid1"
            ref="DAI07040Grid1"
            dataUrl="/DAI07040/Search"
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
#DAI07040Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI07040Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI07040Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI07040Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
form[pgid="DAI07040"] .multiselect.CourseCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI07040",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                TargetDateFrom: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(1).format("YYYYMMDD"),
                TargetDateTo: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6).format("YYYYMMDD"),
                CourseCdArray: vue.viewModel.CourseCdArray,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI07040Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "個人宅 > 週間曜日予定表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                TargetDateFrom: null,
                TargetDateTo: null,
                CourseCdArray: null,
            },
            BushoInfo: null,
            DAI07040Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: false, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 50,
                rowHtSum: 50,
                stripeRows: true,
                freezeCols: 5,
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
                    dataIndx: ["商品"],
                    showSummary: [true],
                    collapsed: [true],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas: [
                    [
                        "商品",
                        function(rowData) {
                            return rowData.商品ＣＤ + ":" + rowData.商品名;
                        },
                    ],
                    [
                        "合計数量",
                        function(rowData) {
                            return _(rowData)
                                .pickBy((v, k) => !!k && k.startsWith("数量_"))
                                .values()
                                .map(v => v * 1)
                                .sum()
                                ;
                        },
                    ],
                    [
                        "合計金額",
                        function(rowData) {
                            return _(rowData)
                                .pickBy((v, k) => !!k && k.startsWith("金額_"))
                                .values()
                                .map(v => v * 1)
                                .sum()
                                ;
                        },
                    ],
                ],
                colModel: [
                    {
                        title: "商品",
                        dataIndx: "商品",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        render: ui => {
                            var $div = $("<div>")
                            if (!!ui.rowData.pq_grandsummary) {
                                (ui.cls || []).push("justify-content-end");
                                return { text: "* * 出荷合計 * *" + "\n" + "* * 売上金額 * *"};
                            }　else if (!!ui.rowData.pq_gsummary) {
                                (ui.cls || []).push("justify-content-end");
                                return { text: "コース計" };
                            }　else if (ui.rowData.pq_level == 0) {
                                $div.append(
                                    $("<div>").addClass("d-flex")
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.商品.split(/:/)[0] ).width(50)
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.商品.split(/:/)[1])
                                        )
                                )
                                return $div.prop("outerHTML");
                            } else {
                                (ui.cls || []).push("pl-5");
                                return { text: ui.rowData.コースＣＤ + ":" + ui.rowData.コース名 };
                            }
                        },
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                    },
                ],
                filter: function() {
                    vue.dummy();
                }
            },
        });

        data.grid1Options.colModel.push(..._.range(0, 6)
            .map(v => moment().day(1).add(v, "days"))
            .map(d => {
                return {
                    title: d.format("M/D") + "<br>" + d.format("(ddd)"),
                    dataIndx: "数量_" + d.format("MMDD"),
                    dataType: "integer",
                    format: "#,##0",
                    dateCol: true,
                    width: 75, maxWidth: 75, minWidth: 75,
                    summary: {
                        type: "TotalInt",
                    },
                    render: ui => {
                        if (!!ui.rowData.pq_grandsummary) {
                            var cnt = ui.rowData[ui.dataIndx];
                            var val = ui.rowData[ui.dataIndx.replace("数量", "金額")];
                            return { text: cnt + "\n" + val };
                        }
                        return ui;
                    },
                }
            })
        );

        data.grid1Options.colModel.push(..._.range(0, 6)
            .map(v => moment().day(1).add(v, "days"))
            .map(d => {
                return {
                    title: d.format("M/D") + "<br>" + d.format("(ddd)"),
                    dataIndx: "金額_" + d.format("MMDD"),
                    dataType: "integer",
                    format: "#,##0",
                    hidden: true,
                    dateCol: true,
                    summary: {
                        type: "TotalInt",
                    },
                }
            })
        );

        data.grid1Options.colModel.push(
            {
                title: "合計",
                dataIndx: "合計数量",
                dataType: "integer",
                format: "#,##0",
                width: 100, maxWidth: 100, minWidth: 100,
                summary: {
                    type: "TotalInt",
                },
                render: ui => {
                    if (!!ui.rowData.pq_grandsummary) {
                        var cnt = ui.rowData[ui.dataIndx];
                        var val = ui.rowData[ui.dataIndx.replace("数量", "金額")];
                        return { text: cnt + "\n" + val };
                    }
                    return ui;
                },
            }
        );
        data.grid1Options.colModel.push(
            {
                title: "合計",
                dataIndx: "合計金額",
                dataType: "integer",
                format: "#,##0",
                hidden: true,
                summary: {
                    type: "TotalInt",
                },
            }
        );

        data.grid1Options.colModel.push(
            {
                title: "備考",
                dataIndx: "備考", dataType: "string",
                width: 200, minWidth: 200,
            }
        );

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            //MultiSelectで利用出来るよう、ログイン済みの場合は部署CDをViewModelに保持
            vue.viewModel.BushoCd = vue.viewModel.BushoCd || vue.getLoginInfo().bushoCd;

            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI07040Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI07040Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entity, entities) {
            var vue = this;

            if (!!entity) {
                vue.BushoInfo = entity.info;
            }

            //検索条件変更
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;
            var grid = vue.DAI07040Grid1;

            var days = _.range(0, 6).map(v => moment(vue.searchParams.TargetDate).day(1).add(v, "days"));
            var holidays = [];

            grid.options.colModel
                .filter(c => !!c.dataIndx && c.dataIndx.match(/^数量_\d+$/))
                .forEach((c, i) => {
                    var d = days[i];
                    c.title = d.format("M/D") + "<br>" + d.format("(ddd)")
                    c.dataIndx = "数量_" + d.format("MMDD");
                });

            grid.options.colModel
                .filter(c => !!c.dataIndx && c.dataIndx.match(/^金額_\d+$/))
                .forEach((c, i) => {
                    var d = days[i];
                    c.title = d.format("M/D") + "<br>" + d.format("(ddd)")
                    c.dataIndx = "金額_" + d.format("MMDD");
                });

            grid.refreshCM();

            //検索条件変更
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI07040Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;

            if (!force && !!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                return;
            }

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI07040Grid1;

            if (!grid) return;

            var rules = [];
            if (!!vue.viewModel.CourseCdArray.length) {
                var crules = vue.viewModel.CourseCdArray.map(v => {
                    return { condition: "equal", mode: "OR", value: v.code };
                });

                rules = [{ dataIndx: "コースＣＤ", crules: crules }];
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var DeliveryList = res[0].DeliveryList.filter(v => v.数量 != 0);

            //集計
            var groupings = _(DeliveryList)
                .groupBy(v => v.商品ＣＤ)
                .values()
                .map((g, i) => {
                    var rows = _(g)
                        .groupBy(v => v.コースＣＤ)
                        .values()
                        .map((r, i) => {
                            var ret = _.reduce(
                                    r,
                                    (acc, v, j) => {
                                        acc.商品ＣＤ = v.商品ＣＤ;
                                        acc.商品名 = v.商品名;
                                        acc.コースＣＤ = v.コースＣＤ;
                                        acc.コース名 = v.コース名;

                                        var ymd = moment(v.日付).format("MMDD");
                                        acc["数量_" + ymd] = (acc["数量_" + ymd] || 0) + v.数量 * 1;
                                        acc["金額_" + ymd] = (acc["金額_" + ymd] || 0) + v.金額 * 1;

                                        return acc;
                                    },
                                    {}
                            );
                            ret.OrginalArray = r;

                            return ret;
                        })
                        .value()
                        ;

                    return rows;
                })
                .flatten()
                .value()
                ;

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
                .filter(v => v.部署ＣＤ == vue.viewModel.BushoCd)
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
        dummy: function() {
            var vue = this;
            console.log("dummy")
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI07040Grid1;

            if (!!vue.viewModel.BushoCd && !vue.BushoInfo) {
                var entity = vue.$refs.VueSelectBusho.$refs.BushoCd.entities.find(v => v.code == vue.viewModel.BushoCd);

                if (!entity) return
                vue.BushoInfo = entity.info;
            }

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
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
                    font-size: 10.5pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 37px;
                    text-align: center;
                }
                td {
                    height: 19px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.row-table:nth-child(even) {
                	break-before: page;
                }
                table.row-table > tbody > tr > td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    padding-bottom: 20px;
                }
                table.row-table:nth-child(even) > tbody > tr > td {
                    border-bottom-width: 1px;
                }
                table.row-table > tbody > tr > td:first-child {
                    border-right-width: 1px;
                }
                table.row-table > tbody > tr > td > div {
                    padding-left: 15px;
                    padding-right: 15px;
                }
                div.title {
                    width: 100%;
                    display: block;
                    text-align: center;
                }
                div.title > h3, div.title > h5 {
                    margin-top: 5px;
                    margin-bottom: 5px;
                }
            `;

            var headerFunc = (header, idx, length) => {
                var TargetDate = vue.viewModel.SimeKbn == "2"
                    ? moment(header.pq_level == 0 ? header.請求日付 : header.parentId).format("YYYY年MM月DD日")
                    : vue.viewModel.SimeKbn == "1"
                        ? moment(vue.searchParams.TargetDate).format("YYYY年MM月DD日")
                        : vue.viewModel.TargetDate
                    ;
                var GroupInfo = vue.viewModel.SimeKbn == "2"
                    ? (header.pq_level == 0 ? (!!header.children.length ? header.children[0].コース : "") : header.コース).split(":")
                    : []
                    ;
                var CourseCd = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[0] || "");
                var CourseNm = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[1] || "");
                var TantoCd = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[2] || "");
                var TantoNm = vue.viewModel.PrintOrder == "0" ? "" : (GroupInfo[3] || "");

                return `
                    <div class="header">
                        <div style="float: left; width: 23%;">
                            <div style="clear: left; height: 18px;"></div>
                            <div style="clear: left;" class="header-box">
                                <div style="float: left; width: 15%;">日付</div>
                                <div style="float: left; width: 76.3%; padding-left: 8px;">${moment(vue.viewModel.TargetDate, "YYYYMMDD").format("YYYY年MM月DD日")}</div>
                            </div>
                            <div style="clear: left;" class="header-box">
                                <div style="float: left; width: 15%;">部署</div>
                                <div style="float: left; width: 15%; text-align: center;">${vue.viewModel.BushoCd}</div>
                                <div style="float: left; width: 60.5%;">${vue.viewModel.BushoNm}</div>
                            </div>
                        </div>
                        <div  class="title" style="float: left; width: 50.7%;">
                            <h3>* * * <span/>週間曜日予定表<span/> * * *</h3>
                            <div style="margin-bottom: 5px;">コース：${!!vue.viewModel.CourseCdArray && vue.viewModel.CourseCdArray.length > 0 ?
                                JSON.stringify(DAI07040.viewModel.CourseCdArray.map(v => v.code)).replace(/"|\[|]/g,"")
                                : "全コース"}
                            </div>
                        </div>
                        <div style="float: left; width: 26.3%;">
                            <div style="clear: left; height: 41px;"></div>
                            <div style="clear: left;" class="header-box">
                                <div style="float: left; width: 18%;">作成日</div>
                                <div style="float: left; width: 37.2%; text-align: center;">${moment().format("YYYY/MM/DD")}</div>
                                <div style="float: left; width: 18%;">PAGE</div>
                                <div style="float: left; width: 16%;">${idx + 1}/${length}</div>
                            </div>
                        </div>
                    </div>
                `;
            };

            var pq_close = grid.pdata.filter(v => !!v.pq_gtitle).map(v => v.pq_close);
            grid.Group().collapseAll(0);

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    .header div:not(.title) {
                                        font-size: 9.5pt;
                                    }
                                    .header-box > div {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                        padding-top: 3px;
                                        padding-left: 3px;
                                        padding-right: 3px;
                                    }
                                    .header-box > div:last-child {
                                        border-right-width: 1px;
                                    }
                                    .header-table th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr th:first-child {
                                        border-left-width: 1px;
                                    }
                                    .header-table tr:nth-child(1) th:nth-child(n+4) {
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr:nth-child(4) th:nth-child(6) {
                                        border-top-width: 0px;
                                    }
                                    table.DAI07040Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI07040Grid1 tr th:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI07040Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI07040Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI07040Grid1 tr td:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI07040Grid1 th:nth-child(1) {
                                        width: 24%;
                                    }
                                    table.DAI07040Grid1 th:nth-child(9) {
                                        width: 18%;
                                    }
                                    table.DAI07040Grid1 tbody tr.grand-summary td:nth-child(1) {
                                        text-align: right;
                                        padding-right: 10px;
                                    }
                                    table.DAI07040Grid1 tbody tr.grand-summary td {
                                        line-height: 1.5em;
                                    }
                                    .text-left {
                                        float: left;
                                    }
                                 `,
                                headerFunc,
                                26,
                                true,
                                false,
                                false,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            pq_close.forEach((v, i) => {
                if (!v) {
                    grid.Group().expandTo(i + "");
                }
            });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
