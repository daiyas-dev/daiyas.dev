<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>銀行CD</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.BankCd" @input="onBankCdChanged" @keydown.enter="() => showDetail()">
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">キーワード</label>
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
            id="DAI04190Grid1"
            ref="DAI04190Grid1"
            dataUrl="/Utilities/GetBankListForMaint"
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
    name: "DAI04190",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04190Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 金融機関名称",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BankCd: null,
                KeyWord: null,
                FilterMode: "AND",
                BankList: [],
            },
            DAI04190Grid1: null,
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
                { visible: "true", value: "検索", id: "DAI04190_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04190_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04190Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04190Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04190Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04190Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04190Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBankCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.BankCd = event.target.value;

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
            var grid = vue.DAI04190Grid1;

            if (vue.getLoginInfo().isLogOn) {
                var params = $.extend(true, {}, vue.viewModel);
                grid.searchData(params);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04190Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.BankCd) {
                rules.push({ dataIndx: "銀行CD", condition: "equal", value: vue.viewModel.BankCd });
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
                    //金融機関名称のカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "金融機関名称" }),
                 ]
            ).then(
                axios.spread((responseBankCols) => {
                    var resBankCols = responseBankCols.data;

                    if (resBankCols.onError && !!resBankCols.errors) {
                        //メッセージリストに追加
                        Object.values(resBankCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resBankCols });

                        return;
                    } else if (resBankCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "金融機関マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resBankCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "金融機関マスタの取得に失敗しました<br/>" + resBankCols.message,
                        });

                        return;
                    } else if (resBankCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "金融機関マスタの取得に失敗しました<br/>" + resBankCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resBankCols, v => v.ORDINAL_POSITION * 1)
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

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "金融機関マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "金融機関マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                // v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                //半角カタカナを全角に変換してキーワードに追加
                v.KeyWord += (!!v.銀行名カナ ? ("," +  Moji(v.銀行名カナ).convert('HK', 'ZK').toString()) : "");
                //英数を全角⇔半角に変換してキーワードに追加
                v.KeyWord += (!!v.銀行名 ? ("," +  Moji(v.銀行名).convert('HE', 'ZE').toString()) : "");
                v.KeyWord += (!!v.銀行名 ? ("," +  Moji(v.銀行名).convert('ZE', 'HE').toString()) : "");
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04190Grid1;
            if (!grid) return;

            var params;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                if (grid.pdata.filter(v => v.銀行CD == vue.viewModel.BankCd).length == 1) {
                    params = _.cloneDeep(grid.pdata.find(v => v.銀行CD == vue.viewModel.BankCd));
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    params = _.cloneDeep(rows[0].rowData);
                }
            }

            params.IsNew = false;

            //DAI04191を子画面表示
            PageDialog.show({
                pgId: "DAI04191",
                params: params,
                isModal: true,
                isChild: true,
                width: 500,
                height: 330,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsNew: true}

            //DAI04191を子画面表示
            PageDialog.show({
                pgId: "DAI04191",
                params: params,
                isModal: true,
                isChild: true,
                width: 500,
                height: 330,
            });
        },
    }
}
</script>
