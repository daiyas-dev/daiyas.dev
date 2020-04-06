<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>請求日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_TargetDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>印字金額</label>
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="PrintValue"
                    ref="VueOptions_PrintValue"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintValue"
                    :list="[
                        {code: '0', name: '請求金額', label: '請求金額'},
                        {code: '1', name: 'お買上げ金額', label: 'お買上げ金額'},
                    ]"
                    :onChangedFunc=onPrintValueChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ UserBushoCd: getLoginInfo().bushoCd }'
                    :isPreload=true
                    :noResearch=true
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "部署ＣＤ", dataIndx: "部署ＣＤ", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 150, maxWidth: 150, minWidth: 150, colIndx: 1 },
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, tooltip: true, }
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=250
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteNoLimit=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-11">
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
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-11">
                <VueCheckList
                    id="SearchOptions"
                    :vmodel=viewModel
                    bind="SearchOptions"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '1', name: 'マイナスも出力', label: 'マイナスも出力'},
                    ]"
                    :onChangedFunc=onSearchOptionsChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI07050Grid1"
            ref="DAI07050Grid1"
            dataUrl="/DAI07050/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI07050Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI07050Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI07050Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI07050",
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
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI07050Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "個人宅 > 個人領収書",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                TargetDate: null,
                CourseCd: null,
                CourseNm: null,
                CustomerCd: null,
                CustomerNm: null,
                SearchOptions: [],
            },
            DAI07050Grid1: null,
            BushoInfo: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 25,
                rowHt: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45 },
                autoRow: false,
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
                    number: false,
                    type: "local",
                    sorter: [
                        { dataIndx: "請求先ＣＤ", dir: "up" },
                        { dataIndx: "請求日付", dir: "up" },
                    ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 0,
                    dataIndx: [],
                    showSummary: [true, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "コース",
                        function(rowData) {
                            return rowData.コースＣＤ + ":" + rowData.コース名 + ":" + rowData.担当者ＣＤ + ":" + rowData.担当者名;
                        }
                    ],
                    [
                        "前回請求残高",
                        function(rowData) {
                            return (rowData["３回前残高"] || 0) * 1 + (rowData["前々回残高"] || 0) * 1 + (rowData["前回残高"] || 0) * 1;
                        }
                    ],
                    [
                        "締日",
                        function(rowData) {
                            return "/"
                                 + (rowData["締日１"] || "")
                                 + "/"
                                 + (rowData["締日２"] || "")
                                 + "/"
                                 ;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コース",
                        dataIndx: "コース",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "請求先ＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "請求日付",
                        dataIndx: "請求日付",
                        dataType: "date",
                        hidden: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        tooltip: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        tooltip: true,
                        render: ui => {
                            if (!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "ＳＥＱ",
                        dataIndx: "ＳＥＱ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "支払方法",
                        dataIndx: "支払方法",
                        dataType: "string",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                    },
                    {
                        title: "集金日",
                        dataIndx: "集金日",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        hidden: true,
                        hiddenOnExport: false,
                        render: ui => {
                            if (!!ui.Export) {
                                if (!!ui.rowData.pq_grandsummary) {
                                    return { text: "合計" };
                                }
                                if (!!ui.rowData.pq_gsummary) {
                                    return { text: ui.rowData.pq_level == 0 ? "請求日計" : "コース計" };
                                }
                            }
                        },
                    },
                    {
                        title: "前回請求残高",
                        dataIndx: "前回請求残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回入金額",
                        dataIndx: "今回入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "差引繰越額",
                        dataIndx: "差引繰越額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回売上額",
                        dataIndx: "今回売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "今回請求額",
                        dataIndx: "今回請求額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                        cautionNegative: true,
                        zeroToEmpty: [true, false],
                    },
                    {
                        title: "締日",
                        dataIndx: "締日",
                        dataType: "string",
                        hidden: true,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI07050Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI07050Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //TODO
            // vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.TargetDate = moment("20190907").format("YYYY年MM月DD日");

            //初期フィルタ
            vue.filterChanged();
        },
        onBushoChanged: function(code, entity, entities) {
            var vue = this;

            if (!!entity) {
                vue.BushoInfo = entity.info;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onPrintValueChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI07050Grid1;
        },
        onSearchOptionsChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署ＣＤ) {
                    vue.viewModel.BushoCd = entity.部署ＣＤ;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                if (vue.viewModel.BushoCd != entity.部署CD) {
                    vue.viewModel.BushoCd = entity.部署CD;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                }
            }

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI07050Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!force && !!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                return;
            }

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI07050Grid1;

            if (!grid) return;

            var rules = [];

            grid.filter({ oper: "replace", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI07050Grid1_Print").disabled = !res.length;

            return res;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "担当者名"];

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
                    ret.label = "[" + (v.部署名 || "部署無し") + "]" + v.Cd + " : " + v.CdNm + "【" + v.担当者名 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
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
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
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
            var grid = vue.DAI07050Grid1;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div{
                    margin-bottom: 3px;
                }
                div.header{
                    font-family: "MS UI Gothic";
                    font-size: 10pt;
                    font-weight: normal;
                    justify-content: left;
                    width: 100%;
                }
                .header-title{
                	font-size: 18pt;
                	font-weight: bold;
                	letter-spacing: 16px;
                }
                .header-subtitle{
                	font-size: 14pt;
                	padding-bottom: 10px;
                }
                .header-seikyu-no{
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    margin-top: 3px;
                    padding-top: 3px;

                }
                span {
                    padding-left: 8px;
                }
                div.header-tokuisaki {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    padding-top: 12px;
                    padding-bottom: 12px;
                    margin-top: 3px;
                    margin-bottom: 3px;
                    font-size: 11pt;
                    font-weight: bold;
                }
				#a-box {
					float: left;
					width: 58%
				}
				#b-box {
					float: left;
					width: 20%;
				}
				#c-box {
					float: left;
					width: 22%;
				}
				#d-box {
					float: left;
					width: 50%;
				}
				#e-box {
					width: 6%;
				}
				#f-box {
					float: right;
					width: 42%;
				}
				#g-box {
					clear: both;
					float: left;
					width: 58%;
					padding-top: 45px;
				}
				#h-box {
					float: right;
					width: 42%;
				}
				#i-box {
					float: left;
					width: 35%;
				}
				#j-box {
					float: right;
					width: 55%;
				}
				#k-box {
					float: left;
					width: 90%;
                    padding-bottom: 8px;
				}
				#l-box {
					float: right;
					width: 10%;
	                padding-bottom: 8px;
	                text-align: right;
				}
				#f-box > div{
					padding-left: 14px;
				}
				#i-box {
					padding-left: 14px;
				}
                table.header-table tbody th {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 12pt;
                    font-weight: normal;
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
                    font-size: 10pt;
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
                    height: 21px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    width: 12%;
                }
                table.header-table tbody tr th {
                    text-align: right;
                    padding-right: 10px;
                }
                table.header-table tr:first-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    height: 26px;
                }
                table.header-table tr:first-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.header-table thead:first-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 3px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                }
                table.header-table thead:first-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 3px;
                    border-right-width: 2px;
                    border-bottom-width: 0px;
                }
                table.header-table tr:last-child th:nth-child(4) {
                    border-style: solid;
                    border-left-width: 3px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 3px;
                }
                table.header-table tr:last-child th:nth-child(5) {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 2px;
                    border-bottom-width: 3px;
                }
            `;

            axios.post("/DAI07050/GetMeisaiList", { SeikyuNoArray: grid.pdata.map(v => v.請求番号 * 1)})
            .then(res => {
                var group = _.groupBy(res.data, v => v.得意先ＣＤ);

                var printable = $("<html>")
                    .append($("<head>").append($("<style>").text(globalStyles)))
                    .append(
                        $("<body>")
                            .append(
                                grid.pdata.map(r => {
                                    var pdata = group[r.請求先ＣＤ] || [{}];
                                    var summary = _.reduce(
                                        pdata,
                                        (a, v, k) => {
                                            a.商品名 = "【 合 計 】";
                                            a.数量 = (a.数量 || 0) + (v.数量 || 0) * 1;
                                            a.金額 = (a.金額 || 0) + (!v.伝票Ｎｏ ? (v.金額 || 0) * 1 : 0);
                                            a.入金金額 = (a.入金金額 || 0) + (!!v.伝票Ｎｏ ? (v.入金金額 || 0) * 1 : 0);
                                            return a;
                                        },
                                        {}
                                    );
                                    pdata.push(summary);
                                    pdata.forEach(v => {
                                        v.数量 = pq.formatNumber(v.数量, "#,##0");
                                        v.単価 = pq.formatNumber(v.単価, "#,##0");
                                        v.金額 = pq.formatNumber(v.金額, "#,##0");
                                        v.入金金額 = pq.formatNumber(v.入金金額, "#,##0");
                                    });

                                    var headerFunc = (header, idx, length, chunk, chunks) => {
                                        return `
                                            <div class="header">
                                                <div>
                                                    <div id="k-box">
                                                        ｺｰﾄﾞNo.${r.請求先ＣＤ}
                                                        <span/>-${r.コースＣＤ}
                                                        <span/>(
                                                        <span/>0
                                                        <span>-0</span>
                                                        <span>-0</span>
                                                        )
                                                    </div>
                                                    <div id="l-box">
                                                        ${idx + 1}
                                                        /
                                                        <span/>${length}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div id="a-box">
                                                        </br></br>
                                                        <div style="margin-bottom: 8px;">
                                                            <span/>〒
                                                            <span/>${r.郵便番号}
                                                        </div>
                                                        <div>
                                                            ${r.住所１}
                                                        </div>
                                                        <br>
                                                    </div>
                                                    <div id="b-box">
                                                        <div class="header-title">
                                                            請求書
                                                        </div>
                                                        <div class="header-subtitle">
                                                            (軽減税率対象)
                                                        </div>
                                                        <div style="margin-bottom: 8px;">
                                                            株式会社<span/>ダイヤス食品
                                                        </div>
                                                    </div>
                                                    <div id="c-box">
                                                        <div>
                                                            <span style="white-space: pre;">${moment(r.請求日付).format("  YY  年  MM  月  DD  日")}</span>
                                                        </div>
                                                        <div class="header-seikyu-no">
                                                            <span/>請求番号
                                                            <span/>${r.請求番号}
                                                        </div>
                                                    </div>
                                                <div>
                                                <div style="clear: both;">
                                                    <div id="d-box">
                                                        <div class="header-tokuisaki">
                                                            ${r.得意先名}
                                                            <span>様
                                                        </div>
                                                        <div>
                                                            Tel
                                                            <span/><span/>${r.電話番号１}
                                                            <span/><span/>Fax
                                                            <span/><span/>${r.ＦＡＸ１}
                                                        </div>
                                                        </br>
                                                    </div>
                                                    <div id="e-box">
                                                    </div>
                                                    <div id="f-box">
                                                        <div>
                                                            <span/>〒
                                                            <span/>${vue.BushoInfo.郵便番号}
                                                        </div>
                                                        <div>
                                                            ${vue.BushoInfo.住所}
                                                        </div>
                                                        <div>
                                                            Tel
                                                            <span/><span/>${vue.BushoInfo.電話番号}
                                                        </div>
                                                        <div>
                                                            Fax
                                                            <span/><span/>${vue.BushoInfo.FAX}
                                                        </div>
                                                    </div>
                                                    <div id="g-box">
                                                        <div style="margin-bottom: 8px;">
                                                            毎度ありがとうございます。
                                                        </div>
                                                        <div>
                                                            下記の通りご請求申し上げます。
                                                        </div>
                                                    </div>
                                                    <div id="h-box">
                                                        <div style="margin-bottom: 8px;">
                                                            取引金融機関
                                                        </div>
                                                        <div id="i-box">
                                                            <div>
                                                                ${vue.BushoInfo.金融機関1名称}
                                                            </div>
                                                            <div>
                                                                ${vue.BushoInfo.口座種別1名称}
                                                                <span/><span/>${vue.BushoInfo.口座番号1}
                                                            </div>
                                                            <div>
                                                                ${vue.BushoInfo.金融機関2名称}
                                                            </div>
                                                            <div>
                                                                ${vue.BushoInfo.口座種別2名称}
                                                                <span/><span/>${vue.BushoInfo.口座番号2}
                                                            </div>
                                                        </div>
                                                        <div id="j-box">
                                                            <div>
                                                                <span/>${vue.BushoInfo.金融機関支店1名称}
                                                            </div>
                                                            <div>
                                                                ${vue.BushoInfo.口座名義人1}
                                                            </div>
                                                            <div>
                                                                <span/>${vue.BushoInfo.金融機関支店2名称}
                                                            </div>
                                                            <div>
                                                                ${vue.BushoInfo.口座名義人1}
                                                        </div>
                                                    </div>
                                                </div>
                                            <table class="header-table" style="border-width: 0px; margin-bottom: 20px;">
                                                <thead>
                                                    <tr>
                                                        <th>前回請求額</th>
                                                        <th>御入金額</th>
                                                        <th>繰越金額</th>
                                                        <th>御買上金額</th>
                                                        <th>消費税</th>
                                                        <th>今回請求額</th>
                                                    </tr>
                                                    <tr>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <th>${pq.formatNumber(r.前回請求残高, "#,###0")}</th>
                                                    <th>${pq.formatNumber(r.今回入金額, "#,###0")}</th>
                                                    <th>${pq.formatNumber(r.差引繰越額, "#,###0")}</th>
                                                    <th>${pq.formatNumber(r.今回売上額, "#,###0")}</th>
                                                    <th>${pq.formatNumber(r.消費税額, "#,###0")}</th>
                                                    <th>${pq.formatNumber(r.今回請求額, "#,###0")}</th>
                                                </tbody>
                                            </table>
                                            </div>
                                        `;
                                    };

                                    var html = grid.generateHtmlFromJson(
                                        pdata,
                                        `
                                            .header-table th {
                                                border-style: solid;
                                                border-left-width: 0px;
                                                border-top-width: 1px;
                                                border-right-width: 1px;
                                                border-bottom-width: 0px;
                                            }
                                            .header-table tr th:first-child {
                                                border-left-width: 1px;
                                            }
                                            .header-table tr:nth-child(1) th:nth-child(n+4) {
                                                border-left-width: 0px;
                                                border-top-width: 0px;
                                                border-right-width: 0px;
                                                border-bottom-width: 0px;
                                            }
                                            .header-table tr:nth-child(4) th:nth-child(6) {
                                                border-top-width: 0px;
                                            }
                                            table.DAI07050Grid1 tr:nth-child(1) th {
                                                border-style: solid;
                                                border-left-width: 1px;
                                                border-top-width: 1px;
                                                border-right-width: 0px;
                                                border-bottom-width: 1px;
                                            }
                                            table.DAI07050Grid1 tr th:last-child {
                                                border-right-width: 1px;
                                            }
                                            table.DAI07050Grid1 tr td {
                                                border-style: solid;
                                                border-left-width: 1px;
                                                border-top-width: 0px;
                                                border-right-width: 0px;
                                                border-bottom-width: 1px;
                                            }
                                            table.DAI07050Grid1 tr.grand-summary td {
                                                border-style: solid;
                                                border-left-width: 1px;
                                                border-top-width: 0px;
                                                border-right-width: 0px;
                                                border-bottom-width: 1px;
                                            }
                                            table.DAI07050Grid1 tr td:last-child {
                                                border-right-width: 1px;
                                            }
                                        `,
                                        headerFunc,
                                        25,
                                        true,
                                        [
                                            "日付",
                                            "食事区分名",
                                            "商品名",
                                            "数量",
                                            "単価",
                                            "金額",
                                            "入金金額",
                                            "備考",
                                        ],
                                        [
                                            "月日",
                                            "区分",
                                            "商品名称",
                                            "食数",
                                            "単価",
                                            "買上額",
                                            "入金額",
                                            "備考",
                                        ],
                                    );
                                    console.log("7050 seikyusho", html)
                                    return html;
                                })
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
                //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
                //$("#printJS").contents().find("html").html()
            })
            .catch(err => {
                console.log(err);
                $.dialogErr({
                    title: "印刷失敗",
                    contents: "請求明細の検索に失敗しました" + "<br/>" + err.message,
                });
            });
        },
    }
}
</script>
