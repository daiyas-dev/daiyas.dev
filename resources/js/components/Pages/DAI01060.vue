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
                <label>日付</label>
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
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-1">
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
        <PqGridWrapper
            id="DAI01060Grid1"
            ref="DAI01060Grid1"
            dataUrl="/DAI01060/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :SearchOnCreate=false
            :SearchOnActivate=false
            :onAfterSearchFunc=this.onAfterSearchFunc
            :resizeFunc=this.gridResizeFunc
            :onCellKeyDownFunc=onCellKeyDownFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
/* 合計行 */
#DAI01060Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
#DAI01060Grid1 .pq-grid-row:nth-child(even) .pq-grid-cell.pq-merge-cell {
    background: white;
    color: initial;
}
#DAI01060Grid1 .pq-grid-row:nth-child(odd) .pq-grid-cell.pq-merge-cell {
    background: #e6f4ff;
    color: initial;
}

label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01060",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                TantoCd: this.viewModel.TantoCd,
            };
        }
    },
    watch: {
    },
    data() {
        var vue = this;

        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 移動入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                UpdateDate: null,
                test: null,
                testKn: null,
            },
            CheckInterVal: null,
            DAI01060Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                rowHt: 25,
                rowHtSum: 25,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: false,
                    header: false,
                    headerMenu: false,
                    dataIndx: ["CustomerIndex", "CustomerInfo"],
                    titleDefault: "{0}",
                    collapsed: [false],
                    merge: true,
                    showSummary: [false],
                    grandSummary: false,
                    summaryEdit: false,
                    icon: ["pq-group-toggle-none"],
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                    [
                        "商品",
                        function (rowData) {
                            return _.padStart(rowData.商品ＣＤ, 3, " ") + ":" + rowData.商品名;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "工場区分",
                        dataIndx: "工場区分",
                        hidden: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return { text: ui.rowData.コース名 };
                            } else {
                                if (!ui.Export) {
                                    ui.style.push("align-items: flex-start;");
                                }
                                return { text: ui.rowData.コースＣＤ + "<br>" + ui.rowData.コース名 };
                            }
                        },
                    },
                    {
                        title: "商品",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ",
                        hidden: true,
                    },
                    {
                        title: "持出数",
                        dataIndx: "持ち出し数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "売上予定",
                        dataIndx: "売上予定",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "工場追加",
                        dataIndx: "受取数_工場",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "もらった",
                        dataIndx: "受取数_一般",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "受取詳細",
                        dataIndx: "受取詳細",
                        dataType: "string",
                        width: 200, minWidth: 200,
                    },
                    {
                        title: "あげた",
                        dataIndx: "引渡数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "引渡詳細",
                        dataIndx: "引渡詳細",
                        dataType: "string",
                        width: 200, minWidth: 200,
                    },
                    {
                        title: "残数",
                        dataIndx: "残数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01060Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(null, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI01060Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI01060Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.DAI01060Grid1.print(vue.setPrintOptions);
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");

            var grid = vue.DAI01060Grid1;

            //watcher
            vue.$watch(
                "$refs.DAI01060Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01060Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            if (!!vue.CheckInterVal) clearInterval(vue.CheckInterVal);
            //TODO: 定期ポーリングの頻度調整
            // vue.CheckInterVal = setInterval(vue.updateCheck, 10000);

            vue.$root.$on("DAI01060_updateCheck", vue.updateCheck);
        },
        updateCheck: function() {
            var vue = this;
            var grid = vue.DAI01060Grid1;

            //更新チェック
            if (!grid.getData().length) return;

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            var checkParams = _.cloneDeep(params);
            checkParams.noCache = true;
            axios.post("/DAI01060/UpdateCheck", checkParams)
                .then(res => {
                    if (res.data.最新修正日時 != vue.viewModel.UpdateDate) {
                        vue.viewModel.UpdateDate = res.data.最新修正日時;
                        grid.blinkDiff(res.data.list);
                    }
                })
                .catch(err => {
                    console.log(err);
                });
        },
        gridResizeFunc: function(grid) {
            var oldHeight = grid.options.height;
            var newHeight = window.innerHeight
                          - grid.widget().position().top
                          - $("#footer-bar:visible").outerHeight()
                          - 20;

            if (_.round(newHeight) != _.round(oldHeight)) {
                grid.options.height += (_.round(newHeight) - _.round(oldHeight));
                grid.refresh();
            }
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
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI01060Grid1;

            console.log("conditionChanged", vue.viewModel);

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CourseKbn) return;

            grid.showLoading();

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            //キャッシュ無効
            params.noCache = true;

            //更新チェック
            var checkParams = _.cloneDeep(params);
            checkParams.noCache = true;
            axios.post("/DAI01060/UpdateCheck", checkParams)
                .then(res => {
                    grid.hideLoading();
                    if (!!force || res.data.最新修正日時 != vue.viewModel.UpdateDate) {
                        vue.viewModel.UpdateDate = res.data.最新修正日時;
                        grid.searchData(params, false, null, callback);
                    }
                })
                .catch(err => {
                    console.log(err);
                    grid.hideLoading();
                    $.dialogErr({
                        title: "異常終了",
                        contents: "データの検索に失敗しました<br/>",
                    });
                });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //summaryDataの設定
            grid.options.summaryData = [];
            // _(res).groupBy(v => _.padStart(v.商品ＣＤ, 3, " ") + ":" + v.商品名)
            _(res).groupBy(v => v.商品名)
                .forIn((v, k) => {
                    var summary = {
                        summaryRow: true,
                        "コース名": grid.options.summaryData.length ? "" : "合計",
                        "商品名": k,
                        pq_fn:{
                            "持ち出し数": "SUMIF(D:D, '" + k + "', F:F)",
                            "売上予定": "SUMIF(D:D, '" + k + "', G:G)",
                            "受取数_工場": "SUMIF(D:D, '" + k + "', H:H)",
                            "受取数_一般": "SUMIF(D:D, '" + k + "', I:I)",
                            "引渡数": "SUMIF(D:D, '" + k + "', K:K)",
                            "残数": "SUMIF(D:D, '" + k + "', M:M)",
                        }
                    };

                    grid.options.summaryData.push(summary);
                });

            //mergeCellsの設定
            var pos = 0;
            _(res).groupBy(v => v.コース名)
                .forIn((v, k) => {
                    var cells = {
                        r1: pos,
                        c1: 2,
                        rc: v.length,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cells);
                    pos += v.length;
                });

            return res;
        },
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printOptions.printType = "raw-html";
            grid.options.printOptions.printStyles = "@media print { @page { size: A4 landscape; } }";

            var table = $($(grid.exportData({ format: "htm", render: true }))[3]);
            var tableHeaders = table.find("tr").filter((i, v) => !!$(v).find("th").length);
            var tableBodies = table.find("tr").filter((i, v) => !!$(v).find("td").length);

            //optional: multiple summary rows
            var courseNm;
            tableBodies.map((i, v) => {
                var row = $(v);
                courseNm = row.find("td[rowspan]").text() || row.find("td").filter((idx, e) => $(e).text().includes("合計")).text() || courseNm;
                if (row.find("td").length != tableHeaders.find("th").length) {
                    row.prepend($("<td>").text(courseNm).hide());
                }
                if (!row.find("td:first").text()) {
                    row.find("td:first").text(courseNm).hide();
                }
                return row.get(0);
            });

            //optional: generate contents for multipages
            var contents = [];
            var maxRowsPerPage = 45;
            _(tableBodies)
                .groupBy(v => $(v).find("td:first").text())
                .values()
                .reduce((a, v, i, o) => {
                    if (!$(v[0]).find("td:first").attr("rowspan")) {
                        $(v[0]).find("td:first").attr("rowspan", v.length).css("border-bottom-width", "1px");
                    }

                    if (!_.isEmpty(a) && a.find(".data-table tr").length + v.length > maxRowsPerPage) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                        a = {};
                    }

                    if (_.isEmpty(a)) {
                        var pageHeader = `
                                            <div class="title">
                                                <h3>* * * 移動表 * * *</h3>
                                            </div>
                                            <table class="header-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">日付</th>
                                                        <th style="width: 15%;">${vue.viewModel.TargetDate}</th>
                                                        <th style="width: 52%; border-top-width: 0px !important;"> </th>
                                                        <th style="width: 16%;">${moment().format("YYYY年MM月DD日 HH:mm:ss")}</th>
                                                        <th style="width: 6%;">PAGE</th>
                                                        <th style="width: 6%;">${contents.length + 1}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        `;

                        a = $("<div>").css("page-break-before", "always")
                            .append(pageHeader)
                            .append($("<table>").addClass("data-table").append(tableHeaders.prop("outerHTML")))
                            .append("<br/>")
                            ;
                    }

                    v.forEach(r => {
                        if (v.indexOf(r) == 0) {
                            $(r).find("td[colspan]").css("border-bottom-width", "1px");
                        }
                        if (v.indexOf(r) == v.length - 1) {
                            $(r).find("td").css("border-bottom-width", "1px");
                        }
                    })
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
                                        height: 12px !important;
                                        font-family: "MS UI Gothic" !important;
                                        font-size: 9pt !important;
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
                                    .grid-contents tr td {
                                        border-top-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .grid-contents tr th:nth-child(1) {
                                        width: 14%;
                                    }
                                    .grid-contents tr th:nth-child(2) {
                                        width: 6%;
                                    }
                                    .grid-contents tr th:nth-child(7),
                                    .grid-contents tr th:nth-child(9)
                                    {
                                        width: 22%;
                                    }
                                    .grid-contents tr th:nth-child(n+3):nth-child(-n+6),
                                    .grid-contents tr th:nth-child(8),
                                    .grid-contents tr th:nth-child(10)
                                    {
                                        width: 6%;
                                        text-align: center;
                                    }
                                    .grid-contents tr td:nth-child(1) {
                                        vertical-align: top;
                                    }
                                </style>
                            `;

            var printable = $("<html>")
                .append($("<head>").append(styles))
                .append($("<body>").append($("<div>").addClass("grid-contents").append(contents)));

            grid.options.printOptions.printable = printable.prop("outerHTML");

            console.log(grid.options.printable);
        },
        onCellKeyDownFunc: function(grid, ui, event) {
            var vue = this;

            switch (event.key) {
                case "Enter":
                    vue.showDetail();
                    return false;
                case "ArrowUp":
                    if (ui.rowIndx > 0) {
                        grid.setSelection(null);
                        grid.setSelection({rowIndx: ui.rowIndx - 1});
                    }
                    return false;
                case "ArrowDown":
                    if (ui.rowIndx < grid.getData().length - 1) {
                        grid.setSelection(null);
                        grid.setSelection({rowIndx: ui.rowIndx + 1});
                    }
                    return false;
                default:
                    return true;
            }
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01060Grid1;
            if (!grid) return;

            var params;

            if (!rowData) {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                rowData = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseKbn: vue.viewModel.CourseKbn,
                CourseCd: rowData.コースＣＤ,
                CourseNm: rowData.コース名,
            };

            grid.showLoading();

            //事前情報取得
            axios.all(
                [
                    //コースリストの取得
                    axios.post("/Utilities/GetCourseList", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, }),
                    //商品リストの取得
                    axios.post("/DAI01061/GetTargetProducts", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, CourseCd: params.CourseCd, }),
                ]
            ).then(
                axios.spread((responseCourse, responseProduct) => {
                    var resCourse = responseCourse.data;
                    var resProduct = responseProduct.data;

                    var checkError = (res, name) => {
                        if (res.onError && !!res.errors) {
                            //メッセージリストに追加
                            Object.values(res.errors).filter(v => v)
                                .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                            //ダイアログ
                            $.dialogErr({ errObj: res });

                            return false;
                        } else if (res.onException) {
                            //メッセージ追加
                            vue.$root.$emit("addMessage", name +"リスト取得失敗(" + vue.page.ScreenTitle + ":" + res.message + ")");

                            //ダイアログ
                            $.dialogErr({
                                title: "異常終了",
                                contents: name +"リストの取得に失敗しました<br/>" + res.message,
                            });

                            return false;
                        } else if (res == "") {
                            //完了ダイアログ
                            $.dialogErr({
                                title: name +"リスト無し",
                                contents: "該当データは存在しません" ,
                            });

                            return false;
                        }

                        return true;
                    };

                    if (!checkError(resCourse)) return;
                    if (!checkError(resProduct)) return;

                    //取得した結果を設定
                    params.CourseList = resCourse;
                    params.ProductList = _.uniqBy(resProduct, "商品ＣＤ");

                    grid.hideLoading();

                    PageDialog.show({
                        pgId: "DAI01061",
                        params: params,
                        isModal: true,
                        isChild: true,
                        width: 800,
                        height: 750,
                        onBeforeClose: (event, ui) => {
                            console.log("onBeforeClose", event, ui);

                            if ($(window.event.target).attr("shortcut") == "ESC") return true;

                            var dlg = $(event.target);
                            var editting = dlg.find(".pq-grid")
                                .map((i, v) => $(v).pqGrid("getInstance").grid)
                                .get()
                                .some(g => !_.isEmpty(g.getEditCell()));
                            var isEscOnEditor = !!window.event && window.event.key == "Escape"
                                && (
                                    $(window.event.target).hasClass("target-input") ||
                                    $(window.event.target).hasClass("pq-cell-editor")
                                );

                            return !editting && !isEscOnEditor;
                        }
                    });
                })
            )
            .catch(error => {
                grid.hideLoading();

                //メッセージ追加
                vue.$root.$emit("addMessage", "DB検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "DBの検索に失敗しました<br/>",
                });
            });
        },
    }
}
</script>
