<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                    :disabled=bushoDisabled
                />
            </div>
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
                <input class="form-control p-0 text-center" style="width: 80px;" type="text" v-model=viewModel.DeliveryTime readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <label>グループ</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="GroupCustomerCd"
                    :vmodel=viewModel
                    bind="GroupCustomerCd"
                    uri="/Utilities/GetGroupCustomerList"
                    :params="{ CustomerCd: viewModel.CustomerCd }"
                    customStyle="{width: 200px}"
                    :withCode=true
                    :hasNull=true
                    :ParamsChangedCheckFunc=GroupCustomerParamsChangedCheckFunc
                    :onChangedFunc=onGroupCustomerChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    :buddies='{ CustomerNm: "CdNm", CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/DAI01030/GetCustomerAndCourseList"
                    :params="{ targetDate: FormattedDeliveryDate, KeyWord: viewModel.CustomerCd, bushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
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
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
                <label class="label-blue text-center">TEL</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.TelNo readonly tabindex="-1">
                <label class="ml-1 label-blue">{{viewModel.PaymentNm}}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-6">
                <input class="form-control label-blue" style="width: 350px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 200px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
            <!-- <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-4">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 300px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <label style="max-width: unset; width: 100%; text-align: center;">コース</label>
            </div>
            <div class="col-md-4">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 300px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
            </div> -->
        </div>
        <div class="row">
            <div class="col-md-1">
                <span class="badge w-75" :class='viewModel.IsEdit ? "badge-warning" : "badge-primary"'>{{viewModel.IsEdit ? "修正" : "登録"}}</span>
            </div>
            <div class="col-md-1">
                <span class="badge w-75" :class='viewModel.IsDeliveried ? "badge-danger" : "badge-primary"'>{{viewModel.IsDeliveried ? "配達済" : "未配達"}}</span>
            </div>
            <div class="col-md-7">
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <button
                    id="btn_prev"
                    type="button"
                    class="btn-light w-100"
                    :disabled=!hasPrev
                    @click=prevOrder
                >
                    <i class="fa fa-caret-left fa-lg"></i>戻る
                </button>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
                <button
                    id="btn_next"
                    type="button"
                    class="btn-light w-100"
                    :disabled=!hasNext
                    @click=nextOrder
                >
                    <i class="fa fa-caret-right fa-lg"></i>先へ
                </button>
            </div>
            <div class="col-md-1 d-flex justify-content-center">
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
            :id='"DAI01030Grid1" + (!!params ? _uid : "")'
            ref="DAI01030Grid1"
            dataUrl="/DAI01030/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :checkChangedCancelFunc=checkChangedCancelFunc
            :options=this.grid1Options
            :onCompleteFunc=onCompleteFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onSelectChangeFunc=onSelectChangeFunc
            :setMoveNextCell=setMoveNextCell
            :autoToolTipDisabled=true
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
            classes="mt-1 mb-1 mr-3 ml-3"
        />
        <div class="row">
            <div class="col-md-1 d-block">
                <label style="width: auto; max-width: unset;">備考(社内)</label>
            </div>
            <div class="col-md-5 d-block">
                <!-- <input v-for="(bikou, index) in viewModel.BikouForControl"
                    v-bind:key=index
                    :id='"BikouForControl" + (index + 1)'
                    v-model=viewModel.BikouForControl[index]
                    class="bikou w-100"
                /> -->
                <textarea
                    class="form-control"
                    style="max-height: unset;"
                    rows=3
                    id="bikou-shanai"
                    v-model=viewModel.BikouForControl
                    type="text"
                    v-maxBytes="200"
                ></textarea>
            </div>
            <div class="col-md-1 d-block">
                <label style="width: 100%; max-width: unset; text-align: center;">備考(配送)</label>
            </div>
            <div class="col-md-5 d-block">
                <!-- <input v-for="(bikou, index) in viewModel.BikouForDelivery"
                    v-bind:key=index
                    :id='"BikouForDelivery" + (index + 1)'
                    v-model=viewModel.BikouForDelivery[index]
                    class="bikou w-100"
                /> -->
                <textarea
                    class="form-control"
                    style="max-height: unset;"
                    rows=3
                    id="bikou-haiso"
                    v-model=viewModel.BikouForDelivery
                    type="text"
                    v-maxBytes="200"
                ></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 d-block">
            </div>
            <div class="col-md-5 d-block">
            </div>
            <div class="col-md-1 d-block">
                <label style="width: 100%; max-width: unset; text-align: center;">備考(通知)</label>
            </div>
            <div class="col-md-5 d-block">
                <textarea
                    class="form-control"
                    style="max-height: unset;"
                    rows=3
                    id="bikou-tuuchi"
                    v-model=viewModel.BikouForNotification
                    type="text"
                    v-maxBytes="200"
                ></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
            </div>
            <div class="col-md-2 update-info">
                <label class="label-blue">修正者</label>
                <label class="label-blue" style="width: auto;">{{viewModel.LastEditor}}</label>
            </div>
            <div class="col-md-3 update-info">
                <label class="label-blue">修正日時</label>
                <label class="label-blue" style="width: auto;">{{viewModel.LastEditDate}}</label>
            </div>
        </div>
    </form>
