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
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi1 @input="onSimebi1Changed">
                <label style="width: 100px;">（月末：99）</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日２</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi2 @input="onSimebi2Changed">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日３</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi3 @input="onSimebi3Changed">
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
                    on: false,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "請求先ＣＤ",
                        dataIndx: "請求先ＣＤ", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 200, minWidth: 200,maxWidth: 200,
                        tooltip: true,
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
                        tooltip: true,
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
                }
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
        onSimebi1Changed: _.debounce(function(event) {
            var vue = this;
            vue.viewModel.Simebi1=event.target.value*1;
            //条件変更ハンドラ
            vue.conditionChanged();
        }, 300),
        onSimebi2Changed: _.debounce(function(event) {
            var vue = this;
            vue.viewModel.Simebi2=event.target.value*1;
            //条件変更ハンドラ
            vue.conditionChanged();
        }, 300),
        onSimebi3Changed: _.debounce(function(event) {
            var vue = this;
            vue.viewModel.Simebi3=event.target.value*1;
            //条件変更ハンドラ
            vue.conditionChanged();
        }, 300),
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03080Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!vue.viewModel.TargetDate) return;

            var params=this.ParamGet();
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI03080Grid1_Download").disabled = !res.length;
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
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;

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
    }
}
</script>
