<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onChanged
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
                    :onChangedFunc=onChanged
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
                    :onChangedFunc=onChanged
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
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "ファイル日時",
                        dataIndx: "ファイル日時",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "処理日付",
                        dataIndx: "処理日付",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
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
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "種別",
                        dataIndx: "種別",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "口座番号",
                        dataIndx: "口座番号",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
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
                    {
                        title: "詳細",
                        editable: false,
                        width: 75, maxWidth: 75, minWidth: 75,
                        align: "center",
                        sortable: false,
                        render: function (ui) {
                            return "<button type='button' class='edit_btn'>詳細</button>";
                        },
                        postRender: function (ui) {
                            var rowIndx = ui.rowIndx,
                                grid = this,
                                $cell = grid.getCell(ui);
                            //TODO:ボタンの処理はこれから記述
                            if(grid.hasClass({ rowData: ui.rowData, cls: 'pq-row-edit' })){
                                //update button
                                $cell.find(".edit_btn")
                                .button({ label: "Update", icons: { primary: 'ui-icon-check'} })
                                .off("click")
                                .on("click", function () {
                                    return update(rowIndx, grid);
                                });
                            }
                            else{
                                //edit button
                                $cell.find(".edit_btn").button({ icons: { primary: 'ui-icon-pencil'} })
                                .off("click")
                                .on("click", function (evt) {
                                    if (isEditing(grid)) {
                                        return false;
                                    }
                                    editRow(rowIndx, grid );
                                });
                            }
                        }
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
                { visible: "true", value: "印刷", id: "DAI05070Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI05070_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
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
                { visible: "true", value: "登録", id: "DAI05070Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
            );

        },
        mountedFunc: function(vue) {
            //TODO
            // vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.TargetDate = moment("20191212").format("YYYY年MM月DD日");
        },
        onChanged: function(code, entities) {
            //TODO:ダミーハンドラ
            //var vue = this;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI05070Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams, false);
        },
        addFileCallback: function(event) {
            var vue = this;
            console.log("5070 addFileFileCallback", event);
            $(vue.$el).find(".UploadFile").attr("data-path-text", event.name);
        },
        uploadFileCallback: function(res) {
            var vue = this;

            if (!!res.result) {
                console.log("5070 uploadCallback", res)
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
        showDetail: function(isNew, rowData) {
            var vue = this;
            var grid = vue.DAI05070Grid1;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }
            var TargetDate=vue.viewModel.TargetDate + "01日";
            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                /*
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: TargetDate,
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
        save: function() {
            var vue = this;
            var grid = vue.DAI05070Grid1;

            var hasError = !!$(vue.$el).find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var SaveList = _.cloneDeep(grid.createSaveParams());

            _.forIn(SaveList,
                (v, k) => {
                    var list = v.filter(r => {
                        return r.商品ＣＤ != null && r.商品ＣＤ != undefined　&& r.商品ＣＤ != "";
                    })
                    .map(r => {
                        r.部署ＣＤ = vue.viewModel.BushoCd;

                        r.販売期間開始 = !!r.販売期間開始
                            ? moment(r.販売期間開始, "YYYY/MM/DD").format("YYYY-MM-DD HH:mm:ss.SSS")
                            : null;
                        r.販売期間終了 = !!r.販売期間終了
                            ? moment(r.販売期間終了, "YYYY/MM/DD").format("YYYY-MM-DD HH:mm:ss.SSS")
                            : null;

                        r.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        delete r.商品区分;
                        delete r.商品名;
                        delete r.主要商品;
                        delete r.特別商品;
                        delete r.sortIndx;
                        return r;
                    })
                    ;
                    SaveList[k] = list;
                }
            );

            if (_.values(SaveList).every(v => !v.length)) {
                //変更無し
                $.dialogInfo({
                    title: "変更無し",
                    contents: "データが変更されていません。",
                });
                return;
            }

            //保存実行
            var params = {SaveList: SaveList, CustomerCd: vue.viewModel.CustomerCd};
            params.noCache = true;

            //登録中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 登録中…",
            });

            axios.post("/DAI05070/Save", params)
                .then(res => {
                    progressDlg.dialog("close");
                    grid.refreshDataAndView();
                })
                .catch(err => {
                    progressDlg.dialog("close");
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "登録に失敗しました<br/>",
                    });
                });
        },
    }
}
</script>

