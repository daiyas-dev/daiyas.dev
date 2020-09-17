<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>商品ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.ProductCd" @input="onProductCdChanged">
            </div>
            <div class="col-md-4">
                <label style="width: 150px;">既定製造パターン</label>
                <VueSelect
                    id="Pattern"
                    :vmodel=viewModel
                    bind="Pattern"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 35 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onPatternChanged
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
            <div class="col-md-3">
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
            id="DAI04170Grid1"
            ref="DAI04170Grid1"
            dataUrl="/DAI04170/Search"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :maxRowSelectCount=1
            :autoToolTipDisabled=true
        />
    </form>
</template>
<style scoped>
</style>
<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04170",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04170Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 製造品マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                ProductCd: null,
                Pattern: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04170Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 25,
                freezeCols: 4,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                trackModel: { on: false },
                historyModel: { on: false },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                colModel: [
                    {
                        title: "部署グループ",
                        dataIndx: "部署グループ",
                        dataType: "integer",
                        align: "left",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            return ui.rowData.部署グループ + ":" + ui.rowData.部署グループ名;
                        },
                    },
                    {
                        title: "既定製造パターン",
                        dataIndx: "既定製造パターン",
                        dataType: "integer",
                        align: "left",
                        width: 150, minWidth: 150, maxWidth: 150,
                        render: ui => {
                            return ui.rowData.既定製造パターン + ":" + ui.rowData.既定製造パターン名;
                        },
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                    },
                    {
                        title: "商品略称",
                        dataIndx: "商品略称",
                        dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分",
                        dataType: "integer",
                        align: "left",
                        width: 85, minWidth: 85, maxWidth: 85,
                        render: ui => {
                            return ui.rowData.商品区分 + ":" + ui.rowData.商品区分名;
                        },
                    },
                    {
                        title: "売価単価",
                        dataIndx: "売価単価",
                        dataType: "integer",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "弁当区分",
                        dataIndx: "弁当区分",
                        dataType: "integer",
                        align: "left",
                        width: 85, minWidth: 85, maxWidth: 85,
                        render: ui => {
                            return ui.rowData.弁当区分 + ":" + ui.rowData.弁当区分名;
                        },
                    },
                    {
                        title: "副食区分",
                        dataIndx: "副食ＣＤ",
                        dataType: "integer",
                        align: "left",
                        width: 150, minWidth: 150, maxWidth: 150,
                        render: ui => {
                            return ui.rowData.副食ＣＤ + ":" + ui.rowData.副食名;
                        },
                    },
                    {
                        title: "主食区分",
                        dataIndx: "主食ＣＤ",
                        dataType: "integer",
                        align: "left",
                        width: 150, minWidth: 150, maxWidth: 150,
                        render: ui => {
                            return ui.rowData.主食ＣＤ + ":" + ui.rowData.主食名;
                        },
                    },
                    {
                        title: "表示区分",
                        dataIndx: "表示ＦＬＧ",
                        dataType: "integer",
                        align: "left",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            return ui.rowData.表示ＦＬＧ + ":" + ui.rowData.表示区分名;
                        },
                    },
                    {
                        title: "部数単位",
                        dataIndx: "部数単位",
                        dataType: "integer",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "KeyWord",
                        dataIndx: "KeyWord",
                        dataType: "string",
                        hidden: true,
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
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04170_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04170_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04170Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04170Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04170Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04170Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04170Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onProductCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.ProductCd = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onPatternChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI04170Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                grid.searchData();
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04170Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.ProductCd) {
                rules.push({ dataIndx: "商品ＣＤ", condition: "equal", value: vue.viewModel.ProductCd });
            }
            if (!!vue.viewModel.Pattern) {
                rules.push({ dataIndx: "既定製造パターン", condition: "equal", value: vue.viewModel.Pattern });
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
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = v.商品名;
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04170Grid1;
            if (!grid) return;

            var params;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                if (grid.pdata.filter(v => v.商品ＣＤ == vue.viewModel.ProductCd).length == 1) {
                    params = _.cloneDeep(grid.pdata.find(v => v.商品ＣＤ == vue.viewModel.ProductCd));
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    params = _.cloneDeep(rows[0].rowData);
                }
            }

            params.IsNew = false;

            //DAI04171を子画面表示
            PageDialog.show({
                pgId: "DAI04171",
                params: params,
                isModal: true,
                isChild: true,
                width: 550,
                height: 560,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsNew: true}

            //DAI04171を子画面表示
            PageDialog.show({
                pgId: "DAI04171",
                params: params,
                isModal: true,
                isChild: true,
                width: 550,
                height: 560,
            });
        },
    }
}
</script>

