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
                    :params='{ BushoArray: searchParams.BushoCdArray, CourseKbn: viewModel.CourseKbn }'
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
                    :ParamsChangedCheckFunc=onCourseParamsCheck
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
            <div class="col-md-5">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoArray: searchParams.BushoArray, CourseCd:viewModel.CourseCd, KeyWord: null, NoLimit: AutocomplteNoLimitCustomer, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    :dataListReset=true
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
                    :nameWidth=300
                    :ParamsChangedCheckFunc=onCustomerParamsCheck
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>商品</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="ProductSelect"
                    ref="PopupSelect_Product"
                    :vmodel=viewModel
                    bind="ProductCd"
                    dataUrl="/Utilities/GetProductListForSelect"
                    :isPreload=true
                    title="商品名一覧"
                    labelCd="商品コード"
                    labelCdNm="商品名"
                    :showColumns='[
                        { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                        { title: "売価単価", dataIndx: "売価単価", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 3 },
                        { title: "得意先単価", dataIndx: "得意先単価", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 4 },
                    ]'
                    :popupWidth=750
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onProductChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ProductAutoCompleteFunc
                />
            </div>
            <div class="col-md-3">
                <label>適用開始日</label>
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
            </div>
            <div class="col-md-2">
               <VueCheck
                    id="VueCheck_Available"
                    ref="VueCheck_Available"
                    :vmodel=viewModel
                    bind="Available"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: '適用中のみ出力'},
                        {code: '1', name: 'する', label: '適用中のみ出力'},
                    ]"
                    :onChangedFunc=onAvailableChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04050Grid1"
            ref="DAI04050Grid1"
            dataUrl="/DAI04050/Search"
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
#DAI04050Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI04050Grid1 .pq-group-icon {
    display: none !important;
}
#DAI04050Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI04050Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI04050Grid1 .pq-td-div span {
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
    name: "DAI04050",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            var vue = this;
            return {
                BushoArray: vue.viewModel.BushoArray.map(v => v.code),
                CustomerCd: vue.viewModel.CustomerCd,
                ProductCd: vue.viewModel.ProductCd,
            };
        },
        AutocomplteNoLimitCustomer: function() {
            var vue = this;
            return !!vue.viewModel.BushoArray.length || !!vue.viewModel.CourseCd
        },
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 得意先別単価",
            noViewModel: true,
            viewModel: {
                BushoArray: [],
                CourseCd: null,
                CustomerCd: null,
                ProductCd: null,
                Available: "0",
                DateStart: null,
            },
            DAI04050Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 50 },
                autoRow: false,
                freezeCols: 11,
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
                    dataIndx: ["部署","得意先"],
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
                        hidden:true,
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "integer",
                        hidden:true,
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                        hidden:true,
                    },
                    {
                        title: "得意先",
                        dataIndx: "得意先", dataType: "string",
                        hidden:true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "integer",
                        hidden:true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        hidden:true,
                    },
                    {
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 180, minWidth: 180, maxWidth: 180,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "適用開始日",
                        dataIndx: "適用開始日", dataType: "date", format: "yy/mm/dd", align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "固定数",
                        dataIndx: "固定数", dataType: "integer",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "状況",
                        dataIndx: "状況", dataType: "integer", align: "center",
                        width: 90, minWidth: 90, maxWidth: 90,
                        render: ui => {
                            return { text: ui.rowData[ui.dataIndx] == "1" ? "適用中" : "" };
                        },
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
                { visible: "true", value: "検索", id: "DAI04050Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI04050Grid1_Printout", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04050_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04050Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04050Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04050Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showDetail(null, true);
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04050Grid1.selectionRow",
                row => {
                    vue.footerButtons.find(v => v.id == "DAI04050Grid1_Detail").disabled = !row || (!!row.rowData && !row.rowData.得意先)
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerParamsCheck: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return true;
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseParamsCheck: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return true;
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;
        },
        onProductChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onDateChanged: function() {
            var vue = this;

            //フィルタハンドラ
            vue.filterChanged();
        },
        onAvailableChanged: function() {
            var vue = this;

            //フィルタハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI04050Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!vue.searchParams.CustomerCd && !vue.searchParams.ProductCd) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04050Grid1;

            if (!grid) return;

            var rules = [];
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }
            if (vue.viewModel.ProductCd != undefined && vue.viewModel.ProductCd != "") {
                rules.push({ dataIndx: "商品ＣＤ", condition: "equal", value: vue.viewModel.ProductCd });
            }
            var mt = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            if (mt.isValid()) {
                rules.push({ dataIndx: "適用開始日", condition: "gte", value: mt.format("YYYY/MM/DD") });
            }
            if (vue.viewModel.Available == "1") {
                rules.push({ dataIndx: "状況", condition: "equal", value: "1" });
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI04050Grid1_Printout").disabled = !res.length;

            res.forEach(r => {
                r.部署 = r.部署ＣＤ + ":" + r.部署名;
                r.得意先 = r.得意先ＣＤ + ":" + r.得意先名;
                return r;
            });

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
        ProductAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        showDetail: function(rowData, IsNew) {
            var vue = this;
            var grid = vue.DAI04050Grid1;
            if (!grid) return;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length > 0) {
                    if (rows.length != 1) return;
                    params = _.cloneDeep(rows[0].rowData);
                } else {
                    //検索結果0件の時　新規登録条件 = 得意先指定
                    if (!vue.viewModel.CustomerCd) return;

                    params = {};
                    params.得意先ＣＤ = vue.viewModel.CustomerCd;
                    params.得意先名 = vue.viewModel.CustomerNm;
                }
            }

            if (!params.得意先 && !params.得意先ＣＤ) return;
            if (!!params.得意先 && !params.得意先ＣＤ) {
                params.得意先ＣＤ = params.得意先.split(":")[0];
                params.得意先名 = params.得意先.split(":")[1];
            }

            params.IsChild = true;
            params.IsNew = IsNew || false;
            params.ParentGrid = grid;

            //DAI04051を子画面表示
            PageDialog.show({
                pgId: "DAI04051",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 880,
                height: 600,
                onBeforeClose: (event, ui) => {
                    console.log("onBeforeClose", event, ui);

                    if ($(window.event.target).attr("shortcut") == "ESC") return true;

                    var dlg = $(event.target);
                    var editting = dlg.find(".pq-grid")
                        .map((i, v) => $(v).pqGrid("getInstance").grid)
                        .get()
                        .some(g => !_.isEmpty(g.getEditCell()));
                    var isEscOnEditor = !!window.event && window.event.key == "Escape"
                        && (
                            $(window.event.target).hasClass("target-input") ||
                            $(window.event.target).hasClass("pq-cell-editor")
                        );

                    return !editting && !isEscOnEditor;
                }
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
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 17px;
                    text-align: center;
                }
                td {
                    height: 17px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 0px black;
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
            var headerFunc = (header, idx, length, chunk, chunks) => {
                var BushoCd = "";
                var BushoNm = "";

                if(header.pq_level==0)
                {
                    BushoCd = header.部署.split(":")[0];
                    BushoNm = header.部署.split(":")[1];
                }else{
                    BushoCd = header.parentId.split(":")[0];
                    BushoNm = header.parentId.split(":")[1];
                }
                return `
                    <div class="title">
                        <h3><div class="report-title-area">＊＊＊　得意先単価表　＊＊＊
                    <div></h3>
                    </div>
                    <table class="header-table">
                        <thead>
                            <tr>
                                <th style="width:  6%;">部署</th>
                                <th style="width:  6%;">${BushoCd}</th>
                                <th style="width: 15%;">${BushoNm}</th>
                                <th style="width: 26%;" class="blank-cell"></th>
                                <th style="width:  6%;">作成日</th>
                                <th style="width: 15%; text-align: right;">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width:  6%;">PAGE</th>
                                <th style="width:  6%; text-align: right;">${chunk}/${chunks}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var styleCustomers =`
                table.header-table th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    text-align: center;
                }
                table.DAI04050Grid1 th,
                table.DAI04050Grid1 td {
                    border-collapse: collapse;
                    border: 0px solid black;
                }
                table.DAI04050Grid1 th,
                table.DAI04050Grid1 td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:nth-child(1) th:nth-child(3),
                table.header-table tr:nth-child(1) th:last-child,
                table.DAI04050Grid1 th:last-child,
                table.DAI04050Grid1 td:last-child {
                    border-right-width: 1px;
                }
                table.DAI04050Grid1 tr:last-child td {
                    border-bottom-width: 1px;
                }
                table.DAI04050Grid1 th:nth-child(1) {
                    width: 5%;
                    color: transparent;
                }
                table.DAI04050Grid1 th:nth-child(2) {
                    width: 20%;
                    text-align: left;
                    padding-left: 25px;
                }
                table.DAI04050Grid1 td:nth-child(2) {
                    padding-left: 5px;
                }
                table.DAI04050Grid1 th:nth-child(3),
                table.DAI04050Grid1 th:nth-child(5) {
                    width: 7%;
                }
                table.DAI04050Grid1 th:nth-child(4) {
                    width: 12%;
                }
                table.DAI04050Grid1 th:nth-child(6) {
                    text-align: left;
                    padding-left: 25px;
                }
                table.DAI04050Grid1 td:nth-child(6) {
                    text-align: left;
                    padding-left: 18px;
                }
                table.DAI04050Grid1 th {
                    white-space: nowrap;
                }
                table.DAI04050Grid1 th:nth-child(2),
                table.DAI04050Grid1 td:nth-child(2) {
                    border-left-width: 0px;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI04050Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                46,
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
                style: "@media print { @page { size: A4; } }",
                printable: printable,
            };
            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
