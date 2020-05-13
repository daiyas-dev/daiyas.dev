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
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi1>
                <label style="width: 100px;">（月末：99）</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日２</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日３</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Simebi3>
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
                CourseCd: null,
                CustomerCd: null,
            },
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
                freezeCols: 1,
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
                        width: 60, minWidth: 60, maxWidth: 60,
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
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "引落銀行名",
                        dataIndx: "引落銀行名", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "引落支店番号",
                        dataIndx: "引落支店番号", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "引落支店名",
                        dataIndx: "引落支店名", dataType: "string",
                        width: 90, maxWidth: 90, minWidth: 90,
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
                        width: 90, maxWidth: 90, minWidth: 90,
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
                { visible: "true", value: "実行", id: "DAI03080Grid1_Download", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.FileDownload();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            //TODO:
            console.log("mounted");//TODO:
            vue.viewModel.TargetDate = moment("20190507").format("YYYY年MM月");
            //vue.viewModel.CourseCd=101;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onWithdrawalDateChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBankFormatChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03080Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!vue.viewModel.TargetDate) return;

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart  = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd    = params.TargetDate ? moment(params.DateStart).add(11, 'months').format("YYYYMMDD") : null;
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            //delete params.CourseCd;
            //delete params.CustomerCd;

            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            return res;
        },
        FileDownload: function() {
            var vue=this;
            /*
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }
            */

            //理由が理由2の場合、金額を更新する。
            /*
            var Kingaku=0;
            if(vue.viewModel.AdjustmentReason==2)
            {
                Kingaku=vue.viewModel.Kingaku;
            }
            var AdjustmentDate=moment(vue.viewModel.AdjustmentDate,"YYYY年MM月DD日").format("YYYY/MM/DD");
            var ShuseiTantoCd=vue.getLoginInfo()["uid"];
            */

            //登録実行
            console.log("FileDownload");//TODO:
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios({
                    url: '/DAI03080/FileDownload',
                    method: 'POST',
                    responseType: 'blob', // これがないと文字化けする
                    data : { timestamp:tc }
                }).then((response) => {
                    window.resa=_.cloneDeep(response);//TODO
                    const bloburl = URL.createObjectURL(new Blob([response.data],{type: 'text/csv'}));
                    const link = document.createElement('a');
                    link.href = bloburl;
                    link.download="abc.txt";
                    link.click();
                    URL.revokeObjectURL(bloburl);
                });
        },
    }
}
</script>
