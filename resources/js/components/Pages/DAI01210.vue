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
                <label>年月</label>
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
            <div class="col-md-3">
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
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteNoLimit=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
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
                    :nameWidth=300
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                    :disabled='viewModel.PrintOrder == 1'
                />
            </div>
            <div class="col-md-4">
                <label style="width: unset; margin-left: 10px; margin-right: 10px;">得意先登録年月</label>
                <DatePickerWrapper
                    id="RegistDate"
                    ref="DatePicker_RegistDate"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="RegistDate"
                    :editable='viewModel.PrintOrder == 0'
                    :onChangedFunc=onRegistDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者ＣＤ</label>
            </div>
            <div class="col-md-5">
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
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onTantoChanged
                    :isShowAutoComplete=true
                    :AutoCompleteNoLimit=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                    :disabled='viewModel.PrintOrder == 1'
                />
            </div>
            <div class="col-md-3">
                <VueCheck
                    id="VueCheck_IncludeZero"
                    ref="VueCheck_IncludeZero"
                    :vmodel=viewModel
                    bind="IsIncludeZero"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '含まない', label: 'チェック無し：0データを表示しない'},
                        {code: '1', name: '含む', label: 'チェック有り：0データを表示する'},
                    ]"
                    :onChangedFunc=onIncludeZeroChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="Grid1Container">
                <PqGridWrapper
                    id="DAI01210Grid1"
                    ref="DAI01210Grid1"
                    dataUrl="/DAI01210/Search"
                    :query=this.searchParams
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :checkChanged=true
                    :checkChangedFunc=checkGridChangedFunc
                    :options=this.grid1Options
                    :onAfterSearchFunc=this.onAfterSearchFunc
                    :autoToolTipDisabled=true
                    :resizeFunc=this.resizeGrid
                    classes="ml-0 mr-0 mt-2"
                />
            </div>
            <div class="Grid2Container">
                <PqGridWrapper
                    id="DAI01210Grid2"
                    ref="DAI01210Grid2"
                    :options=this.grid2Options
                    :autoToolTipDisabled=true
                    :resizeFunc=this.resizeGrid
                    :setCustomTitle=this.setSummaryGridTitle
                    classes="ml-0 mr-0 mt-2"
                />
            </div>
        </div>
    </form>
</template>

<style>
/* stripedの反転 */
[pgid=DAI01210] .pq-grid .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped) {
    background-color: #e6f4ff !important;
}
[pgid=DAI01210] .pq-grid .pq-grid-row.pq-striped:not([id^="pq-head-row"]) {
    background-color: white !important;
}
/* マージセル */
[pgid=DAI01210] .pq-grid .pq-grid-row .pq-grid-cell.pq-merge-cell {
    background: white;
    color: initial;
    white-space: pre-wrap;
    align-items: flex-start;
}
/* 合計行 */
[pgid=DAI01210] .pq-grid .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}

