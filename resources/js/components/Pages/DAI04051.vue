<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>得意先CD</label>
            </div>
            <div class="col-md-2">
                <input class="form-control ml-4 mr-1" type="text"
                    id="CustomerCd"
                    v-model=viewModel.CustomerCd
                    :readonly=true
                >
            </div>
            <div class="col-md-6">
                <input class="form-control" type="text" style="width: 300px;"
                    id="CustomerNm"
                    v-model=viewModel.CustomerNm
                    :readonly=true
                >
            </div>
            <div class="col-md-3">
               <VueCheck
                    id="VueCheck_Available"
                    ref="VueCheck_Available"
                    :vmodel=viewModel
                    bind="Available"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: '適用中のみ出力'},
                        {code: '1', name: 'する', label: '適用中のみ出力'},
                    ]"
                    :onChangedFunc=onAvailableChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04051Grid1"
            ref="DAI04051Grid1"
            dataUrl="/Utilities/GetTankaList"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=false
            :options=this.grid1Options
            :onCompleteFunc=onCompleteFunc
            :autoToolTipDisabled=true
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
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
    name: "DAI04051",
    components: {
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                CustomerCd: vue.viewModel.CustomerCd,
                TargetDate: vue.viewModel.TargetDate,
            }
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04051Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "得意先単価マスタメンテ",
            noViewModel: true,
            viewModel: {
                CustomerCd: null,
                CustomerNm: null,
                TargetDate: null,
                Available: "0",
            },
            CustomerKeyWord: null,
            DAI04051Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                toolbar: {
                    cls: "DAI04051_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRow(this, event);
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRow(this, event);
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: true },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
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
                    on: false,
                    header: false,
                    grandSummary: false,
                },
                formulas: [
                    [
                        "sortIndx",
                        function(rowData){
                            return (rowData["商品ＣＤ"] * 1) || 99999;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "商品CD",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 80, maxWidth: 100, minWidth: 80,
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: () => vue.getProductList(),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm", "売価単価": "売価単価", },
                            onSelect: rowData => {
                                console.log("onSelect", rowData);
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                            onSelect: (rowData, selected, ui) => {
                                console.log("4051 onSelect")
                                if (!rowData.InitialValue || rowData.InitialValue.商品ＣＤ != selected.Cd) {
                                    rowData.単価 = selected.売価単価;
                                } else if (!!rowData.InitialValue && rowData.InitialValue.商品ＣＤ == selected.Cd && rowData.InitialValue.単価 != selected.売価単価) {
                                    rowData.単価 = rowData.InitialValue.単価;
                                }
                            },
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 150,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                    },
                    {
                        title: "適用開始日",
                        dataIndx: "適用開始日", dataType: "date", format: "yy/mm/dd", align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        editable: true,
                    },
                    {
                        title: "固定数",
                        dataIndx: "固定数", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                    },
                    {
                        title: "状況",
                        dataIndx: "状況", dataType: "integer", align: "center",
                        width: 90, minWidth: 90, maxWidth: 90,
                        render: ui => {
                            return { text: ui.rowData[ui.dataIndx] == "1" ? "適用中" : "" };
                        },
                    },
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        if(!!vue.params){
            data.viewModel.CustomerCd = vue.params.得意先CD || vue.params.得意先ＣＤ;
            data.viewModel.CustomerNm = vue.params.得意先名;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {

            //商品リスト検索
            axios.post("/DAI04051/GetProductList")
                .then(res => {
                    grid.hideLoading();
                    vue.ProductList = res.data;
                    vue.DAI04051Grid1.searchData(vue.searchParams);
                })
                .catch(err => {
                    grid.hideLoading();
                    console.log("/DAI04051/GetProductList Error", err)
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });

            vue.footerButtons.push(
                { visible: "true", value: "行削除", id: "DAI04051Grid1_DeleteRow", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04051Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveTankaList();
                    }
                },
                {visible: "false"},
            );

        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04051Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04051Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                }
            );

            vue.filterChanged();
        },
        onAvailableChanged: function() {
            var vue = this;

            //フィルタハンドラ
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04051Grid1;

            if (!grid) return;

            var rules = [];
            if (vue.viewModel.Available == "1") {
                rules.push({ dataIndx: "商品ＣＤ", condition: "empty", });
                rules.push({ dataIndx: "状況", condition: "equal", value: "1" });
            }
            grid.filter({ oper: "replace", mode: "OR", rules: rules });
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI04051Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                grid.searchData(vue.searchParams, false);
            }
        },
        getProductList: function() {
            var vue = this;

            return vue.ProductList;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                var data = grid.pdata[0];
                var colIndx = !data["商品ＣＤ"] ? grid.columns["商品ＣＤ"].leftPos
                    : _(grid.columns).pickBy((v, k) => k.endsWith("単価") && !v.hidden).values().value()[0].leftPos;
                grid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "単価": 0,
                "適用開始日": moment().format("YYYY-MM-DD"),
                "固定数": 0,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品ＣＤ"];
        },
        saveTankaList: function() {
            var vue = this;
            var grid = vue.DAI04051Grid1;

            var hasError = !!$(vue.$el).find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var SaveList = _.cloneDeep(grid.createSaveParams());

            //商品ＣＤ未指定は除外。データの整形。
            _.forIn(SaveList,
                (v, k) => {
                    var list = v.filter(r => {
                        return r.商品ＣＤ != null && r.商品ＣＤ != undefined　&& r.商品ＣＤ != "";
                    })
                    .map(r => {
                        r.得意先ＣＤ = vue.viewModel.CustomerCd;
                        r.適用開始日 = moment(r.適用開始日, "YYYY/MM/DD").format("YYYY-MM-DD HH:mm:ss.SSS");
                        r.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        r.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        delete r.部署ＣＤ;
                        delete r.部署名;
                        delete r.得意先名;
                        delete r.商品名;
                        delete r.商品区分;
                        delete r.売価単価;
                        delete r.状況;
                        delete r.sortIndx;
                        return r;
                    })
                    ;
                    SaveList[k] = list;
                }
            );

            if (_.values(SaveList).every(v => !v.length)) {
                //変更無し
                $.dialogInfo({
                    title: "変更無し",
                    contents: "データが変更されていません。",
                });
                return;
            }

            //保存実行
            var params = {SaveList: SaveList, CustomerCd: vue.viewModel.CustomerCd};
            params.noCache = true;

            var Message = {
                "department_code": vue.viewModel.部署ＣＤ,
                "course_code": null,
                "values": {
                    "updateMaster": true,
                },
                "custom_data": {
                    "message": "",
                },
            };
            params.Message = Message;

            //登録中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 登録中…",
            });

            axios.post("/DAI04051/Save", params)
                .then(res => {
                    if (!!vue.params.ParentGrid) {
                        vue.params.ParentGrid.refreshDataAndView();
                    }
                    if (!!vue.params.Parent && vue.params.Parent.$attrs.pgId == "DAI01030") {
                        vue.params.Parent.updateProduct();
                    }

                    progressDlg.dialog("close");
                    $(vue.$el).closest(".ui-dialog-content").dialog("close");
                })
                .catch(err => {
                    progressDlg.dialog("close");
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "登録に失敗しました<br/>",
                    });
                });
        },
        addRow: function(grid, event) {
            var vue = this;
        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI04051Grid1;

            //選択行なし
            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var row = grid.SelectRow().getSelection()[0].rowData;

            //選択行が未保存のデータなら、画面上行削除のみ
            if(!row.InitialValue){
                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                grid.deleteRow({ rowList: rowList });

                return;
            }

            var params = _.cloneDeep(row.InitialValue);
            params.noCache = true;

            var Message = {
                "department_code": vue.viewModel.部署ＣＤ,
                "course_code": null,
                "values": {
                    "updateMaster": true,
                },
                "custom_data": {
                    "message": "",
                },
            };
            params.Message = Message;

            $.dialogConfirm({
                title: "削除確認",
                contents: "選択行を削除します。宜しいですか？",
                buttons:[
                    {
                        text: "はい",
                        class: "btn btn-primary",
                        click: function(){
                            axios.post("/DAI04051/DeleteTankaList", params)
                            .then(res => {
                                grid.refreshDataAndView();
                                if (!!vue.params.ParentGrid) {
                                    vue.params.ParentGrid.refreshDataAndView();
                                }
                                $(this).dialog("close");
                            })
                            .catch(err => {
                                console.log(err);
                            });
                        }
                    },
                    {
                        text: "いいえ",
                        class: "btn btn-danger",
                        click: function(){
                            $(this).dialog("close");
                        }
                    },
                ],
            });

        },
    }
}
</script>

