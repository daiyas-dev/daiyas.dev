<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>配達日付</label>
            </div>
            <div class="col-md-5">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_DeliveryDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                    customStyle="width: 205px;"
                />
                <label style="width: 120px; max-width: unset; text-align: center;">持出時間</label>
                <DatePickerWrapper
                    id="TakeoutTime"
                    ref="DatePicker_TakeoutTime"
                    format="HH時mm分"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TakeoutTime"
                    :editable=true
                    :onChangedFunc=onTakeoutTimeChanged
                    customStyle="width: 80px;"
                />
            </div>
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
                <label>受注No.</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="ShidashiOrderNoSelect"
                    ref="PopupSelect_ShidashiOrderNo"
                    :vmodel=viewModel
                    bind="ShidashiOrderNo"
                    dataUrl="/Utilities/GetShidashiOrderNoList"
                    :params="{ targetDate: FormattedDeliveryDate }"
                    title="受注No.一覧"
                    labelCd="受注No."
                    :showColumns='[
                        { title: "配達日付", dataIndx: "配達日付", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=false
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :ParamsChangedCheckFunc=ShidashiOrderNoParamsChangedCheckFunc
                    :onChangeFunc=onShidashiOrderNoChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ShidashiOrderNoAutoCompleteFunc
                    :enablePrevNext=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>顧客</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetShidashiCustomerList"
                    :params="{}"
                    title="顧客一覧"
                    labelCd="顧客CD"
                    labelCdNm="顧客名"
                    :showColumns='[
                        { title: "顧客名カナ", dataIndx: "得意先名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150 },
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
                    :inputWidth=150
                    :nameWidth=300
                    :onChangeFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                    :AutoCompleteMinLength=1
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                />
                <label class="label-blue text-center">TEL</label>
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=viewModel.TelNo readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-5">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 300px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 300px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
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
            id="DAI08010Grid1"
            ref="DAI08010Grid1"
            dataUrl="/DAI08010/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :checkChangedFunc=checkChangedFunc
            :checkChangedCancelFunc=checkChangedCancelFunc
            :options=this.grid1Options
            :onCompleteFunc=onCompleteFunc
            :onSelectChangeFunc=onSelectChangeFunc
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
        />
        <div class="row">
            <div class="col-md-1 d-block">
                <label>備考</label>
            </div>
            <div class="col-md-8 d-block">
                <VueDataList v-for="n in 0"
                    v-bind:key='"Bikou" + n'
                    :id='"Bikou" + n'
                    :vmodel=viewModel
                    :bind='"Bikou" + n'
                    dataUrl="/Utilities/GetBikouList"
                    :hasNull=true
                    class="bikou"
                    style="width: 675px;"
                    listHeight="20vh"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 offset-md-5 update-info">
                <label class="label-blue">修正者</label>
                <label class="label-blue" style="width: 100px;"></label>
                <label class="label-blue">修正日時</label>
                <label class="label-blue" style="width: 100px;"></label>
            </div>
        </div>
    </form>
</template>

<style scoped>
label {
    max-width: 60px;
}
.update-info .label-blue {
    max-width: unset !important;
    margin-right: 4px;
}
</style>
<style>
#Page_DAI08010 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08010",
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
                var grid = vue.DAI08010Grid1;

                grid.filter({
                    oper: "replace",
                    rules: newVal ? [] : [{ dataIndx: "全表示", condition: "notequal", value: "1" }],
                });
            },
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理->仕出注文入力",
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
                TantoCd: null,
                TantoNm: null,
                CourseCd: null,
                CourseNm: null,
                Bikou1: null,
                Bikou2: null,
                Bikou3: null,
                Bikou4: null,
                Bikou5: null,
                IsShowAll: null,
                GroupCustomerCd: null,
            },
            DAI08010Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 270,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
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
                            var grid = DAI08010Grid1;

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
                    // {
                    //     title: "確認",
                    //     dataIndx: "Checked", type: "checkbox",
                    //     width: 40, maxWidth: 40, minWidth: 40,
                    //     align: "center",
                    //     cbId: "CheckState",
                    //     render: ui => {
                    //         if (ui.rowData.summaryRow) {
                    //             //合計行では非表示
                    //             return "";
                    //         }
                    //     },
                    // },
                    // {
                    //     title: "確認チェック状態",
                    //     dataIndx: "CheckState",
                    //     dataType: "bool",
                    //     cb: { header: false },
                    //     hidden: true,
                    //     editable: true,
                    // },
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

            console.log("mounted query", vue.query);
            if (!!vue.query.BushoCd) {
                vue.viewModel.BushoCd = vue.query.BushoCd;
            }
            if (!!vue.query.CustomerCd) {
                vue.viewModel.CustomerCd = vue.query.CustomerCd;
            }
            if (!!vue.query.DeliveryDate) {
                vue.viewModel.DeliveryDate = vue.query.DeliveryDate;
            }
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
        onTakeoutTimeChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        onShidashiOrderNoChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            vue.CustomerChanged(info, isValid);
        },
        ShidashiOrderNoChanged: function(info, isValid, noSearch) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            //popupselectがviewModelをwatchしているので、得意先CDから更新
            vue.viewModel.CustomerCd = info["得意先CD"];
            vue.viewModel.CustomerNm = info["得意先名"];

            if (info["部署CD"] && vue.viewModel.BushoCd != info["部署CD"]) {
                vue.viewModel.BushoCd = info["部署CD"];
            }

            vue.viewModel.TelNo = info["電話番号１"];
            vue.viewModel.TantoCd = info["担当者ＣＤ"];
            vue.viewModel.TantoNm = info["担当者名"];
            vue.viewModel.CourseKbn = info["コース区分"];
            vue.viewModel.CourseCd = info["コースCD"];
            vue.viewModel.CourseNm = info["コース名"];
            vue.viewModel.CustomerInfo = info;

            //現金/掛売列の表示制御
            grid.columns["現金個数"].hidden = info["売掛現金区分"] != "0";
            grid.columns["現金個数"].cls = info["売掛現金区分"] == 0 ? "cell-editable" : "cell-readonly-force";
            grid.columns["現金金額"].hidden = info["売掛現金区分"] != "0";
            grid.columns["現金金額"].cls = info["売掛現金区分"] == 0 ? "" : "cell-readonly-force";

            grid.columns["掛売個数"].hidden = info["売掛現金区分"] != "1";
            grid.columns["掛売個数"].cls = info["売掛現金区分"] == 1 ? "cell-editable" : "cell-readonly-force";
            grid.columns["掛売金額"].hidden = info["売掛現金区分"] != "1";
            grid.columns["掛売金額"].cls = info["売掛現金区分"] == 1 ? "" : "cell-readonly-force";

            //顧客ｸﾞﾙｰﾌﾟ
            //vue.setGroupCustomer(vue.viewModel.CustomerCd);

            //条件変更ハンドラ
            vue.conditionChanged(noSearch);
        },
        onCustomerChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            vue.CustomerChanged(info, isValid);
        },
        CustomerChanged: function(info, isValid, noSearch) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            console.log("ShidashiCustomerChanged", JSON.stringify(info));

            //popupselectがviewModelをwatchしているので、得意先CDから更新
            vue.viewModel.CustomerCd = info["得意先ＣＤ"];
            vue.viewModel.CustomerNm = info["得意先名"];

            if (info["部署CD"] && vue.viewModel.BushoCd != info["部署CD"]) {
                vue.viewModel.BushoCd = info["部署CD"];
            }

            vue.viewModel.TelNo = info["電話番号１"];
            vue.viewModel.TantoCd = info["担当者ＣＤ"];
            vue.viewModel.TantoNm = info["担当者名"];
            vue.viewModel.CourseKbn = info["コース区分"];
            vue.viewModel.CourseCd = info["コースCD"];
            vue.viewModel.CourseNm = info["コース名"];
            vue.viewModel.CustomerInfo = info;

            //現金/掛売列の表示制御
            grid.columns["現金個数"].hidden = info["売掛現金区分"] != "0";
            grid.columns["現金個数"].cls = info["売掛現金区分"] == 0 ? "cell-editable" : "cell-readonly-force";
            grid.columns["現金金額"].hidden = info["売掛現金区分"] != "0";
            grid.columns["現金金額"].cls = info["売掛現金区分"] == 0 ? "" : "cell-readonly-force";

            grid.columns["掛売個数"].hidden = info["売掛現金区分"] != "1";
            grid.columns["掛売個数"].cls = info["売掛現金区分"] == 1 ? "cell-editable" : "cell-readonly-force";
            grid.columns["掛売金額"].hidden = info["売掛現金区分"] != "1";
            grid.columns["掛売金額"].cls = info["売掛現金区分"] == 1 ? "" : "cell-readonly-force";

            //顧客ｸﾞﾙｰﾌﾟ
            //vue.setGroupCustomer(vue.viewModel.CustomerCd);

            //条件変更ハンドラ
            vue.conditionChanged(noSearch);
        },
        conditionChanged: function(noSearch) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            if (vue.getLoginInfo().isLogOn
                && vue.viewModel.BushoCd
                && vue.viewModel.DeliveryDate
                && vue.viewModel.CustomerCd
                && noSearch != true
            ) {
                var params = $.extend(true, {}, vue.viewModel);

                //配送日を"YYYYMMDD"形式に編集
                params.DeliveryDate = moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");

                vue.DAI08010Grid1.searchData(params);
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
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                var data = grid.pdata[0];
                var colIndx = !data["商品ＣＤ"] ? grid.columns["商品ＣＤ"].leftPos
                    : _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos;
                grid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        onSelectChangeFunc: function(grid, ui) {
        },
        ShidashiOrderNoAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["得意先名", "住所", "配達先"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.受注Ｎｏ.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.受注Ｎｏ + " : " + "【" + v.部署名 + "】" + v.得意先名;
                    ret.value = v.受注Ｎｏ;
                    ret.text = v.得意先名;
                    return ret;
                })
                ;

            return list;
        },
        CustomerAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            console.time("ShidashiCustomerAutoCompleteFunc");

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["得意先名", "得意先名カナ", "地区名称", "住所"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.得意先ＣＤ.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.得意先ＣＤ + " : " + "【" + v.部署名 + "】" + v.得意先名;
                    ret.value = v.得意先ＣＤ;
                    ret.text = v.得意先名;
                    return ret;
                })
                ;

            console.timeEnd("ShidashiCustomerAutoCompleteFunc");
            console.log("ShidashiCustomerAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        checkChangedFunc: function(grid) {
            console.log("checkChangedFunc");
            return true;
        },
        checkChangedCancelFunc: function(grid) {
            var vue = this;
            var grid = vue.DAI08010Grid1;
            var postData = grid.options.dataModel.postData;

            console.log("checkChangedCancelFunc");

            if (vue.viewModel.CustomerCd != postData.CustomerCd) {
                var ps = vue.$refs.PopupSelect_Customer;
                var info = ps.dataList.find(v => v.Cd == postData.CustomerCd);
                ps.selectValue = info["得意先ＣＤ"]
                ps.selectName = info["得意先名"];
                ps.selectRow = info;
                vue.CustomerChanged(info, true, true);

                ps.AutoCompleteFocusSkip = true;
            }
            if (vue.viewModel.DeliveryDate != postData.DeliveryDate) {
                vue.viewModel.DeliveryDate = moment(postData.DeliveryDate, "YYYYMMDD").format("YYYY年MM月DD日");
            }
        },
        ShidashiOrderNoParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.targetDate;
            console.log("ShidashiOrderNoParamsChangedCheckFunc", newVal, ret);
            return ret;
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.targetDate;
            console.log("CustomerParamsChangedCheckFunc", newVal, ret);
            ret = true;
            return ret;
        },
        onGroupCustomerChanged: function() {
            var vue = this;

            if (!vue.viewModel.GroupCustomerCd) return;

            var vm = _.cloneDeep(vue.viewModel);
            vue.viewModel.GroupCustomerCd = "";

            vue.$router.push({
                path: vue.$route.path,
                query: {
                    userId: vue.$route.query.userId,
                    BushoCd: vm.BushoCd,
                    DeliveryDate: vm.DeliveryDate,
                    CustomerCd: vm.GroupCustomerCd,
                }
            });
        },
    }
}
</script>
