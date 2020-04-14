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
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05050Grid1"
            ref="DAI05050Grid1"
            dataUrl="/DAI05050/Search"
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
#DAI05050Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05050Grid1 .pq-group-icon {
    display: none !important;
}
#DAI05050Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI05050Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI05050Grid1 .pq-td-div span {
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
    name: "DAI05050",
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
            ScreenTitle: "随時処理 > 得意先別単価表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                CourseCd: null,
                CustomerCd: null,
            },
            DAI05050Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: true,
                rowHt: 35,
                freezeCols: 10,
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
                    grandSummary: false,
                    indent: 20,
                    dataIndx: ["部署","コース"],
                    showSummary: [false,false],
                    collapsed: [false,false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "部署",
                        dataIndx: "部署", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "コース",
                        dataIndx: "コース", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "担当者名",
                        dataIndx: "担当者名", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 180, minWidth: 180,
                        tooltip: true,
                        fixed: true,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05050Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05050Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
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
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printHeader =
                `
                    <style>
                        .header-table {

                        }
                        .header-table th {
                            font-family: "MS UI Gothic";
                            font-size: 10pt;
                            font-weight: normal !important;
                            border: solid 1px black !important;
                            white-space: nowrap;
                            overflow: hidden;
                            margin: 0px;
                            padding-left: 3px;
                            padding-right: 3px;
                        }
                        .header-table tr:last-child th{
                            border-bottom-width: 0px !important;
                        }
                    </style>
                    <h3 style="text-align: center; margin: 0px; margin-bottom: 10px;">* * 持ち出し数一覧表 * *</h3>
                    <table style="border-collapse: collapse; width: 100%;" class="header-table">
                        <colgroup>
                                <col style="width:4.58%;"></col>
                                <col style="width:4.60%;"></col>
                                <col style="width:9.00%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th colspan="5">${moment().format("YYYY年MM月DD日 dddd")}</th>
                            </tr>
                            <tr>
                                <th>部署</th>
                                <th>${vue.viewModel.BushoCd}</th>
                                <th colspan="4">${vue.viewModel.BushoNm}</th>
                                <th colspan="6" style="border-top-width: 0px !important;"></th>
                                <th colspan="2">作成日</th>
                                <th colspan="2">${moment().format("YYYY/MM/DD")}</th>
                                <th>PAGE</th>
                                <th>1</th>
                            </tr>
                        </thead>
                    </table>
                `;
            grid.options.printStyles =
                `
                    tr td:nth-child(1) {
                        font-size: 9pt;
                    }
                    tr td:nth-child(n+2) {
                        text-align: right;
                    }
                `;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        refreshCols: function() {
            console.log("refreshCols");//TODO:
            var vue = this;
            var newCols = grid.options.colModel.filter(v => !!v.fixed);
            var i=1;
            [...Array(5)].map(r=>{
                newCols.push(
                    {
                        title: "商品",
                        dataIndx: "商品_" + i,
                        dataType: "string",
                        width: 140, maxWidth: 140, minWidth: 140,
                    }
                );
                newCols.push(
                    {
                        title: "単価",
                        dataIndx: "単価_" + i,
                        dataType: "integer",
                        format: "#,##0",
                        width: 70, maxWidth: 70, minWidth: 70,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    }
                );
                i++;
            });

            //列定義更新
            grid.options.colModel = newCols;
            grid.refreshCM();
            grid.refresh();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05050Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            }

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //コースはフィルタするので除外
            delete params.CourseCd;
            delete params.CustomerCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05050Grid1;

            if (!grid) return;

            var rules = [];
            if (vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                var crules_Course = [];
                crules_Course.push({ condition: "equal", value: vue.viewModel.CourseCd});
                if (crules_Course.length) {
                    rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules_Course });
                }
            }
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                var crules_Customer = [];
                crules_Customer.push({ condition: "equal", value: vue.viewModel.CustomerCd});
                if (crules_Customer.length) {
                    rules.push({ dataIndx: "得意先ＣＤ", mode: "AND", crules: crules_Customer });
                }
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            var groupings = res[0].CourseData.map(r => {
                    var priceData = res[0].PriceData.filter(k => k.部署ＣＤ==r.部署ＣＤ && k.コースＣＤ==r.コースＣＤ && k.得意先ＣＤ==r.得意先ＣＤ);
                    _.chunk(priceData,5)
                        .map(pr => {
                            var i=1;
                            pr.map(cr => {
                                r["商品_" + i] = r["商品_" + i]==undefined ? cr.表示商品名 : r["商品_" + i] + "\n" + cr.表示商品名;
                                r["単価_" + i ]= r["単価_" + i]==undefined ? cr.単価:r["単価_" + i ] + "\n" + cr.単価;
                                i++;
                            });
                        });
                    r.部署=r.部署ＣＤ + " " + r.部署名;
                    r.コース=r.コースＣＤ + " " + r.コース名;
                    return r;
                });
            return groupings;
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
                    font-size: 8pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 12px;
                    text-align: center;
                }
                td {
                    height: 12px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 1px black;
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
                    border: 2px solid #000000;
                    border-radius: 5px;
                }
            `;
            var headerFunc = (header, idx, length) => {
                var BushoCd = "";
                var BushoNm = "";
                var CourseCd="";
                var CourseNm="";
                var TantoCd="";
                var TantoNm="";
                if(header.pq_level==0)
                {
                    BushoCd = header.部署.split(" ")[0];
                    BushoNm = header.部署.split(" ")[1];
                    CourseCd= header.children[0].コース.split(" ")[0];
                    CourseNm= header.children[0].コース.split(" ")[1];
                }else{
                    BushoCd = header.parentId.split(" ")[0];
                    BushoNm = header.parentId.split(" ")[1];
                    CourseCd= header.コース.split(" ")[0];
                    CourseNm= header.コース.split(" ")[1];
                }
                TantoCd = vue.DAI05050Grid1.pdata.find(v => v.部署ＣＤ==BushoCd && v.コースＣＤ==CourseCd).担当者ＣＤ;
                TantoNm = vue.DAI05050Grid1.pdata.find(v => v.部署ＣＤ==BushoCd && v.コースＣＤ==CourseCd).担当者名;
                return `
                    <div class="title">
                        <h3><div class="report-title-area">＊＊＊　得意先単価表　＊＊＊
                    <div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width:  5%;">部署</th>
                                <th style="width:  5%; text-align: right;">${BushoCd}</th>
                                <th style="width: 18%;">${BushoNm}</th>
                                <th colspan="8" class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th style="width:  5%;">コース</th>
                                <th style="width:  5%; text-align: right;">${CourseCd}</th>
                                <th style="width: 18%;">${CourseNm}</th>
                                <th style="width:  5%;">担当者</th>
                                <th style="width:  5%; text-align: right;">${TantoCd}</th>
                                <th style="width: 18%;">${TantoNm}</th>
                                <th style="width:  5%;" class="blank-cell"></th>
                                <th>作成日</th>
                                <th style="text-align: right;">${moment().format("YYYY年MM月DD日")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right;">${idx + 1}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI05050Grid1
                table.DAI05050Grid1 tr,
                table.DAI05050Grid1 th,
                table.DAI05050Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI05050Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                29,
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
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
