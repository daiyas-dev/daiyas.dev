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
                <label>請求年月</label>
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
            id="DAI05030Grid1"
            ref="DAI05030Grid1"
            dataUrl="/DAI05030/Search"
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
#DAI05030Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05030Grid1 .pq-group-icon {
    display: none !important;
}
#DAI05030Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI05030Grid1 .pq-td-div span {
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
    name: "DAI05030",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 売掛残高設定処理",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                Shimebi: null,
                TargetDate: null,
                CustomerCd: null,
            },
            DAI05030Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead:30,
                rowHt: 35,
                rowHtSum: 35,
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
                        title: "コード",
                        dataIndx: "請求先ＣＤ", dataType: "string",
                        width: 60, minWidth: 60, maxWidth: 60,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        width: 120, minWidth: 120,maxWidth: 400,
                    },
                    {
                        title: "前月残高",
                        dataIndx: "前月残高", dataType: "integer", format: "#,###",
                        width: 120, minWidth: 120, maxWidth: 120,
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
                        editable: true,
                        width: 120, minWidth: 120, maxWidth: 120,
                        render: ui => {
                            // hide zero
                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05030Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "更新", id: "DAI05030Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
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
        onCustomerChanged: function(code, entity) {
            var vue = this;
            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05030Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            if (!vue.viewModel.TargetDate) return;
            var params = $.extend(true, {}, vue.viewModel);

            //フィルタするパラメータは除外
            delete params.CustomerCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05030Grid1;
            var rules = [];
            //得意先指定
            if (vue.viewModel.CustomerCd != undefined && vue.viewModel.CustomerCd != "") {
                rules.push({ dataIndx: "請求先ＣＤ",   condition: "equal", value: vue.viewModel.CustomerCd * 1 });
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });
            return;
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;

            //ボタン無効化制御
            vue.footerButtons.find(v => v.id == "DAI05030Grid1_Save").disabled = !res.length;

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
        save: function(){
            var vue=this;
            var grid = vue.DAI05030Grid1;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }

            //登録データの作成
            var SaveList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.請求先ＣＤ=r.請求先ＣＤ;
                //SaveItem.前月残高=r.前月残高;
                //SaveItem.今月入金額=r.今月入金額;
                //SaveItem.差引繰越額=r.差引繰越額;
                //SaveItem.今月売上額=r.今月売上額;
                //SaveItem.今月残高=r.今月残高;
                SaveItem.今月残高=r.今月残高===undefined?0:r.今月残高;
                SaveList.push(SaveItem);
            });

            //登録実行
            grid.saveData(
                {
                    uri: "/DAI05030/Save",
                    params: {
                        BushoCd:vue.viewModel.BushoCd,
                        TargetDate:vue.viewModel.TargetDate,
                        ShuseiTantoCd:vue.getLoginInfo()["uid"],
                        SaveList: SaveList,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                        },
                    },
                }
            );
        },
    }
}
</script>
