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
                    ref="VueOptions_Busho"
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
                    id="GetEigyoTantoCd"
                    ref="PopupSelect_GetEigyoTantoCd"
                    :vmodel=viewModel
                    bind="GetEigyoTantoCd"
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
                    :onAfterChangedFunc=onEigyoTantoCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05100Grid1"
            ref="DAI05100Grid1"
            dataUrl="/DAI05100/Search"
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
#DAI05100Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05100Grid1 .pq-group-icon {
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
    name: "DAI05100",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    watch: {
        "DAI05100Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI05100Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 顧客売上表",
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
            DAI05100Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 0,
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
                    showSummary: [true, true, true],
                    collapsed: [false, false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "営業担当者ＣＤ",
                        dataIndx: "営業担当者ＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "獲得営業者ＣＤ",
                        dataIndx: "獲得営業者ＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "営業担当者",
                        dataIndx: "ＧＫ営業担当者", dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
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
                        width: 100, minWidth: 100, maxWidth: 100,
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
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ2", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "顧客CD",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75, align: "right",
                    },
                    {
                        title: "顧客名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 500, minWidth: 500, maxWidth: 500,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "** 総合計 **" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                if (vue.viewModel.BushoOption == 0){
                                    switch (ui.rowData.pq_level) {
                                        case 0:
                                            return { text: "** 営業担当合計 **" };
                                        case 1:
                                            return { text: "** 合計 **" };
                                        default:
                                            return { text: "" };
                                    }
                                } else {
                                    switch (ui.rowData.pq_level) {
                                        case 0:
                                            return { text: "** 部署合計**" };
                                        case 1:
                                            return { text: "** 営業担当合計 **" };
                                        case 2:
                                            return { text: "** 合計 **" };
                                        default:
                                            return { text: "" };
                                    }
                                }
                            }
                            return { text:ui };
                        },
                    },
                    {
                        title: "稼働日",
                        dataIndx: "稼働日", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "食数合計",
                        dataIndx: "食数合計", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "食数平均",
                        dataIndx: "食数平均", dataType: "float", format: "#,###.0",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            if (ui.rowData[ui.dataIndx] * 1 == 0 || ui.rowData.食数合計 * 1 == 0 || ui.rowData.稼働日 * 1 == 0) {
                                return { text: "0.0" };
                            }
                            else
                            {
                                var eatAvg = (ui.rowData.食数合計.replace(",", "") * 1) / (ui.rowData.稼働日.replace(",", "") * 1);
                                var eatAvgFmt = eatAvg.toFixed(1).toString()
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: eatAvgFmt };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    if (vue.viewModel.BushoOption == 0) {
                                        switch (ui.rowData.pq_level) {
                                            case 0:
                                                return { text: eatAvgFmt };
                                            case 1:
                                                return { text: eatAvgFmt };
                                        }
                                    } else {
                                        switch (ui.rowData.pq_level) {
                                            case 1:
                                                return { text: eatAvgFmt };
                                            case 2:
                                                return { text: eatAvgFmt };
                                        }
                                    }
                                }
                            }

                            return ui;
                        },
                    },
                    {
                        title: "売上金額",
                        dataIndx: "売上金額", dataType: "integer", format: "#,###",
                        width: 115, minWidth: 115, maxWidth: 115,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05100Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI05100Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05100Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI05100Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI05100Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05100Grid1_Print", disabled: true, shortcut: "F11",
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
            var grid = vue.DAI05100Grid1;

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
            var grid = vue.DAI05100Grid1;
            if (vue.viewModel.BushoOption == 0){
                grid.Group().option({
                    "dataIndx": ["ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    "showSummary": [true, true],
                    "collapsed": [false, false],
                });
            } else {
                grid.Group().option({ "dataIndx": ["部署名", "ＧＫ営業担当者", "ＧＫ獲得営業者"],
                    "showSummary": [true, true, true],
                    "collapsed": [false, false, false],
                });
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoCdChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05100Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            if (!vue.viewModel.SaveDateStart || !vue.viewModel.SaveDateEnd) return;

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //配達日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.SaveDateStart = params.SaveDateStart ? moment(params.SaveDateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.SaveDateEnd = params.SaveDateEnd ? moment(params.SaveDateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05100Grid1;
            console.log('filterChanged');

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.EigyoTantoCd) {
                rules.push({ dataIndx: "営業担当者ＣＤ", condition: "equal", value: vue.viewModel.EigyoTantoCd });
            }
            if (!!vue.viewModel.GetEigyoTantoCd) {
                rules.push({ dataIndx: "獲得営業者ＣＤ", condition: "equal", value: vue.viewModel.GetEigyoTantoCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI05100Grid1_CSV").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05100Grid1_Excel").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05100Grid1_Print").disabled = false;

            res.forEach(r => {
                    r.ＧＫ営業担当者 = r.営業担当者ＣＤ + " " + r.営業担当者名;
                    r.ＧＫ獲得営業者 = r.獲得営業者ＣＤ + " " + r.獲得営業者名;
                });
            return res;
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI05100Grid1;

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
                div.title > h2 {
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
                    font-size: 11pt;
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
                    height: 22px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
            `;

            var eigyoNmKey1;
            var eigyoNmKey2;
            var bushoNm;
            var headerFunc = (header, idx, length) => {

                if (vue.viewModel.BushoOption == 0) {
                    if (header.pq_level == 0) {
                        eigyoNmKey1 = header.ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].ＧＫ獲得営業者.split(" ")[1];
                        bushoNm = header.children[0].children[0].部署名;
                    }
                    if (header.pq_level == 1) {
                        eigyoNmKey2 = header.ＧＫ獲得営業者.split(" ")[1];
                        bushoNm = header.children[0].部署名;
                    }
                } else {
                    if (header.pq_level == 0) {
                        eigyoNmKey1 = header.children[0].ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].children[0].ＧＫ獲得営業者.split(" ")[1];
                        bushoNm = header.children[0].children[0].children[0].部署名;
                    }
                    if (header.pq_level == 1) {
                        eigyoNmKey1 = header.ＧＫ営業担当者.split(" ")[1];
                        eigyoNmKey2 = header.children[0].ＧＫ獲得営業者.split(" ")[1];
                        bushoNm = header.children[0].children[0].部署名;
                    }
                    if (header.pq_level == 2) {
                        eigyoNmKey2 = header.ＧＫ獲得営業者.split(" ")[1];
                        bushoNm = header.children[0].部署名;
                    }
                }

                //旧部署CDのため部署CD=nullのデータは帳票に部署なしとして出力する
                return `
                    <div class="title">
                        <h2>* * 顧客売上表 * *</h2>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr class="width-settei">
                                <th style="width: 12.5%;"></th>
                                <th style="width: 10.5%;"></th>
                                <th class="blank-cell" style="width: 5.8%;"></th>
                                <th class="blank-cell" style="width: 3%;"></th>
                                <th class="blank-cell" style="width: 7%;"></th>
                                <th class="blank-cell" style="width: 8.8%;"></th>
                                <th class="blank-cell" style="width: 8%;"></th>
                                <th class="blank-cell" style="width: 11%;"></th>
                                <th class="blank-cell" style="width: 5.6%;"></th>
                                <th class="blank-cell" style="width: 10%;"></th>
                                <th class="blank-cell" style="width: 9%;"></th>
                                <th class="blank-cell" style="width: 8%;"></th>
                            </tr>
                            <tr>
                                <th style="width: 12%;">${vue.viewModel.BushoOption != 0 ?
                                    (bushoNm == null ? "部署なし" : bushoNm ): ""}</th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>対象日付：</th>
                                <th colspan="2">${vue.viewModel.DateStart}</th>
                                <th>～</th>
                                <th colspan="2">${vue.viewModel.DateEnd}</th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th colspan="3" style="font-weight: bold;">[営業売上金額－担当者]</th>
                                <th style="text-align: right;">${idx + 1} / ${length}</th>
                            </tr>
                            <tr>
                                <th>営業担当者：</th>
                                <th colspan="2">${eigyoNmKey1}</th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th colspan="2">獲得営業担当者：</th>
                                <th colspan="2">${eigyoNmKey2}</th>
                                <th colspan="3" style="text-align: right;">作成日:${moment().format("YYYY/MM/DD/ HH:MM")}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI05100Grid1 tr:nth-child(1) th:nth-child(1),
                table.DAI05100Grid1 tr:nth-child(1) th:nth-child(2){
                   text-align: left;
                }
                table.DAI05100Grid1 tr:nth-child(1) th:nth-child(n+3){
                   text-align: right;
                }
                table.DAI05100Grid1 tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI05100Grid1 tr:not(.group-summary) + tr.group-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05100Grid1 tr.group-summary td:nth-child(2),
                table.DAI05100Grid1 tr[level="0"].group-summary td:nth-child(2),
                table.DAI05100Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 20px;
                    height: 25px;
                }
                table.DAI05100Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;
                }
                table.DAI05100Grid1 tr th:nth-child(1) {
                    width: 3.5%;
                }
                table.DAI05100Grid1 tr th:nth-child(2) {
                    width: 12.5%;
                }
                table.DAI05100Grid1 tr th:nth-child(3) {
                    width: 4%;
                }
                table.DAI05100Grid1 tr th:nth-child(4) {
                    width: 20%;
                }
                table.DAI05100Grid1 tr th:nth-child(n+4):nth-child(-n+12) {
                    width: 6%;
                }
                table.DAI05100Grid1 tr th:nth-child(13) {
                    width: 7%;
                }
                table.header-table th {
                    text-align: left;
                }
                tr.width-settei > th {
                    height: 0px;
                }
                table.DAI05100Grid1 tr td:nth-child(1) {
                    padding-right: 8px;;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-right: 15px !important;
                    margin-left: 15px !important;
                }
            `;

            var contents = grid.generateHtml(
                styleCustomers,
                headerFunc,
                36,
                false,
                true,
                true,
            );

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>").append(contents)
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 portrait; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
