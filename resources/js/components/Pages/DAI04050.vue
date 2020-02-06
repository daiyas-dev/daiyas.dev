<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>得意先CD</label>
            </div>
            <div class="col-md-11">
                <PopupSelect
                    id="CustomerCd"
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
        <div class="row">
            <div class="col-md-1">
                <label>キーワード</label>
            </div>
            <div class="col-md-6">
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
            id="DAI04050Grid1"
            ref="DAI04050Grid1"
            dataUrl="/Utilities/GetTankaListForMaint"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
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
    name: "DAI04050",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04050Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "得意先単価マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                CustomerCd: null,
                CustomerNm: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            CustomerKeyWord: null,
            DAI04050Grid1: null,
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
                    {
                        title: "商品CD",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 80, maxWidth: 100, minWidth: 80,
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
                    },
                    {
                        title: "修正担当者CD",
                        dataIndx: "修正担当者ＣＤ", dataType: "integer",
                        width: 150, maxWidth: 150, minWidth: 100,
                    },
                    {
                        title: "修正日",
                        dataIndx: "修正日", dataType: "date", format: "yyyy/mm/dd HH:mm:ss",
                        width: 150, maxWidth: 200, minWidth: 150,
                    },
                ],
            },
        });

        //TODO:作業途中。得意先詳細から開いた時。CustomerCdをうけとる。入力可能な表にする。
        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "削除", id: "DAI04050_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04050_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "登録", id: "DAI04050Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO:登録
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04050Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04050Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
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
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI04050Grid1;

            console.log("DAI04050 conditionChanged", vue.getLoginInfo().isLogOn);

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {CustomerCd : vue.viewModel.CustomerCd};
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
            var grid = vue.DAI04050Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
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
        // onBeforeCreateFunc: function(gridOptions, callback) {
        //     var vue = this;

        //     //PqGrid表示前に必要な情報の取得
        //     axios.all(
        //         [
        //             //得意先単価マスタのカラム情報
        //             axios.post("/Utilities/GetColumns", { TableName: "得意先単価マスタ" }),
        //          ]
        //     ).then(
        //         axios.spread((responseTankaCols) => {
        //             var resTankaCols = responseTankaCols.data;

        //             if (resTankaCols.onError && !!resTankaCols.errors) {
        //                 //メッセージリストに追加
        //                 Object.values(resTankaCols.errors).filter(v => v)
        //                     .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

        //                 //ダイアログ
        //                 $.dialogErr({ errObj: resTankaCols });

        //                 return;
        //             } else if (resTankaCols.onException) {
        //                 //メッセージ追加
        //                 vue.$root.$emit("addMessage", "得意先単価マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resTankaCols.message + ")");

        //                 //ダイアログ
        //                 $.dialogErr({
        //                     title: "異常終了",
        //                     contents: "得意先単価マスタの取得に失敗しました<br/>" + resTankaCols.message,
        //                 });

        //                 return;
        //             } else if (resTankaCols == "") {
        //                 //完了ダイアログ
        //                 //ダイアログ
        //                 $.dialogErr({
        //                     title: "異常終了",
        //                     contents: "得意先単価マスタの取得に失敗しました<br/>" + resTankaCols.message,
        //                 });

        //                 return;
        //             }

        //             //colModel設定
        //             gridOptions.colModel = _.sortBy(resTankaCols, v => v.ORDINAL_POSITION * 1)
        //                 // .filter(v => v.COLUMN_NAME != "パスワード")
        //                 .map(v => {
        //                     var width = !!v.COLUMN_LENGTH
        //                         ? (v.COLUMN_LENGTH * (v.DATA_TYPE == "string" && v.COLUMN_LENGTH > 20 ? 5 : 9)) : 100;

        //                     var titleWidth = Math.ceil((v.COLUMN_NAME.length + 1) / 2) * 15 + 15;
        //                     if (width < titleWidth) {
        //                         width = titleWidth;
        //                     }

        //                     var model = {
        //                         title: v.COLUMN_NAME,
        //                         dataIndx: v.COLUMN_NAME,
        //                         dataType: v.DATA_TYPE,
        //                         width: width,
        //                         minWidth: width,
        //                         dbLength: v.COLUMN_LENGTH * 1,
        //                     };

        //                     if (model.dataType == "date") {
        //                         model.format = "yy/mm/dd";
        //                     }

        //                     return model;
        //                 });

        //             gridOptions.colModel.push(
        //                 {
        //                     title: "KeyWord",
        //                     dataIndx: "KeyWord",
        //                     dataType: "string",
        //                     hidden: true,
        //                 }
        //             );

        //             //callback実行
        //             callback();
        //         })
        //     )
        //     .catch(error => {
        //         //メッセージ追加
        //         vue.$root.$emit("addMessage", "得意先単価マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

        //         //ダイアログ
        //         $.dialogErr({
        //             title: "異常終了",
        //             contents: "得意先単価マスタの検索に失敗しました<br/>",
        //         });
        //     });
        // },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                //v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "修正日" ) && (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                return v;
            });

            return res;
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
    }
}
</script>

