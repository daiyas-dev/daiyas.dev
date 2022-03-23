<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label style="width: unset;">注文情報ファイル</label>
            </div>
            <div class="col-md-6">
                <div
                    class="UploadFile droppable d-flex align-items-center w-100 h-100 pl-2"
                    style="cursor: pointer;"
                    data-empty-text="対象ファイルをドロップ、もしくはここをクリックして選択"
                    data-path-text=""
                    data-url="/DAI01270/UploadFile"
                    data-addedfile-callback="addFileCallback"
                    data-sending-callback="sendingCallback"
                    data-upload-callback="uploadFileCallback"
                >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label id="AlertText" class="badge-danger" style="width: unset;" disabled=true ></label>
            </div>
        </div>
        <PqGridWrapper
            id="DAI01270Grid1"
            ref="DAI01270Grid1"
            dataUrl="/DAI01270/Dummy"
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
        />
    </form>
</template>
<style scoped>
</style>
<style>
form[pgid="DAI01270"] .droppable {
    background-color: aqua;
}
form[pgid="DAI01270"] .droppable:empty:before{
    content:attr(data-empty-text)
}
form[pgid="DAI01270"] .droppable:before{
    content:attr(data-path-text)
}
form[pgid="DAI01270"] .pq-group-toggle-none {
    display: none !important;
}
form[pgid="DAI01270"] .pq-group-icon {
    display: none !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01270",
    components: {
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
            };
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI01270Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 外部受注システム取込",
            noViewModel: true,
            viewModel: {
                TargetDate: null,
            },
            FileName: null,
            CompanyInfo: null,
            CustomerInfoArray: [],
            DAI01270Grid1: null,
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
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                colModel: [
                    {
                        title: "行番号",
                        dataIndx: "行番号",
                        dataType: "integer",
                        hidden: true,
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "配送日",
                        dataIndx: "配送日",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "得意先CD",
                        dataIndx: "得意先CD",
                        dataType: "integer",
                        align: "right",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "顧客名",
                        dataIndx: "顧客名",
                        dataType: "string",
                        width: 300, maxWidth: 300, minWidth: 300,
                    },
                    {
                        title: "商品CD",
                        dataIndx: "商品CD",
                        dataType: "string",
                        align: "center",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 250, maxWidth: 250, minWidth: 250,
                    },
                    {
                        title: "数量",
                        dataIndx: "数量",
                        dataType: "string",
                        align: "right",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI01270Grid1_Clear", disabled: true, shortcut: "F2",
                    onClick: function () {
                        vue.clear();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "実行", id: "DAI01270Grid1_Save", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.save();
                    }
                },
            );

        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        clear: function(){
            var vue = this;
            var grid = DAI01270Grid1;

            document.getElementById("AlertText").innerText = '';

            $(vue.$el).find(".UploadFile").attr("data-path-text", "対象ファイルをドロップ、もしくはここをクリックして選択");
            grid.clearData();
        },
        addFileCallback: function(event) {
            var vue = this;
            $(vue.$el).find(".UploadFile").attr("data-path-text", event.name);
            vue.FileName = event.name;
        },
        sendingCallback: function(event, xhr, formData) {
            var vue = this;
            vue.DAI01270Grid1.showLoading();
            formData.append("TargetDate", vue.searchParams.TargetDate);
        },
        uploadFileCallback: function(res) {
            var vue = this;
            var grid = vue.DAI01270Grid1;

            vue.DAI01270Grid1.hideLoading();
            if (!!res.result) {

                vue.Contents = _.cloneDeep(res.Contents);
                vue.CompanyInfo = _.cloneDeep(res.Company);
                vue.CustomerInfoArray = _.cloneDeep(res.Customers);

                vue.setLocalData(vue.CustomerInfoArray);
            } else {
                vue.Contents = null;
                vue.CompanyInfo = null;
                vue.CustomerInfoArray = [];

                grid.clearData();

                vue.footerButtons.find(v => v.id == "DAI01270Grid1_Clear").disabled = true;
                vue.footerButtons.find(v => v.id == "DAI01270Grid1_Save").disabled = true;

                if (res.DataCheckNo == 0) {
                    $.dialogErr({
                        title: "アップロード失敗",
                        contents: res.message,
                    });
                } else {
                    vue.Contents = _.cloneDeep(res.Contents);
                    vue.CompanyInfo = _.cloneDeep(res.Company);
                    vue.CustomerInfoArray = _.cloneDeep(res.Customers);

                    vue.setLocalData(vue.CustomerInfoArray);
                    if(res.Executionflg == 2) {
                        vue.footerButtons.find(v => v.id == "DAI01270Grid1_Save").disabled = true;
                    };

                    $.dialogErr({
                        title: "CSV不正",
                        contents: res.message,

                    });
                };
            }
            if (res.Existenceflg == 1){
                document.getElementById("AlertText").innerText = '同名のファイルを取込済です。';
                let checkSaveFlg = window.confirm('CSVファイルが登録済みですが更新しますか？');
                    if(checkSaveFlg) {
                } else {
                    vue.footerButtons.find(v => v.id == "DAI01270Grid1_Save").disabled = true;
                }
            }else{
                document.getElementById("AlertText").innerText = '';
            };
        },
        setLocalData: function(data, keep) {
            var vue = this;
            var grid = vue.DAI01270Grid1;

            var customers = data
                .map(v => {
                    v.行番号 = !!v.行番号 ? v.行番号 : null;

                    return v;
                });

            customers = _.sortBy(customers, v => v.行番号);

            vue.footerButtons.find(v => v.id == "DAI01270Grid1_Clear").disabled = !customers.length;
            vue.footerButtons.find(v => v.id == "DAI01270Grid1_Save").disabled = !customers.length;

            grid.setLocalData(_.cloneDeep(customers));
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI01270Grid1;
            var now = moment().format("YYYY-MM-DD HH:mm:ss");
            var FileName = $(".UploadFile").data('path-text');
            var Manager = vue.getLoginInfo().uid;
            var SaveList = grid.pdata
                .map(r => {
                    var v = {};

                    v.配送日 = r.配送日;
                    v.得意先ＣＤ = r.得意先CD;
                    v.商品ＣＤ = r.商品CD;
                    v.掛売個数 = r.数量;
                    v.修正担当者CD = Manager;

                    return v;
                });

            grid.prevData = _.cloneDeep(grid.pdata);

            //保存実行
            var params = { SaveList: SaveList, FileName: FileName, Manager: Manager, Contents: vue.Contents };
            params.noCache = true;
            grid.saveData(
                {
                    uri: "/DAI01270/Save",
                    params: params,
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    //以下のwhenはdoneが元々となる
                    when: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            grid.commit();
                            vue.setLocalData(_.cloneDeep(res.Customers), true);
                            return false;
                        },
                    },
                    done: {
                        isShow: true,
                        title: "正常終了",
                        message: "注文情報の保存が完了しました。",
                        callback: (vue, grid, res)=>{},
                    },
                }
            );
        },
    }
}
</script>

