<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="Busyo"
                    :vmodel=viewModel
                    bind="Busyo"
                    uri="/Utilities/GetBusyoList"
                    codeName="Code"
                    textName="Name"
                    :withCode=true
                    style="width:200px"
                />
            </div>
        </div>
        <div class="row">
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
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コースCD</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseCd"
                    ref="PopupSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCd"
                    dataUrl="/Utilities/GetCourseList"
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者CD</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="User"
                    ref="PopupSelect_User"
                    :vmodel=viewModel
                    bind="UserInfo"
                    dataUrl="/Utilities/GetUserList"
                    title="担当者一覧"
                    labelCd="担当者ID"
                    labelCdNm="担当者名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01040Grid1"
            ref="DAI01040Grid1"
            dataUrl="/DAI01040/Search"
            :query=this.viewModel
            :options=this.grid1Options
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onBeforeCreateFunc=this.onBeforeCreateFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01040Grid1 .pq-group-toggle-none {
    display: none !important;
}
/* stripedの反転 */
#DAI01040Grid1 .pq-grid-row:not(.pq-striped) {
    background-color: #e6f4ff !important;
}
#DAI01040Grid1 .pq-grid-row.pq-striped {
    background-color: white !important;
}

/* 合計行 */
.pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: transparent !important;
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
    name: "DAI01040",
    components: {
        "VueSelect": VueSelect,
        "PopupSelect": PopupSelect,
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > コース別注文入力",
            noViewModel: true,
            viewModel: {
                Busyo: null,
                TargetDate: null,
                CourseCd: null,
                TantoCd: null,
            },
            DAI01040Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "range", row: true },
                numberCell: { show: false },
                freezeCols: 3,
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
                summaryData: [
                    {
                        summaryRow: true,
                        pq_fn:{
                            CustomerInfo: "TRIM('合計')",
                            Payment: "TRIM('現金')",
                        }
                    },
                    {
                        summaryRow: true,
                        pq_fn:{
                            CustomerInfo: "TRIM('')",
                            Payment: "TRIM('売掛')",
                        }
                    },
                ],
                formulas: [
                    [
                        "QuantitySummary",
                        function (rowData) {
                            return _(rowData).pickBy((v, k) => k.startsWith("OrderPrice")).values().sum();
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "No.",
                        dataIndx: "CustomerIndex", dataType: "interger", align: "center",
                        width: 30, maxWidth: 30, minWidth: 30,
                    },
                    {
                        title: "得意先",
                        dataIndx: "CustomerInfo", dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                ui.column.align = "center";
                            }
                            return ui;
                        }
                    },
                    {
                        title: "",
                        dataIndx: "Payment", dataType: "string", align: "center",
                        width: 50, maxWidth: 50, minWidth: 50,
                    },
                    {
                        title: "注文",
                        colModel: [],
                    },
                    {
                        title: "実績",
                        colModel: [],
                    },
                    {
                        title: "確認",
                        dataIndx: "Check", type: "checkbox",
                        width: 50, maxWidth: 50, minWidth: 50,
                        cbId: "chk", align: "center",
                        editable: false,
                        render: ui => {
                            console.log("check rendered");
                        },
                    },
                    {
                        title: "チェック状態",
                        dataIndx: "chk",
                        dataType: "bool",
                        cb: { header: false },
                        hidden: true,
                        editable: true,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01040Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.TargetDate = params.TargetDate ? params.TargetDate.replace(/\//g, "") : null;
                        vue.DAI01040Grid1.searchData(params);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                    }
                }
            );
        },
        mountedFunc: function(vue) {
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //TODO: PqGrid表示前に必要な情報の取得(商品リスト + その他)
            axios.all(
                [
                    //商品リストの取得
                    axios.post("/Utilities/GetProductList", { isOthersGrouping: true }),
                 ]
            ).then(
                axios.spread((responseProduct) => {
                    var resProduct = responseProduct.data;

                    if (resProduct.onError && !!resProduct.errors) {
                        //メッセージリストに追加
                        Object.values(resProduct.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resProduct });

                        return;
                    } else if (resProduct.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "単位リスト取得失敗(" + vue.page.ScreenTitle + ":" + resProduct.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "単位リストの取得に失敗しました<br/>" + resProduct.message,
                        });

                        return;
                    } else if (resProduct == "") {
                        //完了ダイアログ
                        $.dialogErr({
                            title: "単位リスト無し",
                            contents: "該当データは存在しません" ,
                        });

                        return;
                    }

                    //取得した結果を設定
                    vue.ProductList = resProduct;

                    //colModelの設定
                    vue.grid1Options.colModel.filter(c => c.title == "注文")[0].colModel =
                        resProduct.map(r => {
                            return {
                                title: r.productNm,
                                colModel: [
                                    {
                                        title: "個数", dataIndx: "OrderQuantity" + r.productCd, dataType: "integer",
                                        width: 40, maxWidth: 40, minWidth: 40,
                                        editable: true, format: "#,###",
                                        render: ui => {
                                            // zero to blank
                                            return ui.rowData[ui.dataIndx] || "";
                                        },
                                    },
                                    {
                                        title: "金額", dataIndx: "OrderPrice" + r.productCd, dataType: "integer",
                                        width: 50, maxWidth: 100, minWidth: 50,
                                        editable: true, format: "#,##0",
                                        render: ui => {
                                            // zero to blank
                                            return ui.rowData[ui.dataIndx] || "";
                                        },
                                    },
                                ],
                            };
                        })
                        .concat({
                            title: "現金売上",
                            colModel: [
                                {
                                    title: "掛売上", dataIndx: "QuantitySummary", dataType: "integer",
                                    width: 65, maxWidth: 100, minWidth: 65,
                                    editable: false, format: "#,##0",
                                },
                            ],
                        });

                    vue.grid1Options.colModel.filter(c => c.title == "実績")[0].colModel =
                        resProduct.map(r => {
                            return {
                                title: r.productNm,
                                colModel: [
                                    {
                                        title: "個数", dataIndx: "RecordQuantity" + r.productCd, dataType: "integer",
                                        width: 50, maxWidth: 100, minWidth: 50,
                                        editable: true,
                                    },
                                ],
                            };
                        });

                    //summaryDataの設定
                    resProduct.forEach((r, i) => {
                        var col1 = String.fromCharCode("D".charCodeAt() + i * 2);
                        var col2 = String.fromCharCode("D".charCodeAt() + i * 2 + 1);
                        var range1 = col1 + ":" + col1;
                        var range2 = col2 + ":" + col2;
                        vue.grid1Options.summaryData[0].pq_fn["OrderQuantity" + r.productCd] = "SUMIF(C:C, '現金', " + range1 + ")";
                        vue.grid1Options.summaryData[0].pq_fn["OrderPrice" + r.productCd] = "SUMIF(C:C, '現金', " + range2 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["OrderQuantity" + r.productCd] = "SUMIF(C:C, '売掛', " + range1 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["OrderPrice" + r.productCd] = "SUMIF(C:C, '売掛', " + range2 + ")";
                    });

                    var col = String.fromCharCode("D".charCodeAt() + resProduct.length * 2);
                    var range = col + ":" + col;
                    vue.grid1Options.summaryData[0].pq_fn["QuantitySummary"] = "SUMIF(C:C, '現金', " + range + ")";
                    vue.grid1Options.summaryData[1].pq_fn["QuantitySummary"] = "SUMIF(C:C, '売掛', " + range + ")";

                    resProduct.forEach((r, i) => {
                        var col1 = String.fromCharCode("D".charCodeAt() + resProduct.length * 2 + 1 + i);
                        var range1 = col1 + ":" + col1;
                        vue.grid1Options.summaryData[0].pq_fn["RecordQuantity" + r.productCd] = "SUMIF(C:C, '現金', " + range1 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["RecordQuantity" + r.productCd] = "SUMIF(C:C, '売掛', " + range1 + ")";
                    });

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "マスタ検索失敗(" + vue.page.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            res = res.map(r => {
                _.values(r.Orders).forEach(o => {
                    r["OrderQuantity" + o.ProductCd] = o.Quantity;
                    r["OrderPrice" + o.ProductCd] = o.Price;
                })
                return r;
            });

            //mergeCellsの設定
            grid.options.mergeCells = res.filter((r, i) => !(i % 2))
                .map((r, i) => {
                    return [
                        { r1: i * 2, c1: 0, rc: 2, cc: 1 },
                        { r1: i * 2, c1: 1, rc: 2, cc: 1 },
                    ];
                })
                .flat();

            return res;
        },
    }
}
</script>
