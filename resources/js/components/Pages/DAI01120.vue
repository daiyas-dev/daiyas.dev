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
            <div class="col-md-5">
                <label class="text-center mr-2" style="width: auto;">日付</label>
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label class="text-center ml-2 mr-2" style="width: auto;">～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, CourseKbnArray: viewModel.CourseKbnArray }'
                    :isPreload=true
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="SeikyuSelect"
                    ref="PopupSelect_Seikyu"
                    :vmodel=viewModel
                    bind="SeikyuCd"
                    buddy="SeikyuNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="請求先一覧"
                    labelCd="請求先CD"
                    labelCdNm="請求先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=false
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onSeikyuChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
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
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                        { title: "コースCD", dataIndx: "コースＣＤ", dataType: "string", align: "left", width: 100, maxWidth: 100, minWidth: 100, },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 200, maxWidth: 200, minWidth: 200, },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=false
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>受注得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="JuchuSelect"
                    ref="PopupSelect_Juchu"
                    :vmodel=viewModel
                    bind="JuchuCd"
                    buddy="JuchuNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="受注得意先一覧"
                    labelCd="受注得意先CD"
                    labelCdNm="受注得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=false
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :onAfterChangedFunc=onJuchuChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>商品</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="ProductSelect"
                    ref="PopupSelect_Product"
                    :vmodel=viewModel
                    bind="ProductCd"
                    buddy="ProductNm"
                    dataUrl="/DAI01120/GetProductList"
                    :params="{ BushoCd: viewModel.BushoCd }"
                    :isPreload=true
                    title="商品一覧"
                    labelCd="商品CD"
                    labelCdNm="商品名"
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onProductChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ProductAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: auto;">商品区分</label>
            </div>
            <div class="col-md-5">
                <VueSelect
                    id="ProductKbn"
                    :vmodel=viewModel
                    bind="ProductKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 14 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :hasNull=true
                    :onChangedFunc=onProductKbnChanged
                />
                <label class="text-center ml-2 mr-2" style="width: auto;">売掛現金区分</label>
                <VueSelect
                    id="UriGenKbn"
                    :vmodel=viewModel
                    bind="UriGenKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 34 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :hasNull=true
                    :onChangedFunc=onUriGenKbnChanged
                />
                <label class="text-center ml-2 mr-2" style="width: auto;">食事区分</label>
                <VueSelect
                    id="MealKbn"
                    ref="MealKbn_Select"
                    :vmodel=viewModel
                    bind="MealKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 38 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :hasNull=true
                    :onChangedFunc=onMealKbnChanged
                />
            </div>
            <div class="col-md-2">
               <VueCheck
                    id="VueCheck_DaitaiOnly"
                    ref="VueCheck_DaitaiOnly"
                    :vmodel=viewModel
                    bind="IsDaitaiOnly"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: '代替品のみ出力'},
                        {code: '1', name: 'する', label: '代替品のみ出力'},
                    ]"
                    :onChangedFunc=onDaitaiOnlyChanged
                />
            </div>
        </div>

        <PqGridWrapper
            id="DAI01120Grid1"
            ref="DAI01120Grid1"
            dataUrl="/DAI01120/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI01120Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI01120Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI01120Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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

