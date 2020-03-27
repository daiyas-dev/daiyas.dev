<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="ShoriKbn"
                    ref="VueOptions_ShoriKbn"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="ShoriKbn"
                    :list="[
                        {code: '1', name: '集計処理', label: '集計処理'},
                        {code: '2', name: '集計解除', label: '集計解除'},
                    ]"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理年月</label>
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
            <div class="col-md-1">
                <label>最終処理日</label>
            </div>
            <div class="col-md-1">
                <label>2019年07月</label>
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
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null }"
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
            id="DAI03010Grid1"
            ref="DAI03010Grid1"
            dataUrl="/DAI03010/Search"
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
#DAI03010Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03010Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03010Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03010Grid1 .pq-td-div span {
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
    name: "DAI03010",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 月次集計",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                Customer: null,
            },
            DAI03010Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
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
                    on: false,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                     {
                        title: "",
                        dataIndx: "集計対象",
                        type: "checkbox",
                        cbId: "IncludesSummary",
                        width: 50, minWidth: 50, maxWidth: 50,
                        align: "center",
                        editable: true,
                        editor: false,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return "";
                            }
                        },
                    },
                    {
                        dataIndx: "IncludesSummary",
                        dataType: "bool",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                        },
                        hidden: true,
                    },
                   {
                        title: "当月集計済",
                        dataIndx: "当月集計済", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "請求先ＣＤ",
                        dataIndx: "請求先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden: false,
                    },
                    {
                        title: "請求先名",
                        dataIndx: "請求先名", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "前月残高",
                        dataIndx: "前月残高", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "今月入金額",
                        dataIndx: "今月入金額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "今月売上額",
                        dataIndx: "今月売上額", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "今月残高",
                        dataIndx: "今月残高", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "予備金額１",
                        dataIndx: "予備金額１", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "予備金額２",
                        dataIndx: "予備金額２", dataType: "integer", format: "#,###",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "予備ＣＤ１",
                        dataIndx: "予備ＣＤ１", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "予備ＣＤ２",
                        dataIndx: "予備ＣＤ２", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "修正担当者ＣＤ",
                        dataIndx: "修正担当者ＣＤ", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "修正担当者名",
                        dataIndx: "修正担当者名", dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03010Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "登録", id: "DAI03010Grid1_Update", disabled: false,shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI03010Grid1;

                        //登録データの作成
                        var SaveList=[];
                        _.forEach(grid.pdata,r=>{
                            window.resr=_.cloneDeep(r);//TODO:
                            var SaveItem={};
                            SaveItem.部署ＣＤ=r.部署ＣＤ;
                            SaveItem.請求先ＣＤ=r.請求先ＣＤ;
                            SaveItem.前月残高=r.前月残高;
                            SaveItem.今月入金額=r.今月入金額;
                            SaveItem.差引繰越額=r.差引繰越額;
                            SaveItem.今月売上額=r.今月売上額;
                            SaveItem.今月残高=r.今月残高;
                            SaveItem.予備金額１=r.予備金額１;
                            SaveItem.予備ＣＤ１=r.予備ＣＤ１;
                            SaveItem.予備金額２=r.予備金額２;
                            SaveItem.予備ＣＤ２=r.予備ＣＤ２;
                            SaveList.push(SaveItem);
                        });

                        //登録実行
                        grid.saveData(
                            {
                                uri: "/DAI03010/Save",
                                params: {
                                    SaveList: SaveList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: false,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        /*
                                        var compare = vue.mergeData(res.edited);
                                        var d = diff(vue.DAI01100Grid2.getPlainPData(), compare);

                                        _.forIn(d, (v, k) => {
                                            var r = _.omitBy(v, (vv, kk) => vv == undefined);
                                            if (_.isEmpty(r)) {
                                                delete d[k];
                                            } else {
                                                d[k] = r;
                                            }
                                        })

                                        if (_.isEmpty(d)) {
                                            grid2.commit();
                                        } else {
                                            if (res.skipped) {
                                                $.dialogInfo({
                                                    title: "登録チェック",
                                                    contents: "他で変更されたデータがあります。",
                                                });
                                            }

                                            grid2.blinkDiff(compare, true);
                                        }
                                        */
                                        return false;
                                    },
                                },
                            }
                        );
                    }
                }
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

            //検索条件変更
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03010Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;
            var params = $.extend(true, {}, vue.viewModel);

            //処理年月の1日から末日までの範囲を検索条件に指定する
            params.DateStart = params.TargetDate ? moment(params.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
            params.DateEnd   = params.TargetDate ? moment(params.DateStart).endOf('month').format("YYYYMMDD") : null;

            //コースはフィルタするので除外
            delete params.CustomerCd;
            grid.searchData(params, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03010Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.CustomerCd});
            }
            if (crules.length) {
                rules.push({ dataIndx: "得意先ＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
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
                var courseCd = header.コース.split(" ")[0];
                var courseNm = header.コース.split(" ")[1];
                var tantoCd = vue.DAI03010Grid1.pdata.find(v => v.コースＣＤ==courseCd).担当者ＣＤ;
                var tantoNm = vue.DAI03010Grid1.pdata.find(v => v.コースＣＤ==courseCd).担当者名;
                return `
                    <div class="title">
                        <h3><div class="report-title-area">得意先別実績表<div></h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width:  5%;">部署</th>
                                <th style="width:  5%; text-align: right;">${vue.viewModel.BushoCd}</th>
                                <th style="width: 18%;">${vue.viewModel.BushoNm}</th>
                                <th style="width:  5%;" class="blank-cell"></th>
                                <th style="width:  5%;" class="blank-cell"></th>
                                <th style="width: 15%;" class="blank-cell"></th>
                                <th style="width: 20%;" class="blank-cell"></th>
                                <th style="width:  5%;" class="blank-cell"></th>
                                <th style="width: 12%;" class="blank-cell"></th>
                                <th style="width:  5%;" class="blank-cell"></th>
                                <th style="width:  5%;" class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>日付</th>
                                <th colspan="2">${vue.viewModel.TargetDate} ～ ${vue.viewModel.DateEnd}</th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                                <th class="blank-cell"></th>
                            </tr>
                            <tr>
                                <th>コース</th>
                                <th style="text-align: right;">${courseCd}</th>
                                <th>${courseNm}</th>
                                <th>担当者</th>
                                <th style="text-align: right;">${tantoCd}</th>
                                <th>${tantoNm}</th>
                                <th class="blank-cell"></th>
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
                table.DAI03010Grid1
                table.DAI03010Grid1 tr,
                table.DAI03010Grid1 th,
                table.DAI03010Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.DAI03010Grid1 tr th:nth-child(1)[rowspan="2"] {
                    border-right: 0px;
                    color: white;
                    width: 5%;
                }
                table.DAI03010Grid1 tr th:nth-child(2)[rowspan="2"] {
                    border-left: 0px;
                    text-align:left;
                }
                table.DAI03010Grid1 tr td:nth-child(1) {
                    border-right: 0px;
                }
                table.DAI03010Grid1 tr td:nth-child(2) {
                    border-left: 0px;
                }
                table.DAI03010Grid1 tr th:nth-child(n+3)[colspan="2"] {
                    width: 10%;
                }
                table.DAI03010Grid1 tr th:last-child {
                    width: 5%;
                }
                table.DAI03010Grid1 tr th:nth-last-child(2) {
                    width: 5%;
                }
            `;
            var styleBench =`
                table.DAI03010Grid1 tr.group-summary td {
                    border: solid 1px black;
                }
                table.DAI03010Grid1 tr.grand-summary td:nth-child(2) {
                    text-align: right;

                table.DAI03010Grid1 tr.grand-summary td:nth-child(3) {
                    text-align: left;
                }
                table.DAI03010Grid1 tr[level="0"].group-summary td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03010Grid1 tr[level="0"].group-summary td:nth-child(2) {
                    text-align: right;
                    padding-right: 30px;
                }
                table.DAI03010Grid1 tr.grand-summary td {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.DAI03010Grid1 tr th:nth-last-child(-n+2):nth-last-child(-n) {
                    width: 10%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI03010Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                36,
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
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
