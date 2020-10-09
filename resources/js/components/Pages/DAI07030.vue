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
            <div class="col-md-5">
                <PopupSelect
                    id="CourseCd"
                    ref="PopupSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd }'
                    :isPreload=true
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
                    :onAfterChangedFunc=onCourseCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI07030Grid1"
            ref="DAI07030Grid1"
            dataUrl="/DAI07030/Search"
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
#DAI07030Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI07030Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI07030Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI07030Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI07030",
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
                CourseKbn: vue.viewModel.CourseKbn,
                CourseCd: vue.viewModel.CourseCd,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI07030Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "個人宅 > 持出数一覧表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                CourseCd: null,
                CourseNm: null,
            },
            DAI07030Grid1: null,
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
                freezeCols: 2,
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
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string", key: true,
                        hidden: true,
                        editable: false,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string", key: true,
                        width: 200, minWidth: 200,
                        editable: false,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["コース名"] = "合計";
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
                { visible: "true", value: "検索", id: "DAI07030Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI07030Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")}
            )
                .then(res => {
                    console.log(res);
                    vue.viewModel.CourseKbn = res.data.コース区分;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                })
                .catch(err => {
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "祝日マスタの検索に失敗しました<br/>",
                    });
                });
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI07030Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;

            if (!force && !!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                return;
            }

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI07030Grid1;

            if (!grid) return;

            var rules = [];
            if (!!vue.viewModel.CourseCd) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            console.log("7030 aftersearch")
            window.res7030 = res;

            //集計単位取得
            var items = _(res)
                .map(v => _.pick(v, ["商品区分", "商品ＣＤ", "商品略称"]))
                .map(v => {
                    return {
                        "商品区分": (v.商品区分 || 0) * 1,
                        "商品ＣＤ": (v.商品ＣＤ || 0) * 1,
                        "商品略称": v.商品略称,
                    };
                })
                .uniqBy("商品ＣＤ")
                .sortBy(["商品区分", "商品ＣＤ"])
                .value();

            //列定義初期化
            grid.options.colModel = grid.options.colModel.filter(c => c.fixed);

            //列定義に集計単位を設定
            grid.options.colModel = grid.options.colModel.concat(items.map(v => {
                return {
                    title: v.商品略称,
                    dataIndx: "商品ＣＤ_" + v.商品ＣＤ,
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
                                    acc["商品ＣＤ_" + item.商品ＣＤ] = (acc["商品ＣＤ_" + item.商品ＣＤ] || 0)
                                        + (v.商品ＣＤ == item.商品ＣＤ ? v.数量 * 1 : 0);
                                });

                                return acc;
                            },
                            {}
                    );
                    ret.OrginalArray = r;

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
                .filter(v => v.部署ＣＤ == vue.viewModel.BushoCd && v.コース区分 == vue.viewModel.CourseKbn)
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
            var grid = vue.DAI07030Grid1;

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
                    font-size: 10pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
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
                div.title > h2, div.title > h3 {
                    margin-top: 8px;
                    margin-bottom: 8px;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-top: 30px !important;
                    margin-bottom: 42px !important;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            _.reduce(
                                grid.pdata,
                                (acc, r, idx) => {
                                    if (idx % 2 == 0) {
                                        acc.push($("<table>").addClass("row-table")
                                            .append(
                                                $("<tr>")
                                                    .append($("<td>").addClass("col1"))
                                                    .append($("<td>").addClass("col2"))
                                            )
                                        );
                                    }
                                    var td = _.last(acc).children("tr:last-child").children("td.col" + (idx % 2 + 1));

                                    var headerFunc = (header, idx, length, chunk, chunks) => {
                                        return `
                                            <div class="title">
                                                <h2>* * 持ち出し数一覧表 * *</h2>
                                                <h3>${moment(vue.searchParams.TargetDate).format("YYYY年MM月DD日 ddd曜日")}</h3>
                                            </div>
                                            <table class="header-table" style="border-width: 0px">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%; text-align: left;">${vue.viewModel.BushoCd}</th>
                                                        <th style="width: 15%; text-align: left;">${vue.viewModel.BushoNm}</th>
                                                        <th style="width: 30%; text-align: right;">${r.コース名}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        `;
                                    };

                                    var printRows = _.reduce(
                                        r.OrginalArray.concat(_.range(0, 30 - r.OrginalArray.length).fill({ "商品略称": "", "数量": "" })),
                                        (a, v, i) => {
                                            if (i < 15) {
                                                a.push({});
                                            }

                                            var row = i % 15;
                                            var col = Math.floor(i / 15);

                                            a[row]["label" + col] = v.数量==0 ? "" : v.商品略称;
                                            a[row]["value" + col] = v.数量==0 ? "" : v.数量;
                                            return a;
                                        },
                                        []
                                    );

                                    var html = grid.generateHtmlFromJson(
                                        printRows,
                                        `
                                            table.DAI07030Grid1 tr td {
                                                font-size: 13pt;
                                                height: 24px;
                                                border-style: solid;
                                                border-left-width: 1px;
                                                border-top-width: 1px;
                                                border-right-width: 0px;
                                                border-bottom-width: 0px;
                                            }
                                            table.DAI07030Grid1 tr td:nth-child(odd) {
                                                text-align: center;
                                                white-space: normal;
                                                overflow: visible;
                                            }
                                            table.DAI07030Grid1 tr td:nth-child(even) {
                                                text-align: right;
                                                padding-right: 5px;
                                                font-size: 15pt;
                                            }
                                            table.DAI07030Grid1 tr:last-child td {
                                                border-bottom-width: 1px;
                                            }
                                            table.DAI07030Grid1 tr td:last-child {
                                                border-right-width: 1px;
                                            }
                                        `,
                                        headerFunc,
                                        15,
                                        false
                                    );

                                    html.find("div").filter((i, e) => $(e).css("break-before") == "page").css("break-before", "auto");

                                    td.append(html);
                                    return acc;
                                },
                                []
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "dataIndx": [] });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
