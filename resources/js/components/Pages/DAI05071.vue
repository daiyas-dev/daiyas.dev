<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
        <div class="row">
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label>振込ファイル</label>
            </div>
            <div class="col-md-5">
                <input class="form-control p-1 label-blue" style="width: 600px;" type="text" :value=viewModel.FurikomiFileName readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>ファイル日時</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-1 label-blue" style="width: 240px;" type="text" :value=viewModel.FurikomiFileDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>口座</label>
            </div>
            <div class="col-md-5">
                <input class="form-control p-1 label-blue" style="width: 600px;" type="text" :value=viewModel.Kouza readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>振込合計金額</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-0 label-blue" style="width: 120px; text-align:right;" type="text" :value=this.FurikomiKingakuEditted readonly tabindex="-1">
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <fieldset class="w-100">
                    <legend>絞込条件</legend>
                    <div class="row">
                        <div class="col-md-5">
                            <label style="width: auto; margin-right: 10px">得意先名/依頼人名</label>
                            <input class="form-control" type="text" style="width: 200px; font-size: 15px !important;"
                                v-model=filter.CustomerName
                                maxlength="10"
                                v-maxBytes=20
                                @input="onCustomerChanged"
                            >
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <PqGridWrapper
            id="DAI05071Grid1"
            ref="DAI05071Grid1"
            dataUrl="/DAI05071/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.gridOptions
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
form[pgid=DAI05071] label{
    width: auto;
}
form[pgid=DAI05071] fieldset {
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 10px;
    padding-left: 10px;
    margin-bottom: 5px;
}
fieldset * {
    font-size: 14px !important;
}
legend {
    font-size: 18px !important;
    width: 85px !important;
    padding: 0;
    margin: 0;
    margin-left: 5px;
}
fieldset .row {
    margin-left: 10px;
    margin-right: 0px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05071",
    components: {
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                FurikomiFileName: vue.viewModel.FurikomiFileName,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYY/MM/DD"),
            };
        },
        FurikomiKingakuEditted: function() {
            var vue = this;
            return Number(vue.viewModel.FurikomiKingaku).toLocaleString();
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
            filter: {
                CustomerName: null,
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
                rowHtHead: 45,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
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
                        width: 175, maxWidth: 175, minWidth: 175,
                    },
                    {
                        title: "振込金額",
                        dataIndx: "入金金額", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "結果",
                        dataIndx: "結果",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "結果",
                        dataIndx: "結果マーク",
                        dataType: "string",
                        width: 45, maxWidth: 45, minWidth: 45,
                        align: "center",
                    },
                    {
                        title: "得意先CD",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                        width: 80, maxWidth: 80, minWidth: 80,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                    },
                    {
                        title: "得意先名カナ",
                        dataIndx: "得意先名カナ",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "売掛金額",
                        dataIndx: "売掛金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金総額",
                        dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "手数料",
                        dataIndx: "振込手数料",
                        dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editableColumn: true,
                        editable: ui => ui.rowData["CUR入金登録区分"] == "false",
                    },
                    {
                        title: "依頼人登録",
                        dataIndx: "依頼人登録区分",
                        type: "checkbox",
                        cbId: "F依頼人登録区分",
                        width: 75, minWidth: 75, maxWidth: 75,
                        align: "center",
                        editableColumn: true,
                        editor: false,
                    },
                    {
                        title: "依頼人登録",
                        dataIndx: "F依頼人登録区分",
                        dataType: "string",
                        align: "center",
                        editableColumn: true,
                        cb: {
                            header: true,
                            check: "true",
                            uncheck: "false",
                        },
                        hidden: true,
                    },
                    {
                        title: "入金登録",
                        dataIndx: "入金登録区分",
                        type: "checkbox",
                        cbId: "F入金登録区分",
                        width: 60, minWidth: 60, maxWidth: 60,
                        align: "center",
                        editableColumn: true,
                        editable: ui => !ui.rowData["CUR入金登録区分"],
                        editor: false,
                        render: ui => ui.rowData["CUR入金登録区分"] == "true" ? "済" : ui,
                    },
                    {
                        title: "入金登録",
                        dataIndx: "CUR入金登録区分",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                    },
                    {
                        title: "入金登録",
                        dataIndx: "F入金登録区分",
                        dataType: "string",
                        align: "center",
                        editableColumn: true,
                        cb: {
                            header: true,
                            check: "true",
                            uncheck: "false",
                        },
                        hidden: true,
                    },
                    {
                        title: "入金日",
                        dataIndx: "入金日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        editableColumn: true,
                        editable: ui => ui.rowData["CUR入金登録区分"] == "false",
                    },
                    {
                        title: "店番",
                        dataIndx: "店番",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "取引店",
                        dataIndx: "取引店",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "全銀科目コード",
                        dataIndx: "全銀科目コード",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "預金種類コード",
                        dataIndx: "預金種類コード",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "預金種類",
                        dataIndx: "預金種類",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "口座番号",
                        dataIndx: "口座番号",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "口座名義",
                        dataIndx: "口座名義",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "照会期間",
                        dataIndx: "照会期間",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "照会方法",
                        dataIndx: "照会方法",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "指定日",
                        dataIndx: "指定日",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "取引区分",
                        dataIndx: "取引区分",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "依頼人名",
                        dataIndx: "依頼人名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "金融期間名",
                        dataIndx: "金融期間名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "支店名",
                        dataIndx: "支店名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "EDI情報",
                        dataIndx: "EDI情報",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "入金伝票Ｎｏ",
                        dataIndx: "入金伝票Ｎｏ",
                        dataType: "string",
                        hidden: true,
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
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI05071Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            vue.showBushoInfo();
            vue.conditionChanged();
        },
        showBushoInfo: function(){
            var vue = this;
            var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
            axios.post("/DAI05071/GetBushoInfo", { BushoCd: vue.viewModel.BushoCd,timestamp:tc})
                .then(response => {
                    var res = _.cloneDeep(response.data);
                    vue.viewModel.Kouza=res[0].銀行名 + " " + res[0].支店名 + " " + res[0].口座番号 + " " + res[0].種別;
                    if(!vue.params.FileData){
                        vue.viewModel.TargetDate=moment(vue.viewModel.TargetDate).format("YYYY年MM月DD日")
                    }
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
        onCustomerChanged: function() {
            console.log("05071 filter");
            var vue = this;
            var grid = vue.DAI05071Grid1;

            if (!grid) return;
            var rules = [];
            if (vue.filter.CustomerName != "") {
                rules.push({ dataIndx: "得意先名", condition: "contain", value: vue.filter.CustomerName });
                rules.push({ dataIndx: "得意先名カナ", condition: "contain", value: vue.filter.CustomerName, });
                rules.push({ dataIndx: "依頼人名", condition: "contain", value: vue.filter.CustomerName, });
            }
            grid.filter({ oper: "replace", mode: "OR", rules: rules });
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI05071Grid1;

            if (!vue.DAI05071Grid1 || !vue.getLoginInfo().isLogOn) return;
            //if (!vue.viewModel.BushoCd || !vue.viewModel.CustomerCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            //if (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid) return;

            grid.options.colModel.forEach(r =>{
                r.editable=true;
            });
            if(!!vue.params.FileData){
                vue.getFileData(vue.params.FileData);
            }
            else{
                grid.searchData(vue.searchParams);
            }
            grid.options.colModel.forEach(r =>{
                if(r.editableColumn!=true)
                {
                    r.editable=false;
                }
            });
        },
        getFileData: function(FileData)
        {
            var vue = this;
            var grid = vue.DAI05071Grid1;
            grid.clearData();
            FileData.Customers.map((v,i)=>{
                grid.addRow({
                    newRow: {
                        取引日:v.取引日,
                        依頼人名:v.依頼人名,
                        結果:v.結果,
                        得意先ＣＤ:v.得意先ＣＤ,
                        得意先名:v.得意先名,
                        入金金額:v.入金金額,
                        金融機関名:v.金融機関名,
                        支店名:v.支店名,
                        指定日:v.指定日,
                        取引区分:v.取引区分,
                        EDI情報:v.EDI情報,

                        店番:FileData.Company.店番,
                        取引店:FileData.Company.取引店,
                        全銀科目コード:FileData.Company.全銀科目コード,
                        預金種類コード:FileData.Company.預金種類コード,
                        預金種類科目:FileData.Company.預金種類科目,
                        口座番号:FileData.Company.口座番号,
                        口座名義:FileData.Company.口座名義,
                        照会期間:FileData.Company.照会期間,
                        照会方法:FileData.Company.照会方法,
                        操作日:FileData.Company.操作日,
                        操作時刻:FileData.Company.操作時刻,
                    },
                });
            });
        },
        setGridTitle: function (title, grid) {
            return "件数: " + (grid.pdata || []).length;
        },
        save:function()
        {
            var vue=this;
            var grid = vue.DAI05071Grid1;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }

            //登録データの作成
            var FurikomiList=[];
            _.forEach(grid.getData(),r=>{
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
                SaveItem.依頼人登録区分=(r.依頼人登録FLG=="true");
                SaveItem.入金登録区分=(r.入金登録FLG=="true");
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
