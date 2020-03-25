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
                <label>年月</label>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onCourseKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース開始</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseStart"
                    ref="PopupSelect_CourseStart"
                    :vmodel=viewModel
                    bind="CourseStart"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseStartChanged
                    :isShowAutoComplete=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース終了</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseEnd"
                    ref="PopupSelect_CourseEnd"
                    :vmodel=viewModel
                    bind="CourseEnd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 9999 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                    :onAfterChangedFunc=onCourseEndChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先開始</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, KeyWord: null }"
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
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onCustomerStartChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先終了</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, KeyWord: null }"
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
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onCustomerEndChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先登録年月</label>
            </div>
            <div class="col-md-2">
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
                    :inputWidth=75
                    :nameWidth=150
                    :isShowAutoComplete=true
                />
            </div>
        </div>

        <PqGridWrapper
            id="DAI01210Grid1"
            ref="DAI01210Grid1"
            dataUrl="/DAI01210/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :SearchOnCreate=false
            :SearchOnActivate=false
            :onAfterSearchFunc=this.onAfterSearchFunc
            :resizeFunc=this.gridResizeFunc
            :onCellKeyDownFunc=onCellKeyDownFunc
        />
    </form>
</template>

<style>
/* 合計行 */
#DAI01210Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
#DAI01210Grid1 .pq-grid-row:nth-child(even) .pq-grid-cell.pq-merge-cell {
    background: white;
    color: initial;
}
#DAI01210Grid1 .pq-grid-row:nth-child(odd) .pq-grid-cell.pq-merge-cell {
    background: #e6f4ff;
    color: initial;
}

