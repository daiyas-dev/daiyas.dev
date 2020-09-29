<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>コースCD</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control CourseCd" :value="viewModel.CourseCd" @input="onCourseCdChanged">
            </div>
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
            </div>
            <div class="col-md-4">
                <VueCheckList
                    title="種別"
                    id="MngKind"
                    :vmodel=viewModel
                    bind="MngKind"
                    :isGetName=true
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: 70px;"
                    :list="[
                        {code: '0', name: '基本', label: '基本'},
                        {code: '1', name: '一時', label: '一時'},
                        {code: 'null', name: '未登録', label: '未登録'},
                    ]"
                    :onChangedFunc=onMngKindChanged
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
            id="DAI04090Grid1"
            ref="DAI04090Grid1"
            dataUrl="/Utilities/GetCourseTableMngForMaint"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :isMultiRowSelectable=true
            :maxRowSelectCount=2
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
            :keepSelect=true
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
                MngKind: [],
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
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名",
                        dataType: "string",
                        width: 100,
                        minWidth: 100,
                    },
                    {
                        title: "コース区分",
                        dataIndx: "コース区分",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "区分",
                        dataIndx: "コース区分名",
                        dataType: "string",
                        align: "center",
                        width: 50,
                        minWidth: 50,
                        maxWidth: 50,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 85,
                        minWidth: 85,
                        maxWidth: 85,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 200,
                        minWidth: 200,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "担当者名",
                        dataIndx: "担当者名",
                        dataType: "string",
                        width: 100,
                        minWidth: 100,
                    },
                    {
                        title: "一時フラグ",
                        dataIndx: "一時フラグ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "種別",
                        dataIndx: "種別",
                        dataType: "string",
                        align: "center",
                        width: 65,
                        minWidth: 65,
                        maxWidth: 65,
                    },
                    {
                        title: "適用開始日",
                        dataIndx: "適用開始日",
                        dataType: "date", format: "yy/mm/dd",
                        align: "center",
                        width: 100,
                        minWidth: 100,
                        maxWidth: 100,
                    },
                    {
                        title: "適用終了日",
                        dataIndx: "適用終了日",
                        dataType: "date", format: "yy/mm/dd",
                        align: "center",
                        width: 100,
                        minWidth: 100,
                        maxWidth: 100,
                    },
                    {
                        title: "備考",
                        dataIndx: "備考",
                        dataType: "string",
                        width: 150,
                        minWidth: 150,
                    },
                    {
                        title: "KeyWord",
                        dataIndx: "KeyWord",
                        dataType: "string",
                        hidden: true,
                    }
                ],
                formulas: [
                ],
                rowDblClick: function (event, ui) {
                    this.SelectRow().add({rowIndx: ui.rowIndx});
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04090_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04090_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04090Grid1.vue.exportData("csv", false, true);
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
                        vue.showDetail(null, true);
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
        onMngKindChanged: function(list) {
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
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI04090Grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI04090Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 500);
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

            if (!!vue.viewModel.CourseCd) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }
            if (!!vue.viewModel.MngKind) {
                rules.push({ dataIndx: "種別", condition: "range", value: vue.viewModel.MngKind });
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
                v.KeyWord = _.keys(v).filter(k => k != "修正日").map(k => v[k]).join(",");
                return v;
            });

            return res;
        },
        showDetail: function(rowData, isNew) {
            var vue = this;
            var grid = vue.DAI04090Grid1;
            if (!grid) return;

            var params = {};
            if (!isNew) {
                if (rowData) {
                    params.targets = [_.pick(_.cloneDeep(rowData), ["部署ＣＤ", "コースＣＤ", "管理ＣＤ", "コース区分"])];
                } else {
                    var rows = grid.SelectRow().getSelection();
                    if (rows.length == 0 || rows.length > 2) return;

                    params.targets = _.cloneDeep(rows).map(v => _.pick(v.rowData, ["部署ＣＤ", "コースＣＤ", "管理ＣＤ", "コース区分"]));
                }
            }
            params.parent = vue;

            //DAI04091を子画面表示
            PageDialog.show({
                pgId: "DAI04091",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 1200,
                height: 750,
                onBeforeClose: (event, ui) => {
                    if ($(window.event.target).attr("shortcut") == "ESC") return true;

                    var dlg = $(event.target);
                    var editting = dlg.find(".pq-grid")
                        .map((i, v) => $(v).pqGrid("getInstance").grid)
                        .get()
                        .some(g => !_.isEmpty(g.getEditCell()));
                    var isEscOnEditor = !!window.event && window.event.key == "Escape"
                        && (
                            $(window.event.target).hasClass("target-input") ||
                            $(window.event.target).hasClass("pq-cell-editor")
                        );

                    var canClose = !editting && !isEscOnEditor;

                    return canClose;
                }
            });
        },
    }
}
</script>

