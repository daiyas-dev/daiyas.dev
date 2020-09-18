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
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: 1 }'
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
                    bind="TantoCd"
                    dataUrl="/Utilities/GetUserList"
                    title="担当者一覧"
                    labelCd="担当者ID"
                    labelCdNm="担当者名"
                    :showColumns='[{ title: "部署名", dataIndx: "部署.部署名", dataType: "string", width: 200 }]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
                    :isTrim=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01040Grid1"
            ref="DAI01040Grid1"
            dataUrl="/DAI01040/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onBeforeCreateFunc=this.onBeforeCreateFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
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
#DAI01040Grid1 div.tk_info {
    width: 100%;
}
#DAI01040Grid1 div.tk_credit div {
    color: royalblue;
}
#DAI01040Grid1 div.tk_code {
    width: 70%;
}
#DAI01040Grid1 div.tk_payment {
    width: 30%;
}
#DAI01040Grid1 div.tk_name {
    width: 100%;
}

/* 合計行 */
#DAI01040Grid1　.pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                TantoCd: this.viewModel.TantoCd,
            };
        }
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > コース別注文入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoInfo: null,
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
                            return _(rowData).pickBy((v, k) => k.startsWith("OrderPrice")).map(v => v * 1).values().sum();
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
                        width: 150, maxWidth: 1000, minWidth: 150,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                ui.column.align = "center";
                                return ui;
                            } else {
                                var el = $("<div>").addClass("tk_info").addClass(ui.rowData.得意先支払方法 == "売掛" ? "tk_credit" :"tk_cash")
                                    .append($("<div>").addClass("d-flex")
                                        .append($("<div>").addClass("tk_code text-left pl-2").text(ui.rowData.得意先ＣＤ))
                                        .append($("<div>").addClass("tk_payment").text(ui.rowData.得意先支払方法))
                                    )
                                    .append($("<div>").addClass("tk_name text-left").text(ui.rowData.得意先名略称))
                                    ;
                                return { text: el[0].outerHTML };
                            }
                        }
                    },
                    {
                        title: "",
                        dataIndx: "Payment", dataType: "string", align: "center",
                        width: 35, maxWidth: 35, minWidth: 35,
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
                        dataIndx: "Checked", type: "checkbox",
                        width: 40, maxWidth: 40, minWidth: 40,
                        align: "center",
                        cbId: "CheckState",
                        editable: false,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                //合計行では非表示
                                return "";
                            }
                        },
                    },
                    {
                        title: "チェック状態",
                        dataIndx: "CheckState",
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
                        vue.DAI01040Grid1.searchData(vue.searchParams);
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

            //PqGrid表示前に必要な情報の取得(商品リスト + その他)
            axios.all(
                [
                    //商品リストの取得
                    axios.post(
                        "/Utilities/GetAvailableProductList",
                        {
                            group: vue.viewModel.BushoInfo ? vue.viewModel.BushoInfo["部署グループ"] : null,
                            isOthersGrouping: true
                        }
                    ),
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
                                title: r["商品名"],
                                colModel: [
                                    {
                                        title: "個数", dataIndx: "OrderQuantity" + r["商品ＣＤ"], dataType: "integer",
                                        width: 40, maxWidth: 40, minWidth: 40,
                                        editable: true, format: "#,###",
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "" };
                                            }
                                            return ui;
                                        },
                                    },
                                    {
                                        title: "金額", dataIndx: "OrderPrice" + r["商品ＣＤ"], dataType: "integer",
                                        width: 75, maxWidth: 75, minWidth: 75,
                                        editable: true, format: "#,##0",
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "" };
                                            }
                                            return ui;
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
                                    width: 75, maxWidth: 75, minWidth: 75,
                                    editable: false, format: "#,##0",
                                        render: ui => {
                                            // zero to blank
                                            return ui.rowData[ui.dataIndx] || "";
                                        },
                                },
                            ],
                        });

                    vue.grid1Options.colModel.filter(c => c.title == "実績")[0].colModel =
                        resProduct.map(r => {
                            return {
                                title: r["商品名"],
                                colModel: [
                                    {
                                        title: "個数", dataIndx: "RecordQuantity" + r["商品ＣＤ"], dataType: "integer",
                                        width: 75, maxWidth: 75, minWidth: 75,
                                        editable: true,
                                        render: ui => {
                                            // zero to blank
                                            return ui.rowData[ui.dataIndx] || "";
                                        },
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
                        vue.grid1Options.summaryData[0].pq_fn["OrderQuantity" + r["商品ＣＤ"]] = "SUMIF(C:C, '現金', " + range1 + ")";
                        vue.grid1Options.summaryData[0].pq_fn["OrderPrice" + r["商品ＣＤ"]] = "SUMIF(C:C, '現金', " + range2 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["OrderQuantity" + r["商品ＣＤ"]] = "SUMIF(C:C, '売掛', " + range1 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["OrderPrice" + r["商品ＣＤ"]] = "SUMIF(C:C, '売掛', " + range2 + ")";
                    });

                    var col = String.fromCharCode("D".charCodeAt() + resProduct.length * 2);
                    var range = col + ":" + col;
                    vue.grid1Options.summaryData[0].pq_fn["QuantitySummary"] = "SUMIF(C:C, '現金', " + range + ")";
                    vue.grid1Options.summaryData[1].pq_fn["QuantitySummary"] = "SUMIF(C:C, '売掛', " + range + ")";

                    resProduct.forEach((r, i) => {
                        var col1 = String.fromCharCode("D".charCodeAt() + resProduct.length * 2 + 1 + i);
                        var range1 = col1 + ":" + col1;
                        vue.grid1Options.summaryData[0].pq_fn["RecordQuantity" + r["商品ＣＤ"]] = "SUMIF(C:C, '現金', " + range1 + ")";
                        vue.grid1Options.summaryData[1].pq_fn["RecordQuantity" + r["商品ＣＤ"]] = "SUMIF(C:C, '売掛', " + range1 + ")";
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
            var merged = _.values(_.groupBy(res, v => v.得意先ＣＤ + v.支払方法))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.得意先ＣＤ = v.得意先ＣＤ;
                                acc.得意先名略称 = v.得意先名略称;
                                acc.売掛現金区分 = v.売掛現金区分;
                                acc.得意先支払方法 = v.得意先支払方法;
                                acc.支払方法 = v.支払方法;
                                acc.CustomerIndex = acc.CustomerIndex || parseInt(i / 2 + 1);
                                acc.CustomerInfo = acc.CustomerInfo || (v.得意先ＣＤ + "\t" + v.支払方法 + "\r\n" + v.得意先名略称);
                                acc.Payment = acc.Payment || v.支払方法;
                                acc["OrderQuantity" + v.商品ＣＤ] = v.個数 * 1;
                                acc["OrderPrice" + v.商品ＣＤ] = v.金額 * 1;
                                acc["RecordQuantity" + v.商品ＣＤ] = v.個数 * 1;

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });
            console.log(merged);

            //mergeCellsの設定
            grid.options.mergeCells = _.flattenDeep(res.filter((r, i) => !(i % 2))
                .map((r, i) => {
                    var checkedCol = grid.options.colModel.filter(c => c.dataIndx == "Checked")[0];
                    var checkStateCol = grid.options.colModel.filter(c => c.dataIndx == "CheckState")[0];
                    return [
                        { r1: i * 2, c1: 0, rc: 2, cc: 1 },
                        { r1: i * 2, c1: 1, rc: 2, cc: 1 },
                        { r1: i * 2, c1: checkedCol.leftPos, rc: 2, cc: 1 },
                        { r1: i * 2, c1: checkStateCol.leftPos, rc: 2, cc: 1 },
                    ];
                })
            );

            return merged;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            var entity = entities.find(e => e.code == code);

            vue.viewModel.BushoInfo = entity.info;
        },
    }
}
</script>
