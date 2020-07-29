<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>対象日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateStartChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateEndChanged
                />
            </div>
            <div class="col-md-1">
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="Customer"
                    ref="VueOptions_Customer"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="Customer"
                    :list="[
                        {code: '0', name: '全顧客', label: '0:全顧客'},
                        {code: '1', name: '新規顧客', label: '1:新規顧客のみ'},
                    ]"
                    :onChangedFunc=onCustomerChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>新規登録日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="SaveDateStart"
                    ref="DatePicker_SaveDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="SaveDateStart"
                    :editable=true
                    :onChangedFunc=onSaveDateStartChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="SaveDateEnd"
                    ref="DatePicker_SaveDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="SaveDateEnd"
                    :editable=true
                    :onChangedFunc=onSaveDateEndChanged
                />
            </div>
            <div class="col-md-1">
            </div>
            <VueCheck
                id="VueCheck_ShowSyonin"
                ref="VueCheck_ShowSyonin"
                :vmodel=viewModel
                bind="ShowSyonin"
                checkedCode="1"
                customContainerStyle="border: none;"
                :list="[
                    {code: '0', name: 'しない', label: '承認・仮承認'},
                    {code: '1', name: 'する', label: '承認・仮承認'},
                ]"
                :onChangedFunc=onShowSyoninChanged
            />
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="BushoOption"
                    ref="VueOptions_BushoOption"
                    customItemStyle="text-align: center; margin-right: 10px; border: none;"
                    :vmodel=viewModel
                    bind="BushoOption"
                    :list="[
                        {code: '0', name: '部署なし', label: '部署なし'},
                        {code: '1', name: '全社', label: '全社　　'},
                        {code: '2', name: '部署', label: '部署'},
                    ]"
                    :onChangedFunc=onBushoOptionChanged
                />
                <VueSelectBusho
                    :onChangedFunc=onBushoCdChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>営業担当者</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="EigyoTantoCd"
                    ref="PopupSelect_EigyoTantoCd"
                    :vmodel=viewModel
                    bind="EigyoTantoCd"
                    dataUrl="/Utilities/GetTantoList"
                    :params='{}'
                    :dataListReset=true
                    title="営業担当者"
                    labelCd="営業担当者CD"
                    labelCdNm="営業担当者名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onEigyoTantoCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>獲得営業者</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="GetKakutokuTantoCd"
                    ref="PopupSelect_GetKakutokuTantoCd"
                    :vmodel=viewModel
                    bind="GetKakutokuTantoCd"
                    dataUrl="/Utilities/GetTantoList"
                    :params='{}'
                    :dataListReset=true
                    title="獲得営業担当者"
                    labelCd="獲得営業担当者CD"
                    labelCdNm="獲得営業担当者名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onKakutokuTantoCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05090Grid1"
            ref="DAI05090Grid1"
            dataUrl="/DAI05090/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI05090Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05090Grid1 .pq-group-icon {
    display: none !important;
}
label{
    width: 80px;
}
</style>
<style scoped>
</style>
<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05090",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            var getYYYYMMDD = target => {
                var mt = moment(target, "YYYY年MM月DD日");
                return mt.isValid() ? mt.format("YYYYMMDD") : null;
            };

            return {
                BushoCd: vue.viewModel.BushoOption == 2 ? vue.viewModel.BushoCd : null,
                DateStart: getYYYYMMDD(vue.viewModel.DateStart),
                DateEnd: getYYYYMMDD(vue.viewModel.DateEnd),
                SaveDateStart: getYYYYMMDD(vue.viewModel.SaveDateStart),
                SaveDateEnd: getYYYYMMDD(vue.viewModel.SaveDateEnd),
                Customer: vue.viewModel.Customer,
                ShowSyonin: vue.viewModel.ShowSyonin,
                BushoOption: vue.viewModel.BushoOption,
            };
        },
    },
    watch: {
        "DAI05090Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI05090Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 顧客売上推移表",
            noViewModel: true,
            viewModel: {
                BushoOption: "2",
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                SaveDateStart: null,
                SaveDateEnd: null,
                Customer: "0",
                ShowSyonin: "0",
            },
            DAI05090Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 50,
                freezeCols: 7,
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
                    type: "remote",
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["部署名", "ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    showSummary: [false, false, true],
                    collapsed: [false, false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ2", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者ＣＤ",
                        dataIndx: "営業担当者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "獲得営業者ＣＤ",
                        dataIndx: "獲得営業者ＣＤ", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "営業担当者",
                        dataIndx: "ＧＫ営業担当者", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                        hidden: true,
                        fixed: true,
                        render: ui => {
                            if (vue.viewModel.BushoOption == 0){
                                if (ui.rowData.pq_level != 0) {
                                    return { text: "" };
                                }
                            } else {
                                if (ui.rowData.pq_level != 1) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "獲得営業者",
                        dataIndx: "ＧＫ獲得営業者", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                        hidden: true,
                        fixed: true,
                        render: ui => {
                            if (vue.viewModel.BushoOption == 0){
                                switch (ui.rowData.pq_level) {
                                    case 0:
                                        return { text: "" };
                                    case 1:
                                        return ui;
                                    default:
                                        return { text: "" };
                                }
                            } else {
                                switch (ui.rowData.pq_level) {
                                    case 0:
                                        return { text: "" };
                                    case 1:
                                        return ui;
                                    case 2:
                                        return ui;

                                    default:
                                        return { text: "" };
                                }
                            }
                        },
                    },
                    {
                        title: "顧客CD",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 70, minWidth: 70, maxWidth: 70, align: "right",
                        fixed: true,
                    },
                    {
                        title: "顧客名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 300, minWidth: 300, maxWidth: 300,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "総合計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                if (vue.viewModel.BushoOption == 0){
                                    switch (ui.rowData.pq_level) {
                                        case 1:
                                            return { text: "合計" };
                                        default:
                                            return { text: "" };
                                    }
                                } else {
                                    switch (ui.rowData.pq_level) {
                                        case 2:
                                            return { text: "合計" };
                                        default:
                                            return { text: "" };
                                    }
                                }
                            }
                            return { text:ui };
                        },
                    },
                ],
            },
        });

        var mt = moment();
        var days = _.range(1, mt.endOf("month").format("D") * 1 + 1);
        var max = 31;
        days = days.length == max ? days : days.concat(_.range(0, days.length - max).fill(null));

        data.grid1Options.colModel = data.grid1Options.colModel
            .concat(
                days.map((d, i) => {
                    var date = mt.startOf("month").add("days", i);

                    return {
                        title: !!d ? (date.format("ddd") + "<br>" + d) : "<br>",
                        dataIndx: !!d ? date.format("D") : ("empty" + i),
                        dataType: "integer",
                        format: "#,##0",
                        width: 50, maxWidth: 50, minWidth: 50,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        days: true,
                    }
                })
            )
            .concat(
                [
                    {
                        title: "合計" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "平日日数",
                        dataIndx: "得意先平日日数", dataType: "integer", format: "#,###",
                        hidden: true,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "平均" + "<br>" + "(月～金)",
                        dataIndx: "得意先平日平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                if (vue.viewModel.BushoOption == 0) {
                                    switch (ui.rowData.pq_level) {
                                        case 1:
                                            if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                            {
                                                return { text: "" };
                                            }
                                            else
                                            {
                                                var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                                var avgValFmt = avgVal.toFixed(1).toString();
                                                return { text: avgValFmt };
                                            }
                                        default:
                                            return { text: "" };
                                    }
                                } else {
                                    switch (ui.rowData.pq_level) {
                                        case 2:
                                            if (ui.rowData.得意先平日合計 * 1 == 0 || ui.rowData.得意先平日日数 * 1 == 0)
                                            {
                                                return { text: "" };
                                            }
                                            else
                                            {
                                                var avgVal = (ui.rowData.得意先平日合計.replace(",", "") * 1) / (ui.rowData.得意先平日日数.replace(",", "") * 1);
                                                var avgValFmt = avgVal.toFixed(1).toString();
                                                return { text: avgValFmt };
                                            }
                                        default:
                                            return { text: "" };
                                    }
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "合計",
                        dataIndx: "得意先合計", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "平均",
                        dataIndx: "得意先平均", dataType: "float", format: "#,###.0",
                        width: 80, minWidth: 80, maxWidth: 80,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                {
                                    return { text: "" };
                                }
                                else
                                {
                                    var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                    var avgValFmt = avgVal.toFixed(1).toString();
                                    return { text: avgValFmt };
                                }
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                if (vue.viewModel.BushoOption == 0) {
                                    switch (ui.rowData.pq_level) {
                                        case 1:
                                            if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                            {
                                                return { text: "" };
                                            }
                                            else
                                            {
                                                var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                                var avgValFmt = avgVal.toFixed(1).toString();
                                                return { text: avgValFmt };
                                            }
                                        default:
                                            return { text: "" };
                                    }
                                } else {
                                    switch (ui.rowData.pq_level) {
                                        case 2:
                                            if (ui.rowData.得意先合計 * 1 == 0 || ui.rowData.得意先売上日数 * 1 == 0)
                                            {
                                                return { text: "" };
                                            }
                                            else
                                            {
                                                var avgVal = (ui.rowData.得意先合計.replace(",", "") * 1) / (ui.rowData.得意先売上日数.replace(",", "") * 1);
                                                var avgValFmt = avgVal.toFixed(1).toString();
                                                return { text: avgValFmt };
                                            }
                                        default:
                                            return { text: "" };
                                    }
                                }
                            }
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "売上日数",
                        dataIndx: "得意先売上日数", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "売上金額",
                        dataIndx: "得意先売上金額", dataType: "integer", format: "#,###",
                        width: 80, minWidth: 80, maxWidth: 80,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                ]
            );

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05090Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI05090Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05090Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI05090Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI05090Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05090Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日moment(vue.viewModel.TargetDate, "YYYYMM01").endOf('month')
            vue.viewModel.DateStart = moment().format("YYYY年MM月01日");
            vue.viewModel.DateEnd = moment().endOf('month').format("YYYY年MM月DD日");
            vue.viewModel.SaveDateStart = moment().format("YYYY年MM月01日");
            vue.viewModel.SaveDateEnd = moment().endOf('month').format("YYYY年MM月DD日");

            vue.refreshCols();
        },
        refreshCols: function(callback) {
            var vue = this;
            var grid = vue.DAI05090Grid1;

            var mt = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var days = _.range(1, mt.endOf("month").format("D") * 1 + 1);
            var max = 31;
            days = days.length == max ? days : days.concat(_.range(0, days.length - max).fill(null));

            grid.options.colModel.filter(c => !!c.days).map((c, i) => {
                var d = days[i];
                var date = mt.startOf("month").add("days", i);

                c.title = !!d ? (date.format("ddd") + "<br>" + d) : "<br>";
                c.dataIndx = !!d ? date.format("D") : ("empty" + i);

                return c;
            });

            grid.refreshCM();
            grid.refresh();

            if (!!callback) callback();
        },
        onBushoOptionChanged: function(code, entities) {
            var vue = this;

            //グループキー切替
            var grid = vue.DAI05090Grid1;
            if (vue.viewModel.BushoOption == 0){
                grid.Group().option({
                    "dataIndx": ["ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    "showSummary": [false, true],
                    "collapsed": [false, false],
                });
            } else {
                grid.Group().option({ "dataIndx": ["部署名", "ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    "showSummary": [false, false, true],
                    "collapsed": [false, false, false],
                });
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoCdChanged: function(code, entities) {
            var vue = this;

            if (vue.viewModel.BushoOption == 2) {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onDateStartChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.DateEnd = ms.endOf("month").format("YYYY年MM月DD日");
            } else {
                //列定義変更 + 条件変更ハンドラ
                vue.refreshCols(vue.conditionChanged);
            }
        },
        onDateEndChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.DateStart = me.startOf("month").format("YYYY年MM月DD日");
            } else {
                //列定義変更 + 条件変更ハンドラ
                vue.refreshCols(vue.conditionChanged);
            }
        },
        onSaveDateStartChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.SaveDateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.SaveDateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.SaveDateEnd = ms.endOf("month").format("YYYY年MM月DD日");
            } else {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onSaveDateEndChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.SaveDateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.SaveDateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.SaveDateStart = me.startOf("month").format("YYYY年MM月DD日");
            } else {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onShowSyoninChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onEigyoTantoCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onKakutokuTantoCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["担当者名", "部署.部署名", "担当者名カナ"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.担当者ＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.担当者ＣＤ + " : " + v.担当者名 + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.担当者ＣＤ;
                    ret.text = v.担当者名;
                    return ret;
                })
                ;

            return list;
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid1 = vue.DAI05090Grid1;

            if (!grid1 || !vue.getLoginInfo().isLogOn) return;

            if (!vue.searchParams.DateStart || !vue.searchParams.DateEnd) return;

            if (!force && _.isEqual(grid1.options.dataModel.postData, vue.searchParams)) return;

            grid1.searchData(vue.searchParams);
        },
        filterChanged: function() {
            var vue = this;
            var grid1 = vue.DAI05090Grid1;

            if (!grid1) return;

            var rules = [];

            if (!!vue.viewModel.EigyoTantoCd) {
                rules.push({ dataIndx: "営業担当者ＣＤ", condition: "equal", value: vue.viewModel.EigyoTantoCd });
            }
            if (!!vue.viewModel.GetEigyoTantoCd) {
                rules.push({ dataIndx: "獲得営業者ＣＤ", condition: "equal", value: vue.viewModel.GetEigyoTantoCd });
            }

            grid1.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_CSV").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_Excel").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05090Grid1_Print").disabled = false;

            res.forEach(r => {
                    r.ＧＫ営業担当者 = r.営業担当者ＣＤ + " " + r.営業担当者名;
                    r.ＧＫ獲得営業者 = r.獲得営業者ＣＤ + " " + r.獲得営業者名;
                });


            return res;
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
                    font-size: 9.5pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 25px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
            `;

            var bushoNm;
            var eigyoNmKey1;
            var eigyoNmKey2;
            var headerFunc = (header, idx, length) => {
                if (vue.viewModel.BushoOption == 0) {
                    if (header.pq_level == 0) {
                        bushoNm = header.children[0].children[0].部署名;
                        eigyoNmKey1 = header.ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].ＧＫ獲得営業者.split(" ")[1];;
                    }
                    if (header.pq_level == 1) {
                        bushoNm = header.children[0].部署名;
                        eigyoNmKey2 = header.ＧＫ獲得営業者.split(" ")[1];
                    }
                } else {
                    if (header.pq_level == 0) {
                        bushoNm = header.部署名;
                        eigyoNmKey1 = header.children[0].ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].children[0].ＧＫ獲得営業者.split(" ")[1];
                    }
                    if (header.pq_level == 1) {
                        bushoNm = header.children[0].children[0].部署名;
                        eigyoNmKey1 = header.ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].ＧＫ獲得営業者.split(" ")[1];
                    }
                    if (header.pq_level == 2) {
                        bushoNm = header.children[0].部署名;
                        eigyoNmKey2 = header.ＧＫ獲得営業者.split(" ")[1];
                    }
                }
                return `
                    <div class="title">
                        <h3>* * 顧客売上推移表 * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th class="blank-cell" style="width: 27%;"></th>
                                <th style="width: 8%;">対象範囲</th>
                                <th style="width: 10.5%;">（${vue.viewModel.DateStart}</th>
                                <th style="width: 2%;">～</th>
                                <th style="width: 10.5%;">${vue.viewModel.DateEnd}）</th>
                                <th style="width: 11%;" class="blank-cell"></th>
                                <th style="width: 20%; text-align:right; font-weight: bold;">[営業食数－担当者]</th>
                                <th style="width: 11%; text-align: right;">${idx + 1} / ${length}</th>
                            </tr>
                            <tr>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th style="text-align: right;">作成日:</th>
                                <th>${moment().format("YYYY/MM/DD hh:mm")}</th>
                            </tr>
                            <tr>
                                <th>${vue.viewModel.BushoOption != 0 ? bushoNm : ""}</th>
                                <th>営業担当者：</th>
                                <th colspan="3">${eigyoNmKey1}</th>
                                <th style="text-align: right;">獲得営業担当者：</th>
                                <th>${eigyoNmKey2}</th>
                                <th class="blank-cell"></th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                .header-table thead tr th {
                    text-align: left;
                }
                table.DAI05090Grid1 tr th ,
                table.DAI05090Grid1 tr td {
                    font-size: 8pt;
                }
                table.DAI05090Grid1 tr:not(.group-summary) td:nth-child(2) {
                    font-size: 7pt;
                }
                table.DAI05090Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    line-height: 1.3em;
                }
                table.DAI05090Grid1 tr td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05090Grid1 tr th:last-child,
                table.DAI05090Grid1 tr td:last-child {
                    border-right-width: 1px;
                }
                table.DAI05090Grid1 tr:last-child td,
                table.DAI05090Grid1 tr.group-summary td {
                    border-bottom-width: 1px;
                }
                table.DAI05090Grid1 tr[level="0"].group-summary td:nth-child(2),
                table.DAI05090Grid1 tr.group-summary td:nth-child(2) {
                    text-align: center;
                }
                table.DAI05090Grid1 tr th:nth-child(1) {
                    width: 4.2%;
                }
                table.DAI05090Grid1 tr th:nth-child(2) {
                    width: 12%;
                }
                table.DAI05090Grid1 tr th:nth-last-child(1) {
                    width: 5.2%;
                }
                table.DAI05090Grid1 tr th:nth-last-child(2) ,
                table.DAI05090Grid1 tr th:nth-last-child(3) ,
                table.DAI05090Grid1 tr th:nth-last-child(4) {
                    width: 3%;
                }
                table.DAI05090Grid1 tr th:nth-last-child(5) ,
                table.DAI05090Grid1 tr th:nth-last-child(6) {
                    width: 4.5%;
                }
                table.DAI05090Grid1 tr td {
                    padding-left: 1px;
                    padding-right: 1px;
                }
                table.DAI05090Grid1 tr.grand-summary td {
                    color: transparent;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-right: 10px !important;
                    margin-left: 10px !important;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI05090Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                30,
                                false,
                                true,
                                true,
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
