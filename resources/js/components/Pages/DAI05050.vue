<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05050Grid1"
            ref="DAI05050Grid1"
            dataUrl="/DAI05050/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI05050Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05050Grid1 .pq-group-icon {
    display: none !important;
}
#DAI05050Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI05050Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI05050Grid1 .pq-td-div span {
    line-height: inherit;
    text-align: center;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05050",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > コース外得意先問合せ",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
            },
            DAI05050Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 40,
                rowHt: 30,
                freezeCols: 2,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: false,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 230, minWidth: 230, maxWidth: 230,
                    },
                    {
                        title: "得意先名カナ",
                        dataIndx: "得意先名カナ", dataType: "string",
                        width: 180, minWidth: 180, maxWidth: 180,
                    },
                    {
                        title: "郵便番号",
                        dataIndx: "郵便番号", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "住所１",
                        dataIndx: "住所１", dataType: "string",
                        width: 230, minWidth: 230, maxWidth: 230,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号1", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "売現区分",
                        dataIndx: "売掛現金区分", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "締区分",
                        dataIndx: "締区分", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "締日１",
                        dataIndx: "締日１", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05050Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI05050Grid1_Csv", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI05050Grid1.vue.exportData("csv");
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05050Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            var params = $.extend(true, {}, vue.viewModel);
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            return res;
        },
    }
}
</script>
