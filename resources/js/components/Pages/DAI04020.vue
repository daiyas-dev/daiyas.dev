<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>担当者ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.担当者ＣＤ" @input="onTantoCdChanged" @keydown.enter="() => showDetail()">
            </div>
            <div class="col-md-1">
                <label style="width:90px">部署</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="BushoCd"
                    :vmodel=viewModel
                    bind="所属部署ＣＤ"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onBushoCdChanged
                />
            </div>
            <div class="col-md-1">
                <label style="width:90px">営業業務区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="SalesDiv"
                    :vmodel=viewModel
                    bind="SalesDiv"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 46 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onSalesDivChanged
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
            id="DAI04020Grid1"
            ref="DAI04020Grid1"
            dataUrl="/Utilities/GetTantoListForMaint"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
            :maxRowSelectCount=1
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
    name: "DAI04020",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04020Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 担当者マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TantoCd: null,
                SalesDiv: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04020Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 30,
                freezeCols: 2,
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
                { visible: "true", value: "検索", id: "DAI04020_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04020_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04020Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "詳細", id: "DAI04020Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04020Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04020Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04020Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBushoCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onSalesDivChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onTantoCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.担当者ＣＤ = event.target.value;

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
            var grid = vue.DAI04020Grid1;

            console.log("DAI04020 conditionChanged", vue.getLoginInfo().isLogOn);

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
            var grid = vue.DAI04020Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.担当者ＣＤ) {
                rules.push({ dataIndx: "担当者ＣＤ", condition: "equal", value: vue.viewModel.担当者ＣＤ });
            }
            if (!!vue.viewModel.所属部署ＣＤ) {
                rules.push({ dataIndx: "所属部署ＣＤ", condition: "equal", value: vue.viewModel.所属部署ＣＤ });
            }
            if (!!vue.viewModel.SalesDiv) {
                rules.push({ dataIndx: "営業業務区分", condition: "equal", value: vue.viewModel.SalesDiv });
            }
            if (!!vue.viewModel.KeyWord) {
                //平仮名をカタカナに変換後、半角に変換する
                var HankakuKeywords = Moji(vue.viewModel.KeyWord.toString()).convert('HG', 'KK').convert('ZK', 'HK').toString();
                var keywords = HankakuKeywords.split(/[, 、　]/)
                    .map(v => _.trim(v))
                    .map(k => k.replace(/^[\+＋]/, ""))
                    .filter(v => !!v);

                var rulesKeyWord = keywords.map(k => { return { condition: "contain", value: k }; });

                rules.push({ dataIndx: "KeyWord", mode: vue.viewModel.FilterMode, condition: "equal", crules: rulesKeyWord });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //PqGrid表示前に必要な情報の取得
            axios.all(
                [
                    //担当者マスタのカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "担当者マスタ" }),
                 ]
            ).then(
                axios.spread((responseTantoCols) => {
                    var resTantoCols = responseTantoCols.data;

                    if (resTantoCols.onError && !!resTantoCols.errors) {
                        //メッセージリストに追加
                        Object.values(resTantoCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resTantoCols });

                        return;
                    } else if (resTantoCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "担当者マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resTantoCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "担当者マスタの取得に失敗しました<br/>" + resTantoCols.message,
                        });

                        return;
                    } else if (resTantoCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "担当者マスタの取得に失敗しました<br/>" + resTantoCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resTantoCols, v => v.ORDINAL_POSITION * 1)
                        // .filter(v => v.COLUMN_NAME != "パスワード")
                        .map(v => {
                            var width = !!v.COLUMN_LENGTH
                                ? (v.COLUMN_LENGTH * (v.DATA_TYPE == "string" && v.COLUMN_LENGTH > 20 ? 5 : 9)) : 100;

                            var titleWidth = Math.ceil((v.COLUMN_NAME.length + 1) / 2) * 15 + 15;
                            if (width < titleWidth) {
                                width = titleWidth;
                            }

                            var model = {
                                title: v.COLUMN_NAME,
                                dataIndx: v.COLUMN_NAME,
                                dataType: v.DATA_TYPE,
                                width: width,
                                minWidth: width,
                                dbLength: v.COLUMN_LENGTH * 1,
                            };

                            if (model.dataType == "date") {
                                model.format = "yy/mm/dd";
                            }

                            return model;
                        });

                    gridOptions.colModel.push(
                        {
                            title: "KeyWord",
                            dataIndx: "KeyWord",
                            dataType: "string",
                            hidden: true,
                        }
                    );

                    gridOptions.colModel
                        .filter(c => ["郵便番号", "住所", "電話番号", "ＦＡＸ"].includes(c.dataIndx))
                        .forEach(c => c.hidden = true);

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "担当者マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "担当者マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                //v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "修正日" ) && (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                //半角カタカナを全角に変換してキーワードに追加
                v.KeyWord += (!!v.担当者名カナ ? ("," +  Moji(v.担当者名カナ).convert('HK', 'ZK').toString()) : "");
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04020Grid1;
            if (!grid) return;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                if (grid.pdata.filter(v => v.担当者ＣＤ == vue.viewModel.担当者ＣＤ).length == 1) {
                    params = _.cloneDeep(grid.pdata.find(v => v.担当者ＣＤ == vue.viewModel.担当者ＣＤ));
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    params = _.cloneDeep(rows[0].rowData);
                }
            }

            params.IsNew = false;

            //DAI04021を子画面表示
            PageDialog.show({
                pgId: "DAI04021",
                params: params,
                isModal: true,
                isChild: true,
                width: 840,
                height: 500,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsNew: true}

            //DAI04021を子画面表示
            PageDialog.show({
                pgId: "DAI04021",
                params: params,
                isModal: true,
                isChild: true,
                width: 840,
                height: 500,
            });
        },
    }
}
</script>