label{
    width: 80px;
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
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月").format("YYYYMM"),
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
            ScreenTitle: "日次処理 > 日配売上日計表",
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
            DAI01210Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                rowHt: 25,
                rowHtSum: 25,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: true,
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
                    dataIndx: ["CustomerIndex", "CustomerInfo"],
                    titleDefault: "{0}",
                    collapsed: [false],
                    merge: true,
                    showSummary: [false],
                    grandSummary: false,
                    summaryEdit: false,
                    icon: ["pq-group-toggle-none"],
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
                        title: "工場区分",
                        dataIndx: "工場区分",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        fixed: true,
                        width: 200, maxWidth: 200, minWidth: 200,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return { text: ui.rowData.コース名 };
                            } else {
                                if (!ui.Export) {
                                    ui.style.push("align-items: flex-start;");
                                }
                                return { text: ui.rowData.コースＣＤ + "<br>" + ui.rowData.コース名 };
                            }
                        },
                    },
                    {
                        title: "商品",
                        dataIndx: "商品名",
                        dataType: "string",
                        fixed: true,
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ",
                        hidden: true,
                        fixed: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.refreshCols();

            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01210Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
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
            // vue.viewModel.TargetDate = moment();
            vue.viewModel.TargetDate = moment("2019/09/04");    //TODO: debug


            var grid = vue.DAI01210Grid1;

            //watcher
            vue.$watch(
                "$refs.DAI01210Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01210Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            if (!!vue.CheckInterVal) clearInterval(vue.CheckInterVal);
            //TODO:
            // vue.CheckInterVal = setInterval(vue.updateCheck, 10000);

            vue.$root.$on("DAI01210_updateCheck", vue.updateCheck);
        },
        updateCheck: function() {
            var vue = this;
            var grid = vue.DAI01210Grid1;

            //更新チェック
            if (!grid.getData().length) return;

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            var checkParams = _.cloneDeep(params);
            checkParams.noCache = true;
            axios.post("/DAI01210/UpdateCheck", checkParams)
                .then(res => {
                    if (res.data.最新修正日時 != vue.viewModel.UpdateDate) {
                        vue.viewModel.UpdateDate = res.data.最新修正日時;
                        grid.blinkDiff(res.data.list);
                    }
                })
                .catch(err => {
                    console.log(err);
                });
        },
        gridResizeFunc: function(grid) {
            var oldHeight = grid.options.height;
            var newHeight = window.innerHeight
                          - grid.widget().position().top
                          - $("#footer-bar:visible").outerHeight()
                          - 20;

            if (_.round(newHeight) != _.round(oldHeight)) {
                grid.options.height += (_.round(newHeight) - _.round(oldHeight));
                grid.refresh();
            }
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
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseStartChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseEndChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerStartChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerEndChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI01210Grid1;

            console.log("conditionChanged", vue.viewModel);

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CourseKbn) return;

            grid.showLoading();

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            //キャッシュ無効
            params.noCache = true;

            //更新チェック
            var checkParams = _.cloneDeep(params);
            checkParams.noCache = true;
            axios.post("/DAI01210/UpdateCheck", checkParams)
                .then(res => {
                    grid.hideLoading();
                    if (!!force || res.data.最新修正日時 != vue.viewModel.UpdateDate) {
                        vue.viewModel.UpdateDate = res.data.最新修正日時;
                        grid.searchData(params, false, null, callback);
                    }
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

            //summaryDataの設定
            grid.options.summaryData = [];
            // _(res).groupBy(v => _.padStart(v.商品ＣＤ, 3, " ") + ":" + v.商品名)
            _(res).groupBy(v => v.商品名)
                .forIn((v, k) => {
                    var summary = {
                        summaryRow: true,
                        "コース名": grid.options.summaryData.length ? "" : "合計",
                        "商品名": k,
                        pq_fn:{
                            "1": "SUMIF(D:D, '" + k + "', F:F)",
                            "2": "SUMIF(D:D, '" + k + "', G:G)",
                            "3": "SUMIF(D:D, '" + k + "', H:H)",
                            "4": "SUMIF(D:D, '" + k + "', I:I)",
                            "5": "SUMIF(D:D, '" + k + "', J:J)",
                            "6": "SUMIF(D:D, '" + k + "', K:K)",
                            "7": "SUMIF(D:D, '" + k + "', L:L)",
                            "8": "SUMIF(D:D, '" + k + "', M:M)",
                            "9": "SUMIF(D:D, '" + k + "', N:N)",
                            "10": "SUMIF(D:D, '" + k + "', O:O)",
                            "11": "SUMIF(D:D, '" + k + "', P:P)",
                            "12": "SUMIF(D:D, '" + k + "', Q:Q)",
                            "13": "SUMIF(D:D, '" + k + "', R:R)",
                            "14": "SUMIF(D:D, '" + k + "', S:S)",
                            "15": "SUMIF(D:D, '" + k + "', T:T)",
                            "16": "SUMIF(D:D, '" + k + "', U:U)",
                            "17": "SUMIF(D:D, '" + k + "', V:V)",
                            "18": "SUMIF(D:D, '" + k + "', W:W)",
                            "19": "SUMIF(D:D, '" + k + "', X:X)",
                            "20": "SUMIF(D:D, '" + k + "', Y:Y)",
                            "21": "SUMIF(D:D, '" + k + "', Z:Z)",
                            "22": "SUMIF(D:D, '" + k + "', AA:AA)",
                            "23": "SUMIF(D:D, '" + k + "', AB:AB)",
                            "24": "SUMIF(D:D, '" + k + "', AC:AC)",
                            "25": "SUMIF(D:D, '" + k + "', AD:AD)",
                            "26": "SUMIF(D:D, '" + k + "', AE:AE)",
                            "27": "SUMIF(D:D, '" + k + "', AF:AF)",
                            "28": "SUMIF(D:D, '" + k + "', AG:AG)",
                            "29": "SUMIF(D:D, '" + k + "', AH:AH)",
                            "30": "SUMIF(D:D, '" + k + "', AI:AI)",
                            "31": "SUMIF(D:D, '" + k + "', AJ:AJ)",
                            "合計": "SUMIF(D:D, '" + k + "', AK:AK)",
                            "平均": "SUMIF(D:D, '" + k + "', AL:AL)",
                            "土曜合計": "SUMIF(D:D, '" + k + "', AM:AM)",
                            "土曜平均": "SUMIF(D:D, '" + k + "', AN:AN)",
                            "日曜合計": "SUMIF(D:D, '" + k + "', AO:AO)",
                            "日曜平均": "SUMIF(D:D, '" + k + "', AP:AP)",
                        }
                    };

                    grid.options.summaryData.push(summary);
                });

            //mergeCellsの設定
            var pos = 0;
            _(res).groupBy(v => v.コース名)
                .forIn((v, k) => {
                    var cells = {
                        r1: pos,
                        c1: 2,
                        rc: v.length,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cells);
                    pos += v.length;
                });

            return res;
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return ret;
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
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.bushoCd && !!newVal.courseKbn;
            return ret;
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

        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01210Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                var newCols = grid.options.colModel.filter(v => !!v.fixed);
                for (var i=1; i<=31; i++) {
                    newCols.push(
                        {
                            title: i,
                            dataIndx: i,
                            dataType: "integer",
                            format: "#,##0",
                            width: 60, maxWidth: 60, minWidth: 60,
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
                    );

                }
                newCols.push(
                    {
                        title: "合計",
                        dataIndx: "合計",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
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
                        width: 70, maxWidth: 70, minWidth: 70,
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
                        width: 70, maxWidth: 70, minWidth: 70,
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
                        width: 70, maxWidth: 70, minWidth: 70,
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
                        width: 70, maxWidth: 70, minWidth: 70,
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
                        width: 70, maxWidth: 70, minWidth: 70,
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
                )

                //列定義更新
                grid.options.colModel = newCols;
                grid.refreshCM();
                grid.refresh();

                if (!!grid) grid.hideLoading();

                //条件変更ハンドラ
                vue.conditionChanged();
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
        onCellKeyDownFunc: function(grid, ui, event) {
            var vue = this;

            switch (event.key) {
                case "Enter":
                    vue.showDetail();
                    return false;
                case "ArrowUp":
                    if (ui.rowIndx > 0) {
                        grid.setSelection(null);
                        grid.setSelection({rowIndx: ui.rowIndx - 1});
                    }
                    return false;
                case "ArrowDown":
                    if (ui.rowIndx < grid.getData().length - 1) {
                        grid.setSelection(null);
                        grid.setSelection({rowIndx: ui.rowIndx + 1});
                    }
                    return false;
                default:
                    return true;
            }
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01210Grid1;
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
