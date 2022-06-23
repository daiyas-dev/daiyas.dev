<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>登録日</label>
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
                    :onChangedFunc=onDateStartChanged
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
                    :onChangedFunc=onDateEndChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05160Grid1"
            ref="DAI05160Grid1"
            dataUrl="/DAI05160/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI05160Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI05160Grid1 .pq-group-icon {
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
    name: "DAI05160",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    watch: {
        "DAI05160Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > 注文データ送信エラー一覧",
            noViewModel: true,
            viewModel: {
                BushoOption: "2",
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                SaveDateStart: null,
                SaveDateEnd: null,
                Customer: "0",
                ShowSyonin: "0",
            },
            DAI05160Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 35,
                freezeCols: 0,
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
                groupModel: [
                ],
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "送信ＩＤ",
                        dataIndx: "送信ＩＤ", dataType: "string",
                        width: 120, minWidth: 75, maxWidth: 120, align: "center",
                    },
                    {
                        title: "部署ＣＤ",
                        dataIndx: "部署ＣＤ", dataType: "string",
                        width: 100, minWidth: 75, maxWidth: 100, align: "center",
                    },
                    {
                        title: "部署名",
                        dataIndx: "部署名", dataType: "string",
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        width: 120, minWidth: 75, maxWidth: 120, align: "center",
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        hidden: false,
                    },
                    {
                        title: "作成日時",
                        dataIndx: "表示用作成日時", dataType: "string",
                        width: 300, minWidth: 100, maxWidth: 300, align: "center",
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI05160Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            vue.conditionChanged();
        },
        onDateStartChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.DateEnd = ms.endOf("month").format("YYYY年MM月DD日");
            } else {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onDateEndChanged: function(code, entity) {
            var vue = this;

            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");

            if (ms.month() != me.month()) {
                vue.viewModel.DateStart = me.startOf("month").format("YYYY年MM月DD日");
            } else {
                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["担当者名", "部署.部署名", "担当者名カナ"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.担当者ＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.担当者ＣＤ + " : " + v.担当者名 + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.担当者ＣＤ;
                    ret.text = v.担当者名;
                    return ret;
                })
                ;

            return list;
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI05160Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            var params = $.extend(true, {}, vue.viewModel);

            //検索パラメータの加工
            //日付を"YYYYMMDD"形式に編集
            params.DateStart = params.DateStart ? moment(params.DateStart, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;
            params.DateEnd = params.DateEnd ? moment(params.DateEnd, "YYYY年MM月DD日").format("YYYY/MM/DD") : null;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            return res;
        },
    }
}
</script>
