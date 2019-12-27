<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :withCode=true
                    style="width:200px"
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
        <PqGridWrapper
            id="DAI01060Grid1"
            ref="DAI01060Grid1"
            dataUrl="/DAI01060/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onAfterSearchFunc=this.onAfterSearchFunc
            :resizeFunc=this.gridResizeFunc
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

label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

import PopupSelect from "@vcs/PopupSelect.vue";
import VueSelect from "@vcs/VueSelect.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01060",
    components: {
        "VueSelect": VueSelect,
        "PopupSelect": PopupSelect,
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYY-MM-DD"),
                CourseCd: this.viewModel.CourseCd,
                TantoCd: this.viewModel.TantoCd,
            };
        }
    },
    watch: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 移動入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                TargetDate: null,
                CourseKbn: null,
            },
            CheckDate: null,
            CheckInterVal: null,
            DAI01060Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "range", row: true },
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
                                ui.style.push("align-items: flex-start;");
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
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01060Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        console.log("F5");
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment();

            if (!!vue.CheckInterVal) clearInterval(vue.CheckInterVal);
            vue.CheckInterVal = setInterval(() => {
                //更新チェック
                var params = $.extend(true, {}, vue.viewModel);
                //日付を"YYYYMMDD"形式に編集
                params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

                var checkParams = _.cloneDeep(params);
                checkParams.noCache = true;
                axios.post("/DAI01060/UpdateCheck", checkParams)
                    .then(res => {
                        if (res.data.最新修正日時 != vue.CheckDate) {
                            vue.CheckDate = res.data.最新修正日時;
                            grid.searchData(params);
                            // axios.post("/DAI01060/UpdateCheck", params)
                            //     .then(res => {
                            //         grid.hideLoading();
                            //         if (res.data.最新修正日時 != vue.CheckDate) {
                            //             vue.CheckDate = res.data.最新修正日時;
                            //             grid.searchData(params, false, null, callback);
                            //         }
                            //     })
                            //     .catch(err => {
                            //         console.log(err);
                            //     });
                        }
                    })
                    .catch(err => {
                        console.log(err);
                    });
            }, 5000);
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

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01060Grid1;

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
                    if (res.data.最新修正日時 != vue.CheckDate) {
                        vue.CheckDate = res.data.最新修正日時;
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

            // grid.options.mergeCells = _.flattenDeep(res.filter((r, i) => !(i % 2))
            //     .map((r, i) => {
            //         var checkedCol = grid.options.colModel.filter(c => c.dataIndx == "Checked")[0];
            //         var checkStateCol = grid.options.colModel.filter(c => c.dataIndx == "CheckState")[0];
            //         return [
            //             { r1: i * 2, c1: 0, rc: 2, cc: 1 },
            //             { r1: i * 2, c1: 1, rc: 2, cc: 1 },
            //             { r1: i * 2, c1: checkedCol.leftPos, rc: 2, cc: 1 },
            //             { r1: i * 2, c1: checkStateCol.leftPos, rc: 2, cc: 1 },
            //         ];
            //     })
            // );

            return res;
        },
    }
}
</script>
