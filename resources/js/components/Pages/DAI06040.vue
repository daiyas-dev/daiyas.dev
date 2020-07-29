<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                    :hasNull=true
                />
            </div>
            <div class="col-md-5">
                <label class="text-center mr-2" style="width: auto;">日付</label>
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_DateStart"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label class="text-center ml-2 mr-2" style="width: auto;">～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_DateEnd"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
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
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ UserBushoCd: getLoginInfo().bushoCd }'
                    :isPreload=true
                    :noResearch=true
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
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
        <PqGridWrapper
            id="DAI06040Grid1"
            ref="DAI06040Grid1"
            dataUrl="/DAI06040/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI06040Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06040Grid1 .pq-group-icon {
    display: none !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI06040",
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
                DateStart: moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYYMMDD"),
                DateEnd: moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseCd: vue.viewModel.CourseCd,
                SeikyuCd: vue.viewModel.CustomerCd,
                CustomerCd: vue.viewModel.CustomerCd,
                JuchuCd: vue.viewModel.JuchuCd,
                ProductCd: vue.viewModel.ProductCd,
                ProductKbn: vue.viewModel.ProductKbn,
                UriGenKbn: vue.viewModel.UriGenKbn,
                MealKbn: vue.viewModel.MealKbn,
                IsDaitaiOnly: vue.viewModel.MealKbn == "1",
            };
        },
        dateRange: function() {
            var vue = this;

            var start = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var end = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (!start.isValid() || !end.isValid()) return [];
            if (!start.isSameOrBefore(end)) return [];

            return _.range(0, end.diff(start, "days") + 1)
                    .map(v => moment(vue.viewModel.DateStart, "YYYY年MM月DD日").add("days", v).format("YYYYMMDD"));
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.DateStart || !newVal.DateEnd;
                vue.footerButtons.find(v => v.id == "DAI06040Grid1_Search").disabled = disabled;
            },
        },
        "DAI06040Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI06040Grid1_Print").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "チケット > チケット販売台帳",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                CourseCd: null,
                CourseNm: null,
                SeikyuCd: null,
                SeikyuNm: null,
                CustomerCd: null,
                CustomerNm: null,
                TantoCd: null,
                TantoNm: null,
                JuchuCd: null,
                JuchuNm: null,
                ProductCd: null,
                ProductNm: null,
                ProductKbn: null,
                UriGenKbn: null,
                MealKbn: null,
                IsDaitaiOnly: "0",
            },
            DAI06040Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 35,
                rowHt: 35,
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
                    type: "local",
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 10,
                    dataIndx: ["コース名", "得意先商品名"],
                    showSummary: [false, true],
                    collapsed: [false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 75, minWidth: 75, maxWidth: 75,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.pq_level != 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                        hidden:true,
                        fixed: true,
                    },
                    {
                        title: "得意先（商品）",
                        dataIndx: "得意先商品名",
                        dataType: "string",
                        width: 125, minWidth: 125, maxWidth: 125,
                        render: ui => {
                            switch (ui.rowData.pq_level) {
                                case 0:
                                    return { text: "" };
                                case 1:
                                    return ui;
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        dataIndx: "ROWNUMBER", dataType: "integer",
                        hidden:true,
                    },
                    {
                        title: "日付",
                        dataIndx: "日付",
                        dataType: "string",
                        align: "center",
                        width: 125, minWidth: 125, maxWidth: 125,
                        render: ui => {
                            if (!!ui.rowData.日付) {
                                return { text: moment(ui.rowData.日付).format("YYYY/MM/DD　dd") };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                // return { text: "合計" };
                                switch (ui.rowData.pq_level) {
                                    case 1:
                                        return { text: "合計" }
                                    default:
                                        return { text: "総合計" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "チケット販売SV",
                        dataIndx: "チケット販売SV",
                        dataType: "float",
                        format: "#,###.0",
                        hidden: true,
                        summary: {
                            type: "TotalFloat",
                        },
                    },
                    {
                        title: "チケット券販売",
                        dataIndx: "チケット販売",
                        dataType: "string",
                        align: "right",
                        width: 125, minWidth: 125, maxWidth: 125,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            var valSale = "";
                            if (ui.rowData.チケット販売 * 1 != 0) {
                                valSale = ui.rowData.チケット販売;
                            }
                            if (ui.rowData.チケット販売SV * 1 != 0)
                            {
                                valSale = valSale + "(" + (ui.rowData.チケット販売SV * 1).toFixed(1) + ")";
                            }
                            if (!!ui.rowData.pq_gsummary || !!ui.rowData.pq_grandsummary) {
                                valSale = ui.rowData.チケット販売.replace(",","") * 1;
                                valSale = valSale + "(" + (ui.rowData.チケット販売SV.replace(",","") * 1).toFixed(1) + ")";
                                return { text: valSale };
                            }
                            return { text: valSale };
                        },
                    },
                    {
                        title: "弁当売上SV",
                        dataIndx: "弁当売上SV",
                        dataType: "float",
                        format: "#,###.0",
                        hidden: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "弁当売上",
                        dataIndx: "弁当売上",
                        dataType: "integer",
                        format: "#,###",
                        width: 125, minWidth: 125, maxWidth: 125,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            var valSale = "";
                            if (ui.rowData.弁当売上 * 1 != 0) {
                                valSale = ui.rowData.弁当売上;
                            }
                            if (ui.rowData.弁当売上SV * 1 != 0)
                            {
                                valSale = valSale + "(" + (ui.rowData.弁当売上SV * 1).toFixed(1) + ")";
                            }
                            if (!!ui.rowData.pq_gsummary || !!ui.rowData.pq_grandsummary) {
                                valSale = ui.rowData.弁当売上.replace(",","") * 1;
                                valSale = valSale + "(" + (ui.rowData.弁当売上SV.replace(",","") * 1).toFixed(1) + ")";
                                return { text: valSale };
                            }
                            return { text: valSale };
                        },
                    },
                    {
                        title: "調整SV",
                        dataIndx: "調整SV",
                        dataType: "float",
                        format: "#,###.0",
                        hidden: true,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "調整",
                        dataIndx: "調整",
                        dataType: "integer",
                        format: "#,###",
                        width: 125, minWidth: 125, maxWidth: 125,
                        summary: {
                            type: "TotalInt",
                        },
                        render: ui => {
                            var valSale = "";
                            if (ui.rowData.調整 * 1 != 0) {
                                valSale = ui.rowData.調整;
                            }
                            if (ui.rowData.調整SV * 1 != 0)
                            {
                                valSale = valSale + "(" + (ui.rowData.調整SV * 1).toFixed(1) + ")";
                            }
                            if (!!ui.rowData.pq_gsummary || !!ui.rowData.pq_grandsummary) {
                                valSale = ui.rowData.調整.replace(",","") * 1;
                                valSale = valSale + "(" + (ui.rowData.調整SV.replace(",","") * 1).toFixed(1) + ")";
                                return { text: valSale };
                            }
                            return { text: valSale };
                        },
                    },
                    {
                        title: "チケット残数SV",
                        dataIndx: "チケット残数SV",
                        dataType: "float",
                        format: "#,###.0",
                        hidden: true,
                    },
                    {
                        title: "チケット残数",
                        dataIndx: "チケット残数",
                        dataType: "integer",
                        format: "#,###",
                        width: 125, minWidth: 125, maxWidth: 125,
                        render: ui => {
                            if (!!ui.rowData.pq_gsummary) {
                                return { text: "" };
                            }
                            else
                            {
                                var valSale = "0";
                                valSale = ui.rowData.チケット残数 * 1;
                                valSale = valSale + "(" + (ui.rowData.チケット残数SV * 1).toFixed(1) + ")";
                                return { text: valSale };
                            }
                        },
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI06040Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI06040Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI06040Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI06040Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI06040Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI06040Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI06040Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().startOf("month").format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf("month").format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI06040Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI06040Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity.部署ＣＤ;
            }

            //条件変更ハンドラ
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06040Grid1;
            console.log("6040 conditionChanged")

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            if ((!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid)
                ||
                (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid)) return;

            if (!!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && vue.viewModel.BushoCd != vue.$refs.PopupSelect_Course.selectRow.部署ＣＤ) return;

            if (!!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                console.log("same condition", _.isEqual(grid.prevPostData, vue.searchParams));
                return;
            }

            grid.searchData(vue.searchParams, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI06040Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CourseCd) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }
            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return ret;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI06040Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI06040Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI06040Grid1_Print").disabled = !res.length;

            res = res.filter(r => !!r.コースＣＤ);
            res.forEach(r => {
                    r.コース名 = r.コース名 + (r.処理区分 == "1" ? "" : " (残数のみ)");
                    r.得意先商品名 = r.得意先ＣＤ + " " + r.得意先商品名;
                    // r.日付 = moment(r.日付).format("YYYY/MM/DD") + "　　" + r.曜日;
                });

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                grid.setSelection({ rowIndx: 0, colIndx: 1 });
            }
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
        showDetail: function(rowData) {
            var vue = this;

            var data;
            if (!!rowData) {
                data = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                data = _.cloneDeep(rows[0].rowData);
            }

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                TargetDate: moment(data.入金日付).format("YYYY年MM月DD日"),
                CustomerCd: data.得意先ＣＤ,
                onSaveFunc: () => {
                    var grid = vue.DAI06040Grid1;
                    grid.refreshDataAndView();
                },
            };

            PageDialog.show({
                pgId: "DAI01130",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
                onBeforeClose: (event, ui) => {
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

                    var canClose = !editting && !isEscOnEditor;

                    return canClose;
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
                    font-size: 10pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 25px;
                    text-align: center;
                }
                td {
                    height: 22px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th.blank-cell {
                    border:none;
                }
            `;

            var courseNm;
            var headerFunc = (header, idx, length) => {
                if (header.pq_level == 0)
                {
                    courseNm = header.コース名;
                }
                var TargetDateFrom = moment(vue.searchParams.DateStart, "YYYYMMDD").format("YYYY/MM/DD");
                var TargetDateTo = moment(vue.searchParams.DateEnd, "YYYYMMDD").format("YYYY/MM/DD");

                return `
                    <div class="header">
                        <div class="title" style="float: left; width: 100%">
                            <h3>＊＊チケット販売台帳＊＊</h3>
                        </div>
                        <div style="float: left; width: 100%;">
                            <div style="float: left; width: 100%; text-align: center;">
                                対象期間 ( ${TargetDateFrom}<span> ～ </span>${TargetDateTo} )
                            </div>

                            <div style="float: left; width: 90%;">&nbsp</div>
                            <div style="float: left; width: 10%; text-align: right;">${idx + 1} / ${length}</div>

                            <div style="float: left; width: 100%;">&nbsp</div>

                            <div style="float: left; width: 35%; text-align: left;">配送コース: ${courseNm}</div>
                            <div style="float: left; width: 40%;">&nbsp</div>
                            <div style="float: left; width: 10%; text-align: right;">作成日</div>
                            <div style="float: left; width: 15%; text-align: right;">${moment().format("YYYY/MM/DD")}</div>
                        </div>
                    </div>
                `;
            };

            var styleCustomers =`
                div.header div:not(.title) {
                    font-size: 12px;
                }
                table.DAI06040Grid1 {
                    border-collapse:collapse;
                }
                table.DAI06040Grid1 thead tr {
                    border-top: solid 1px black;
                }
                table.DAI06040Grid1 tbody tr[level="1"].group-row {
                    border-top: solid 1px black;
                }
                table.DAI06040Grid1 tbody tr td {
                    border-bottom: dotted 1px black;
                }
                table.DAI06040Grid1 tbody tr[level="1"].group-summary td {
                    border-bottom: dotted 0px black;
                }
                table.DAI06040Grid1 tbody tr.group-summary td {
                    border-bottom: dotted 0px black;
                }
                table.DAI06040Grid1 tbody tr.grand-summary td {
                    border-bottom: dotted 0px black;
                }
                table.DAI06040Grid1 tbody tr td:nth-child(1) {
                    border-bottom: dotted 0px black;
                }
                table.DAI06040Grid1 thead tr th:nth-child(1) {
                    width: 20%;
                }
                table.DAI06040Grid1 thead tr th:nth-child(2) {
                    width: 20%;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI06040Grid1.generateHtml(
                                styleCustomers,
                                headerFunc,
                                36,
                                [false, true],
                                [false, true],
                                [true, false],
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
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
