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
                    :params="{ BushoCd: viewModel.BushoCd, CourseCd:null, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
            id="DAI05080Grid1"
            ref="DAI05080Grid1"
            dataUrl="/DAI05080/Search"
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
#DAI05080Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05080Grid1 .pq-group-icon {
    display: none !important;
}
#DAI05080Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI05080Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI05080Grid1 .pq-td-div span {
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
    name: "DAI05080",
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
            ScreenTitle: "随時処理 > FAX注文書発行",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                CustomerCd: null,
            },
            DAI05080Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: true,
                rowHt: 35,
                freezeCols: 7,
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
                    dataIndx: ["部署"],
                    showSummary: [false],
                    collapsed: [false],
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
                        title: "電話番号",
                        dataIndx: "電話番号１", dataType: "string",
                        fixed: true,
                        hidden:true,
                    },
                    {
                        title: "ＦＡＸ",
                        dataIndx: "ＦＡＸ１", dataType: "string",
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
                    {
                        title: "商品_0",
                        dataIndx: "商品_0", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05080Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05080Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            //TODO:
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05080Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            }

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.CustomerCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05080Grid1;

            if (!grid) return;

            var rules = [];
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
                    var priceData = res[0].PriceData.filter(k => k.部署ＣＤ==r.部署ＣＤ && k.得意先ＣＤ==r.得意先ＣＤ);
                    _.chunk(priceData,5)
                        .map(pr => {
                            var i=1;
                            pr.map(cr => {
                                r["商品_" + i] = r["商品_" + i]==undefined ? cr.表示商品名 : r["商品_" + i] + "\n" + cr.表示商品名;
                                r["単価_" + i ]= r["単価_" + i]==undefined ? cr.単価:r["単価_" + i ] + "\n" + cr.単価;
                                i++;
                                var strProduct = (cr.商品名==null ? "":cr.商品名) + "\\" + cr.単価;
                                r["商品_0"] = r["商品_0"]==undefined ? strProduct : r["商品_0"] + "\n" + strProduct;
                            });
                        });
                    r.部署=r.部署ＣＤ + " " + r.部署名;
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
        print: function() {
            var vue = this;
            var grid = vue.DAI05080Grid1;

            //印刷用HTML全体適用CSS
            var targetData = grid.pdata;
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
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 22px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 1px black;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    font-size: 13.5pt;
                }
                table.header-table th:last-child {
                    border-right-width: 1px;
                }
                table.header-table tbody tr:last-child th {
                    border-bottom-width: 1px;
                }
                table.header-table thead th {
                    font-size: 11pt;
                    height: 22px;
                    text-align: center;
                }
                table.header-table tbody th {
                    height: 20.5x;
                }
                td.customer-nm {
                    font-size: 14pt;
                    letter-spacing: 0.1em;
                }
                div.title {
                    font-size: 20pt;
                }
                td.customer-cd-A{
                    font-size: 20pt;
                    text-align: center;
                    padding-left: 170px;
                }
                td.customer-cd{
                    text-align: center;
                    padding-left: 150px;
                }
                div.kaisya-info{
                    margin-left: 100px;
                    margin-bottom: 20px;
                    font-size: 8pt;
                }
                div.kaisya-info > div:first-child {
                    font-size: 11pt;
                }
                div.kaisya-info > div{
                    text-align: right;
                }
                div.chuui-gaki > div {
                    font-size: 8.5pt;
                    letter-spacing: 0.2em;
                    line-height: 15px;
                }
                table.header-table th:nth-child(1) {
                    width: 42%;
                }
                table.header-table th:nth-child(2),
                table.header-table th:nth-child(3) {
                    width: 13%;
                    text-align: right;
                    padding-right: 5px;
                }
                div.fax-tel {
                    margin-left: 125px;
                }
                div.hizuke {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    width: 150px;
                    margin-left: 20px;
                    padding: 3px;
                }
                span {
                    margin-left: 8px;
                    margin-right: 8px;
                }
                hr {
                    border: none;
                }
                div.insatubi {
                    font-size: 8pt;
                }
            `;

            var target = targetData.filter(k=>k.pq_level==undefined)
                .map(r => {
                    var products=r.商品_0.split("\n");
                    var layout_product=``;
                    products.map(pr=>{
                        var product=pr.split("\\");
                        layout_product += `
                            <tr>
                                <th>${product[0]}</th>
                                <th>${product[1]}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        `;
                    });
                    var row=12-products.length;
                    if(0<row){
                        [...Array(row)].map(r=>{
                            layout_product += `
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            `;
                        });
                    }
                    var layout=`
                            <div>
                                <div class="header">
                                    <div>
                                        <div class="title">
                                            注文書
                                        </div>
                                            <table style="width:100%;">
                                                <tr>
                                                    <td class="customer-nm">
                                                        <span>${r.得意先名}</span><span>様</span>
                                                    </td>
                                                    <td class="customer-cd-A">
                                                        A${r.得意先ＣＤ}A
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="fax-tel"><span>Fax.</span><span>${r.ＦＡＸ１ ? r.ＦＡＸ１ : ""}</span></div>
                                                        <div class="fax-tel"><span>Tel.</span><span>${r.電話番号１ ? r.電話番号１ : ""}</span></div>
                                                        <div class="hizuke"><span/><span/><span/><span>月<span/></span><span/><span/>日分</div>
                                                        <div style="height: 8px;"></div>
                                                    </td>
                                                    <td class="customer-cd">
                                                        <span>顧客コード<span/><span/></span><span/><span/><span>${r.得意先ＣＤ}</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <table class="header-table" style="border-width: 0px; margin-bottom: 8px;">
                                            <thead>
                                                <tr>
                                                    <th>弁当名</th>
                                                    <th>単価</th>
                                                    <th>数量</th>
                                                    <th>備考</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${layout_product}
                                            </tbody>
                                        </table>
                                        <div style="float:left;" class="chuui-gaki">
                                            <div>※本用紙をコピーしてお使い下さい。</div>
                                            <div>※本注文書は、御社専用です。他のお客様に流用しないでください。</div>
                                            <div>※ご注文、食数の変更はできるだけ午前９時１０分迄にお願いします。</div>
                                        </div>
                                        <div style="float:left;" class="kaisya-info">
                                            <div>株式会社 <span/>ダイヤス食品<span/></div>
                                            <div>Fax.<span/><span/>0836-21-4700</div>
                                            <div>Tel.<span/><span/>0836-32-1113</div>
                                            <div class="insatubi">印刷日　${moment().format("YYYY/MM/DD")}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    `;
                    layout = layout + `<div style="clear:left"></div><hr noshade/>` + layout;//1ページに同じ内容を2つ表示する。
                return { contents: layout };
            });
            window.res_tg = _.cloneDeep(target);//TODO:

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtmlFromJson(
                                target,
                                ``,
                                null,
                                1,
                                false,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;
            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 portrait; } }",
                printable: printable,
            };
            printJS(printOptions);
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
