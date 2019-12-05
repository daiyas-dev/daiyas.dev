<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-2">
                <label>コース区分</label>
                <VueSelect
                    id="CourseKbn"
                    ref="VueSelect_CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onCourseKbnChanged
                />
            </div>
            <div class="col-md-4">
                <label>担当者</label>
                <PopupSelect
                    id="TantoSelect"
                    ref="PopupSelect_Tanto"
                    :vmodel=viewModel
                    bind="TantoCd"
                    buddy="TantoNm"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: null }"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者CD"
                    labelCdNm="担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :onAfterChangedFunc=onTantoCdChanged
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
            <div class="col-md-3">
                <label>工場区分</label>
                <VueOptions
                    id="KojoKbn"
                    ref="VueOptions_KojoKbn"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="KojoKbn"
                    :list="[
                        {code: '', name: '', label: '指定なし'},
                        {code: '1', name: '工場', label: '工場'},
                        {code: '2', name: '他支店', label: '他支店'},
                    ]"
                    :onChangedFunc=onKojoKbnChanged
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
            id="DAI04080Grid1"
            ref="DAI04080Grid1"
            dataUrl="/Utilities/GetCourseList"
            :SearchOnCreate=true
            :SearchOnActivate=false
            :options=grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
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
    name: "DAI04080",
    components: {
    },
    computed: {
        FormattedKojoKbn: function() {
            var vue = this;
            return vue.viewModel.KojoKbn ? moment(vue.viewModel.KojoKbn, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04080Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > コースマスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseKbn: null,
                KojoKbn: null,
                TantoCd: null,
                TantoNm: null,
                KojoKbn: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04080Grid1: null,
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
                    vue.showDetail();
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04080_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "再検索", id: "DAI04080_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04080_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04080Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04080Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "hasSelectionRow",
                (newVal) => {
                    console.log("hasSelectionRow watcher: " + newVal);
                    vue.footerButtons.find(v => v.id == "DAI04080Grid1_Detail").disabled = !newVal;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBushoChanged: function(code, entity) {
            var vue = this;

            // //条件変更ハンドラ
            // vue.conditionChanged();
            //フィルタ変更
            vue.filterChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onKojoKbnChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onTantoCdChanged: function(element, info, comp, isNoMsg, isValid) {
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
            var grid = vue.DAI04080Grid1;

            console.log("DAI04080 conditionChanged", vue.getLoginInfo().isLogOn);

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
            var grid = vue.DAI04080Grid1;

            if (!grid) return;

            console.log("DAI04080 filterChanged");

            var rules = [];

            if (!!vue.viewModel.BushoCd) {
                rules.push({ dataIndx: "部署ＣＤ", condition: "equal", value: vue.viewModel.BushoCd });
            }
            if (!!vue.viewModel.CourseKbn) {
                rules.push({ dataIndx: "コース区分", condition: "equal", value: vue.viewModel.CourseKbn });
            }
            if (!!vue.viewModel.TantoCd) {
                rules.push({ dataIndx: "担当者ＣＤ", condition: "equal", value: vue.viewModel.TantoCd });
            }
            if (!!vue.viewModel.KojoKbn) {
                if (vue.viewModel.KojoKbn == "0") {
                    rules.push({ dataIndx: "工場区分", condition: "range", value: ["0", "-1"] });
                } else {
                    rules.push({ dataIndx: "工場区分", condition: "equal", value: vue.viewModel.KojoKbn });
                }
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
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //PqGrid表示前に必要な情報の取得
            axios.all(
                [
                    //コースマスタのカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "コースマスタ" }),
                 ]
            ).then(
                axios.spread((responseCustomerCols) => {
                    var resCustomerCols = responseCustomerCols.data;

                    if (resCustomerCols.onError && !!resCustomerCols.errors) {
                        //メッセージリストに追加
                        Object.values(resCustomerCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resCustomerCols });

                        return;
                    } else if (resCustomerCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "コースマスタ取得失敗(" + vue.page.ScreenTitle + ":" + resCustomerCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "コースマスタの取得に失敗しました<br/>" + resCustomerCols.message,
                        });

                        return;
                    } else if (resCustomerCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "コースマスタの取得に失敗しました<br/>" + resCustomerCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resCustomerCols, v => v.ORDINAL_POSITION * 1)
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
                                hidden: ["修正日", "修正担当者ＣＤ"].includes(v.COLUMN_NAME),
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

                    //コース区分表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="コース区分").render
                        = ui => vue.$refs.VueSelect_CourseKbn.entities.find(v => v.code==ui.rowData[ui.dataIndx]).label || "";
                    gridOptions.colModel.find(c => c.dataIndx=="コース区分").align = "left";

                    //担当者名表示設定
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.title=="担当者ＣＤ") + 1,
                        0,
                        {
                            title: "担当者名",
                            dataIndx: "担当者名",
                            dataType: "string",
                            width: 200,
                            minWidth: 200,
                        }
                    );

                    //工場区分表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="工場区分").render
                        = ui => (vue.$refs.VueOptions_KojoKbn.entities.find(v => v.code==ui.rowData[ui.dataIndx]) || { name: "" }).name;
                    gridOptions.colModel.find(c => c.dataIndx=="工場区分").align = "left";

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                console.log(error);

                //メッセージ追加
                vue.$root.$emit("addMessage", "コースマスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "コースマスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = _.values(v).join(",");
                // delete v.パスワード;
                return v;
            });

            //フィルタ設定
            vue.filterChanged();

            return res;
        },
        showDetail: function() {
            var vue = this;
            var grid = vue.DAI04080Grid1;
            if (!grid) return;

            var row = grid.getSelectionRowData();
            if (!row) return;

            var params = _.cloneDeep(row);
            params.IsNew = false;

            //TODO: 子画面化
            vue.$router.push({
                path: "/DAI04/DAI04041",
                query: params,
            });
        },
    }
}
</script>
