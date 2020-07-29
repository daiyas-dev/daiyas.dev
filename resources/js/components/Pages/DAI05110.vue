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
                    format="YYYY年MM月"
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
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateEndChanged
                />
            </div>
        </div>
        <div class="row">
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
            <VueCheck
                id="VueCheck_Customer"
                ref="VueCheck_Customer"
                :vmodel=viewModel
                bind="Customer"
                checkedCode="1"
                customContainerStyle="margin-left: 50px; border: none;"
                :list="[
                    {code: '0', name: '全顧客', label: '新規獲得顧客のみ'},
                    {code: '1', name: '新規顧客', label: '新規獲得顧客のみ'},
                ]"
                :onChangedFunc=onCustomerChanged
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
            id="DAI05110Grid1"
            ref="DAI05110Grid1"
            dataUrl="/DAI05110/SearchB"
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
#DAI05110Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05110Grid1 .pq-group-icon {
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
    name: "DAI05110",
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
                BushoCd: vue.viewModel.BushoOption == 2 ? vue.viewModel.BushoCd : null,
                DateStart: moment(vue.viewModel.DateStart, "YYYY年MM月DD日").startOf("month").format("YYYYMMDD"),
                DateEnd: moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").endOf("month").format("YYYYMMDD"),
                Customer: vue.viewModel.Customer,
                ShowSyonin: vue.viewModel.ShowSyonin,
                BushoOption: vue.viewModel.BushoOption,
            };
        },
    },
    watch: {
        "DAI05110Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI05110Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 顧客売上累計表",
            noViewModel: true,
            viewModel: {
                BushoOption: "2",
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                Customer: "0",
                ShowSyonin: "0",
            },
            DAI05110Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: true,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 8,
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
                    dataIndx: ["部署名", "ＧＫ担当者"],
                    showSummary: [false, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: ["a", "b"
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
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
                        title: "担当者",
                        dataIndx: "ＧＫ担当者", dataType: "string",
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
                                return { text: "売上金額総合計\n新規客総合計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                switch (vue.viewModel.BushoOption) {
                                    case "0":
                                        switch (ui.rowData.pq_level) {
                                            case 0:
                                                return { text: "売上金額合計\n新規客計" };
                                            default:
                                                return { text: "" };
                                        }
                                        break;
                                    case "1":
                                        switch (ui.rowData.pq_level) {
                                            case 0:
                                            return { text: "売上金額部署計\n新規客部署計" };
                                            case 1:
                                                return { text: "売上金額合計\n新規客計" };
                                            default:
                                                return { text: "" };
                                        }
                                        break;
                                    case "2":
                                        switch (ui.rowData.pq_level) {
                                            case 0:
                                            return { text: "売上金額総合計\n新規総客計" };
                                            case 1:
                                                return { text: "売上金額合計\n新規客計" };
                                            default:
                                                return { text: "" };
                                        }
                                        break;
                                }
                            }
                            return { text:ui };
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
                { visible: "true", value: "検索", id: "DAI05110Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI05110Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05110Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI05110Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI05110Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05110Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日moment(vue.viewModel.TargetDate, "YYYYMM01").endOf('month')
            vue.viewModel.DateStart = moment("20190401").format("YYYY年MM月");
            vue.viewModel.DateEnd = moment("20190901").endOf('month').format("YYYY年MM月");

            vue.refreshCols();
        },
        onBushoOptionChanged: function(code, entities) {
            var vue = this;

            //グループキー切替
            var grid = vue.DAI05110Grid1;
            switch (vue.viewModel.BushoOption) {
                case "0":
                    grid.Group().option({
                        "dataIndx": ["ＧＫ担当者"],
                        "showSummary": [true],
                        "collapsed": [false],
                    });
                    break;
                case "1":
                    grid.Group().option({
                        "dataIndx": ["部署名", "ＧＫ担当者"],
                        "showSummary": [true, true],
                        "collapsed": [false, false],
                    });
                    break;
                case "2":
                    grid.Group().option({
                        "dataIndx": ["部署名", "ＧＫ担当者"],
                        "showSummary": [false, true],
                        "collapsed": [false, false],
                    });
                    break;
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

            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd){
                $.dialogErr({
                    title: "検索不可",
                    contents: "日付が入力されていません。",
                })
                return;
            }
            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (!!vue.viewModel.DateStart && !!vue.viewModel.DateEnd) {
                if (ms.isBefore(me, 'months') || (ms.year() == me.year() && ms.month() == me.month())) {
                    if (ms.diff(me, 'months') < -5) {
                        $.dialogErr({
                            title: "検索不可",
                            contents: "対象年月は6カ月以内で入力して下さい。",
                        })
                        return;
                    } else {
                        //列定義変更 + 条件変更ハンドラ
                        vue.refreshCols(vue.conditionChanged);
                    }
                } else {
                    $.dialogErr({
                        title: "検索不可",
                        contents: "範囲指定に誤りがあります。",
                    })
                    return;
                }
            }
        },
        onDateEndChanged: function(code, entity) {
            var vue = this;

            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd){
                $.dialogErr({
                    title: "検索不可",
                    contents: "日付が入力されていません。",
                })
                return;
            }

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (!!vue.viewModel.DateStart && !!vue.viewModel.DateEnd) {
                if (me.isAfter(ms, 'months') || (ms.year() == me.year() && ms.month() == me.month())) {
                    if (ms.diff(me, 'months') > 0 || ms.diff(me, 'months') < -5) {
                        $.dialogErr({
                            title: "検索不可",
                            contents: "対象年月は6カ月以内で入力して下さい。",
                        })
                        return;
                    } else {
                        //列定義変更 + 条件変更ハンドラ
                        vue.refreshCols(vue.conditionChanged);
                    }
                } else {
                    $.dialogErr({
                        title: "検索不可",
                        contents: "範囲指定に誤りがあります。",
                    })
                    return;
                }
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
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI05110Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05110Grid1;
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
            vue.footerButtons.find(v => v.id == "DAI05110Grid1_CSV").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05110Grid1_Excel").disabled = false;
            vue.footerButtons.find(v => v.id == "DAI05110Grid1_Print").disabled = false;

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月").startOf("month");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月").endOf("month");

            var groups = _(res)
                .groupBy(v => v.部署ＣＤ + "_" + v.得意先ＣＤ)
                .values()
                .map((g, i) => {
                    var ret = _.reduce(
                        g,
                        (a, v, i) => {
                            if (i == 0) {
                                a.部署ＣＤ = v.部署ＣＤ;
                                a.部署名 = v.部署名;
                                a.担当者ＣＤ = v.担当者ＣＤ;
                                a.営業担当者ＣＤ = v.営業担当者ＣＤ;
                                a.営業担当者名 = v.営業担当者名;
                                a.獲得営業者ＣＤ = v.獲得営業者ＣＤ;
                                a.獲得営業者名 = v.獲得営業者名;
                                a.得意先ＣＤ = v.得意先ＣＤ;
                                a.得意先名 = v.得意先名;

                                var mt = moment(v.新規登録日);
                                if (mt.isSameOrAfter(ms) && mt.isSameOrBefore(me)) {
                                    a["MONTH_" + mt.format("M") + "_新規客件数"] = 1;
                                    a["累計_新規客件数"] = 1;
                                }
                            }
                            a["MONTH_" + v.月 + "_金額"] = (a["MONTH_" + v.月 + "_金額"] || 0) + v.金額 * 1;
                            if (grid.options.colModel.map(c => c.dataIndx).includes("MONTH_" + v.月 + "_金額")) {
                                a["累計_金額"] = (a["累計_金額"] || 0) + v.金額 * 1;
                            }

                            return a;
                        },
                        {}
                    );

                    ret.ＧＫ担当者 = ret.営業担当者名 + " / " + ret.獲得営業者名;

                    return ret;
                })
                .value()
                ;
            console.log("5110", groups);
            return groups;

            res.forEach(r => {
                    ret.ＧＫ担当者 = ret.営業担当者名 + " / " + ret.獲得営業者名;
                });
            return res;
        },
        refreshCols: function(callback) {
            var vue = this;
            var grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid1 = vue.DAI05110Grid1;
                    if (!!grid1 && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid1);
                    }
                }, 100);
            })
            .then((grid1) => {
                grid1.showLoading();

                var ms = moment(vue.viewModel.DateStart, "YYYY年MM月");
                var me = moment(vue.viewModel.DateEnd, "YYYY年MM月");
                var r = me.diff(ms, "months") + 1;
                var months = _.range(0, r)
                    .map(v => moment(vue.viewModel.DateStart, "YYYY年MM月").startOf("month").add("month", v));

                var min = 6;
                months = months.length < min ? months.concat(_.range(0, months.length - min).fill(null)) : months;

                var newCols = grid1.options.colModel
                    .filter(v => !!v.fixed)
                    .concat(
                        ...months.map((m, i) => {
                            return {
                                title: !!m ? m.format("YYYY年MM月") : "<br>",
                                dataIndx: !!m ? ("MONTH_" + m.format("M") + "_金額") : ("empty" + i),
                                dataType: "integer",
                                format: "#,##0",
                                width: 90, maxWidth: 90, minWidth: 90,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (ui.rowData.pq_grandsummary || ui.rowData.pq_gsummary)
                                    {
                                        // hide zero
                                        // if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                        //     return { text: "" };
                                        // }

                                        //0表示
                                        if (ui.dataIndx.startsWith("empty")) {
                                            return { text: "" };
                                        }
                                        return (ui.rowData[ui.dataIndx] || 0)
                                            + "\n" + (ui.rowData[ui.dataIndx.replace("金額", "新規客件数")] || 0);
                                    } else {
                                        // hide zero
                                        if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                            return { text: "" };
                                        }
                                        return ui;
                                    }
                                },
                            }
                        })
                    );

                newCols.push(
                        ...months.map((m, i) => {
                            return {
                                title: !!m ? m.format("YYYY年MM月") : "<br>",
                                dataIndx: !!m ? ("MONTH_" + m.format("M") + "_新規客件数") : ("empty" + i),
                                dataType: "integer",
                                format: "#,##0",
                                hidden: true,
                                summary: {
                                    type: "TotalInt",
                                },
                            }
                        })
                    );

                newCols.push(...[
                    {
                        title: "累計",
                        dataIndx: "累計_金額", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowData.pq_grandsummary || ui.rowData.pq_gsummary)
                            {
                                // hide zero
                                if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                    return { text: "" };
                                }
                                return (ui.rowData[ui.dataIndx] || 0)
                                    + "\n" + (ui.rowData[ui.dataIndx.replace("金額", "新規客件数")] || 0);
                            } else {
                                // hide zero
                                if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                    return { text: "" };
                                }
                                return ui;
                            }
                        },
                    },
                    {
                        title: "備考", dataIndx: "備考", dataType: "string",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                ]);

                newCols.push(...[
                    {
                        title: "累計",
                        dataIndx: "累計_新規客件数", dataType: "integer", format: "#,###",
                        hidden: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ]);

                //列定義更新
                grid1.options.colModel = newCols;
                grid1.refreshCM();
                grid1.refresh();

                if (!!grid1) grid1.hideLoading();

                if (!!callback) callback();
            })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();
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
                    font-size: 10pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 15px;
                    text-align: center;
                }
                td {
                    height: 15px;
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
                        bushoNm = header.children[0].部署名;
                        eigyoNmKey1 = header.ＧＫ担当者.split(" / ")[0];
                        eigyoNmKey2 = header.ＧＫ担当者.split(" / ")[1];
                    }
                } else {
                    if (header.pq_level == 0) {
                        bushoNm = header.部署名;
                        eigyoNmKey1 = header.children[0].ＧＫ担当者.split(" / ")[0];
                        eigyoNmKey2 = header.children[0].ＧＫ担当者.split(" / ")[1];
                    }
                    if (header.pq_level == 1) {
                        bushoNm = header.children[0].部署名;
                        eigyoNmKey1 = header.ＧＫ担当者.split(" / ")[0];
                        eigyoNmKey2 = header.ＧＫ担当者.split(" / ")[1];
                    }
                }
                return `
                    <div class="title">
                        <h3>* * 顧客売上累計表 * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 28%;" class="blank-cell"></th>
                                <th style="width: 7%;">集計範囲</th>
                                <th style="width: 7.5%;">（ ${vue.viewModel.DateStart}</th>
                                <th style="width: 3%;">～</th>
                                <th style="width: 10.5%;">${vue.viewModel.DateEnd} ）</th>
                                <th style="width: 18%;" colspan="2" class="eigyouriage">[営業売上金額]</th>
                                <th style="width: 8%;" class="blank-cell"></th>
                                <th style="width: 7%; text-align: right;">${idx + 1} / ${length}</th>
                            </tr>
                            <tr>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th colspan="2">作成日： ${moment().format("YYYY/MM/DD HH:MM")}</th>
                            </tr>
                            <tr>
                                <th>${vue.viewModel.BushoOption != 0 ? bushoNm : ""}</th>
                                <th>営業担当者：</th>
                                <th colspan="3">${eigyoNmKey1}</th>
                                <th>獲得営業担当者：</th>
                                <th colspan="3">${eigyoNmKey2}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                div.title{
                    padding-bottom: 5px;
                }
                table.header-table tr th{
                    text-align: left;
                    height: 20px
                }
                table.DAI05110Grid1 tr:nth-child(1) th ,
                table.DAI05110Grid1 tr td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI05110Grid1 tr:nth-child(1) th:last-child ,
                table.DAI05110Grid1 tr td:last-child {
                    border-right-width: 1px;
                }
                table.DAI05110Grid1 tr:last-child td {
                    border-bottom-width: 1px;
                }
                table.DAI05110Grid1 tr th:nth-child(1) {
                    width: 5%;
                }
                table.DAI05110Grid1 tr th:nth-child(2) {
                    width: 16%;
                }
                table.DAI05110Grid1 tr th:nth-child(10) {
                    width: 18%;
                }
                table.DAI05110Grid1 tr.group-summary td:nth-child(2),
                table.DAI05110Grid1 tr.grand-summary td:nth-child(2) {
                    border-left-width: 0px;
                    text-align: center;
                    padding-right: 50px;
                }
                table.DAI05110Grid1 tr th:nth-child(n+3):nth-child(-n+9) {
                    font-weight: bold;
                }
                table.DAI05110Grid1 th,
                table.DAI05110Grid1 td {
                    font-size: 9pt;
                }
                /* 集計行内部罫線(空セル) */
                table.DAI05110Grid1 tr.group-summary td:empty,
                table.DAI05110Grid1 tr.grand-summary td:empty {
					padding: 0px;
                }
                table.DAI05110Grid1 tr.group-summary td:empty::before,
                table.DAI05110Grid1 tr.grand-summary td:empty::before {
					content: '';
					display: block;
					background: black;
					height: 1px;
					width: 100%;
                }
                /* 集計行内部罫線(内容有り) */
                table.DAI05110Grid1 tr.group-summary td:not(:empty),
                table.DAI05110Grid1 tr.grand-summary td:not(:empty) {
                	line-height: 16px;
                }
                table.DAI05110Grid1 tr.group-summary td:not(:empty)::before,
                table.DAI05110Grid1 tr.grand-summary td:not(:empty)::before {
					content: '';
					display: block;
					position: relative;
					top: 16px;
					left: -3px;
					background: black;
					height: 1px;
					width: calc(100% + 53px);
                }
                .eigyouriage {
                    font-weight: bold;
                    text-align: right !important;
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
                            vue.DAI05110Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                32,
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
