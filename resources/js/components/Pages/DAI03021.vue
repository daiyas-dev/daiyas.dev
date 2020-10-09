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
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>売掛期間</label>
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
                    id="DAI03021GridSummary"
                    ref="DAI03021GridSummary"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=this.gridSummaryOptions
                    :autoToolTipDisabled=true
                    classes="ml-0 mr-0"
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI03021GridMeisai"
            ref="DAI03021GridMeisai"
            dataUrl="/DAI03021/Search"
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
[pgid=DAI03021][isChild=true] .row {
    margin-left: 0px !important;
    margin-right: 0px !important;
}
[pgid=DAI03021] svg.pq-grid-overlay {
    display: block;
}
[pgid=DAI03021] .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
[pgid=DAI03021] .pq-grid-cell.plus-value {
    color: black;
}
[pgid=DAI03021] .pq-grid-cell.minus-value {
    color: red;
}
[pgid=DAI03021] .title-col {
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
    name: "DAI03021",
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
                vue.footerButtons.find(v => v.id == "DAI03021Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "売掛残高表(明細)",
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
            DAI03021GridMeisai: null,
            DAI03021GridSummary: null,
            gridSummaryOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                showHeader: true,
                autoRow: false,
                strNoRows: "",
                rowHt: 25,
                rowHtHead: 25,
                width: 530,
                height: 78,
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
                            "今月入金額": 0,
                            "差引繰越額": 0,
                            "今回売上額": 0,
                            "今月残高": 0,
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
                        dataIndx: "前月残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "今月入金額",
                        dataIndx: "今月入金額",
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
                        title: "今月売上額",
                        dataIndx: "今月売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        cautionNegative: true,
                    },
                    {
                        title: "今月残高",
                        dataIndx: "今月残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 120, minWidth: 100, maxWidth: 120,
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
                            var grid = vue.DAI03021GridMeisai;

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
                        width: 75, maxWidth: 75, minWidth: 75,
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
                { visible: "true", value: "再検索", id: "DAI03021Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI03021GridMeisai.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI03021Grid1_Detail").disabled = cnt == 0 || cnt > 1;
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
            console.log("3021 conditionChanged")

            if (!vue.DAI03021GridSummary || !vue.DAI03021GridMeisai || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.CustomerCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            if (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid) return;

            vue.DAI03021GridMeisai.searchData(vue.searchParams);
        },
        setGridMeisaiTitle: function (title, grid) {
            return "件数: " + (grid.pdata || []).filter(v => !!v.伝票日付).length;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;
            var gridSummary = vue.DAI03021GridSummary;
            var gridSeikyu = vue.DAI03021GridMeisai;
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
    }
}
</script>
