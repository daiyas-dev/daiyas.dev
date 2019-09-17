
<template>
    <form id="this.$options.name" @submit.prevent="sendNotification">
        <div class="row m-1">
            <div class="col-md-6">
                <button class="btn btn-primary">全送信x</button>
            </div>
        </div>
        <PqGridWrapper
            id="PID0201Grid1"
            ref="PID0201Grid1"
            dataUrl="/Utilities/GetUserList"
            :isFixedHeight=true
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=this.grid1Options
        />
    </form>
</template>

<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "PID0201",
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "通知送信",
            noViewModel: true,
            PID0201Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                numberCell: { show: true, title: "No.", resizable: false, },
                height: 500,
                rowHtHead: 35,
                rowHt: 50,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                formulas: [
                ],
                filterModel: {
                    on: true,
                    header: true,
                    menuIcon: true,
                },
                colModel: [
                    {
                        title: "ユーザID",
                        dataType: "string",
                        align: "center",
                        width: 120, minWidth: 120, maxWidth: 120,
                        dataIndx: "id",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "ユーザ名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        dataIndx: "name",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "ログイン状態",
                        dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        dataIndx: "loggedin",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "通知",
                        dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        dataIndx: "push",
                        postRender: (ui) => {
                            var grid = eval("this");
                            var gridVue = grid.options.vue;
                            var ScreenVue = gridVue.$parent;

                            var gridCell = $(ui.cell);

                            var $el = $("<div>")
                                .addClass("w-100")
                                .append(
                                    $("<input type='checkbox'>")
                                        .on("change", (evt) => ui.rowData.push == evt.checked)
                                )
                                .append(
                                    $("<input type='button'>")
                                        .val("送信")
                                        .addClass("w-75 btn-primary")
                                        .on("click", (evt) => ScreenVue.sendNotification(evt, ui.rowData.name))
                                )
                                ;

                            gridCell.append($el);

                            return ui;
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "Excel出力", id: "PID0201_ExportExcel", shortcut: "F8",
                    onClick: function () {
                        var vm = vue.viewModel;
                        var grid = vue.PID0201Grid1;

                        //パラメータの生成
                        var params = { kind: "Excel" };
                        $.downloadFromUrl("PID0201/Export", params, "test.xlsx", response => console.log(response));
                    }
                },
                { visible: "true", value: "PDF表示", id: "PID0201_ShowPDF", shortcut: "F9",
                    onClick: function () {
                        var vm = vue.viewModel;
                        var grid = vue.PID0201Grid1;

                        //パラメータの生成
                        var params = { kind: "Pdf" };
                        var filename = "test_" + moment().format("YYYYMMDDHHmmss") + ".pdf";
                        var options = { isPrintImmediately: true };

                        $.showPdfViewer("PID0201/Export", params, filename, options, response => console.log(response));
                        //$.downloadFromUrl("PID0201/Export", params, filename, response => console.log(response));
                    }
                },
                { visible: "true", value: "PDF印刷", id: "PID0201_PrintPDFF", shortcut: "F10",
                    onClick: function () {
                        var vm = vue.viewModel;
                        var grid = vue.PID0201Grid1;

                        //パラメータの生成
                        var params = { kind: "Pdf" };
                        var filename = "test_" + moment().format("YYYYMMDDHHmmss") + ".pdf";
                        var options = { isPrintImmediately: true };

                        $.printPdf("PID0201/Export", params, filename, options, response => console.log(response));
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        activatedFunc: function(vue) {
        },
        sendNotification: function(evt, targets) {
            console.log("send notification to " + (targets || "all"));
            axios.post("Utilities/Push", { isAll: !targets });

            return false;
        }
    }
}
</script>
