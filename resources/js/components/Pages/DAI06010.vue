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
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理選択</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="SearchType"
                    ref="VueOptions_SearchType"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SearchType"
                    :list="[
                        {code: '1', name: '部署全て', label: '1:部署全て'},
                        {code: '2', name: '得意先指定', label: '2:得意先指定'},
                    ]"
                    :onChangedFunc=onSearchTypeChanged
                />
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
                    :params="{ BushoCd: viewModel.BushoCd, CourseCd:null, KeyWord: null }"
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
                    :disabled='viewModel.SearchType != 2'
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>発行範囲</label>
            </div>
            <div class="col-md-2">
                <VueOptions
                    id="IssuePeriod"
                    ref="VueOptions_IssuePeriod"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="IssuePeriod"
                    :list="[
                        {code: '1', name: '全期間', label: '1:全期間'},
                        {code: '2', name: '範囲', label: '2:範囲'},
                    ]"
                    :onChangedFunc=onIssuePeriodChanged
                />
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
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
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力順</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="PrintOrder"
                    ref="VueOptions_PrintOrder"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintOrder"
                    :list="[
                        {code: '1', name: '顧客カナ順', label: '1:顧客カナ順'},
                        {code: '2', name: '発効日降順', label: '2:発効日降順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI06010Grid1"
            ref="DAI06010Grid1"
            dataUrl="/DAI06010/Search"
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
#DAI06010Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06010Grid1 .pq-group-icon {
    display: none !important;
}
#DAI06010Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI06010Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI06010Grid1 .pq-td-div span {
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
    name: "DAI06010",
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
            ScreenTitle: "チケット > チケット印刷管理",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SearchType:"2",
                CustomerCd: null,
                IssuePeriod:"2",
                DateStart:null,
                DateEnd:null,
                PrintOrder:"2",
            },
            DAI06010Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: true,
                rowHtHead: 40,
                rowHt: 30,
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
                    cancel: true,
                    type: "local",
                    sorter:[
                        { dataIndx: "得意先ＣＤ",dir: "up" },
                        { dataIndx: "F発行日",dir: "down" }
                    ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "管理No",
                        dataIndx: "チケット管理番号", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 280, minWidth: 280, maxWidth: 280,
                    },
                    {
                        title: "発効日付",
                        dataIndx: "F発行日", dataType: "string",
                        width: 111, minWidth: 111, maxWidth: 111,
                    },
                    {
                        title: "有効期限",
                        dataIndx: "F有効期限", dataType: "string",
                        width: 111, minWidth: 111, maxWidth: 111,
                    },
                    {
                        title: "印刷日時",
                        dataIndx: "F印刷日時", dataType: "string",
                        width: 111, minWidth: 111, maxWidth: 111,
                        render: ui => {
                            if(ui.Export) {
                                if (ui.rowData.pq_grandsummary) {
                                   return "";
                                }
                            }
                            else{
                                if (ui.rowData.pq_grandsummary) {
                                    //集計行
                                    return { text: "チケット枚数計\nサービス枚数計" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,###",
                        width: 111, minWidth: 111, maxWidth: 111,
                        render: ui => {
                            if(ui.Export) {
                                if (ui.rowData.pq_grandsummary) {
                                   return "";
                                }
                            }
                            else{
                                if (ui.rowData.pq_grandsummary) {
                                    //集計行
                                    var sv=ui.rowData.有効サービス枚数.replace(',','')*1;
                                    sv=sv.toFixed(1);//小数第1位までの表示
                                    sv=sv.toString().replace(/(\d)(?=(\d{3})+(\.\d+))/g , '$1,');//整数部を3桁毎カンマ区切り
                                    return { text: ui.rowData.有効チケット枚数+"\n"+sv };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "券数",
                        dataIndx: "チケット内数", dataType: "integer", format: "#,###",
                        width: 111, minWidth: 111, maxWidth: 111,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if(ui.Export) {
                                if (ui.rowData.pq_grandsummary) {
                                   return "";
                                }
                            }
                            else{
                                if (ui.rowData.pq_grandsummary) {
                                    //集計行
                                    return { text: "チケット金額計\nサービス金額計" };
                                }
                                return ui;
                            }
                        },
                    },
                    {
                        title: "サービス",
                        dataIndx: "SV内数", dataType: "float", format: "#,###.0",
                        width: 111, minWidth: 111, maxWidth: 111,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if(ui.Export) {
                                if (ui.rowData.pq_grandsummary) {
                                   return "";
                                }
                            }
                            else{
                                if (ui.rowData.pq_grandsummary) {
                                    //集計行
                                    return { text: ui.rowData.チケット金額+"\n"+ui.rowData.サービス金額 };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "責任者",
                        dataIndx: "担当者名", dataType: "string",
                        width: 111, minWidth: 111, maxWidth: 111,
                    },
                    {
                        title: "捨",
                        dataIndx: "廃棄対象",
                        type: "checkbox",
                        cbId: "F廃棄",
                        width: 50, minWidth: 50, maxWidth: 50,
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
                        title: "捨",
                        dataIndx: "F廃棄",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                            check: "YES",
                            uncheck: "NO",
                        },
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "integer",
                        hidden:true,
                    },
                    {
                        title: "得意先名カナ",
                        dataIndx: "得意先名カナ", dataType: "string",
                        hidden:true,
                    },
                    {
                        title: "有効チケット枚数",
                        dataIndx: "有効チケット枚数", dataType: "integer", format: "#,###",
                        hidden:true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "チケット金額",
                        dataIndx: "チケット金額", dataType: "integer", format: "#,###",
                        hidden:true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "有効サービス枚数",
                        hidden:true,
                        dataIndx: "有効サービス枚数", dataType: "float", format: "#,###.0",
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "サービス金額",
                        dataIndx: "サービス金額", dataType: "integer", format: "#,###",
                        hidden:true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI06010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "登録", id: "DAI06010Grid1_Save", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.save();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI06010Grid1_Csv", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI06010Grid1.vue.exportData("csv",null,true);
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //日付の初期値 -> 当日
            vue.viewModel.DateStart = moment().startOf('month').format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf('month').format("YYYY年MM月DD日");
        },
        onChanged: function(code, entities) {
            var vue = this;
            //ダミーハンドラ
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSearchTypeChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onIssuePeriodChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;
            //ソートキーの変更
            this.SortKeyChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06010Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;

            //処理選択が「得意先指定」で、得意先未指定の場合は検索しない
            if(vue.viewModel.SearchType==2 && !vue.viewModel.CustomerCd){
                return;
            }

            //発行範囲が「範囲」で、範囲未指定の場合は検索しない
            if(vue.viewModel.IssuePeriod==2 && (!vue.viewModel.DateStart || !vue.viewModel.DateEnd)){
                return;
            }

            var params = $.extend(true, {}, vue.viewModel);
            //パラメータの書式を調整
            params.DateStart=moment(params.DateStart,"YYYY年MM月DD日").format("YYYY/MM/DD");
            params.DateEnd=moment(params.DateEnd,"YYYY年MM月DD日").format("YYYY/MM/DD");

            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            //ソートキーの変更
            this.SortKeyChanged();
            //ボタン無効化制御
            vue.footerButtons.find(v => v.id == "DAI06010Grid1_Save").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI06010Grid1_Csv").disabled = !res.length;
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
                .filter(v => (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd) ? (v.部署CD == vue.viewModel.BushoCd && v.コースＣＤ == vue.viewModel.CourseCd) : true)
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
        SortKeyChanged:function()
        {
            var vue = this;
            var grid = vue.DAI06010Grid1;
            var keys = [];

            if(vue.viewModel.PrintOrder=='1')
            {
                keys.push({ dataIndx: "得意先名カナ",dir: "up" });
            }else{
                keys.push({ dataIndx: "得意先ＣＤ",dir: "up" });
                keys.push({ dataIndx: "F発行日",dir: "down" });
            }
            grid.sort({"sorter": keys});
        },
        save:function()
        {
            var vue = this;
            var grid = vue.DAI06010Grid1;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }

            //登録データの作成
            var SaveList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.TicketNo=r.チケット管理番号;
                SaveItem.CustomerCd=r.得意先ＣＤ;
                SaveItem.Dispose=(r.F廃棄=="YES");
                SaveList.push(SaveItem);
            });

            //登録実行
            grid.saveData(
                {
                    uri: "/DAI06010/Save",
                    params: {
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
        },
    }
}
</script>
