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
                    :params="{ BushoCd: !!viewModel.CourseCd ? viewModel.BushoCd : null, CourseCd: viewModel.CourseCd, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                        { title: "コースCD", dataIndx: "コースＣＤ", dataType: "string", align: "left", width: 100, maxWidth: 100, minWidth: 100, },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 200, maxWidth: 200, minWidth: 200, },  
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
<!--
                <label class="text-center ml-2 mr-2" style="width: auto;">～</label>
                <PopupSelect
                    id="CustomerEndSelect"
                    ref="PopupSelect_CustomerEnd"
                    :vmodel=viewModel
                    bind="CustomerEndCd"
                    buddy="CustomerEndNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: null }"
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
                    :inputWidth=150
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
-->
            </div>
        </div>
<!--
        <div class="row">
            <div class="col-md-1">
                <label>入金種別</label>
            </div>
            <div class="col-md-11">
                <VueCheckList
                    id="NyukinKind"
                    :vmodel=viewModel
                    bind="NyukinKind"
                    :isGetName=true
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 10px;"
                    :list="[
                        {code: '1', name: '現金', label: '現金'},
                        {code: '2', name: '小切手', label: '小切手'},
                        {code: '3', name: '振込', label: '振込'},
                        {code: '4', name: '振替', label: '振替'},
                        {code: '5', name: 'チケット入金', label: 'チケット入金'},
                        {code: '6', name: '振込料', label: '振込料'},
                        {code: '7', name: '値引', label: '値引'},
                    ]"
                    :onChangedFunc=onNyukinKindChanged
                />
            </div>
        </div>
