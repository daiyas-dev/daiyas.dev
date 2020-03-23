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
            ScreenTitle: "日時処理 > 移動入力",
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
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [],
                colModel: [
                    {
                        title: "日付",
                        dataIndx: "日付",
                        width: 100, maxWidth: 100, minWidth: 100,
                        hidden: false,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "配送担当者ＣＤ",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "担当者名",
                        dataIndx: "担当者名",
                        width: 60, maxWidth: 60, minWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "行番号",
                        dataIndx: "行番号",
                        width: 60, maxWidth: 60, minWidth: 60,
                    },
                    {
                        title: "商品区分ＣＤ",
                        dataIndx: "商品区分ＣＤ",
                        width: 60, maxWidth: 60, minWidth: 60,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品名",
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
                        dataIndx: "引渡数",
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
                            if(ui.rowData.商品区分ＣＤ=="7")
                            {
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
                            if(ui.rowData.商品区分ＣＤ=="7")
                            {
                                var UriageMeisaiData  = vue.UriageMeisaiData.filter(v=>(v.日付==ui.rowData.日付) && (v.コースＣＤ==ui.rowData.コースＣＤ))
                                window.rese=_.cloneDeep(UriageMeisaiData);//TODO
                                var NebikiKingaku=0;
                                    console.log('ここから');
                                _.forEach(UriageMeisaiData,r=>
                                {
                                    console.log(r);
                                    console.log(r["値引金額"]);
                                    if(r["値引金額"] !== undefined)
                                    {
                                        NebikiKingaku+=r["値引金額"] * 1;
                                    }
                                });
                                window.resg=_.cloneDeep(NebikiKingaku);//TODO
                                //alert(UriageMeisaiData[0].値引金額);
                                return { text: NebikiKingaku.toString() };
                            }
                        },
                    },
                    {
                        title: "総売上金額",
                        dataIndx: "総売上金額",
                        width: 200, maxWidth: 200, minWidth: 200,
                        render: ui => {
                            return {text:"<span style=\"width:95px;text-align:left;\">ほえほえ</span><span style=\"width:95px;text-align:right;\">1,000</span>"};
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
                        vue.DAI01200Grid1.print(vue.setPrintOptions);
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
                    RowData.商品名=pv.各種名称;
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
            });

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
                                                        <th style="width: 15%;">${vue.viewModel.TargetDate}</th>
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
    }
}
</script>
