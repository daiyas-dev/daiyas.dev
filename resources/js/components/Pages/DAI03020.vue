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
                <label>出力順</label>
            </div>
            <div class="col-md-3">
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
            </div>
            <div class="col-md-2">
                <VueCheck
                    id="VueCheck_ShowZandakaNashi"
                    ref="VueCheck_ShowZandakaNashi"
                    :vmodel=viewModel
                    bind="ShowZandakaNashi"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '出力しない', label: '残高なしも出力'},
                        {code: '1', name: '出力する', label: '残高なしも出力'},
                    ]"
                    :onChangedFunc=onShowZandakaNashiChanged
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
                    :params='{ bushoCd: viewModel.BushoCd, BushoArray:this.BushoCdArray,UserBushoCd: getLoginInfo().bushoCd }'
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
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
            id="DAI03020Grid1"
            ref="DAI03020Grid1"
            dataUrl="/DAI03020/Search"
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
#DAI03020Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI03020Grid1 .pq-group-icon {
    display: none !important;
}
#DAI03020Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI03020Grid1 .pq-td-div span {
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
    name: "DAI03020",
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
        "DAI03020Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI03020Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "月次処理 > 売掛残高表",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                TargetDate: null,
                PrintOrder: null,
                ShowZandakaNashi: null,
                CourseCd: null,
                CustomerCd: null,
            },
            DAI03020Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead:30,
                rowHt: 30,
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
                    dataIndx: [],
                    showSummary: [true,true],
                    collapsed: [false,false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string", align:"right",
                        width: 100, minWidth: 100, maxWidth: 100,
                        hiddenOnExport: true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hiddenOnExport: true,
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "請求先ＣＤ",
                        dataIndx: "請求先ＣＤ", dataType: "string", align:"right",
                        width: 100, minWidth: 100, maxWidth: 100,
                        hiddenOnExport: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 120, minWidth: 120,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "合　計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                var text = "";
                                if (vue.viewModel.PrintOrder==0) {
                                    text = "部署計";
                                } else {
                                    text = ui.rowData.pq_level == 0 ? "部署計" : "コース計"
                                }
                                return { text: text };
                            }
                            if (!!ui.Export) {
                                return { text: ui.rowData.請求先ＣＤ + " " + ui.rowData.得意先名};
                            }
                        }
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        hidden: true,
                        hiddenOnExport: false,
                        render: ui => {
                            if (!!ui.Export && !ui.rowData.pq_gsummary) {
                                var text = ui.rowData.コースＣＤ==null ? "コースなし" : (ui.rowData.コースＣＤ + " " + ui.rowData.コース名)
                                return { text: text };
                            }
                        }
                    },
                    {
                        title: "前月請求残高",
                        dataIndx: "前月残高", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "今月入金額",
                        dataIndx: "今月入金額", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "今月売上額",
                        dataIndx: "今月売上額", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "今月残高",
                        dataIndx: "今月残高", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "残高有無判定",
                        dataIndx: "残高有無判定", dataType: "bool",
                        hidden: true,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コース担当者名",
                        dataIndx: "コース担当者名", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "GroupKey1",
                        dataIndx: "GroupKey1", dataType: "string",
                        hidden:true,
                        render: ui => {
                            if (!ui.Export) {
                                return { text:ui.rowData.GroupKey1.split(":")[0] + " " + ui.rowData.GroupKey1.split(":")[1]};
                            }
                        }
                    },
                    {
                        title: "GroupKey2",
                        dataIndx: "GroupKey2", dataType: "string",
                        hidden:true,
                        render: ui => {
                            if (!ui.Export) {
                                return { text:ui.rowData.GroupKey2.split(":")[0] + " " + ui.rowData.GroupKey2.split(":")[1]};
                            }
                        }
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI03020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "明細", id: "DAI03020Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI03020Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI03020Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI03020Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI03020Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI03020Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
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
        onPrintOrderChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI03020Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;
            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            params.BushoArray = vue.BushoCdArray;//部署コードのみ渡す

            //フィルタするパラメータは除外
            delete params.ShowZandakaNashi;
            delete params.CustomerCd;
            grid.searchData(params, false, null, callback);
        },

        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI03020Grid1;

            if (!grid) return;

            var rules = [];
            //残高があるものだけ表示する場合
            if(vue.viewModel.ShowZandakaNashi!=1)
            {
                //前月残高!=0 or 今月入金額!=0 or 今月売上額!=0を表示
                rules.push({ dataIndx: "残高有無判定",   condition: "equal", value: true });
            }

            //コース指定
            if (vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                rules.push({ dataIndx: "コースＣＤ",   condition: "equal", value: vue.viewModel.CourseCd * 1 });
            }

            //得意先指定
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                rules.push({ dataIndx: "請求先ＣＤ",   condition: "equal", value: vue.viewModel.CustomerCd * 1 });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //残高有無チェック
            _.map(res,r=>{
                r.残高有無判定 = !(r.前月残高==0 && r.今月入金額==0 && r.今月売上額==0);
                r.GroupKey1 = r.部署ＣＤ + ":" + r.部署名;
                r.GroupKey2 = r.コースＣＤ + ":" + r.コース名 + ":" + r.担当者ＣＤ + ":" + r.コース担当者名;
            });

            vue.footerButtons.find(v => v.id == "DAI03020Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI03020Grid1_Print").disabled = !res.length;

            return res;
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
        showDetail: function(isNew, rowData) {
            var vue = this;
            var grid = vue.DAI03020Grid1;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }
            var TargetDate=vue.viewModel.TargetDate + "01日";
            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: TargetDate,
                DateStart: TargetDate,
                DateEnd: moment(TargetDate,"YYYY年MM月DD日").endOf('month').format("YYYY年MM月DD日"),
                //SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
                //CourseKbn: data.コース区分,
            };

            PageDialog.show({
                pgId: "DAI03021",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
            });
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
                    height: 14px;
                    text-align: center;
                }
                td {
                    height: 14px;
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
                    if(vue.viewModel.PrintOrder == "1")
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
                table.DAI03020Grid1
                table.DAI03020Grid1 tr,
                table.DAI03020Grid1 th,
                table.DAI03020Grid1 td {
                    border-collapse: collapse;
                    border:1px solid black;
                }
                table.DAI03020Grid1 tr th:nth-child(1)[rowspan="2"] {
                    border-right: 0px;
                    color: white;
                    width: 5%;
                }
                table.DAI03020Grid1 tr th:nth-child(2)[rowspan="2"] {
                    border-left: 0px;
                    text-align:left;
                }
                table.DAI03020Grid1 tr td:nth-child(1) {
                    border-right: 0px;
                }
                table.DAI03020Grid1 tr td:nth-child(2) {
                    border-left: 0px;
                }
                table.DAI03020Grid1 tr th:nth-child(n+3)[colspan="2"] {
                    width: 10%;
                }
                table.DAI03020Grid1 tr th:last-child {
                    width: 5%;
                }
                table.DAI03020Grid1 tr th:nth-last-child(2) {
                    width: 5%;
                }
            `;

            //Grouping再設定
            var keys = [];
            keys.push("GroupKey1");
            if (vue.viewModel.PrintOrder == "1") {
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
                            vue.DAI03020Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                32,
                                vue.viewModel.PrintOrder == "1" ? [false, false] : false,
                                vue.viewModel.PrintOrder == "1" ? [true, true] : true,
                                vue.viewModel.PrintOrder == "1" ? [true, true] : true,
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
            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
