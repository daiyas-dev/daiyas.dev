<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    bind="BushoCd"
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-1">
                <label>コースCD</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control CourseCd" :value="viewModel.CourseCd" @input="onCourseCdChanged">
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
            id="DAI04090Grid1"
            ref="DAI04090Grid1"
            dataUrl="/Utilities/GetCourseTableForMaint"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :isMultiRowSelectable=true
            :maxRowSelectCount=2
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
    name: "DAI04090",
    components: {
    },
    computed: {
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > コーステーブルメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                CourseCd: null,
                CourseNm: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04090Grid1: null,
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
                { visible: "true", value: "クリア", id: "DAI04090_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04090_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04090_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04090Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04090Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04090Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04090Grid1_Detail").disabled = cnt == 0 || cnt > 2;
                }
            );
        },
        onBushoChanged: function(code, entity) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
        },
        onCourseCdChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.CourseCd = event.target.value;

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
            var grid = vue.DAI04090Grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI04090Grid1;
                    console.log("DAI04090 conditionChanged", "grid is " + grid, vue.getLoginInfo().isLogOn);
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                //再検索
                var params = $.extend(true, {}, vue.viewModel);
                grid.searchData(params, false);
            });
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04090Grid1;

            if (!grid) return;

            var rules = [];

            // if (!!vue.viewModel.BushoCd) {
            //     rules.push({ dataIndx: "部署ＣＤ", condition: "equal", value: vue.viewModel.BushoCd });
            // }
            if (!!vue.viewModel.CourseCd) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
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
                axios.spread((responseCourseTableCols) => {
                    var resCourseTableCols = responseCourseTableCols.data;

                    if (resCourseTableCols.onError && !!resCourseTableCols.errors) {
                        //メッセージリストに追加
                        Object.values(resCourseTableCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resCourseTableCols });

                        return;
                    } else if (resCourseTableCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "コースマスタ取得失敗(" + vue.page.ScreenTitle + ":" + resCourseTablevCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "コースマスタの取得に失敗しました<br/>" + resCourseTableCols.message,
                        });

                        return;
                    } else if (resCourseTableCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "コースマスタの取得に失敗しました<br/>" + resCourseTableCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resCourseTableCols, v => v.ORDINAL_POSITION * 1)
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

                    //部署名表示設定
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.dataIndx=="部署ＣＤ") + 1,
                        0,
                        {
                            title: "部署名",
                            dataIndx: "部署名",
                            dataType: "string",
                            width: 100,
                            minWidth: 100,
                        }
                    );

                    //コース区分名表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="コース区分").hidden = true;
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.dataIndx=="コース区分") + 1,
                        0,
                        {
                            title: "コース区分",
                            dataIndx: "コース区分名",
                            dataType: "string",
                            width: 100,
                            minWidth: 100,
                        }
                    );

                    //担当者名表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="担当者ＣＤ").hidden = true;
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.dataIndx=="担当者ＣＤ") + 1,
                        0,
                        {
                            title: "担当者",
                            dataIndx: "担当者名",
                            dataType: "string",
                            width: 100,
                            minWidth: 100,
                        }
                    );

                    //修正担当者名表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="修正担当者ＣＤ").hidden = true;
                    gridOptions.colModel.splice(
                        gridOptions.colModel.findIndex(c => c.dataIndx=="修正担当者ＣＤ") + 1,
                        0,
                        {
                            title: "修正担当者",
                            dataIndx: "修正担当者名",
                            dataType: "string",
                            width: 100,
                            minWidth: 100,
                        }
                    );

                    //工場区分非表示設定
                    gridOptions.colModel.find(c => c.dataIndx=="工場区分").hidden = true;

                    //KeyWordカラム設定
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
                vue.$root.$emit("addMessage", "コーステーブル検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "コーステーブルの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = _.keys(v).filter(k => k != "修正日").map(k => v[k]).join(",");
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04090Grid1;
            if (!grid) return;

            var params = {};
            if (rowData) {
                params.targets = [_.cloneDeep(rowData)];
            } else {
                var rows = grid.SelectRow().getSelection();
                if (rows.length == 0 || rows.length > 2) return;

                params.targets = _.cloneDeep(rows);
            }

            //TODO: 子画面化
            vue.$router.push({
                path: "/DAI04/DAI04091",
                query: params,
            });
        },
    }
}
</script>

