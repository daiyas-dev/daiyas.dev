<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="BushoCd"
                    ref="VueMultiSelect_Busho"
                    :vmodel=viewModel
                    bind="BushoArray"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力区分</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="BankFormat"
                    ref="VueOptions_BankFormat"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="BankFormat"
                    :list="[
                        {code: '0', name: '山口銀行', label: '0:山口銀行'},
                        {code: '1', name: '東京三菱UFJ', label: '1:東京三菱UFJ'},
                    ]"
                    :onChangedFunc=onBankFormatChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理年月</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>銀行引落日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="WithdrawalDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="WithdrawalDate"
                    :editable=true
                    :onChangedFunc=onWithdrawalDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-4">
                <VueCheck
                    id="VueCheck_IsKouzaYose"
                    ref="VueCheck_IsKouzaYose"
                    :vmodel=viewModel
                    bind="IsKouzaYose"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '口座寄せを行わない', label: '口座寄せを行う'},
                        {code: '1', name: '口座寄せを行う', label: '口座寄せを行う'},
                    ]"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日１</label>
            </div>
            <div class="col-md-3">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="number" :value=viewModel.Simebi1 @input="onSimebi1Changed">
                <label style="width: 100px;">（月末：99）</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日２</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="number" :value=viewModel.Simebi2 @input="onSimebi2Changed">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日３</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="number" :value=viewModel.Simebi3 @input="onSimebi3Changed">
            </div>
        </div>
        <PqGridWrapper
            id="DAI03080Grid1"
            ref="DAI03080Grid1"
            dataUrl="/DAI03080/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI03080Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03080Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03080Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03080Grid1 .pq-td-div span {
    line-height: inherit;
    text-align: center;
}
label{
    width: 80px;
}
#DAI03080Grid1 input[type="number"]::-webkit-outer-spin-button,
#DAI03080Grid1 input[type="number"]::-webkit-inner-spin-button {
    position: relative;
    right: -10px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI03080",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        BushoCdArray: function() {
            var vue = this;
            return vue.viewModel.BushoArray.map(v => v.code);
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > ビジネスダイレクトフォーマット出力",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                BankFormat: "0",
                TargetDate: null,
                WithdrawalDate:null,
                IsKouzaYose:"0",
                Simebi1:null,
                Simebi2:null,
                Simebi3:null,
            },
            IsShowDialog:false,
            DAI03080Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHt: 35,
                freezeCols: 2,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 20,
                    dataIndx: ["部署ＣＤ"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "No.",
                        dataIndx: "No", dataType: "string",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "請求先ＣＤ",
                        dataIndx: "請求先ＣＤ", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 200, minWidth: 200,maxWidth: 200,
                        render: ui => {
                            var vue = this;
                            if (!!ui.rowData.pq_grandsummary) {
                                //合計行
                                ui.rowData["得意先名"] = "合計";
                                return { text: " * * 合　計 * * " };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                //小計行
                                ui.rowData["得意先名"] = "小計";
                                return { text: " * * 小　計 * * " };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "今回請求額",
                        dataIndx: "今回請求額", dataType: "integer", format: "#,##0",
                        width: 90, maxWidth: 90, minWidth: 90,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt"
                        },
                    },
                    {
                        title: "引落銀行番号",
                        dataIndx: "引落銀行番号", dataType: "string",
                        width: 110, maxWidth: 110, minWidth: 110,
                    },
                    {
                        title: "引落銀行名",
                        dataIndx: "引落銀行名", dataType: "string",
                        width: 110, maxWidth: 110, minWidth: 110,
                    },
                    {
                        title: "引落支店番号",
                        dataIndx: "引落支店番号", dataType: "string",
                        width: 110, maxWidth: 110, minWidth: 110,
                    },
                    {
                        title: "引落支店名",
                        dataIndx: "引落支店名", dataType: "string",
                        width: 110, maxWidth: 110, minWidth: 110,
                    },
                    {
                        title: "預金種目",
                        dataIndx: "預金種目", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "口座番号",
                        dataIndx: "口座番号", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "預金者名",
                        dataIndx: "預金者名", dataType: "string",
                        width: 120, maxWidth: 120, minWidth: 120,
                    },
                    {
                        title: "顧客番号",
                        dataIndx: "顧客番号", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03080Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "実行", id: "DAI03080Grid1_Download", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.FileDownload();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03080Grid1_Print", disabled: true, shortcut: "F7",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            vue.LatestSeikyuDateGet();
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;
            vue.WithdrawalDateGet();
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onWithdrawalDateChanged: function(code, entity) {
            var vue = this;
            if(vue.IsShowDialog){
                return;
            }

            //銀行引落日が土日でなければ処理終了
            var dayno = moment(vue.viewModel.WithdrawalDate, "YYYY年MM月DD日").day();
            if((dayno!=0) && (dayno!=6)){
                return;
            }
            var next_monday;
            if(dayno==0){
                next_monday = moment(vue.viewModel.WithdrawalDate, "YYYY年MM月DD日").add('days', 1).format("YYYY年MM月DD日");
            }
            else if(dayno==6){
                next_monday = moment(vue.viewModel.WithdrawalDate, "YYYY年MM月DD日").add('days', 2).format("YYYY年MM月DD日");
            }

            vue.IsShowDialog=true;
            $.dialogConfirm({
                title: "確認",
                contents: "引落日が休日です。<br/>引落日を【"+ moment(next_monday, "YYYY年MM月DD日").format("MM月DD日") +"】に変更しますか？",
                buttons:[
                    {
                        text: "はい",
                        class: "btn btn-primary",
                        click: function(){
                            $(this).dialog("close");
                            vue.viewModel.WithdrawalDate=next_monday;
                            vue.IsShowDialog=false;
                        }
                    },
                    {
                        text: "いいえ",
                        class: "btn btn-danger",
                        click: function(){
                            $(this).dialog("close");
                            vue.IsShowDialog=false;
                        }
                    },
                ],
            });
        },
        onBankFormatChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimebi1Changed: function(event) {
            var vue = this;
            vue.viewModel.Simebi1=event.target.value*1;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        onSimebi2Changed: function(event) {
            var vue = this;
            vue.viewModel.Simebi2=event.target.value*1;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        onSimebi3Changed: function(event) {
            var vue = this;
            vue.viewModel.Simebi3=event.target.value*1;
            //条件変更ハンドラ
            //vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03080Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;

            var params=this.ParamGet();
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI03080Grid1_Download").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI03080Grid1_Print").disabled = !res.length;

            //No追加
            res.forEach((v,i) => { v.No = i + 1; });

            return res;
        },
        ParamGet: function(){
            var vue = this;
            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.TargetDate = params.TargetDate ? params.TargetDate+"01日" : null;
            params.StartDate  = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;
            params.EndDate    = params.TargetDate ? moment(params.StartDate, "YYYY年MM月DD日").endOf('month').format("YYYY/MM/DD") : null;
            params.WithdrawalDate= params.WithdrawalDate ? moment(params.WithdrawalDate, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            return params;
        },
        LatestSeikyuDateGet: function(){
            var vue = this;

            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0){
                vue.viewModel.TargetDate = moment().format("YYYY年MM月")+"01日";
                return;
            }
            var BushoCd=vue.BushoCdArray[0];

            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI03080/LatestSeikyuDateGet", { BushoCd:BushoCd, timestamp:tc })
                .then(response => {
                    vue.viewModel.TargetDate = moment(response.data.前回請求日, "YYYY/MM/DD").format("YYYY年MM月")+"01日";
                })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: " 各種テーブル検索失敗",
                    contents: " 各種テーブル検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        WithdrawalDateGet: function(){
            var vue = this;
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI03080/WithdrawalDateGet", { timestamp:tc })
                .then(response => {
                    vue.viewModel.WithdrawalDate = moment(vue.viewModel.TargetDate+"01日", "YYYY年MM月DD日").add(1,'month').format("YYYY年MM月") + ( '00' + response.data.銀行引落日 ).slice( -2 ) + "日";
                })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: " 各種テーブル検索失敗",
                    contents: " 各種テーブル検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        FileDownload: function() {
            var vue=this;

            var BankName =vue.viewModel.BankFormat=="0" ? "山口銀行" : "三菱東京ＵＦＪ";//銀行名が「三菱東京ＵＦＪ」になっているが、良いか。現在は「三菱ＵＦＪ銀行」
            var params=this.ParamGet();

            //登録実行
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios({
                    url: '/DAI03080/FileDownload',
                    method: 'POST',
                    responseType: 'blob', // これがないと文字化けする
                    data : {
                        timestamp:tc,
                        BushoArray:params.BushoArray,
                        BankFormat:params.BankFormat,
                        StartDate:params.StartDate,
                        EndDate:params.EndDate,
                        WithdrawalDate:params.WithdrawalDate,
                        IsKouzaYose:params.IsKouzaYose,
                        Simebi1:params.Simebi1,
                        Simebi2:params.Simebi2,
                        Simebi3:params.Simebi3,
                    }
                }).then((response) => {
                    const bloburl = URL.createObjectURL(new Blob([response.data],{type: 'text/csv'}));
                    const link = document.createElement('a');
                    link.href = bloburl;
                    link.download="ビジネスダイレクト"+ BankName + moment(params.WithdrawalDate, "YYYY年MM月DD日").format("YYYYMMDD") +".txt";
                    link.click();
                    URL.revokeObjectURL(bloburl);
                });
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
                div.header-table {
                    font-family: "MS UI Gothic";
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    height: 22px;
                }
                div.header-table span {
                    margin-right: 25px;
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
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 18px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3>* * ビジネスダイレクトフォーマット * *</h3>
                    </div>
                    <div class="header">
                        <th>部署：</th>
                        <th>${DAI03080.viewModel.BushoArray.map(v => (v.code + ":" + v.name))}</th>
                    </div>
                    <div class="header">
                        <th>出力区分：</th>
                        <th>${vue.viewModel.BankFormat == 0 ? '0:山口銀行' : '1:東京三菱UFJ'}</th>
                    </div>
                    <div class="header">
                        <th>処理年月：</th>
                        <th>${moment(vue.viewModel.TargetDate, "YYYY年MM月").format("YYYY年MM月")}</th>
                    </div>
                    <div class="header">
                        <th>銀行引落日：</th>
                        <th>${moment(vue.viewModel.WithdrawalDate, "YYYY年MM月DD日").format("YYYY年MM月DD日")}</th>
                        <th>${vue.viewModel.IsKouzaYose == 0 ? "口座寄せを行わない" : "口座寄せを行う"}</th>
                    </div>
                    <div class="header">
                        <th>締日 (月末:99) ：</th>
                        <th>${!!vue.viewModel.Simebi1 ? vue.viewModel.Simebi1 : ""}
                            <span/>${!!vue.viewModel.Simebi2 ? vue.viewModel.Simebi2 : ""}
                            <span/>${!!vue.viewModel.Simebi3 ? vue.viewModel.Simebi3 : ""}
                        </th>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 74.0%;"></th>
                                <th style="width: 6.0%;">作成日</th>
                                <th style="width: 10.0%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 5.0%;">PAGE</th>
                                <th style="width: 5.0%; text-align: right;">${idx + 1}/${length}</th>
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
                            vue.DAI03080Grid1.generateHtml(
                                `
                                    table.DAI03080Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr.group-summary td,
                                    table.DAI03080Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(1) {
                                        width: 3.5%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(2) {
                                        width: 3.0%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(3) {
                                        width: 4.0%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(4),
                                    table.DAI03080Grid1 tr th:nth-child(12) {
                                        width: 21.0%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(5) {
                                        width: 7.0%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(7),
                                    table.DAI03080Grid1 tr th:nth-child(9) {
                                        width: 9.0%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(6),
                                    table.DAI03080Grid1 tr th:nth-child(8) {
                                        width: 4.5%;
                                    }
                                    table.DAI03080Grid1 tr th:nth-child(10) {
                                        width: 3.5%;
                                    }
                                    table.DAI03080Grid1 tr th:last-child {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr:not(.grand-summary):not(.group-summary) td:last-child {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr.group-summary td:nth-child(4),
                                    table.DAI03080Grid1 tr.grand-summary td:nth-child(4) {
                                        text-align: center;
                                    }
                                    table.DAI03080Grid1 tr.group-summary td:first-child,
                                    table.DAI03080Grid1 tr.grand-summary td:first-child {
                                        border-left-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI03080Grid1 tr.group-summary td:last-child,
                                    table.DAI03080Grid1 tr.grand-summary td:last-child {
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.header-table th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(1) {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(5) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    div.header {
                                        font-family: "MS UI Gothic";
                                        font-size: 11pt;
                                        font-weight: normal;
                                        margin: 0px;
                                        padding-left: 3px;
                                        padding-right: 3px;
                                        height: 18px;
                                    }
                                `,
                                headerFunc,
                                25,
                                false,
                                true,
                                false,
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
