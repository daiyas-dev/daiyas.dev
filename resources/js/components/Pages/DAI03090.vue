<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label style="width: unset;">振替ファイル</label>
            </div>
            <div class="col-md-6">
                <div
                    class="UploadFile droppable d-flex align-items-center w-100 h-100 pl-2"
                    style="cursor: pointer;"
                    data-empty-text="対象ファイルをドロップ、もしくはここをクリックして選択"
                    data-path-text=""
                    data-url="/DAI03090/UploadFile"
                    data-addedfile-callback="addFileCallback"
                    data-sending-callback="sendingCallback"
                    data-upload-callback="uploadFileCallback"
                >
                </div>
            </div>
            <div class="col-md-1">
                <label>入金日</label>
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
        <PqGridWrapper
            id="DAI03090Grid1"
            ref="DAI03090Grid1"
            dataUrl="/DAI03090/Dummy"
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onRefreshFunc=onRefreshFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>
<style scoped>
</style>
<style>
form[pgid="DAI03090"] .droppable {
    background-color: aqua;
}
form[pgid="DAI03090"] .droppable:empty:before{
    content:attr(data-empty-text)
}
form[pgid="DAI03090"] .droppable:before{
    content:attr(data-path-text)
}
form[pgid="DAI03090"] .pq-group-toggle-none {
    display: none !important;
}
form[pgid="DAI03090"] .pq-group-icon {
    display: none !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI03090",
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
            var grid = vue.DAI03090Grid1;
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
                TargetDate: null,
            },
            CompanyInfo: null,
            CustomerInfoArray: [],
            DAI03090Grid1: null,
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
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["部署"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                formulas: [
                    [
                        "sortIndx",
                        function(rowData){
                            return (rowData["商品区分"] || "9") + "_" + _.padStart(rowData["商品ＣＤ"] || "99999", 5, "0");
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署",
                        dataIndx: "部署", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "部署CD",
                        dataIndx: "部署CD", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名",
                        dataType: "string",
                        width: 125, maxWidth: 125, minWidth: 125,
                    },
                    {
                        title: "得意先CD",
                        dataIndx: "得意先CD", dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        tooltip: true,
                    },
                    {
                        title: "金融機関CD",
                        dataIndx: "金融機関CD",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                    },
                    {
                        title: "銀行名",
                        dataIndx: "金融機関名",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "金融機関支店CD",
                        dataIndx: "金融機関支店CD",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                    },
                    {
                        title: "支店名",
                        dataIndx: "金融機関支店名",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "口座種別",
                        dataIndx: "口座種別",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "種別",
                        dataIndx: "口座種別名",
                        dataType: "string",
                        width: 50, maxWidth: 50, minWidth: 50,
                    },
                    {
                        title: "口座",
                        dataIndx: "口座番号",
                        dataType: "string",
                        align: "center",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "引落金額",
                        dataIndx: "引落金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "エラー",
                        dataIndx: "エラー",
                        dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "処理",
                        dataIndx: "処理",
                        type: "checkbox",
                        cbId: "処理FLG",
                        width: 75, maxWidth: 75, minWidth: 75,
                        align: "center",
                        editable: true,
                        editor: false,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return "";
                            }
                        },
                    },
                    {
                        title: "処理",
                        dataIndx: "処理FLG",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                            check: "true",
                            uncheck: "false",
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI03090Grid1_Clear", disabled: true, shortcut: "F2",
                    onClick: function () {
                        vue.clear();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "実行", id: "DAI03090Grid1_Save", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI03090Grid1_Printout", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI03090Grid1_Download", disabled: true, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
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
        addFileCallback: function(event) {
            var vue = this;
            $(vue.$el).find(".UploadFile").attr("data-path-text", event.name);
        },
        sendingCallback: function(event, xhr, formData) {
            var vue = this;
            formData.append("TargetDate", vue.searchParams.TargetDate);
        },
        uploadFileCallback: function(res) {
            var vue = this;
            var grid = vue.DAI03090Grid1;

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

                vue.footerButtons.find(v => v.id == "DAI03090Grid1_Clear").disabled = true;
                vue.footerButtons.find(v => v.id == "DAI03090Grid1_Save").disabled = true;
                vue.footerButtons.find(v => v.id == "DAI03090Grid1_Printout").disabled = true;
                vue.footerButtons.find(v => v.id == "DAI03090Grid1_Download").disabled = true;

                $.dialogErr({
                    title: "アップロード失敗",
                    contents: res.message,
                });
            }
        },
        setLocalData: function(data, keep) {
            var vue = this;
            var grid = vue.DAI03090Grid1;

            var customers = data
                .map(v => {
                    v.部署 = !!v.部署CD ? (v.部署CD + ":" + v.部署名) : "部署無し";
                    v.得意先CD = !!v.得意先CD ? v.得意先CD * 1 : null;
                    v.引落金額 = !!v.引落金額 ? v.引落金額 * 1 : null;
                    v.入金額 = !!v.入金額 ? v.入金額 * 1 : null;

                    if (!!keep && !!v.得意先CD) {
                        v.処理FLG = grid.prevData.find(r => r.得意先CD == v.得意先CD).処理FLG;
                    }

                    return v;
                });

            customers = _.sortBy(customers, v => v.部署CD);

            vue.footerButtons.find(v => v.id == "DAI03090Grid1_Clear").disabled = !customers.length;
            vue.footerButtons.find(v => v.id == "DAI03090Grid1_Printout").disabled = !customers.length;
            vue.footerButtons.find(v => v.id == "DAI03090Grid1_Download").disabled = !customers.length;

            grid.setLocalData(_.cloneDeep(customers));
        },
        onRefreshFunc: function(grid) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI03090Grid1_Save").disabled = !grid.pdata.some(v => !!v.処理FLG);
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI03090Grid1;

            var tekiyo = Moji(moment(vue.searchParams.TargetDate).format("M")).convert("HE", "ZE").toString() + "月分入金";
            var date = moment(vue.searchParams.TargetDate).endOf("month").format("YYYYMMDD");
            var now = moment().format("YYYY-MM-DD HH:mm:ss");

            var SaveList = grid.pdata.filter(v => !!v.処理FLG)
                .map(r => {
                    var v = {};

                    v.入金日付 = vue.searchParams.TargetDate;
                    v.伝票Ｎｏ = null;
                    v.部署ＣＤ = r.部署CD;
                    v.得意先ＣＤ = r.得意先CD;
                    v.入金区分 = 3;
                    v.現金 = 0;
                    v.小切手 = 0;
                    v.振込 = 0;
                    v.バークレー = r.引落金額;
                    v.その他 = 0;
                    v.相殺 = 0;
                    v.値引 = 0;
                    v.摘要 = tekiyo;
                    v.備考 = "一括入金";
                    v.請求日付 = date;
                    v.予備金額１ = 0;
                    v.予備金額２ = 0;
                    v.予備ＣＤ１ = 0;
                    v.予備ＣＤ２ = 0;
                    v.修正日 = now;
                    v.修正担当者ＣＤ = 9998;    //vue.getLoginInfo().uid;

                    return v;
                });

            grid.prevData = _.cloneDeep(grid.pdata);

            //保存実行
            var params = { SaveList: SaveList, Contents: vue.Contents };
            params.noCache = true;

            grid.saveData(
                {
                    uri: "/DAI03090/Save",
                    params: params,
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            grid.commit();
                            vue.setLocalData(_.cloneDeep(res.Customers), true);

                            return false;
                        },
                    },
                }
            );
        },
        print: function() {
            var vue = this;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.title {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                }
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border: solid 0px black;
                }
                th, td {
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 30px;
                    text-align: center;
                }
                td {
                    height: 17px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3>* * * 売上明細表 * * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 15%;">${vue.viewModel.BushoCd}:${vue.viewModel.BushoNm}</th>
                                <th style="width: 46%;"></th>
                                <th style="width: 10%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 6%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03090Grid1.generateHtml(
                                `
                                    table.DAI03090Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03090Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03090Grid1 tr th:nth-child(1) {
                                        width: 8.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(2) {
                                        width: 4.2%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(3) {
                                        width: 13.6%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(4) {
                                        width: 5.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(5) {
                                        width: 17.9%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(6) {
                                        width: 4.2%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(7) {
                                        width: 8.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(8) {
                                        width: 4.2%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(9) {
                                        width: 4.2%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(10) {
                                        width: 5.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(11) {
                                        width: 5.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(12) {
                                        width: 5.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(13) {
                                        width: 8.5%;
                                    }

                                    table.DAI03090Grid1 tr th:nth-child(14) {
                                        width: 4.2%;
                                    }
                                `,
                                headerFunc,
                                32,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };

            printJS(printOptions);
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>

