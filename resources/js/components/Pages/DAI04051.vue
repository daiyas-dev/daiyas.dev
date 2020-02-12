<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>得意先CD</label>
            </div>
            <div class="col-md-11">
                <PopupSelect
                    id="CustomerCd"
                    class="Tanka"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ CustomerCd: null , KeyWord: CustomerKeyWord }"
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=400
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerCdAutoCompleteFunc
                    :onAfterChangedFunc=onCustomerCdChanged
                    :isPreload=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04051Grid1"
            ref="DAI04051Grid1"
            dataUrl="/Utilities/GetTankaListForMaint"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :checkChangedFunc=checkChangedFunc
            :checkChangedCancelFunc=checkChangedCancelFunc
            :options=this.grid1Options
            :onCompleteFunc=onCompleteFunc
            :onSelectChangeFunc=onSelectChangeFunc
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
                KeyWord: null,
                FilterMode: "AND",
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
                rowHtHead: 50,
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
                            buddies: { "商品名": "CdNm", "単価": "売価単価", "商品区分": "商品区分", },
                            onSelect: rowData => {
                                console.log("onSelect", rowData);
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
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
                        title: "修正担当者CD",
                        dataIndx: "修正担当者ＣＤ", dataType: "integer",
                        width: 150, maxWidth: 150, minWidth: 100,
                    },
                    {
                        title: "修正日",
                        dataIndx: "修正日", dataType: "date", format: "YYYY/MM/DD HH:mm:ss",
                        width: 150, maxWidth: 200, minWidth: 150,
                    },
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        //得意先マスタメンテの詳細画面から開いた時
        if(!!vue.params){
            data.viewModel.CustomerCd = vue.params.得意先CD;
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
                // { visible: "true", value: "行追加", id: "DAI04051Grid1_AddRow", disabled: false, shortcut: "F2",
                //     onClick: function () {
                //         vue.addRow();
                //     }
                // },
                { visible: "true", value: "行削除", id: "DAI04051Grid1_DeleteRow", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04051Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(null, true);
                    }
                },
                { visible: "true", value: "登録", id: "DAI04051Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO:登録
                        vue.saveTankaList();
                    }
                }
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

        },
        onCustomerCdChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();

            console.log("onCustomerCdChanged", info, comp, isValid);
            if (!isValid) {
                vue.CustomerKeyWord = comp.selectValue;
            }
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI04051Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            console.log("DAI04051 conditionChanged", vue.getLoginInfo().isLogOn);

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {CustomerCd : vue.viewModel.CustomerCd};
                grid.searchData(params, false);
            }
        },
        getProductList: function() {
            var vue = this;

            return vue.ProductList;
        },
        CustomerCdAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if (input == comp.selectValue && comp.isUnique) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("CustomerCdAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onSelectChangeFunc: function(grid, ui) {
        },
        checkChangedFunc: function(grid) {
            console.log("checkChangedFunc");
            return true;
        },
        checkChangedCancelFunc: function(grid) {
            var vue = this;
            var grid = vue.DAI04051Grid1;
            var postData = grid.options.dataModel.postData;

            console.log("checkChangedCancelFunc");

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

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["得意先ＣＤ"];
        },
        saveTankaList: function() {
            var vue = this;
            var grid = vue.DAI04051Grid1;

            var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            var hasError = checkError(grid);

            //TODO:エラー未確認
            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            //TODO:不要？
            // var checkRequire = grid => grid.pdata.map(r => [r.商品ＣＤ]).every(r => r.every(v => !!v) || r.every(v => !v));

            // var require = checkRequire(grid);

            // if(!require){
            //     $.dialogErr({
            //         title: "未入力項目",
            //         contents: "未入力項目があるため、登録できません。",
            //     });
            //     return;
            // }

            var SaveList = _.cloneDeep(grid.createSaveParams());

            //商品ＣＤ未指定は除外。データの整形。
            _.forIn(SaveList,
                (v, k) => {
                    var list = v.filter(r => {
                        return r.商品ＣＤ != null && r.商品ＣＤ != undefined;
                    })
                    .map(r => {
                        r.得意先ＣＤ = vue.viewModel.CustomerCd;
                        r.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        r.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        delete r.得意先名;
                        delete r.商品名;
                        delete r.商品区分;
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
            var params = {SaveList: SaveList};
            params.noCache = true;
            axios.post("/DAI04051/Save", params)
                .then(res => {
                    console.log("res", res);
                    //TODO:画面を閉じる
                })
                .catch(err => {
                    console.log(error);
                    //TODO: エラー
                }
            );
            console.log("登録", params);

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

            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
            grid.deleteRow({ rowList: rowList });
        },
    }
}
</script>

