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
            <div class="col-md-1">
                <label>配送日</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コースCD</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
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
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
            <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-4">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 275px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="Grid1Container">
                <PqGridWrapper
                    id="DAI01100Grid1"
                    ref="DAI01100Grid1"
                    dataUrl="/DAI01100/Search"
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
                    id="DAI01100Grid2"
                    ref="DAI01100Grid2"
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
#DAI01100Grid1 .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped),
#DAI01100Grid2 .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped)
{
    background-color: #e6f4ff !important;
}
#DAI01100Grid2 .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped):not(.pq-state-select) .pq-grid-cell.toggle:not([id^=pq-sum]) {
    background-color: #ffc7ac !important;
}
#DAI01100Grid1 .pq-grid-row.pq-striped:not([id^="pq-head-row"]),
#DAI01100Grid2 .pq-grid-row.pq-striped:not([id^="pq-head-row"])
{
    background-color: white !important;
}
/* マージセル */
#DAI01100Grid1 .pq-grid-row .pq-grid-cell.pq-merge-cell,
#DAI01100Grid2 .pq-grid-row .pq-grid-cell.pq-merge-cell
{
    background: white;
    color: initial;
}
#DAI01100Grid1 div.tk_info {
    width: 100%;
}
#DAI01100Grid1 div.tk_code {
    width: 70%;
}
#DAI01100Grid1 div.tk_payment {
    width: 30%;
}
#DAI01100Grid1 div.tk_name {
    width: 100%;
}
/* 合計行 */
#DAI01100Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner),
#DAI01100Grid2 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner)
{
    font-weight: bold;
    color: black;
    background-color: transparent !important;
}
label{
    width: 80px;
}
</style>
<style scoped>
.Grid1Container {
    width: calc(100vw / var(--ratio) - 260px);
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
    name: "DAI01100",
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
                DeliveryDate: moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                noCache: true,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = _.values(newVal).some(v => !v);
                vue.footerButtons.find(v => v.id == "DAI01100Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 日配売上入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                CourseKbn: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
            },
            DAI01100Grid1: null,
            DAI01100Grid2: null,
            ProductList: null,
            BikouList: [
                "１月分入金",
                "２月分入金",
                "３月分入金",
                "４月分入金",
                "５月分入金",
                "６月分入金",
                "７月分入金",
                "８月分入金",
                "９月分入金",
                "１０月分入金",
                "１１月分入金",
                "１２月分入金",
                "翌日分",
                "翌週分",
                "翌月分",
            ],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                freezeCols: 3,
                rowHtHead: 25,
                rowHt: 30,
                rowHtSum: 30,
                editable: true,
                fillHandle: "",
                numberCell: { show: false },
                autoRow: false,
                editable: true,
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
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                cellClick: function (event, ui) {
                    var grid = this;
                    vue.toggleGrid2(false);
                    if (ui.$td.hasClass("autocomplete-cell") || ui.$td.hasClass("select-cell")) {
                        grid.editCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                    }
                },
                summaryData: [
                    {
                        summaryRow: true,
                        pq_fn:{
                            "得意先名": "TRIM('合計')",
                        }
                    },
                    {
                        summaryRow: true,
                        pq_fn:{
                        }
                    },
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "No.",
                        dataIndx: "CustomerIndex", dataType: "interger", align: "center",
                        width: 30, maxWidth: 30, minWidth: 30,
                        fixed: true,
                    },
                    {
                        title: "得意先",
                        dataIndx: "得意先名", dataType: "string",
                        width: 150, maxWidth: 1000, minWidth: 150,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                ui.column.align = "center";
                                return ui;
                            } else {
                                var el = $("<div>").addClass("tk_info").addClass(ui.rowData.得意先売掛現金区分 == "1" ? "tk_credit" :"tk_cash")
                                    .append($("<div>").addClass("d-flex")
                                        .append($("<div>").addClass("tk_code text-left pl-2").text(ui.rowData.得意先ＣＤ))
                                        .append($("<div>").addClass("tk_payment").text(ui.rowData.得意先売掛現金区分名称))
                                    )
                                    .append($("<div>").addClass("tk_name text-left").text(ui.rowData.得意先名))
                                    ;
                                return { text: el[0].outerHTML };
                            }
                        },
                        fixed: true,
                    },
                    {
                        title: "",
                        dataIndx: "売掛現金区分名称", dataType: "string", align: "center",
                        width: 35, maxWidth: 35, minWidth: 35,
                        fixed: true,
                    },
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
                rowDblClick: function (event, ui) {
                    vue.showDetail(false, ui.rowData);
                },
            },
            grid2Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                strNoRows: "",
                columnBorders: true,
                rowHtHead: 25,
                rowHt: 30,
                rowHtSum: 30,
                width: 245,
                editable: true,
                fillHandle: "",
                numberCell: { show: false },
                autoRow: false,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
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
                    hideRows: false,
                },
                groupModel: {
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                cellClick: function (event, ui) {
                    var grid = this;
                    vue.toggleGrid2(true);
                    if (ui.$td.hasClass("autocomplete-cell") || ui.$td.hasClass("select-cell")) {
                        grid.editCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                    }
                },
                summaryData: [
                    {
                        summaryRow: true,
                        pq_fn:{
                        }
                    },
                    {
                        summaryRow: true,
                        pq_fn:{
                        }
                    },
                ],
                formulas:[
                    [
                        "売上",
                        function (rowData) {
                            return _(rowData).pickBy((v, k) => k.startsWith("金額")).map(v => v * 1).values().sum()
                                 + (rowData.チケット売 || 0) * 1;
                        }
                    ],
                    [
                        "掛売上",
                        function (rowData) {
                            return _(rowData).pickBy((v, k) => k.startsWith("金額")).map(v => v * 1).values().sum()
                                + (rowData.チケット売 || 0) * 1;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "No.",
                        dataIndx: "CustomerIndex", dataType: "interger", align: "center",
                        hidden: true,
                    },
                    {
                        title: "",
                        dataIndx: "得意先名", dataType: "string", align: "center",
                        hidden: true,
                    },
                    {
                        title: "",
                        dataIndx: "売掛現金区分名称", dataType: "string", align: "center",
                        hidden: true,
                    },
                    {
                        title: "その他",
                        dataIndx: "その他", dataType: "integer",
                        width: 115, minWidth: 115, maxWidth: 115,
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "個数_その他",
                                dataType: "integer",
                                format: "#,###",
                                width: 40, maxWidth: 40, minWidth: 40,
                                cls: "toggle",
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                toggle: true,
                            },
                            {
                                title: "金額",
                                dataIndx: "金額_その他",
                                dataType: "integer",
                                format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                cls: "toggle",
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                toggle: true,
                            },
                        ],
                        hidden: true,
                        toggle: true,
                    },
                    {
                        title: "チケット売",
                        dataIndx: "チケット売", dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        cls: "toggle",
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        hidden: true,
                        toggle: true,
                    },
                    {
                        title: "値引",
                        dataIndx: "値引", dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        cls: "toggle",
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        hidden: true,
                        toggle: true,
                    },
                    {
                        title: "現金売上",
                        width: 75, maxWidth: 75, minWidth: 75,
                        colModel: [
                            {
                                title: "掛売上",
                                dataIndx: "売上",
                                dataType: "integer",
                                format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                cls: "toggle",
                                render: ui => {
                                    // zero to blank
                                    return ui.rowData[ui.dataIndx] || "";
                                },
                                toggle: false,
                            },
                        ],
                        hidden: true,
                        toggle: true,
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, minWidth: 75, maxWidth: 75,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        toggle: false,
                    },
                    {
                        title: "摘要",
                        dataIndx: "備考", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                        colModel: [
                            {
                                title: "備考",
                                dataIndx: "備考", dataType: "string",
                                width: 150, minWidth: 150, maxWidth: 150,
                                editable: true,
                                autocomplete: {
                                    trigger: ui => ui.rowData.売掛現金区分名称 == "現金",
                                    source: vue.BikouList,
                                    bind: "備考",
                                    noCheck: true,
                                    selectSave: true,
                                    AutoCompleteFunc: () => vue.BikouList,
                                    AutoCompleteMinLength: 0,
                                },
                                toggle: false,
                            }
                        ],
                        toggle: false,
                    },
                ],
                scroll: function (event, ui) {
                    var grid = this;

                    $("body").find("[id^=tooltip]").tooltip("hide");

                    vue.syncScroll(grid.scrollY());
                },
                rowDblClick: function (event, ui) {
                    if (ui.dataIndx != "入金額" && ui.dataIndx != "備考") {
                        vue.showDetail(false, ui.rowData);
                    }
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "明細入力", id: "DAI01100Grid1_Detail", disabled: true, shortcut: "F1",
                    onClick: function () {
                        vue.showDetail(false);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI01100Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        if (vue.DAI01100Grid1.colModel.some(v => !!v.custom)) {
                            vue.DAI01100Grid1.searchData(vue.searchParams);
                        } else {
                            vue.refreshCols();
                        }
                    }
                },
                { visible: "true", value: "分配入力", id: "DAI01100Grid1_Bunpai", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.showBunpai(false);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01100Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI01100Grid1;
                        var grid2 = vue.DAI01100Grid2;

                        var changes = _.cloneDeep(grid2.getChanges());

                        var SaveList = _(changes.updateList)
                            .groupBy(r => r.得意先ＣＤ)
                            .values()
                            .value()
                            .map(r => _.reduce(r, (a, v) => {
                                a.入金日付 = vue.searchParams.DeliveryDate;
                                a.伝票Ｎｏ = v.伝票Ｎｏ;
                                a.部署ＣＤ = vue.searchParams.BushoCd;
                                a.得意先ＣＤ = v.得意先ＣＤ;
                                a.入金区分 = 1;
                                a.現金 = (a.現金 || 0) + (v.売掛現金区分 == 0 ? v.入金額 : 0) * 1;
                                a.小切手 = 0;
                                a.振込 = 0;
                                a.バークレー = 0;
                                a.その他 = 0;
                                a.相殺 = 0;
                                a.値引 = 0;
                                if (v.備考 == undefined) {
                                    a.摘要 = "";
                                    a.備考 = "";
                                }
                                else
                                {
                                    a.摘要 = (a.摘要 || "") + (v.売掛現金区分 == 0 ? v.備考 : "");
                                    a.備考 = (a.備考 || "") + (v.売掛現金区分 == 1 ? v.備考 : "");
                                }

                                a.請求日付 = v.請求日付;
                                a.予備金額１ = 0;
                                a.予備金額２ = 0;
                                a.予備ＣＤ１ = 0;
                                a.予備ＣＤ２ = 0;
                                a.修正日 = v.入金データ修正日;
                                a.修正担当者ＣＤ = vue.getLoginInfo().uid;

                                if(a.摘要.length>20)
                                {
                                    $.dialogInfo({
                                        title: "登録チェック",
                                        contents: "得意先[" + v.得意先ＣＤ + "]<br/>摘要は20文字以内で入力して下さい。",
                                    });
                                    return false;
                                }
                                /*
                                if(a.備考.length>60)
                                {
                                    $.dialogInfo({
                                        title: "登録チェック",
                                        contents: "得意先[" + v.得意先ＣＤ + "]<br/>備考は60文字以内で入力して下さい。",
                                    });
                                    return false;
                                }
                                */

                                return a;
                            }, {}))
                            ;
                        //SaveListでfalseでない値が戻ってきたら登録実施(falseは登録チェックエラーとみなす)
                        var check_result = [];
                        check_result = SaveList.filter(v => v == false);
                        if(check_result[0]==false)
                        {
                            return false;
                        }

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI01100/Save",
                                params: {
                                    SaveList: SaveList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        var compare = vue.mergeData(res.edited);
                                        var d = diff(vue.DAI01100Grid2.getPlainPData(), compare);

                                        _.forIn(d, (v, k) => {
                                            var r = _.omitBy(v, (vv, kk) => vv == undefined);
                                            if (_.isEmpty(r)) {
                                                delete d[k];
                                            } else {
                                                d[k] = r;
                                            }
                                        })

                                        if (_.isEmpty(d)) {
                                            grid2.commit();
                                        } else {
                                            if (res.skipped) {
                                                $.dialogInfo({
                                                    title: "登録チェック",
                                                    contents: "他で変更されたデータがあります。",
                                                });
                                            }

                                            grid2.blinkDiff(compare, true);
                                        }

                                        return false;
                                    },
                                },
                            }
                        );
                    }
                },
                { visible: "true", value: "新規明細入力", id: "DAI01100Grid1_DetailNew", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.showDetail(true);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI01100Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01100Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
            vue.$watch(
                "$refs.DAI01100Grid1.selectionRow",
                row => {
                    vue.footerButtons.find(v => v.id == "DAI01100Grid1_Bunpai").disabled = row.rowData.分配先件数 == 0;
                    vue.DAI01100Grid2.refresh();
                }
            );
            vue.$watch(
                "$refs.DAI01100Grid2.selectionRow",
                row => {
                    vue.footerButtons.find(v => v.id == "DAI01100Grid1_Bunpai").disabled = row.rowData.分配先件数 == 0;
                    vue.DAI01100Grid1.refresh();
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //列定義更新
            vue.refreshCols();
        },
        refreshCols: function() {
            var vue = this;
            var grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid1 = vue.DAI01100Grid1;
                    if (!!grid1 && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid1);
                    }
                }, 100);
            })
            .then((grid1) => {
                grid1.showLoading();

                axios.post("/DAI01100/ColSearch", { BushoCd: vue.viewModel.BushoCd, noCache:true })
                    .then(response => {
                        var res = _.cloneDeep(response.data);
                        vue.ProductList = res;

                        var newCols = grid1.options.colModel.filter(v => !!v.fixed);

                        var productCols = res.map((v, i) => {
                            return {
                                title: v.商品名,
                                custom: true,
                                hasSummary: true,
                                cd: v.商品ＣＤ,
                                colModel: [
                                    {
                                        title: "個数",
                                        dataIndx: "個数_" + v.商品ＣＤ,
                                        dataType: "integer",
                                        format: "#,###",
                                        width: 40, maxWidth: 40, minWidth: 40,
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "" };
                                            }
                                            return ui;
                                        },
                                    },
                                    {
                                        title: "金額",
                                        dataIndx: "金額_" + v.商品ＣＤ,
                                        dataType: "integer",
                                        format: "#,##0",
                                        width: 75, maxWidth: 75, minWidth: 75,
                                        render: ui => {
                                            if (!ui.rowData[ui.dataIndx]) {
                                                return { text: "" };
                                            }
                                            return ui;
                                        },
                                    },
                                ],
                            };
                        });

                        newCols = newCols.concat(productCols);

                        //合計行設定
                        newCols.forEach((c, i) => {
                            if (!!c.hasSummary) {
                                var cidx = i * 2 - 3;
                                var getCN = v => String.fromCharCode("A".charCodeAt() + v);
                                var getRange = v => {
                                    var intVal = Math.floor(v / 26);
                                    var modVal = v % 26;
                                    var cn = (intVal > 0 ? getCN(intVal - 1) : "") + (intVal > 0 || modVal > 0 ? getCN(modVal) : "");
                                    var range = cn + ":" + cn;
                                    return range;
                                };

                                grid1.options.summaryData[0].pq_fn["個数_" + c.cd] = "SUMIF(C:C, '現金', " + getRange(cidx) + ")";
                                grid1.options.summaryData[1].pq_fn["個数_" + c.cd] = "SUMIF(C:C, '売掛', " + getRange(cidx) + ")";
                                grid1.options.summaryData[0].pq_fn["金額_" + c.cd] = "SUMIF(C:C, '現金', " + getRange(cidx + 1) + ")";
                                grid1.options.summaryData[1].pq_fn["金額_" + c.cd] = "SUMIF(C:C, '売掛', " + getRange(cidx + 1) + ")";
                            }
                        });

                        //列定義更新
                        grid1.options.colModel = newCols;
                        grid1.refreshCM();
                        grid1.refresh();

                        //集計grid設定
                        var grid2 = vue.DAI01100Grid2;
                        grid2.options.summaryData[0].pq_fn["個数_その他"] = "SUMIF(C:C, '現金', D:D)";
                        grid2.options.summaryData[0].pq_fn["金額_その他"] = "SUMIF(C:C, '現金', E:E)";
                        grid2.options.summaryData[0].pq_fn["チケット売"] = "SUMIF(C:C, '現金', F:F)";
                        grid2.options.summaryData[0].pq_fn["値引"] = "SUMIF(C:C, '現金', G:G)";
                        grid2.options.summaryData[0].pq_fn["売上"] = "SUMIF(C:C, '現金', H:H)";
                        grid2.options.summaryData[0].pq_fn["入金額"] = "SUMIF(C:C, '現金', I:I)";

                        grid2.options.summaryData[1].pq_fn["個数_その他"] = "SUMIF(C:C, '売掛', D:D)";
                        grid2.options.summaryData[1].pq_fn["金額_その他"] = "SUMIF(C:C, '売掛', E:E)";
                        grid2.options.summaryData[1].pq_fn["チケット売"] = "SUMIF(C:C, '売掛', F:F)";
                        grid2.options.summaryData[1].pq_fn["値引"] = "SUMIF(C:C, '売掛', G:G)";
                        grid2.options.summaryData[1].pq_fn["売上"] = "SUMIF(C:C, '売掛', H:H)";

                        //列定義更新
                        grid2.refreshCM();
                        grid2.refresh();

                        if (!!grid1) grid1.hideLoading();

                        //条件変更ハンドラ
                        vue.conditionChanged();
                    });
            })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: "各種テーブル検索失敗",
                    contents: "各種テーブル検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD"), noCache: true }
            )
                .then(res => {
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01100Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.CourseCd) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            } else {
                grid.searchData(vue.searchParams, false, null, callback);
            }
        },
        resizeGrid: function(grid) {
            var vue = this;
            var widget = grid.widget();

            var oldH = widget.outerHeight();
            var containerH = widget.closest(".body-content").outerHeight(true);
            var otherH = _.sum(widget.closest(".row").siblings(".row").map((i, el) => $(el).outerHeight(true)));

            var newH = containerH - otherH - 10;

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
            var grid1 = vue.DAI01100Grid1;
            var grid2 = vue.DAI01100Grid2;

            if (grid1.scrollY() != val) grid1.scrollY(val);
            if (grid2.scrollY() != val) grid2.scrollY(val);
        }, 0),
        toggleGrid2: function(show) {
            var vue = this;
            var grid2 = vue.DAI01100Grid2;

            if (vue.grid2Expand == show) return;

            grid2.options.colModel.forEach(c => {
                var isHidden = c => (c.toggle == undefined) || (!!c.toggle && !show);
                c.hidden = isHidden(c);
                (c.colModel || []).forEach(cc => cc.hidden = isHidden(cc));
            });

            grid2.options.width = _.sum(grid2.options.colModel.filter(c => !c.hidden).map(c => c.width)) + 20;
            grid2.refreshCM();
            grid2.refresh();

            vue.grid2Expand = show;
        },
        checkGridChangedFunc: function(grid) {
            var vue = this;
            var grid2 = vue.DAI01100Grid2;

            return grid2.isChanged();
        },
        mergeData: function (data) {
            var vue = this;

            var merged = _.values(_.groupBy(data, v => v.得意先ＣＤ + "," + v.売掛現金区分))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.得意先ＣＤ = v.得意先ＣＤ;
                                acc.得意先名 = v.得意先名;
                                acc.分配先件数 = v.分配先件数 * 1;
                                acc.得意先売掛現金区分 = v.得意先売掛現金区分;
                                acc.得意先売掛現金区分名称 = v.得意先売掛現金区分名称;
                                acc.得意先支払方法 = v.得意先支払方法;
                                acc.得意先支払方法名称 = v.得意先支払方法名称;
                                acc.入金額 = v.入金額 ;
                                //2020/12/16 備考は最後に取得したもののみ生かすように変更
                                //acc.備考 = (acc.備考 || "") + ((v.売掛現金区分 == 0 ? v.備考 : v.備考テキスト) || "");
                                acc.備考 = ((v.売掛現金区分 == 0 ? v.備考 : v.備考テキスト) || "");
                                acc.入金日付 = v.入金日付 ;
                                acc.伝票Ｎｏ = v.伝票Ｎｏ ;
                                acc.請求日付 = v.請求日付 ;
                                acc.入金データ修正日 = v.入金データ修正日 ;

                                acc.売掛現金区分 = v.売掛現金区分;
                                acc.売掛現金区分名称 = v.売掛現金区分名称;

                                acc["現金個数_" + v.商品ＣＤ] = (acc["現金個数_" + v.商品ＣＤ] || 0) + v.現金個数 * 1;
                                acc["現金金額_" + v.商品ＣＤ] = (acc["現金金額_" + v.商品ＣＤ] || 0) + v.現金金額 * 1;
                                acc["掛売個数_" + v.商品ＣＤ] = (acc["掛売個数_" + v.商品ＣＤ] || 0) + v.掛売個数 * 1;
                                acc["掛売金額_" + v.商品ＣＤ] = (acc["掛売金額_" + v.商品ＣＤ] || 0) + v.掛売金額 * 1;
                                acc["分配元数量_" + v.商品ＣＤ] = (acc["分配元数量_" + v.商品ＣＤ] || 0) + v.分配元数量 * 1;

                                if (!!vue.ProductList.find(p => p.商品ＣＤ == v.商品ＣＤ)) {
                                    acc["個数_" + v.商品ＣＤ] = (acc["個数_" + v.商品ＣＤ] || 0)
                                        + (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数 ) * 1 + acc["分配元数量_" + v.商品ＣＤ];
                                    acc["金額_" + v.商品ＣＤ] = (acc["金額_" + v.商品ＣＤ] || 0)
                                        + (v.売掛現金区分 == 0 ? v.現金金額 : v.掛売金額 ) * 1;
                                } else if (v.商品区分 == 9) {
                                    //チケット
                                    acc["チケット売"] = (acc["チケット売"] || 0)
                                        + (v.売掛現金区分 == 0 ? v.現金金額 : v.掛売金額 ) * 1;
                                } else {
                                    acc["個数_その他"] = (acc["個数_その他"] || 0)
                                        + (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数 ) * 1;
                                    acc["金額_その他"] = (acc["金額_その他"] || 0)
                                        + (v.売掛現金区分 == 0 ? v.現金金額 : v.掛売金額 ) * 1;
                                }
                                acc["値引"] = (acc["値引"] || 0) + (v.売掛現金区分 == 0 ? v.現金値引 : v.掛売値引 ) * 1;

                                acc.CustomerIndex = acc.CustomerIndex || parseInt(i / 2 + 1);

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });

            return merged;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            vue.orgData = _.cloneDeep(res);
            var merged = vue.mergeData(res);

            //mergeCellsの設定
            grid.options.mergeCells = _.flattenDeep(res.filter((r, i) => !(i % 2))
                .map((r, i) => {
                    return [
                        { r1: i * 2, c1: 0, rc: 2, cc: 1 },
                        { r1: i * 2, c1: 1, rc: 2, cc: 1 },
                    ];
                })
            );

            var grid2 = vue.DAI01100Grid2;
            grid2.options.mergeCells = _.flattenDeep(res.filter((r, i) => !(i % 2))
                .map((r, i) => {
                    return [
                        { r1: i * 2, c1: 8, rc: 2, cc: 1 },
                    ];
                })
            );
            grid2.options.dataModel.location = "local";
            grid2.options.dataModel.data = merged;
            grid2.refreshDataAndView();

            vue.footerButtons.find(v => v.id == "DAI01100Grid1_Save").disabled = !merged.length;

            return merged;
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
        showDetail: function(isNew, rowData) {
            var vue = this;
            var grid = vue.DAI01100Grid1;

            var params = null;
            if (!isNew) {
                var data;
                if (!!rowData) {
                    data = _.cloneDeep(rowData);
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    data = _.cloneDeep(rows[0].rowData);
                }

                params = {
                    Parent: vue,
                    ParentGrid: grid,
                    BushoCd: vue.viewModel.BushoCd,
                    BushoNm: vue.viewModel.BushoNm,
                    TargetDate: vue.viewModel.DeliveryDate,
                    CourseKbn: vue.viewModel.CourseKbn,
                    CourseCd: vue.viewModel.CourseCd,
                    CourseNm: vue.viewModel.CourseNm,
                    CustomerCd: data.得意先ＣＤ,
                    CustomerIndex: data.CustomerIndex,
                    PaymentCd: data.得意先支払方法 == 5 ? 2 : data.得意先売掛現金区分,
                    PaymentNm: data.得意先支払方法 == 5 ? data.得意先支払方法名称 : data.得意先売掛現金区分名称,
                };
            } else {
                params = {
                    Parent: vue,
                    ParentGrid: grid,
                    BushoCd: vue.viewModel.BushoCd,
                    BushoNm: vue.viewModel.BushoNm,
                    TargetDate: vue.viewModel.DeliveryDate,
                    CourseKbn: vue.viewModel.CourseKbn,
                    CourseCd: vue.viewModel.CourseCd,
                    CourseNm: vue.viewModel.CourseNm,
                };
            }

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
        showBunpai: function(isNew, rowData) {
            var vue = this;
            var grid = vue.DAI01100Grid1;

            var selection = grid.SelectRow().getSelection();

            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: vue.viewModel.DeliveryDate,
                CourseKbn: vue.viewModel.CourseKbn,
                CourseCd: vue.viewModel.CourseCd,
                CourseNm: vue.viewModel.CourseNm,
                CustomerCd: data.得意先ＣＤ,
                CustomerIndex: data.CustomerIndex,
            };

            PageDialog.show({
                pgId: "DAI01101",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1175,
                height: 600,
            });
        },
    }
}
</script>
