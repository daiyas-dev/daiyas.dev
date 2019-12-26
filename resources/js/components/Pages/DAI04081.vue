<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>部署</label>
                <VueSelect
                    id="BushoCd"
                    ref="BushoCdSelect"
                    :vmodel=viewModel
                    bind="部署ＣＤ"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=true
                    customStyle="{ width: 100px; }"
                    :disabled=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="width:100">コース区分</label>
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="コース区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>コースＣＤ</label>
                <input class="form-control text-right" type="text"
                    :value=viewModel.コースＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="">コース名</label>
                <input class="form-control p-2" type="text" :value=viewModel.コース名>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">担当者ＣＤ</label>
                <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.担当者ＣＤ>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">担当者名</label>
                <input type="text" class="form-control" :value="viewModel.担当者名">
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>工場区分</label>
                <VueOptions
                    id="KojoKbn"
                    ref="VueOptions_KojoKbn"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="工場区分"
                    :list="[
                        {code: '', name: '', label: '指定なし'},
                        {code: '1', name: '工場', label: '工場'},
                        {code: '2', name: '他支店', label: '他支店'},
                    ]"
                />
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
    margin: 0;
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
#Page_DAI04081 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04081",
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
            ScreenTitle: "コースマスタメンテ詳細",
            noViewModel: true,
            DAI04081Grid1: null,
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
                { visible: "true", value: "クリア", id: "DAI04081_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04081_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04081Grid1_Save", disabled: false, shortcut: "F9",
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
                                { visible: "true", value: "CSV", id: "DAI04081_Csv", disabled: false, shortcut: "F10",
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
