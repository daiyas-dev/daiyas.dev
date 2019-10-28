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
                    :hasNull=true
                    :onChangedFunc=onCourseKbnChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01020Grid1"
            ref="DAI01020Grid1"
            dataUrl="/DAI01020/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01020Grid1 .pq-group-toggle-none {
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
    name: "DAI01020",
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
            ScreenTitle: "日時処理 > 日配持出入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                CourseKbn: null,
                ProductList: [],
            },
            DAI01020Grid1: null,
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
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
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
                ],
                printSize: "A4",    //TODO: deprecated
                printDirection: "portrait", //"landscape"   //TODO: deprecated
                printHeader: null,
                printHeaderStyle: null,
                printGridHeaderStyle: null,
                printGridStyle: null,
                printStyles: null,
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI01020Grid1_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                    }
                },
                { visible: "true", value: "検索", id: "DAI01020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();

                        // var params = $.extend(true, {}, vue.viewModel);

                        // //配送日を"YYYYMMDD"形式に編集
                        // params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        // vue.DAI01020Grid1.searchData(params);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01020Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //var targets = $.extend(true, {}, grid.createSaveParams());
                        var targets = grid.getCellsByClass({cls: "pq-cell-dirty"})
                            .map(v => {
                                return {
                                    "部署CD": v.rowData["部署CD"],
                                    "コースＣＤ": v.rowData["コースＣＤ"],
                                    "商品CD": v.dataIndx,
                                    "個数": v.rowData[v.dataIndx],
                                    "対象日付": v.rowData["対象日付"],
                                };
                            });
                        var conditions = $.extend(true, {}, vue.viewModel);

                        vue.DAI01020Grid1.saveData(
                            {
                                uri: "/DAI01020/Save",
                                params: { targets: targets },
                                //optional: { conditions: conditions },
                                // done: {
                                //     callback: (gridVue, grid, res) => {
                                //         vue.DAI01020Grid1.searchData(params);
                                //     },
                                // },
                            }
                        );
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment();
        },
        onAfterSearchFunc: function (vue, grid, res) {
            //商品列の抽出
            var cols = grid.options.colModel.filter(c => !c.hidden && !c.key);

            //集計
            var groupings = _.values(_.groupBy(res, v => v.コースＣＤ))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.部署CD = v.部署CD;
                                acc.対象日付 = v.対象日付;
                                acc.コースＣＤ = v.コースＣＤ;
                                acc.コース名 = v.コース名;
                                cols.forEach(col => {
                                    acc[col.dataIndx] = (acc[col.dataIndx] || 0)
                                                      + (col.dataIndx == v.商品ＣＤ ? v.個数 * 1 : 0);
                                });

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });

            return groupings;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            vue.getProductList(code);
        },
        getProductList: function(BushoCd) {
            var vue = this;
            var grid = vue.DAI01020Grid1;

            //商品リスト取得
            axios.post("/DAI01020/GetProductList", { BushoCd: BushoCd })
                .then(response => {
                    vue.viewModel.ProductList = response.data;

                    //列定義に部署CD, 対象日付, コースＣＤ, コース名を設定
                    grid.options.colModel = [
                        {
                            title: "部署CD",
                            dataIndx: "部署CD", dataType: "integer", key: true,
                            hidden: true,
                            width: 100, maxWidth: 100, minWidth: 100,
                            editable: false,
                        },
                        {
                            title: "対象日付",
                            dataIndx: "対象日付", dataType: "string", key: true,
                            hidden: true,
                            width: 100, maxWidth: 100, minWidth: 100,
                            editable: false,
                        },
                        {
                            title: "コースＣＤ",
                            dataIndx: "コースＣＤ", dataType: "integer", key: true,
                            width: 100, maxWidth: 100, minWidth: 100,
                            editable: false,
                        },
                        {
                            title: "コース名",
                            dataIndx: "コース名", dataType: "string", key: true,
                            width: 200, minWidth: 200,
                            editable: false,
                            render: ui => {
                                if (ui.rowData.pq_grandsummary) {
                                    //集計行
                                    ui.rowData["コース名"] = "合計";
                                    return { text: "合計" };
                                }
                                return ui;
                            },
                        },
                    ];

                    //列定義に集計単位を設定
                    grid.options.colModel = grid.options.colModel.concat(vue.viewModel.ProductList.map(v => {
                        return {
                            title: v.表示名,
                            dataIndx: v.商品ＣＤ,
                            dataType: "integer",
                            format: "#,###",
                            width: 60, maxWidth: 60, minWidth: 60,
                            editable: true,
                            summary: {
                                type: "TotalInt",
                            },
                        };
                    }));

                    //列定義更新
                    grid.refreshCM();

                    //条件変更ハンドラ
                    vue.conditionChanged();
                })
                .catch(error => {
                    console.log(error);

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタ検索に失敗しました" + "<br/>" + error.message,
                    });
                });
        },
        onDeliveryDateChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI01020Grid1;

            if (vue.getLoginInfo().isLogOn && vue.viewModel.BushoCd && vue.viewModel.DeliveryDate) {
                var params = $.extend(true, {}, vue.viewModel);

                //配送日を"YYYYMMDD"形式に編集
                params.DeliveryDate = moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");

                if (vue.viewModel.ProductList.length) {
                    vue.DAI01020Grid1.searchData(params);
                } else {
                    vue.getProductList(vue.viewModel.BushoCd);
                }
            }
        },
    }
}
</script>
