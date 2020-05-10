<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>配達日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日(dd)"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    customStyle="width: 165px;"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">時間</label>
                <DatePickerWrapper
                    id="DeliveryTime"
                    ref="DatePicker_DeliveryTime"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryTime"
                    :editable=true
                    customStyle="width: 85px;"
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">持出時間</label>
                <DatePickerWrapper
                    id="TakeoutTime"
                    ref="DatePicker_TakeoutTime"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TakeoutTime"
                    :editable=true
                    customStyle="width: 85px;"
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
                    id="OrderNo"
                    ref="PopupSelect_OrderNo"
                    :vmodel=viewModel
                    bind="OrderNo"
                    dataUrl="/DAI08010/GetOrderNoList"
                    :params="{ TargetDate: searchParams.DeliveryDate, CustomerCd: searchParams.CustomerCd }"
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
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=Order.注文日付 readonly tabindex="-1">
                <label class="text-center">時間</label>
                <input class="form-control p-0 text-center" style="width: 80px;" type="text" :value=Order.注文時間 readonly tabindex="-1">
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
                    :vmodel=CustomerInfo
                    bind="得意先ＣＤ"
                    buddy="得意先名"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: viewModel.CustomerCd, UserBushoCd: getLoginInfo().bushoCd }"
                    title="顧客一覧"
                    labelCd="顧客CD"
                    labelCdNm="顧客名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
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
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-2">
                <label class="w-50 text-center">売掛現金区分</label>
                <VueSelect
                    id="UriGenKbn"
                    :vmodel=CustomerInfo
                    bind="売掛現金区分"
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
                <input type="text" class="form-control" :value="CustomerInfo.得意先名カナ">
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL1</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=CustomerInfo.電話番号１>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL2</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=CustomerInfo.電話番号２>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">Fax</label>
                <input class="form-control p-2" style="width: 125px;" type="text" :value=CustomerInfo.ＦＡＸ１>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>郵便番号</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-2" style="width: 90px;" type="text" :value=CustomerInfo.郵便番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" :value=CustomerInfo.住所１>
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">配達先</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" :value=CustomerInfo.お届け先住所１>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" :value=CustomerInfo.住所２>
            </div>
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" :value=CustomerInfo.お届け先住所２>
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
                    :vmodel=Order
                    bind="エリアＣＤ"
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ BushoCd: viewModel.BushoCd }"
                    :dataListReset=true
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
                    :vmodel=Order
                    bind="地域区分"
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
                    :vmodel=Order
                    bind="ＡＭＰＭ区分"
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
                    :vmodel=Order
                    bind="税区分"
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
                    :vmodel=Order
                    bind="配達区分"
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
                    :vmodel=Order
                    bind="連絡区分"
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
                    :vmodel=CustomerInfo
                    bind="登録担当者ＣＤ"
                    dataUrl="/Utilities/GetTantoList"
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
                    :vmodel=CustomerInfo
                    bind="営業担当者ＣＤ"
                    dataUrl="/Utilities/GetTantoList"
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
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
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
                <textarea class="form-control" style="max-height: unset;" :value=Order.製造用特記 rows=3 />
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">事務用特記</label>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" style="max-height: unset;" :value=Order.事務用特記 rows=3 />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 offset-md-5 update-info">
                <label class="label-blue">修正者</label>
                <label class="label-blue" style="width: 100px;">{{Order.修正者名}}</label>
                <label class="label-blue">修正日時</label>
                <label class="label-blue" style="width: 100px;">{{Order.修正日}}</label>
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
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                CustomerCd: this.CustomerInfo.得意先ＣＤ,
                DeliveryDate: moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD"),
            };
        }
    },
    watch: {
        "CustomerInfo.得意先ＣＤ": {
            handler: function(newVal) {
                var vue = this;
                var disabled = !(!!vue.CustomerInfo.得意先ＣＤ && vue.$refs.PopupSelect_Customer.isValid);
                vue.footerButtons.find(v => v.id == "DAI08010Grid1_Save").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理->仕出注文入力",
            noViewModel: true,
            viewModel: {
                OrderNo: null,
                BushoCd: null,
                DeliveryDate: null,
                DeliveryTime: null,
                TakeoutTime: null,
            },
            CustomerInfo: {
                得意先ＣＤ: null,
                得意先名: null,
                得意先名カナ: null,
                得意先名略称: null,
                郵便番号: null,
                住所１: null,
                住所２: null,
                電話番号１: null,
                ＦＡＸ１: null,
                電話番号２: null,
                ＦＡＸ２: null,
                お届け先郵便番号: null,
                お届け先住所１: null,
                お届け先住所２: null,
                お届け先電話番号１: null,
                お届け先ＦＡＸ１: null,
                お届け先電話番号２: null,
                お届け先ＦＡＸ２: null,
                部署CD: null,
                売掛現金区分: null,
                電話確認区分: null,
                締区分: null,
                締日１: null,
                締日２: null,
                支払サイト: null,
                支払日: null,
                集金区分: null,
                請求先ＣＤ: null,
                支払方法１: null,
                支払方法２: null,
                集金手数料: null,
                金融機関CD: null,
                金融機関支店CD: null,
                記号番号: null,
                口座種別: null,
                口座番号: null,
                口座名義人: null,
                チケット枚数: null,
                サービスチケット枚数: null,
                営業担当者ＣＤ: null,
                獲得営業者ＣＤ: null,
                登録担当者ＣＤ: null,
                受注得意先ＣＤ: null,
                配送ｸﾞﾙｰﾌﾟＣＤ: null,
                受注方法: null,
                電話確認時間_時: null,
                電話確認時間_分: null,
                味噌汁区分: null,
                ふりかけ区分: null,
                納品書発行区分: null,
                空き容器回収区分: null,
                既定製造パターン: null,
                請求書敬称: null,
                請求書区分別頁: null,
                請求内訳区分: null,
                備考１: null,
                備考２: null,
                備考３: null,
                ＷＥＢ表示区分: null,
                取扱区分: null,
                ＩＤ: null,
                パスワード: null,
                状態区分: null,
                承認日: null,
                承認者ＣＤ: null,
                状態理由区分: null,
                受注顧客区分: null,
                休日設定: null,
                新規登録日: null,
                税区分: null,
                税処理: null,
                祝日配送区分: null,
                誕生日１: null,
                誕生日２: null,
                失客日: null,
                修正担当者ＣＤ: null,
                修正日: null,
            },
            Order: {
                注文日付: null,
                注文時間: null,
                エリアＣＤ: null,
                地域区分: null,
                ＡＭＰＭ区分: "0",
                税区分: "0",
                配達区分: "0",
                連絡区分: "0",
                製造用特記: null,
                事務用特記: null,
                修正者名: null,
                修正日: null,
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

        if (!!vue.params && !!vue.params.CustomerInfo) {
            vue.CustomerInfo = vue.params.CustomerInfo;
            vue.CustomerChanged(vue.CustomerInfo);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI08010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "伝票削除", id: "DAI08010Grid1_DeleteOrder", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteOrder();
                    }
                },
                { visible: "true", value: "行削除", id: "DAI08010Grid1_DeleteRow", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "得意先単価<br>マスタメンテ", id: "DAI08010Grid1_showProductMaint", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showProductMaint();
                    }
                },
                { visible: "true", value: "登録", id: "DAI08010Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveOrder();
                    }
                },
                { visible: "true", value: "受注問合せ", id: "DAI08010Grid1_showOrderList", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.showOrderList();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //TODO:
            // vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.DeliveryDate = moment("20190906").format("YYYY年MM月DD日");
            vue.viewModel.DeliveryTime = moment().format("HH時mm分");

            //TODO:
            vue.viewModel.BushoCd = "985";
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //vue.getProductList(code);
        },
        onDeliveryDateChanged: function(code, entities) {
            var vue = this;

            // 条件変更ハンドラ
            vue.conditionChanged();
        },
        onTakeoutTimeChanged: function(code, entities) {
            var vue = this;
        },
        onShidashiOrderNoChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;

            // 条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;
            console.log("8010 customer changed")

            if (!!entity && !_.isEmpty(entity)) {
                // 条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.searchParams.BushoCd || !vue.searchParams.DeliveryDate || !vue.searchParams.CustomerCd) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.showLoading();

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //商品リスト検索
            axios.post("/DAI08010/GetProductList", params)
                .then(res => {
                    grid.hideLoading();
                    vue.ProductList = res.data;
                    grid.searchData(vue.searchParams);
                })
                .catch(err => {
                    grid.hideLoading();
                    console.log("/DAI08010/GetProductList Error", err)
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });
        },
        getProductList: function() {
            var vue = this;
            return vue.ProductList;
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList || !dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.CustomerInfo = res[0].CustomerInfo;
            vue.viewModel.Order = res[0].Order;

            var list = res[0].MeisaiList;

            return list;
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
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
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
