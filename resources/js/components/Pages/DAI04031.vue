<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>商品ＣＤ</label>
                <input class="form-control text-right" type="text"
                    :value=viewModel.商品ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">商品詳細</legend>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="width:100">部署グループ</label>
                            <VueSelect
                                id="ProductKbn"
                                :vmodel=viewModel
                                bind="部署グループ"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 18 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="">商品名</label>
                            <input type="text" class="form-control" :value="viewModel.商品名">
                        </div>
                        <div class="col-md-12">
                            <label class="">商品略称</label>
                            <input type="text" class="form-control" style="width:400px" :value="viewModel.商品略称">
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">商品区分</label>
                            <VueSelect
                                id="ProductKbn"
                                :vmodel=viewModel
                                bind="商品区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 14 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="">売価単価</label>
                            <input class="form-control p-2 text-right" style="width: 90px;" type="text" :value=viewModel.売価単価>
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">弁当区分</label>
                            <VueSelect
                                id="BentoKbn"
                                :vmodel=viewModel
                                bind="弁当区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 15 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">副食区分</label>
                            <VueSelect
                                id="SubKbn"
                                :vmodel=viewModel
                                bind="副食ＣＤ"
                                uri="/Utilities/GetMainSubListForSelect"
                                :params="{ bentoKbn: 1 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">主食区分</label>
                            <VueSelect
                                id="MainKbn"
                                :vmodel=viewModel
                                bind="主食ＣＤ"
                                uri="/Utilities/GetMainSubListForSelect"
                                :params="{ bentoKbn: 2 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">表示区分</label>
                            <VueSelect
                                id="DisplayKbn"
                                :vmodel=viewModel
                                bind="表示ＦＬＧ"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 17 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label>部数単位</label>
                            <input class="form-control text-right" style="width:50px" type="text" :value=viewModel.部数単位>
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">食事区分</label>
                            <VueSelect
                                id="MealKbn"
                                :vmodel=viewModel
                                bind="食事区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 38 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </form>
</template>

<style scoped>
span.ModeLabel {
    font-size: 15px;
}
.row {
    margin-bottom: 2px;
}
div[class^="col-md"] > label {
    min-width: 100px;
}
fieldset div[class^="col-md"] label {
    min-width: 90px;
    text-align: left;
    margin-left: 10px;
}
fieldset fieldset label {
    margin-right: -5px;
}
fieldset {
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    padding-right: 5px;
    margin: 0;
}
fieldset * {
    font-size: 14px !important;
}
legend {
    font-size: 15px;
    width: auto;
    padding: 0;
    margin: 1;
    margin-left: 5px;
    border-bottom:none;
}
fieldset .row {
    margin-left: 10px;
    margin-right: 0px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
</style>
<style>
#Page_DAI04031 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04031",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "商品マスタメンテ詳細",
            noViewModel: true,
            DAI04031Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 200,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                ],
                colModel: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04031_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04031_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04031Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                        console.log("登録");
                        return;

                        //var targets = $.extend(true, {}, grid.createSaveParams());
                        var targets = grid.getCellsByClass({cls: "pq-cell-dirty"})
                            .map(v => {
                                return {
                                    "部署CD": v.rowData["部署CD"],
                                    "コースＣＤ": v.rowData["コースＣＤ"],
                                    "商品CD": v.dataIndx,
                                    "個数": v.rowData[v.dataIndx],
                                    "対象日付": v.rowData["対象日付"],
                                };
                            });
                        var conditions = $.extend(true, {}, vue.viewModel);

                        vue.DAI01020Grid1.saveData(
                            {
                                uri: "/DAI01020/Save",
                                params: { targets: targets },
                                //optional: { conditions: conditions },
                                // done: {
                                //     callback: (gridVue, grid, res) => {
                                //         vue.DAI01020Grid1.searchData(params);
                                //     },
                                // },
                            }
                        );
                    }
                },
                                { visible: "true", value: "CSV", id: "DAI04031_Csv", disabled: false, shortcut: "F10",
                    onClick: function () {
                        //TODO: CSV
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
    }
}
</script>
