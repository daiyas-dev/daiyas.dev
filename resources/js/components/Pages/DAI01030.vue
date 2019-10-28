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
            <div class="col-md-3 d-flex justify-content-center">
                <button id="btn_group_end" type="button" class="btn-light mr-1"><span>グループ終了</span></button>
                <button id="btn_group_search" type="button" class="btn-light ml-1"><span>グループ検索</span></button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配送日</label>
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
            <div class="col-md-2">
                <label class="text-center">時間</label>
                <input class="form-control p-0 text-center" style="width: 80px;" type="text" :value=viewModel.DeliveryTime readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-6">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerAndCourseList"
                    :params="{ bushoCd: viewModel.BushoCd, targetDate: FormattedDeliveryDate }"
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                        { title: "コースCD", dataIndx: "コースCD", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 200, maxWidth: 200, minWidth: 200 }
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=400
                    :onChangeFunc=onCustomerChanged
                />
            </div>
            <div class="col-md-4">
                <label class="text-center">TEL</label>
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=viewModel.TelNo readonly>
                <label class="ml-1 label-blue">{{viewModel.PaymentNm}}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly>
                <input class="form-control ml-1" style="width: 300px;" type="text" :value=viewModel.TantoNm readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly>
                <input class="form-control ml-1" style="width: 300px;" type="text" :value=viewModel.CourseNm readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 offset-md-11 d-flex justify-content-center">
                <button
                    id="btn_view_toggle"
                    type="button"
                    class="btn-light w-100"
                    @click=toggleGridView
                >
                    <span>{{viewModel.IsShowAll ? "初期表示" : "全表示"}}</span>
                </button>
            </div>
        </div>
        <PqGridWrapper
            id="DAI01030Grid1"
            ref="DAI01030Grid1"
            dataUrl="/DAI01030/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onSelectChangeFunc=onSelectChangeFunc
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
        />
        <div class="row m-1">
            <div class="col-md-1">
                <label for="Bikou">備考</label>
            </div>
            <div class="col-md-11">
                    <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
                    <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
                     <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
      </div>
        </div>
    </form>
</template>

