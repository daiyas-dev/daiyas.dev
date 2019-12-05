<template>
    <form id="this.$options.name">
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
            id="DAI04070Grid1"
            ref="DAI04070Grid1"
            dataUrl="/Utilities/GetBushoListForMaint"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onAfterSearchFunc=onAfterSearchFunc
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
    name: "DAI04070",
    components: {
    },
    computed: {
        FormattedShoninDate: function() {
            var vue = this;
            return vue.viewModel.ShoninDate ? moment(vue.viewModel.ShoninDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 部署マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04070Grid1: null,
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
                { visible: "true", value: "クリア", id: "DAI04070_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04070_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04070_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04070Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04070Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04070Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04070Grid1_Detail").disabled = cnt == 0 || cnt > 2;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI04070Grid1;

            console.log("DAI04070 conditionChanged", vue.getLoginInfo().isLogOn);

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
            var grid = vue.DAI04070Grid1;

            if (!grid) return;

            var rules = [];

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
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //PqGrid表示前に必要な情報の取得
            axios.all(
                [
                    //部署マスタのカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "部署マスタ" }),
                 ]
            ).then(
                axios.spread((responseBushoCols) => {
                    var resBushoCols = responseBushoCols.data;

                    if (resBushoCols.onError && !!resBushoCols.errors) {
                        //メッセージリストに追加
                        Object.values(resBushoCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resBushoCols });

                        return;
                    } else if (resBushoCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "部署マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resBushoCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "部署マスタの取得に失敗しました<br/>" + resBushoCols.message,
                        });

                        return;
                    } else if (resBushoCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "部署マスタの取得に失敗しました<br/>" + resBushoCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resBushoCols, v => v.ORDINAL_POSITION * 1)
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

                    //金融機関名表示設定(口座情報１)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="金融機関CD1") + 1,
                        0,
                        {
                            title: "銀行名１",
                            dataIndx: "金融機関名称１",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );
                    //支店名表示設定(口座情報１)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="金融機関支店CD1") + 1,
                        0,
                        {
                            title: "支店名１",
                            dataIndx: "金融機関支店名称１",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );
                    //口座種別表示設定(口座情報１)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="口座種別1") + 1,
                        0,
                        {
                            title: "口座種別１",
                            dataIndx: "口座種別１",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );
                    //金融機関名表示設定(口座情報２)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="金融機関CD2") + 1,
                        0,
                        {
                            title: "銀行名２",
                            dataIndx: "金融機関名称２",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );
                    //支店名表示設定(口座情報１)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="金融機関支店CD2") + 1,
                        0,
                        {
                            title: "支店名２",
                            dataIndx: "金融機関支店名称２",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );
                    //口座種別表示設定(口座情報１)
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="口座種別2") + 1,
                        0,
                        {
                            title: "口座種別２",
                            dataIndx: "口座種別２",
                            dataType: "string",
                            width: 200,
                            minWidth: 100,
                        }
                    );


                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "部署マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "部署マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = _.values(v).join(",");
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04070Grid1;
            if (!grid) return;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                params = _.cloneDeep(rows[0]);
            }

            params.IsNew = false;

            //TODO: 子画面化
            vue.$router.push({
                path: "/DAI04/DAI04071",
                query: params,
            });
        },
    }
}
</script>
