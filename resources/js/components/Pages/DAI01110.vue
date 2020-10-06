<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-12">
                <label>部署</label>
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                />
                <label>日付</label>
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
                <label>コース</label>
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
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
                    :inputWidth=75
                    :nameWidth=225
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
                <label>担当者</label>
                <PopupSelect
                    id="User"
                    ref="PopupSelect_User"
                    :vmodel=viewModel
                    bind="TantoCd"
                    :buddies='{ TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetTantoList"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者ID"
                    labelCdNm="担当者名"
                    :showColumns='[{ title: "部署名", dataIndx: "部署.部署名", dataType: "string", width: 200 }]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=75
                    :nameWidth=150
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <PqGridWrapper
                    id="DAI01110GridIdou"
                    ref="DAI01110GridIdou"
                    :options=this.gridIdouOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :onAfterSearchFunc=this.onAfterSearchFunc
                    classes="ml-0 mr-0 mt-1"
                />
            </div>
            <div class="col-md-4 d-block" style="align-items: baseline;">
                <PqGridWrapper
                    id="DAI01110GridTicketSummary"
                    ref="DAI01110GridTicketSummary"
                    :options=this.gridTicketSummaryOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :onAfterSearchFunc=this.onAfterSearchFunc
                    classes="ml-0 mr-0 mt-1"
                />
                <div  v-show="viewModel.IsSaved" class="w-100 mt-4">
                    <label class="label-blue" style="width: inherit; text-align: left;">修正者　： {{viewModel.LastEditor}}</label>
                    <label class="label-blue" style="width: inherit; text-align: left;">修正日時： {{viewModel.LastEditDate}}</label>
                    <label class="label-blue" style="color: red; width: inherit; text-align: left;">精算済みですので、モバイルからの更新は出来ません</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9" style="display: block;">
                <div class="row">
                    <div class="col-md-12">
                        <PqGridWrapper
                            id="DAI01110GridCash"
                            ref="DAI01110GridCash"
                            :options=this.gridCashOptions
                            :autoToolTipDisabled=true
                            :SearchOnCreate=false
                            :SearchOnActivate=false
                            :onAfterSearchFunc=this.onAfterSearchFunc
                            classes="ml-0 mr-0 mt-1"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <PqGridWrapper
                            id="DAI01110GridCredit"
                            ref="DAI01110GridCredit"
                            :options=this.gridCreditOptions
                            :autoToolTipDisabled=true
                            :SearchOnCreate=false
                            :SearchOnActivate=false
                            :onAfterSearchFunc=this.onAfterSearchFunc
                            classes="ml-0 mr-0 mt-1"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <PqGridWrapper
                            id="DAI01110GridSummary"
                            ref="DAI01110GridSummary"
                            :options=this.gridSummaryOptions
                            :autoToolTipDisabled=true
                            :SearchOnCreate=false
                            :SearchOnActivate=false
                            :onAfterSearchFunc=this.onAfterSearchFunc
                            classes="ml-0 mr-0 mt-1"
                        />
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-block">
                <PqGridWrapper
                    id="DAI01110GridMoneyInfo"
                    ref="DAI01110GridMoneyInfo"
                    :options=this.gridMoneyInfoOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle=setGridMoneyInfoTitle
                    classes="ml-5 mr-0 mt-1 mb-0"
                />
                <PqGridWrapper
                    id="DAI01110GridTicket"
                    ref="DAI01110GridTicket"
                    :options=this.gridTicketOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle=setGridTicketTitle
                    classes="ml-5 mr-0 mt-1 mb-0"
                />
                <PqGridWrapper
                    id="DAI01110GridBV"
                    ref="DAI01110GridBV"
                    :options=this.gridBVOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle=setGridBVTitle
                    classes="ml-5 mr-0 mt-1 mb-0"
                />
            </div>
        </div>
    </form>
</template>

<style>
[pgid=DAI01110] div.pq-theme * {
    font-size: 14px;
}
[pgid=DAI01110] .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner):not(.cell-disabled),
[pgid=DAI01110] .pq-grid-cell.summary-cell
{
    font-weight: bold;
    color: black;
    background-color: white !important;
}
[pgid=DAI01110] .pq-grid-cell.plus-value {
    color: black;
}
[pgid=DAI01110] .pq-grid-cell.minus-value {
    color: red;
}
[pgid=DAI01110] .pq-td-div span {
    line-height: 1;
    display: block;
    text-align: center;
}
[pgid=DAI01110] .title-col {
    background-image: -webkit-linear-gradient(top, rgb(254, 254, 254), rgb(218, 230, 240));
}
[pgid=DAI01110] .pq-cell-editor {
    height: 21px;
}
[pgid=DAI01110] .pq-align-center-force {
    justify-content: center !important;
}

