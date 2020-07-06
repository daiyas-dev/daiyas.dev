<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>商品ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.ProductCd" @input="onProductCdChanged">
            </div>
            <div class="col-md-1">
                <label style="width:90px">部署</label>
            </div>
            <div class="col-md-2">
                <VueSelect v-if="!!params && !!params.IsChild"
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :withCode=true
                    :onChangedFunc=onBushoCdChanged
                />
                <VueSelectBusho v-else
                    :withCode=true
                    :onChangedFunc=onBushoCdChanged
                />
            </div>
            <div class="col-md-1">
                <label style="width:90px">商品種類</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="ProductKind"
                    ref="VueSelect_ProductKind"
                    :vmodel=viewModel
                    bind="ProductKind"
                    :list=ProductKindList
                    :withCode=true
                    :hasNull=true
                    :onChangedFunc=onProductKindChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>キーワード</label>
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" :value="viewModel.KeyWord" @input="onKeyWordChanged">
            </div>
            <div class="col-md-4">
                <VueOptions
                    title="検索条件:"
                    customLabelStyle="text-align: center;"
                    id="FilterMode"
                    :vmodel=viewModel
                    bind="FilterMode"
                    :list="[
                        {code: 'AND', name: 'AND', label: '全て含む'},
                        {code: 'OR', name: 'OR', label: 'いずれかを含む'},
                    ]"
                    :onChangedFunc=onFilterModeChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04180Grid1"
            ref="DAI04180Grid1"
            dataUrl="/DAI04180/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :setMoveNextCell=setMoveNextCell
            :autoToolTipDisabled=true
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
            :onAfterSearchFunc=onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :onAfterAddAutoEmptyRowFunc=onAfterAddAutoEmptyRowFunc
        />
    </form>
</template>

