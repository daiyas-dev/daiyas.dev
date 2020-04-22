<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>処理選択</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="SearchCondition"
                    ref="VueOptions_SearchCondition"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="SearchCondition"
                    :list="[
                        {code: '1', name: '部署全て', label: '1:部署全て'},
                        {code: '2', name: '得意先指定', label: '2:得意先指定'},
                    ]"
                    :onChangedFunc=onChanged
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
                    :onAfterChangedFunc=onChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>発行範囲</label>
            </div>
            <div class="col-md-2">
                <VueOptions
                    id="IssuePeriod"
                    ref="VueOptions_IssuePeriod"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="IssuePeriod"
                    :list="[
                        {code: '1', name: '全期間', label: '1:全期間'},
                        {code: '2', name: 'コース順', label: '2:範囲'},
                    ]"
                    :onChangedFunc=onChanged
                />
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onChanged
                />
                <label>～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onChanged
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
                        {code: '1', name: '顧客カナ順', label: '1:顧客カナ順'},
                        {code: '2', name: '発効日降順', label: '2:発効日降順'},
                    ]"
                    :onChangedFunc=onChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI06010Grid1"
            ref="DAI06010Grid1"
            dataUrl="/DAI06010/Search"
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
#DAI06010Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06010Grid1 .pq-group-icon {
    display: none !important;
}
#DAI06010Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI06010Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI06010Grid1 .pq-td-div span {
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
    name: "DAI06010",
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
            ScreenTitle: "チケット > チケット印刷管理",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                SearchCondition:"2",
                CustomerCd: null,
                IssuePeriod:"2",
                DateStart:null,
                DateEnd:null,
                PrintOrder:"2",
            },
            DAI06010Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 40,
                rowHt: 30,
                freezeCols: 3,
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
                        title: "管理No",
                        dataIndx: "チケット管理番号", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",align:"right",
                        hidden:true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 280, minWidth: 280, maxWidth: 280,
                        tooltip: true,
                    },
                    {
                        title: "発効日付",
                        dataIndx: "F発行日", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "有効期限",
                        dataIndx: "F有効期限", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "印刷日時",
                        dataIndx: "F印刷日時", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,###",　
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "券数",
                        dataIndx: "チケット内数", dataType: "integer", format: "#,###",　
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "サービス",
                        dataIndx: "SV内数", dataType: "integer", format: "#,###",　
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "責任者",
                        dataIndx: "担当者名", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                    },
                    {
                        title: "捨",
                        dataIndx: "廃棄対象",
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
                        hidden: false,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI06010Grid1_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                    }
                },
                { visible: "true", value: "検索", id: "DAI06010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "登録", id: "DAI06010Grid1_Save", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.save();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI06010Grid1_Csv", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI06010Grid1.vue.exportData("csv");
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //日付の初期値 -> 当日
            vue.viewModel.DateStart = moment().startOf('month').format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf('month').format("YYYY年MM月DD日");

            //TODO:開発用に条件を書き込む
            vue.viewModel.CustomerCd="25997";
            vue.viewModel.DateStart = moment("2019/04/01");
            vue.viewModel.DateEnd = moment("2019/04/30");
        },
        onChanged: function(code, entities) {
            var vue = this;
            //ダミーハンドラ
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06010Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            var params = $.extend(true, {}, vue.viewModel);
            //パラメータの書式を調整
            params.DateStart=moment(params.DateStart,"YYYY年MM月DD日").format("YYYY/MM/DD");
            params.DateEnd=moment(params.DateEnd,"YYYY年MM月DD日").format("YYYY/MM/DD");

            grid.searchData(params, false, null, callback);
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
        save()
        {

        },
    }
}
</script>