-->
        <PqGridWrapper
            id="DAI01140Grid1"
            ref="DAI01140Grid1"
            dataUrl="/DAI01140/Search"
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
#DAI01140Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI01140Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI01140Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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
    name: "DAI01140",
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
                vue.footerButtons.find(v => v.id == "DAI01140Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 入金明細問合せ",
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
            DAI01140Grid1: null,
            ProductList: [],
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
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                        hidden: true,
                        hiddenOnExport: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        hidden: true,
                        hiddenOnExport: true,
                    },
                    {
                        title: "担当者ＣＤ",
                        dataIndx: "担当者ＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                        hidden: true,
                        hiddenOnExport: true,
                    },
                    {
                        title: "担当者名",
                        dataIndx: "担当者名",
                        dataType: "string",
                        width: 100, minWidth: 100,
                        hidden: true,
                        hiddenOnExport: true,
                    },
                    {
                        title: "<span>CD</span>",
                        dataIndx: "得意先ＣＤ",
                        dataType: "integer",
                        width: 65, minWidth: 65, maxWidth: 65,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                return { text: "合計" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "伝票No",
                        dataIndx: "伝票Ｎｏ",
                        dataType: "integer",
                        width: 65, minWidth: 65, maxWidth: 65,
                    },
                    {
                        title: "入金日付",
                        dataIndx: "入金日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            if (!!ui.Export && !ui.rowData.pq_grandsummary) {
                                return { text: moment(ui.rowData[ui.dataIndx]).format("YYYY/MM/DD") };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "入金種別",
                        dataIndx: "入金種別",
                        dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "入金金額",
                        dataIndx: "入金金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "備考",
                        dataIndx: "備考",
                        dataType: "string",
                        width: 100, minWidth: 100,
                    },
                    {
                        title: "<span>CD</span>",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        width: 150, minWidth: 150,
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
                { visible: "true", value: "明細入力", id: "DAI01140Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "検索", id: "DAI01140Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01140Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01140Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01140Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01140Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01140Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01140Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI01140Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01140Grid1_Detail").disabled = cnt == 0 || cnt > 1;
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
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01140Grid1;
            console.log("1140 conditionChanged")

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
            var grid = vue.DAI01140Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.NyukinKind) {
                rules.push({ dataIndx: "入金種別", condition: "range", value: vue.viewModel.NyukinKind });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd;
            return ret;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            var data = _.cloneDeep(res)
                .filter(v => _.some(["現金", "小切手", "振込", "バークレー","その他", "相殺","値引"], k => v[k] != 0));

            var group = _.groupBy(data, v => v.得意先ＣＤ);
            var sort = _.uniq(data.map(v => v.得意先ＣＤ));

            var list = _(group).keys()
                .sortBy(k => _.findIndex(sort, v => v == k))
                .map(k => group[k])
                .flatten()
                .value();

            // var dataList = _.flatten(
            //     _.cloneDeep(res)
            var dataList = _.flatten(list
                .map(v => {
                    var pick = kind => {
                        if (!!v[kind] && v[kind] != "0") {
                            var data = _.cloneDeep(v);
                            data.入金種別 = kind == "現金" ? "現金" :
                                           kind == "小切手" ? "小切手" :
                                           kind == "振込" ? "振込" :
                                           kind == "バークレー" ? "振替" :
                                           kind == "その他" ? "チケット入金" :
                                           kind == "相殺" ? "振込料" :
                                           kind == "値引" ? "値引" : ""
                                           ;
                            data.入金金額 = data[kind];

                            return data;
                        } else {
                            return null;
                        }
                    }

                    var ret = [
                        "現金",
                        "小切手",
                        "振込",
                        "バークレー",
                        "その他",
                        "相殺",
                        "値引",
                    ].map(v => pick(v)).filter(v => !!v);

                    return ret;
                })
            );

            vue.footerButtons.find(v => v.id == "DAI01140Grid1_CSV").disabled = !dataList.length;
            vue.footerButtons.find(v => v.id == "DAI01140Grid1_Excel").disabled = !dataList.length;
            vue.footerButtons.find(v => v.id == "DAI01140Grid1_Print").disabled = !dataList.length;

            return dataList;
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
                    var grid = vue.DAI01140Grid1;
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
                    margin-bottom: 10px;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                    padding-left: 100px;
                    padding-right: 100px;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    border: solid 1px black;
                    border-radius: 6px;
                    background-color: cyan;
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
                    height: 15.5px;
                    text-align: center;
                }
                td {
                    height: 15.5px;
                    white-space: nowrap;
                    overflow: hidden;
                }
            `;

            var headerFunc = (chunk, idx, length) => {
                return `
                    <div class="title">
                        <h3> 入金明細表 </h3>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 8%;">日付</th>
                                <th style="width: 34%;" colspan="3">
                                    ${moment(vue.viewModel.DateStart, "YYYYMMDD").format("YYYY年MM月DD日")}
                                    ～
                                    ${moment(vue.viewModel.DateEnd, "YYYYMMDD").format("YYYY年MM月DD日")}
                                </th>
                                <th style="width: 68%;" colspan="7"></th>
                            </tr>
                            <tr>
                                <th style="width: 8%;">コース</th>
                                <th style="width: 10%;">${!!vue.viewModel.CourseCd ? vue.viewModel.CourseCd : ""}</th>
                                <th style="width: 24%;" colspan="2">${!!vue.viewModel.CourseNm ? vue.viewModel.CourseNm : ""}</th>
                                <th style="width: 68%;" colspan="7"></th>
                            </tr>
                            <tr>
                                <th style="width: 8%;">担当者</th>
                                <th style="width: 10%;">${!!vue.viewModel.TantoCd ? vue.viewModel.TantoCd : ""}</th>
                                <th style="width: 21%;" colspan="2">${!!vue.viewModel.TantoNm ? vue.viewModel.TantoNm : ""}</th>
                                <th style="width: 21%;" colspan="2"></th>
                                <th style="width: 8%;">作成日</th>
                                <th style="width: 16%;" colspan="2">${moment().format("YYYY年MM月DD日")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 8%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01140Grid1.generateHtml(
                                `
                                    table.header-table tr th{
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table tr:nth-child(1) th:nth-child(3){
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table tr:nth-child(2) th:nth-child(4){
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table tr:nth-child(3) th:nth-child(4){
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table tr:nth-child(3) th:last-child{
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01140Grid1 tr th ,
                                    table.DAI01140Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(2),
                                    table.DAI01140Grid1 tr td:nth-child(2) {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01140Grid1 tr th:last-child,
                                    table.DAI01140Grid1 tr td:last-child {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01140Grid1 tr:last-child td {
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(2),
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(4),
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(5),
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(8),
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(9) {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(9) {
                                        border-right-width: 1px;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(1) {
                                        width: 7.0%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(2) {
                                        width: 21.5%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(3) {
                                        width: 8.0%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(4) {
                                        width: 11.5%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(7) {
                                        width: 14.0%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(8) {
                                        width: 4.5%;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(9) {
                                        width: 14.0%;
                                    }
                                    table.DAI01140Grid1 tr td {
                                        padding-left: 5px;
                                        padding-right: 5px;
                                    }
                                    table.DAI01140Grid1 thead span {
                                        color: transparent;
                                    }
                                    table.DAI01140Grid1 tr th:nth-child(2),
                                    table.DAI01140Grid1 tr th:last-child {
                                        text-align: left;
                                        padding-left: 20px;
                                    }
                                    table.DAI01140Grid1 tr.grand-summary td:nth-child(2) {
                                        letter-spacing: 2.0em;
                                    }
                                `,
                                headerFunc,
                                49,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 ; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
