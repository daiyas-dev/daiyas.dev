<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-3">
                <label>番号範囲</label>
            </div>
            <div class="col-md-6">
                <input class="form-control text-right p-1" type="text"
                    id="StartNo"
                    v-model=viewModel.StartNo
                    :readonly=false
                    maxlength="7"
                    v-int
                >
                ～
                <input class="form-control text-right p-1" type="text"
                    id="EndNo"
                    v-model=viewModel.EndNo
                    :readonly=false
                    maxlength="7"
                    v-int
                    @focusout="searchFreeCd"
                >
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary ml-2" style="width:80px; height: 30px;" @click="selectFreeCd">
                    選択
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 align-items-start">
                <PqGridWrapper
                    id="DAI04043Grid1"
                    ref="DAI04043Grid1"
                    dataUrl="/DAI04041/GetFreeCustomerCdList"
                    :query=this.viewModel
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI04043"] .pq-grid .DAI04043_toolbar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
form[pgid="DAI04043"] .pq-grid .DAI04043_toolbar .toolbar_button {
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
form[pgid="DAI04043"] .pq-grid .DAI04043_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI04043"] .pq-grid .DAI04043_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI04043"] .pq-grid .DAI04043_toolbar .toolbar_button.ope {
    width: 45px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04043",
    components: {
    },
    computed: {
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "空き番号検索",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                StartNo: null,
                EndNo: null,
            },
            DAI04043Grid1: null,
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
                        title: "空き番号",
                        dataIndx: "digits",
                        dataType: "string",
                        key: true,
                        align: "right",
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.selectFreeCd(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI04043_Search", disabled: false, shortcut: "F4",
                    onClick: function () {
                        vue.searchFreeCd();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            res = res.map(v => {
                return v;
            });

            return res;
        },
        searchFreeCd: function() {
            var vue = this;
            var grid = vue.DAI04043Grid1;

            //アルファベットの除去
            var regex = /[^0-9]/;
            vue.viewModel.StartNo = regex.test(vue.viewModel.StartNo) ? "" : vue.viewModel.StartNo;
            vue.viewModel.EndNo = regex.test(vue.viewModel.EndNo) ? "" : vue.viewModel.EndNo;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {StartNo: vue.viewModel.StartNo, EndNo: vue.viewModel.EndNo};
                grid.searchData(params, false);
            }
        },
        selectFreeCd: function(rowData) {
            var vue = this;
            var grid = vue.DAI04043Grid1;
            if(!grid) return;

            //検索結果0件
            if(grid.pdata.length == 0) return;

            var params;
            var rows = grid.SelectRow().getSelection();

            //複数件選択
            if(rows.length > 1) return;

            if(rows.length == 1){
                params = _.cloneDeep(rows[0].rowData.digits);
            }else{
                //未選択 -> 1行目の値を採用
                params = !!rowData.digits ? rowData.digits: grid.pdata[0].digits;
            }

            //元の画面に値をセット
            vue.params.setCustomerCd(params);

            //画面を閉じる
            $(vue.$el).closest(".ui-dialog-content").dialog("close");
        },
    }
}
</script>