export default {
    mixins: [PageBaseMixin],
    name: "DAI01120",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                DateStart: moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYYMMDD"),
                DateEnd: moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: vue.viewModel.CourseCd,
                SeikyuCd: vue.viewModel.SeikyuCd,
                CustomerCd: vue.viewModel.CustomerCd,
                JuchuCd: vue.viewModel.JuchuCd,
                ProductCd: vue.viewModel.ProductCd,
                ProductKbn: vue.viewModel.ProductKbn,
                UriGenKbn: vue.viewModel.UriGenKbn,
                MealKbn: vue.viewModel.MealKbn,
                IsDaitaiOnly: vue.viewModel.MealKbn == "1",
            };
        },
        dateRange: function() {
            var vue = this;

            var start = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var end = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (!start.isValid() || !end.isValid()) return [];
            if (!start.isSameOrBefore(end)) return [];

            return _.range(0, end.diff(start, "days") + 1)
                    .map(v => moment(vue.viewModel.DateStart, "YYYY年MM月DD日").add("days", v).format("YYYYMMDD"));
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.BushoCd || !newVal.DateStart || !newVal.DateEnd;
                vue.footerButtons.find(v => v.id == "DAI01120Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 売上データ明細問合せ",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                CourseCd: null,
                CourseNm: null,
                SeikyuCd: null,
                SeikyuNm: null,
                CustomerCd: null,
                CustomerNm: null,
                JuchuCd: null,
                JuchuNm: null,
                ProductCd: null,
                ProductNm: null,
                ProductKbn: null,
                UriGenKbn: null,
                MealKbn: null,
                IsDaitaiOnly: "0",
            },
            DAI01120Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 45,
                rowHt: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45 },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "日付",
                        dataIndx: "日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            if (!!ui.Export && !ui.rowData.pq_grandsummary) {
                                return { text: moment(ui.rowData[ui.dataIndx]).format("YYYY/MM/DD") };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "CD",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                    },
                    {
                        title: "CD",
                        dataIndx: "得意先ＣＤ",
                        dataType: "integer",
                        width: 65, minWidth: 65, maxWidth: 65,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                    },
                    {
                        title: "CD",
                        dataIndx: "商品ＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "売現区分",
                        dataIndx: "売掛現金区分",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "合計" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "現金個数",
                        dataIndx: "現金個数",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "現金金額",
                        dataIndx: "現金金額",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "現金値引",
                        dataIndx: "現金値引",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "掛売個数",
                        dataIndx: "掛売個数",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "掛売金額",
                        dataIndx: "掛売金額",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "掛売値引",
                        dataIndx: "掛売値引",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "個数",
                        dataIndx: "個数",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, minWidth: 65, maxWidth: 65,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "単価",
                        dataIndx: "予備金額１",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, minWidth: 65, maxWidth: 65,
                    },
                    {
                        title: "値引",
                        dataIndx: "値引",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, minWidth: 65, maxWidth: 65,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額",
                        dataIndx: "金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "食事区分",
                        dataIndx: "食事区分",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "代替品",
                        dataIndx: "代替品",
                        dataType: "string",
                        hidden: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI01120Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "検索", id: "DAI01120Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01120Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01120Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01120Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01120Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01120Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01120Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI01120Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01120Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
        },
        onDateChanged: function() {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDateList",
                {TargetDateList: vue.dateRange },
            )
                .then(res => {
                    console.log(res);
                    vue.viewModel.CourseKbnArray = _.uniq(res.data.map(v => v.コース区分));

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
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSeikyuChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onJuchuChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onProductChanged: function(code, entity, comp) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onProductKbnChanged: function() {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onUriGenKbnChanged: function() {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onMealKbnChanged: function() {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onDaitaiOnlyChanged: function() {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01120Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            if (!!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                console.log("same condition", _.isEqual(grid.prevPostData, vue.searchParams));
                return;
            }

            grid.searchData(vue.searchParams, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01120Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.ProductCd) {
                rules.push({ dataIndx: "商品ＣＤ", condition: "equal", value: vue.viewModel.ProductCd });
            }
            if (!!vue.viewModel.ProductKbn) {
                rules.push({ dataIndx: "商品区分", condition: "equal", value: vue.viewModel.ProductKbn });
            }
            if (!!vue.viewModel.UriGenKbn) {
                rules.push({ dataIndx: "売掛現金区分", condition: "equal", value: vue.viewModel.UriGenKbn });
            }
            if (!!vue.viewModel.MealKbn) {
                rules.push({ dataIndx: "食事区分", condition: "equal", value: vue.viewModel.MealKbn });
            }
            if (vue.viewModel.IsDaitaiOnly == "1") {
                rules.push({ dataIndx: "代替品", condition: "equal", value: vue.viewModel.IsDaitaiOnly });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return ret;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI01120Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01120Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01120Grid1_Print").disabled = !res.length;

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                grid.setSelection({ rowIndx: 0, colIndx: 1 });
            }
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "担当者名"];

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
                    ret.label = v.Cd + " : " + v.CdNm + "【" + v.担当者名 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.replace(/-/g, "").includes(k.replace(/-/g, "")) : false)
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
        ProductAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

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
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01120Grid1;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                Parent: vue,
                ParentGrid: grid,
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: data.日付,
                CourseKbn: data.コース区分,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CustomerCd: data.得意先ＣＤ,
                CustomerIndex: data.行Ｎｏ,
                PaymentCd: data.得意先支払方法 == 5 ? 2 : data.得意先売掛現金区分,
                PaymentNm: data.得意先支払方法 == 5 ? data.得意先支払方法名称 : data.得意先売掛現金区分名称,
            };

            PageDialog.show({
                pgId: "DAI10010",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1000,
                height: 600,
            });
        },
        print: function() {
            var vue = this;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.title {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                }
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border: solid 0px black;
                }
                th, td {
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 30px;
                    text-align: center;
                }
                td {
                    height: 17px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3> * * 売上明細表 * * </h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 15%;">${vue.viewModel.BushoCd}:${vue.viewModel.BushoNm}</th>
                                <th style="width: 46%;"></th>
                                <th style="width: 10%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 6%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01120Grid1.generateHtml(
                                `
                                    table.DAI01120Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01120Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01120Grid1 tr th:nth-child(1) {
                                        width: 8.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(2) {
                                        width: 2.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(3) {
                                        width: 13.6%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(4) {
                                        width: 4.0%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(5) {
                                        width: 17.9%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(6) {
                                        width: 4.2%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(7) {
                                        width: 8.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(8) {
                                        width: 5.3%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(9) {
                                        width: 5.3%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(10) {
                                        width: 5.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(11) {
                                        width: 5.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(12) {
                                        width: 5.5%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(13) {
                                        width: 8.4%;
                                    }

                                    table.DAI01120Grid1 tr th:nth-child(14) {
                                        width: 5.3%;
                                    }
                                `,
                                headerFunc,
                                32,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