<style scoped>
label {
    max-width: 60px;
}
label.label-blue {
    color: royalblue;
    font-size: 12pt;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01030",
    components: {
    },
    computed: {
        FormattedDeliveryDate: function() {
            var vue = this;
            return vue.viewModel.DeliveryDate ? moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
    },
    watch: {
        "viewModel.IsShowAll": {
            handler: function(newVal) {
                console.log("viewModel.IsShowAll:" + newVal);
                var vue = this;
                var grid = vue.DAI01030Grid1;

                grid.filter({
                    oper: "replace",
                    rules: newVal ? [] : [{ dataIndx: "全表示", condition: "notequal", value: "1" }],
                });
            },
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "注文入力",
            noViewModel: true,
            viewModel: {
                CustomerInfo: null,
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                DeliveryTime: null,
                CustomerCd: null,
                CustomerNm: null,
                TelNo: null,
                PaymentCd: null,
                PaymentNm: null,
                TantoCd: null,
                TantoNm: null,
                CourseCd: null,
                CourseNm: null,
                IsShowAll: null,
            },
            DAI01030Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                height: 300,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: false },
                historyModel: { on: false },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                    [
                        "sortIndx",
                        function(rowData){
                            return (rowData["商品ＣＤ"] * 1) || 99999;
                        }
                    ],
                    [
                        "現金金額",
                        function(rowData){
                            return rowData["単価"] * rowData["現金個数"];
                        }
                    ],
                    [
                        "掛売金額",
                        function(rowData){
                            return rowData["単価"] * rowData["掛売個数"];
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "全表示",
                        dataIndx: "全表示", dataType: "integer",
                        width: 80, maxWidth: 80, minWidth: 80,
                        hidden: true,
                    },
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 120, maxWidth: 120, minWidth: 120,
                        editable: ui => {
                            var vue = this;
                            var grid = DAI01030Grid1;

                            if (grid.getSelectionRowData()) {
                                if (grid.getSelectionRowData().pq_ri == ui.rowData.pq_ri
                                    &&
                                    _(grid.getSelectionData()).keys().first() == "商品ＣＤ"
                                ) {
                                    return true;
                                }
                            }

                            return !ui.rowData[ui.dataIndx];
                        },
                        dataUrl: "/Utilities/GetProductList",
                        buddy: "商品名",
                        selectorTitle: "商品一覧",
                        labelCd: "商品ＣＤ",
                        labelCdNm: "商品名",
                        isModal: true,
                        reuse: true,
                        existsCheck: true,
                        onChangeFunc: function(element, info, comp, isNoMsg, isValid) {
                            console.log("grid popupselect onchange func");

                            comp.ui.rowData["商品名"] = info["商品名"];
                            comp.ui.rowData["単価"] = info["売価単価"];
                            comp.grid.refreshCell({ rowIndx: comp.ui.rowIndx, dataIndx: "商品名"});
                            comp.grid.refreshCell({ rowIndx: comp.ui.rowIndx, dataIndx: "単価"});
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "予定数",
                        dataIndx: "予定数", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["予定数"] = "合計";
                                return { text: "合計" };
                            } else {
                                //0非表示
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "現金",
                        dataIndx: "現金",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "現金個数", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        // if (!(ui.rowData[ui.dataIndx] * 1)) {
                                        //     return { text: "" };
                                        // }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "現金金額", dataType: "integer", format: "#,##0",
                                width: 120, maxWidth: 120, minWidth: 120,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        if (!(ui.rowData[ui.dataIndx] * 1)) {
                                            return { text: "" };
                                        }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    {
                        title: "掛売",
                        dataIndx: "掛売",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "掛売個数", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        // if (!(ui.rowData[ui.dataIndx] * 1)) {
                                        //     return { text: "" };
                                        // }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "掛売金額", dataType: "integer", format: "#,##0",
                                width: 120, maxWidth: 120, minWidth: 120,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        //集計行以外、0非表示
                                        if (!(ui.rowData[ui.dataIndx] * 1)) {
                                            return { text: "" };
                                        }
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    {
                        title: "確認",
                        dataIndx: "Checked", type: "checkbox",
                        width: 40, maxWidth: 40, minWidth: 40,
                        align: "center",
                        cbId: "CheckState",
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                //合計行では非表示
                                return "";
                            }
                        },
                    },
                    {
                        title: "確認チェック状態",
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
        },
        mountedFunc: function(vue) {
            vue.viewModel.IsShowAll = false;
            vue.viewModel.DeliveryDate = moment();
            vue.viewModel.DeliveryTime = moment().format("HH:mm:ss");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //vue.getProductList(code);
        },
        onDeliveryDateChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        onCustomerChanged: function(element, info, comp, isNoMsg, isValid) {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            vue.viewModel.CustomerInfo = info;
            vue.viewModel.TelNo = info["電話番号１"];
            vue.viewModel.PaymentCd = info["売掛現金区分"];
            vue.viewModel.PaymentNm = ["現金", "掛売"][vue.viewModel.PaymentCd] || (isValid ? "チケット" : "");
            vue.viewModel.TantoCd = info["担当者ＣＤ"];
            vue.viewModel.TantoNm = info["担当者名"];
            vue.viewModel.CourseKbn = info["コース区分"];
            vue.viewModel.CourseCd = info["コースCD"];
            vue.viewModel.CourseNm = info["コース名"];

            //現金/掛売列の表示制御
            grid.columns["現金個数"].editable = info["売掛現金区分"] == 0;
            grid.columns["現金個数"].cls = info["売掛現金区分"] == 0 ? "cell-editable" : "cell-readonly-force";
            grid.columns["現金金額"].cls = info["売掛現金区分"] == 0 ? "" : "cell-readonly-force";
            grid.columns["掛売個数"].editable = info["売掛現金区分"] == 1;
            grid.columns["掛売個数"].cls = info["売掛現金区分"] == 1 ? "cell-editable" : "cell-readonly-force";
            grid.columns["掛売金額"].cls = info["売掛現金区分"] == 1 ? "" : "cell-readonly-force";

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            if (vue.getLoginInfo().isLogOn && vue.viewModel.BushoCd && vue.viewModel.DeliveryDate && vue.viewModel.CustomerCd) {
                var params = $.extend(true, {}, vue.viewModel);

                //配送日を"YYYYMMDD"形式に編集
                params.DeliveryDate = moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");

                vue.DAI01030Grid1.searchData(params);
            }
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "単価": 0,
                "現金個数": 0,
                "現金金額": 0,
                "掛売個数": 0,
                "掛売金額": 0,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["得意先ＣＤ"];
        },
        toggleGridView: function() {
            var vue = this;
            vue.viewModel.IsShowAll = !vue.viewModel.IsShowAll;
        },
        onSelectChangeFunc: function(grid, ui) {
        },
    }
}
</script>
