<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>電話番号</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" v-model="viewModel.TelNo" @keyup.enter="conditionChanged(null)">
                <input type="text" class="form-control" style="width:1px;visibility:hidden;">
            </div>
        </div>
        <PqGridWrapper
            id="DAI04210Grid1"
            ref="DAI04210Grid1"
            dataUrl="/DAI04210/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI04210Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI04210Grid1 .pq-group-icon {
    display: none !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04210",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 非顧客電話番号の登録解除",
            noViewModel: true,
            viewModel: {
                TelNo: null,
            },
            DAI04210Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 0,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [],
                colModel: [
                    {
                        title: "電話番号",
                        dataIndx: "電話番号", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 400, minWidth: 400, maxWidth: 400,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI04210Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "削除", id: "DAI04210Grid1_Delete", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.delete();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        activatedFunc: function(vue) {
            //vue.IsChild = !!vue.params;
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI04210Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TelNo) return;

            var params = $.extend(true, {}, vue.viewModel);

            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            //検索結果があれば削除ボタンを押せるようにする
            var button_disabled = !(!!res && res.length>0);
            vue.footerButtons.find(v => v.id == "DAI04210Grid1_Delete").disabled = button_disabled;
            return res;
        },
        delete: function(){
            var vue = this;
            var grid = vue.DAI04210Grid1;
            var selection = grid.SelectRow().getSelection();
            var rows = grid.SelectRow().getSelection();

            if (rows.length != 1) return;

            var data = _.cloneDeep(rows[0].rowData);

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI04210/Delete",
                    params: {
                        TelNo: data.電話番号,
                        noCache: true,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: true,
                        title: "削除確認",
                        message: "[" + data.電話番号 + "]を非顧客電話番号を削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                    },
                }
            );
        },
    }
}
</script>
