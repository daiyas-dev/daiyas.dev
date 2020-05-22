<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
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
                    :buddies='{ CustomerNm: "CdNm", Address: "住所１", Tel: "電話番号１", Fax: "ＦＡＸ１", Sime: "締日１" }'
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
                    :nameWidth=325
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
                <label class="text-center">締日</label>
                <input class="form-control p-0 text-center" style="width: 50px;" type="text" :value=viewModel.SimeDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-0 text-center w-100" type="text" :value=viewModel.TargetDate readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <label>請求期間</label>
            </div>
            <div class="col-md-5">
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
                <label style="text-align: center; margin-left: 5px; margin-right: 5px;">～</label>
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
            <div class="col-md-12">
                <PqGridWrapper
                    id="DAI02021GridSummary"
                    ref="DAI02021GridSummary"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=this.gridSummaryOptions
                    :autoToolTipDisabled=true
                    classes="ml-0 mr-0"
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI02021GridMeisai"
            ref="DAI02021GridMeisai"
            dataUrl="/DAI02021/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.gridMeisaiOptions
            :onAfterSearchFunc=this.onAfterSearchFunc
            :setCustomTitle=setGridMeisaiTitle
            :autoToolTipDisabled=true
            classes="ml-0 mr-0"
        />
    </form>
</template>

