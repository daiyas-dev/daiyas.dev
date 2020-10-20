<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理区分</label>
            </div>
            <div class="col-md-2">
                <VueOptions
                    id="ShoriKbn"
                    ref="VueOptions_ShoriKbn"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="ShoriKbn"
                    :list="[
                        {code: '1', name: '集計処理', label: '集計処理'},
                        {code: '2', name: '集計解除', label: '集計解除'},
                    ]"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理年月</label>
            </div>
            <div class="col-md-6">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label>最終処理日</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.LastUpdateDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI03010Grid1"
            ref="DAI03010Grid1"
            dataUrl="/DAI03010/Search"
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
#DAI03010Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03010Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03010Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03010Grid1 .pq-td-div span {
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
    name: "DAI03010",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 月次集計",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CustomerCd: null,
                LastUpdateDate: null,
            },
            DAI03010Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead:50,
                rowHt: 35,
                freezeCols: 5,
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
                        title: "集計済",
                        dataIndx: "当月集計済", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        align: "center",
                    },
                    {
                        title: "部署<br/>ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "請求先<br/>ＣＤ",
                        dataIndx: "請求先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "請求先名",
                        dataIndx: "請求先名", dataType: "string",
                        width: 120, minWidth: 120,
                    },
                    {
                        title: "前月残高",
                        dataIndx: "前月残高", dataType: "integer", format: "#,###",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "今月<br/>入金額",
                        dataIndx: "今月入金額", dataType: "integer", format: "#,###",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "差引<br/>繰越額",
                        dataIndx: "差引繰越額", dataType: "integer", format: "#,###",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "今月<br/>売上額",
                        dataIndx: "今月売上額", dataType: "integer", format: "#,###",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "今月<br/>残高",
                        dataIndx: "今月残高", dataType: "integer", format: "#,###",
                        width: 85, minWidth: 85, maxWidth: 85,
                    },
                    {
                        title: "予備<br/>金額１",
                        dataIndx: "予備金額１", dataType: "integer", format: "#,###",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "予備<br/>金額２",
                        dataIndx: "予備金額２", dataType: "integer", format: "#,###",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "予備<br/>ＣＤ１",
                        dataIndx: "予備ＣＤ１", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "予備<br/>ＣＤ２",
                        dataIndx: "予備ＣＤ２", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "修正<br/>担当者<br/>ＣＤ",
                        dataIndx: "修正担当者ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "修正<br/>担当者名",
                        dataIndx: "修正担当者名", dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI03010Grid1_Update", disabled: true,shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI03010Grid1;

                        //登録データの作成
                        var SaveList=[];
                        _.forEach(grid.pdata,r=>{
                            var SaveItem={};
                            SaveItem.部署ＣＤ=r.部署ＣＤ;
                            SaveItem.請求先ＣＤ=r.請求先ＣＤ;
                            SaveItem.前月残高=r.前月残高;
                            SaveItem.今月入金額=r.今月入金額;
                            SaveItem.差引繰越額=r.差引繰越額;
                            SaveItem.今月売上額=r.今月売上額;
                            SaveItem.今月残高=r.今月残高;
                            SaveItem.予備金額１=r.予備金額１;
                            SaveItem.予備ＣＤ１=r.予備ＣＤ１;
                            SaveItem.予備金額２=r.予備金額２;
                            SaveItem.予備ＣＤ２=r.予備ＣＤ２;
                            SaveList.push(SaveItem);
                        });

                        //登録実行
                        grid.saveData(
                            {
                                uri: "/DAI03010/Save",
                                params: {
                                    BushoCd:vue.viewModel.BushoCd,
                                    TargetDate:vue.viewModel.TargetDate,
                                    ShoriKbn: vue.viewModel.ShoriKbn,
                                    TokuiCd: vue.viewModel.CustomerCd,
                                    ShuseiTantoCd:vue.getLoginInfo()["uid"],
                                    SaveList: SaveList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                    },
                                },
                            }
                        );
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //検索条件変更
            vue.conditionChanged();
        },

        showLastUpdateDate: function(){
            var vue = this;
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI03010/LastUpdateDateSearch", { BushoCd: vue.viewModel.BushoCd,timestamp:tc})
                .then(response => {
                    var res = _.cloneDeep(response.data);
                    vue.viewModel.LastUpdateDate=moment(res.日付).format("YYYY年MM月");
                })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: " 売掛データ検索失敗",
                    contents: " 売掛データ検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        onDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03010Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;

            var params = $.extend(true, {}, vue.viewModel);

            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd   = params.TargetDate ? moment(params.DateStart).endOf('month').format("YYYYMMDD") : null;

            //コースはフィルタするので除外
            delete params.CustomerCd;
            grid.searchData(params, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03010Grid1;
            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.CustomerCd});
            }
            if (crules.length) {
                rules.push({ dataIndx: "請求先ＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            vue.footerButtons.find(v => v.id == "DAI03010Grid1_Update").disabled = !res.length;
            vue.showLastUpdateDate();
            return res;
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;
            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .filter(v => (!!vue.viewModel.BushoCd) ? (v.部署CD == vue.viewModel.BushoCd) : true)
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.replace(/-/g, "").includes(k.replace(/-/g, "")) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
    }
}
</script>