<style scoped>
</style>
<style>
#DAI04180Grid1 svg.pq-grid-overlay {
    display: block;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04180",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04180Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 商品種類マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                ProductCd: null,
                ProductKind: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04180Grid1: null,
            BushoGroupList: [],
            ProductList: [],
            ProductKindList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 30,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                formulas: [
                    [
                        "sortIndx",
                        function(rowData){
                            return (!!rowData.InitialValue ? "1" : "0") + "_" + (rowData["商品種類"] || "0") + "_" + _.padStart(rowData["商品ＣＤ"] || "00000", 5, "0");
                        }
                    ],
                    [
                        "商品種類名",
                        function(rowData){
                            var grid = vue.DAI04180Grid1;
                            var list = grid.getData().filter(v => !!v.商品種類名 && !!v.InitialValue).map(v => { return { 商品種類: v.商品種類, 商品種類名: v.商品種類名 }; });
                            var match = list.find(v => v.商品種類 == rowData["商品種類"]);
                            return !!match ? match.商品種類名 : rowData["商品種類名"];
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署<br>グループ",
                        dataIndx: "部署グループ", dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                        key: true,
                        hidden: true,
                    },
                    {
                        title: "商品種類",
                        dataIndx: "商品種類", dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        key: true,
                        // autocomplete: {
                        //     source: (ui, grid) => vue.getProductKindList(ui, grid),
                        //     bind: "商品種類",
                        //     buddies: { "商品種類名": "CdNm" },
                        //     AutoCompleteFunc: vue.ProductKindAutoCompleteFuncInGrid,
                        //     AutoCompleteMinLength: 0,
                        //     selectSave: true,
                        // },
                    },
                    {
                        title: "商品種類名",
                        dataIndx: "商品種類名", dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                        editable: true,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: (ui, grid) => vue.getProductList(ui, grid),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm" },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 250, maxWidth: 250, minWidth: 250,
                        editable: false,
                    },
                    {
                        title: "KeyWord",
                        dataIndx: "KeyWord", dataType: "string",
                        hidden: true,
                    },
                ],
            },
        });

        if(!!vue.params){
            data.viewModel.BushoCd = vue.params.BushoCd;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "行削除", id: "DAI04180_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRowFunc();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "再表示", id: "DAI04180_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04180_Csv", disabled: false, shortcut: "F8",
                    onClick: function () {
                        vue.DAI04180Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "登録", id: "DAI04180Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04180Grid1.isSelection",
                isSelection => {
                    vue.footerButtons.find(v => v.id == "DAI04180_Delete").disabled = !isSelection;
                }
            );

            var grid = vue.DAI04180Grid1;
            grid.sort(grid.options.sortModel.sorter);

            if (vue.params.IsChild) {
                vue.conditionChanged(true);
            }
        },
        onBushoCdChanged: function(code, entity) {
            var vue = this;
            console.log("4180 onBushoCdChanged", code)

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onProductCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.ProductCd = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onProductKindChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI04180Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn && vue.viewModel.BushoCd) {
                grid.searchData({BushoCd: vue.viewModel.BushoCd});
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04180Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.ProductKind) {
                rules.push(
                    {
                        dataIndx: "商品種類",
                        mode: "OR",
                        crules:  [
                            { condition: "equal", value: vue.viewModel.ProductKind },
                            { condition: "empty" },
                        ],
                    }
                );
            }
            if (!!vue.viewModel.ProductCd) {
                rules.push({ dataIndx: "商品ＣＤ", condition: "begin", value: vue.viewModel.ProductCd });
            }
            if (!!vue.viewModel.KeyWord) {
                var keywords = vue.viewModel.KeyWord.split(/[, 、　]/)
                    .map(v => _.trim(v))
                    .map(k => k.replace(/^[\+＋]/, ""))
                    .filter(v => !!v);

                var rulesKeyWord = keywords.map(k => { return { condition: "contain", value: k }; });

                rules.push({ dataIndx: "KeyWord", mode: vue.viewModel.FilterMode, condition: "equal", crules: rulesKeyWord });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onCompleteFunc: function (grid, ui) {
            grid.sort(grid.options.sortModel.sorter);
        },
        onAfterAddAutoEmptyRowFunc: function (grid, rowList) {
            grid.sort(grid.options.sortModel.sorter);
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.BushoGroupList = res[0].BushoGroupList;
            vue.ProductList = res[0].ProductList;
            vue.ProductKindList = _.uniqBy(
                res[0].ProductKindList
                    .map(v => {
                        return {
                            code: v.商品種類,
                            name: v.商品種類名,
                            label: v.商品種類 + ":" + v.商品種類名,
                        };
                    }),
                    "code"
                );

            var list = res[0].ProductKindList
                .map(v => {
                    v.InitialValue = _.cloneDeep(v);
                    v.KeyWord = _.keys(v).filter(k => (k != "修正日" ) && (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                    return v;
                });

            return list;
        },
        setMoveNextCell: function(grid, ui, reverse) {
            if (grid.getEditCell().$editor) {
                grid.saveEditCell();
            }

            if (ui.dataIndx == "商品種類") {
                grid.setSelection({
                    rowIndx: ui.rowIndx,
                    colIndx: grid.columns["商品ＣＤ"].leftPos,
                });
            } else if (ui.dataIndx == "商品種類名") {
                grid.setSelection({
                    rowIndx: ui.rowIndx,
                    colIndx: grid.columns["商品ＣＤ"].leftPos,
                });
            } else if (ui.dataIndx == "商品ＣＤ") {
                grid.setSelection({
                    rowIndx: ui.rowIndx + 1,
                    colIndx: grid.columns["商品種類"].leftPos,
                });
            } else {
                return true;
            }

            return false;
        },
        getProductKindList: function(ui, grid) {
            var vue = this;
            var list = _.uniqBy(grid.pdata.filter(v => !!v.商品種類).map(v => { return { Cd: v.商品種類, CdNm: v.商品種類名 }; }), "Cd");
            return list;
        },
        ProductKindAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v));
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
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
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getProductList: function(ui, grid) {
            var vue = this;
            var excepts = _.uniq(grid.pdata.filter(v => !!v.商品ＣＤ).filter(v => v.商品種類 == ui.rowData.商品種類).map(v => v.商品ＣＤ));
            excepts = [];
            var list = vue.ProductList.filter(v => !excepts.includes(v.商品ＣＤ) || v.商品ＣＤ == ui.rowData.商品ＣＤ);
            console.log(list);
            return list;
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v));
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
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
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "[商品区分:" + v.商品区分 + "]" + " [売価単価:" + v.売価単価 + "]";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "sortIndx": 0,
                "部署グループ": null,
                "商品種類": null,
                "商品種類名": null,
                "商品ＣＤ": null,
                "商品名": null,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品種類"] || !rowData["商品種類名"] || !rowData["商品ＣＤ"];
        },
        addRowFunc: function() {
            var grid = DAI04180Grid1;
            var rowIndx = grid.SelectRow().getSelection().length == 0
                ? 0
                : (grid.SelectRow().getFirst() + 1);

            grid.addRow({
                rowIndx: rowIndx,
                newRow: {},
                checkEditable: false
            });

            grid.scrollRow({rowIndxPage: rowIndx});

        },
        deleteRowFunc: function() {
            var vue = this;
            var grid = vue.DAI04180Grid1;

            if(!grid.Selection()._areas.length){
                return;
            }

            var rowIndx = grid.Selection()._areas[0].r1;
            grid.deleteRow({ rowIndx: rowIndx });
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI04180Grid1;

            var hasError = !!$(vue.$el).find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var date = moment().format("YYYY-MM-DD HH:mm:ss")
            var SaveList = _.cloneDeep(grid.createSaveParams());
            _.forIn(SaveList, (l, k) => {
                l.map(v => {
                    v.部署グループ = vue.BushoGroupList.find(b => b.部署ＣＤ == vue.viewModel.BushoCd).部署グループ;
                    v.修正担当者ＣＤ = vue.getLoginInfo().uid;
                    v.修正日 = date;
                    delete v.sortIndx;
                    delete v.部署CD;
                    delete v.部署名;
                    delete v.商品名;
                    delete v.KeyWord;
                    return v;
                })
            });
            SaveList.AddList = SaveList.AddList.filter(v => grid.pdata.find(r => r.商品種類 == v.商品種類 && r.商品ＣＤ == v.商品ＣＤ));

            //保存実行
            var params = {SaveList: SaveList};
            params.noCache = true;

            grid.saveData(
                {
                    uri: "/DAI04180/Save",
                    params: {
                        SaveList: SaveList,
                    },
                    optional: this.viewmodel,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            grid.commit();
                            grid.refreshDataAndView();

                            if (!!vue.params.Parent) {
                                vue.params.Parent.updateProduct();
                            }

                            return false;
                        },
                    },
                }
            );
        },
    }
}
</script>
