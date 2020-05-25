<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.BushoNm readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <label>処理日付</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.TargetDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>振込ファイル</label>
            </div>
            <div class="col-md-5">
                <input class="form-control p-0 label-blue" style="width: 600px;" type="text" :value=viewModel.FurikomiFileName readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>ファイル日時</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-0 label-blue" style="width: 240px;" type="text" :value=viewModel.FurikomiFileDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>口座</label>
            </div>
            <div class="col-md-5">
                <input class="form-control p-0 label-blue" style="width: 600px;" type="text" :value=viewModel.Kouza readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>振込合計金額</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-0 label-blue" style="width: 120px;" type="text" :value=viewModel.FurikomiKingaku readonly tabindex="-1">
            </div>
        </div>
        <PqGridWrapper
            id="DAI05071Grid1"
            ref="DAI05071Grid1"
            dataUrl="/DAI05071/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.gridOptions
            :onAfterSearchFunc=this.onAfterSearchFunc
            :setCustomTitle=setGridTitle
            :autoToolTipDisabled=true
            classes="ml-0 mr-0"
        />
    </form>
</template>

<style>
[pgid=DAI05071][isChild=true] .row {
    margin-left: 0px !important;
    margin-right: 0px !important;
}
[pgid=DAI05071] svg.pq-grid-overlay {
    display: block;
}
[pgid=DAI05071] .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
[pgid=DAI05071] .pq-grid-cell.plus-value {
    color: black;
}
[pgid=DAI05071] .pq-grid-cell.minus-value {
    color: red;
}
[pgid=DAI05071] .title-col {
    background-image: -webkit-linear-gradient(top, rgb(254, 254, 254), rgb(218, 230, 240));
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05071",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                FurikomiFileName: vue.viewModel.FurikomiFileName,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate || !newVal.CustomerCd;
                vue.footerButtons.find(v => v.id == "DAI05071Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "振込入金取込処理(明細)",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                FurikomiFileName: null,
                FurikomiFileDate: null,
                Kouza: null,
                GinkoMei: null,
                SitenMei: null,
                KouzaBangou: null,
                KouzaSyubetu: null,
                FurikomiKingaku: null,
            },
            IsChild: false,
            IsFirstFocus: false,
            DAI05071Grid1: null,
            gridOptions: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHt: 25,
                rowHtHead: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: false,
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "取引日",
                        dataIndx: "取引日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "振込依頼人名",
                        dataIndx: "依頼人名",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "振込金額",
                        dataIndx: "入金金額", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "結果",
                        dataIndx: "結果",
                        dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "売掛金額",
                        dataIndx: "売掛金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        render: ui => {
                            if (!!ui.rowData.伝票Ｎｏ) {
                                return { text: !!ui.rowData.IsBikoRow
                                    ? ""
                                    : pq.formatNumber(ui.rowData.入金金額, "#,##0")
                                };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金金額", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "手数料(DD)",
                        dataIndx: "振込手数料", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "依頼人登録",
                        dataIndx: "依頼人登録区分",
                        type: "checkbox",
                        cbId: "依頼人登録FLG",
                        width: 150, maxWidth: 150, minWidth: 150,
                        align: "center",
                        editable: true,
                        editor: false,
                    },
                    {
                        title: "依頼人登録",
                        dataIndx: "依頼人登録FLG",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                            check: "true",
                            uncheck: "false",
                        },
                        hidden: false,
                    },
                    {
                        title: "入金登録",
                        dataIndx: "入金登録区分",
                        type: "checkbox",
                        cbId: "入金登録FLG",
                        width: 150, maxWidth: 150, minWidth: 150,
                        align: "center",
                        editable: true,
                        editor: false,
                    },
                    {
                        title: "入金登録",
                        dataIndx: "入金登録FLG",
                        dataType: "string",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                            check: "true",
                            uncheck: "false",
                        },
                        hidden: false,
                    },
                    {
                        title: "入金日",
                        dataIndx: "入金日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 150, maxWidth: 150, minWidth: 150,
                        editable: true,
                    },
                    {
                        title: "店番",
                        dataIndx: "店番",
                        dataType: "string",
                    },
                    {
                        title: "取引店",
                        dataIndx: "取引店",
                        dataType: "string",
                    },
                    {
                        title: "全銀科目コード",
                        dataIndx: "全銀科目コード",
                        dataType: "string",
                    },
                    {
                        title: "預金種類コード",
                        dataIndx: "預金種類コード",
                        dataType: "string",
                    },
                    {
                        title: "預金種類",
                        dataIndx: "預金種類",
                        dataType: "string",
                    },
                    {
                        title: "口座番号",
                        dataIndx: "口座番号",
                        dataType: "string",
                    },
                    {
                        title: "口座名義",
                        dataIndx: "口座名義",
                        dataType: "string",
                    },
                    {
                        title: "照会期間",
                        dataIndx: "照会期間",
                        dataType: "string",
                    },
                    {
                        title: "照会方法",
                        dataIndx: "照会方法",
                        dataType: "string",
                    },
                    {
                        title: "指定日",
                        dataIndx: "指定日",
                        dataType: "string",
                    },
                    {
                        title: "取引区分",
                        dataIndx: "取引区分",
                        dataType: "string",
                    },
                    {
                        title: "依頼人名",
                        dataIndx: "依頼人名",
                        dataType: "string",
                    },
                    {
                        title: "金融期間名",
                        dataIndx: "金融期間名",
                        dataType: "string",
                    },
                    {
                        title: "支店名",
                        dataIndx: "支店名",
                        dataType: "string",
                    },
                    {
                        title: "EDI情報",
                        dataIndx: "EDI情報",
                        dataType: "string",
                    },
                    {
                        title: "入金伝票Ｎｏ",
                        dataIndx: "入金伝票Ｎｏ",
                        dataType: "string",
                    },
                ],
            },
        });

        if (!!vue.params) {
            data.viewModel = $.extend(true, data.viewModel, vue.params);
            data.IsChild = true;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "再検索", id: "DAI05071Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "得意先検索", id: "DAI05071Grid1_SearchCustomer", disabled: false, shortcut: "F8",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "登録", id: "DAI05071Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            /*
            vue.$watch(
                "$refs.DAI05071Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI05071Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
            */
            vue.showBushoInfo();
            vue.conditionChanged();
        },
        showBushoInfo: function(){
            var vue = this;
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI05071/GetBushoInfo", { BushoCd: vue.viewModel.BushoCd,timestamp:tc})
                .then(response => {
                    var res = _.cloneDeep(response.data);
                    window.resr=_.cloneDeep(res);//TODO:
                    vue.viewModel.Kouza=res[0].銀行名 + " " + res[0].支店名 + " " + res[0].口座番号 + " " + res[0].種別;
                })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: " 部署データ検索失敗",
                    contents: " 部署データ検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        activatedFunc: function(vue) {
            vue.IsChild = !!vue.params;
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            console.log("5071 conditionChanged")

            console.log('dialog show')//TODO:
            window.resp=_.cloneDeep(vue.params);//TODO:

            if (!vue.DAI05071Grid1 || !vue.getLoginInfo().isLogOn) return;
            //if (!vue.viewModel.BushoCd || !vue.viewModel.CustomerCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            //if (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid) return;

            if(!!vue.params.FileData){
                vue.getFileData(vue.params.FileData);
            }
            else{
                vue.DAI05071Grid1.searchData(vue.searchParams);
            }
        },
        getFileData: function(FileData)
        {
            var vue = this;
            var grid = vue.DAI05071Grid1;
            var rowIndex=1;
            FileData.Customers.map((v,i)=>{
                /*
                grid.addRow(
                    { newRow:{取引日:"2001/01/01",
                    依頼人名:"aaaaa",入金金額:"0",
                    結果:"a",
                    得意先ＣＤ:"1",
                    得意先名:"1",売掛金額:"0",入金金額:"0",手数料:"0",依頼人登録:"0",入金登録:"0",入金日:"2001/01/01"
                    }, rowIndx: rowIndex++
                    }
                );
                */
                var rowIndx = grid.pdata.length;
                grid.addRow({
                    rowIndx: rowIndx,
                    newRow: {依頼人名:'aaaaa'},
                });
            });
            grid.refreshCM();
        },
        setGridTitle: function (title, grid) {
            return "件数: " + (grid.pdata || []).filter(v => !!v.伝票日付).length;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;
            /*
            if (!res.length || !res[0].SeikyuData) {
                gridSummary.options.dataModel.data = [
                    {
                        "前回請求残高": 0,
                        "今回入金額": 0,
                        "差引繰越額": 0,
                        "今回売上額": 0,
                        "今回請求額": 0,
                    }
                ];
                gridSummary.refreshDataAndView();
            }

            var list = [];
            if (!!res.length) {
                list = _.flatten(
                    res[0].MeisaiList.map(v => {
                        if (!!v.伝票Ｎｏ) {
                            return [v, { "伝票Ｎｏ": v.伝票Ｎｏ, "商品名": v.備考, IsBikoRow: true }];
                        } else {
                            return v;
                        }
                    })
                );
                gridSummary.options.dataModel.data = [res[0].SeikyuData];
                gridSummary.refreshDataAndView();
            }
            */
            return res;
        },
        save:function()
        {
            console.log("save");//TODO:
            var vue=this;
            var grid = vue.DAI05071Grid1;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }

            //登録データの作成
            var FurikomiList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.取引日=r.取引日;
                SaveItem.振込依頼人名=r.依頼人名;
                SaveItem.振込金額=r.振込金額;
                SaveItem.結果=r.結果;
                SaveItem.得意先ＣＤ=r.得意先ＣＤ;
                SaveItem.得意先名=r.得意先名;
                SaveItem.売掛金額=r.売掛金額;
                SaveItem.入金金額=r.入金金額;
                SaveItem.振込手数料=r.振込手数料;
                SaveItem.依頼人登録区分=r.依頼人登録区分;
                SaveItem.入金登録区分=r.入金登録区分;
                SaveItem.入金日=r.入金日;
                SaveItem.店番=r.店番;
                SaveItem.取引店=r.取引店;
                SaveItem.全銀科目コード=r.全銀科目コード;
                SaveItem.預金種類コード=r.預金種類コード;
                SaveItem.預金種類=r.預金種類;
                SaveItem.口座番号=r.口座番号;
                SaveItem.口座名義=r.口座名義;
                SaveItem.照会期間=r.照会期間;
                SaveItem.照会方法=r.照会方法;
                SaveItem.指定日=r.指定日;
                SaveItem.取引区分=r.取引区分;
                SaveItem.依頼人名=r.依頼人名;
                SaveItem.金融機関名=r.金融機関名;
                SaveItem.支店名=r.支店名;
                SaveItem.EDI情報=r.EDI情報;
                SaveItem.入金伝票Ｎｏ=r.入金伝票Ｎｏ;
                SaveItem.依頼人登録区分="1";//TODO:
                SaveItem.入金登録区分="1";//TODO:
                FurikomiList.push(SaveItem);
            });

            //登録実行
            grid.saveData(
                {
                    uri: "/DAI05071/Save",
                    params: {
                        BushoCd:vue.viewModel.BushoCd,
                        TargetDate:vue.viewModel.TargetDate,
                        FurikomiFileName:vue.viewModel.FurikomiFileName,
                        FurikomiFileDate:vue.viewModel.FurikomiFileDate,
                        FurikomiKingaku:vue.viewModel.FurikomiKingaku,
                        FurikomiList: FurikomiList,
                        ShuseiTantoCd:vue.getLoginInfo()["uid"],
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
