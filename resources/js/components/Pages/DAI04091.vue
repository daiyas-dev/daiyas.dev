<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :hasNull=false
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-3">
                <VueCheck
                    id="ReferenceExistCourse"
                    ref="VueCheck_ReferenceExistCourse"
                    title="登録済コース参照"
                    :vmodel=viewModel
                    bind="IsReferenceExistCourse"
                    :withCode=false
                    checkedCode="1"
                    :list="[
                        {code: '0', name: '参照しない', label: '参照しない'},
                        {code: '1', name: '参照する', label: '参照する'},
                    ]"
                    customContainerStyle="border-style: none;"
                    customTitleStyle="width: 150px; justify-content: center;"
                    customContentStyle="width: 120px"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseSelect"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ bushoCd: viewModel.BushoCd }"
                    :isPreload=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
                    :popupWidth=600
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=200
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                />
            </div>
            <template v-if="viewModel.IsReferenceExistCourse == 1">
                <div class="col-md-6">
                    <label style="width: 100px;">コース(参照)</label>
                    <PopupSelect
                        id="CourseSelect_Ref"
                        ref="PopupSelect_Course_Ref"
                        :vmodel=reference
                        bind="CourseCd"
                        :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                        dataUrl="/Utilities/GetCourseList"
                        :params="{ bushoCd: viewModel.BushoCd }"
                        :isPreload=true
                        title="コース一覧"
                        labelCd="コースCD"
                        labelCdNm="コース名"
                        :showColumns='[
                            { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                            { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                        ]'
                        :popupWidth=600
                        :popupHeight=600
                        :isShowName=true
                        :isModal=true
                        :editable=true
                        :reuse=true
                        :existsCheck=true
                        :inputWidth=100
                        :nameWidth=200
                        :onAfterChangedFunc=onCourseChanged
                        :isShowAutoComplete=true
                        :AutoCompleteFunc=CourseAutoCompleteFunc
                        :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                    />
                </div>
            </template>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-5">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 265px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
            <template v-if="viewModel.IsReferenceExistCourse == 1">
                <div class="col-md-6">
                    <label style="width: 100px;">担当者(参照)</label>
                    <input class="form-control label-blue" style="width: 100px;" type="text" :value=reference.TantoCd readonly tabindex="-1">
                    <input class="form-control ml-1 label-blue" style="width: 265px;" type="text" :value=reference.TantoNm readonly tabindex="-1">
                </div>
            </template>
        </div>
        <div class="row">
            <div class="col-md-6">
                <PqGridWrapper
                    id="DAI04091Grid1"
                    ref="DAI04091Grid1"
                    dataUrl="/Utilities/GetCustomerListFromCourse"
                    :query="{ bushoCd: viewModel.BushoCd, courseCd: viewModel.CourseCd }"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    :onChangeFunc=onChangeGrid
                    :isFloat=true
                    :resizeFunc=onMainGridResize
                    :isMultiRowSelectable=true
                    classes="mt-2 mb-2"
                />
            </div>
            <template v-if="viewModel.IsReferenceExistCourse == 1">
                <div class="col-md-6">
                    <PqGridWrapper
                        id="DAI04091Grid2"
                        ref="DAI04091Grid2"
                        dataUrl="/Utilities/GetCustomerListFromCourse"
                        :query="{ bushoCd: viewModel.BushoCd, courseCd: reference.CourseCd }"
                        :SearchOnCreate=false
                        :SearchOnActivate=false
                        :options=grid2Options
                        :onAfterSearchFunc=onAfterSearchFunc
                        :onChangeFunc=onChangeGrid
                        :isFloat=true
                        :resizeFunc=onSubGridResize
                        :isMultiRowSelectable=true
                        classes="mt-2 mb-2"
                    />
                </div>
            </template>
        </div>
    </form>
</template>

<style scoped>
</style>
<style>
#Page_DAI04091 .pq-grid .pq-state-select.pq-grid-row .pq-grid-cell{
    background: steelblue;
    color: white;
}
#Page_DAI04091 .pq-grid .pq-state-select.pq-grid-row .pq-grid-number-cell {
    background: black !important;
    color: orange;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04091",
    components: {
    },
    computed: {
        FormattedKojoKbn: function() {
            var vue = this;
            return vue.viewModel.KojoKbn ? moment(vue.viewModel.KojoKbn, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04091Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        grid2Options: function() {
            var vue = this;
            var options = _.cloneDeep(vue.grid1Options);
            // options.editable = false;
            // options.columnTemplate.editable = false;
            // options.dropModel.on = false;
            return options;
        },
    },
    watch: {
        "viewModel.IsReferenceExistCourse": {
            handler: function(newVal) {
                var vue = this;
                var grid = vue.DAI04091Grid1;
                var grid2 = vue.DAI04091Grid2;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > コーステーブルメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
                IsReferenceExistCourse: "0",
            },
            reference: {
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
            },
            DAI04091Grid1: null,
            DAI04091Grid2: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                freezeCols: 2,
                width: 475,
                editable: true,
                columnTemplate: {
                    editable: true,
                    sortable: true,
                },
                dataModel: { recIndx: "得意先ＣＤ" },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                dragModel: {
                    on: true,
                    diHelper: ["pq_label"],
                    cssHelper: {
                        opacity: 0.8,
                        position:"absolute",
                        height: 25,
                        width: "auto",
                        overflow: "hidden",
                        background: "white",
                        border: "1px groove",
                        boxShadow:"4px 4px 2px gray",
                        zIndex: 4001,
                    },
                    dragNodes: (rd, evt) => {
                        var grid = eval("this");
                        var target = grid.SelectRow().getSelection().map(v => v.rowData);
                        target = target.length && target.includes(rd) ? target : [rd];
                        //target = _.cloneDeep(target).map(v => _.omitBy(v, (v, k) => k.startsWith("pq") || k == "InitialValue"));
                        return target;
                    },
                    beforeDrop: function(evt, ui) {
                        var grid = eval("this");

                        if (!evt.ctrlKey) {
                            var nodes = grid.Drag().getUI().nodes;
                            grid.deleteNodes(nodes);
                        }
                    },
                },
                dropModel: {
                    on: true,
                    isDroppable: function(evt, ui) {
                        var grid = eval("this");

                        var dragGrid = $(evt.target).closest(".pq-grid").pqGrid("getInstance").grid;
                        var dropGrid = ui.$cell.closest(".pq-grid").pqGrid("getInstance").grid;

                        if (dragGrid.widget().prop("id") == dropGrid.widget().prop("id")) {
                            return true;
                        } else {
                            var nodes = dragGrid.Drag().getUI().nodes;
                            var ret = _.intersectionBy(dragGrid.getData(), dropGrid.getData(), "得意先ＣＤ").length == 0;
                            return ret;
                        }

                        return true;
                    },
                },
                moveNode: ( event, ui ) => {
                    //console.log("moveNode", ui);
                    vue.resetData(grid);
                },
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ",
                        dataType: "integer",
                        hidden: true,
                        key: true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ",
                        dataType: "integer",
                        hidden: true,
                        key: true,
                    },
                    {
                        title: "ＳＥＱ",
                        dataIndx: "ＳＥＱ",
                        dataType: "integer",
                        hidden: true,
                        key: true,
                    },
                    {
                        title: "pq_label",
                        dataIndx: "pq_label",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "得意先",
                        dataIndx: "Content",
                        dataType: "string",
                        render: ui => {
                            var grid = eval("this");
                            var gridVue = grid.options.vue;
                            var ScreenVue = gridVue.$parent;

                            var $el = $("<div>")
                                .addClass("d-flex")
                                .append($("<div>").text(ui.rowData.得意先ＣＤ).width(60).addClass("text-right"))
                                .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                .append($("<div>").text(ui.rowData.得意先名))
                                ;

                            return $el[0].outerHTML;
                        },
                    }
                ],
                formulas: [
                    [
                        "Content",
                        function(rowData){
                            return _.keys(rowData)
                                .filter(k => !k.startsWith("pq") && k != "Content" && k != "InitialValue")
                                .map(k => rowData[k])
                                .join(",");
                        }
                    ],
                    [
                        "pq_label",
                        function(rowData){
                            var $el = $("<div>")
                                .addClass("d-flex")
                                .append($("<div>").text(rowData.得意先ＣＤ).width(60).addClass("text-right"))
                                .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                .append($("<div>").text(rowData.得意先名))
                                ;

                            return $el[0].outerHTML;
                        }
                    ],
                ],
            },
        });

        if (!!vue.$route && !!vue.$route.query) {
            data.viewModel = $.extend(true, data.viewModel, vue.$route.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04091_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "再検索", id: "DAI04091_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04091_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04091Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04091Grid1_Save", disabled: false, shortcut: "F9",
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
                    vue.footerButtons.find(v => v.id == "DAI04091Grid1_Detail").disabled = !newVal;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBushoChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(forced) {
            var vue = this;
            var grid1 = vue.DAI04091Grid1;
            var grid2 = vue.DAI04091Grid2;

            if (!!grid1 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd;
                var bushoChanged = !grid1.prevPostData || grid1.prevPostData.bushoCd != vue.viewModel.BushoCd;
                var courseChanged = !grid1.prevPostData || grid1.prevPostData.courseCd != vue.viewModel.CourseCd;

                if (required && (forced || bushoChanged ||courseChanged)) {
                    grid1.searchData({ bushoCd: vue.viewModel.BushoCd, courseCd: vue.viewModel.CourseCd });
                }
            }

            if (!!grid2 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.reference.CourseCd;
                var bushoChanged = !grid2.prevPostData || grid2.prevPostData.bushoCd != vue.viewModel.BushoCd;
                var courseChanged = !grid2.prevPostData || grid2.prevPostData.courseCd != vue.reference.CourseCd;

                if (required && (forced || bushoChanged ||courseChanged)) {
                    grid2.searchData({ bushoCd: vue.viewModel.BushoCd, courseCd: vue.reference.CourseCd });
                }
            }
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            console.log("CourseAutoCompleteFunc", comp.id, dataList);

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if (input == comp.selectValue && comp.isUnique) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            console.log("CourseAutoCompleteFunc", comp.id, list);

            return list;
        },
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;

            return !!newVal.bushoCd && vue.$refs.VueSelectBusho.ready;
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            res = res.map(v => {
                return v;
            });

            return res;
        },
        onChangeGrid: function(grid, ui, event) {
            var vue = this;
            console.log("DAI04091 onChangeGrid", ui, event);

            var targetEvents = ["add", "delete", "addNodes", "deleteNodes"];

            if (targetEvents.includes(ui.source)) {
                vue.resetData(grid);
            }
        },
        resetData: function(grid) {
            var vue = this;

            console.log("resetData", grid.widget().prop("id"));

            var rowList = grid.pdata
                .map((v, i) => {
                    var rowData = _.cloneDeep(v);

                    if (v.ＳＥＱ != i + 1) {
                        rowData.ＳＥＱ = i + 1;
                    }
                    if (v.コースＣＤ != grid.prevPostData.courseCd) {
                        rowData.コースＣＤ = grid.prevPostData.courseCd;
                    }

                    var content =  _.keys(rowData)
                        .filter(k => !k.startsWith("pq") && k != "Content" && k != "InitialValue")
                        .map(k => rowData[k])
                        .join(",");

                    if (v.Content != content) {
                        return {
                            rowIndx: i,
                            newRow: { ＳＥＱ: i + 1, Content: content },
                            history: false
                        };
                    } else {
                        return null;
                    }
                })
                .filter(v => !!v);

            grid.updateRow({ rowList: rowList, history: false });
        },
        onMainGridResize: function(grid) {

        },
        onSubGridResize: function(grid) {

        },
    }
}
</script>
