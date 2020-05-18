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
                <label>対象月</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
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
                <label>出力区分</label>
            </div>
            <div class="col-md-6">
                <VueOptions
                    id="SearchType"
                    ref="VueOptions_SearchType"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SearchType"
                    :list="[
                        {code: '1', name: '部署別売上推移', label: '部署別売上推移'},
                        {code: '2', name: '営業担当者別売上推移', label: '営業担当者別売上推移'},
                        {code: '3', name: '顧客別売上推移', label: '顧客別売上推移'},
                    ]"
                    :onChangedFunc=onSearchTypeChanged
                />
            </div>
        </div>
        <div class="row" :hidden='viewModel.SearchType != 2'>
            <div class="col-md-1">
            </div>
            <div class="col-md-4">
                <label>処理選択1</label>
                <VueOptions
                    id="SearchTantoType"
                    ref="VueOptions_SearchTantoType"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SearchTantoType"
                    :list="[
                        {code: '1', name: '全担当者', label: '全担当者'},
                        {code: '2', name: '担当者指定', label: '担当者指定'},
                    ]"
                    :onChangedFunc=onSearchTantoTypeChanged
                />
            </div>
        </div>
        <div class="row" :hidden='viewModel.SearchType != 2 || viewModel.SearchTantoType != 2'>
            <div class="col-md-1">
            </div>
            <div class="col-md-6">
                <label>担当者</label>
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
            id="DAI05120Grid1"
            ref="DAI05120Grid1"
            dataUrl="/DAI05120/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI05120Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05120Grid1 .pq-group-icon {
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
    name: "DAI05120",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 給食売上推移",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                SearchType: null,
                SearchTantoType: null,
                TantoCd:null,
                ColHeader : [],
            },
            DAI05120Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 3,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: false,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: false,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["部署ＣＤ"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                    },
                    {
                        title: "対象月",
                        dataIndx: "年月", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                        fixed: true,
                    },
                ],
            },
            colModelBushoBetsu:[
                    {
                        title: "既存客・食数",
                        dataIndx: "既存_売上個数", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "既存客・金額",
                        dataIndx: "既存_売上金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "新規客・食数",
                        dataIndx: "新規_売上個数", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "新規客・金額",
                        dataIndx: "新規_売上金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "食数合計",
                        dataIndx: "食数合計", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額合計",
                        dataIndx: "金額合計", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            colModelEigyoBetsu:[
                    {
                        title: "営業担当ＣＤ",
                        dataIndx: "営業担当者ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "営業担当",
                        dataIndx: "営業担当者名", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "既存客・食数",
                        dataIndx: "既存_売上個数", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "既存客・金額",
                        dataIndx: "既存_売上金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "新規客・食数",
                        dataIndx: "新規_売上個数", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "新規客・金額",
                        dataIndx: "新規_売上金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "食数合計",
                        dataIndx: "食数合計", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額合計",
                        dataIndx: "金額合計", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            colModelKokyakuBetsu:[
                    {
                        title: "顧客コード",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "顧客名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "食数",
                        dataIndx: "売上個数", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額",
                        dataIndx: "売上金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "新規区分",
                        dataIndx: "新規区分", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                ],
        });

        data.grid1Options.filter = this.setNavigator;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05120Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //入金日付を"YYYYMMDD"形式に編集
                        params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        vue.DAI05120Grid1.searchData(params);
                    }
                },
                { visible: "true", value: "CSV", id: "DAI05120Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05120Grid1.vue.exportData("csv", false, true);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            //TODO:
            // vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            // vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateStart = moment("20190101").format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment("20190531").format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSearchTypeChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSearchTantoTypeChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onDateChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05120Grid1;
            console.log("5120 conditionChanged")

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart) return;
            var params = $.extend(true, {}, vue.viewModel);
            window.resa=_.cloneDeep(params);//TODO
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;
            var grid = vue.DAI05120Grid1;

            vue.footerButtons.find(v => v.id == "DAI05120Grid1_CSV").disabled = !res.length;

            //列定義更新
            grid.options.colModel = grid.options.colModel.filter(c => !!c.fixed);
            if(vue.viewModel.SearchType=="1"){
                grid.options.colModel.push(...vue.colModelBushoBetsu);
            }
            else if(vue.viewModel.SearchType=="2"){
                grid.options.colModel.push(...vue.colModelEigyoBetsu);
            }
            else if(vue.viewModel.SearchType=="3"){
                grid.options.colModel.push(...vue.colModelKokyakuBetsu);
            }
            grid.refreshCM();
            return res;
        },
        setNavigator: function(evt, ui) {
            var vue = this;
            console.log("setNavigator", evt, ui);
        },
    }
}
</script>
