<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-12 align-items-start">
                <PqGridWrapper
                    id="DAI04042Grid1"
                    ref="DAI04042Grid1"
                    dataUrl="/Utilities/GetBunpaisakiList"
                    :query="{ CustomerCd: params.得意先CD, BushoCd: params.部署CD }"
                    :SearchOnCreate=true
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    :onChangeFunc=onChangeGrid
                    :resizeFunc=onMainGridResize
                    :isMultiRowSelectable=true
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
fieldset {
    width: 90%;
    display: flex;
    justify-content: center;
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    margin: 0;
    margin-bottom: 5px;
}
.moveLegend {
    width: auto;
    font-size: 15px;
    margin: 0;
}
.moveAction {
    font-weight: bold;
    color: green;
}
.moveAction.copy {
    color: orange;
}
.moveButtons button {
    width: 55px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    margin-left: 8px;
    margin-right: 8px;
}
.moveButtons button span {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<style>
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button {
    width: 45px;
    height: 30px;
    padding: 0px;
    padding-left: 3px;
    padding-right: 3px;
    margin: 0px;
    display: flex;
    justify-content: center;
    align-items: center;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button.ope {
    width: 45px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04042",
    components: {
    },
    computed: {
        FormattedKojoKbn: function() {
            var vue = this;
            return vue.viewModel.KojoKbn ? moment(vue.viewModel.KojoKbn, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "分配得意先入力",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
                MngCd: null,
                Kind: null,
                KindNm: null,
                StartDate: null,
                EndDate: null,
                Memo: null,
            },
            DAI04042Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI04042_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;

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
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        }
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                freezeCols: 2,
                width: "100%",
                height: 450,
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

                        console.log("beforeDrop", evt, ui, evt.ctrlKey);
                        if (!evt.ctrlKey) {
                            var nodes = _.cloneDeep(grid.Drag().getUI().nodes);
                            console.log("beforeDrop deleteNodes", nodes);
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
                            var ret = _.intersectionBy(nodes, dropGrid.getData(), "得意先ＣＤ").length == 0;
                            return ret;
                        }

                        return true;
                    },
                },
                colModel: [
                    {
                        title: "得意先",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                        key: true,
                        psProps: {
                            dataUrl: "/DAI04042/GetCustomerListForSelect",
                            //params: { bushoCd: () => { var val = !!vue.viewModel ? vue.viewModel.BushoCd : null; console.log("psProps params", vue, val); return val; } },
                            params: vue.getCustomerPsParamsInGrid,
                            bind: "得意先ＣＤ",
                            buddies: { "得意先名": "CdNm" },
                            isPreload: true,
                            title: "得意先一覧",
                            labelCd: "得意先ＣＤ",
                            labelCdNm: "得意先名",
                            popupWidth: 600,
                            popupHeight: 600,
                            isShowName: true,
                            isModal: true,
                            reuse: true,
                            existsCheck: true,
                            inputWidth: 90,
                            nameWidth: 195,
                            onAfterChangedFunc: vue.onCustomerChangedInGrid,
                            isShowAutoComplete: true,
                            AutoCompleteFunc: vue.CustomerAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 1,
                            ParamsChangedCheckFunc: vue.CustomerParamsChangedCheckFuncInGrid,
                            getData: (ui, grid) => {
                                console.log("psprops getData", ui.$cell.find(".target-input").val());
                                return ui.$cell.find(".target-input").val();
                            },
                            htmlRender: ui => {
                                var $el = $("<div>")
                                    .addClass("d-flex")
                                    .append($("<div>").text(ui.rowData.得意先ＣＤ).width(60).addClass("text-right"))
                                    .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                    .append($("<div>").text(ui.rowData.得意先名))
                                    ;

                                return $el[0];
                            },
                        },
                    },
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

        var targets;
        if (!!vue.params || !!vue.query) {
            targets = (vue.params || vue.query).targets;
        }

        if (!targets) return data;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "削除", id: "DAI04042_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04042_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveBunpaisaki();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        conditionChanged: function(forced) {
            var vue = this;
            var grid1 = vue.DAI04042Grid1;

            if (!!grid1 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && (!!vue.viewModel.MngCd && !vue.viewModel.MngCd.includes("新規"));
                var bushoChanged = !grid1.prevPostData || grid1.prevPostData.bushoCd != vue.viewModel.BushoCd;
                var courseChanged = !grid1.prevPostData || grid1.prevPostData.courseCd != vue.viewModel.CourseCd;
                var mngCdChanged = !grid1.prevPostData || grid1.prevPostData.mngCd != vue.viewModel.MngCd;

                if (required && (forced || bushoChanged || courseChanged || mngCdChanged)) {
                    grid1.searchData({ bushoCd: vue.viewModel.BushoCd, courseCd: vue.viewModel.CourseCd, mngCd: vue.viewModel.MngCd });
                }
            }
        },
        saveBunpaisaki: function() {
            var vue = this;
            var grid1 = vue.DAI04042Grid1;

            grid.saveData(
                {
                    uri: "/DAI04042/Save",
                    params: { targets: targets },
                }
            );

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
            console.log(grid.widget().prop("id") + " onChangeGrid", ui, event);

            var targetEvents = ["add", "addNodes", "deleteNodes"];

            if (targetEvents.includes(ui.source)) {
                // vue.resetData(grid);
            }
        },
        onMainGridResize: grid => {
        },
        onCustomerChangedInGrid: function(code, entity) {
            var vue = this;
            console.log("onCustomerChangedInGrid", code);
        },
        CustomerAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            console.log("CustomerAutoCompleteFuncInGrid", comp.id, input, dataList);

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            console.log("CustomerAutoCompleteFuncInGrid", comp.id, list);

            return list;
        },
        CustomerParamsChangedCheckFuncInGrid: function(newVal, oldVal, comp) {
            var vue = this;
            console.log(comp.id + " CustomerParamsChangedCheckFuncInGrid", newVal);
            return !!newVal.bushoCd;
        },
        getCustomerPsParamsInGrid: (vue, grid) => {
            return { bushoCd: !!vue.viewModel ? vue.viewModel.BushoCd : null };
        },
    }
}
</script>
