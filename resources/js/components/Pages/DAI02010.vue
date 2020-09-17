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
            <div class="col-md-3">
                <label>締区分</label>
                <VueOptions
                    id="SimeKbn"
                    ref="VueOptions_SimeKbn"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SimeKbn"
                    buddy="SimeKbnNm"
                    :list="[
                        {code: '0', name: '日締', label: '日締'},
                        {code: '1', name: '週締', label: '週締'},
                        {code: '2', name: '月締', label: '月締'},
                    ]"
                    :onChangedFunc=onSimeKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_TargetDate"
                    :format=TargetDateFormat
                    :dayViewHeaderFormat=TargetDateDayViewHeaderFormat
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
            <div class="col-md-4">
                <label style="width: unset;">{{TargetDateMsg}}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日</label>
            </div>
            <div class="col-md-1">
                <input id="SimeDate" type="text" v-model=viewModel.SimeDate class="form-control" :disabled="SimeDateDisabled" @keydown.enter="onSimeDateChanged">
            </div>
            <div class="col-md-1">
                <label>末日：99</label>
            </div>
            <div class="col-md-1">
                <label>最終締日</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 150px;" type="text" :value=viewModel.LastSimeDate readonly tabindex="-1">
            </div>

            <!-- <div class="col-md-11">
                <VueSelect
                    id="SimeDate"
                    :vmodel=viewModel
                    bind="SimeDate"
                    uri="/Utilities/GetSimeDateList"
                    :hasNull=true
                    :onChangedFunc=onSimeDateChanged
                    :disabled=SimeDateDisabled
                />
            </div> -->
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日範囲</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDateMax"
                    ref="DatePicker_TargetDateMax"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDateMax"
                    :editable=true
                    :onChangedFunc=onTargetDateMaxChanged
                />
                <label class="text-left pl-1">迄</label>
            </div>
            <!-- <div class="col-md-3">
                <label>出力順</label>
                <VueOptions
                    id="PrintOrder"
                    ref="VueOptions_PrintOrder"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintOrder"
                    :list="[
                        {code: '0', name: '得意先順', label: '0:得意先順'},
                        {code: '1', name: 'コース順', label: '1:コース順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
                />
            </div> -->
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <!-- <div class="col-md-6">
                <VueCheckList
                    id="SearchOptions"
                    :vmodel=viewModel
                    bind="SearchOptions"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '1', name: '残高無しも出力', label: '残高無しも出力'},
                        {code: '2', name: '残高ゼロは出力しない', label: '残高ゼロは出力しない'},
                        {code: '3', name: 'ルートが無い得意先を出力', label: 'ルートが無い得意先を出力'},
                    ]"
                    :onChangedFunc=onSearchOptionsChanged
                />
            </div> -->
            <div class="col-md-3">
                <VueCheckList
                    id="Untreated"
                    :vmodel=viewModel
                    bind="Untreated"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '1', name: '未処理のみ', label: '未処理のみ'},
                    ]"
                    :onChangedFunc=onUntreatedChanged
                />
                <VueCheckList
                    id="Unshared"
                    :vmodel=viewModel
                    bind="Unshared"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '1', name: '未分配のみ', label: '未分配のみ'},
                    ]"
                    :onChangedFunc=onUnsharedChanged
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
                    :params='{ UserBushoCd: getLoginInfo().bushoCd }'
                    :isPreload=true
                    :noResearch=true
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
                    :AutoCompleteNoLimit=false
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-11">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerAbbListForSelect"
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI02010Grid1"
            ref="DAI02010Grid1"
            dataUrl="/DAI02010/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI02010Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI02010Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI02010Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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
    name: "DAI02010",
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
                SimeKbn: vue.viewModel.SimeKbn,
                SimeDate: vue.viewModel.SimeDate,
                TargetDate: vue.calcTargetDate(),
                TargetDateMax: moment(vue.viewModel.TargetDateMax, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: vue.viewModel.CourseCd,
                CustomerCd: vue.viewModel.CustomerCd,
                TantoCd: vue.getLoginInfo()["uid"],
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI02010Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "締日処理 > 請求締処理",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SimeKbn: 0,
                SimeKbnNm: null,
                TargetDate: null,
                TargetDateMax: null,
                SimeDate: 0,
                LastSimeDate: null,
                CourseCd: null,
                CourseNm: null,
                CustomerCd: null,
                CustomerNm: null,
                SearchOptions: [],
                Untreated: [],
                Unshared: [],
            },
            pgId: "DAI02010",
            DAI02010Grid1: null,
            SimeDateDisabled: true,
            TargetDateMsg: null,
            TargetDateFormat: "YYYY年MM月DD日",
            TargetDateDayViewHeaderFormat: "YYYY年MM月",
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 25,
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
                sortModel: {
                    on: true,
                    cancel: false,
                    number: false,
                    type: "local",
                    sorter: [
                        { dataIndx: "請求日付", dir: "up" },
                        { dataIndx: "請求先ＣＤ", dir: "up" },
                    ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [true, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "コース",
                        function(rowData) {
                            return rowData.コースＣＤ + ":" + rowData.コース名 + ":" + rowData.担当者ＣＤ + ":" + rowData.担当者名;
                        }
                    ],
                    [
                        "前回請求残高",
                        function(rowData) {
                            return (rowData["３回前残高"] || 0) * 1 + (rowData["前々回残高"] || 0) * 1 + (rowData["前回残高"] || 0) * 1;
                        }
                    ],
                    [
                        "残高有無判定",
                        function(rowData) {
                            return (rowData["３回前残高"] || 0) * 1 != 0
                                || (rowData["前々回残高"] || 0) * 1 != 0
                                || (rowData["前回残高"] || 0) * 1 != 0
                                || (rowData["今回入金額"] || 0) * 1 != 0
                                || (rowData["今回売上額"] || 0) * 1 != 0
                                ;
                        }
                    ],
                    [
                        "締日",
                        function(rowData) {
                            return "/"
                                 + (rowData["締日１"] || "")
                                 + "/"
                                 + (rowData["締日２"] || "")
                                 + "/"
                                 ;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コース",
                        dataIndx: "コース",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "請求先ＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "得意先名略称",
                        dataIndx: "得意先名略称",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        hidden: false,
                        hiddenOnExport: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 125,
                        render: ui => {
                            if (!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "請求日付",
                        dataIndx: "請求日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "ＳＥＱ",
                        dataIndx: "ＳＥＱ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "支払方法",
                        dataIndx: "支払方法",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "集金日",
                        dataIndx: "集金日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                        render: ui => {
                            if (!!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "前回請求残高",
                        dataIndx: "前回請求残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回入金額",
                        dataIndx: "今回入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回売上額",
                        dataIndx: "今回売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回請求額",
                        dataIndx: "今回請求額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "済",
                        dataIndx: "締処理済",
                        dataType: "integer",
                        align: "center",
                        width: 50, minWidth: 50, maxWidth: 50,
                        render: ui => {
                            return { text: ui.rowData.締処理済 == "1" ? "済" : "" };
                        },
                    },
                    {
                        title: "未分配",
                        dataIndx: "未分配",
                        dataType: "string",
                        align: "center",
                        width: 55, minWidth: 55, maxWidth: 55,
                        render: ui => {
                            return { text: ui.rowData.未分配 == "1" ? "×" : "" };
                        },
                    },
                    {
                        title: "残高有無判定",
                        dataIndx: "残高有無判定",
                        dataType: "bool",
                        hidden: true,
                    },
                    {
                        title: "締日",
                        dataIndx: "締日",
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
                { visible: "true", value: "検索", id: "DAI02010Grid1_Search", disabled: false, shortcut: "F1",
                    onClick: function () {
                        vue.DAI02010Grid1.searchData(vue.searchParams);
                    }
                },
                { visible: "false" } ,
                { visible: "true", value: "一括実行", id: "DAI02010Grid1_ExecuteAll", disabled: true, shortcut: "F5",
                    onClick: function () {
                        var grid = vue.DAI02010Grid1;

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI02010/Save",
                                params: {
                                    CustomerList: DAI02010Grid1.pdata.map(v => v.請求先ＣＤ),
                                    noCache: true,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                },
                            }
                        );
                    }
                },
                { visible: "true", value: "一括解除", id: "DAI02010Grid1_ReleaseAll", disabled: true, shortcut: "F6",
                    onClick: function () {
                        var grid = vue.DAI02010Grid1;

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI02010/Release",
                                params: {
                                    CustomerList: DAI02010Grid1.pdata.map(v => v.請求先ＣＤ),
                                    noCache: true,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                },
                            }
                        );
                    }
                },
                { visible: "false" } ,
                { visible: "true", value: "個別実行", id: "DAI02010Grid1_ExecuteSingle", disabled: true, shortcut: "F7",
                    onClick: function () {
                        var grid = vue.DAI02010Grid1;
                        var selection = grid.SelectRow().getSelection();
                        var rows = grid.SelectRow().getSelection();

                        if (rows.length != 1) return;

                        var data = _.cloneDeep(rows[0].rowData);

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI02010/Save",
                                params: {
                                    CustomerList: data.請求先ＣＤ,
                                    noCache: true,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                },
                            }
                        );
                    }
                },
                { visible: "true", value: "個別解除", id: "DAI02010Grid1_ReleaseSingle", disabled: true, shortcut: "F8",
                    onClick: function () {
                        var grid = vue.DAI02010Grid1;
                        var selection = grid.SelectRow().getSelection();
                        var rows = grid.SelectRow().getSelection();

                        if (rows.length != 1) return;

                        var data = _.cloneDeep(rows[0].rowData);

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI02010/Release",
                                params: {
                                    CustomerList: data.請求先ＣＤ,
                                    noCache: true,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                },
                            }
                        );
                    }
                },
                { visible: "false" } ,
                { visible: "true", value: "未分配一覧", id: "DAI02010Grid1_ShowUnshared", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.showUnshared();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.TargetDateMax = moment().format("YYYY年MM月DD日");
            // vue.viewModel.SimeKbn = "2";
            // vue.viewModel.SimeDate = "20";
            // vue.viewModel.TargetDate = moment('20190320').format("YYYY年MM月DD日");
            // vue.viewModel.TargetDateMax = moment(20190320).format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI02010Grid1.selectionRowList",
                list => {
                    var data = list.map(v => v.rowData);
                    console.log("2010 selectrow change")
                    vue.footerButtons.find(v => v.id == "DAI02010Grid1_ExecuteSingle").disabled =
                        data.some(r => r.締処理済 == 1) || data.some(r => r.未分配 == 1);
                    vue.footerButtons.find(v => v.id == "DAI02010Grid1_ReleaseSingle").disabled =
                        data.some(r => r.締処理済 == 0) || data.some(r => r.未分配 == 1);
                    vue.footerButtons.find(v => v.id == "DAI02010Grid1_ShowUnshared").disabled = data.some(r => r.未分配 == 0);
                }
            );

            vue.changeScreen(vue);

            //初期フィルタ
            vue.filterChanged();
        },
        activatedFunc: function(vue) {
            vue.changeScreen(vue);
        },
        changeScreen(vue) {
            var pgId = vue.$route.path == "/DAI07/DAI07070" ? "DAI07070" : "DAI02010";
            var title = pgId == "DAI07070" ? "個人宅 > 請求締処理" : "締日処理 > 請求締処理";
            vue.pgId = pgId;
            vue.$root.$emit("setTitle", title);

            switch (vue.pgId) {
                case "DAI07070":
                    // vue.viewModel.SimeKbn = "1";
                    // vue.SimeKbnDisabled = true;
                    break;
                case "DAI02010":
                    // vue.SimeKbnDisabled = false;
                    break;
            }
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        showLastSimeDate: function(){
            var vue = this;
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI02010/LastSimeDateSearch", { BushoCd:vue.viewModel.BushoCd, SimeKbn:vue.viewModel.SimeKbn, timestamp:tc})
                .then(response => {
                    var res = _.cloneDeep(response.data);
                    window.resdt=_.cloneDeep(res);
                    vue.viewModel.LastSimeDate=moment(res.請求日付).format("YYYY年MM月DD日");
                    console.log("",vue.viewModel.LastSimeDate);
                })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: " 請求データ検索失敗",
                    contents: " 請求データ検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        onSimeKbnChanged: function() {
            var vue = this;

            //請求日付DatePicker option変更
            vue.TargetDateFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年MM月" : "YYYY年MM月DD日";
            vue.TargetDateDayViewHeaderFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年" : "YYYY年MM月";

            //締日MultiSelect状態変更
            vue.viewModel.SimeDate = "0";
            vue.SimeDateDisabled = vue.viewModel.SimeKbn != "2";

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function() {
            var vue = this;

            vue.viewModel.TargetDateMax = vue.viewModel.TargetDate;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateMaxChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimeDateChanged: function(entity, entities) {
            var vue = this;
            var lastDay = moment(vue.viewModel.TargetDate, "YYYYMM01").endOf('month').format("DD");

            if ((entity.target.value >= 1 && entity.target.value <= lastDay) || entity.target.value == 99) {
                vue.viewModel.SimeDate = entity.target.value;
                var simeDay = entity.target.value == 99 ? lastDay : entity.target.value;
                vue.viewModel.TargetDateMax = vue.viewModel.SimeKbn == "2"
                    ? moment(vue.viewModel.TargetDate, vue.TargetDateFormat).format(vue.TargetDateFormat) + simeDay + "日"
                    : vue.viewModel.TargetDate;
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI02030Grid1;

            //ソート順変更
            var sorter = [
                { dataIndx: "請求日付", dir: "up" },
            ];

            if (vue.viewModel.PrintOrder == "1") {
                sorter.push({ dataIndx: "コースＣＤ", dir: "up" });
                sorter.push({ dataIndx: "ＳＥＱ", dir: "up" });
            }
            sorter.push({ dataIndx: "請求先ＣＤ", dir: "up" });

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //ソート変更
            if (!!grid) {
                grid.sort( { sorter: sorter });
            }
        },
        onSearchOptionsChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onUntreatedChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onUnsharedChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署ＣＤ) {
                    vue.viewModel.BushoCd = entity.部署ＣＤ;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署CD) {
                    vue.viewModel.BushoCd = entity.部署CD;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI02010Grid1;
            console.log("2020 conditionChanged")

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate || !vue.viewModel.TargetDateMax) return;

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                console.log("same condition", _.isEqual(grid.prevPostData, vue.searchParams));
                return;
            }

            grid.searchData(vue.searchParams, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI02010Grid1;
            console.log("2020 filterChanged")

            if (!grid) return;

            var rules = [];

            // //締日
            // if (vue.viewModel.SimeKbn == "2") {
            //     // if (!!vue.viewModel.SimeDateArray.length) {
            //     //     var crules = vue.viewModel.SimeDateArray.map(d => {
            //     //         return { condition: "contain", mode: "OR", value: "/" + d.code + "/" };
            //     //     });
            //     //     rules.push({ dataIndx: "締日", crules: crules });
            //     // } else {
            //     //     rules.push({ dataIndx: "締日", condition: "contain", value: "99" });
            //     // }
            //     rules.push({ dataIndx: "締日", condition: "contain", value: "0" });
            // }

            // //残高無し表示
            // if (!vue.viewModel.SearchOptions.includes("1")) {
            //     rules.push({ dataIndx: "残高有無判定", condition: "equal", value: true });
            // }

            // //残高ゼロ非表示
            // if (vue.viewModel.SearchOptions.includes("2")) {
            //     rules.push({ dataIndx: "今回請求額", condition: "notequal", value: "0" });
            // }

            // //ルート無し表示
            // if (vue.viewModel.SearchOptions.includes("3")) {
            //     rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: "0" });
            // } else {
            //     if (vue.viewModel.PrintOrder == "1") {
            //         rules.push({ dataIndx: "コースＣＤ", condition: "notequal", value: "0" });
            //     }
            // }

            //締処理済
            if (vue.viewModel.Untreated.includes("1")) {
                rules.push({ dataIndx: "締処理済", condition: "equal", value: "0" });
            }

            //未分配
            if (vue.viewModel.Unshared.includes("1")) {
                rules.push({ dataIndx: "未分配", condition: "equal", value: "1" });
            }

            //コース
            if (!!vue.viewModel.CourseCd && vue.$refs.PopupSelect_Course.isValid) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }

            //請求先
            if (!!vue.viewModel.CustomerCd && vue.$refs.PopupSelect_Customer.isValid) {
                rules.push({ dataIndx: "請求先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            grid.filter({ oper: "replace", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            //最終締日
            vue.showLastSimeDate();

            // vue.footerButtons.find(v => v.id == "DAI02010Grid1_CSV").disabled = !res.length;
            // vue.footerButtons.find(v => v.id == "DAI02010Grid1_Excel").disabled = !res.length;
            // vue.footerButtons.find(v => v.id == "DAI02010Grid1_Print").disabled = !res.length;

            vue.footerButtons.find(v => v.id == "DAI02010Grid1_ExecuteAll").disabled =
                !res.length || res.some(v => v.未分配 == 1);
            vue.footerButtons.find(v => v.id == "DAI02010Grid1_ReleaseAll").disabled =
                !res.length || !res.some(r => r.締処理済 == 1);

            return res;
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
                .filter(v => !!vue.viewModel.BushoCd ? v.部署ＣＤ == (vue.viewModel.BushoCd || vue.getLoginInfo().bushoCd) : true)
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
                    ret.label = "[" + (v.部署名 || "部署無し") + "]" + v.Cd + " : " + v.CdNm + "【" + v.担当者名 + "】";
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
                .filter(v => (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd) ? (v.部署CD == vue.viewModel.BushoCd && v.コースＣＤ == vue.viewModel.CourseCd) : true)
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
        calcTargetDate: function() {
            var vue = this;

            var ret;
            vue.TargetDateMsg = null;

            switch (vue.viewModel.SimeKbn)
            {
                case "0":   //日締
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    break;
                case "1":   //週締
                    //該当週の土曜
                    var mt = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6);
                    ret = mt.format("YYYYMMDD");
                    if (ret != moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")) {
                        vue.TargetDateMsg = mt.format("YYYY年MM月DD日") + "扱いで処理されます";
                    }
                    break;
                case "2":
                    //該当月
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月").startOf("month").format("YYYYMMDD");
                    break;
            }

            return ret;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI02010Grid1;

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
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: moment(data.請求日付).format("YYYY年MM月DD日"),
                DateStart: moment(data.請求日範囲開始).format("YYYY年MM月DD日"),
                DateEnd: moment(data.請求日範囲終了).format("YYYY年MM月DD日"),
                SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CourseKbn: data.コース区分,
            };

            PageDialog.show({
                pgId: "DAI02021",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
            });
        },
        showUriage: function() {
            var vue = this;
            var grid = vue.DAI02010Grid1;

            var selection = grid.SelectRow().getSelection();
            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: moment().format("YYYY年MM月DD日"),
                CourseKbn: data.コース区分,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CustomerCd: data.請求先ＣＤ,
                onSaveFunc: () => {
                    var grid = vue.DAI02010Grid1;
                    grid.refreshDataAndView();
                },
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
        showNyukin: function() {
            var vue = this;
            var grid = vue.DAI02010Grid1;

            var selection = grid.SelectRow().getSelection();
            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment().format("YYYY年MM月DD日"),
                CustomerCd: data.請求先ＣＤ,
                onSaveFunc: () => {
                    var grid = vue.DAI02010Grid1;
                    grid.refreshDataAndView();
                },
            };

            PageDialog.show({
                pgId: "DAI01130",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
                onBeforeClose: (event, ui) => {
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

                    var canClose = !editting && !isEscOnEditor;

                    return canClose;
                }
            });
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI02010Grid1;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                h3 {
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
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (header, idx, length) => {
                var TargetDate = vue.viewModel.SimeKbn == "2"
                    ? moment(header.pq_level == 0 ? header.請求日付 : header.parentId).format("YYYY年MM月DD日")
                    : vue.viewModel.SimeKbn == "1"
                        ? moment(vue.searchParams.TargetDate).format("YYYY年MM月DD日")
                        : vue.viewModel.TargetDate
                    ;
                var GroupInfo = vue.viewModel.SimeKbn == "2"
                    ? (header.pq_level == 0 ? (!!header.children.length ? header.children[0].コース : "") : header.コース).split(":")
                    : []
                    ;
                var CourseCd = GroupInfo[0] || "";
                var CourseNm = GroupInfo[1] || "";
                var TantoCd = GroupInfo[2] || "";
                var TantoNm = GroupInfo[3] || "";

                return `
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 6%;">部署</th>
                                <th style="width: 4%;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 12%;">${vue.viewModel.BushoNm}</th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 26%; vertical-align: top;" rowspan=2>
                                    <h3 style="font-size: 16pt;">* * * 請求一覧表 * * *</h3>
                                </th>
                                <th style="width: 6%;"></th>
                                <th style="width: 10%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                                <th style="width: 6%;"></th>
                            </tr>
                            <tr>
                                <th>請求日付</th>
                                <th colspan=2>${TargetDate}</th>
                            </tr>
                            <tr>
                                <th>コース</th>
                                <th>${CourseCd}</th>
                                <th colspan=2>${CourseNm}</th>
                            </tr>
                            <tr>
                                <th>担当者</th>
                                <th>${TantoCd}</th>
                                <th>${TantoNm}</th>
                                <th>締区分</th>
                                <th>${vue.viewModel.SimeKbnNm}</th>
                                <th></th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th>時間</th>
                                <th>${moment().format("HH:mm:ss")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right; padding-right: 10px;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var keys = [];
            if (vue.viewModel.SimeKbn == "2") {
                keys.push("請求日付");
            }
            if (vue.viewModel.PrintOrder == "1") {
                keys.push("コース");
            }
            if (!!keys.length) {
                grid.Group().option({ "dataIndx": keys});
            }

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    .header-table th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr th:first-child {
                                        border-left-width: 1px;
                                    }
                                    .header-table tr:nth-child(1) th:nth-child(n+4) {
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .header-table tr:nth-child(4) th:nth-child(6) {
                                        border-top-width: 0px;
                                    }
                                    table.DAI02010Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02010Grid1 tr th:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI02010Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02010Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI02010Grid1 tr td:last-child {
                                        border-right-width: 1px;
                                    }
                                `,
                                headerFunc,
                                32,
                                [false, false],
                                [true, true],
                                [true, true],
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "dataIndx": [] });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
        showUnshared: function() {
            var vue = this;
            var grid = vue.DAI02010Grid1;

            var selection = grid.SelectRow().getSelection();

            var rows = grid.SelectRow().getSelection().map(v => v.rowData);
            if (!rows.length) return;

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                DateStart: _.min(rows.map(v => v.請求日範囲開始)),
                DateEnd: _.max(rows.map(v => v.請求日範囲終了)),
                IsChild: true,
                Parent: vue,
            };

            PageDialog.show({
                pgId: "DAI01250",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1200,
                height: 700,
            });
        },
    }
}
</script>
