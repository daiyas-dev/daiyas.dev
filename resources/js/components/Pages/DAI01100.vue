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

        <PqGridWrapper
            id="DAI01100Grid1"
            ref="DAI01100Grid1"
            dataUrl="/DAI01100/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :freezeRightCols=6
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
/* stripedの反転 */
#DAI01100Grid1 .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped),
#DAI01100Grid1_right .pq-grid-row:not([id^="pq-head-row"]):not(.pq-striped)
{
    background-color: #e6f4ff !important;
}
#DAI01100Grid1 .pq-grid-row.pq-striped:not([id^="pq-head-row"]),
#DAI01100Grid1_right .pq-grid-row.pq-striped:not([id^="pq-head-row"])
{
    background-color: white !important;
}
/* マージセル */
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
#DAI01100Grid1_right .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner)
{
    font-weight: bold;
    color: black;
    background-color: transparent !important;
}
label{
    width: 80px;
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
            };
        }
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
            PatternList: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                freezeCols: 3,
                rowHtHead: 25,
                rowHt: 25,
                rowHtSum: 25,
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
                    dataIndx: ["CustomerIndex", "CustomerInfo"],
                    titleDefault: "{0}",
                    collapsed: [false],
                    merge: true,
                    showSummary: [false],
                    grandSummary: false,
                    summaryEdit: false,
                    icon: ["pq-group-toggle-none"],
                },
                cellClick: function (event, ui) {
                    var grid = this;
                    console.log("click", grid, grid.isRight);
                },
                summaryData: [
                    {
                        summaryRow: true,
                        pq_fn:{
                            CustomerInfo: "TRIM('合計')",
                            Payment: "TRIM('現金')",
                        }
                    },
                    {
                        summaryRow: true,
                        pq_fn:{
                            CustomerInfo: "TRIM('')",
                            Payment: "TRIM('売掛')",
                        }
                    },
                ],
                formulas:[
                    [
                        "QuantitySummary",
                        function (rowData) {
                            return _(rowData).pickBy((v, k) => k.startsWith("OrderPrice")).map(v => v * 1).values().sum();
                        }
                    ],
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
                        dataIndx: "CustomerInfo", dataType: "string",
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
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                right: true,
                                float: true,
                                fixed: true,
                            },
                            {
                                title: "金額",
                                dataIndx: "金額_その他",
                                dataType: "integer",
                                format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                render: ui => {
                                    if (!ui.rowData[ui.dataIndx]) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                right: true,
                                float: true,
                                fixed: true,
                            },
                        ],
                        right: true,
                        float: true,
                        fixed: true,
                    },
                    {
                        title: "チケット売",
                        dataIndx: "チケット売", dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        right: true,
                        float: true,
                        fixed: true,
                    },
                    {
                        title: "値引",
                        dataIndx: "値引", dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        right: true,
                        float: true,
                        fixed: true,
                    },
                    {
                        title: "現金売上",
                        dataIndx: "現金売上",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        colModel: [
                            {
                                title: "掛売上",
                                dataIndx: "掛売上",
                                dataType: "integer",
                                format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: false,
                                render: ui => {
                                    // zero to blank
                                    return ui.rowData[ui.dataIndx] || "";
                                },
                                right: true,
                                float: true,
                                fixed: true,
                            },
                        ],
                        right: true,
                        float: true,
                        fixed: true,
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金額", dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        right: true,
                        fixed: true,
                    },
                    {
                        title: "備考",
                        dataIndx: "備考", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                        colModel: [
                            {
                                title: "備考",
                                dataIndx: "備考テキスト",
                                dataType: "string",
                                width: 150, minWidth: 150, maxWidth: 150,
                                right: true,
                                fixed: true,
                            },
                        ],
                        right: true,
                        fixed: true,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01100Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01100Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01100Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI01100Grid1;

                        var changes = _.cloneDeep(grid.createSaveParams());

                        var productUpdateList = [];
                        var patternUpdateList = [];

                        changes.UpdateList.forEach((r, i) => {
                            var d = diff(changes.OldList[i], r);

                            _.forIn(d, (v, k) => {
                                if (k.startsWith("商品_")) {
                                    var product = {};
                                    var cd = k.replace("商品_", "");
                                    product.部署ＣＤ = r.部署ＣＤ;
                                    product.得意先ＣＤ = r.得意先ＣＤ;
                                    product.日付 = vue.searchParams.DeliveryDate;
                                    product.商品ＣＤ = cd;
                                    product.見込数 = !v ? 0 : (v * 1);
                                    product.見込入力 = 1;
                                    product.更新フラグ = 0;
                                    product.修正日 = r["予測データ更新日時_" + cd];

                                    productUpdateList.push(product);

                                } else if (k == "製造パターン") {
                                    var pattern = {};
                                    pattern.部署ＣＤ = r.部署ＣＤ;
                                    pattern.製造日 = vue.searchParams.DeliveryDate;
                                    pattern.コースＣＤ = r.コースＣＤ;
                                    pattern.得意先ＣＤ = r.得意先ＣＤ;
                                    pattern.製造パターン = r.製造パターン;
                                    pattern.修正担当者ＣＤ = vue.getLoginInfo().uid;
                                    pattern.修正日 = r.製造パターン更新日時;

                                    patternUpdateList.push(pattern);

                                }
                            });
                        });

                        console.log("01100 save", productUpdateList, patternUpdateList);

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI01100/Save",
                                params: {
                                    ProductList: productUpdateList,
                                    PatternList: patternUpdateList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: true,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        var compare = vue.onAfterSearchFunc(gridVue, grid, res.edited);
                                        var d = diff(vue.DAI01100Grid1.getPlainPData(), compare);

                                        _.forIn(d, (v, k) => {
                                            var r = _.omitBy(v, (vv, kk) => vv == undefined);
                                            if (_.isEmpty(r)) {
                                                delete d[k];
                                            } else {
                                                d[k] = r;
                                            }
                                        })

                                        if (_.isEmpty(d)) {
                                            grid.commit();
                                        } else {
                                            if (res.skipped) {
                                                $.dialogInfo({
                                                    title: "登録チェック",
                                                    contents: "他で変更されたデータがあります。",
                                                });
                                            }

                                            grid.blinkDiff(compare, true);
                                        }

                                        return false;
                                    },
                                },
                            }
                        );
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            //TODO
            // vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.DeliveryDate = moment("20190904").format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //列定義更新
            vue.refreshCols();
        },
        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01100Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                axios.post("/DAI01100/ColSearch", { BushoCd: vue.viewModel.BushoCd })
                    .then(response => {
                        var res = _.cloneDeep(response.data);

                        var newCols = grid.options.colModel.filter(v => !!v.fixed);

                        var productCols = res.map((v, i) => {
                            return {
                                title: v.商品名,
                                custom: true,
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

                        newCols.splice(
                            _.findIndex(newCols, v => v.right),
                            0,
                            ...productCols
                        );

                        //列定義更新
                        grid.options.colModel = newCols;
                        grid.refreshCM();
                        grid.refresh();

                        if (!!grid) grid.hideLoading();

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
                {TargetDate: moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD")}
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
            if (!grid.options.colModel.some(v => v.custom)) return;

            grid.searchData(vue.searchParams, false, null, callback);
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            window.orgData = _.cloneDeep(res);
            var merged = _.values(_.groupBy(res, v => v.得意先ＣＤ + "," + v.売掛現金区分))
                .map((r, i) => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc.得意先ＣＤ = v.得意先ＣＤ;
                                acc.得意先名 = v.得意先名;
                                acc.得意先売掛現金区分 = v.得意先売掛現金区分;
                                acc.得意先売掛現金区分名称 = v.得意先売掛現金区分名称;
                                acc.入金額 = v.入金額;
                                acc.備考 = v.備考;

                                acc.売掛現金区分 = v.売掛現金区分;
                                acc.売掛現金区分名称 = v.売掛現金区分名称;

                                acc["現金個数_" + v.商品ＣＤ] = (acc["現金個数_" + v.商品ＣＤ] || 0) + v.現金個数 * 1;
                                acc["現金金額_" + v.商品ＣＤ] = (acc["現金金額_" + v.商品ＣＤ] || 0) + v.現金金額 * 1;
                                acc["掛売個数_" + v.商品ＣＤ] = (acc["掛売個数_" + v.商品ＣＤ] || 0) + v.掛売個数 * 1;
                                acc["掛売金額_" + v.商品ＣＤ] = (acc["掛売金額_" + v.商品ＣＤ] || 0) + v.掛売金額 * 1;

                                acc["個数_" + v.商品ＣＤ] = (acc["個数_" + v.商品ＣＤ] || 0)
                                    + (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数 ) * 1;
                                acc["金額_" + v.商品ＣＤ] = (acc["金額_" + v.商品ＣＤ] || 0)
                                    + (v.売掛現金区分 == 0 ? v.現金金額 : v.掛売金額 ) * 1;

                                acc.CustomerIndex = acc.CustomerIndex || parseInt(i / 2 + 1);
                                acc.CustomerInfo = acc.CustomerInfo || (v.得意先ＣＤ + "\t" + v.売掛現金区分名称 + "\r\n" + v.得意先名);

                                return acc;
                            },
                            {}
                    );

                    return ret;
                });
            console.log(merged);

            //mergeCellsの設定
            grid.options.mergeCells = _.flattenDeep(res.filter((r, i) => !(i % 2))
                .map((r, i) => {
                    var checkedCol = grid.options.colModel.filter(c => c.dataIndx == "Checked")[0];
                    var checkStateCol = grid.options.colModel.filter(c => c.dataIndx == "CheckState")[0];
                    return [
                        { r1: i * 2, c1: 0, rc: 2, cc: 1 },
                        { r1: i * 2, c1: 1, rc: 2, cc: 1 },
                        // { r1: i * 2, c1: checkedCol.leftPos, rc: 2, cc: 1 },
                        // { r1: i * 2, c1: checkStateCol.leftPos, rc: 2, cc: 1 },
                    ];
                })
            );

            vue.footerButtons.find(v => v.id == "DAI01100Grid1_Save").disabled = !merged.length;

            return merged;
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
    }
}
</script>
