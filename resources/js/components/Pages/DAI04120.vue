<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-11">
                <VueSelectBusho
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04120Grid1"
            ref="DAI04120Grid1"
            dataUrl="/DAI04120/GetMobileProductList"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=false
            :options=this.grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
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
form[pgid="DAI04120"] .pq-editor-inner .DatePickerWrapper .target-input {
    width: 120px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04120",
    components: {
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
            }
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04120Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "モバイル対象商品マスタメンテ",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
            },
            DAI04120Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                toolbar: {
                    cls: "DAI04120_toolbar",
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
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 50,
                editable: false,
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
                            return (rowData["商品区分"] || "9") + "_" + _.padStart(rowData["商品ＣＤ"] || "99999", 5, "0");
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
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分", dataType: "integer",
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
                            buddies: { "商品名": "CdNm", "商品区分": "商品区分", },
                            onSelect: rowData => {
                                console.log("onSelect", rowData);
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 150,
                    },
                    {
                        title: "主要商品",
                        dataIndx: "主要商品",
                        type: "checkbox",
                        cbId: "主要商品FLG",
                        width: 50, minWidth: 50, maxWidth: 50,
                        align: "center",
                        editable: true,
                        editor: false,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return "";
                            }
                        },
                    },
                    {
                        title: "主要商品",
                        dataIndx: "主要商品FLG",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: false,
                            check: "1",
                            uncheck: "0",
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "特別商品",
                        dataIndx: "特別商品",
                        type: "checkbox",
                        cbId: "期間限定FLG",
                        width: 50, minWidth: 50, maxWidth: 50,
                        align: "center",
                        editable: true,
                        editor: false,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return "";
                            }
                        },
                    },
                    {
                        title: "特別商品",
                        dataIndx: "期間限定FLG",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: false,
                            check: "1",
                            uncheck: "0",
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "販売期間(開始)",
                        dataIndx: "販売期間開始", dataType: "date", format: "yy/mm/dd", align: "center",
                        width: 120, minWidth: 120, maxWidth: 120,
                        editable: true,
                    },
                    {
                        title: "販売期間(終了)",
                        dataIndx: "販売期間終了", dataType: "date", format: "yy/mm/dd", align: "center",
                        width: 120, minWidth: 120, maxWidth: 120,
                        editable: true,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {

            //商品リスト検索
            axios.post("/DAI04120/GetProductList")
                .then(res => {
                    grid.hideLoading();
                    vue.ProductList = res.data;
                    vue.DAI04120Grid1.searchData(vue.searchParams);
                })
                .catch(err => {
                    grid.hideLoading();
                    console.log("/DAI04120/GetProductList Error", err)
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });

            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI04120Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI04120Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04120_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04120Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "行削除", id: "DAI04120Grid1_DeleteRow", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04120Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
            );

        },
        mountedFunc: function(vue) {
            vue.filterChanged();

            //watcher
            vue.$watch(
                "$refs.DAI04120Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04120Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entitiy) {
            var vue = this;

            //フィルタハンドラ
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04120Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            crules.push({ condition: "equal", value: vue.viewModel.BushoCd });
            crules.push({ condition: "empty" });
            rules.push({ dataIndx: "部署ＣＤ", mode: "OR", crules: crules });

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI04120Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams, false);
        },
        getProductList: function() {
            var vue = this;

            return vue.ProductList;
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;
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
                "主要商品FLG": "0",
                "期間限定FLG": "0",
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品ＣＤ"];
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI04120Grid1;

            var hasError = !!$(vue.$el).find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var SaveList = _.cloneDeep(grid.createSaveParams());

            _.forIn(SaveList,
                (v, k) => {
                    var list = v.filter(r => {
                        return r.商品ＣＤ != null && r.商品ＣＤ != undefined　&& r.商品ＣＤ != "";
                    })
                    .map(r => {
                        r.部署ＣＤ = vue.viewModel.BushoCd;

                        r.販売期間開始 = !!r.販売期間開始
                            ? moment(r.販売期間開始, "YYYY/MM/DD").format("YYYY-MM-DD HH:mm:ss.SSS")
                            : null;
                        r.販売期間終了 = !!r.販売期間終了
                            ? moment(r.販売期間終了, "YYYY/MM/DD").format("YYYY-MM-DD HH:mm:ss.SSS")
                            : null;

                        r.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        delete r.商品区分;
                        delete r.商品名;
                        delete r.主要商品;
                        delete r.特別商品;
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
            var params = {SaveList: SaveList, BushoCd: vue.viewModel.BushoCd};
            params.noCache = true;
            var Message = {
                "department_code": vue.viewModel.BushoCd,
                "course_code": null,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };
            params.Message = Message;

            //登録中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 登録中…",
            });

            axios.post("/DAI04120/Save", params)
                .then(res => {
                    progressDlg.dialog("close");
                    grid.refreshDataAndView();
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

            grid = grid || vue.DAI04120Grid1;

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

            $.dialogConfirm({
                title: "削除確認",
                contents: "選択行を削除します。宜しいですか？",
                buttons:[
                    {
                        text: "はい",
                        class: "btn btn-primary",
                        click: function(){
                            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                            grid.deleteRow({ rowList: rowList });
                            $(this).dialog("close");
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
        print: function() {
            var vue = this;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.title {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                }
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border: solid 0px black;
                }
                th, td {
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 17px;
                    text-align: center;
                }
                td {
                    height: 17px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 0px black;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
                div.report-title-area {
                    width:400px;
                    height:35px;
                    text-align: center;
                    display:table-cell;
                    vertical-align: middle;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3><div class="report-title-area">＊＊＊　モバイル対象商品一覧表　＊＊＊
                    <div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width:  6%;">部署</th>
                                <th style="width:  6%;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 15%;">${vue.viewModel.BushoNm}</th>
                                <th style="width: 26%;" class="blank-cell"></th>
                                <th style="width:  6%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width:  6%;">PAGE</th>
                                <th style="width:  6%;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI04120Grid1.generateHtml(
                                `
                                    table.header-table th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                        text-align: center;
                                    }
                                    table.DAI04120Grid1 th,
                                    table.DAI04120Grid1 td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI04120Grid1 tr:last-child td {
                                        border-bottom-width: 1px;
                                    }
                                    table.header-table tr:nth-child(1) th:nth-child(3),
                                    table.header-table tr:nth-child(1) th:last-child,
                                    table.DAI04120Grid1 th:last-child,
                                    table.DAI04120Grid1 td:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI04120Grid1 tr th:nth-child(1) {
                                        width: 7.0%;
                                    }
                                    table.DAI04120Grid1 tr th:nth-child(2) {
                                        width: 22.0%;
                                    }
                                    table.DAI04120Grid1 tr th:nth-child(3) {
                                        width: 9.0%;
                                    }
                                    table.DAI04120Grid1 tr th:nth-child(4) {
                                        width: 9.0%;
                                    }
                                    table.DAI04120Grid1 tr th:nth-child(5) {
                                        width: 15.0%;
                                    }
                                    table.DAI04120Grid1 th:nth-child(6) {
                                        text-align: left;
                                        padding-left: 25px;
                                    }
                                    table.DAI04120Grid1 td:nth-child(6) {
                                        text-align: left;
                                        padding-left: 18px;
                                    }
                                `,
                                headerFunc,
                                46,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>

