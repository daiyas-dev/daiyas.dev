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
                <label>日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onCourseKbnChanged
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
#DAI01200Grid1 .pq-grid-row:nth-child(even) .pq-grid-cell.pq-merge-cell {
    background: white;
    color: initial;
}
#DAI01200Grid1 .pq-grid-row:nth-child(odd) .pq-grid-cell.pq-merge-cell {
    background: #e6f4ff;
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
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: this.viewModel.CourseCd,
                TantoCd: this.viewModel.TantoCd,
            };
        }
    },
    watch: {
    },
    data() {
        var vue = this;

        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 日配売上集計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                UpdateDate: null,
                test: null,
                testKn: null,
            },
            CheckInterVal: null,
            DAI01200Grid1: null,
            UriageMeisaiData: [],
            NyukinData: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                numberCell: { show: false },
                showTitle: false,
                autoRow: false,
                rowHt: 25,
                rowHtSum: 25,
                editable: false,
                freezeCols: 5,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: false,
                    indent: 10,
                    dataIndx: ["日付", "コース"],
                    showSummary: [false, false],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                    [
                        "コース",
                        function(rowData) {
                            return rowData.コースＣＤ + ":" + rowData.コース名 + "【" + rowData.配送担当者ＣＤ + ":" + rowData.担当者名 + "】";
                        },
                    ]
                ],
                colModel: [
                    {
                        title: "日付",
                        dataIndx: "日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        width: 60, maxWidth: 60, minWidth: 60,
                        render: ui => {
                            if (ui.rowData.pq_level != 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "コース",
                        dataIndx: "コース",
                        dataType: "string",
                        width: 60, maxWidth: 60, minWidth: 60,
                        render: ui => {
                            if (ui.rowData.pq_level != 1 && !ui.Export && ui.rowData.行番号 != 1) {
                                return { text: "" };
                            }
                            if (!!ui.Export && ui.rowData.行番号 != 1) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "行番号",
                        dataIndx: "行番号",
                        hidden: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        hidden: true,
                        hiddenOnExport: false,
                        render: ui => {
                            if (!!ui.Export && ui.rowData.行番号 == 1) {
                                return { text: ui.rowData.コースＣＤ + "<br>" + ui.rowData.コース名 };
                            } else {
                                return { text: "" };
                            }
                        },
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分名",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
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
                    },
                    {
                        title: "売上額",
                        dataIndx: "売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 70, maxWidth: 70, minWidth: 70,
                    },
                    {
                        title: "その他",
                        dataIndx: "その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号=="7"){
                                //TODO:その他の計算処理を記述
                                return { text: "0" };
                            }
                        },
                    },
                    {
                        title: "値引金額",
                        dataIndx: "値引金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号=="7"){
                                if (ui.rowData.summaryRow) {
                                    return ui;
                                }
                                else{
                                    var UriageMeisaiData  = vue.UriageMeisaiData.filter(v=>(v.日付==ui.rowData.日付) && (v.コースＣＤ==ui.rowData.コースＣＤ))
                                    var NebikiKingaku=0;
                                    _.forEach(UriageMeisaiData,r=>{
                                        if(r["値引金額"] !== undefined){
                                            NebikiKingaku+=r["値引金額"] * 1;
                                        }
                                    });
                                    return { text: NebikiKingaku.toString() };
                                }
                            }
                        },
                    },
                    {
                        title: "総売数",
                        dataIndx: "総売数",
                        dataType: "integer",
                        format: "#,###",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                        render: ui => {
                            if(ui.rowData.行番号=="7"){
                                var UriageMeisaiData  = vue.UriageMeisaiData.filter(v=>(v.日付==ui.rowData.日付) && (v.コースＣＤ==ui.rowData.コースＣＤ))
                                var NebikiKingaku=0;
                                _.forEach(UriageMeisaiData,r=>{
                                    if(r["個数"] !== undefined){
                                        NebikiKingaku+=r["個数"] * 1;
                                    }
                                });
                                return { text: NebikiKingaku.toString() };
                            }
                        },
                    },
                    {
                        title: "総売上金額",
                        dataIndx: "総売上金額",
                        hidden: false,
                        width: 155, maxWidth: 155, minWidth: 155,
                        render: ui => {
                            var spanLeft="<span style=\"width:75px;text-align:left;\">";
                            var spanRight="<span style=\"width:75px;text-align:right;\">";
                            var spanClose="</span>";
                            var UriageMeisaiData  = vue.UriageMeisaiData.filter(v=>(v.日付==ui.rowData.日付) && (v.コースＣＤ==ui.rowData.コースＣＤ))

                            //値引金額の取得
                            /*
                            if(ui.rowData.行番号=="6"){
                                //束売の場合は商品区分9の値引きを求める。
                                _.forEach(UriageMeisaiData.filter(v=>(v.商品区分==9)),r=>{
                                    if(r["値引金額"] !== undefined){
                                        NebikiKingaku+=r["値引金額"] * 1;
                                    }
                                });
                            }
                            */

                            if(ui.rowData.行番号=="1"){
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
                                return {text:spanLeft + "現金" + spanClose  + spanRight + sum.toString() + spanClose};
                            }
                            else if(ui.rowData.行番号=="2"){
                                //チケット
                                var sum=0;
                                var nebiki=0;
                                _.forEach(UriageMeisaiData,r=>{
                                    if(1<=r["商品区分"] && r["商品区分"]<=7)
                                    {
                                        nebiki =r["現金値引"] * 1;
                                        nebiki+=r["掛売値引"] * 1;
                                        if(r.売掛現金区分==="2"){
                                            sum+=r["掛売金額"] * 1;
                                            sum-=nebiki;
                                        }
                                    }
                                });
                                return {text:spanLeft + "チケット" + spanClose  + spanRight + sum.toString() + spanClose};
                            }
                            else if(ui.rowData.行番号=="3"){
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
                                return {text:spanLeft + "ＢＶ" + spanClose  + spanRight + sum.toString() + spanClose};
                            }

                            else if(ui.rowData.行番号=="4"){
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
                                return {text:spanLeft + "掛売" + spanClose  + spanRight + sum.toString() + spanClose};
                            }
                            else if(ui.rowData.行番号=="5"){
                                //総売
                                var sum=0;
                                var nebiki=0;
                                var bonus=0;
                                //ボーナス額の計算(行番号8と同等の処理)
                                _.forEach(UriageMeisaiData,r=>{
                                    if(1<=r["商品区分"] && r["商品区分"]<=7)
                                    {
                                        nebiki =r["現金値引"] * 1;
                                        nebiki+=r["掛売値引"] * 1;
                                        if(r.売掛現金区分==="4"){
                                            bonus+=r["掛売金額"] * 1;
                                            bonus-=nebiki;
                                        }
                                    }
                                });
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

                                return {text:spanLeft + "総売" + spanClose  + spanRight + (sum-bonus-nebiki).toString() + spanClose};
                            }
                            else if(ui.rowData.行番号=="6"){
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
                                return {text:spanLeft + "束売り" + spanClose  + spanRight + sum.toString() + spanClose};
                            }
                            else if(ui.rowData.行番号=="8"){
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
                                return {text:spanLeft + "ボーナス" + spanClose  + spanRight + sum.toString() + spanClose};
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
                            else if(ui.rowData.行番号=="2"){
                                return {text:HaisouinNyukin.toString()};
                            }
                            else if(ui.rowData.行番号=="3"){
                                return {text:"事務現金入金　"};
                            }
                            else if(ui.rowData.行番号=="4"){
                                return {text:JimGenkinNyukin.toString()};
                            }
                            else if(ui.rowData.行番号=="5"){
                                return {text:"振込入金　"};
                            }
                            else if(ui.rowData.行番号=="6"){
                                return {text:FurikomiNyukin.toString()};
                            }
                            else if(ui.rowData.行番号=="7"){
                                return {text:"入金合計　"};
                            }
                            else if(ui.rowData.行番号=="8"){
                                //TODO:集計を見直す。現金+VB+束売り+配送員入金+事務現金入金+振込入金
                                return {text:(HaisouinNyukin+JimGenkinNyukin+FurikomiNyukin).toString()};
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
                { visible: "true", value: "明細入力", id: "DAI01200Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI01200Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            // vue.viewModel.TargetDate = moment();
            vue.viewModel.TargetDate = moment("2019/09/04");    //TODO: debug
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //検索条件変更
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")}
            )
                .then(res => {
                    console.log(res);
                    vue.viewModel.CourseKbn = res.data.コース区分;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                })
                .catch(err => {
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "祝日マスタの検索に失敗しました<br/>",
                    });
                });
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback, force) {
            var vue = this;
            var grid = vue.DAI01200Grid1;

            console.log("conditionChanged", vue.viewModel);

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CourseKbn) return;

            grid.showLoading();

            var params = $.extend(true, {}, vue.viewModel);
            //日付を"YYYYMMDD"形式に編集
            params.TargetDate = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            //キャッシュ無効
            params.noCache = true;
/*
            axios.post("/DAI01200/Search", params)
                .then(res => {
                    grid.hideLoading();
                    //移動データ
                    vue.DAI01200Grid1.options.dataModel.data = res.data.CourseMeisaiData;
                    vue.DAI01200Grid1.refreshDataAndView();
                })
                .catch(err => {
                    console.log(err);
                    grid.hideLoading();
                    $.dialogErr({
                        title: "異常終了",
                        contents: "データの検索に失敗しました<br/>",
                    });
                });
                */
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

            window.resa=_.cloneDeep(res[0].SyouhinKubunData);//TODO
            window.resb=_.cloneDeep(vue.UriageMeisaiData);//TODO
            window.resc=_.cloneDeep(UriageSumData);//TODO
            window.resd=_.cloneDeep(vue.NyukinData);//TODO

            //明細行をコピーして、商品区分ごとにデータをセットする
            var CourseMeisaiData=[];
            res[0].CourseMeisaiData.map((v, i) => {
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

                    var UriageSumItem = UriageSumData.find(k => (k.日付==v.日付) && (k.コースＣＤ==v.コースＣＤ) && (k.商品区分 == pv.商品区分));
                    if(!_.isEmpty(UriageSumItem))
                    {
                        RowData.売上数=UriageSumItem.個数;
                        RowData.売上額=UriageSumItem.金額;
                    }
                    CourseMeisaiData.push(RowData);
                });
                //さらにもう1行追加
                var RowData = _.cloneDeep(v);
                RowData.行番号=SyouhinKubunData.length+1;
                CourseMeisaiData.push(RowData);
            });

            //summaryDataの設定
            var row_num=1;
            grid.options.summaryData = [];
             _(SyouhinKubunData).forIn((v, k) => {
                window.rese=_.cloneDeep(k);//TODO

                var summary = {
                    summaryRow: true,
                    "行番号":row_num++,
                    "コース名": grid.options.summaryData.length ? "" : "合計",
                    "商品区分ＣＤ": v["商品区分"],
                    "商品区分名": v["各種名称"],
                    pq_fn:{
                        "持出数": "SUMIF(C:C, '" + row_num + "', F:F)",
                        "受取数_工場": "SUMIF(C:C, '" + row_num + "', G:G)",
                        "受取数_一般": "SUMIF(C:C, '" + row_num + "', H:H)",
                        "引渡数": "SUMIF(C:C, '" + row_num + "', I:I)",
                        "ボーナス": "SUMIF(C:C, '" + row_num + "', J:J)",
                        "残数": "SUMIF(C:C, '" + row_num + "', K:K)",
                        "売上数": "SUMIF(C:C, '" + row_num + "', L:L)",
                        "売上額": "SUMIF(C:C, '" + row_num + "', M:M)",
                        "その他": "SUMIF(C:C, '" + row_num + "', N:N)",
                        "値引金額": "SUMIF(C:C, '" + row_num + "', O:O)",
                        "総売数": "SUMIF(C:C, '" + row_num + "', P:P)",
                        "総売上金額": "SUMIF(C:C, '" + row_num + "', Q:Q)",
                        "入金": "SUMIF(C:C, '" + row_num + "', R:R)",
                    }
                };

                grid.options.summaryData.push(summary);
            });
            //もう1行追加
            var summary = {
                summaryRow: true,
                "行番号":row_num,
                pq_fn:{
                }
            };
            grid.options.summaryData.push(summary);

            //mergeCellsの設定
            var pos = 0;
            _(CourseMeisaiData).groupBy(v => [v.日付,v.コース名])
                .forIn((v, k) => {
                    var cells = {
                        r1: pos,
                        c1: 2,
                        rc: v.length,
                        cc: 1,
                    };
                    grid.options.mergeCells.push(cells);
                    pos += v.length;
                });


            return CourseMeisaiData;
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
                    padding-left: 50px;
                    padding-right: 50px;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    border: solid 1px black;
                    border-radius: 6px;
                    background-color: cyan;
                }
                div.header-table {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    padding-top: 2px;
                    padding-bottom: 2px;
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
            `;

            var headerFunc = (header, idx, length) => {
                var TargetDate = moment(header.pq_level == 0 ? header.日付 : header.parentId).format("YYYY年MM月DD日");
                var GroupInfo = (
                        header.pq_level == 0
                            ? (!!header.children.length ? header.children[0].コース : "")
                            : header.コース
                    ).split(/[:【】]/);
                var CourseCd = GroupInfo[0] || "";
                var CourseNm = GroupInfo[1] || "";
                var TantoCd = GroupInfo[2] || "";
                var TantoNm = GroupInfo[3] || "";
                console.log("1200 headerFunc", header, TargetDate, GroupInfo);
                return `
                    <div class="title">
                        <h3>* * 日配売上集計表 * *</h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 15%;">${vue.viewModel.BushoCd}:${vue.viewModel.BushoNm}</th>
                                <th style="width: 5%;">${CourseCd}</th>
                                <th style="width: 15%;">${TargetDate}</th>
                                <th style="width: 26%;"></th>
                                <th style="width: 10%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 6%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var maxRow = 32;
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01200Grid1.generateHtml(
                                `
                                    table.DAI01200Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01200Grid1 tr th:nth-child(2) {
                                        width: 20%;
                                        white-space: pre-wrap;
                                    }
                                    table.DAI01200Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01200Grid1 tr th:nth-child(-n+1) {
                                        display: none;
                                    }
                                    table.DAI01200Grid1 tr td:nth-child(-n+1) {
                                        display: none;
                                    }
                                    table.header-table th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(4) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(8) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01200Grid1 thead:first-child th {
                                        text-align: left;
                                        padding-left: 5px;
                                    }
                                    table.DAI01200Grid1 {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01200Grid1 tr:not(.group-row):not(.group-summary):not(:nth-child(1))  td:nth-child(n+4){
                                        border-style: dashed;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01200Grid1 tr.group-row + tr td:nth-child(n+4){
                                        border-style: none !important;
                                    }
                                    table.DAI01200Grid1 tr.group-summary  td:nth-child(n+4) {
                                        border-style: double;
                                        border-left-width: 0px;
                                        border-top-width: 3px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01200Grid1 tr.group-summary + tr td{
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                `,
                                headerFunc,
                                maxRow,
                                [false, false],
                                [false, false],
                                [true, false],
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