[pgid=DAI01110] .pq-cont-inner.pq-cont-right {
    overflow-x: hidden !important;
}
</style>
<style scoped>
label{
    width: 60px;
}
.row:not(:first-child) {
    margin-top: 0px;
    margin-bottom: 1px;
}
.row .row {
    margin-left: 0px;
    margin-right: 0px;
}
.row label:not(:first-child) {
    text-align: center;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01110",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                TantoCd: this.viewModel.TantoCd,
                noCache: true,
            };
        }
    },
    watch: {
        "viewModel.IsSaved": {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI01110_Delete").disabled = !newVal;
            },
        },
        "viewModel.CourseCd": {
            handler: function(newVal) {
                var vue = this;
                if(!!newVal){
                    var cd = DAI01110.$refs.PopupSelect_Course.selectRow.担当者ＣＤ;
                    DAI01110.$refs.PopupSelect_User.setSelectValue(cd);
                }
            },
        },
    },
    data() {
        var vue = this;

        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 売上精算入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                IsSaved: false,
                LastEditor: null,
                LastEditDate: null,
                TantoCd: null,
            },
            CheckInterVal: null,
            ProductList: [],
            DAI01110GridIdou: null,
            DAI01110GridTicketSummary: null,
            DAI01110GridCash: null,
            DAI01110GridCredit: null,
            DAI01110GridSummary: null,
            DAI01110GridMoneyInfo: null,
            DAI01110GridTicket: null,
            DAI01110GridBV: null,
            gridIdouOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 820,
                height: 155,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                    render: ui => {
                        if (!ui.cls) ui.cls = [];
                        ui.cls.push(ui.cellData < 0 ? "minus-value" : "plus-value");

                        return { text: !!ui.Export || ui.cellData != 0 ? ui.cellData : "" };
                    },
                },
                dataModel: {
                    location: "local",
                    data: [],
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
                        title: "工場区分",
                        dataIndx: "工場区分",
                        hidden: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ",
                        hidden: true,
                    },
                    {
                        title: "商品",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 125, maxWidth: 125, minWidth: 125,
                    },
                    {
                        title: "持出個数",
                        dataIndx: "持ち出し数",
                        dataType: "integer",
                        format: "#,###",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "工場追加",
                        dataIndx: "受取数_工場",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "もらった",
                        dataIndx: "受取数_一般",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "あげた",
                        dataIndx: "引渡数",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "ボーナス他",
                        dataIndx: "ボーナス販売数",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "残数",
                        dataIndx: "残数",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "合計",
                        dataIndx: "合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                ],
            },
            gridTicketSummaryOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 420,
                height: 41,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [{チケット精算: "チケット", "持出枚数": 0, "持帰枚数": 0, "売り枚数": 0, "売上金額": 0 }],
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
                        title: "チケット精算",
                        dataIndx: "チケット精算",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "持出枚数",
                        dataIndx: "持出枚数",
                        dataType: "integer",
                        format: "#,###",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "持帰枚数",
                        dataIndx: "持帰枚数",
                        dataType: "integer",
                        format: "#,###",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "売り枚数",
                        dataIndx: "売り枚数",
                        dataType: "integer",
                        format: "#,###",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "売上金額",
                        dataIndx: "売上金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                ],
            },
            gridCashOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                rowHtSum: 20,
                width: 945,
                height: 193,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [],
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    headerMenu: false,
                    grandSummary: true,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                    [
                        "本日現金入金",
                        function(rowData) {
                            return (rowData.現金金額 || 0)
                                - (rowData.現金売値引金額 || 0)
                                + (rowData.その他現金 || 0)
                                + (rowData.売掛入金 || 0)
                                + (rowData.チケット代入金 || 0);
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "①現金売上金額",
                        width: 300, maxWidth: 300, minWidth: 300,
                        colModel:[
                            {
                                title: "商品",
                                dataIndx: "商品名",
                                dataType: "string",
                                width: 125, maxWidth: 125, minWidth: 125,
                                render: ui => {
                                    if (ui.rowData.pq_grandsummary) {
                                        ui.rowData["商品名"] = "現金売合計";
                                        if (!ui.cls) ui.cls = [];
                                        ui.cls.push("pq-align-right");
                                        return { text: "現金売合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "個数",
                                dataIndx: "現金個数",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "現金金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ]
                    },
                    {
                        title: "②現金売値引金額",
                        colModel:[
                            {
                                title: "件数",
                                dataIndx: "現金売値引件数",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                // summary: {
                                //     type: "TotalInt",
                                // },
                                render: ui => {
                                    if (ui.rowData.pq_grandsummary) {
                                        ui.rowData["現金売値引件数"] = "値引額合計";
                                        return { text: "値引額合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "現金売値引金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ]
                    },
                    {
                        title: "③その他",
                        dataIndx: "その他現金",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                if (!!ui.Export) {
                                    return { text: ""};
                                } else {
                                    ui.cls.push("cell-disabled");
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "④売掛入金",
                        dataIndx: "売掛入金",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowIndx == 0 && !!ui.rowData.売掛入金件数) {
                                return { text: ui.rowData.売掛入金件数 + "件分"};
                            }
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                if (!!ui.Export) {
                                    return { text: ""};
                                } else {
                                    ui.cls.push("cell-disabled");
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "売掛入金件数",
                        dataIndx: "売掛入金件数",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "⑤チケット代入金",
                        dataIndx: "チケット代入金",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!!ui.Export) {
                                    return { text: ""};
                                } else {
                                    ui.cls.push("cell-disabled");
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "本日現金入金",
                        colModel: [
                            {
                                title: "①-②+③+④+⑤",
                                dataIndx: "本日現金入金",
                                dataType: "integer",
                                format: "#,###",
                                width: 125, maxWidth: 125, minWidth: 125,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                        if (!!ui.Export) {
                                            return { text: ""};
                                        } else {
                                            ui.cls.push("cell-disabled");
                                        }
                                    }
                                    return ui;
                                },
                            },
                        ],
                    },
                ],
            },
            gridCreditOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                rowHtSum: 20,
                width: 945,
                height: 193,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [],
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    headerMenu: false,
                    grandSummary: true,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "⑥掛売売上金額(掛)",
                        width: 300, maxWidth: 300, minWidth: 300,
                        colModel:[
                            {
                                title: "商品",
                                dataIndx: "商品名",
                                dataType: "string",
                                width: 125, maxWidth: 125, minWidth: 125,
                                render: ui => {
                                    if (ui.rowData.pq_grandsummary) {
                                        ui.rowData["商品名"] = "掛売合計";
                                        if (!ui.cls) ui.cls = [];
                                        ui.cls.push("pq-align-right");
                                        return { text: "掛売合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "個数",
                                dataIndx: "掛売個数",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "掛売金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ]
                    },
                    {
                        title: "⑦掛売値引金額(掛)",
                        colModel:[
                            {
                                title: "件数",
                                dataIndx: "掛売値引件数",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                // summary: {
                                //     type: "TotalInt",
                                // },
                                render: ui => {
                                    if (ui.rowData.pq_grandsummary) {
                                        ui.rowData["掛売値引件数"] = "値引額合計";
                                        return { text: "値引額合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "掛売値引金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ]
                    },
                    {
                        title: "⑧その他",
                        dataIndx: "その他掛売",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                if (!!ui.Export) {
                                    return { text: ""};
                                } else {
                                    ui.cls.push("cell-disabled");
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "",
                        dataIndx: "",
                        width: 100, maxWidth: 100, minWidth: 100,
                        cls: "cell-disabled",
                    },
                    {
                        title: "",
                        dataIndx: "",
                        width: 100, maxWidth: 100, minWidth: 100,
                        cls: "cell-disabled",
                    },
                    {
                        title: "本日掛売未収",
                        dataIndx: "本日掛売未収",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "本日掛売掛",
                        dataIndx: "本日掛売掛",
                        dataType: "integer",
                        format: "#,###",
                        hidden: true,
                    },
                    {
                        title: "本日掛売売上",
                        colModel: [
                            {
                                title: "⑥-⑦+⑧",
                                dataIndx: "本日掛売売上",
                                dataType: "integer",
                                format: "#,###",
                                width: 125, maxWidth: 125, minWidth: 125,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (!!ui.rowData.pq_grandsummary) return ui;
                                    switch(ui.rowIndx) {
                                        case 0:
                                        case 1:
                                            return {text: ""};
                                        case 2:
                                            _.remove(ui.cls, v => v == "pq-align-right")
                                            if (!ui.cls) ui.cls = [];
                                            ui.cls.push("summary-cell");
                                            return {text: "本日未集金"};
                                        case 3:
                                            var g = eval("this");
                                            var val = _.sum(g.pdata.map(v => v.本日掛売未収));
                                            return {text: pq.formatNumber(val, "#,###")};
                                        case 4:
                                            _.remove(ui.cls, v => v == "pq-align-right")
                                            if (!ui.cls) ui.cls = [];
                                            ui.cls.push("summary-cell");
                                            return {text: "本日掛売上"};
                                        case 5:
                                            var g = eval("this");
                                            var val = _.sum(g.pdata.map(v => v.本日掛売掛));
                                            return {text: pq.formatNumber(val, "#,###")};
                                        case 6:
                                            _.remove(ui.cls, v => v == "pq-align-right")
                                            if (!ui.cls) ui.cls = [];
                                            ui.cls.push("summary-cell");
                                            return {text: "本日掛合計"};
                                    }

                                    return ui;
                                },
                            },
                        ],
                    },
                ],
            },
            gridSummaryOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 945,
                height: 60,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [],
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
                    grandSummary: true,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                    [
                        "本日実質総売上額",
                        function(rowData) {
                            return rowData["本日総売上額"] - rowData["総値引金額"] + rowData["その他"] + rowData["チケット"] + rowData["バークレー"];
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "⑨本日総売上額",
                        colModel: [
                            {
                                title: "①+⑥",
                                dataIndx: "本日総売上額",
                                dataType: "integer",
                                format: "#,###",
                                width: 300, maxWidth: 300, minWidth: 300,
                                cls: "summary-cell",
                            },
                        ],
                    },
                    {
                        title: "⑩総値引金額",
                        colModel: [
                            {
                                title: "②+⑦",
                                dataIndx: "総値引金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 200, maxWidth: 200, minWidth: 200,
                                cls: "summary-cell",
                            },
                        ],
                    },
                    {
                        title: "⑪その他",
                        colModel: [
                            {
                                title: "③+⑧",
                                dataIndx: "その他",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                cls: "summary-cell",
                            },
                        ],
                    },
                    {
                        title: "⑫チケット",
                        dataIndx: "チケット",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        cls: "summary-cell",
                    },
                    {
                        title: "⑬バークレー",
                        dataIndx: "バークレー",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        cls: "summary-cell",
                    },
                    {
                        title: "本日実質総売上額",
                        colModel: [
                            {
                                title: "⑨-⑩+⑪+⑫+⑬",
                                dataIndx: "本日実質総売上額",
                                dataType: "integer",
                                format: "#,###",
                                width: 125, maxWidth: 125, minWidth: 125,
                                cls: "summary-cell",
                            },
                        ],
                    },
                ],
            },
            gridMoneyInfoOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: true,
                showHeader: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 245,
                height: 299,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {kind: 10000, value: 0},
                        {kind: 5000, value: 0},
                        {kind: 2000, value: 0},
                        {kind: 1000, value: 0},
                        {kind: 500, value: 0},
                        {kind: 100, value: 0},
                        {kind: 50, value: 0},
                        {kind: 10, value: 0},
                        {kind: 5, value: 0},
                        {kind: 1, value: 0},
                        {kind: "", value: 0},
                        {kind: "小切手", value: 0},
                        {kind: "領収書", value: 0, value2: "過剰金"},
                        {kind: "合計", value: 0, value2: 0},
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
                        title: "金銭明細書",
                        colModel: [
                            {
                                title: "種別",
                                dataIndx: "kind",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                cls: "title-col",
                                editable: false,
                                render: ui => {
                                    if (!_.isInteger(ui.rowData[ui.dataIndx]) && ui.cellData != "") {
                                        if (!ui.cls) ui.cls = [];
                                        ui.cls.push("pq-align-center-force");
                                    }

                                    return ui;
                                }
                            },
                            {
                                title: "数量",
                                dataIndx: "value",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: ui => {
                                    return  ui.rowData.kind != "合計";
                                },
                                render: ui => {
                                    if (ui.rowData.kind != "合計") return ui;

                                    var grid = eval("this");
                                    if (!grid.pdata || !grid.pdata.length) return ui;
                                    var sum = _.sum(grid.pdata.filter(v => v.kind != "合計").map(v => v.value * 1));
                                    ui.rowData.value = sum;
                                    return { text: sum };
                                }
                            },
                            {
                                title: "過剰金",
                                dataIndx: "value2",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: false,
                                render: ui => {
                                    if (ui.cellData == "過剰金") {
                                        if (!ui.cls) ui.cls = [];
                                        ui.cls.push("pq-align-center-force");
                                        ui.cls.push("title-col");
                                    } else if (ui.rowData.kind != "合計") {
                                        if (!ui.cls) ui.cls = [];
                                        ui.cls.push("cell-disabled");
                                    } else {
                                        var grid = eval("this");
                                        if (!grid.pdata || !grid.pdata.length) return ui;
                                        var sum = _.sum(grid.pdata.filter(v => v.kind != "合計").map(v => v.value * 1));

                                        var val = 0;
                                        if (sum > 0) {
                                            val = sum - pq.deFormatNumber(vue.DAI01110GridCash.options.summaryData[0].本日現金入金);
                                        }

                                        ui.rowData.value2 = val;
                                        return { text: pq.formatNumber(val, "#,###") };
                                    }

                                    return ui;
                                }
                            },
                        ],
                    },
                ],
            },
            gridTicketOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: true,
                showHeader: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 170,
                height: 71,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {kind: "チケット", value: 0},
                        {kind: "ボーナス", value: 0},
                    ],
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
                        title: "チケット",
                        colModel: [
                            {
                                title: "種別",
                                dataIndx: "kind",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                cls: "title-col",
                            },
                            {
                                title: "枚数",
                                dataIndx: "value",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                            },
                        ],
                    },
                ],
            },
            gridBVOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: true,
                showHeader: false,
                autoRow: false,
                strNoRows: "",
                rowHt: 19,
                rowHtHead: 19,
                width: 245,
                height: 71,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {price: 300, value: 0},
                        {price: 200, value: 0},
                    ],
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
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
                    [
                        "summary",
                        function(rowData){
                            return rowData.price * (rowData.value || 0);
                        }
                    ]
                ],
                colModel: [
                    {
                        title: "バークレーヴァウチャーズチケット(弁当用)",
                        colModel: [
                            {
                                title: "単価",
                                dataIndx: "price",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                cls: "title-col",
                                editable: false,
                            },
                            {
                                title: "枚数",
                                dataIndx: "value",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: true,
                            },
                            {
                                title: "金額",
                                dataIndx: "summary",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: false,
                            },
                        ],
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "削除", id: "DAI01110_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.delete();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI01110_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(null, true);
                    }
                },
                {visible: "false"},
                // { visible: "true", value: "金銭明細入力", id: "DAI01110_MoneyDetail", disabled: false, shortcut: "F6",
                //     onClick: function () {
                //         vue.showMoneyDetail();
                //     }
                // },
                { visible: "true", value: "チケット<br>明細表示", id: "DAI01110_TicketDetail", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.showTicketDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01110_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01110_Printout", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            if (vue.viewModel.CourseCd == null || vue.viewModel.CourseCd == undefined) {
                //商品リスト変更
                var params = vue.searchParams;
                params.noCache = true;
                axios.post("/DAI01110/GetProductList", params)
                    .then(res => {
                        vue.ProductList = res.data;

                        vue.DAI01110GridCash.options.dataModel.data = vue.ProductList.map(v => { return { "商品名": v.各種名称, "商品区分": v.サブ各種CD2 }; });
                        vue.DAI01110GridCredit.options.dataModel.data = vue.ProductList.map(v => { return { "商品名": v.各種名称, "商品区分": v.サブ各種CD2 }; });

                        vue.DAI01110GridCash.refreshDataAndView();
                        vue.DAI01110GridCredit.refreshDataAndView();

                        //条件変更ハンドラ
                        vue.conditionChanged();
                    })
                    .catch(err => {
                        console.log(err);
                        $.dialogErr({
                            title: "異常終了",
                            contents: "各種テーブルの検索に失敗しました<br/>",
                        });
                    });
            } else {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"), noCache:true }
            )
                .then(res => {
                    console.log(res);
                    vue.viewModel.CourseKbn = res.data.コース区分;

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
        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            return list;
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
        conditionChanged: function(callback, force) {
            var vue = this;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CourseCd) return;

            //検索
            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //検索中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 検索中…",
            });

            axios.post("/DAI01110/Search", params)
                .then(res => {
                    //移動データ
                    vue.DAI01110GridIdou.options.dataModel.data = res.data.IdouDataList;
                    vue.DAI01110GridIdou.refreshDataAndView();

                    //明細データ
                    var MeisaiDataList = _.cloneDeep(res.data.MeisaiDataList);

                    //入金データ
                    var NyukinData = _.cloneDeep(res.data.NyukinData);

                    var ProductDataList = vue.ProductList
                        .map((r, i) => {
                            var pdata = {
                                "商品名": r.各種名称,
                                "商品区分": r.サブ各種CD2,
                                "現金個数": 0,
                                "現金金額": 0,
                                "現金売値引件数": 0,
                                "現金売値引金額": 0,
                                "その他現金": i == 0 ? _.sum(MeisaiDataList.filter(m => m.商品区分 == 8 && m.売掛現金区分 != 2).map(m => m.現金金額)) * 1 : 0,
                                "掛売個数": 0,
                                "掛売金額": 0,
                                "掛売値引件数": 0,
                                "掛売値引金額": 0,
                                "その他掛売": i == 0 ? _.sum(MeisaiDataList.filter(m => m.商品区分 == 8 && m.売掛現金区分 != 2).map(m => m.掛売金額)) * 1 : 0,
                                "売掛入金": i == 0 ? (!!NyukinData ? NyukinData.現金 * 1 : 0) : 0,
                                "売掛入金件数": i == 0 ? (!!NyukinData ? NyukinData.件数 * 1 : 0) : 0,

                                "チケット代入金": i == 0 ? _.sum(MeisaiDataList.filter(m => m.商品区分 == 9).map(m => m.現金金額 * 1 - m.現金値引 * 1)) : 0,

                                "本日掛売未収": i == 0 ? _.sum(
                                    MeisaiDataList.filter(m => !["2", "3", "4"].includes(m.売掛現金区分) && m.マスタ売掛現金区分 == 0 && m.商品区分 != 9)
                                        .map(m => m.掛売金額 * 1 - m.掛売値引 * 1)
                                ) : 0,
                                "本日掛売掛": i == 0 ? _.sum(
                                    MeisaiDataList.filter(m => !["2", "3", "4"].includes(m.売掛現金区分) && m.マスタ売掛現金区分 != 0 && m.商品区分 != 9)
                                        .map(m => m.掛売金額 * 1 - m.掛売値引 * 1)
                                ) : 0,
                                "本日掛売売上": 0,
                            };

                            var meisai = _.reduce(
                                MeisaiDataList.filter(v => v.商品区分 == r.サブ各種CD2),
                                (acc, v,) => {

                                    acc.現金個数 = (acc.現金個数 || 0)
                                        + (["3", "4"].includes(v.売掛現金区分) ? 0 : (v.現金個数 * 1));
                                    acc.現金金額 = (acc.現金金額 || 0)
                                        + (["3", "4"].includes(v.売掛現金区分) ? 0 : (v.現金金額 * 1));
                                    acc.現金売値引件数 = (acc.現金売値引件数 || 0)
                                        + (["3", "4"].includes(v.売掛現金区分) ? 0 : (v.現金値引 != 0 ? 1 : 0));
                                    acc.現金売値引金額 = (acc.現金売値引金額 || 0)
                                        + (["3", "4"].includes(v.売掛現金区分) ? 0 : (v.現金値引 * 1));

                                    acc.掛売個数 = (acc.掛売個数 || 0)
                                        + (["2", "3", "4"].includes(v.売掛現金区分) ? 0 : (v.掛売個数 * 1));
                                    acc.掛売金額 = (acc.掛売金額 || 0)
                                        + (["2", "3", "4"].includes(v.売掛現金区分) ? 0 : (v.掛売金額 * 1));
                                    acc.掛売値引件数 = (acc.掛売値引件数 || 0)
                                        + (["2", "3", "4"].includes(v.売掛現金区分) ? 0 : (v.掛売値引 != 0 ? 1 : 0));
                                    acc.掛売値引金額 = (acc.掛売値引金額 || 0)
                                        + (["2", "3", "4"].includes(v.売掛現金区分) ? 0 : (v.掛売値引 * 1));

                                    // acc.本日掛売未収 = (acc.本日掛売未収 || 0)
                                    //     + (!["2", "3", "4"].includes(v.売掛現金区分) && v.マスタ売掛現金区分 == 0
                                    //         ? (v.掛売金額 * 1 - v.掛売値引 * 1) : 0
                                    //     );
                                    // acc.本日掛売掛 = (acc.本日掛売掛 || 0)
                                    //     + (!["2", "3", "4"].includes(v.売掛現金区分) && v.マスタ売掛現金区分 != 0
                                    //         ? (v.掛売金額 * 1 - v.掛売値引 * 1) : 0
                                    //     );
                                    acc.本日掛売売上 = (acc.掛売金額 || 0) - (acc.掛売値引金額 || 0) + (acc.その他掛売 || 0);

                                    acc.チケット枚数 = (acc.チケット枚数 || 0) + (v.売掛現金区分 == 2 ? (v.現金個数 * 1 + v.掛売個数 * 1 + v.分配元数量 * 1) : 0);
                                    acc.チケット金額 = (acc.チケット金額 || 0) + (v.売掛現金区分 == 2 ? (v.掛売金額 * 1 - v.掛売値引 * 1) : 0);

                                    acc.バークレー金額 = (acc.バークレー金額 || 0)
                                        + (v.売掛現金区分 == 3 ? (v.現金金額 * 1 - v.現金値引 * 1) + v.掛売金額 * 1 - v.掛売値引 * 1 : 0);

                                    acc.ボーナス枚数 = (acc.ボーナス枚数 || 0) + (v.売掛現金区分 == 4 ? (v.現金個数 * 1 + v.掛売個数 * 1 + v.分配元数量 * 1) : 0);

                                    return acc;
                                },
                                {},
                            );

                            var ret = _.merge(pdata, meisai);
                            return ret;
                        });

                    vue.DAI01110GridCash.options.dataModel.data = ProductDataList;
                    vue.DAI01110GridCash.refreshDataAndView();
                    vue.DAI01110GridCredit.options.dataModel.data = ProductDataList;
                    vue.DAI01110GridCredit.refreshDataAndView();


                    //チケット
                    var TicketData = [
                        {kind: "チケット", value: _.sumBy(ProductDataList, "チケット枚数") || 0},
                        {kind: "ボーナス", value: _.sumBy(ProductDataList, "ボーナス枚数") || 0},
                    ];
                    vue.DAI01110GridTicket.options.dataModel.data = TicketData;
                    vue.DAI01110GridTicket.refreshDataAndView();


                    var SummaryData = [
                        {
                            "本日総売上額": _.sumBy(ProductDataList, "現金金額") + _.sumBy(ProductDataList, "掛売金額"),
                            "総値引金額": _.sumBy(ProductDataList, "現金売値引金額") + _.sumBy(ProductDataList, "掛売値引金額"),
                            "その他": _.sumBy(ProductDataList, "その他現金") + _.sumBy(ProductDataList, "その他掛売"),
                            "チケット": _.sumBy(ProductDataList, "チケット金額") || 0,
                            "バークレー": _.sumBy(ProductDataList, "バークレー金額") || 0,
                        }
                    ];
                    vue.DAI01110GridSummary.options.dataModel.data = SummaryData;
                    vue.DAI01110GridSummary.refreshDataAndView();

                    var TicketSummaryData = [
                        {
                            チケット精算: "チケット",
                            "持出枚数": 0,
                            "持帰枚数": 0,
                            "売り枚数": 0,
                            "売上金額": _.sumBy(ProductDataList, "チケット代入金"),
                        }
                    ];

                    vue.DAI01110GridTicketSummary.options.dataModel.data = TicketSummaryData;
                    vue.DAI01110GridTicketSummary.refreshDataAndView();

                    //コース別明細データ
                    var CourseMeisaiData = _.cloneDeep(res.data.CourseMeisaiData);
                    vue.viewModel.IsSaved = !!CourseMeisaiData;
                    vue.viewModel.LastEditor = !!CourseMeisaiData ? CourseMeisaiData.修正担当者名 : "";
                    vue.viewModel.LastEditDate = !!CourseMeisaiData? moment(CourseMeisaiData.修正日).format("YYYY/MM/DD HH:mm:ss") : "";

                    if (!!CourseMeisaiData) {
                        //金銭明細書
                        vue.DAI01110GridMoneyInfo.rollback();
                        vue.DAI01110GridMoneyInfo.pdata.map((v, i) => {
                            var zIdx = Moji((i + 1).toString()).convert('HE', 'ZE').toString();
                            if (_.has(CourseMeisaiData, "金種" + zIdx)) {
                                v.value = CourseMeisaiData["金種" + zIdx];
                            } else if (_.has(CourseMeisaiData, v.kind)) {
                                v.value = CourseMeisaiData[v.kind] * 1;
                            }

                            if (v.kind == "合計") {
                                v.value2 = CourseMeisaiData.過剰金;
                            }
                        });
                        vue.DAI01110GridMoneyInfo.refreshDataAndView();
                        vue.DAI01110GridMoneyInfo.commit();

                        //チケット
                        var TicketData = [
                            {kind: "チケット", value: CourseMeisaiData.セイブチケットＥお || 0},
                            {kind: "ボーナス", value: CourseMeisaiData.セイブチケットＨお || 0},
                        ];
                        vue.DAI01110GridTicket.options.dataModel.data = TicketData;
                        vue.DAI01110GridTicket.refreshDataAndView();

                        //バークレーバウチャー
                        vue.DAI01110GridBV.pdata.map((v, i) => {
                            var zPrice = Moji(v.price.toString()).convert('HE', 'ZE').toString();
                            if (_.has(CourseMeisaiData, "ＢＶチケット" + zPrice)) {
                                v.value = CourseMeisaiData["ＢＶチケット" + zPrice] * 1;
                            }
                        });
                        vue.DAI01110GridBV.refreshDataAndView();
                        vue.DAI01110GridBV.commit();
                    }

                    progressDlg.dialog("close");
                    vue.DAI01110GridMoneyInfo.setSelection({ rowIndx: 0, colIndx: 1 });
                })
                .catch(err => {
                    progressDlg.dialog("close");
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "データの検索に失敗しました<br/>",
                    });
                });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            return res;
        },
        setGridMoneyInfoTitle: function(title) {
            return "金銭明細書";
        },
        setGridTicketTitle: function(title) {
            return "チケット";
        },
        setGridBVTitle: function(title) {
            return "バークレーヴァウチャーズチケット";
        },
        delete: function() {
            var vue = this;

            //削除実行
            vue.DAI01110GridIdou.saveData(
                {
                    uri: "/DAI01110/Delete",
                    params: {
                        LastEditDate: moment(vue.viewModel.LastEditDate).format("YYYY-MM-DD HH:mm:ss.SSS"),
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: true,
                        title: "コース別明細データ削除確認",
                        message: "コース別明細データを削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited && !!res.edited.length) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                vue.notifyChange(res.edited);
                            } else {
                                vue.viewModel.IsSaved = false;
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            }

                            return false;
                        },
                    },
                }
            );

        },
        notifyChange: function(newData) {
            var vue = this;
        },
        save: function() {
            var vue = this;

            var target = {
                "日付": vue.searchParams.TargetDate,
                "部署CD": vue.searchParams.BushoCd,
                "コースＣＤ": vue.searchParams.CourseCd,
                "配送担当者ＣＤ": vue.searchParams.TantoCd,
            };

            var idou = vue.DAI01110GridIdou.pdata.map((v, i) => {
                var zIdx = Moji((i + 1).toString()).convert('HE', 'ZE').toString();
                return {
                    ["持出し個数" + zIdx] : v.持ち出し数 * 1,
                    ["工場追加" + zIdx] : v.受取数_工場 * 1,
                    ["もらった" + zIdx] : v.受取数_一般 * 1,
                    ["やった" + zIdx] : v.引渡数 * 1,
                    ["見本" + zIdx] : v.ボーナス販売数 * 1,
                    ["残数" + zIdx] : v.残数 * 1,
                };
            });
            _.merge(target, ...idou);

            var ticketSummary = {
                "チケット１持出枚数": vue.DAI01110GridTicketSummary.pdata[0].持出枚数 * 1,
                "チケット１持帰枚数": vue.DAI01110GridTicketSummary.pdata[0].持帰枚数 * 1,
                "チケット１生協売": 0,
                "チケット１売枚数": vue.DAI01110GridTicketSummary.pdata[0].売り枚数 * 1,
                "チケット１売上金額": vue.DAI01110GridTicketSummary.pdata[0].売上金額 * 1,
                "チケット２持出枚数": 0,
                "チケット２持帰枚数": 0,
                "チケット２生協売": 0,
                "チケット２売枚数": 0,
                "チケット２売上金額": 0,
                "チケット１現金集金": 0,
                "チケット２現金集金": 0,
                "ＶＢ集金３００個数": 0,
                "ＶＢ集金２００個数": 0,
            };
            _.merge(target, ticketSummary);

            var money = vue.DAI01110GridMoneyInfo.pdata.map((v, i) => {
                var zIdx = Moji((i + 1).toString()).convert('HE', 'ZE').toString();

                if (i < 11) {
                    var key = "金種" + zIdx;
                    return { [key]: v.value * 1 };
                } else if (v.value2 == "過剰金") {
                    return [
                        { [v.kind] : v.value * 1 },
                        { [v.value2] : vue.DAI01110GridMoneyInfo.pdata.find(r => r.kind == "合計").value2 * 1 },
                    ];
                } else {
                    return { [v.kind] : v.value * 1 };
                }
            });
            _.merge(target, ...(_.flatten(money)));

            target.セイブチケットＥお = vue.DAI01110GridTicket.pdata.find(v => v.kind == "チケット").value * 1;
            target.セイブチケットＨお = vue.DAI01110GridTicket.pdata.find(v => v.kind == "ボーナス").value * 1;
            target.セイブボーナス = 0;
            target.ＢＶチケット３００ = vue.DAI01110GridBV.pdata.find(v => v.price == 300).value * 1;
            target.ＢＶチケット２００ = vue.DAI01110GridBV.pdata.find(v => v.price == 200).value * 1;

            target.予備金額１ = 0;
            target.予備金額２ = 0;
            target.予備ＣＤ１ = 0;
            target.予備ＣＤ２ = 0;

            target.修正担当者ＣＤ = vue.getLoginInfo().uid;

            console.log(target);

            //登録実行
            vue.DAI01110GridIdou.saveData(
                {
                    uri: "/DAI01110/Save",
                    params: {
                        Target: target,
                        LastEditDate: moment(vue.viewModel.LastEditDate).format("YYYY-MM-DD HH:mm:ss.SSS"),
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited && !!res.edited.length) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                vue.notifyChange(res.edited);
                            } else {
                                var ret = res.CourseMeisaiData;
                                vue.viewModel.IsSaved = true;
                                vue.viewModel.LastEditor = !!ret ? ret.修正担当者名 : "";
                                vue.viewModel.LastEditDate = !!ret? moment(ret.修正日).format("YYYY/MM/DD HH:mm:ss") : "";
                                vue.DAI01110GridMoneyInfo.commit();
                                vue.DAI01110GridBV.commit();
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            }

                            return false;
                        },
                    },
                }
            );
        },
        showTicketDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01110GridIdou;
            if (!grid) return;

            if (!rowData) {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                rowData = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseKbn: vue.viewModel.CourseKbn,
                CourseCd: rowData.コースＣＤ,
                CourseNm: rowData.コース名,
            };

            grid.showLoading();

            //事前情報取得
            axios.all(
                [
                    //コースリストの取得
                    axios.post("/Utilities/GetCourseList", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, noCache:true }),
                    //商品リストの取得
                    axios.post("/DAI01061/GetTargetProducts", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, CourseCd: params.CourseCd, noCache:true }),
                ]
            ).then(
                axios.spread((responseCourse, responseProduct) => {
                    var resCourse = responseCourse.data;
                    var resProduct = responseProduct.data;

                    var checkError = (res, name) => {
                        if (res.onError && !!res.errors) {
                            //メッセージリストに追加
                            Object.values(res.errors).filter(v => v)
                                .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                            //ダイアログ
                            $.dialogErr({ errObj: res });

                            return false;
                        } else if (res.onException) {
                            //メッセージ追加
                            vue.$root.$emit("addMessage", name +"リスト取得失敗(" + vue.page.ScreenTitle + ":" + res.message + ")");

                            //ダイアログ
                            $.dialogErr({
                                title: "異常終了",
                                contents: name +"リストの取得に失敗しました<br/>" + res.message,
                            });

                            return false;
                        } else if (res == "") {
                            //完了ダイアログ
                            $.dialogErr({
                                title: name +"リスト無し",
                                contents: "該当データは存在しません" ,
                            });

                            return false;
                        }

                        return true;
                    };

                    if (!checkError(resCourse)) return;
                    if (!checkError(resProduct)) return;

                    //取得した結果を設定
                    params.CourseList = resCourse;
                    params.ProductList = resProduct;

                    grid.hideLoading();

                    PageDialog.show({
                        pgId: "DAI01061",
                        params: params,
                        isModal: true,
                        isChild: true,
                        width: 800,
                        height: 750,
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
                })
            )
            .catch(error => {
                grid.hideLoading();

                //メッセージ追加
                vue.$root.$emit("addMessage", "DB検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "DBの検索に失敗しました<br/>",
                });
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
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border-bottom: solid 1px black;
                    border-right: solid 1px black;
                }
                th, td {
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    border-top: solid 1px black;
                    border-left: solid 1px black;
                    overflow: hidden;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 19px;
                    text-align: center;
                }
                td {
                    height: 19px;
                }
            `;

            var header = `
                <div class="title">
                    <h3>* * * 売上明細表 * * *</h3>
                </div>
                <table class="header-table" style="border-top-width: 0px; border-left-width: 0px;">
                    <colgroup>
                            <col style="width:5.0%;"></col>
                            <col style="width:5.0%;"></col>
                            <col style="width:7.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                            <col style="width:5.5%;"></col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th colspan="11" style="border-top-width: 0px; border-left-width: 0px;"></th>
                            <th colspan="2">作成日</th>
                            <th colspan="3">${moment().format("YYYY年MM月DD日")}</th>
                            <th>PAGE</th>
                            <th>1/1</th>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <th colspan="5">${vue.viewModel.TargetDate}</th>
                            <th colspan="5" style="border-top-width: 0px !important;"></th>
                            <th colspan="2">コース</th>
                            <th>${vue.viewModel.CourseCd}</th>
                            <th colspan="4">${vue.viewModel.CourseNm}</th>
                        </tr>
                        <tr>
                            <th>部署</th>
                            <th>${vue.viewModel.BushoCd}</th>
                            <th colspan="4">${vue.viewModel.BushoNm}</th>
                            <th colspan="5" style="border-top-width: 0px !important;"></th>
                            <th colspan="2">担当者</th>
                            <th>${vue.viewModel.TantoCd}</th>
                            <th colspan="4">${vue.viewModel.TantoNm}</th>
                        </tr>
                    </thead>
                </table>
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(header)
                        .append(vue.getGridHtml(vue.DAI01110GridIdou))
                        .append(vue.getGridHtml(vue.DAI01110GridTicketSummary, "table.DAI01110GridTicketSummary { width: 400px; }"))
                        .append(vue.getGridHtml(
                                vue.DAI01110GridCash,
                                `
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(1) {
                                        width: 30%;
                                    }
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(2) {
                                        width: 20%;
                                    }
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(3),
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(4),
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(5)
                                    {
                                        width: 10%;
                                    }
                                    table.DAI01110GridCash tr:nth-child(1) th:nth-child(6) {
                                        width: 20%;
                                    }
                                    table.DAI01110GridCash tr:nth-child(n+4):nth-child(-n+9) td {
                                        border-top-width: 0px;
                                    }
                                    table.DAI01110GridCash tr:nth-child(n+3) td:nth-child(2),
                                    table.DAI01110GridCash tr:nth-child(n+3) td:nth-child(3),
                                    table.DAI01110GridCash tr:nth-child(n+3) td:nth-child(5)
                                    {
                                        border-left-width: 0px;
                                    }
                                `
                            )
                        )
                        .append(vue.getGridHtml(
                                vue.DAI01110GridCredit,
                                `
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(1) {
                                        width: 30%;
                                    }
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(2) {
                                        width: 20%;
                                    }
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(3),
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(4),
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(5)
                                    {
                                        width: 10%;
                                    }
                                    table.DAI01110GridCredit tr:nth-child(1) th:nth-child(6) {
                                        width: 20%;
                                    }
                                    table.DAI01110GridCredit tr:nth-child(n+4):nth-child(-n+9) td {
                                        border-top-width: 0px;
                                    }
                                    table.DAI01110GridCredit tr:nth-child(n+3) td:nth-child(2),
                                    table.DAI01110GridCredit tr:nth-child(n+3) td:nth-child(3),
                                    table.DAI01110GridCredit tr:nth-child(n+3) td:nth-child(5)
                                    {
                                        border-left-width: 0px;
                                    }
                                `
                            )
                        )
                        .append(vue.getGridHtml(vue.DAI01110GridSummary))
                        .append(vue.getGridHtml(
                            vue.DAI01110GridTicket,
                                `
                                    div table.DAI01110GridTicket {
                                        width: 49.5%;
                                        float: left;
                                        margin-right: 5px;
                                    }
                                `
                            )
                        )
                        .append(vue.getGridHtml(
                            vue.DAI01110GridBV,
                                `
                                    div table.DAI01110GridBV {
                                        width: 49.5%;
                                        float: left;
                                    }
                                `
                            )
                        )
                    )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
        getGridHtml: function(grid, styles) {
            var baseStyles = `
                table.${grid.vue.id} {
                    margin-top: 5px;
                }
                table.${grid.vue.id} th {
                    background-color: lightgrey !important;
                }
            `;
            return grid.generateHtml(baseStyles + (styles || ""));
        },
    }
}
</script>
