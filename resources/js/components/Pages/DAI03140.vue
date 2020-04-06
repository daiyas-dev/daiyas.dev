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
                        hidden:false,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                        hidden:true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        width: 120, minWidth: 120,
                        hidden:true,
                    },
                    {
                        title: "ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        render: ui => {
                            return { text:ui.rowData.部署ＣＤ};
                        }
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 120, minWidth: 120,
                        tooltip: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "* * 合　計 * *" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                return { text: "部署計" };
                            }
                        }
                    },
                    {
                        title: "",
                        dataIndx: "部署計", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                        hidden:true,
                        render: ui => {
                            return { text: "部署計" };
                        }
                    },
                    {
                        title: "前月末金額",
                        dataIndx: "前月末金額", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売上合計",
                        dataIndx: "売上合計", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "入金合計",
                        dataIndx: "入金合計", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売掛残",
                        dataIndx: "売掛残", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売上金額",
                        dataIndx: "売上金額", dataType: "string", align: "right",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            //売上金額、消費税額はそれぞれ3桁カンマ区切りで2段表示
                            //消費税額は12文字固定長(左スペース詰め)で表示
                            if (ui.rowData.pq_grandsummary || ui.rowData.pq_gsummary || ui.rowData.pq_child_sum) {
                                var uriage=ui.rowData.売上金額;
                                var shiouhizei = ui.rowData.消費税額;
                                var str_shiouhizei=("            " + shiouhizei).substr(-12);
                                str_shiouhizei = str_shiouhizei.replace(/\s/g, "&ensp;");
                                return { text: uriage + "&nbsp;\n(" + str_shiouhizei +")" };
                            }
                            else
                            {
                                var uriage=Number(ui.rowData.売上金額).toLocaleString();
                                var shiouhizei = Number(ui.rowData.消費税額).toLocaleString();
                                var str_shiouhizei=("            " + shiouhizei).substr(-12);
                                str_shiouhizei = str_shiouhizei.replace(/\s/g, "&ensp;");
                                return { text: uriage + "&ensp;\n(" + str_shiouhizei + ")" };
                            }
                        }
                    },
                    {
                        title: "その他売上",
                        dataIndx: "その他売上", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "現金",
                        dataIndx: "現金", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込",
                        dataIndx: "振込", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振替",
                        dataIndx: "振替", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金券",
                        dataIndx: "金券", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "その他入金",
                        dataIndx: "その他入金", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "消費税額",
                        dataIndx: "消費税額", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
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
                { visible: "true", value: "印刷", id: "DAI03140Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            //TODO:
            // vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.TargetDate = moment("20190801").format("YYYY年MM月DD日");
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
        onShowZandakaNashiChanged: function(code, entity) {
            var vue = this;
            //フィルタ変更ハンドラ
            vue.filterChanged();
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
        onSummaryKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI03140Grid1;

            //集計変更
            grid.Group()[vue.viewModel.SummaryKind == "1" ? "expandAll":"collapseAll"]();

            //列タイトル変更
            /*
            grid.options.colModel.find(c => c.dataIndx == "得意先名").title = isCourseSummary ? "コース名" : "得意先名";
            grid.options.colModel.find(c => c.dataIndx == "締日").title = isCourseSummary ? "" : "締日";
            grid.refreshCM();
            grid.refresh();
            */

            //集計変更
            //grid.Group()[isCourseSummary ? "collapse" : "expand"](1);
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03140Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;
            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd   = params.TargetDate ? moment(params.DateStart).endOf('month').format("YYYYMMDD") : null;
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.SummaryKind;

            window.resc=_.cloneDeep(params);//TODO:

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
            return;
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //残高有無チェック
            _.map(res,r=>{
                r.GroupKey1 = r.部署ＣＤ + ":" + r.部署名;
            });

            vue.footerButtons.find(v => v.id == "DAI03140Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI03140Grid1_Print").disabled = !res.length;

            return res;
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
                var bushoCd="";
                var bushoNm="";
                var courseCd="";
                var courseNm="";
                var tantoCd="";
                var tantoNm="";
                if(header.pq_level == 0)
                {
                    bushoCd = header.GroupKey1.split(":")[0];
                    bushoNm = header.GroupKey1.split(":")[1];
                    if(vue.viewModel.SummaryKind == "1")
                    {
                        var child=!!header.children.length ? header.children[0]:null;
                        if(child!=null)
                        {
                            courseCd = child.GroupKey2.split(":")[0];
                            courseNm = child.GroupKey2.split(":")[1];
                            tantoCd = child.GroupKey2.split(":")[2];
                            tantoNm = child.GroupKey2.split(":")[3];
                        }
                    }
                }
                else
                {
                    bushoCd = header.parentId.split(":")[0];
                    bushoNm = header.parentId.split(":")[1];
                    courseCd = header.GroupKey2.split(":")[0];
                    courseNm = header.GroupKey2.split(":")[1];
                    tantoCd = header.GroupKey2.split(":")[2];
                    tantoNm = header.GroupKey2.split(":")[3];
                }
                return `
                    <div class="title">
                        <h3><div class="report-title-area">売掛残高表<div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th>${moment(vue.viewModel.TargetDate, "YYYY年MM月").format("YYYY年MM月")}</th>
                            </tr>
                            <tr>
                                <th>部署</th>
                                <th>${bushoCd}</th>
                                <th>${bushoNm}</th>
                            </tr>
                            <tr>
                                <th>コース</th>
                                <th>${courseCd}</th>
                                <th>${courseNm}</th>
                                <th>担当者</th>
                                <th>${tantoCd}</th>
                                <th>${tantoNm}</th>
                                <th>作成日</th>
                                <th>${moment().format("YYYY年MM月DD日")}</th>
                                <th>PAGE</th>
                                <th>${idx + 1}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.DAI03140Grid1
                table.DAI03140Grid1 tr,
                table.DAI03140Grid1 th,
                table.DAI03140Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.DAI03140Grid1 tr th:nth-child(1)[rowspan="2"] {
                    border-right: 0px;
                    color: white;
                    width: 5%;
                }
                table.DAI03140Grid1 tr th:nth-child(2)[rowspan="2"] {
                    border-left: 0px;
                    text-align:left;
                }
                table.DAI03140Grid1 tr td:nth-child(1) {
                    border-right: 0px;
                }
                table.DAI03140Grid1 tr td:nth-child(2) {
                    border-left: 0px;
                }
                table.DAI03140Grid1 tr th:nth-child(n+3)[colspan="2"] {
                    width: 10%;
                }
                table.DAI03140Grid1 tr th:last-child {
                    width: 5%;
                }
                table.DAI03140Grid1 tr th:nth-last-child(2) {
                    width: 5%;
                }
            `;

            //Grouping再設定
            var keys = [];
            keys.push("GroupKey1");
            if (vue.viewModel.SummaryKind == "1") {
                keys.push("GroupKey2");
            }
            if (!!keys.length) {
                grid.Group().option({"dataIndx": keys});
            }

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03140Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                32,
                                vue.viewModel.SummaryKind == "1" ? [false, false] : false,
                                vue.viewModel.SummaryKind == "1" ? [true, true] : true,
                                vue.viewModel.SummaryKind == "1" ? [true, true] : true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "dataIndx": [] });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };
            //TODO: 印刷改ページの確認
            printJS(printOptions);
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
