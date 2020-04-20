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
        <div class="row">
            <div class="col-md-7">
                <label>前チケット番号</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.LastTicketNo readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>印刷枚数</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.PrintNum>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>発行日付</label>
            </div>
            <div class="col-md-8">
                <VueCheck
                    id="VueCheck_IsPrintIssueDate"
                    ref="VueCheck_IsPrintIssueDate"
                    :vmodel=viewModel
                    bind="IsPrintIssueDate"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '印刷なし', label: '印刷あり'},
                        {code: '1', name: '印刷あり', label: '印刷あり'},
                    ]"
                    :onChangedFunc=onIncNoJissekiChanged
                />
                <DatePickerWrapper
                    id="IssueDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="IssueDate"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>有効期間</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.ValidPeriod>ヶ月
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>有効期限</label>
            </div>
            <div class="col-md-8">
                <VueCheck
                    id="VueCheck_IsPrintExpireDate"
                    ref="VueCheck_IsPrintExpireDate"
                    :vmodel=viewModel
                    bind="IsPrintExpireDate"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '印刷なし', label: '印刷あり'},
                        {code: '1', name: '印刷あり', label: '印刷あり'},
                    ]"
                    :onChangedFunc=onIncNoJissekiChanged
                />
                <DatePickerWrapper
                    id="ExpireDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="ExpireDate"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI06020Grid1"
            ref="DAI06020Grid1"
            dataUrl="/DAI06020/Search"
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
#DAI06020Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06020Grid1 .pq-group-icon {
    display: none !important;
}
#DAI06020Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI06020Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI06020Grid1 .pq-td-div span {
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
    name: "DAI06020",
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
            ScreenTitle: "チケット > チケット印刷",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                CustomerCd: null,
                PrintNum:"5",
                LastTicketNo:null,
                IssueDate: null,
                IsPrintIssueDate:"1",
                ValidPeriod:"12",
                ExpireDate: null,
                IsPrintExpireDate:"1",
            },
            DAI06020Grid1: null,
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
                freezeCols: 2,
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
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 230, minWidth: 230, maxWidth: 230,
                        tooltip: true,
                    },
                    {
                        title: "得意先名カナ",
                        dataIndx: "得意先名カナ", dataType: "string",
                        width: 180, minWidth: 180, maxWidth: 180,
                    },
                    {
                        title: "郵便番号",
                        dataIndx: "郵便番号", dataType: "string",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "住所１",
                        dataIndx: "住所１", dataType: "string",
                        width: 230, minWidth: 230, maxWidth: 230,
                        tooltip: true,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号1", dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "売現区分",
                        dataIndx: "売掛現金区分", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "締区分",
                        dataIndx: "締区分", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "締日１",
                        dataIndx: "締日１", dataType: "string",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI06020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI06020Grid1_Csv", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI06020Grid1.vue.exportData("csv");
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            //TODO:
            //vue.viewModel.CourseCd=101;
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06020Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            var params = $.extend(true, {}, vue.viewModel);
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
                .filter(v => (!!vue.viewModel.BushoCd) ? (v.部署CD == vue.viewModel.BushoCd) : true)
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
    }
}
</script>
