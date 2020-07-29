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
            <div class="col-md-1">
                <label>配送日付</label>
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
                    :onChangedFunc=onDateChanged
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
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseCd"
                    ref="PopupSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :isPreload=true
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01200Grid1"
            ref="DAI01200Grid1"
            dataUrl="/DAI01200/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :SearchOnCreate=false
            :SearchOnActivate=false
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
/* 合計行 */
#DAI01200Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
#DAI01200Grid1 .pq-grid-row .pq-grid-cell.pq-merge-cell {
    background: white;
    color: initial;
}

label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01200",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                DateStart: moment(this.viewModel.DateStart, "YYYY年MM月DD日").format("YYYYMMDD"),
                DateEnd: moment(this.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
            };
        }
    },
    watch: {
        "DAI01200Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI01200Grid1_Printout").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;

        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 日配売上集計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DateStart: null,
                DateEnd: null,
                CourseCd: null,
            },
            CheckInterVal: null,
            DAI01200Grid1: null,
            UriageMeisaiData: [],
            NyukinData: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showTitle: false,
                numberCell: { show: false },
                autoRow: false,
                rowHt: 25,
                rowHtSum: 25,
                freezeCols: 8,
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
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: false,
                    indent: 20,
                    dataIndx: ["日付"],
                    showSummary: [false],
                    collapsed: [false],
                    summaryInTitleRow: "ExpandAll",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [],
                colModel: [
                    {
                        title: "日付",
                        dataIndx: "日付",
                        hidden: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        render: ui => {
                            if(ui.rowData.pq_gtitle && ui.rowData.pq_level == 0)
                                return ui.rowData.日付 ? moment(ui.rowData.日付, "YYYY年MM月DD日").format("YYYY年MM月DD日") : null;
                            if (!ui.rowData.summaryRow) {
                                return ui.rowData.コースＣＤ +"<br/>"+ ui.rowData.コース名;
                            }
                        },
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ",
                        hidden: true,
                    },
                    {
                        title: "担当者",
                        dataIndx: "担当者名",
                        width: 120, maxWidth: 120, minWidth: 120,
                        hidden: false,
                        render: ui => {
                            if (!ui.rowData.pq_gtitle && ui.rowData.pq_level!=0 && !ui.rowData.summaryRow) {
                                return ui.rowData.担当者ＣＤ +"<br/>"+ ui.rowData.担当者名;
                            }
                        },
                    },
                    {
                        title: "行番号",
                        dataIndx: "行番号",
                        hidden: true,
                    },
                    {
                        title: "商品区分ＣＤ",
                        dataIndx: "商品区分ＣＤ",
                        hidden: true,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分名",
                        dataType: "string",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "持出数",
                        dataIndx: "持出数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "工場",
                        dataIndx: "受取数_工場",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "もらった",
                        dataIndx: "受取数_一般",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "やった",
                        dataIndx: "引渡数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "ボーナス",
                        dataIndx: "ボーナス",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "残数",
                        dataIndx: "残数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "売上数",
                        dataIndx: "売上数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                        render: ui => {
                        },
                    },
                    {
                        title: "売上額",
                        dataIndx: "売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 90, maxWidth: 90, minWidth: 90,
                    },
                    {
                        title: "その他",
                        dataIndx: "その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号!="7"){
                                return { text: "" };
                            }
                        },
                    },
                    {
                        title: "値引金額",
                        dataIndx: "値引金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号!="7"){
                                return { text: "" };
                            }
                        },
                    },
                    {
                        title: "総売数",
                        dataIndx: "総売数",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号!="7"){
                                return { text: "" };
                            }
                        },
                    },
                    {
                        title: "総売上金額",
                        dataIndx: "総売上金額",
                        hidden: false,
                        width: 140, maxWidth: 140, minWidth: 140,
                        render: ui => {
                            var spanLeft="<span style=\"width:75px;text-align:left;\">";
                            var spanRight="<span style=\"width:75px;text-align:right;\">";
                            var spanClose="</span>";
                            if(ui.rowData.行番号=="1"){
                                return {text:spanLeft + "現金" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="2"){
                                return {text:spanLeft + "チケット" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="3"){
                                return {text:spanLeft + "ＢＶ" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="4"){
                                return {text:spanLeft + "掛売" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="5"){
                                return {text:spanLeft + "総売" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="6"){
                                return {text:spanLeft + "束売り" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                            else if(ui.rowData.行番号=="7"){
                                return "";
                            }
                            else if(ui.rowData.行番号=="8"){
                                return {text:spanLeft + "ボーナス" + spanClose  + spanRight + pq.formatNumber(ui.rowData.総売上金額,"#,###") + spanClose};
                            }
                           else{
                               return ui;
                           }
                        },
                    },
                    {
                        title: "入金",
                        dataIndx: "入金",
                        dataType: "integer",
                        format: "#,###",
                        width: 130, maxWidth: 130, minWidth: 130,
                        hidden: false,
                        render: ui => {
                            var HaisouinNyukin=0;
                            var JimGenkinNyukin=0;
                            var FurikomiNyukin=0;
                            _.forEach(vue.NyukinData.filter(v=>(v.入金日付==ui.rowData.日付)),r=>{
                                if(r["入金区分"]==0){
                                    JimGenkinNyukin += r["現金"]!==undefined ? r["現金"]*1 : 0;
                                    JimGenkinNyukin += r["小切手"]!==undefined ? r["小切手"]*1 : 0;
                                    JimGenkinNyukin += r["バークレー"]!==undefined ? r["バークレー"]*1 : 0;
                                    JimGenkinNyukin += r["その他"]!==undefined ? r["その他"]*1 : 0;
                                    JimGenkinNyukin += r["値引"]!==undefined ? r["値引"]*1 : 0;;

                                    FurikomiNyukin += r["振込"]!==undefined ? r["振込"]*1 : 0;
                                    FurikomiNyukin += r["相殺"]!==undefined ? r["相殺"]*1 : 0;;
                                }
                                else{
                                    HaisouinNyukin += r["現金"]!==undefined ? r["現金"]*1 : 0;
                                    HaisouinNyukin += r["小切手"]!==undefined ? r["小切手"]*1 : 0;
                                    HaisouinNyukin += r["振込"]!==undefined ? r["振込"]*1 : 0;
                                    HaisouinNyukin += r["バークレー"]!==undefined ? r["バークレー"]*1 : 0;
                                    HaisouinNyukin += r["その他"]!==undefined ? r["その他"]*1 : 0;
                                    HaisouinNyukin += r["相殺"]!==undefined ? r["相殺"]*1 : 0;
                                    HaisouinNyukin += r["値引"]!==undefined ? r["値引"]*1 : 0;
                                }
                            });

                            if(ui.rowData.行番号=="1"){
                                return {text:"配送員入金　"};
                            }
                            else if(ui.rowData.行番号=="3"){
                                return {text:"事務現金入金　"};
                            }
                            else if(ui.rowData.行番号=="5"){
                                return {text:"振込入金　"};
                            }
                            else if(ui.rowData.行番号=="7"){
                                return {text:"入金合計　"};
                            }
                            else{
                                return ui;
                            }
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01200Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(null, true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI01200Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
        },
        onDateChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI01200Grid1;

            console.log("conditionChanged", vue.viewModel);

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;
            grid.showLoading();

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            //キャッシュ無効
            params.noCache = true;
            grid.searchData(params, false, null, callback);
            grid.hideLoading();
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;
            var SyouhinKubunData = _.cloneDeep(res[0].SyouhinKubunData);
            vue.UriageMeisaiData = _.cloneDeep(res[0].UriageMeisaiData);
            vue.NyukinData = _.cloneDeep(res[0].NyukinData);

            //商品区分ごとの合計値を集計
            var UriageSumData = _(vue.UriageMeisaiData)
                .groupBy(v => [v.日付,v.コースＣＤ,v.商品区分])
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(r,(acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                acc["個数"] = (acc["個数"] || 0) + v.現金個数 * 1;
                                acc["個数"] = (acc["個数"] || 0) + v.掛売個数 * 1;
                                acc["個数"] = (acc["個数"] || 0) + v.分配元数量 * 1;
                                acc["金額"] = (acc["金額"] || 0) + v.現金金額 * 1;
                                acc["金額"] = (acc["金額"] || 0) + v.掛売金額 * 1;
                                acc["値引金額"] = (acc["値引金額"] || 0) + v.現金値引 * 1;
                                acc["値引金額"] = (acc["値引金額"] || 0) + v.掛売値引 * 1;
                                return acc;
                            },
                            {}
                    );
                    return ret;
                })

            var CourseMeisaiData=[];
            res[0].CourseMeisaiData.map((v, i) => {
                //日付・コース単位の売上明細データを取得
                var UriageMeisaiData  = vue.UriageMeisaiData.filter(fv=>(fv.日付==v.日付) && (fv.コースＣＤ==v.コースＣＤ));

                //集計用変数
                var bonus=0;
                var genkin=0;
                var bv=0;
                var tabauri=0;

                //入金データの集計
                var HaisouinNyukin=0;
                var JimGenkinNyukin=0;
                var FurikomiNyukin=0;
                _.forEach(vue.NyukinData.filter(fv=>(fv.入金日付==v.日付) && (fv.コースＣＤ==v.コースＣＤ)),r=>{
                    if(r["入金区分"]==0){
                        JimGenkinNyukin += r["現金"]!==undefined ? r["現金"]*1 : 0;
                        JimGenkinNyukin += r["小切手"]!==undefined ? r["小切手"]*1 : 0;
                        JimGenkinNyukin += r["バークレー"]!==undefined ? r["バークレー"]*1 : 0;
                        JimGenkinNyukin += r["その他"]!==undefined ? r["その他"]*1 : 0;
                        JimGenkinNyukin += r["値引"]!==undefined ? r["値引"]*1 : 0;;

                        FurikomiNyukin += r["振込"]!==undefined ? r["振込"]*1 : 0;
                        FurikomiNyukin += r["相殺"]!==undefined ? r["相殺"]*1 : 0;;
                    }
                    else{
                        HaisouinNyukin += r["現金"]!==undefined ? r["現金"]*1 : 0;
                        HaisouinNyukin += r["小切手"]!==undefined ? r["小切手"]*1 : 0;
                        HaisouinNyukin += r["振込"]!==undefined ? r["振込"]*1 : 0;
                        HaisouinNyukin += r["バークレー"]!==undefined ? r["バークレー"]*1 : 0;
                        HaisouinNyukin += r["その他"]!==undefined ? r["その他"]*1 : 0;
                        HaisouinNyukin += r["相殺"]!==undefined ? r["相殺"]*1 : 0;
                        HaisouinNyukin += r["値引"]!==undefined ? r["値引"]*1 : 0;
                    }
                });

                //明細行をコピーして、商品区分ごとにデータをセットする
                SyouhinKubunData.map((pv,pi) => {
                    var StrIndex = pv.商品区分 =="1" ? "１"
                                 : pv.商品区分 =="2" ? "２"
                                 : pv.商品区分 =="3" ? "３"
                                 : pv.商品区分 =="4" ? "４"
                                 : pv.商品区分 =="5" ? "５"
                                 : pv.商品区分 =="6" ? "６"
                                 : pv.商品区分 =="7" ? "７"
                                 : "" ;
                    var RowData = _.cloneDeep(v);
                    RowData.行番号=pi+1;
                    RowData.商品区分ＣＤ=pv.商品区分;
                    RowData.商品区分名=pv.各種名称;
                    RowData.持出数=RowData["持出し個数" + StrIndex];
                    RowData.受取数_工場=RowData["工場追加" + StrIndex];
                    RowData.受取数_一般=RowData["もらった" + StrIndex];
                    RowData.引渡数=RowData["やった" + StrIndex];
                    RowData.ボーナス=RowData["見本" + StrIndex];
                    RowData.残数=RowData["残数" + StrIndex];

                    //売上数、売上額の追加
                    var UriageSumItem = UriageSumData.find(k => (k.日付==v.日付) && (k.コースＣＤ==v.コースＣＤ) && (k.商品区分 == pv.商品区分));
                    if(!_.isEmpty(UriageSumItem))
                    {
                        RowData.売上数=UriageSumItem.個数;
                        RowData.売上額=UriageSumItem.金額;
                    }
                    else
                    {
                        RowData.売上数=0;
                        RowData.売上額=0;
                    }

                    if(RowData.行番号==7)
                    {
                        //その他の計算
                        var sum=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(r["商品区分"]==8){
                                sum+=r["現金金額"] * 1;
                                sum+=r["掛売金額"] * 1;
                            }
                        });
                        RowData.その他=sum;

                        //値引金額の計算
                        var NebikiKingaku=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(r["値引金額"] !== undefined){
                                NebikiKingaku+=r["値引金額"] * 1;
                            }
                        });
                        RowData.値引金額=NebikiKingaku;

                        //総売数の計算
                        var UriageKosu=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7){
                                UriageKosu+=r["現金個数"] * 1;
                                UriageKosu+=r["掛売個数"] * 1;
                                UriageKosu+=r["分配元数量"] * 1;
                            }
                        });
                        RowData.総売数=UriageKosu;
                    }

                    if(RowData.行番号==1){
                        //現金
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7){
                                nebiki =r["現金値引"] * 1;
                                nebiki+=r["掛売値引"] * 1;
                            }
                            if(1<=r["商品区分"] && r["商品区分"]<=8){
                                if(r.売掛現金区分==="0"){
                                    sum+=r["現金金額"] * 1;
                                    sum-=nebiki;
                                }
                            }
                        });
                        RowData.総売上金額=sum;
                        genkin=sum;
                    }
                    if(RowData.行番号==2){
                        //チケット
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7){
                                nebiki =r["現金値引"] * 1;
                                nebiki+=r["掛売値引"] * 1;
                                if(r.売掛現金区分==="2"){
                                    sum+=r["掛売金額"] * 1;
                                    sum-=nebiki;
                                }
                            }
                        });
                        RowData.総売上金額=sum;
                    }
                    if(RowData.行番号==3){
                        //ＢＶ
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7)
                            {
                                nebiki =r["現金値引"] * 1;
                                nebiki+=r["掛売値引"] * 1;
                                if(r.売掛現金区分==="3"){
                                    sum+=r["掛売金額"] * 1;
                                    sum-=nebiki;
                                }
                            }
                        });
                        RowData.総売上金額=sum;
                        bv=sum;
                    }
                    if(RowData.行番号==4){
                        //掛売
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7){
                                nebiki =r["現金値引"] * 1;
                                nebiki+=r["掛売値引"] * 1;
                            }
                            if(1<=r["商品区分"] && r["商品区分"]<=8){
                                if(r.売掛現金区分==="1"){
                                    sum+=r["掛売金額"] * 1;
                                    sum-=nebiki;
                                }
                            }
                        });
                        RowData.総売上金額=sum;
                    }
                    if(RowData.行番号==5){
                        //ボーナス
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=7)
                            {
                                nebiki =r["現金値引"] * 1;
                                nebiki+=r["掛売値引"] * 1;
                                if(r.売掛現金区分==="4"){
                                    sum+=r["掛売金額"] * 1;
                                    sum-=nebiki;
                                }
                            }
                        });
                        RowData.総売上金額=sum;
                        bonus=sum;
                    }
                    if(RowData.行番号==5){
                        //総売
                        var sum=0;
                        var nebiki=0;
                        //値引金額の取得
                        _.forEach(UriageMeisaiData,r=>{
                            if(r["値引金額"]!==undefined){
                                nebiki+=r["値引金額"] * 1;
                            }
                        });

                        _.forEach(UriageMeisaiData,r=>{
                            if(1<=r["商品区分"] && r["商品区分"]<=8)
                            {
                                sum+=r["現金金額"] * 1;
                                sum+=r["掛売金額"] * 1;
                            }
                        });
                        RowData.総売上金額=sum-bonus;
                    }
                    if(RowData.行番号==6){
                        //束売
                        var sum=0;
                        var nebiki=0;
                        _.forEach(UriageMeisaiData,r=>{
                            if(r["商品区分"]==="9")
                            {
                                sum+=(r["現金金額"] * 1) - (r["現金値引"] * 1);
                                sum+=(r["掛売金額"] * 1) - (r["掛売値引"] * 1);
                            }
                        });
                        RowData.総売上金額=sum;
                        tabauri=sum;
                    }

                    //入金の作成
                    if(RowData.行番号==2){
                        RowData.入金=HaisouinNyukin;
                    }
                    if(RowData.行番号==4){
                        RowData.入金=JimGenkinNyukin;
                    }
                    if(RowData.行番号==6){
                        RowData.入金=FurikomiNyukin;
                    }
                    CourseMeisaiData.push(RowData);
                });
                //さらにもう1行追加
                var RowData = _.cloneDeep(v);
                RowData.行番号=SyouhinKubunData.length+1;
                RowData.商品区分ＣＤ=SyouhinKubunData.length+1;
                RowData.総売上金額=bonus;
                RowData.入金=genkin + tabauri + HaisouinNyukin + JimGenkinNyukin + FurikomiNyukin + bv;
                CourseMeisaiData.push(RowData);
            });

            //summaryDataの設定
            var row_num=1;
            grid.options.summaryData = [];
             _(SyouhinKubunData).forIn((v, k) => {
                    var summary = {
                        summaryRow: true,
                        "行番号":row_num++,
                        "コース名": grid.options.summaryData.length ? "" : "合計",
                        "商品区分ＣＤ": v["商品区分"],
                        "商品区分名": v["各種名称"],
                        pq_fn:{
                            "持出数": "SUMIF(G:G, '" + v["商品区分"] + "', I:I)",
                            "受取数_工場": "SUMIF(G:G, '" + v["商品区分"] + "', J:J)",
                            "受取数_一般": "SUMIF(G:G, '" + v["商品区分"] + "', K:K)",
                            "引渡数": "SUMIF(G:G, '" + v["商品区分"] + "', L:L)",
                            "ボーナス": "SUMIF(G:G, '" + v["商品区分"] + "', M:M)",
                            "残数": "SUMIF(G:G, '" + v["商品区分"] + "', N:N)",
                            "売上数": "SUMIF(G:G, '" + v["商品区分"] + "', O:O)",
                            "売上額": "SUMIF(G:G, '" + v["商品区分"] + "', P:P)",
                            "その他": "SUMIF(G:G, '" + v["商品区分"] + "', Q:Q)",
                            "値引金額": "SUMIF(G:G, '" + v["商品区分"] + "', R:R)",
                            "総売数": "SUMIF(G:G, '" + v["商品区分"] + "', S:S)",
                            "総売上金額": "SUMIF(G:G, '" + v["商品区分"] + "', T:T)",
                            "入金": "SUMIF(G:G, '" + v["商品区分"] + "', U:U)",
                        }
                    };
                    grid.options.summaryData.push(summary);
                });
            //もう1行追加
            var summary = {
                summaryRow: true,
                "行番号":row_num,
                "商品区分ＣＤ": row_num,
                pq_fn:{
                    "総売上金額": "SUMIF(G:G, '" + row_num + "', "+ grid.getExcelColumnName("総売上金額","${nm}:${nm}") +")",
                    "入金": "SUMIF(G:G, '" + row_num + "', "+ grid.getExcelColumnName("入金","${nm}:${nm}") +")",
                }
            };
            grid.options.summaryData.push(summary);

            //mergeCellsの設定
            grid.options.mergeCells=[];
            var pos = 1;
            var idx = 0;
            var g = _.groupBy(_.filter(CourseMeisaiData,f=>f.行番号==1), v => v.日付);
            g=_.sortBy(g, ["日付"]);
            _.forEach(g,k=>{
                _.forEach(k,k2=>{
                    var cellsCourse = {
                        r1: pos+idx,
                        c1: 2,
                        rc: 8,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cellsCourse);
                    var cellsTanto = {
                        r1: pos+idx,
                        c1: 4,
                        rc: 8,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cellsTanto);
                    pos += 8;
                });
                idx++;
            });
            return CourseMeisaiData;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            return list;
        },
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printOptions.printType = "raw-html";
            grid.options.printOptions.printStyles = "@media print { @page { size: A4 landscape; } }";

            var table = $($(grid.exportData({ format: "htm", render: true }))[3]);
            var tableHeaders = table.find("tr").filter((i, v) => !!$(v).find("th").length);
            var tableBodies = table.find("tr").filter((i, v) => !!$(v).find("td").length);

            //optional: multiple summary rows
            var courseNm;
            tableBodies.map((i, v) => {
                var row = $(v);
                courseNm = row.find("td[rowspan]").text() || row.find("td").filter((idx, e) => $(e).text().includes("合計")).text() || courseNm;
                if (row.find("td").length != tableHeaders.find("th").length) {
                    row.prepend($("<td>").text(courseNm).hide());
                }
                if (!row.find("td:first").text()) {
                    row.find("td:first").text(courseNm).hide();
                }
                return row.get(0);
            });

            //optional: generate contents for multipages
            var contents = [];
            var maxRowsPerPage = 45;
            _(tableBodies)
                .groupBy(v => $(v).find("td:first").text())
                .values()
                .reduce((a, v, i, o) => {
                    if (!$(v[0]).find("td:first").attr("rowspan")) {
                        $(v[0]).find("td:first").attr("rowspan", v.length).css("border-bottom-width", "1px");
                    }

                    if (!_.isEmpty(a) && a.find(".data-table tr").length + v.length > maxRowsPerPage) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                        a = {};
                    }

                    if (_.isEmpty(a)) {
                        var pageHeader = `
                                            <div class="title">
                                                <h3>* * * 移動表 * * *</h3>
                                            </div>
                                            <table class="header-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">日付</th>
                                                        <th style="width: 15%;">${vue.viewModel.DateStart}</th>
                                                        <th style="width: 52%; border-top-width: 0px !important;"> </th>
                                                        <th style="width: 16%;">${moment().format("YYYY年MM月DD日 HH:mm:ss")}</th>
                                                        <th style="width: 6%;">PAGE</th>
                                                        <th style="width: 6%;">${contents.length + 1}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        `;

                        a = $("<div>").css("page-break-before", "always")
                            .append(pageHeader)
                            .append($("<table>").addClass("data-table").append(tableHeaders.prop("outerHTML")))
                            .append("<br/>")
                            ;
                    }

                    v.forEach(r => {
                        if (v.indexOf(r) == 0) {
                            $(r).find("td[colspan]").css("border-bottom-width", "1px");
                        }
                        if (v.indexOf(r) == v.length - 1) {
                            $(r).find("td").css("border-bottom-width", "1px");
                        }
                    })
                    a.find(".data-table").append(v);

                    if (_.last(o) == v) {
                        var page = _.cloneDeep(a);
                        page.find("tr:last td").css("border-bottom-width", "1px");
                        contents.push(page);
                    }

                    return a;
                }, {});

            var styles =  `
                                <style>
                                    .grid-contents .title {
                                        width: 100%;
                                        display: inline-flex;
                                        justify-content: center;
                                        margin-bottom: 10px;
                                    }
                                    .grid-contents .title h3 {
                                        text-align: center;
                                        border: solid 1px black;
                                        border-radius: 4px;
                                        background-color: grey;
                                        margin: 0px;
                                        padding-left: 30px;
                                        padding-right: 30px;
                                    }
                                    .grid-contents table {
                                        width: 100%;
                                        border-collapse:collapse;
                                    }
                                    .grid-contents .header-table tr th {
                                        border-bottom: 0px;
                                    }
                                    .grid-contents tr th,
                                    .grid-contents tr td
                                    {
                                        height: 12px !important;
                                        font-family: "MS UI Gothic" !important;
                                        font-size: 9pt !important;
                                        font-weight: normal !important;
                                        line-height: normal !important;
                                        border: solid 1px black;
                                        margin: 0px;
                                        padding: 0px;
                                        padding-top: 1px;
                                        padding-bottom: 1px;
                                        padding-left: 3px;
                                        padding-right: 3px;
                                    }
                                    .grid-contents tr td {
                                        border-top-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    .grid-contents tr th:nth-child(1) {
                                        width: 14%;
                                    }
                                    .grid-contents tr th:nth-child(2) {
                                        width: 6%;
                                    }
                                    .grid-contents tr th:nth-child(7),
                                    .grid-contents tr th:nth-child(9)
                                    {
                                        width: 22%;
                                    }
                                    .grid-contents tr th:nth-child(n+3):nth-child(-n+6),
                                    .grid-contents tr th:nth-child(8),
                                    .grid-contents tr th:nth-child(10)
                                    {
                                        width: 6%;
                                        text-align: center;
                                    }
                                    .grid-contents tr td:nth-child(1) {
                                        vertical-align: top;
                                    }
                                </style>
                            `;

            var printable = $("<html>")
                .append($("<head>").append(styles))
                .append($("<body>").append($("<div>").addClass("grid-contents").append(contents)));

            grid.options.printOptions.printable = printable.prop("outerHTML");

            console.log(grid.options.printable);
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
                    margin-bottom: 20px;
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
                    height: 13.5px;
                    text-align: center;
                }
                td {
                    height: 13.5px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table th:nth-child(2),
                table.header-table th:nth-child(7) {
                    text-align: left;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
                div.report-title-area{
                    width:400px;
                    height:35px;
                    text-align: center;
                    display:table-cell;
                    vertical-align: middle;
                    background-color: #c0ffff;
                    border: 1px solid #000000;
                    border-radius: 5px;
                }
            `;
            var headerFunc = (header, idx, length) => {
                return `
                    <div class="title">
                        <h3><div class="report-title-area">売上集計表<div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width:  5%;">日付</th>
                                <th style="width: 12%; text-align: center;">${moment(header.日付, "YYYY年MM月DD日").format("YYYY年MM月DD日")}</th>
                                <th style="width: 55%;" class="blank-cell"></th>
                                <th style="width:  5%;">作成日</th>
                                <th style="width: 11%; text-align: right;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width:  5%;">PAGE</th>
                                <th style="width:  5%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI01200Grid1
                table.DAI01200Grid1 tr,
                table.DAI01200Grid1 th,
                table.DAI01200Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.header-table tr th{
                    font-size: 10pt;
                }
                table.header-table tr,
                table.header-table tr {
                    height: 18px;
                }
                table.DAI01200Grid1 thead tr {
                    height: 20px;
                }
                table.DAI01200Grid1 thead tr th:nth-child(1),
                table.DAI01200Grid1 thead tr th:nth-child(15) {
                    width: 12%;
                }
                table.DAI01200Grid1 thead tr th:nth-child(2) {
                    width: 7.5%;
                }
                table.DAI01200Grid1 thead tr th:nth-child(3) {
                    width: 5.6%;
                }
                table.DAI01200Grid1 thead tr th:nth-child(11) {
                    width: 5.5%;
                }
                table.DAI01200Grid1 thead tr th:nth-child(12),
                table.DAI01200Grid1 thead tr th:nth-child(13) {
                    width: 6.2%;
                }
                table.DAI01200Grid1 thead tr th:nth-child(16) {
                    width: 8%;
                }
                table.DAI01200Grid1 thead tr th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01200Grid1 thead tr th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01200Grid1 td:not([colspan]) {
                    border-collapse: collapse;
                    border: none;
                }
                table.DAI01200Grid1 tr:nth-child(8n+1) td:nth-child(1) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    vertical-align: top;
                    white-space: pre-wrap;
                }
                table.DAI01200Grid1 tr:nth-child(8n+1) td:nth-child(2) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    vertical-align: top;
                    white-space: pre-wrap;
                }
                table.DAI01200Grid1 tr:nth-child(8n+1) td:nth-child(n+3) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01200Grid1 tr:not(:nth-child(8n+1)) td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI01200Grid1 tr:nth-child(8n) td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01200Grid1 tr:nth-child(8n+1) td:last-child,
                table.DAI01200Grid1 tr:not(:nth-child(8n+1)) td:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.DAI01200Grid1 tr:nth-child(8n) td:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01200Grid1 tbody tr td:nth-last-child(2) span {
                    width: auto !important;
                }
                table.DAI01200Grid1 tbody tr td:nth-last-child(2) span:nth-child(2) {
                    float: right;
                }
                table.DAI01200Grid1 tr.grand-summary:nth-child(8n+1) td:nth-child(-n+2) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    vertical-align: top;
                }
                table.DAI01200Grid1 tr.grand-summary:nth-child(8n+1) td:nth-child(1):before {
                    content: " 【 ";
                }
                table.DAI01200Grid1 tr.grand-summary:nth-child(8n+1) td:nth-child(1):after {
                    content: " 】";
                }
                table.DAI01200Grid1 tr.grand-summary:nth-child(8n+1) td:nth-child(1) {
                    letter-spacing: 0.5em;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01200Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                40,
                                false,
                                false,
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
