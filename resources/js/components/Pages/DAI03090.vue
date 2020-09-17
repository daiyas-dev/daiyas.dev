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
            FileName: null,
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
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: " * * 合　計 * * " };
                            } else if (!!ui.rowData.pq_gsummary) {
                                return { text: " * * 小　計 * * " };
                            } else {
                                return ui;
                            }
                        },
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
                {visible: "false"},
            );

        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        clear: function(){
            var vue = this;
            var grid = DAI03090Grid1;

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
            vue.DAI03090Grid1.showLoading();
            formData.append("TargetDate", vue.searchParams.TargetDate);
        },
        uploadFileCallback: function(res) {
            var vue = this;
            var grid = vue.DAI03090Grid1;

            vue.DAI03090Grid1.hideLoading();
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
            vue.footerButtons.find(v => v.id == "DAI03090Grid1_Save").disabled = !customers.length;

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
            var SaveList = grid.pdata.filter(v => ((v.処理FLG=="true") && (!v.エラー)))
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
            var grid = vue.DAI03090Grid1;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
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
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.row-table > tbody > tr > td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    padding-bottom: 20px;
                }
                table.row-table:nth-child(even) > tbody > tr > td {
                    border-bottom-width: 1px;
                }
                table.row-table > tbody > tr > td:first-child {
                    border-right-width: 1px;
                }
                table.row-table > tbody > tr > td > div {
                    padding-left: 15px;
                    padding-right: 15px;
                }
                div.title {
                    display: block;
                    text-align: center;
                }
                div.title > h3, div.title > h5 {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
            `;

            var headerFunc = (header, idx, length, chunk, chunks) => {
                var TargetDateFrom = moment(vue.searchParams.DateStart, "YYYYMMDD").format("YYYY/MM/DD");
                var TargetDateTo = moment(vue.searchParams.DateEnd, "YYYYMMDD").format("YYYY/MM/DD");

                var Busho = header.pq_gid.split(":");
                var BushoCd = Busho[0] || "";
                var BushoNm = Busho[1] || "部署無し";

                var match = vue.FileName.replace(".txt", "").match(/\d+$/);
                var HikiDate = !!match && match[0].includes(vue.CompanyInfo.引落日)
                    ? moment(match[0]).format("YYYY/MM/DD")
                    : ""
                    ;

                return `
                    <div class="header">
                        <div class="title" style="float: left; width: 100%">
                            <h3>* * * <span></span>振替一覧表<span></span> * * *</h3>
                        </div>
                        <div style="float: left; width: 100%; margin-bottom: 10px;">
                            <div style="float: left; width: 10%; text-align: left;">ファイル名</div>
                            <div style="float: left; width: 30%; text-align: left;">${vue.FileName}</div>
                            <div style="float: left; width: 60%;">&nbsp</div>

                            <div style="float: left; width: 10%; text-align: left;">引落日</div>
                            <div style="float: left; width: 20%; text-align: left;">${HikiDate}</div>
                            <div style="float: left; width: 70%;">&nbsp</div>

                            <div style="float: left; width: 10%; text-align: left;">入金日</div>
                            <div style="float: left; width: 20%; text-align: left;">${moment(vue.searchParams.TargetDate).format("YYYY/MM/DD")}</div>

                            <div style="float: left; width: 38%;">&nbsp</div>
                            <div style="float: left; width: 7%; text-align: right;">作成日</div>
                            <div style="float: left; width: 15%; text-align: center;">${moment().format("YYYY年MM月DD日")}</div>
                            <div style="float: left; width: 5%; text-align: center;">PAGE</div>
                            <div style="float: left; width: 5%; text-align: center;">${idx + 1}/${length}</div>
                        </div>
                    </div>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    div.header > div > div > span {
                                        padding-left: 8px;
                                        padding-right: 8px;
                                    }
                                    div.header-box > div{
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                        padding-left: 3px;
                                        padding-top: 3px;
                                        height: 20px;
                                    }
                                    div.header div:not(.title) {
                                        font-size: 12px;
                                    }
                                    div.header-box > div:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI03090Grid1 {
                                        border-collapse:collapse;
                                    }
                                    table.DAI03090Grid1 thead tr {
                                        border-top: solid 1px black;
                                        border-bottom: solid 1px black;
                                    }
                                    table.DAI03090Grid1 tbody tr {
                                        border-bottom: dotted 1px black;
                                        height: 24px;
                                        min-height: 24px;
                                    }
                                    table.DAI03090Grid1 tbody tr.group-summary,
                                    table.DAI03090Grid1 tbody tr.grand-summary {
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI03090Grid1 tbody tr.group-summary td:nth-child(n+3),
                                    table.DAI03090Grid1 tbody tr.grand-summary td:nth-child(n+3) {
                                        border-bottom: dotted 1px black;
                                    }
                                    table.DAI03090Grid1 tbody tr:first-child {
                                        border-bottom: dotted 1px black;
                                    }
                                    table.DAI03090Grid1 thead tr th {
                                        text-align: left;
                                        padding-left: 2px;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(1) {
                                        width: 10%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(2) {
                                        width: 6%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(3) {
                                        width: 32%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(4) {
                                        width: 10%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(5) {
                                        width: 10%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(6) {
                                        width: 5%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(7) {
                                        width: 6%;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(8) {
                                        width: 8%;
                                        text-align: right;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(9) {
                                        width: 8%;
                                        text-align: right;
                                    }
                                    table.DAI03090Grid1 thead tr th:nth-child(10) {
                                        width: 5%;
                                        text-align: center;
                                    }
                                    table.DAI03090Grid1 tbody tr td {
                                        text-align: left;
                                    }
                                    table.DAI03090Grid1 tbody tr td:nth-child(8) {
                                        text-align: right;
                                    }
                                    table.DAI03090Grid1 tbody tr td:nth-child(9) {
                                        text-align: right;
                                    }
                                    table.DAI03090Grid1 tbody tr td:nth-child(10) {
                                        text-align: center;
                                    }
                                `,
                                headerFunc,
                                24,
                                false,
                                true,
                                true,
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
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>

