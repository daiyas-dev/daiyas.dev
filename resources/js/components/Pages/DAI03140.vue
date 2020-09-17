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
                <label>出力年月</label>
            </div>
            <div class="col-md-2">
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
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力区分</label>
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="SummaryKind"
                    ref="VueOptions_SummaryKind"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SummaryKind"
                    :list="[
                        {code: '0', name: '部署計', label: '0:部署計'},
                        {code: '1', name: '得意先別', label: '1:得意先別'},
                    ]"
                    :onChangedFunc=onSummaryKindChanged
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
            id="DAI03140Grid1"
            ref="DAI03140Grid1"
            dataUrl="/DAI03140/Search"
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
#DAI03140Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03140Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03140Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03140Grid1 .pq-td-div span {
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
    name: "DAI03140",
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
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 得意先別月間売上入金表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                TargetDate: null,
                SummaryKind: null,
                CustomerCd: null,
            },
            DAI03140Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead:30,
                rowHt: 50,
                rowHtSum: 50,
                freezeCols: 6,
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
                    grandSummary: true,
                    indent: 0,
                    dataIndx: ["GroupKey1"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "GroupKey1",
                        dataIndx: "GroupKey1", dataType: "string",
                        hidden:true,
                    },
                    {
                        title: "ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden:true,
                        render: ui => {
                            if (ui.rowData.pq_child_sum) {
                                return{ text:ui.rowData.GroupKey1.split(":")[0]};
                            }
                        }
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        width: 120, minWidth: 120,
                        hidden:true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                return { text: "部署計" };
                            }
                            if (ui.rowData.pq_child_sum) {
                                return{ text:ui.rowData.GroupKey1.split(":")[1]};
                            }
                            return { text:ui };
                        }
                    },
                    {
                        title: "ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden:true,
                        render: ui => {
                            if (!!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    var tokui = ui.rowData.parentId;
                                    if(tokui!=null && tokui != undefined){
                                        var tokuiCd=tokui.split(":")[0];
                                        return{ text:tokuiCd};
                                    }
                                }else{
                                    return { text:ui };
                                }
                            }else{
                                if (!!ui.rowData.pq_grandsummary) {
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                }
                                return { text:ui };
                            }
                        }
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 120, minWidth: 120,
                        hidden:true,
                        render: ui => {
                            if (!!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "* * 合　計 * *" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    var tokui = ui.rowData.parentId;
                                    if(tokui!=null && tokui != undefined){
                                        var tokuiNm=tokui.split(":")[1];
                                        return{ text: "<span class=\"summary_caption1\">" + tokuiNm + "</span><span class=\"summary_caption2\">部署計</span>" };
                                    }
                                }else{
                                    return { text:ui };
                                }
                            }else{
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "* * 合　計 * *" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: "部署計" };
                                }
                                return { text:ui };
                            }
                        }
                    },
                    {
                        title: "",
                        dataIndx: "部署計", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "* * 合　計 * *" };
                            }
                            else
                            {
                                return { text: "部署計" };
                            }
                        }
                    },
                    {
                        title: "前月末金額",
                        dataIndx: "前月末金額", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売上合計",
                        dataIndx: "売上合計", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "入金合計",
                        dataIndx: "入金合計", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売掛残",
                        dataIndx: "売掛残", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売上金額",
                        dataIndx: "売上金額", dataType: "string", align: "right",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            //売上金額、消費税額はそれぞれ3桁カンマ区切りで2段表示
                            //消費税額は固定長10文字(左スペース詰め)で表示
                            if (ui.rowData.pq_grandsummary || ui.rowData.pq_gsummary || ui.rowData.pq_child_sum) {
                                var uriage=ui.rowData.売上金額;
                                var shiouhizei = ui.rowData.消費税額;
                                var str_shiouhizei=("          " + shiouhizei).substr(-10);
                                str_shiouhizei = str_shiouhizei.replace(/\s/g, "&ensp;");
                                return { text: uriage + "&nbsp;\n(" + str_shiouhizei +")" };
                            }
                            else
                            {
                                var uriage=Number(ui.rowData.売上金額).toLocaleString();
                                var shiouhizei = Number(ui.rowData.消費税額).toLocaleString();
                                var str_shiouhizei=("          " + shiouhizei).substr(-10);
                                str_shiouhizei = str_shiouhizei.replace(/\s/g, "&ensp;");
                                return { text: uriage + "&nbsp;\n(" + str_shiouhizei + ")" };
                            }
                        }
                    },
                    {
                        title: "その他売上",
                        dataIndx: "その他売上", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "現金",
                        dataIndx: "現金", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込",
                        dataIndx: "振込", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振替",
                        dataIndx: "振替", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金券",
                        dataIndx: "金券", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "その他入金",
                        dataIndx: "その他入金", dataType: "integer", format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "消費税額",
                        dataIndx: "消費税額", dataType: "integer", format: "#,###",
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
                { visible: "true", value: "検索", id: "DAI03140Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI03140Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI03140Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03140Grid1_Print", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
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
        onSummaryKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI03140Grid1;
            var isExpand = vue.viewModel.SummaryKind == "1";

            //列表示変更
            grid.options.colModel.find(c => c.dataIndx == "部署ＣＤ").hidden　=　isExpand;
            grid.options.colModel.find(c => c.dataIndx == "部署名").hidden　=　isExpand;
            grid.options.colModel.find(c => c.dataIndx == "部署計").hidden　=　isExpand;
            grid.options.colModel.find(c => c.dataIndx == "得意先ＣＤ").hidden　=　!isExpand;
            grid.options.colModel.find(c => c.dataIndx == "得意先名").hidden　=　!isExpand;
            grid.refreshCM();
            grid.refresh();

            //集計変更
            grid.Group()[isExpand ? "expandAll":"collapseAll"]();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03140Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!vue.viewModel.TargetDate) return;
            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd   = params.TargetDate ? moment(params.DateStart).endOf('month').format("YYYYMMDD") : null;
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.SummaryKind;

            grid.searchData(params, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03140Grid1;
            var rules = [];
            //得意先指定
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                rules.push({ dataIndx: "得意先ＣＤ",   condition: "equal", value: vue.viewModel.CustomerCd * 1 });
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });

            //ボタン無効化制御
            //vue.footerButtons.find(v => v.id == "DAI03140Grid1_CSV").disabled = (grid.pdata.length==0);
            //vue.footerButtons.find(v => v.id == "DAI03140Grid1_Print").disabled = (grid.pdata.length==0);
            return;
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //グループキーの生成
            _.map(res,r=>{
                r.GroupKey1 = r.部署ＣＤ + ":" + r.部署名;
            });

            //ボタン無効化制御
            //vue.footerButtons.find(v => v.id == "DAI03140Grid1_CSV").disabled = !res.length;
            //vue.footerButtons.find(v => v.id == "DAI03140Grid1_Print").disabled = !res.length;

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
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 19px;
                    text-align: center;
                }
                td {
                    height: 32px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                }
                div.report-title-area{
                    width:400px;
                    height:35px;
                    text-align: center;
                    display:table-cell;
                    vertical-align: middle;
                }
            `;
            var headerFunc = (header, idx, length) => {
                var bushoCd="";
                var bushoNm="";
                if(vue.viewModel.SummaryKind == "1")
                {
                    bushoCd = header.GroupKey1.split(":")[0];
                    bushoNm = header.GroupKey1.split(":")[1];
                }
                return `
                    <div class="title">
                        <h3><div class="report-title-area">＊ ＊ 得意先別月間売上入金表 ＊ ＊<div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 6%;">${vue.viewModel.SummaryKind == "1" ? bushoCd : ""}</th>
                                <th style="width: 10%;">${vue.viewModel.SummaryKind == "1" ? bushoNm : ""}</th>
                                <th style="width: 59%;" class="blank-cell"></th>
                                <th style="width: 5%;" class="blank-cell"></th>
                                <th style="width: 10%;" class="blank-cell"></th>
                                <th style="width: 5%;" class="blank-cell"></th>
                                <th style="width: 5%;" class="blank-cell"></th>
                           </tr>
                            <tr>
                                <th colspan=2>${moment(vue.viewModel.TargetDate, "YYYY年MM月").format("YYYY年MM月")}</th>
                                <th class="blank-cell"></th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th style="text-align: right;">PAGE</th>
                                <th style="text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI03140Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.DAI03140Grid1 th {
                    border-style : solid;
                    border-left-width : 0px;
                    border-top-width : 1px;
                    border-right-width : 0px;
                    border-bottom-width : 1px;
                }
                table.DAI03140Grid1 td {
                    border-style : dashed;
                    border-left-width : 0px;
                    border-top-width : 1px;
                    border-right-width : 0px;
                    border-bottom-width : 0px;
                }
                table.DAI03140Grid1 tbody tr:nth-child(1) td {
                    border-top-width : 0px;
                }
                table.DAI03140Grid1 tr.grand-summary td {
                    border-style : solid;
                    border-left-width : 0px;
                    border-top-width : 1px;
                    border-right-width : 0px;
                    border-bottom-width : 0px;
                }
                table.DAI03140Grid1 tr th:nth-child(1) {
                    width : 4.2%;
                }
                table.DAI03140Grid1 tr th:nth-child(2) {
                    width : 16%;
                    padding-left: 5px;
                }
                table.DAI03140Grid1 tr td:nth-child(1) {
                    text-align :right;
                }
                table.DAI03140Grid1 tr.grand-summary td:nth-child(3):nth-last-child(12) {
                    color: transparent;
                }
                table.DAI03140Grid1 tr.grand-summary td:nth-child(2):nth-last-child(13):after {
                    content: "** 合 計 **";
                    padding-left: 40px;
                    letter-spacing: 0.3em;
                }
                table.DAI03140Grid1 tr.grand-summary td:nth-child(2):nth-last-child(12) {
                    padding-left: 40px;
                }
                table.DAI03140Grid1 tr td:not(:nth-child(1)):not(:nth-child(2)) {
                    text-align : right;
                }
                table.DAI03140Grid1 tr th:nth-child(7):nth-last-child(7),
                table.DAI03140Grid1 tr th:nth-child(8):nth-last-child(7) {
                    width: 8.3%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03140Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                16,
                                vue.viewModel.SummaryKind == "1" ? false : true ,
                                vue.viewModel.SummaryKind == "1" ? true  : false,
                                vue.viewModel.SummaryKind == "1" ? true  : false,
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
