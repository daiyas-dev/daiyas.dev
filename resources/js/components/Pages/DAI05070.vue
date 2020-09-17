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
            <div class="col-md-1">
                <label>処理日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_TargetDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: unset;">振替ファイル</label>
            </div>
            <div class="col-md-6">
                <div
                    class="UploadFile droppable d-flex align-items-center w-100 h-100 pl-2"
                    style="cursor: pointer;"
                    data-empty-text="対象ファイルをドロップ、もしくはここをクリックして選択"
                    data-path-text="xxx"
                    data-url="/DAI05070/UploadFile"
                    data-addedfile-callback="addFileCallback"
                    data-upload-callback="uploadFileCallback"
                >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理日付</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05070Grid1"
            ref="DAI05070Grid1"
            dataUrl="/DAI05070/GetFurikomiList"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=false
            :options=this.grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>
<style scoped>
</style>
<style>
form[pgid="DAI05070"] .droppable {
    background-color: aqua;
}
form[pgid="DAI05070"] .droppable:empty:before{
    content:attr(data-empty-text)
}
form[pgid="DAI05070"] .droppable:before{
    content:attr(data-path-text)
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05070",
    components: {
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
            }
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI05070Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 一括入金入力",
            noViewModel: true,
            UpdateFileName:null,
            UpdateFileLastModifiedDate:null,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                DateStart: null,
                DateEnd: null,
            },
            DAI05070Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 50,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
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
                    on: false,
                    header: false,
                    grandSummary: false,
                },
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "ファイル名",
                        dataIndx: "ファイル名",
                        dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                    },
                    {
                        title: "ファイル日時",
                        dataIndx: "ファイル日時",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                        render: ui => {
                            if (!!ui.rowData[ui.dataIndx]){
                                return { text: moment(ui.rowData[ui.dataIndx]).format("YYYY年MM月DD日 HH時mm分ss秒") };
                            }
                            return ui;
                        }
                    },
                    {
                        title: "処理日付",
                        dataIndx: "処理日付",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                        render: ui => {
                            if (!!ui.rowData[ui.dataIndx]){
                                return { text: moment(ui.rowData[ui.dataIndx]).format("YYYY年MM月DD日") };
                            }
                            return ui;
                        }
                    },
                    {
                        title: "銀行",
                        dataIndx: "銀行",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "本支店",
                        dataIndx: "本支店",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "種別",
                        dataIndx: "種別",
                        dataType: "string",
                        width: 80, maxWidth: 80, minWidth: 80,
                    },
                    {
                        title: "口座番号",
                        dataIndx: "口座番号",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "振込合計金額",
                        dataIndx: "振込合計金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "結果",
                        dataIndx: "結果",
                        dataType: "string",
                        width: 50, maxWidth: 50, minWidth: 50,
                        align: "center",
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05070Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "明細", id: "DAI05070Grid1_Detail", disabled: false, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
               {visible: "false"},
                {visible: "false"},
            );

        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateStart = moment().startOf('month').format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf('month').format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onDateChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function() {
            var vue = this;
            var params = $.extend(true, {}, vue.viewModel);
            if (!vue.getLoginInfo().isLogOn) return;
            params.StartDate  = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;
            params.EndDate  = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;
            grid.searchData(params, false);
        },
        addFileCallback: function(event) {
            var vue = this;
            console.log("5070 addFileFileCallback", event);
            vue.UpdateFileName=event.name;
            vue.UpdateFileLastModifiedDate=event.lastModifiedDate;
            $(vue.$el).find(".UploadFile").attr("data-path-text", event.name);
        },
        uploadFileCallback: function(res) {
            var vue = this;

            if (!!res.result) {
                console.log("5070 uploadCallback", res)
                vue.showDetail(null,res);
            } else {
                $.dialogErr({
                    title: "アップロード失敗",
                    contents: res.message,
                });
            }
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;
        },
        showDetail: function(rowData,fileData) {
            var vue = this;
            var grid = vue.DAI05070Grid1;

            var data;
            if (!!fileData) {
                data = {
                    ファイル名:vue.UpdateFileName,
                    ファイル日時:moment(vue.UpdateFileLastModifiedDate).format("YYYY年MM月DD日 HH時mm分ss秒"),
                    処理日付:vue.viewModel.TargetDate,
                    振込合計金額:fileData.Summary.合計金額,
                    ファイルデータ:fileData,
                };
            } else if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                TargetDate: data.処理日付,
                FurikomiFileName: data.ファイル名,
                FurikomiFileDate: data.ファイル日時,
                FurikomiKingaku: data.振込合計金額,
                FileData: data.ファイルデータ,
                /*
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                DateStart: TargetDate,
                DateEnd: moment(TargetDate,"YYYY年MM月DD日").endOf('month').format("YYYY年MM月DD日"),
                //SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                //CourseKbn: data.コース区分,
                */
            };

            PageDialog.show({
                pgId: "DAI05071",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
            });
        },
    }
}
</script>

