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
#DAI01101Grid1 svg.pq-grid-overlay {
    display: block;
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
            ParentDataList: [],
            ParentData: {},
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "all",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 40,
                rowHt: 30,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    onSave: null,
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
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
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
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01101Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI01101Grid1;
                        var UpdateList = grid.getChanges().updateList;
                        var OldList = grid.getChanges().oldList;

                        var params = _.cloneDeep(vue.searchParams);
                        params.EditUser = vue.getLoginInfo().uid;
                        params.noCache = true;

                        var UriageList = _.reduce(
                            OldList,
                            (acc, v, i) => {
                                var cols = _.keys(v).filter(c => !!c.startsWith("個数_"));
                                var upd = UpdateList[i];

                                cols.forEach(c => {
                                    var cd = c.replace("個数_", "");
                                    var tanka = vue.ProductList.find(p => p.商品ＣＤ == cd).売価単価 || 0;
                                    var parent = vue.ParentDataList.find(p => p.商品ＣＤ == cd);
                                    var product = vue.ProductList.find(p => p.商品ＣＤ == cd);

                                    var data  = {};
                                    data.日付 = moment(vue.params.TargetDate, "YYYY年MM月DD日").format("YYYY-MM-DD");
                                    data.部署ＣＤ = vue.params.BushoCd;
                                    data.コースＣＤ = vue.params.CourseCd || 0;
                                    data.得意先ＣＤ = upd.得意先ＣＤ;
                                    data.行Ｎｏ = upd["行Ｎｏ_" + cd];
                                    data.明細行Ｎｏ = upd["明細行Ｎｏ_" + cd];
                                    data.受注Ｎｏ = upd["受注Ｎｏ_" + cd] || 0;
                                    data.配送担当者ＣＤ = 0;
                                    data.商品ＣＤ = cd;
                                    data.商品区分 = product.商品区分;
                                    data.現金個数 = (upd.得意先売掛現金区分 == 0 ? upd[c] : 0) || 0;
                                    data.現金金額 = data.現金個数 * tanka;
                                    data.現金値引 = 0;
                                    data.現金値引事由ＣＤ = 0;
                                    data.掛売個数 = (upd.得意先売掛現金区分 == 1 ? upd[c] : 0) || 0;
                                    data.掛売金額 = data.掛売個数 * tanka;
                                    data.掛売値引 = 0;
                                    data.掛売値引事由ＣＤ = 0;
                                    data.請求日付 = !!data.請求日付 ? moment(data.請求日付).format("YYYYMMDD") : "";
                                    data.予備金額１ = tanka;
                                    data.予備金額２ = 0;
                                    data.売掛現金区分 = upd.得意先売掛現金区分;
                                    data.予備ＣＤ２ = 0;
                                    data.主食ＣＤ = upd["主食_" + cd]
                                        || (!!parent ? parent.主食ＣＤ : !!product ? product.主食ＣＤ : null);
                                    data.副食ＣＤ = upd["副食_" + cd]
                                        || (!!parent ? parent.副食ＣＤ : !!product ? product.副食ＣＤ : null);
                                    data.食事区分 = upd["食事区分_" + cd]
                                        || (!!parent ? parent.食事区分 : !!product ? product.食事区分 : null);
                                    data.分配元数量 = 0;
                                    data.備考 = upd.備考;
                                    data.修正日 = upd["修正日_" + cd];
                                    data.修正担当者ＣＤ = vue.getLoginInfo().uid;

                                    acc.push(data);
                                });

                                return acc;
                            },
                            []
                        );

                        var NyukinList = OldList
                            .map((v, i) => _.has(v, "入金額") ? _.cloneDeep(UpdateList[i]) : null)
                            .filter(v => !!v)
                            .map(v => {
                                var data  = {};
                                data.入金日付 = v.入金日付;
                                data.伝票Ｎｏ = v.伝票Ｎｏ;
                                data.部署ＣＤ = vue.params.BushoCd;
                                data.得意先ＣＤ = v.得意先ＣＤ;
                                data.入金区分 = 1;
                                data.現金 = v.入金額;
                                data.小切手 = 0;
                                data.振込 = 0;
                                data.バークレー = 0;
                                data.その他 = 0;
                                data.相殺 = 0;
                                data.値引 = 0;
                                data.摘要 = v.摘要;
                                data.備考 = v.備考;
                                data.請求日付 = v.請求日付;
                                data.予備金額１ = 0;
                                data.予備金額２ = 0;
                                data.予備ＣＤ１ = 0;
                                data.予備ＣＤ２ = 0;
                                data.修正日 = v.入金データ修正日;
                                data.修正担当者ＣＤ = vue.getLoginInfo().uid;

                                return data;
                            });



                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI01101/Save",
                                params: {
                                    UriageList: UriageList,
                                    NyukinList: NyukinList,
                                },
                                optional: params,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        console.log("res", res);

                                        if (!!res.edited && !!res.edited.length) {
                                            $.dialogInfo({
                                                title: "登録チェック",
                                                contents: "他で変更されたデータがあるか、コースが変更された得意先です。",
                                            });
                                            //grid.blinkDiff(res.edited);
                                        } else {
                                            grid.commit();

                                            var refresh = params => {
                                                var parent = params.Parent;

                                                if (!parent) return;

                                                parent.conditionChanged(true);

                                                if (!!parent.params) {
                                                    refresh(parent.params);
                                                }
                                            };

                                            if (!!vue.params) {
                                                refresh(vue.params);
                                            }

                                            $(vue.$el).closest(".ui-dialog-content").dialog("close");
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
                "$refs.DAI01101Grid1.isSelection",
                isSelection => {
                    vue.footerButtons.find(v => v.id == "DAI01101Grid1_Detail").disabled = !isSelection;
                }
            );
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //事前情報取得
            axios.all(
                [
                    //商品リストの取得
                    axios.post("/DAI01101/GetProductList", { CustomerCd: vue.params.CustomerCd }),
                    //分配元データの取得
                    axios.post("/DAI01101/GetParentData", vue.searchParams),
                ]
            ).then(
                axios.spread((responseProduct, responseParent) => {
                    vue.ProductList = responseProduct.data;
                    vue.ParentDataList = responseParent.data;
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
                                acc["修正日_" + v.商品ＣＤ] =
                                    (!acc["修正日_" + v.商品ＣＤ] || acc["修正日_" + v.商品ＣＤ] < v.修正日) ? v.修正日 : acc["修正日_" + v.商品ＣＤ];

                                acc["行Ｎｏ_" + v.商品ＣＤ] = v.行Ｎｏ;
                                acc["明細行Ｎｏ_" + v.商品ＣＤ] = v.明細行Ｎｏ;
                                acc["受注Ｎｏ_" + v.商品ＣＤ] = v.受注Ｎｏ;

                                acc["主食_" + v.商品ＣＤ] = v.主食ＣＤ;
                                acc["副食_" + v.商品ＣＤ] = v.副食ＣＤ;
                                acc["食事区分_" + v.商品ＣＤ] = v.食事区分;
                            } else {
                                acc["個数_その他"] = (acc["個数_その他"] || 0)　+ v.現金個数 * 1 + v.掛売個数 * 1 + v.分配元数量 * 1;
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
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI01101Grid1;

            var params = _.cloneDeep(vue.params);

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.Selection().getSelection()
                if (selection.length != 1) return;

                data = _.cloneDeep(selection[0].rowData);
            }

            params.IsBunpai = true;
            params.ParentCustomerCd = vue.params.CustomerCd;
            params.CustomerCd = data.得意先ＣＤ;
            params.Parent = vue;
            params.ParentGrid = grid;

            delete params.CustomerIndex;

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
