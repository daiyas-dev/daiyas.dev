<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>銀行</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="BankCd"
                    ref="PopupSelect_Bank"
                    :vmodel=viewModel
                    bind="BankCd"
                    dataUrl="/Utilities/GetBankList"
                    :params='{ bankCd: null }'
                    title="銀行一覧"
                    labelCd="銀行CD"
                    labelCdNm="銀行名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=BankAutoCompleteFunc
                    :onChangeFunc=onBankChanged
                    :isPreload=true
                />
            </div>
            <div class="col-md-1">
                <label>支店CD</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control" :value="viewModel.BranchCd" @input="onBranchCdChanged" @keydown.enter="() => showDetail()">
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
            id="DAI04200Grid1"
            ref="DAI04200Grid1"
            dataUrl="/Utilities/GetBankBranchListForMaint"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
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
    name: "DAI04200",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04200Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 金融機関支店名称",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BankCd: null,
                BranchCd: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04200Grid1: null,
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
                { visible: "true", value: "検索", id: "DAI04200_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04200_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04200Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04200Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04200Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04200Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04200Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBankChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBranchCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.BranchCd = event.target.value;

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
            var grid = vue.DAI04200Grid1;

            console.log("DAI04200 conditionChanged", vue.getLoginInfo().isLogOn);

            if(!vue.$refs.PopupSelect_Bank.selectValue) return;
            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {BankCd : vue.$refs.PopupSelect_Bank.selectValue};
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
            var grid = vue.DAI04200Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.BranchCd) {
                rules.push({ dataIndx: "支店CD", condition: "equal", value: vue.viewModel.BranchCd });
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
                    //金融機関支店名称のカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "金融機関支店名称" }),
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
                        vue.$root.$emit("addMessage", "金融機関支店マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resBankCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "金融機関支店マスタの取得に失敗しました<br/>" + resBankCols.message,
                        });

                        return;
                    } else if (resBankCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "金融機関支店マスタの取得に失敗しました<br/>" + resBankCols.message,
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
                vue.$root.$emit("addMessage", "金融機関支店マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "金融機関支店マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                // v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                return v;
            });

            return res;
        },
        BankAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["銀行名", "銀行名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.銀行CD.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.銀行CD + " : " + v.銀行名 + "【" + v.銀行名カナ + "】";
                    ret.value = v.銀行CD;
                    ret.text = v.銀行名;
                    return ret;
                })
                ;

            return list;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04200Grid1;
            if (!grid) return;

            var params;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                if (grid.pdata.filter(v => v.支店CD == vue.viewModel.BranchCd).length == 1) {
                    params = _.cloneDeep(grid.pdata.find(v => v.支店CD == vue.viewModel.BranchCd));
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    params = _.cloneDeep(rows[0].rowData);
                }
            }

            params.IsNew = false;

            //DAI04201を子画面表示
            PageDialog.show({
                pgId: "DAI04201",
                params: params,
                isModal: true,
                isChild: true,
                width: 530,
                height: 330,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsNew: true}

            //DAI04201を子画面表示
            PageDialog.show({
                pgId: "DAI04201",
                params: params,
                isModal: true,
                isChild: true,
                width: 530,
                height: 330,
            });
        },
    }
}
</script>
