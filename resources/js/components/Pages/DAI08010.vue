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
                <label style="width: 120px; max-width: unset; text-align: center;">注文日付</label>
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=viewModel.OrderDate readonly tabindex="-1">
                <label class="text-center">時間</label>
                <input class="form-control p-0 text-center" style="width: 80px;" type="text" :value=viewModel.OrderTime readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>顧客</label>
            </div>
            <div class="col-md-5">
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
                    :isNameEditable=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :onChangeFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                    :AutoCompleteMinLength=1
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-2">
                <label class="w-50 text-center">売掛現金区分</label>
                <VueSelect
                    id="UriGenKbn"
                    :vmodel=viewModel
                    bind="UriGenKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 1 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">顧客名ｶﾅ</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" :value="viewModel.CustomerNmK">
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL1</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=viewModel.TelNo1>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL2</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=viewModel.TelNo2>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">Fax</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=viewModel.FaxNo>
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">郵便番号</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.FaxNo>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" :value=viewModel.TelNo2>
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">配達先</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" :value=viewModel.TelNo2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" :value=viewModel.TelNo2>
            </div>
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" :value=viewModel.TelNo2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>エリア</label>
            </div>
            <div class="col-md-3">
                <PopupSelect
                    id="AreaSelect"
                    ref="PopupSelect_Area"
                    :vmodel=viewModel
                    bind="AreaCd"
                    buddy="AreaNm"
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ bushoCd: viewModel.BushoCd }"
                    title="エリア一覧"
                    labelCd="エリアCD"
                    labelCdNm="エリア名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">地域区分</label>
                <VueSelect
                    id="TiikiKbn"
                    :vmodel=viewModel
                    bind="TiikiKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 32 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-2">
                <VueOptions
                    title="AMPM"
                    customLabelStyle="width: 60px; text-align: center;"
                    id="AmPmKbn"
                    :vmodel=viewModel
                    bind="AmPmKbn"
                    :list="[
                        {code: '0', name: 'AM', label: 'AM'},
                        {code: '1', name: 'PM', label: 'PM'},
                    ]"
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">税区分</label>
                <VueSelect
                    id="TaxKbn"
                    :vmodel=viewModel
                    bind="TaxKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 20 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配達区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="DeliveryKbn"
                    :vmodel=viewModel
                    bind="DeliveryKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 31 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-2">
                <VueOptions
                    title="連絡"
                    customLabelStyle="width: 60px; text-align: center;"
                    id="RenrakuKbn"
                    :vmodel=viewModel
                    bind="RenrakuKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 30 }"
                    :withCode=false
                />
            </div>
            <div class="col-md-4">
                <label class="text-center">担当者</label>
                <PopupSelect
                    id="TantoSelect"
                    ref="PopupSelect_Tanto"
                    :vmodel=viewModel
                    bind="TantoCd"
                    buddy="TantoNm"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: viewModel.BushoCd }"
                    title="担当者一覧"
                    labelCd="担当者CD"
                    labelCdNm="担当者名"
                    :showColumns='[
                        { title: "担当者名カナ", dataIndx: "担当者名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150 },
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                />
            </div>
            <div class="col-md-4">
                <label>営業担当者</label>
                <PopupSelect
                    id="EigyoTantoSelect"
                    ref="PopupSelect_EigyoTanto"
                    :vmodel=viewModel
                    bind="EigyoTantoCd"
                    buddy="EigyoTantoNm"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: viewModel.BushoCd }"
                    title="営業担当者一覧"
                    labelCd="担当者CD"
                    labelCdNm="担当者名"
                    :showColumns='[
                        { title: "担当者名カナ", dataIndx: "担当者名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150 },
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                />
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
            <div class="col-md-1">
                <label>製造用特記</label>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" style="max-height: unset;" :value=viewModel.NoteSeizo rows=3 />
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">事務用特記</label>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" style="max-height: unset;" :value=viewModel.NoteJimu rows=3 />
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
}
div[class^="col-md"] > label {
    max-width: unset;
    line-height: 16px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
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
                OrderNo: null,
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                DeliveryTime: null,
                CustomerCd: null,
                CustomerNm: null,
                TelNo1: null,
                TelNo2: null,
                FaxNo: null,
                OrderDate: null,
                OrderTime: null,
                Address: null,
                DeliveryPlace: null,
                AreaCd: null,
                AreaNm: null,
                TiikiKbn: null,
                TiikiNm: null,
                DeliveryKbn: null,
                DeliveryKbnNm: null,
                TaxKbn: null,
                AmPmKbn: null,
                TaxNm: null,
                TantoCd: null,
                TantoNm: null,
                EigyoTantoCd: null,
                EigyoTantoNm: null,
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
                height: 200,
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
        onCustomerChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            vue.CustomerChanged(info, isValid);
        },
        CustomerChanged: function(info, isValid, noSearch) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            console.log("ShidashiCustomerChanged", JSON.stringify(info));

            if (!(!!info["部署CD"] && (!!info["受注Ｎｏ"] || !!info["得意先ＣＤ"]))) {
                return;
            }

            axios.post(
                    "/Utilities/GetShidashiCustomer",
                    {
                        bushoCd: info["部署CD"],
                        orderNo: info["受注Ｎｏ"],
                        customerCd: info["得意先ＣＤ"],
                    }
                )
                .then(response => {
                    if (!response.data || response.data.length != 1) return;

                    var res = response.data[0];

                    vue.viewModel.OrderNo = res["受注Ｎｏ"];
                    vue.viewModel.BushoCd = res["部署ＣＤ"];
                    vue.viewModel.BushoNm = res["部署名"];
                    vue.viewModel.DeliveryDate = res["配達日付"];
                    vue.viewModel.DeliveryTime = res["配達時間"];
                    vue.viewModel.CustomerCd = res["得意先ＣＤ"];
                    vue.viewModel.CustomerNm = res["得意先名"];
                    vue.viewModel.TelNo1 = res["電話番号１"];
                    vue.viewModel.TelNo2 = res["電話番号２"];
                    vue.viewModel.FaxNo = res["ＦＡＸ１"];
                    vue.viewModel.OrderDate = res["注文日付"];
                    vue.viewModel.Address = res["住所"];
                    vue.viewModel.DeliveryPlace = res["配達先"];
                    vue.viewModel.AreaCd = res["エリアＣＤ"];
                    vue.viewModel.AreaNm = res["エリア名称"];
                    vue.viewModel.TiikiKbn = res["地域区分"];
                    vue.viewModel.TiikiNm = res["地区名称"];
                    vue.viewModel.DeliveryKbn = res["配達区分"];
                    vue.viewModel.DeliveryKbnNm = res["配達名称"];
                    vue.viewModel.TaxKbn = res["税区分"];
                    vue.viewModel.TaxNm = res["税名称"];
                    vue.viewModel.TantoCd = res["担当者ＣＤ"];
                    vue.viewModel.TantoNm = res["担当者名"];
                    vue.viewModel.EigyoTantoCd = res["営業担当者ＣＤ"];
                    vue.viewModel.EigyoTantoNm = res["営業担当者名"];

                    //条件変更ハンドラ
                    vue.conditionChanged(noSearch);
                })
                .catch(error => {
                    console.log(error);
                });
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
    }
}
</script>
