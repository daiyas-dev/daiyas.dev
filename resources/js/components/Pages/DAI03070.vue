﻿<template>
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
            <div class="col-md-4">
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
                        {code: '0', name: '得意先順', label: '0:得意先順'},
                        {code: '1', name: 'コース順', label: '1:コース順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
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
                    :params='{ bushoCd: viewModel.BushoCd }'
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
                    :disabled='viewModel.PrintOrder != 1'
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
            id="DAI03070Grid1"
            ref="DAI03070Grid1"
            dataUrl="/DAI03070/Search"
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
#DAI03070Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03070Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03070Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03070Grid1 .pq-td-div span {
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
    name: "DAI03070",
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
    watch: {
        "DAI03070Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI03070Grid1_Printout").disabled = !newVal.length;
            },
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 得意先別売上推移表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                TargetDate: null,
                CourseCd: null,
                CustomerCd: null,
                PrintOrder: "0",
            },
            DAI03070Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
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
                    grandSummary: true,
                    indent: 20,
                    dataIndx: ["GroupKey1"],
                    showSummary:[true,true],
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
                        fixed: true,
                    },
                    {
                        title: "GroupKey2",
                        dataIndx: "GroupKey2", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "担当者名",
                        dataIndx: "担当者名", dataType: "string",
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden:false,
                        fixed: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 200, minWidth: 200,
                        hidden:false,
                        fixed: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                return { text: "合　計" };
                            }
                            if (!!ui.rowData.pq_gsummary && !! ui.rowData.pq_level==1) {
                                //小計行
                                return { text: "コース　計" };
                            }
                            if (!!ui.rowData.pq_gsummary && !! ui.rowData.pq_level==0) {
                                //小計行
                                return { text: "部署　計" };
                            }
                            return ui;
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03070Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03070Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月");
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
            var vue = this;
            if(!vue.viewModel.TargetDate)
            {
                return;
            }
            var DateStart = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYY/MM/DD");
            var DateEnd   = moment(DateStart).add(11, 'months').format("YYYYMMDD");
            var ListAllMonth=vue.getListAllMonth(DateStart,DateEnd);
            var newCols = grid.options.colModel.filter(v => !!v.fixed);
            ListAllMonth.map(r=>{
                newCols.push(
                    {
                        title: r + "月",
                        dataIndx: "金額_" + r,
                        dataType: "integer",
                        format: "#,##0",
                        width: 90, maxWidth: 90, minWidth: 90,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "0" };
                            }
                            return ui;
                        },
                    }
                );
            });

            //合計追加
            newCols.push(
                {
                    title: "合計",
                    dataIndx: "合計",
                    dataType: "integer",
                    format: "#,##0",
                    width: 90, maxWidth: 90, minWidth: 90,
                    summary: {
                        type: "TotalInt",
                    },
                    render: ui => {
                        if (!ui.rowData[ui.dataIndx]) {
                            return { text: "0" };
                        }
                        return ui;
                    },
                }
            );

            //列定義更新
            grid.options.colModel = newCols;
            grid.refreshCM();
            grid.refresh();
        },
        getListAllMonth: function(params_start,params_end){
            var ListAllDate=[];
            var start = moment(params_start),
            end = moment(params_end);
            while(start.diff(end) <= 0){
                ListAllDate.push(start.format('M'));
                start.add(1, 'months');
            }
            return ListAllDate;
        },
        onDateChanged: function(code, entity) {
            var vue = this;
            //列定義更新
            vue.refreshCols();
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03070Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoArray || vue.viewModel.BushoArray.length==0) return;
            if (!vue.viewModel.TargetDate) return;
            if (!grid.options.colModel.some(v => v.custom)) {
                vue.refreshCols();
            }

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart  = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd    = params.TargetDate ? moment(params.DateStart).add(11, 'months').format("YYYYMMDD") : null;
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.CourseCd;
            delete params.CustomerCd;

            grid.searchData(params, false, null, callback);
            this.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03070Grid1;

            if (!grid) return;

            var rules = [];

            if(vue.viewModel.PrintOrder==1)
            {
                if (!vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                    var crulesCourse = [];
                    crulesCourse.push({ condition: "equal", value: vue.viewModel.CourseCd});
                    if (0<crulesCourse.length) {
                        rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crulesCourse });
                    }
                }
            }
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                var crulesCustomer = [];
                crulesCustomer.push({ condition: "equal", value: vue.viewModel.CustomerCd});
                if (0<crulesCustomer.length) {
                    rules.push({ dataIndx: "得意先ＣＤ", mode: "AND", crules: crulesCustomer });
                }
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //集計
            var groupings = _(res)
                .groupBy(v => (vue.viewModel.PrintOrder==1) ? [v.部署ＣＤ,v.コースＣＤ,v.得意先ＣＤ] : [v.部署ＣＤ,v.得意先ＣＤ])
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                acc["金額_" + v.売上データ明細月] = (acc["金額_" + v.売上データ明細月] || 0) + v.合計金額 * 1;
                                acc["合計"] = (acc["合計"] || 0) + v.合計金額 * 1;
                                return acc;
                            },
                            {}
                    );
                    ret.GroupKey1 = ret.部署ＣＤ + " " + ret.部署名;
                    ret.GroupKey2 = ret.コースＣＤ + " " + ret.コース名;
                    return ret;
                })

            //Grouping再設定
            var keys = [];
            keys.push("GroupKey1");
            //コース別の場合はGroupKey2(コース)をグループキーに追加
            if(vue.viewModel.PrintOrder==1){
                keys.push("GroupKey2");
            }
            grid.Group().option({"dataIndx": keys});

            return groupings;
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
                    return vue.BushoCdArray.length > 0 ? _.some(vue.BushoCdArray, k => k == v.部署ＣＤ) : true;
                })
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
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 15px;
                    text-align: center;
                }
                td {
                    height: 15px;
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
                }
            `;
            var headerFunc = (header, idx, length) => {
                var BushoCd = "";
                var BushoNm = "";
                var CourseCd="";
                var CourseNm="";
                var TantoCd="";
                var TantoNm="";
                if(vue.viewModel.PrintOrder==1){
                    //コース順
                    if(header.pq_level==0)
                    {
                        BushoCd = header.GroupKey1.split(" ")[0];
                        BushoNm = header.GroupKey1.split(" ")[1];
                        CourseCd= header.children[0].GroupKey2.split(" ")[0];
                        CourseNm= header.children[0].GroupKey2.split(" ")[1];
                    }else{
                        BushoCd = header.parentId.split(" ")[0];
                        BushoNm = header.parentId.split(" ")[1];
                        CourseCd= header.GroupKey2.split(" ")[0];
                        CourseNm= header.GroupKey2.split(" ")[1];
                    }
                    TantoCd = vue.DAI03070Grid1.pdata.find(v => v.部署ＣＤ==BushoCd && v.コースＣＤ==CourseCd).担当者ＣＤ;
                    TantoNm = vue.DAI03070Grid1.pdata.find(v => v.部署ＣＤ==BushoCd && v.コースＣＤ==CourseCd).担当者名;
                }else{
                    //部署順
                    BushoCd = header.GroupKey1.split(" ")[0];
                    BushoNm = header.GroupKey1.split(" ")[1];
                    CourseCd="";
                    CourseNm="";
                    TantoCd="";
                    TantoNm="";
                }
                return `
                    <div class="title">
                        <h3><div class="report-title-area">＊＊＊　得意先別売上推移表　＊＊＊
                    <div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 5.5%;">部署</th>
                                <th style="width: 5.5%; text-align: right;">${BushoCd}</th>
                                <th style="width: 16%;">${BushoNm}</th>
                                <th style="width: 42%;" class="blank-cell"></th>
                                <th style="width: 4.5%;" class="blank-cell"></th>
                                <th style="width: 9.5%;" class="blank-cell"></th>
                                <th style="width: 4.5%;" class="blank-cell"></th>
                                <th style="width: 5.5%;" class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>コース</th>
                                <th style="text-align: right;">${CourseCd}</th>
                                <th>${CourseNm}</th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>担当者</th>
                                <th style="text-align: right;">${TantoCd}</th>
                                <th>${TantoNm}</th>
                                <th class="blank-cell"></th>
                                <th>作成日</th>
                                <th style="text-align: right;">${moment().format("YYYY年MM月DD日")}</th>
                                <th>PAGE</th>
                                <th style="text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI03070Grid1
                table.DAI03070Grid1 tr,
                table.DAI03070Grid1 th,
                table.DAI03070Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.header-table th:not(.blank-cell),
                table.DAI03070Grid1 th,
                table.DAI03070Grid1 td {
                    border-style : solid;
                    border-left-width : 1px;
                    border-top-width : 1px;
                    border-right-width : 0px;
                    border-bottom-width : 0px;
                }
                table.header-table th:not(.blank-cell):nth-child(3),
                table.header-table th:not(.blank-cell):last-child,
                table.DAI03070Grid1 th:last-child,
                table.DAI03070Grid1 td:last-child {
                    border-right-width : 1px;
                }
                table.DAI03070Grid1 tr:last-child td {
                    border-bottom-width : 1px;
                }
                table.DAI03070Grid1 th:nth-child(1) {
                    width : 5%;
                    color: transparent;
                }
                table.DAI03070Grid1 th:nth-child(2) {
                    width : 20%;
                    text-align: left;
                    padding-left: 45px;
                }
                table.DAI03070Grid1 th:nth-child(15) {
                    width : 6.5%;
                }
                table.DAI03070Grid1 th {
                    white-space: nowrap;
                }
                table.DAI03070Grid1 th:nth-child(2),
                table.DAI03070Grid1 td:nth-child(2) {
                    border-left-width : 0px;
                }
                table.DAI03070Grid1 td:nth-child(1) {
                    text-align : right;
                    padding-right: 5px;
                }
                table.DAI03070Grid1 td:nth-child(1),
                table.DAI03070Grid1 td:nth-child(2) {
                    font-size: 8pt;
                }
                table.DAI03070Grid1 td:not(:nth-child(1)):not(:nth-child(2)) {
                    font-size: 7pt;
                }
                table.DAI03070Grid1  tr.group-summary td:nth-child(2),
                table.DAI03070Grid1  tr.grand-summary td:nth-child(2) {
                    padding-left: 45px;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03070Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                32,
                                false,
                                true,
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
