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
            <div class="col-md-4" style="align-items: baseline;">
                <PqGridWrapper
                    id="DAI01110GridTicketSummary"
                    ref="DAI01110GridTicketSummary"
                    :options=this.gridTicketOptionsSummary
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :onAfterSearchFunc=this.onAfterSearchFunc
                    classes="ml-0 mr-0 mt-1"
                />
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
                    classes="ml-5 mr-0 mt-1 mb-1"
                />
                <PqGridWrapper
                    id="DAI01110GridTicket"
                    ref="DAI01110GridTicket"
                    :options=this.gridTicketOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle=setGridTicketTitle
                    classes="ml-5 mr-0 mt-3 mb-1"
                />
                <PqGridWrapper
                    id="DAI01110GridBV"
                    ref="DAI01110GridBV"
                    :options=this.gridBVOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle=setGridBVTitle
                    classes="ml-5 mr-0 mt-2 mb-1"
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
            };
        }
    },
    watch: {
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
                UpdateDate: null,
                test: null,
                testKn: null,
            },
            CheckInterVal: null,
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
                        ui.cls.push(ui.cellData < 0 ? "minus-value" : "plus-value");
                        if(ui.cellData == 0) {
                            return { text: "" };
                        }

                        return ui;
                    },
                },
                dataModel: {
                    location: "local",
                    data: _.range(7).map(v => {return { "商品名": "商品" + (v + 1) };}),
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
            gridTicketOptionsSummary: {
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
                    data: _.range(7).map(v => {return { "商品名": "商品" + (v + 1) };}),
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
                                        ui.cls.push("pq-align-right");
                                        return { text: "現金売合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "個数",
                                dataIndx: "個数",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "金額",
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
                        dataIndx: "その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                ui.cls.push("cell-disabled");
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
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                ui.cls.push("cell-disabled");
                            }
                            return ui;
                        },
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
                                ui.cls.push("cell-disabled");
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
                                        ui.cls.push("cell-disabled");
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
                    data: _.range(7).map(v => {return { "商品名": "商品" + (v + 1) };}),
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
                                        ui.cls.push("pq-align-right");
                                        return { text: "掛売合計" };
                                    }
                                    return ui;
                                },
                            },
                            {
                                title: "個数",
                                dataIndx: "個数",
                                dataType: "integer",
                                format: "#,###",
                                width: 75, maxWidth: 75, minWidth: 75,
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "金額",
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
                        dataIndx: "その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (ui.rowIndx != 0 && !ui.rowData.pq_grandsummary) {
                                ui.cls.push("cell-disabled");
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
                    data: [{"本日総売上額": 99999, "総値引金額": 99999, "その他": 99999, "チケット": 99999,　"バークレー": 99999, }],
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
                height: 276,
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
                                        ui.cls.push("pq-align-center-force");
                                        ui.cls.push("title-col");
                                    } else if (ui.rowData.kind != "合計") {
                                        ui.cls.push("cell-disabled");
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
                height: 67,
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
                height: 89,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {price: 200, value: 0},
                        {price: 300, value: 0},
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
                        title: "チケット",
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
                { visible: "true", value: "検索", id: "DAI01110GridIdou_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(null, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI01110GridIdou_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI01110GridIdou_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.DAI01110GridIdou.print(vue.setPrintOptions);
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            // vue.viewModel.TargetDate = moment();
            vue.viewModel.TargetDate = moment("2019/09/04");    //TODO: debug
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")}
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

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
        conditionChanged: function(callback, force) {
            var vue = this;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CourseCd) return;

            //移動入力検索
            axios.post("/DAI01110/Search", vue.searchParams)
                .then(res => {
                    vue.DAI01110GridIdou.options.dataModel.data = res.data.IdouDataList;
                    vue.DAI01110GridIdou.refreshDataAndView();
                })
                .catch(err => {
                    console.log(err);
                    grid.hideLoading();
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
            return "バークレーヴァウチャーズチケット(弁当用)";
        },
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printOptions.printType = "raw-html";
            grid.options.printOptions.printStyles = "@media print { @page { size: A4 landscape; } }";

            var table = $($(grid.exportData({ format: "htm", render: true }))[3]);
            var tableHeaders = table.find("tr").filter((i, v) => !!$(v).find("th").length);
            var tableBodies = table.find("tr").filter((i, v) => !!$(v).find("td").length);

            //optional: multiple summary rows
            var courseNm;
            tableBodies.map((i, v) => {
                var row = $(v);
                courseNm = row.find("td[rowspan]").text() || row.find("td").filter((idx, e) => $(e).text().includes("合計")).text() || courseNm;
                if (row.find("td").length != tableHeaders.find("th").length) {
                    row.prepend($("<td>").text(courseNm).hide());
                }
                if (!row.find("td:first").text()) {
                    row.find("td:first").text(courseNm).hide();
                }
                return row.get(0);
            });

            //optional: generate contents for multipages
            var contents = [];
            var maxRowsPerPage = 45;
            _(tableBodies)
                .groupBy(v => $(v).find("td:first").text())
                .values()
                .reduce((a, v, i, o) => {
                    if (!$(v[0]).find("td:first").attr("rowspan")) {
                        $(v[0]).find("td:first").attr("rowspan", v.length).css("border-bottom-width", "1px");
                    }

                    if (!_.isEmpty(a) && a.find(".data-table tr").length + v.length > maxRowsPerPage) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                        a = {};
                    }

                    if (_.isEmpty(a)) {
                        var pageHeader = `
                                            <div class="title">
                                                <h3>* * * 移動表 * * *</h3>
                                            </div>
                                            <table class="header-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">日付</th>
                                                        <th style="width: 15%;">${vue.viewModel.TargetDate}</th>
                                                        <th style="width: 52%; border-top-width: 0px !important;"> </th>
                                                        <th style="width: 16%;">${moment().format("YYYY年MM月DD日 HH:mm:ss")}</th>
                                                        <th style="width: 6%;">PAGE</th>
                                                        <th style="width: 6%;">${contents.length + 1}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        `;

                        a = $("<div>").css("page-break-before", "always")
                            .append(pageHeader)
                            .append($("<table>").addClass("data-table").append(tableHeaders.prop("outerHTML")))
                            .append("<br/>")
                            ;
                    }

                    v.forEach(r => {
                        if (v.indexOf(r) == 0) {
                            $(r).find("td[colspan]").css("border-bottom-width", "1px");
                        }
                        if (v.indexOf(r) == v.length - 1) {
                            $(r).find("td").css("border-bottom-width", "1px");
                        }
                    })
                    a.find(".data-table").append(v);

                    if (_.last(o) == v) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                    }

                    return a;
                }, {});

            var styles =  `
                                <style>
                                    .grid-contents .title {
                                        width: 100%;
                                        display: inline-flex;
                                        justify-content: center;
                                        margin-bottom: 10px;
                                    }
                                    .grid-contents .title h3 {
                                        text-align: center;
                                        border: solid 1px black;
                                        border-radius: 4px;
                                        background-color: grey;
                                        margin: 0px;
                                        padding-left: 30px;
                                        padding-right: 30px;
                                    }
                                    .grid-contents table {
                                        width: 100%;
                                        border-collapse:collapse;
                                    }
                                    .grid-contents .header-table tr th {
                                        border-bottom: 0px;
                                    }
                                    .grid-contents tr th,
                                    .grid-contents tr td
                                    {
                                        height: 12px !important;
                                        font-family: "MS UI Gothic" !important;
                                        font-size: 9pt !important;
                                        font-weight: normal !important;
                                        line-height: normal !important;
                                        border: solid 1px black;
                                        margin: 0px;
                                        padding: 0px;
                                        padding-top: 1px;
                                        padding-bottom: 1px;
                                        padding-left: 3px;
                                        padding-right: 3px;
                                    }
                                    .grid-contents tr td {
                                        border-top-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .grid-contents tr th:nth-child(1) {
                                        width: 14%;
                                    }
                                    .grid-contents tr th:nth-child(2) {
                                        width: 6%;
                                    }
                                    .grid-contents tr th:nth-child(7),
                                    .grid-contents tr th:nth-child(9)
                                    {
                                        width: 22%;
                                    }
                                    .grid-contents tr th:nth-child(n+3):nth-child(-n+6),
                                    .grid-contents tr th:nth-child(8),
                                    .grid-contents tr th:nth-child(10)
                                    {
                                        width: 6%;
                                        text-align: center;
                                    }
                                    .grid-contents tr td:nth-child(1) {
                                        vertical-align: top;
                                    }
                                </style>
                            `;

            var printable = $("<html>")
                .append($("<head>").append(styles))
                .append($("<body>").append($("<div>").addClass("grid-contents").append(contents)));

            grid.options.printOptions.printable = printable.prop("outerHTML");

            console.log(grid.options.printable);
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01110GridIdou;
            if (!grid) return;

            var params;

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
                    axios.post("/Utilities/GetCourseList", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, }),
                    //商品リストの取得
                    axios.post("/DAI01061/GetTargetProducts", { BushoCd: params.BushoCd, CourseKbn: params.CourseKbn, CourseCd: params.CourseCd, }),
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
    }
}
</script>
