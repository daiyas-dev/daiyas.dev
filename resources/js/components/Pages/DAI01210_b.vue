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
                    :nameWidth=250
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
            <div class="col-md-4">
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
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
            <div class="col-md-3">
                <label style="width: unset;">得意先登録年月</label>
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
                    :nameWidth=250
                    :isShowAutoComplete=true
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
                PrintOrder: "0",
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
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: false,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [false, false],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                    [
                        "商品",
                        function (rowData) {
                            return _.padStart(rowData.商品ＣＤ, 3, " ") + ":" + rowData.商品名;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コース名",
                        dataIndx: "コース",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先",
                        width: 200, maxWidth: 200, minWidth: 200,
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
                        title: "商品",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
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
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: false,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [false, false],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "コース名",
                        dataIndx: "コース",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先",
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
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
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
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
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");

            vue.refreshCols();
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
                var days = _.range(1, mt.endOf("month").format("DD") * 1 + 1);
                var max = 31;
                days = days.length == 31 ? days : days.concat(_.range(0, days.length - 31).fill(null));

                var newCols = grid1.options.colModel
                    .filter(v => !!v.fixed)
                    .concat(
                        ...days.map((d, i) => {
                            var date = mt.startOf("month").add("days", i);

                            return {
                                title: !!d ? (date.format("ddd") + "<br>" + d) : "<br>",
                                dataIndx: !!d ? date.format("DD") : ("empty" + i),
                                dataType: "integer",
                                format: "#,##0",
                                width: 50, maxWidth: 50, minWidth: 50,
                                summary: {
                                    type: "TotalInt",
                                },
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "0" };
                                    }
                                    return ui;
                                },
                            }
                        })
                    );

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

            //ソート順変更
            var sorter = [{ dataIndx: vue.viewModel.PrintOrder == "0" ? "得意先ＣＤ" : "コースＣＤ", dir: "up" }];

            //ソート変更
            grid.sort( { sorter: sorter });
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
        conditionChanged: function(force) {
            var vue = this;
            var grid1 = vue.DAI01210Grid1;
            var grid2 = vue.DAI01210Grid2;

            if (!grid1 || !grid2 || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;

            if (!vue.ProductList.length) return;
            vue.setSummaryRow();

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
                    "コース名": i == 0 ? "合計" : "",
                    "商品名": p.商品名,
                    pq_fn: pqfn,
                };
            });

            grid2.options.summaryData = vue.ProductList.map((p, i) => {
                var pqfn = _.reduce(
                    grid2.options.colModel.filter(c => !c.fixed),
                    (acc, c) => {
                        acc[c.dataIndx] = "SUMIF(C:C, '"
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

            grid.options.colModel.find(c => c.dataIndx == "得意先").hidden = vue.viewModel.PrintOrder != "0";
            grid.options.colModel.find(c => c.dataIndx == "コース").hidden = vue.viewModel.PrintOrder == "0";

            var group = _(res)
                .groupBy(v => vue.viewModel.PrintOrder == "0" ? v.得意先ＣＤ : v.コースＣＤ)
                .forIn((v, k, o) => {
                    var rows = vue.ProductList.map(p => {
                        var ret = {};
                        var key = vue.viewModel.PrintOrder == "0" ? "得意先" : "コース";
                        ret[key] = k + ":" + v[0][key + "名"];
                        ret.商品区分 = p.商品区分;
                        ret.商品名 = p.商品名;
                        ret = _.reduce(
                            v.filter(r => r.商品区分 == p.商品区分),
                            (acc, r) => {
                                acc[moment(r.日付).format("DD")] = r.個数;
                                return acc;
                            },
                            ret
                        );
                        return ret;
                    });
                    o[k] = rows;
                });

            var list = _.flatten(_.values(group));
            console.log(list);

            // res.forEach(v => {
            //     v.コース = v.コースＣＤ + ":" + v.コース名;
            //     v.得意先 = v.得意先ＣＤ + ":" + v.得意先名;
            //     var product = vue.ProductList.find(p => p.商品区分 == v.商品区分);
            //     v.商品名 = !!product ? product.商品名 : "";
            //     return v;
            // });

            //mergeCellsの設定
            // var pos = 0;
            // _(res).groupBy(v => v.コース名)
            //     .forIn((v, k) => {
            //         var cells = {
            //             r1: pos,
            //             c1: 2,
            //             rc: v.length,
            //             cc: 1,
            //         };
            //         grid.options.mergeCells.push(cells);
            //         pos += v.length;
            //     });

            var grid2 = vue.DAI01210Grid2;
            grid2.options.dataModel.data = list;
            grid2.refreshDataAndView();

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
    }
}
</script>
