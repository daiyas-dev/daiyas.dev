<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>各種CD</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.各種CD" @input="onCodeChanged">
            </div>
            <div class="col-md-1">
                <label style="width:90px">サブ各種CD1</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.サブ各種CD1" @input="onSubCode1Changed">
            </div>
            <div class="col-md-1">
                <label style="width:90px">サブ各種CD2</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.サブ各種CD2" @input="onSubCode2Changed">
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
            id="DAI04130Grid1"
            ref="DAI04130Grid1"
            dataUrl="/Utilities/GetCodeListForMaint"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
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
    name: "DAI04130",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04130Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 各種テーブルメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04130Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI04130_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRowFunc();
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRowFunc();
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: false },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 30,
                freezeCols: 2,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
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
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "各種CD",
                        dataIndx: "各種CD", dataType: "integer",
                        width: 80, maxWidth: 80, minWidth: 60,
                        editable: true,
                        key: true,
                    },
                    {
                        title: "行NO",
                        dataIndx: "行NO", dataType: "integer",
                        width: 80, maxWidth: 80, minWidth: 60,
                        editable: true,
                        key: true,
                    },
                    {
                        title: "各種名称",
                        dataIndx: "各種名称", dataType: "string",
                        width: 200, maxWidth: 300, minWidth: 100,
                        editable: true,
                    },
                    {
                        title: "各種略称",
                        dataIndx: "各種略称", dataType: "string",
                        width: 150, maxWidth: 250, minWidth: 100,
                        editable: true,
                    },
                    {
                        title: "サブ各種CD1",
                        dataIndx: "サブ各種CD1", dataType: "integer",
                        width: 100, maxWidth: 120, minWidth: 80,
                        editable: true,
                   },
                    {
                        title: "サブ各種CD2",
                        dataIndx: "サブ各種CD2", dataType: "integer",
                        width: 100, maxWidth: 120, minWidth: 80,
                        editable: true,
                    },
                    {
                        title: "修正担当者CD",
                        dataIndx: "修正担当者CD", dataType: "string",
                        width: 150, maxWidth: 120, minWidth: 80,
                    },
                    {
                        title: "修正日",
                        dataIndx: "修正日", dataType: "date", format: "yyyy/MM/dd HH:mm:ss",
                        width: 150, maxWidth: 200, minWidth: 150,
                    },
                    {
                        title: "KeyWord",
                        dataIndx: "KeyWord", dataType: "string",
                        hidden: true,
                    },
                ],
                formulas: [
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "行追加", id: "DAI04130_AddRow", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.addRowFunc();
                    }
                },
                { visible: "true", value: "行削除", id: "DAI04130_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRowFunc();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "再表示", id: "DAI04130_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04130_Csv", disabled: false, shortcut: "F8",
                    onClick: function () {
                        vue.DAI04130Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "登録", id: "DAI04130Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveKakusyuList();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04130Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04130_Delete").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onCodeChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.各種CD = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onSubCode1Changed: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.サブ各種CD1 = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onSubCode2Changed: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.サブ各種CD2 = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI04130Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = $.extend(true, {}, vue.viewModel);
                grid.searchData(params, false);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04130Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.各種CD) {
                rules.push({ dataIndx: "各種CD", condition: "equal", value: vue.viewModel.各種CD });
            }
            if (!!vue.viewModel.サブ各種CD1) {
                rules.push({ dataIndx: "サブ各種CD1", condition: "equal", value: vue.viewModel.サブ各種CD1 });
            }
            if (!!vue.viewModel.サブ各種CD2) {
                rules.push({ dataIndx: "サブ各種CD2", condition: "equal", value: vue.viewModel.サブ各種CD2 });
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
                // v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "修正日" ) && (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                return v;
            });

            return res;
        },
        saveKakusyuList: function() {
            var vue = this;
            var grid = vue.DAI04130Grid1;

            var hasError = !!$(vue.$el).find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var SaveList = _.cloneDeep(grid.createSaveParams());

            //各種CDと行NO未指定は更新対象外。データの整形。
            _.forIn(SaveList,
                (v, k) => {
                    var list = v.filter(r => {
                        return r.各種CD != null && r.各種CD != undefined　&& r.各種CD != ""
                            && r.行NO != null && r.行NO != undefined　&& r.行NO != "";
                    })
                    .map(r => {
                        r.サブ各種CD1 = r.サブ各種CD1 || 0;
                        r.サブ各種CD2 = r.サブ各種CD2 || 0;
                        r.修正担当者CD = vue.getLoginInfo().uid;
                        r.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        delete r.sortIndx;
                        delete r.KeyWord;
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
            var Message = {
                "department_code": null,
                "course_code": null,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };
            params.Message = Message;

            axios.post("/DAI04130/Save", params)
                .then(res => {
                    if(res.data.duplicateList.length > 0 ){
                        var duplicate = [];
                        for( var i in res.data.duplicateList){
                            var k = res.data.duplicateList[i][0];
                            duplicate[i] = {};
                            duplicate[i].各種CD = k.各種CD;
                            duplicate[i].行NO = k.行NO;
                        }
                        duplicate = JSON.stringify(duplicate).replace("},{","},<br/>{").replace(/[\[\]\{\}")]/g,"");
                        $.dialogInfo({
                            title: "登録失敗",
                            contents: "各種CDと行NOが重複しています。<br>" + duplicate,
                        });
                    }

                    //Cache更新
                    axios.post("/Utilities/GetCodeList", {noCache: true, replace: true});

                    //画面を閉じる
                    vue.conditionChanged();
                    $(vue.$el).closest(".ui-dialog-content").dialog("close");

                })
                .catch(err => {
                    console.log(err);
                }
            );

            console.log("登録", params);

        },
        addRowFunc: function() {
            var grid = DAI04130Grid1;
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
            var grid = vue.DAI04130Grid1;

            //選択行なし
            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var row = grid.SelectRow().getSelection()[0].rowData

            //選択行が未保存のデータなら、画面上行削除のみ
            if(!row.InitialValue){
                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                grid.deleteRow({ rowList: rowList });

                return;
            }

            //選択行の初期値から削除対象のキーを取得
            var KakusyuCd = row.InitialValue.各種CD;
            var GyoNo = row.InitialValue.行NO;

            var params = { KakusyuCd: KakusyuCd, GyoNo: GyoNo };
            params.noCache = true;
            var Message = {
                "department_code": null,
                "course_code": null,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };
            params.Message = Message;

            $.dialogConfirm({
                title: "マスタ削除確認",
                contents: "マスタを削除します。",
                buttons:[
                    {
                        text: "はい",
                        class: "btn btn-primary",
                        click: function(){
                            axios.post("/DAI04130/DeleteKakusyuList", params)
                            .then(res => {
                                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                                grid.deleteRow({ rowList: rowList });
                                $(this).dialog("close");

                                //画面を閉じる
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
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
