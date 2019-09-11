<template>
    <form id="this.$options.name">
        <label class="mb-0">お知らせ</label>
        <PqGridWrapper
            id="PID0001Grid1"
            ref="PID0001Grid1"
            dataUrl="/PID0001/SearchInfo"
            :isFixedHeight=true
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=this.grid1Options
        />
        <label class="mt-1 mb-0">タスク</label>
        <PqGridWrapper
            id="PID0001Grid2"
            ref="PID0001Grid2"
            dataUrl="/PID0001/SearchTask"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=this.grid2Options
        />
        <label class="mt-1">ここまで</label>
    </form>
</template>

<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "PID0001",
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "ダッシュボード",
            noViewModel: false,
            PID0001Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                numberCell: { show: true, title: "No.", resizable: false, },
                height: 250,
                rowHtHead: 35,
                rowHt: 50,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                formulas: [
                    ["StartYMD", function(rowData){ return moment(rowData.StartDate).format("YYYY/MM/DD"); }],
                ],
                filterModel: {
                    on: true,
                    header: true,
                    menuIcon: true,
                },
                colModel: [
                    {
                        title: "掲載開始日",
                        align: "center", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        dataIndx: "StartYMD",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "お知らせ件名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 300,
                        dataIndx: "InfoTitle",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "お知らせ内容",
                        dataType: "string",
                        width: 800, minWidth: 600,
                        dataIndx: "InfoMemo",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                ],
            },
            grid2Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                numberCell: { show: true, title: "No.", resizable: false, },
                rowHtHead: 35,
                rowHt: 33,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                formulas: [
                    ["StartYMD", function(rowData){ return moment(rowData.StartDate).format("YYYY/MM/DD"); }],
                ],
                filterModel: {
                    on: true,
                    header: true,
                    menuIcon: true,
                },
                colModel: [
                    {
                        title: "完了期限",
                        align: "center", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        dataIndx: "DeadLine",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "タスク件名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 300,
                        dataIndx: "TaskTitle",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "タスク概要",
                        dataType: "string",
                        width: 800, minWidth: 600,
                        dataIndx: "TaskSummary",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
        },
        activatedFunc: function(vue) {
        },
    }
}
</script>