label{
    width: 80px;
}
</style>
<style scoped>
.Grid1Container {
    width: calc(100vw / var(--ratio) - 425px);
    max-width: unset;
}
.Grid2Container {
    position: absolute;
    right: 18px;
    width: auto;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01210",
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
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                CustomerCd: this.viewModel.CustomerCd,
                RegistDate: moment(this.viewModel.RegistDate, "YYYY年MM月").isValid() ? moment(this.viewModel.RegistDate, "YYYY年MM月").format("YYYYMMDD") : null,
                TantoCd: this.viewModel.TantoCd,
                PrintOrder: this.viewModel.PrintOrder,
            };
        }
    },
    watch: {
    },
    data() {
        var vue = this;

        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 日配売上日計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseCd: null,
                CustomerCd: null,
                RegistDate: null,
                TantoCd: null,
                PrintOrder: "0",
                IsIncludeZero: "0",
            },
            PrevBushoCd: null,
            ProductList: [],
            DAI01210Grid1: null,
            DAI01210Grid2: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                rowHt: 22,
                rowHtHead: 50,
                rowHtSum: 22,
                freezeCols: 4,
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
                    on: false,
                    header: false,
                    grandSummary: false,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [false],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "対象CD",
                        dataIndx: "対象CD",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "対象名",
                        width: 200, maxWidth: 200, minWidth: 200,
                        fixed: true,
                        render: ui => {
                            if (!ui.rowData.summaryRow) {
                                return _.padStart(ui.rowData.対象CD, 10, " ") + "\n" + ui.rowData.対象名;
                            }
                            return ui;
                        },
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                        fixed: true,
                    },
                    {
                        title: "有無",
                        dataIndx: "売上データ明細有無",
                        dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
            },
            grid2Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                numberCell: { show: false },
                strNoRows: "",
                showTitle: false,
                autoRow: false,
                rowHt: 22,
                rowHtHead: 50,
                rowHtSum: 22,
                width: 410,
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
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: false,
                    header: false,
                    grandSummary: false,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "対象CD",
                        dataIndx: "対象CD",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "合計",
                        dataIndx: "合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        dataIndx: "平均",
                        dataType: "float",
                        format: "#,###.0",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        title: "土曜",
                        dataIndx: "土曜合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        dataIndx: "土曜平均",
                        dataType: "float",
                        format: "#,###.0",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        title: "日曜",
                        dataIndx: "日曜合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        dataIndx: "日曜平均",
                        dataType: "float",
                        format: "#,###.0",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        title: "有無",
                        dataIndx: "売上データ明細有無",
                        dataType: "string",
                        hidden: true,
                    },
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01210Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01210Grid1_CSV", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01210Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01210Grid1_Excel", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01210Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01210Grid1_Print", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().startOf("month").format("YYYY年MM月");

            vue.refreshCols();
            vue.filterChanged();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01210Grid1;

            if (vue.PrevBushoCd != vue.viewModel.BushoCd) {
                vue.PrevBushoCd = vue.viewModel.BushoCd;

                //商品リスト変更
                axios.post("/DAI01210/GetProductList", vue.searchParams)
                    .then(res => {
                        vue.ProductList = res.data;

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
                //検索条件変更
                vue.conditionChanged();
            }
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //列定義変更 + 条件変更ハンドラ
            vue.refreshCols(vue.conditionChanged);
        },
        onRegistDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        refreshCols: function(callback) {
            var vue = this;
            var grid1, grid2;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid1 = vue.DAI01210Grid1;
                    grid2 = vue.DAI01210Grid2;
                    if (!!grid1 && !!grid2 && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid1, grid2);
                    }
                }, 100);
            })
            .then((grid1, grid2) => {
                grid1.showLoading();

                var mt = moment(vue.viewModel.TargetDate, "YYYY年MM月");
                var days = _.range(1, mt.endOf("month").format("D") * 1 + 1);
                var max = 31;
                days = days.length == 31 ? days : days.concat(_.range(0, days.length - 31).fill(null));

                var newCols = grid1.options.colModel
                    .filter(v => !!v.fixed)
                    .concat(
                        ...days.map((d, i) => {
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
                            }
                        })
                    );

                newCols.push(...[
                    {
                        title: "合計",
                        dataIndx: "合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平均",
                        dataIndx: "平均",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "土曜",
                        dataIndx: "土曜合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平均",
                        dataIndx: "土曜平均",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "日曜",
                        dataIndx: "日曜合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "平均",
                        dataIndx: "日曜平均",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, maxWidth: 65, minWidth: 65,
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
                        hidden: true,
                        hiddenOnExport: false,
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
        onPrintOrderChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01210Grid1;

            //条件変更ハンドラ
            vue.conditionChanged();

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity.部署ＣＤ;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTantoChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid1 = vue.DAI01210Grid1;
            var grid2 = vue.DAI01210Grid2;

            console.log("1210 conditionChanged")

            if (!grid1 || !grid2 || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;

            if (!vue.ProductList.length) return;
            vue.setSummaryRow();

            if (!force && !grid1.prevPostData
            && !vue.viewModel.CourseCd && !vue.viewModel.CustomerCd && !vue.viewModel.RegistDate && !vue.viewModel.TantoCd) {
                return;
            }

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!force && !!grid1.prevPostData && _.isEqual(grid1.prevPostData, vue.searchParams)) {
                return;
            }

            grid1.searchData(vue.searchParams, false, null);
        },
        setSummaryRow: function() {
            var vue = this;
            var grid1 = vue.DAI01210Grid1;
            var grid2 = vue.DAI01210Grid2;

            //summaryDataの設定
            grid1.options.summaryData = vue.ProductList.map((p, i) => {
                var pqfn = _.reduce(
                    grid1.options.colModel.filter(c => !c.fixed),
                    (acc, c) => {
                        acc[c.dataIndx] = "SUMIF(C:C, '"
                            + p.商品区分 + "', "
                            + grid1.getExcelColumnName(c.dataIndx, "${nm}:${nm}")
                            + ")";
                        return acc;
                    },
                    {}
                );

                return  {
                    summaryRow: true,
                    "対象名": i == 0 ? "合計" : "",
                    "商品名": p.商品名,
                    pq_fn: pqfn,
                };
            });

            grid2.options.summaryData = vue.ProductList.map((p, i) => {
                var pqfn = _.reduce(
                    grid2.options.colModel.filter(c => !c.fixed),
                    (acc, c) => {
                        acc[c.dataIndx] = "SUMIF(B:B, '"
                            + p.商品区分 + "', "
                            + grid2.getExcelColumnName(c.dataIndx, "${nm}:${nm}")
                            + ")";
                        return acc;
                    },
                    {}
                );

                return  {
                    summaryRow: true,
                    pq_fn: pqfn,
                };
            });

            grid1.refresh();
            grid2.refresh();
        },
        checkGridChangedFunc: function(grid) {
            var vue = this;
            var grid2 = vue.DAI01210Grid2;

            if (!grid2) return false;

            return grid2.isChanged();
        },
        resizeGrid: function(grid) {
            var vue = this;
            var widget = grid.widget();

            var oldH = widget.outerHeight();
            var containerH = widget.closest(".body-content").outerHeight(true);
            var otherH = _.sum(widget.closest(".row").siblings(".row").map((i, el) => $(el).outerHeight(true)));

            var newH = containerH - otherH - 5;

            if (_.round(newH) != _.round(oldH)) {
                grid.options.height = newH;
                grid.refresh();
            }
        },
        setSummaryGridTitle: function(title) {
            return "　";
        },
        syncScroll: _.debounce(function(val) {
            var vue = this;
            var grid1 = vue.DAI01210Grid1;
            var grid2 = vue.DAI01210Grid2;

            if (grid1.scrollY() != val) grid1.scrollY(val);
            if (grid2.scrollY() != val) grid2.scrollY(val);
        }, 0),
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            grid.options.colModel.find(c => c.dataIndx == "対象名").title =
                vue.viewModel.PrintOrder == "0" ? "得意先名" : "コース名";

            //mergeCellsの設定
            var pos = 0;
            _(res).groupBy(v => v.対象CD)
                .forIn((v, k) => {
                    var cells = {
                        r1: pos,
                        c1: 1,
                        rc: v.length,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cells);
                    pos += v.length;
                });

            var grid2 = vue.DAI01210Grid2;
            grid2.options.dataModel.data = res;
            grid2.refreshDataAndView();

            return res;
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

            var wholeColumns = ["Cd", "CdNm", "担当者名カナ"];

            if (input == comp.selectValue && comp.isUnique) {
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
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
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
                    font-size: 8pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 10.5px;
                    text-align: center;
                }
                td {
                    height: 10.5px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: unset;
                }
                table.header-table tr:nth-child(1) th ,
                table.header-table tr:nth-child(3) th {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                div.report-title-area{
                    width:400px;
                    height:35px;
                    text-align: center;
                    display:table-cell;
                    vertical-align: middle;
                    background-color: #c0ffff;
                    border: 2px solid #000000;
                    border-radius: 5px;
                }
            `;
            var headerFunc = (header, idx, length) => {
                console.log("headerFunc", header);
                return `
                    <div class="title">
                        <h3><div class="report-title-area">日配売上日計表<div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 6%;">部署</th>
                                <th style="width: 8%;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 12%;">${vue.viewModel.BushoNm}</th>
                                <th style="width: 46%;"></th>
                                <th style="width: 6%;">作成日</th>
                                <th style="width: 10%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 6%;">PAGE</th>
                                <th style="text-align: right; padding-right: 10px;">${idx + 1}/${length}</th>
                            </tr>
                            <tr>
                                <th>&nbsp</th>
                            </tr>
                            <tr>
                                <th>日付</th>
                                <th>${vue.viewModel.TargetDate}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.header-table tr th {
                    height: 16px;
                }
                table.header-table tr:nth-child(1) th {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.header-table tr:nth-child(1) th:nth-child(3),
                table.header-table tr:nth-child(1) th:nth-child(8) {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.header-table tr:nth-child(3) th:nth-child(1) {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:nth-child(3) th:nth-child(2) {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:nth-child(1) th:nth-child(4) ,
                table.header-table tr:nth-child(3) th:nth-child(n+3) {
                    border:none;
                }
                table.DAI01210Grid1 thead tr:nth-child(1) th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    height: 25px;
                    line-height: 1.3;
                }
                table.DAI01210Grid1 thead tr:nth-child(1) th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01210Grid1 th:nth-child(1) {
                    width: 9%;
                }
                table.DAI01210Grid1 th:nth-child(2) {
                    width: 3.5%;
                }
                table.DAI01210Grid1 th:nth-child(n+3):nth-child(-n+33) {
                    width: 2.25%;
                }
                table.DAI01210Grid1 th:nth-child(n+34) {
                    width: 3%;
                }
                table.DAI01210Grid1 th,
                table.DAI01210Grid1 td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01210Grid1 th:last-child,
                table.DAI01210Grid1 td:last-child {
                    border-right-width: 1px;
                }
                table.DAI01210Grid1 th {
                    border-bottom-width: 1px;
                }
                table.DAI01210Grid1 tbody td[rowspan] {
                    border-bottom-width: 1px;
                    vertical-align: top;
                    white-space: pre-wrap;
                }
                table.DAI01210Grid1 tbody tr:nth-child(7n) td {
                    border-bottom-width: 1px;
                }
                table.DAI01210Grid1 tbody td p {
                    margin: 0px;
                }
                table.DAI01210Grid1 tbody td:not([colspan]) p {
                    font-size: 7.0pt;
                }
                table.DAI01210Grid1 tr.grand-summary:nth-child(7n+1) td:nth-child(1) p:before {
                    content: " 【 ";
                }
                table.DAI01210Grid1 tr.grand-summary:nth-child(7n+1) td:nth-child(1) p:after {
                    content: " 】";
                }
                table.DAI01210Grid1 tr.grand-summary:nth-child(7n+1) td:nth-child(1) p{
                    margin-left: 10px;
                    letter-spacing: 0.3em;
                }
            `;

            //一時Grouping
            grid.Group().option({ on: true });

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01210Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                70,
                                false,
                                false,
                                false,
                                true,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ on: false });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A3 landscape; } }",
                printable: printable,
            };
            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
        onIncludeZeroChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid1 = vue.DAI01210Grid1;
            var grid2 = vue.DAI01210Grid2;

            if (!grid1) return;

            var rules = [];
            //0データ非表示
            if (vue.viewModel.PrintOrder == 0 && vue.viewModel.IsIncludeZero == 0) {
                rules.push({ dataIndx: "売上データ明細有無", condition: "equal", value: "1" });
            }

            grid1.filter({ oper: "replace", rules: rules });
            grid2.filter({ oper: "replace", rules: rules });
        },
    }
}
</script>
