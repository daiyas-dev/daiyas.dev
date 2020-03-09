<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
        <PqGridWrapper
            id="DAI01101Grid1"
            ref="DAI01101Grid1"
            dataUrl="/DAI01101/Search"
            :query=this.searchParams
            :SearchOnCreate=true
            :SearchOnActivate=false
            :checkChanged=true
            :options=this.grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onCompleteFunc=onCompleteFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onRefreshFunc=onGridRefreshFunc
            :autoToolTipDisabled=true
            classes="ml-0 mr-0 mt-2"
        />
    </form>
</template>

<style scoped>
.row {
    margin-left: 0px;
    margin-right: 0px;
}
.badge {
    font-size: 15px;
}
</style>
<style>
/* 選択セル/行 */
#DAI01101Grid1.pq-grid .pq-state-select.pq-grid-row{
    background: transparent;
}
#DAI01101Grid1.pq-grid .pq-state-select.pq-grid-row .pq-grid-cell{
    background: transparent;
    color: black;
}
#DAI01101Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner):not(.cell-disabled) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
#DAI01101Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner):not(.cell-disabled).ok_value {
    color: blue;
}
#DAI01101Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner):not(.cell-disabled).ng_value {
    color: red;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01101",
    components: {
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").isValid() ? moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null,
                CourseCd: this.viewModel.CourseCd || 0,
                CustomerCd: this.viewModel.CustomerCd,
            };
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "分配入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                TargetDate: null,
                CourseKbn: null,
                CourseCd: null,
                TantoCd: null,
                CustomerCd: null,
            },
            isLocked: null,
            DAI01101Grid1: null,
            CustomerList: [],
            ProductList: [],
            ParentData: {},
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
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
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                ],
                colModel: [
                    {
                        title: "得意先",
                        fixed: true,
                        editable: false,
                        colModel: [
                            {
                                title: "コード",
                                dataIndx: "得意先ＣＤ", dataType: "integer",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: false,
                            },
                            {
                                title: "得意先名",
                                dataIndx: "得意先名", dataType: "string",
                                width: 200, minWidth: 200,
                                editable: false,
                            },
                        ],
                    }
                ],
            },
        });

        if (!!vue.params) {
            data.viewModel = $.extend(true, data.viewModel, vue.params);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI01101Grid1_Detail", disabled: true, shortcut: "F1",
                    onClick: function () {
                        vue.showDetail(false);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01101Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI01101Grid1;

                        var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

                        var hasError = checkError(grid);

                        if(hasError){
                            $.dialogErr({
                                title: "入力値エラー",
                                contents: "エラー項目があるため、登録できません。",
                            });
                            return;
                        }

                        var checkRequire = grid => grid.pdata.map(r => [r.商品ＣＤ, r.個数]).every(r => r.every(v => !!v) || r.every(v => !v));

                        var require = checkRequire(grid);

                        if(!require){
                            $.dialogErr({
                                title: "未入力項目",
                                contents: "未入力項目があるため、登録できません。",
                            });
                            return;
                        }

                        var changes = grid.createSaveParams();
                        changes.AddList = changes.AddList.filter(r => _.values(diff(grid.vue.autoEmptyRowFunc(), r)).some(v => !!v));
                        var SaveList = _.concat(changes.AddList, changes.UpdateList);

                        //注文データの型に整形
                        SaveList.forEach((v, i) => {
                            v.日付 = moment(vue.searchParams.TargetDate).format("YYYY-MM-DD");
                            v.部署ＣＤ = vue.searchParams.BushoCd;
                            v.コースＣＤ = vue.searchParams.CourseCd || 0;
                            v.行Ｎｏ = !!vue.params ? (vue.params.CustomerIndex || 0): 0;
                            v.得意先ＣＤ = vue.searchParams.CustomerCd;
                            v.受注Ｎｏ = _.max(grid.pdata.map(v => v.受注Ｎｏ)) || 0;
                            v.配送担当者ＣＤ = vue.searchParams.TantoCd || 999;
                            v.現金個数 = (v.売上売掛現金区分 == 0 ? v.個数 : v.現金個数) || 0;
                            v.現金金額 = (v.売上売掛現金区分 == 0 ? v.金額 : v.現金金額) || 0;
                            v.現金値引 = (v.売上売掛現金区分 == 0 ? v.値引 : v.現金値引) || 0;
                            v.現金値引事由ＣＤ = (v.売上売掛現金区分 == 0 ? v.値引事由 : v.現金値引事由ＣＤ) || 0;
                            v.掛売個数 = (v.売上売掛現金区分 == 1 ? v.個数 : v.掛売個数) || 0;
                            v.掛売金額 = (v.売上売掛現金区分 == 1 ? v.金額 : v.掛売金額) || 0;
                            v.掛売値引 = (v.売上売掛現金区分 == 1 ? v.値引 : v.掛売値引) || 0;
                            v.掛売値引事由ＣＤ = (v.売上売掛現金区分 == 1 ? v.値引事由 : v.掛売値引事由ＣＤ) || 0;
                            v.請求日付 = !!v.請求日付 ? moment(v.請求日付).format("YYYYMMDD") : "";
                            v.予備金額１ = v.単価;
                            v.予備金額２ = 0;
                            v.売掛現金区分 = v.売上売掛現金区分;
                            v.予備ＣＤ２ = 0;
                            v.修正担当者ＣＤ = vue.getLoginInfo().uid;

                            delete v.コース名;
                            delete v.ＳＥＱ;
                            delete v.得意先名;
                            delete v.売掛現金区分名称;
                            delete v.得意先売掛現金区分;
                            delete v.得意先売掛現金区分名称;
                            delete v.売上売掛現金区分;
                            delete v.売上売掛現金区分名称;
                            delete v.商品名;
                            delete v.単価;
                            delete v.個数;
                            delete v.値引;
                            delete v.値引事由;
                            delete v.金額;
                            delete v.今回請求額;
                        });

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI01101/Save",
                                params: {
                                    SaveList: SaveList,
                                },
                                optional: this.searchParams,
                                confirm: {
                                    isShow: true,
                                    title: "確認 " + vue.viewModel.CustomerNm,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        console.log("res", res);

                                        if (!!res.edited && !!res.edited.length) {
                                            $.dialogInfo({
                                                title: "登録チェック",
                                                contents: "他で変更されたデータがあります。",
                                            });
                                            grid.blinkDiff(res.edited);
                                        } else {
                                            if (!!vue.params) {
                                                grid.commit();
                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            } else {
                                                grid.clearData();
                                                vue.viewModel.CustomerCd = null;
                                                vue.viewModel.CustomerCd = null;
                                                vue.viewModel.CourseCd = null;
                                                vue.viewModel.CourseNm = null;
                                                vue.viewModel.TantoCd = null;
                                                vue.viewModel.PaymentCd = null;
                                                vue.viewModel.PaymentNm = null;
                                            }
                                        }

                                        return false;
                                    },
                                },
                            }
                        );
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI01101Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01101Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //事前情報取得
            axios.all(
                [
                    //対象商品リストの取得
                    axios.post("/Utilities/GetCodeList", { cd: 44 }),
                    //分配元データの取得
                    axios.post("/DAI01101/GetParentData", vue.searchParams),
                ]
            ).then(
                axios.spread((responseProduct, responseParent) => {
                    vue.ProductList = responseProduct.data.map(v => {
                        return { "商品ＣＤ": v.サブ各種CD1, "商品名": v.各種名称 };
                    });
                    vue.ParentData = vue.mergeData(responseParent.data)[0];

                    if(callback) callback();
                })
            )
            .catch(err => {
                console.log(err);
                if (!!grid) grid.hideLoading();

                if (!err.message) return;

                //失敗ダイアログ
                $.dialogErr({
                    title: "各種テーブル検索失敗",
                    contents: "各種テーブル検索に失敗しました" + "<br/>" + err.message,
                });
            });
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;
            // vue.setGridFocus(grid);
        },
        setGridFocus: function(grid, ui) {
            var vue = this;
            grid = grid || vue.DAI01101Grid1;

            if (grid.pdata.length > 0) {
                var data = grid.pdata[0];
                var colIndx = !data["得意先ＣＤ"] ? grid.columns["得意先ＣＤ"].leftPos
                    : _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos;
                grid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            var data = _.cloneDeep(res);
            var childrenList = vue.mergeData(data);

            if (!grid.options.colModel.some(c => !!c.custom)) {
                var newCols = grid.options.colModel.filter(c => !!c.fixed);

                var productCols = vue.ProductList.map((v, i) => {
                    return {
                        title: v.商品名 + "<br>分配元: <span style='font-weight: bold;'>" + (vue.ParentData["個数_" + v.商品ＣＤ] || 0) + "</span>",
                        dataIndx: "個数_" + v.商品ＣＤ,
                        dataType: "integer",
                        format: "#,###",
                        width: 90, maxWidth: 90, minWidth: 90,
                        editable: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                var isOK = ui.cellData * 1 == ui.column.parentVal * 1;
                                ui.cls.push(isOK ? "ok_value" : "ng_value");

                                return { text: (isOK ? "OK" : "NG") + " : " + ui.cellData };
                            } else {
                                if (!ui.rowData[ui.dataIndx]) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                        custom: true,
                        parentVal: vue.ParentData["個数_" + v.商品ＣＤ] || 0,
                    };
                });
                newCols = newCols.concat(productCols);

                newCols.push(
                    {
                        title: "その他" + "<br>分配元: <span style='font-weight: bold;'>" + (vue.ParentData["個数_その他"] || 0) + "</span>",
                        dataIndx: "個数_その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 90, maxWidth: 90, minWidth: 90,
                        editable: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                var isOK = ui.cellData * 1 == ui.column.parentVal * 1;
                                ui.cls.push(isOK ? "ok_value" : "ng_value");

                                return { text: (isOK ? "OK" : "NG") + " : " + ui.cellData };
                            } else {
                                if (!ui.rowData[ui.dataIndx]) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                        custom: true,
                        parentVal: vue.ParentData["個数_その他"] || 0,
                    }
                );

                newCols.push(
                    {
                        title: "入金額",
                        dataIndx: "入金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 90, maxWidth: 90, minWidth: 90,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                        custom: true,
                    }
                );

                grid.options.colModel = newCols;
                grid.refreshCM();
                grid.refresh();
            }

            return childrenList;
        },
        mergeData: function (data) {
            var vue = this;

            var merged = _.values(_.groupBy(data, v => v.得意先ＣＤ))
                .map((r, i) => {
                    var ret = _.reduce(
                        r,
                        (acc, v, j) => {
                            acc.得意先ＣＤ = v.得意先ＣＤ;
                            acc.得意先名 = v.得意先名;
                            acc.得意先売掛現金区分 = v.得意先売掛現金区分;
                            acc.得意先売掛現金区分名称 = v.得意先売掛現金区分名称;

                            acc.入金日付 = v.入金日付 ;
                            acc.伝票Ｎｏ = v.伝票Ｎｏ ;
                            acc.入金額 = v.入金額 ;
                            acc.摘要 = v.摘要,
                            acc.備考 = v.備考,
                            acc.請求日付 = v.請求日付 ;
                            acc.入金データ修正日 = v.入金データ修正日 ;

                            acc.売上売掛現金区分 = v.売上売掛現金区分;
                            acc.売上売掛現金区分名称 = v.売上売掛現金区分名称;

                            acc["現金個数_" + v.商品ＣＤ] = (acc["現金個数_" + v.商品ＣＤ] || 0) + v.現金個数 * 1;
                            acc["掛売個数_" + v.商品ＣＤ] = (acc["掛売個数_" + v.商品ＣＤ] || 0) + v.掛売個数 * 1;
                            acc["分配元数量_" + v.商品ＣＤ] = (acc["分配元数量_" + v.商品ＣＤ] || 0) + v.分配元数量 * 1;

                            if (!!vue.ProductList.find(p => p.商品ＣＤ == v.商品ＣＤ)) {
                                acc["個数_" + v.商品ＣＤ] = (acc["個数_" + v.商品ＣＤ] || 0)
                                    + v.現金個数 * 1 + v.掛売個数 * 1 + acc["分配元数量_" + v.商品ＣＤ];
                            } else {
                                acc["個数_その他"] = (acc["個数_その他"] || 0)
                                    + v.現金個数 * 1 + v.掛売個数 * 1;
                            }

                            return acc;
                        },
                        {}
                    );

                    _.forIn(ret, (v, k, o) =>{
                        if (k.startsWith("個数_") || k == "入金額") {
                            if (v == 0 || v == null) o[k] = undefined;
                        }
                    });

                    var base = _.reduce(vue.ProductList, (acc, v, j) => { acc["個数_" + v.商品ＣＤ] = undefined; return acc; }, {});
                    base.個数_その他 = undefined;
                    base.入金額 = undefined;

                    return _.merge(base, ret);
                });

            return merged;
        },
        onGridRefreshFunc: function(grid) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI01101Grid1_Save").disabled = grid.widget().find(".ng_value").length > 0;
        },
        showDetail: function() {
            var vue = this;

            var params = vue.params;

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
    }
}
</script>