<style>
[pgid=DAI02021][isChild=true] .row {
    margin-left: 0px !important;
    margin-right: 0px !important;
}
[pgid=DAI02021] svg.pq-grid-overlay {
    display: block;
}
[pgid=DAI02021] .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
[pgid=DAI02021] .pq-grid-cell.plus-value {
    color: black;
}
[pgid=DAI02021] .pq-grid-cell.minus-value {
    color: red;
}
[pgid=DAI02021] .title-col {
    background-image: -webkit-linear-gradient(top, rgb(254, 254, 254), rgb(218, 230, 240));
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI02021",
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
                CustomerCd: vue.viewModel.CustomerCd,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                DateStart: moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYYMMDD"),
                DateEnd: moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD"),
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate || !newVal.CustomerCd;
                vue.footerButtons.find(v => v.id == "DAI02021Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "請求明細表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                CustomerNm: null,
                CourseCd: null,
                CourseNm: null,
                CourseKbn: null,
                TargetDate: null,
                SimeDate: null,
                DateStart: null,
                DateEnd: null,
            },
            IsChild: false,
            IsFirstFocus: false,
            DAI02021GridMeisai: null,
            DAI02021GridSummary: null,
            gridSummaryOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                showHeader: true,
                autoRow: false,
                strNoRows: "",
                rowHt: 25,
                rowHtHead: 25,
                width: 520,
                height: 52,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {
                            "前回請求残高": 0,
                            "今回入金額": 0,
                            "差引繰越額": 0,
                            "今回売上額": 0,
                            "今回請求額": 0,
                        }
                    ],
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextEdit",
                    onTab: "nextEdit",
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "前回請求残高",
                        dataIndx: "前回請求残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "今回入金額",
                        dataIndx: "今回入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "今回売上額",
                        dataIndx: "今回売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "今回請求額",
                        dataIndx: "今回請求額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                ],
            },
            gridMeisaiOptions: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHt: 25,
                rowHtHead: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
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
                        title: "伝票日付",
                        dataIndx: "伝票日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            var grid = vue.DAI02021GridMeisai;

                            if (ui.rowIndx > 0 && grid.pdata[ui.rowIndx - 1].伝票日付 == ui.rowData.伝票日付) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "伝票No",
                        dataIndx: "伝票Ｎｏ",
                        dataType: "integer",
                        width: 65, minWidth: 65, maxWidth: 65,
                        render: ui => {
                            if (!!ui.rowData.IsBikoRow) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ",
                        dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "【 合 計 】" };
                            }
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "数量", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "金額",
                        dataIndx: "金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: false,
                        render: ui => {
                            if (!!ui.rowData.伝票Ｎｏ) {
                                return { text: !!ui.rowData.IsBikoRow
                                    ? ""
                                    : pq.formatNumber(ui.rowData.入金金額, "#,##0")
                                };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });

        if (!!vue.params) {
            data.viewModel = $.extend(true, data.viewModel, vue.params);
            data.IsChild = true;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "再検索", id: "DAI02021Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: vue.params.IsSeikyuOutput ? "false" :"true", value: "明細入力", id: "DAI02021Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: vue.params.IsSeikyuOutput ? "false" : "true", value: "CSV", id: "DAI02021Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI02021GridMeisai.vue.exportData("csv", false, true);
                    }
                },
                { visible: vue.params.IsSeikyuOutput ? "true" : "false", value: "印刷", id: "DAI02021Grid1_Print", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").css("max-height", $(vue.$el).parents("div.body-content").css("height"));

            //watcher
            vue.$watch(
                "$refs.DAI02021GridMeisai.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI02021Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        activatedFunc: function(vue) {
            vue.IsChild = !!vue.params;
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            console.log("2021 conditionChanged")

            if (!vue.DAI02021GridSummary || !vue.DAI02021GridMeisai || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.CustomerCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            if (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid) return;

            vue.DAI02021GridMeisai.searchData(vue.searchParams);
        },
        setGridMeisaiTitle: function (title, grid) {
            return "件数: " + (grid.pdata || []).filter(v => !!v.伝票日付).length;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;
            var gridSummary = vue.DAI02021GridSummary;
            var gridSeikyu = vue.DAI02021GridMeisai;

            if (!res.length || !res[0].SeikyuData) {
                gridSummary.options.dataModel.data = [
                    {
                        "前回請求残高": 0,
                        "今回入金額": 0,
                        "差引繰越額": 0,
                        "今回売上額": 0,
                        "今回請求額": 0,
                    }
                ];
                gridSummary.refreshDataAndView();
            }

            var list = [];
            if (!!res.length) {
                list = _.flatten(
                    res[0].MeisaiList.map(v => {
                        if (!!v.伝票Ｎｏ) {
                            return [v, { "伝票Ｎｏ": v.伝票Ｎｏ, "商品名": v.備考, IsBikoRow: true }];
                        } else {
                            return v;
                        }
                    })
                );
                gridSummary.options.dataModel.data = [res[0].SeikyuData];
                gridSummary.refreshDataAndView();
            }

            vue.footerButtons.find(v => v.id == "DAI02021Grid1_CSV").disabled = !res.length;

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
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI02021GridMeisai;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            if (!!data.伝票Ｎｏ) {
                vue.showNyukin(data);
            } else {
                vue.showUriage(data);
            }
        },
        showUriage: function(data) {
            var vue = this;
            var grid = vue.DAI02021GridMeisai;

            var params = {
                IsSeikyu: true,
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: moment(data.伝票日付).format("YYYY年MM月DD日"),
                CourseKbn: data.コース区分,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                CustomerCd: vue.viewModel.CustomerCd,
                onSaveFunc: () => {
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
        showNyukin: function(data) {
            var vue = this;
            var grid = vue.DAI02021GridMeisai;

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment(data.伝票日付).format("YYYY年MM月DD日"),
                CustomerCd: vue.viewModel.CustomerCd,
                onSaveFunc: () => {
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
            var grid = vue.DAI02021GridMeisai;

            $(vue.$el).parents("div.body-content").css("max-height", $(vue.$el).parents("div.body-content").css("height"));

            vue.BushoInfo = vue.viewModel.BushoInfo;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div{
                    margin-bottom: 3px;
                }
                div.header{
                    font-family: "MS UI Gothic";
                    font-size: 10pt;
                    font-weight: normal;
                    justify-content: left;
                    width: 100%;
                }
                .header-title{
                	font-size: 18pt;
                	font-weight: bold;
                	letter-spacing: 16px;
                }
                .header-subtitle{
                	font-size: 14pt;
                	padding-bottom: 10px;
                }
                .header-seikyu-no{
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    margin-top: 3px;
                    padding-top: 3px;

                }
                span {
                    padding-left: 8px;
                }
                div.header-tokuisaki {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    padding-top: 12px;
                    padding-bottom: 12px;
                    margin-top: 3px;
                    margin-bottom: 3px;
                    font-size: 11pt;
                    font-weight: bold;
                }
                #a-box {
                    float: left;
                    width: 58%
                }
                #b-box {
                    float: left;
                    width: 20%;
                }
                #c-box {
                    float: left;
                    width: 22%;
                }
                #d-box {
                    float: left;
                    width: 50%;
                }
                #e-box {
                    width: 6%;
                }
                #f-box {
                    float: right;
                    width: 42%;
                }
                #g-box {
                    clear: both;
                    float: left;
                    width: 58%;
                    padding-top: 45px;
                    letter-spacing: 0.1em;
                }
                #h-box {
                    float: right;
                    width: 42%;
                }
                #i-box {
                    float: left;
                    width: 35%;
                }
                #j-box {
                    float: right;
                    width: 55%;
                }
                #k-box {
                    float: left;
                    width: 90%;
                    padding-bottom: 8px;
                }
                #l-box {
                    float: right;
                    width: 10%;
                    padding-bottom: 8px;
                    text-align: right;
                }
                #f-box > div{
                    padding-left: 14px;
                }
                #i-box {
                    padding-left: 14px;
                }
                table.header-table tbody th {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 12pt;
                    font-weight: normal;
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
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 21px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    width: 12%;
                }
                table.header-table tbody tr th {
                    text-align: right;
                    padding-right: 10px;
                }
                table.header-table thead th {
                    height: 18px;
                }
                table.header-table tbody th {
                    height: 24px;
                }
                table.header-table tr:first-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.header-table tr:first-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.header-table thead:first-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 3px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table thead:first-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 3px;
                    border-right-width: 2px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 3px;
                }
                table.header-table tr:last-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 2px;
                    border-bottom-width: 3px;
                }
            `;

            axios.post("/DAI02030/GetMeisaiList", { SeikyuNoArray: [vue.viewModel.Data.請求番号]})
            .then(res => {
                var group = _.groupBy(res.data, v => v.請求先ＣＤ);


                var meisaiGen;
                if (vue.viewModel.BushoCd == 501) {
                    meisaiGen = (r, pdata) => {
                        // var days = _.range(0, moment(r.請求日範囲終了).diff(moment(r.請求日範囲開始), "days") + 1)
                        //     .map(v => moment(r.請求日範囲開始).add(v, "days").format("D日(dd)"));
                        var days = _.range(-6, 1).map(v => moment(r.請求日範囲終了).add(v, "days").format("D日<br>(dd)"));

                        var target = _(pdata)
                            .groupBy(v => v.商品ＣＤ)
                            .values()
                            .map(g => {
                                return _.reduce(
                                    g,
                                    (a, v, i) => {
                                        if (i == 0) {
                                            a.商品 = v.商品名
                                        }

                                        days.forEach(date => {
                                            var d = moment(v.伝票日付).format("D日<br>(dd)");
                                            a[date] = (a[date] || 0) + (date == d ? v.数量 * 1 : 0);
                                        })

                                        a.食数 = (a.食数 || 0) + (v.数量 || 0) * 1;
                                        a.買上額 = (a.買上額 || 0) + (v.金額 || 0) * 1;
                                        a.入金額 = (a.入金額 || 0) + (v.入金金額 || 0) * 1;

                                        return a;
                                    },
                                    {}
                                );
                            })
                            .value()
                            ;

                        //空行補完
                        target.push(..._.range(0, 8 - target.length)
                            .map(v => {
                                var empty = {};
                                empty.商品 = null;

                                days.forEach(date => {
                                    empty[date] = null;
                                })

                                empty.食数 = null;
                                empty.買上額 = null;
                                empty.入金額 = null;

                                return empty;
                            })
                        );

                        //合計行
                        target.push(_.reduce(
                            pdata,
                            (a, v, i) => {
                                if (i == 0) {
                                    a.商品 = "合計";
                                }

                                days.forEach(date => {
                                    var d = moment(v.伝票日付).format("D日<br>(dd)");
                                    a[date] = (a[date] || 0) + (date == d ? v.数量 * 1 : 0);
                                })

                                a.食数 = (a.食数 || 0) + (v.数量 || 0) * 1;
                                a.買上額 = (a.買上額 || 0) + (v.金額 || 0) * 1;
                                a.入金額 = (a.入金額 || 0) + (v.入金金額 || 0) * 1;

                                return a;
                            },
                            { class: "grandsummary" }
                        ));

                        // 0 to null, value add comma
                        target.forEach(t => {
                            _.forIn(t, (v, k, o) => { o[k] = !!v ? (pq.formatNumber(v, "#,##0") || v) : null; })
                        });

                        return [target];
                    };
                } else {
                    meisaiGen = (r, pdata) => {
                        var target = [];

                        if (_.every(pdata, v => v.得意先ＣＤ == r.請求先ＣＤ || v.得意先ＣＤ == undefined)) {
                            var datas = _.cloneDeep(pdata);

                            var summary = _.reduce(
                                datas,
                                (a, v, k) => {
                                    a.商品名 = "【 合 計 】";
                                    a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                    a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                    a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                    return a;
                                },
                                {}
                            );
                            summary.class = "grandsummary";
                            datas.push(summary);
                            datas.forEach((v, i) => {
                                v.日付 = i == 0 || pdata[i - 1].日付 != v.日付 ? v.日付 : "";
                                v.数量 = pq.formatNumber(v.数量, "#,##0");
                                v.単価 = pq.formatNumber(v.単価, "#,##0");
                                v.金額 = pq.formatNumber(v.金額, "#,##0");
                                v.入金金額 = pq.formatNumber(v.入金金額, "#,##0");
                            });
                            target.push(datas);

                        } else {
                            var tg = _.groupBy(pdata, v => v.得意先ＣＤ);
                            var tk = _.sortBy(_.keys(tg), k => k != r.請求先ＣＤ ? 0 : 1);
                            var tsums = tk.map(k => {
                                var summary = _.reduce(
                                    tg[k],
                                    (a, v) => {
                                        a.商品名 = v.得意先名;
                                        a.区分 = "全";
                                        a.買上小計 = (a.買上小計 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                        a.入金小計 = (a.入金小計 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                        return a;
                                    },
                                    {}
                                );
                                summary.class = "tsums";
                                return summary;
                            });
                            var tgsum = _.reduce(
                                tsums,
                                (a, v) => {
                                    a.商品名 = "【 合 計 】";
                                    a.買上小計 = (a.買上小計 || 0) + v.買上小計;
                                    a.入金小計 = (a.入金小計 || 0) + v.入金小計;
                                    return a;
                                },
                                {}
                            );
                            tgsum.class = "tsums-grandsummary";
                            var sums = tsums.concat(tgsum);
                            sums.forEach((v, i) => {
                                v.買上小計 = pq.formatNumber(v.買上小計, "#,##0");
                                v.入金小計 = pq.formatNumber(v.入金小計, "#,##0");
                            });
                            target = [sums];

                            var tgmeisai = tk.map(k => _.cloneDeep(tg[k])).map((m, i) => {
                                var title = { "商品名": m[0].得意先名 };
                                var summary = _.reduce(
                                    m,
                                    (a, v, k) => {
                                        a.商品名 = "【 小 計 】";
                                        a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                        a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                        a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                        return a;
                                    },
                                    {}
                                );
                                summary.class = "gsummary";

                                m.unshift(title);
                                m.push(summary);

                                if (i == tk.length - 1) {
                                    console.log("gsum");
                                    var gsum = _.reduce(
                                        pdata,
                                        (a, v) => {
                                            a.商品名 = "【 合 計 】";
                                            a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                            a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                            a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                            return a;
                                        },
                                        {}
                                    );
                                    gsum.class = "grandsummary";
                                    m.push(gsum);
                                }
                                m.forEach((v, j) => {
                                    v.日付 = j == 0 || m[j - 1].日付 != v.日付 ? v.日付 : "";
                                    v.数量 = pq.formatNumber(v.数量, "#,##0");
                                    v.単価 = pq.formatNumber(v.単価, "#,##0");
                                    v.金額 = pq.formatNumber(v.金額, "#,##0");
                                    v.入金金額 = pq.formatNumber(v.入金金額, "#,##0");
                                });

                                return m;
                            });

                            target.push(...tgmeisai);
                        }

                        return target;
                    };
                }

                var contents = [vue.viewModel.Data].map(r => {
                    var pdata = group[r.請求先ＣＤ] || [{}];
                    var target = meisaiGen(r, pdata);

                    //501用レイアウト
                    var styleSeikyuMeisai501 =`
                        body > div > div:nth-of-type(even) > div[style='break-before: page;'] {
                            break-before: auto !important;
                        }
                        div#g-box {
                            padding-top: 0px !important;
                        }
                        div#h-box {
                            display: none;
                        }
                        .header-table th {
                            border-style: solid;
                            border-left-width: 0px;
                            border-top-width: 1px;
                            border-right-width: 1px;
                            border-bottom-width: 0px;
                        }
                        table.DAI02021GridMeisai tr th,
                        table.DAI02021GridMeisai tr td {
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 0px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02021GridMeisai tr th {
                            border-top-width: 1px;
                        }
                        table.DAI02021GridMeisai thead tr {
                            height: 25px;
                        }
                        table.DAI02021GridMeisai tbody tr {
                            height: 20px;
                        }
                        table.DAI02021GridMeisai tr > *:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02021GridMeisai thead tr th:nth-child(1) {
                            width: 16%;
                        }
                        table.DAI02021GridMeisai thead tr th:nth-child(n+2):nth-child(-n+9) {
                            width: 8%;
                        }
                        table.DAI02021GridMeisai thead tr th:nth-child(10),
                        table.DAI02021GridMeisai thead tr th:nth-child(11) {
                            width: 10%;
                        }
                        table.DAI02021GridMeisai tbody tr td:nth-child(1) {
                            text-align: left;
                        }
                        table.DAI02021GridMeisai tbody tr.grandsummary td:nth-child(1) {
                            text-align: center;
                        }
                        table.DAI02021GridMeisai tbody tr td:nth-child(n+2){
                            text-align: right;
                        }
                    `;
                    var styleSeikyuMeisaiElse =`
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
                        table.DAI02021GridMeisai tr:nth-child(1) th {
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 1px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02021GridMeisai tr th:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02021GridMeisai tr td {
                            border-style: solid;
                            border-left-width: 1px;
                            border-top-width: 0px;
                            border-right-width: 0px;
                            border-bottom-width: 1px;
                        }
                        table.DAI02021GridMeisai tr td:nth-child(1),
                        table.DAI02021GridMeisai tr td:nth-child(2) {
                            text-align: center;
                        }
                        table.DAI02021GridMeisai tr td:nth-child(4),
                        table.DAI02021GridMeisai tr td:nth-child(5),
                        table.DAI02021GridMeisai tr td:nth-child(6),
                        table.DAI02021GridMeisai tr td:nth-child(7) {
                            text-align: right;
                        }
                        table.DAI02021GridMeisai tr.gsummary td:nth-child(3),
                        table.DAI02021GridMeisai tr.grandsummary td:nth-child(3) {
                            text-align: center;
                        }
                        table.DAI02021GridMeisai tr td:last-child {
                            border-right-width: 1px;
                        }
                        table.DAI02021GridMeisai tbody tr {
                            height: 25px;
                        }
                        th:first-child:nth-last-child(8),
                        th:first-child:nth-last-child(8) ~ th:nth-child(2) {
                            width: 7.0%;
                        }
                        th:first-child:nth-last-child(8) ~ th:nth-child(3),
                        th:first-child:nth-last-child(8) ~ th:nth-child(8) {
                            width: 25.0%;
                        }
                        th:first-child:nth-last-child(8) ~ th:nth-child(4),
                        th:first-child:nth-last-child(8) ~ th:nth-child(5) {
                            width: 8.0%;
                        }
                        th:first-child:nth-last-child(5) {
                            width: 40.0%;
                        }
                        th:first-child:nth-last-child(5) ~ th:nth-child(2) {
                            width: 8.0%;
                        }
                        th:first-child:nth-last-child(5) ~ th:nth-child(5) {
                            width: 22.0%;
                        }
                        tr.tsums td:nth-child(1){
                            text-align: left !important;
                        }
                        tr.tsums td:nth-child(3),
                        tr.tsums td:nth-child(4){
                            text-align: right !important;
                        }
                        tr.tsums-grandsummary td:nth-child(3){
                            text-align: right;
                        }
                        tr.tsums td:nth-child(5),
                        tr.tsums-grandsummary td:nth-child(5){
                            text-align: start !important;
                        }
                        tr.tsums-grandsummary td:nth-child(2){
                            border-left-width: 0;
                        }
                    `;

                    var styleSeikyuMeisai = vue.viewModel.BushoCd == 501 ? styleSeikyuMeisai501 : styleSeikyuMeisaiElse;


                    var maxPage = _.sum(target.map(t => _.chunk(t, 25).length));
                    var htmls = target.map((json, tIdx) => {

                        var headerFunc = (header, idx, length, chunk, chunks) => {
                            return `
                                <div class="header">
                                    <div>
                                        <div id="k-box">
                                            ｺｰﾄﾞNo.${r.請求先ＣＤ}
                                            <span/>-${r.コースＣＤ != 0 ? r.コースＣＤ : ""}
                                            <span/>(
                                            <span/>${r.締日１}
                                            <span>- ${r.支払サイト}</span>
                                            <span>- ${r.支払日}</span>
                                            )
                                        </div>
                                        <div id="l-box">
                                            ${tIdx + idx + 1}
                                            /
                                            <span/>${maxPage}
                                        </div>
                                    </div>
                                    <div>
                                        <div id="a-box">
                                            </br></br>
                                            <div style="margin-bottom: 8px;">
                                                <span/>〒
                                                <span/>${r.郵便番号}
                                            </div>
                                            <div>
                                                ${r.住所１}
                                            </div>
                                            <br>
                                        </div>
                                        <div id="b-box">
                                            <div class="header-title">
                                                請求書
                                            </div>
                                            <div class="header-subtitle">
                                                (軽減税率対象)
                                            </div>
                                            <div style="margin-bottom: 8px;">
                                                株式会社<span/>ダイヤス食品
                                            </div>
                                        </div>
                                        <div id="c-box">
                                            <div>
                                                <span style="white-space: pre;">${moment(r.請求日付).format("  YY  年  MM  月  DD  日")}</span>
                                            </div>
                                            <div class="header-seikyu-no">
                                                <span/>請求番号
                                                <span/>${r.請求番号}
                                            </div>
                                        </div>
                                    <div>
                                    <div style="clear: both;">
                                        <div id="d-box">
                                            <div class="header-tokuisaki">
                                                ${r.得意先名}
                                                <span>様
                                            </div>
                                            <div>
                                                Tel
                                                <span/><span/>${r.電話番号１ || ""}
                                                <span/><span/>Fax
                                                <span/><span/>${r.ＦＡＸ１ || ""}
                                            </div>
                                            </br>
                                        </div>
                                        <div id="e-box">
                                        </div>
                                        <div id="f-box">
                                            <div>
                                                <span/>〒
                                                <span/>${vue.BushoInfo.郵便番号}
                                            </div>
                                            <div>
                                                ${vue.BushoInfo.住所}
                                            </div>
                                            <div>
                                                Tel
                                                <span/><span/>${vue.BushoInfo.電話番号}
                                            </div>
                                            <div>
                                                Fax
                                                <span/><span/>${vue.BushoInfo.FAX || ""}
                                            </div>
                                        </div>
                                        <div id="g-box">
                                            <div style="margin-bottom: 8px;">
                                                毎度ありがとうございます。
                                            </div>
                                            <div>
                                                下記の通りご請求申し上げます。
                                            </div>
                                        </div>
                                        <div id="h-box">
                                            <div style="margin-bottom: 8px;">
                                                取引金融機関
                                            </div>
                                            <div id="i-box">
                                                <div>
                                                    ${vue.BushoInfo.金融機関1名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座種別1名称}
                                                    <span/><span/>${vue.BushoInfo.口座番号1}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.金融機関2名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座種別2名称}
                                                    <span/><span/>${vue.BushoInfo.口座番号2}
                                                </div>
                                            </div>
                                            <div id="j-box">
                                                <div>
                                                    <span/>${vue.BushoInfo.金融機関支店1名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座名義人1}
                                                </div>
                                                <div>
                                                    <span/>${vue.BushoInfo.金融機関支店2名称}
                                                </div>
                                                <div>
                                                    ${vue.BushoInfo.口座名義人1}
                                            </div>
                                        </div>
                                    </div>
                                <table class="header-table" style="border-width: 0px; margin-bottom: 20px;">
                                    <thead>
                                        <tr>
                                            <th>前回請求額</th>
                                            <th>御入金額</th>
                                            <th>繰越金額</th>
                                            <th>御買上金額</th>
                                            <th>消費税</th>
                                            <th>今回請求額</th>
                                        </tr>
                                        <tr>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.前回請求残高, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回入金額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.差引繰越額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回売上額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.消費税額, "#,##0") : ""}</th>
                                        <th>${tIdx + idx == "0" ? pq.formatNumber(r.今回請求額, "#,##0") : ""}</th>
                                    </tbody>
                                </table>
                                </div>
                            `;
                        };

                        var html = grid.generateHtmlFromJson(
                            json,
                            styleSeikyuMeisai,
                            headerFunc,
                            25,
                            true,
                            vue.viewModel.BushoCd == 501, //false,
                            vue.viewModel.BushoCd == 501 ? null :
                            tIdx == 0 && target.length > 1
                                ? [
                                    "商品名",
                                    "区分",
                                    "買上小計",
                                    "入金小計",
                                    "備考",
                                ]
                                : [
                                    "日付",
                                    "食事区分名",
                                    "商品名",
                                    "数量",
                                    "単価",
                                    "金額",
                                    "入金金額",
                                    "備考",
                                ],
                            vue.viewModel.BushoCd == 501 ? null :
                            tIdx == 0 && target.length > 1
                                ? [
                                    "商品名称",
                                    "区分",
                                    "買上小計",
                                    "入金小計",
                                    "備考",
                                ]
                                : [
                                    "月日",
                                    "区分",
                                    "商品名称",
                                    "食数",
                                    "単価",
                                    "買上額",
                                    "入金額",
                                    "備考",
                                ],
                        );

                        return html;
                    })
                    .map(v => $(v.get(0)).prop("outerHTML"))
                    .join("")
                    ;
                    console.log("htmls", htmls);
                    return htmls;
                });

                var printable = $("<html>")
                    .append($("<head>").append($("<style>").text(globalStyles)))
                    .append(
                        $("<body>")
                            .append(contents)
                    )
                    .prop("outerHTML")
                    ;

                var printOptions = {
                    type: "raw-html",
                    style: vue.viewModel.BushoCd == 501 ? "@media print { @page { size: A4; margin: 15px 20px; } }" : "@media print { @page { size: A4; } }",
                    printable: printable,
                };

                printJS(printOptions);
                //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
                //$("#printJS").contents().find("html").html()
            })
            .catch(err => {
                console.log(err);
                $.dialogErr({
                    title: "印刷失敗",
                    contents: "請求明細の検索に失敗しました" + "<br/>" + err.message,
                });
            });
        },
    }
}
</script>