</template>

<style scoped>
label {
    max-width: 60px;
}
.badge {
    font-size: 15px;
}
.update-info .label-blue {
    max-width: unset !important;
    margin-right: 4px;
}
.btn-light:not(.footer-button-visible-true):disabled {
    color: #aaaaaa;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
</style>
<style>
[pgid=DAI01030] .CustomerSelect .select-name {
    color: royalblue;
}
[pgid=DAI01030] .pq_grid svg.pq-grid-overlay {
    display: block;
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
        bushoDisabled: function() {

        },
        hasPrev: function() {
            var vue = this;
            return !!vue.TodayOrders.length && _.last(vue.TodayOrders) != vue.CurrentOrder;
        },
        hasNext: function() {
            var vue = this;
            //TODO西山　CurrentOrder nullの時ある
            return !!vue.TodayOrders.length && _.first(vue.TodayOrders) != vue.CurrentOrder;
        },
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                CustomerCd: this.viewModel.CustomerCd,
                DeliveryDate: moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD"),
            };
        }
    },
    watch: {
        "viewModel.IsShowAll": {
            handler: function(newVal) {
                console.log("viewModel.IsShowAll:" + newVal);
                var vue = this;
                var grid = vue.DAI01030Grid1;
                if (!grid) return;

                grid.filter({
                    oper: "replace",
                    rules: newVal ? [] : [{ dataIndx: "全表示", condition: "notequal", value: "1" }],
                });
            },
        },
        "viewModel.IsEdit": {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI01030Grid1_DeleteOrder").disabled = !newVal;
            },
        },
        "viewModel.CustomerCd": {
            handler: function(newVal) {
                var vue = this;
                var disabled = !(!!vue.viewModel.CustomerCd && vue.$refs.PopupSelect_Customer.isValid);
                vue.footerButtons.find(v => v.id == "DAI01030Grid1_showCustomerMaint").disabled = disabled;
                vue.footerButtons.find(v => v.id == "DAI01030Grid1_showProductMaint").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
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
                IsEdit: false,
                IsDeliveried: false,
                LastEditor: null,
                LastEditDate: null,
                BikouForControl: null,
                BikouForDelivery: null,
                BikouForNotification: null,
                ChumonBikou1: null,
                ChumonBikou2: null,
                ChumonBikou3: null,
                ChumonBikou4: null,
                ChumonBikou5: null,
                IsShowAll: null,
                GroupCustomerCd: null,
            },
            TodayOrders: [],
            CurrentOrder: null,
            DAI01030Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                toolbar: {
                    cls: "DAI01030_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRow(this, event);
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRow(this, event);
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: true },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 350,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
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
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: () => vue.getProductList(),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm", "単価": "単価", "商品区分": "商品区分", },
                            onSelect: rowData => {
                                console.log("onSelect", rowData);
                                rowData["現金金額"] = rowData["単価"] * rowData["現金個数"];
                                rowData["掛売金額"] = rowData["単価"] * rowData["掛売個数"];
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        tooltip: true,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分", dataType: "integer",
                        hidden: true,
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
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, data.viewModel, _.omit(vue.params, ["Parent"]), _.omit(vue.query, ["Parent"]));
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01030Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "伝票削除", id: "DAI01030Grid1_DeleteOrder", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteOrder();
                    }
                },
                { visible: "true", value: "行削除", id: "DAI01030Grid1_DeleteRow", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "コース表示", id: "DAI01030Grid1_ShowCourse", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.showCourse();
                    }
                },
                { visible: "true", value: "残高表示", id: "DAI01030Grid1_ShowBalance", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.showBalance();
                    }
                },
                { visible: "true", value: "得意先<br>マスタメンテ", id: "DAI01030Grid1_showCustomerMaint", disabled: true, shortcut: "F7",
                    onClick: function () {
                        vue.showCustomerMaint();
                    }
                },
                { visible: "true", value: "得意先単価<br>マスタメンテ", id: "DAI01030Grid1_showProductMaint", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showProductMaint();
                    }
                },
                { visible: "true", value: "登録", id: "DAI01030Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveOrder();
                    }
                },
            );

            vue.$root.$on("logOn", () => vue.getTodayOrder());
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI01030Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01030Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                }
            );
            vue.$watch(
                "$refs.DAI01030Grid1.isSelection",
                isSelection => {
                    vue.footerButtons.find(v => v.id == "DAI01030Grid1_DeleteRow").disabled = !isSelection;
                }
            );

            vue.viewModel.IsShowAll = false;

            console.log("check params", vue.params)
            //queryがある場合は初期値設定しない
            if (!vue.params && !_.isEmpty(_.omit(vue.query, "userId"))) {
                //本日注文履歴取得
                vue.getTodayOrder(() => {
                    vue.CurrentOrder = vue.TodayOrders.find(v => {
                        return v.部署ＣＤ == vue.query.BushoCd
                            && moment(v.配送日).format("YYYY年MM月DD日") == vue.query.DeliveryDate
                            && v.修正時間 == vue.query.DeliveryTime
                            && v.得意先CD == vue.query.CustomerCd
                            ;
                    })
                    //検索
                    vue.conditionChanged(true);
                });

                return;
            }

            if (!!vue.params && vue.params.CustomerCd) {
                vue.footerButtons.find(v => v.id == "DAI01030Grid1_showCustomerMaint").disabled = false;
                vue.footerButtons.find(v => v.id == "DAI01030Grid1_showProductMaint").disabled = false;
            }

            //TODO:
            // vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.DeliveryDate = moment("20190904").format("YYYY年MM月DD日");
            vue.viewModel.DeliveryTime = moment().format("HH:mm:ss");

            //本日注文履歴取得
            vue.getTodayOrder();
        },
        deactivatedFunc: function(vue) {
            var vue = this;
            vue.$root.$emit("DAI01030_Deactivated");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            vue.$root.$emit("DAI01030_BushoChanged", code);
        },
        onDeliveryDateChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.CustomerChanged(entity, comp.isValid);
            }
        },
        CustomerChanged: function(info, isValid) {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            if (info["部署CD"] && vue.viewModel.BushoCd != info["部署CD"]) {
                vue.viewModel.BushoCd = info["部署CD"];
            }

            vue.viewModel.TelNo = info["電話番号１"];
            vue.viewModel.PaymentCd = info["売掛現金区分"];
            vue.viewModel.PaymentNm = ["現金", "掛売"][vue.viewModel.PaymentCd] || (isValid ? "チケット" : "");
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
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.CustomerCd) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.showLoading();

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //商品リスト検索
            axios.post("/DAI01030/GetProductList", params)
                .then(res => {
                    grid.hideLoading();
                    vue.ProductList = res.data;
                    vue.DAI01030Grid1.searchData(vue.searchParams);
                })
                .catch(err => {
                    grid.hideLoading();
                    console.log("/DAI01030/GetProductList Error", err)
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

            console.log("1030 input", input);
            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
            return !rowData["商品ＣＤ"];
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
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //配送日
            var deliverDate = _.max(res.filter(v => v.注文区分 == 0).map(v => v.配送日));
            vue.viewModel.IsEdit = !!deliverDate ? moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").isSame(deliverDate) : false;

            //注文時間
            var deliverTime = _.max(res.map(v => v.注文時間));
            vue.viewModel.DeliveryTime = deliverTime || moment().format("HH:mm:ss");

            if (vue.viewModel.IsEdit) {
                var params = _.cloneDeep(vue.searchParams);
                params.noCache = true;

                //最終修正日/担当者
                axios.post("/DAI01030/GetLastEdit", params)
                    .then(res => {
                        vue.viewModel.LastEditor = res.data.修正担当者名;
                        vue.viewModel.LastEditDate = !!res.data.修正日 ? moment(res.data.修正日).format("YYYY/MM/DD HH:mm:ss") : null;
                    })
                    .catch(err => {
                        console.log("/DAI01030/GetLastEdit Error", err);
                    })
                    ;
                //配達済/未配達
                axios.post("/DAI01030/IsDeliveried", params)
                    .then(res => {
                        vue.viewModel.IsDeliveried = res.data.IsDeliveried;
                    })
                    .catch(err => {
                        console.log("/DAI01030/IsDeliveried Error", err);
                    })
                    ;

            } else {
                vue.viewModel.IsDeliveried = false;
                vue.viewModel.LastEditor = "";
                vue.viewModel.LastEditDate = "";
            }

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //備考
            axios.post("/DAI01030/GetBikou", params)
                .then(res => {
                    var bikou;
                    bikou = res.data
                        .reduce((a, c) => a = _.mergeWith(a, c, (o, s) => {
                            if (s == "\r\n") s = "";
                            // return o.includes(s) ? o : (o + "," + s)
                            return !!s ? (o.includes(s) ? o : (!!o ? (o + "\r\n" + s) : s)) : ""
                        }));
                    vue.viewModel.BikouForControl = bikou.備考社内;
                    vue.viewModel.BikouForDelivery = bikou.備考配送;
                    vue.viewModel.BikouForNotification = bikou.備考通知;
                    vue.viewModel.ChumonBikou1 = bikou.CD備考１;
                    vue.viewModel.ChumonBikou2 = bikou.CD備考２;
                    vue.viewModel.ChumonBikou3 = bikou.CD備考３;
                    vue.viewModel.ChumonBikou4 = bikou.CD備考４;
                    vue.viewModel.ChumonBikou5 = bikou.CD備考５;
                    // console.log("GetBikou", res.data, bikou, vue.viewModel.BikouForControl);
                })
                .catch(err => {
                    console.log("/DAI01030/GetBikou Error", err);
                })
                ;

            return res;
        },
        onSelectChangeFunc: function(grid, ui) {
        },
        checkChangedCancelFunc: function(grid) {
            var vue = this;
            var grid = vue.DAI01030Grid1;
            var postData = grid.options.dataModel.postData;

            console.log("checkChangedCancelFunc");

            if (vue.viewModel.CustomerCd != postData.CustomerCd) {
                var ps = vue.$refs.PopupSelect_Customer;
                var info = ps.dataList.find(v => v.Cd == postData.CustomerCd);
                ps.selectValue = info["得意先CD"]
                ps.selectName = info["得意先名"];
                ps.selectRow = info;
                vue.CustomerChanged(info, true, true);

                ps.AutoCompleteFocusSkip = true;
            }
            if (vue.viewModel.DeliveryDate != postData.DeliveryDate) {
                vue.viewModel.DeliveryDate = moment(postData.DeliveryDate, "YYYYMMDD").format("YYYY年MM月DD日");
            }
        },
        setMoveNextCell: function(grid, ui, reverse) {
            if (grid.getEditCell().$editor) {
                grid.saveEditCell();
            }

            if (ui.dataIndx == "商品ＣＤ") {
                grid.setSelection({
                    rowIndx: ui.rowIndx,
                    colIndx: _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos,
                });
            } else if (ui.dataIndx.includes("個数")) {
                grid.setSelection({
                    rowIndx: ui.rowIndx + 1,
                    colIndx: grid.columns["商品ＣＤ"].leftPos,
                });
            } else {
                return true;
            }

            return false;
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.targetDate && newVal.bushoCd;
            return ret;
        },
        GroupCustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.CustomerCd;
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

            vue.viewModel.CustomerCd = vm.GroupCustomerCd;
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v));

            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["得意先名", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if ((input == comp.selectValue && comp.isUnique)) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.得意先CD.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.得意先CD + " : " + "【" + v.部署名 + "】" + v.得意先名;
                    ret.value = v.得意先CD;
                    ret.text = v.得意先名;
                    return ret;
                })
                ;

            return list;
        },
        saveOrder: function() {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            var hasError = checkError(grid);

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var checkRequire = grid => grid.pdata.map(r => [r.商品ＣＤ]).every(r => r.every(v => !!v) || r.every(v => !v));

            var require = checkRequire(grid);

            if(!require){
                $.dialogErr({
                    title: "未入力項目",
                    contents: "未入力項目があるため、登録できません。",
                });
                return;
            }

            var SaveList = _.cloneDeep(grid.getPlainPData().filter(v => !!v.商品ＣＤ));

            //注文データの型に整形
            SaveList.forEach((v, i) => {
                v.注文区分 = 0;
                v.注文日付 = v.注文日付 || moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY-MM-DD");
                v.注文時間 = v.注文時間 || vue.viewModel.DeliveryTime;
                v.部署ＣＤ = vue.viewModel.BushoCd;
                v.得意先ＣＤ = vue.viewModel.CustomerCd;
                v.配送日 = v.配送日 || moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY-MM-DD");
                v.入力区分 = 0;
                v.備考１ = vue.viewModel.ChumonBikou1;
                v.備考２ = vue.viewModel.ChumonBikou2;
                v.備考３ = vue.viewModel.ChumonBikou3;
                v.備考４ = vue.viewModel.ChumonBikou4;
                v.備考５ = vue.viewModel.ChumonBikou5;
                v.予備金額１ = v.単価;
                v.予備金額２ = 0;
                v.予備ＣＤ１ = 0;
                v.予備ＣＤ２ = 0;
                v.修正担当者ＣＤ = vue.getLoginInfo().uid;
                //TODO
                v.特記_社内用 = vue.viewModel.BikouForControl;
                v.特記_配送用 = vue.viewModel.BikouForDelivery;
                v.特記_通知用 = vue.viewModel.BikouForNotification;

                delete v.商品名;
                delete v.単価;
                delete v.予定数;
                delete v.現金;
                delete v.掛売;
                delete v.全表示;
                delete v.sortIndx;
            });

            var DeleteList = grid.getChanges().deleteList
                .map(v => {
                    var ret = _.cloneDeep(v.InitialValue);
                    ret.部署ＣＤ = vue.viewModel.BushoCd;

                    return ret;
                });

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI01030/Save",
                    params: {
                        SaveList: SaveList,
                        DeleteList: DeleteList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "確認 " + vue.viewModel.CustomerNm,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            console.log("res", res);

                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.current);
                            } else {
                                // grid.setLocalData(res.current);
                                grid.refreshDataAndView();
                                vue.viewModel.IsEdit = true;
                            }

                            return false;
                        },
                    },
                }
            );
        },
        deleteOrder: function() {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            var DeleteList = _.cloneDeep(grid.getPlainPData().filter(v => !!v.商品ＣＤ));

            //注文データの型に整形
            DeleteList.forEach((v, i) => {
                v.注文区分 = 0;
                v.注文日付 = v.注文日付 || moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY-MM-DD");
                v.注文時間 = v.注文時間 || vue.viewModel.DeliveryTime;
                v.部署ＣＤ = vue.viewModel.BushoCd;
                v.得意先ＣＤ = vue.viewModel.CustomerCd;
                v.配送日 = v.配送日 || moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY-MM-DD");
                v.明細行Ｎｏ = (i + 1);
                v.入力区分 = 0;
                v.修正担当者ＣＤ = vue.getLoginInfo().uid;

                delete v.商品名;
                delete v.単価;
                delete v.予定数;
                delete v.現金;
                delete v.掛売;
                delete v.全表示;
                delete v.sortIndx;
            });

            //削除実行
            grid.saveData(
                {
                    uri: "/DAI01030/Delete",
                    params: {
                        DeleteList: DeleteList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "注文データ削除確認",
                        message: "注文データを削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            console.log("res", res);

                            if (!!res.edited && !!res.edited.length) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.edited);
                            } else {
                                grid.clearData();
                                vue.viewModel.CustomerCd = null;
                                vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
                                vue.viewModel.DeliveryTime = moment().format("HH:mm:ss");
                                vue.viewModel.IsEdit = false;
                                vue.viewModel.IsDeliveried = false;
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            }

                            return false;
                        },
                    },
                }
            );
        },
        addRow: function(grid, event) {
            var vue = this;
        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI01030Grid1;

            if(!grid.Selection()._areas.length){
                return;
            }

            var rowIndx = grid.Selection()._areas[0].r1;
            grid.deleteRow({ rowIndx: rowIndx });
        },
        showCourse: function() {
            var vue = this;

            PageDialog.show({
                pgId: "DAI01031",
                params: {
                    BushoCd: vue.viewModel.BushoCd,
                    TargetDate: vue.searchParams.DeliveryDate,
                    CourseCd: vue.viewModel.CourseCd,
                    CourseNm: vue.viewModel.CourseNm,
                    TantoCd: vue.viewModel.TantoCd,
                    TantoNm: vue.viewModel.TantoNm,
                },
                isModal: false,
                isChild: true,
                width: 500,
                height: 650,
            });
        },
        showBalance: function() {
            var vue = this;

            axios.post(
                "/DAI01030/GetZandaka",
                {BushoCd: vue.viewModel.BushoCd, CustomerCd: vue.viewModel.CustomerCd, noCache: true}
            )
            .then(res => {
                var contents = "前回残高: "
                             + $("<label>").css("width", "75").css("text-align", "right").text(Number(res.data.Zandaka).toLocaleString("jp")).prop("outerHTML")
                             + "<br>"
                             + "締後売上: "
                             + $("<label>").css("width", "75").css("text-align", "right").text(Number(res.data.Uriage).toLocaleString("jp")).prop("outerHTML")
                             ;

                $.dialogInfo({
                    title: "残高表示",
                    contents: contents,
                    minWidth: 200,
                });
            })
            .catch(err => {
                console.log(err)
            });
        },
        getTodayOrder: function(callback) {
            var vue = this;

            if (!vue.getLoginInfo().isLogOn) return;

            //今日の担当注文取得
            axios.post("/DAI01030/GetTodayOrder", { TantoCd: vue.getLoginInfo().uid })
                .then(res => {
                    vue.TodayOrders = res.data;

                    if (callback) callback();
                })
                .catch(err => {
                    console.log("/DAI01030/GetTodayOrder Error", err);
                })
                ;
        },
        prevOrder: function() {
            var vue = this;

            if (!vue.CurrentOrder) {
                vue.loadOrder(0);
                return;
            }

            var idx = _.indexOf(vue.TodayOrders, vue.CurrentOrder) + 1;
            idx = idx > _.findLastIndex(vue.TodayOrders) ? _.findLastIndex(vue.TodayOrders) : idx;
            vue.loadOrder(idx);
        },
        nextOrder: function() {
            var vue = this;

            if (!vue.CurrentOrder) {
                vue.loadOrder(0);
                return;
            }

            //TODO西山 index確認中
            var idx = _.indexOf(vue.TodayOrders, vue.CurrentOrder) - 1;
            idx = idx < 0 ? 0 : idx;
            vue.loadOrder(idx);
        },
        loadOrder: function(idx) {
            var vue = this;

            vue.CurrentOrder = vue.TodayOrders[idx];

            vue.$router.push({
                path: vue.$route.path,
                query: {
                    userId: vue.$route.query.userId,
                    BushoCd: vue.CurrentOrder.部署ＣＤ,
                    DeliveryDate: moment(vue.CurrentOrder.配送日).format("YYYY年MM月DD日"),
                    DeliveryTime: vue.CurrentOrder.修正時間,
                    CustomerCd: vue.CurrentOrder.得意先CD,
                }
            });
        },
        showCustomerMaint: function() {
            var vue = this;

            var cd = vue.viewModel.CustomerCd;
            if (!cd) return;

            var params = {CustomerCd: cd, noCache: true};
            axios.post("/Utilities/GetCustomerListForMaint", params)
                .then(res => {
                    if (res.data.Data.length == 1) {
                        var params = _.cloneDeep(res.data.Data[0]);
                        params.IsNew = false;
                        params.Parent = vue;

                        //DAI04041を子画面表示
                        PageDialog.show({
                            pgId: "DAI04041",
                            params: params,
                            isModal: true,
                            isChild: true,
                            width: 1200,
                            height: 700,
                        });
                    }
                })
                .catch(err => {
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "得意先マスタの検索に失敗しました"
                    })
                })
        },
        updateCustomer: function() {
            var vue = this;
            var ps = vue.$refs.PopupSelect_Customer;

            ps.getDataList(null, () => {
                console.log("update customer", ps.selectRow);
                var info = ps.dataList.find(v => v.Cd == vue.viewModel.CustomerCd);
                vue.CustomerChanged(info);
            });
        },
        showProductMaint: function() {
            var vue = this;

            var cd = vue.viewModel.CustomerCd;
            if (!cd) return;

            var params = { 得意先ＣＤ: cd, 得意先名: vue.viewModel.CustomerNm };
            params.IsNew = false;
            params.Available = "1";
            params.TargetDate = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");
            params.Parent = vue;

            //DAI04051を子画面表示
            PageDialog.show({
                pgId: "DAI04051",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 880,
                height: 600,
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
        },
        updateProduct: function() {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //商品リスト検索
            axios.post("/DAI01030/GetProductList", params)
                .then(res => {
                    vue.ProductList = res.data;
                    grid.getData()
                        .filter(r => !r.InitialValue && !!r.商品ＣＤ)
                        .forEach(r => {
                            var pd = vue.ProductList.find(p => p.商品ＣＤ == r.商品ＣＤ);
                            if (!!pd) {
                                r.単価 = pd.単価;
                                r.現金金額 = r.単価 * 1 * r.現金個数 * 1;
                                r.掛売金額 = r.単価 * 1 * r.掛売個数 * 1;
                            }
                        });
                    grid.refreshView();
                })
                .catch(err => {
                    console.log("/DAI01030/GetProductList Error", err)
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });
        },
    }
}
</script>
