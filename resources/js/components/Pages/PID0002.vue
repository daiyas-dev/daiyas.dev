<template>
    <form id="this.$options.name">
        <div class="row m-0">
            <div class="col-md-6">
                <PopupSelect
                    id="User"
                    ref="PopupSelect_User"
                    label="ユーザ"
                    :vmodel=viewModel
                    bind="UserInfo"
                    dataUrl="/Utilities/GetUserList"
                    labelCd="ユーザID"
                    labelCdNm="ユーザ名"
                    :isGetName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :showColumns='[{dataIndx: "GroupName", title: "グループ名", width: 200}]'
                />
            </div>
            <div class="col-md-6">
                <DatePickerWrapper
                    id="UpdateDate"
                    ref="DatePicker_Date"
                    label="対象日付"
                    :vmodel=viewModel
                    bind="UpdateDate"
                    :config=UpdateDateConfig
                    :editable=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="PID0002Grid1"
            ref="PID0002Grid1"
            dataUrl="/PID0002/Search"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onRefreshFunc=this.onRefreshGridFunc
            :options=this.grid1Options
            :autoEmptyRow=true
            :autoEmptyRowCount=2
            autoEmptyRowFormula="3n"
        />
    </form>
</template>

<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

import PopupSelect from "@vcs/PopupSelect.vue";

export default {
    mixins: [PageBaseMixin],
    name: "PID0002",
    components: {
        "PopupSelect": PopupSelect,
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "お知らせ更新",
            PID0002Grid1: null,
            UpdateDate: null,
            UpdateDateConfig: {
                // DatePickerWrapperの基本設定はYYYY/MM/DD
                // format: "YYYY/MM",
                // dayViewHeaderFormat: "YYYY",
            },
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 50,
                editable: true,
                filterModel: {
                    on: false,
                    header: false,
                },
                columnTemplate: {
                    editable: true,
                    sortable: true,
                },
                formulas: [
                    [
                        "user_id",
                        (rowData) => !!rowData.name ? rowData.user_id : undefined,
                    ],
                ],
                colModel: [
                    {
                        title: "登録番号",
                        align: "center", dataType: "integer",
                        width: 100, minWidth: 100, maxWidth: 100,
                        dataIndx: "info_no",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "掲載開始日",
                        align: "center", dataType: "date", format: "yy/mm/dd",
                        width: 250, minWidth: 250, maxWidth: 250,
                        dataIndx: "start_date",
                        filter: {
                            crules: [{ condition: "range" }],
                            conditionList: ["range", "between"],
                        },
                    },
                    {
                        title: "登録者名称",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        dataIndx: "name",
                        buddy: "user_id",
                        dataUrl: "/Utilities/GetUserList",
                        selectorTitle: "ユーザ一覧",
                        labelCd: "ユーザID",
                        labelCdNm: "ユーザ名",
                        isGetName: true,
                        isModal: true,
                        reuse: true,
                        existsCheck: true,
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "登録者Id",
                        dataType: "integer",
                        dataIndx: "user_id",
                        hidden: true,
                    },
                    {
                        title: "お知らせ件名",
                        dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                        dataIndx: "info_title",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "お知らせ内容",
                        dataType: "string",
                        width: 1000,
                        cls: "text-breakable",
                        dataIndx: "info_memo",
                        editor: { type: "textarea" },
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: "チェック",
                        type: "checkbox",
                        dataIndx: "check",
                        editable: true,
                        cb: {
                           check: "yes" ,
                           uncheck: "no",
                        },
                        editor: false,
                    },
                    {
                        title: "Availability",
                        dataIndx: "Discontinued",
                        type: 'checkbox', //required property.
                        cb: {
                            check: "YES", //check the checkbox when cell value is "YES".
                            uncheck: "NO" //uncheck when "NO".
                        },
                        editor: false, //cell renderer i.e., checkbox serves as editor, so no separate editor.
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
        setFooterButtons: function(vue) {
            vue.$root.$emit("setFooterButtons",
                [
                    { visible: "true", value: "保存", id: "PID0002Grid1_Save", disabled: true,
                        onClick: function () {
                            var vm = vue.viewModel;
                            var grid = vue.PID0002Grid1;

                            //パラメータの生成
                            var params = grid.createSaveParams();

                            //PqGridのデータ保存メソッドを呼び出す
                            grid.saveData(
                                {
                                    uri: "/PID0002/Save",
                                    params: params,
                                    done: {
                                        callback: (gridVue, grid, res) => {
                                        },
                                    },
                                }
                            );
                        }
                    },
                    { visible: "true", value: "XXX", id: "PID0002Grid1_XXX", disabled: true,
                        onClick: function () {
                        }
                    },
                    {
                        visible: "true", value: "終了", align: "right",
                        class: "btn-danger",
                        onClick: function() {
                            //確認ダイアログ
                            $.dialogConfirm({
                                title: "確認",
                                contents: "終了してよろしいですか？",
                                buttons:[
                                    {
                                        text: "はい",
                                        class: "btn btn-primary",
                                        click: function(){
                                            $(this).dialog("close");
                                            vue.$root.$emit("execLogOff");
                                        }
                                    },
                                    {
                                        text: "いいえ",
                                        class: "btn btn-danger",
                                        click: function(){
                                            $(this).dialog("close");
                                        }
                                    },
                                ],
                            });
                        }
                    },
                ]
            );
        },
        onRefreshGridFunc: function(grid) {
            var vue = this;
            var canSave = grid.isChanged();

            $("footer").find("#PID0002Grid1_Save").prop("disabled", !canSave);
        },
    }
}
</script>
