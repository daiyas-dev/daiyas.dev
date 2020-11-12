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
            <div class="col-md-3">
                <label>締区分</label>
                <VueOptions
                    id="SimeKbn"
                    ref="VueOptions_SimeKbn"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SimeKbn"
                    buddy="SimeKbnNm"
                    :list="[
                        {code: '0', name: '日締', label: '日締'},
                        {code: '1', name: '週締', label: '週締'},
                        {code: '2', name: '月締', label: '月締'},
                    ]"
                    :onChangedFunc=onSimeKbnChanged
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
                    :format=TargetDateFormat
                    :dayViewHeaderFormat=TargetDateDayViewHeaderFormat
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
            <div class="col-md-4">
                <label style="width: unset;">{{TargetDateMsg}}</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>締日</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="SimeDate"
                    ref="VueMultiSelect_SimeDate"
                    :vmodel=viewModel
                    bind="SimeDateArray"
                    uri="/DAI07050/GetSimeDateList"
                    :params='{ BushoCd: searchParams.BushoCd, TargetDate: searchParams.TargetDate }'
                    :hasNull=true
                    :onChangedFunc=onSimeDateChanged
                    :ParamsChangedCheckFunc=SimeDateParamsChangedCheckFunc
                    :disabled=SimeDateDisabled
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>印字金額</label>
            </div>
            <div class="col-md-3">
                <VueOptions
                    id="ValueKind"
                    ref="VueOptions_ValueKind"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="ValueKind"
                    :list="[
                        {code: '0', name: '請求金額', label: '請求金額'},
                        {code: '1', name: 'お買上げ金額', label: 'お買上げ金額'},
                    ]"
                    :onChangedFunc=onValueKindChanged
                />
            </div>
            <div class="col-md-2">
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
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
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
            <div class="col-md-6">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, CourseCd: viewModel.CourseCd, KeyWord: null }"
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
            <div class="col-md-1">
                <label style="width: unset;">空領収証発行</label>
            </div>
            <div class="col-md-3">
                <label class="mr-1" style="width: unset;">枚数:</label>
                <input type="number" class="form-control text-right" style="width: 60px;" v-model="EmptyPrintPages" min=1 max=99>
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
<style scoped>
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    position: relative;
    right: -10px;
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
                SimeKbn: vue.viewModel.SimeKbn,
                TargetDate: vue.calcTargetDate(),
                CourseCd: vue.viewModel.CourseCd,
                CustomerCd: vue.viewModel.CustomerCd,
                UpdateUser: vue.getLoginInfo().uid,
                EmptyPrintPages: vue.EmptyPrintPages,
                EmptyPrintCount: vue.EmptyPrintPages * 5,
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
            ScreenTitle: "個人宅 > 領収証発行",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SimeKbn: 0,
                SimeKbnNm: null,
                TargetDate: null,
                SimeDateArray: [],
                CourseCd: null,
                CourseNm: null,
                CustomerCd: null,
                CustomerNm: null,
                SearchOptions: [],
            },
            DAI07050Grid1: null,
            SimeDateDisabled: true,
            TargetDateMsg: null,
            TargetDateFormat: "YYYY年MM月DD日",
            TargetDateDayViewHeaderFormat: "YYYY年MM月",
            BushoInfo: null,
            EmptyPrintPages: 1,
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
                        { dataIndx: "コースＣＤ", dir: "up" },
                        { dataIndx: "ＳＥＱ", dir: "up" },
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
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200,
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
                        title: "請求日付",
                        dataIndx: "請求日付",
                        dataType: "date",
                        format: "yy/mm/dd",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
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
                        title: "請求金額",
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
                        title: "お買上げ金額",
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
                        title: "締日",
                        dataIndx: "締日",
                        dataType: "string",
                        hidden: true,
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
                { visible: "true", value: "検索", id: "DAI07050Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI07050Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI07050Grid1_Detail", disabled: true, shortcut: "Enter",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "印刷", id: "DAI07050Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "空領収証<br>印刷", id: "DAI07050Grid1_PrintEmpty", disabled: false, shortcut: "F10",
                    onClick: function () {
                        var params = _.cloneDeep(vue.searchParams);
                        params.noCache = true;
                        params.UpdateUser = vue.getLoginInfo().uid;

                        axios.post("/DAI07050/GetSeikyuNo", params)
                            .then(res => {
                                console.log("7050 GetSeikyuNo");
                                var current = res.data.current;
                                var list = _.range(current - params.EmptyPrintCount, current)
                                    .map(v => {
                                        return {
                                            "請求日付": params.TargetDate,
                                            "請求番号": v + 1,
                                        };
                                    });
                                vue.print(list);
                            })
                            .catch(err => {
                                $.dialogErr({
                                    title: "発番失敗",
                                    contents: "請求番号の発番に失敗しました",
                                });
                            });
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI07050Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI07050Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );

            //初期フィルタ
            vue.filterChanged();
        },
        onBushoChanged: function(code, entity, entities) {
            var vue = this;
            console.log("7050 bushoChanged")

            if (!!entity) {
                vue.BushoInfo = entity.info;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimeKbnChanged: function() {
            var vue = this;

            //請求日付DatePicker option変更
            vue.TargetDateFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年MM月" : "YYYY年MM月DD日";
            vue.TargetDateDayViewHeaderFormat = vue.viewModel.SimeKbn == "2" ? "YYYY年" : "YYYY年MM月";

            //締日MultiSelect状態変更
            vue.SimeDateDisabled = vue.viewModel.SimeKbn != "2";

            //フィルタ変更ハンドラ
            vue.filterChanged();

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onTargetDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onSimeDateChanged: function(entity, entities) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        SimeDateParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BushoCd && !!newVal.TargetDate;
            return ret;
        },
        onValueKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI07050Grid1;

            //フィルタ変更ハンドラ
            vue.filterChanged();
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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI07050Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate) return;

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
            var grid = vue.DAI07050Grid1;

            if (!grid) return;

            var rules = [];

            //請求日付
            if (vue.viewModel.SimeKbn == "2") {
                if (!!vue.viewModel.SimeDateArray.length) {
                    var crules = vue.viewModel.SimeDateArray.map(d => {
                        var date;
                        if (d.code == 99) {
                            date = moment(vue.searchParams.TargetDate).endOf("month").format("YYYY-MM-DD 00:00:00.000");
                        } else {
                            date = moment(vue.searchParams.TargetDate).startOf("month").add(d.code - 1, "day").format("YYYY-MM-DD 00:00:00.000");
                        }
                        return { condition: "equal", mode: "OR", value: date };
                    });
                    rules.push({ dataIndx: "請求日付", crules: crules });
                }
            }

            //マイナスも出力
            if (vue.viewModel.ValueKind == "0" && !vue.viewModel.SearchOptions.includes("1")) {
                rules.push({
                    dataIndx: "今回請求額",
                    condition: "great",
                    value: "0"
                });
            }

            //コース
            if (!!vue.viewModel.CourseCd && vue.$refs.PopupSelect_Course.isValid) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }

            //請求先
            if (!!vue.viewModel.CustomerCd && vue.$refs.PopupSelect_Customer.isValid) {
                rules.push({ dataIndx: "請求先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

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
        calcTargetDate: function() {
            var vue = this;

            var ret;
            vue.TargetDateMsg = null;

            switch (vue.viewModel.SimeKbn)
            {
                case "0":   //日締
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    break;
                case "1":   //週締
                    //該当週の土曜
                    var mt = moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6);
                    ret = mt.format("YYYYMMDD");
                    if (ret != moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")) {
                        vue.TargetDateMsg = mt.format("YYYY年MM月DD日") + "扱いで処理されます";
                    }
                    break;
                case "2":
                    //該当月
                    ret = moment(vue.viewModel.TargetDate, "YYYY年MM月").startOf("month").format("YYYYMMDD");
                    break;
            }

            return ret;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI07050Grid1;

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
                IsSeikyuOutput: true,
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                CustomerCd: data.請求先ＣＤ,
                CustomerNm: data.得意先名,
                TargetDate: moment(data.請求日付).format("YYYY年MM月DD日"),
                DateStart: moment(data.請求日範囲開始).format("YYYY年MM月DD日"),
                DateEnd: moment(data.請求日範囲終了).format("YYYY年MM月DD日"),
                SimeDate: data.締日１,
                CourseCd: data.コースＣＤ,
                CourseNm: data.コース名,
            };

            PageDialog.show({
                pgId: "DAI02021",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 900,
                height: 725,
            });
        },
        print: function(data) {
            var vue = this;
            var grid = vue.DAI07050Grid1;

            if (!!vue.viewModel.BushoCd && !vue.BushoInfo) {
                var entity = vue.$refs.VueSelectBusho.$refs.BushoCd.entities.find(v => v.code == vue.viewModel.BushoCd);

                if (!entity) return
                vue.BushoInfo = entity.info;
            }

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
                	font-size: 13pt;
                	font-weight: bold;
                	letter-spacing: 16px;
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                .header-seikyu-no{
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    margin-top: 3px;
                    padding-top: 3px;
                    text-align: center;
                    font-size: 11pt;
                }
                span {
                    padding-left: 8px;
                }
                div.header-tokuisaki {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    padding-top: 10px;
                    padding-bottom: 3px;
                    margin-top: 3px;
                    margin-bottom: 3px;
                    font-size: 10pt;
                    font-weight: bold;
                    height: 15px;
                }
                div.header-tokuisaki > div {
                    font-size: 12pt;
                }
				#busho-inkan {
                    -webkit-print-color-adjust: exact;
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-image: url(${window.location.origin}/images/BushoStamp/${vue.BushoInfo.部署CD}.png);
                    margin-left: 20px;
                    background-position-x: right;
                    margin-right: 250px;
                    background-size: 52px;
                }
                table.header-table tbody th {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 10pt;
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
                div.inkan:first-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 0px;
                    margin: 0px;
                    margin-left: 40px;
                    padding-top: 2px;
                    text-align: center;
                    height: 15px !important;
                }
                div.inkan:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                    margin-left: 40px;
                    height: 55px !important;
                }
                td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    padding-top: 20px;
                }
                table.DAI07050Grid1 tbody tr:nth-of-type(5n) td{
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    padding-top: 20px;
                }
                tr:first-child > td {
                    padding-top: 8px;
                }
                table.DAI07050Grid1 {
                    margin-top: 13px;
                }
                div.header,
                div.header > div {
                    margin: 0px;
                }
                div.tel-no {
                    margin-bottom: 10px;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-top: 0px !important;
                    margin-bottom: 0px !important;
                    margin-right: 50px !important;
                    margin-left: 50px !important;
                }
              `;

            var targetData = data || grid.pdata;

            var target = targetData.map(r => {
                var layout = `
                    <div>
                        <div class="header">
                            <div style="float: left; width: 80%; height: 85px;">
                                <div style="clear: left;">
                                    <div style="float: left; width: 43%; font-size: 11pt;">
                                        ${!!r.請求先ＣＤ ? r.請求先ＣＤ + " - " + r.コースＣＤ : ""}
                                    </div>
                                    <div class="header-title" style="float: left; width: 38%; text-align: center; margin-left:20px;">
                                        領収証
                                    </div>
                                </div>
                                <div style="clear: left;">
                                    <div class="header-tokuisaki" style="clear: left; width: 58%; margin-left: 20px;">
                                        <div style="float: left; width: 80%;">
                                            ${r.得意先名 || ""}
                                        </div>
                                        <div style="float: left; width: 15%; text-align: right; padding-right: 5px;">様
                                        </div>
                                    </div>
                                    <table class="header-table" style="clear: left; float: left; border-width: 0px; margin-bottom: 10px; margin-left: 80px; width: 46%;">
                                        <tbody>
                                            <tr>
                                                <th style="width: 25%"> 金 額 </th>
                                                <th style="width: 75%; font-size: 15pt;">${!!r.請求先ＣＤ ? "¥" + (pq.formatNumber(vue.viewModel.ValueKind == "0" ? r.今回請求額 : r.今回売上額, "#,##0")) + "-" : ""}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div style="padding-top: 18px; height: 30px; padding-left:293px; font-size: 11pt;">
                                        <span/><span/>${!!r.請求先ＣＤ ? "（" + moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6).format("YYYY/MM/DD") + " 締）" : ""}
                                    </div>
                                </div>
                            </div>
                            <div style="float: left; width: 20%">
                                <div class="header-seikyu-no">
                                    <span/>${
                                        !!r.請求先ＣＤ
                                            ? ("000000000" + r.請求番号).slice(-9)
                                            : (moment(r.請求日付).format("YYMMDD") + " - " + ("000" + r.請求番号).slice(-3))
                                    }
                                </div>
                                <div style="margin-top: 10px;">
                                    <span style="padding-left: 40px; letter-spacing: 0.8em;"> 年 月 日</span>
                                </div>
                                <div></div>
                            </div>
                            <div style="clear: left; width: 100%">
                                <div style="float: left; width: 60%;">
                                    <div style="margin-left: 100px;">
                                        但し　お弁当代として（軽減税率対象）
                                        <br>
                                        <span/><span/><span/><span/><span/>上記金額正に領収いたしました。
                                    </div>
                                    <div id="busho-inkan">
                                        <div style="margin: 0px">
                                            ${vue.BushoInfo.会社名称.split("食品")[0]}
                                        </div>
                                        <div style="margin: 0px">
                                            ${vue.BushoInfo.会社名称.split("食品")[1].replace(/\s+/g, "")}
                                        </div>
                                        <div>
                                            <span/>〒
                                            <span/>${vue.BushoInfo.郵便番号}
                                        </div>
                                        <div>
                                            <span/>${vue.BushoInfo.住所}
                                        </div>
                                        <div class="tel-no">
                                            <span/><span/>Tel.
                                            <span/>${vue.BushoInfo.電話番号}
                                        </div>
                                    </div>
                                </div>
                                <div style="float: left; width: 40%">
                                    <div style="float: left; width: 40%; margin-top: 5px;">
                                        <div style="clear: left; height:20px;" class="inkan">
                                            取扱者印
                                        </div>
                                        <div style="clear: left; height:60px;" class="inkan">
                                        </div>
                                    </div>
                                    <div style="float: left; width: 60%">
                                        <div style="clear: left; height:50px;"></div>
                                        <div style="clear: left; padding-left: 5px;">
                                            取扱者印なき<br>ものは無効
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div>
                `;

                return { contents: layout };
            });

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>").append(
                        grid.generateHtmlFromJson(
                            target,
                            ``,
                            null,
                            5,
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

            //印鑑画像のロード待ち
            $("<img>")
                .attr("src", location.origin + "/images/BushoStamp/" + vue.BushoInfo.部署CD + ".png")
                .on("load", () => printJS(printOptions))
                .on("error", () => {
                    printOptions.printable = printOptions.printable.replace(/^.+background-image:.+;\n/g, "");
                    printJS(printOptions);
                });

            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
