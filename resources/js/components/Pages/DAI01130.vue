<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>伝票番号</label>
            </div>
            <div class="col-md-11">
                <VueSelect
                    id="DenpyoNo"
                    ref="VueSelect_DenpyoNo"
                    :vmodel=viewModel
                    bind="DenpyoNo"
                    :list=DenpyoNoList
                    customStyle="{ width: 200px; }"
                    :hasNull=true
                    :onChangedFunc=onDenpyoNoChanged
                />
                <label class="text-center">部署</label>
                <VueSelectBusho
                    :disabled=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>入金日</label>
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
                <label>得意先</label>
            </div>
            <div class="col-md-11">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    :buddies='{ CustomerNm: "CdNm", Address: "住所１", Tel: "電話番号１", Fax: "ＦＡＸ１", Sime: "締日１" }'
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
                    :nameWidth=325
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
                <label class="text-center">締日</label>
                <input class="form-control p-0 text-center" style="width: 50px;" type="text" :value=viewModel.Sime readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-6">
                <input class="form-control text-left label-blue" style="width: 495px;" type="text" :value=viewModel.Address readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-11">
                <label class="text-left" style="width: 50px;">TEL</label>
                <input class="form-control p-0 text-center label-blue" style="width: 150px;" type="text" :value=viewModel.Tel readonly tabindex="-1">
                <label class="text-center" style="width: 50px;">FAX</label>
                <input class="form-control p-0 text-center label-blue" style="width: 150px;" type="text" :value=viewModel.Fax readonly tabindex="-1">
            </div>
        </div>
        <div class="row mt-3 mb-0 pt-3 pl-2 pr-2 pb-0 border border-bottom-0 border-dark" :style='!!IsChild ? "" : "width: 850px;"'>
            <div class="col-md-1">
                <label class="text-center">摘要</label>
            </div>
            <div class="col-md-3">
                <!-- <PopupSelect
                    id="TekiyoSelect"
                    ref="PopupSelect_Tekiyo"
                    :vmodel=viewModel
                    bind="Tekiyo"
                    :list='[
                        "",
                        "１月分入金",
                        "２月分入金",
                        "３月分入金",
                        "４月分入金",
                        "５月分入金",
                        "６月分入金",
                        "７月分入金",
                        "８月分入金",
                        "９月分入金",
                        "１０月分入金",
                        "１１月分入金",
                        "１２月分入金",
                        "翌日分",
                        "翌週分",
                        "翌月分"
                    ]'
                    :isPreload=true
                    :isShowName=false
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=false
                    :noResearch=true
                    :hideSearchButton=true
                    :hideClearButton=true
                    :inputWidth=200
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TekiyoAutoCompleteFunc
                /> -->
                <input type="text" list="tekiyo_list" class="form-control" v-model="viewModel.Tekiyo">
                <datalist id="tekiyo_list">
                    <option value="１月分入金" />
                    <option value="２月分入金" />
                    <option value="３月分入金" />
                    <option value="４月分入金" />
                    <option value="５月分入金" />
                    <option value="６月分入金" />
                    <option value="７月分入金" />
                    <option value="８月分入金" />
                    <option value="９月分入金" />
                    <option value="１０月分入金" />
                    <option value="１１月分入金" />
                    <option value="１２月分入金" />
                    <option value="翌日分" />
                    <option value="翌週分" />
                    <option value="翌月分" />
                </datalist>
            </div>
        </div>
        <div class="row mt-0 mb-0 pt-2 pl-2 pr-2 pb-2 border-left border-right border-dark" :style='!!IsChild ? "" : "width: 850px;"'>
            <div class="col-md-12">
                <PqGridWrapper
                    id="DAI01130GridNyukin"
                    ref="DAI01130GridNyukin"
                    :options=this.gridNyukinOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :setCustomTitle='title => "入金内訳"'
                />
                <PqGridWrapper
                    id="DAI01130GridSeikyu"
                    ref="DAI01130GridSeikyu"
                    :options=this.gridSeikyuOptions
                    :autoToolTipDisabled=true
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                />
            </div>
        </div>
        <div class="row mt-0 mb-0  pt-2 pl-2 pr-2 pb-0 pb-3 border border-top-0 border-dark" :style='!!IsChild ? "" : "width: 850px;"'>
            <div class="col-md-1">
                <label class="text-center">備考</label>
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" v-model="viewModel.Biko">
            </div>
        </div>
        <div class="row" :style='!!IsChild ? "" : "width: 850px;"'>
            <div class="col-md-12">
                <label style="width: 100%; color: red; text-align: right;">{{checkMsg}}</label>
            </div>
        </div>
    </form>
</template>

<style>
[pgid=DAI01130] svg.pq-grid-overlay {
    display: block;
}
[pgid=DAI01130] .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
[pgid=DAI01130] .pq-grid-cell.plus-value {
    color: black;
}
[pgid=DAI01130] .pq-grid-cell.minus-value {
    color: red;
}
[pgid=DAI01130] .title-col {
    background-image: -webkit-linear-gradient(top, rgb(254, 254, 254), rgb(218, 230, 240));
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01130",
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
                CustomerCd: vue.viewModel.CustomerCd,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate || !newVal.CustomerCd;
                vue.footerButtons.find(v => v.id == "DAI01130Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: !!vue.params ? "入金入力" : "日時処理 > 入金入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                TargetDate: null,
                CustomerCd: null,
                CustomerNm: null,
                Address: null,
                Sime: null,
                Tel: null,
                Fax: null,
                Tekiyo: null,
                Biko: null,
                DenpyoNo: null,
            },
            IsChild: false,
            checkMsg: null,
            DAI01130GridNyukin: null,
            DAI01130GridSeikyu: null,
            DenpyoNoList: [],
            CurrentNyukinData: null,
            gridNyukinOptions: {
                selectionModel: { type: "cell", mode: "block", row: true },
                numberCell: { show: false },
                showTitle: true,
                showHeader: true,
                autoRow: false,
                strNoRows: "",
                rowHt: 25,
                rowHtHead: 25,
                width: 220,
                height: 256,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: {
                    location: "local",
                    data: [
                        {kind: "現金", value: 0},
                        {kind: "小切手", value: 0},
                        {kind: "振込", value: 0},
                        {kind: "振替", value: 0},
                        {kind: "チケット入金", value: 0},
                        {kind: "振込料", value: 0},
                        {kind: "手形", value: 0},
                        {kind: "合計", value: 0},
                    ],
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextEdit",
                    onTab: "nextEdit",
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: false,
                    header: false,
                    headerMenu: false,
                },
                summaryData: [],
                mergeCells: [],
                formulas: [
                ],
                colModel: [
                    {
                        title: "種別",
                        dataIndx: "kind",
                        dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                        cls: "title-col",
                        editable: false,
                    },
                    {
                        title: "入金額",
                        dataIndx: "value",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: ui => {
                            return  ui.rowData.kind != "合計";
                        },
                        render: ui => {
                            ui.cls.push(ui.cellData < 0 ? "minus-value" : "plus-value");

                            if (ui.rowData.kind != "合計") return ui;

                            var grid = eval("this");
                            if (!grid.pdata || !grid.pdata.length) return ui;

                            var sum = _.sum(grid.pdata.filter(v => v.kind != "合計").map(v => v.value * 1));
                            ui.rowData.value = sum;
                            ui.cls.push(sum < 0 ? "minus-value" : "plus-value");

                            return { text: sum };
                        }
                    },
                ],
            },
            gridSeikyuOptions: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHt: 25,
                rowHtHead: 25,
                width: 550,
                height: 256,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                    render: ui => {
                        ui.cls.push(ui.cellData < 0 ? "minus-value" : "plus-value");
                        return ui;
                    },
                },
                dataModel: {
                    location: "local",
                    data: [],
                },
                filterModel: {
                    on: false,
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
                        title: "請求日",
                        dataIndx: "請求日付",
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
                        title: "回収予定日",
                        dataIndx: "回収予定日",
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
                        title: "期間売上高",
                        dataIndx: "今回売上額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "回収額",
                        dataIndx: "今回入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "未収残高",
                        dataIndx: "未収残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                ],
            },
        });

        if (!!vue.params) {
            data.viewModel = $.extend(true, data.viewModel, vue.params);
            data.IsChild = true;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "削除", id: "DAI01130Grid1_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.delete();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "再検索", id: "DAI01130Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01130Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            if (!vue.viewModel.TargetDate) {
                vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            }
        },
        activatedFunc: function(vue) {
            vue.IsChild = !!vue.params;
        },
        onDenpyoNoChanged: function(code, entity) {
            var vue = this;

            if (!!entity) {
                console.log("onDenpyoNoChanged", entity);
                vue.viewModel.TargetDate = moment(entity.入金日付).format("YYYY年MM月DD日");
            }

            //条件変更ハンドラ
            vue.conditionChanged(true);
        },
        onTargetDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity) {
                vue.viewModel.BushoCd = entity.部署CD;
            }

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;

            if (!vue.DAI01130GridNyukin || !vue.DAI01130GridSeikyu || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.TargetDate || !vue.viewModel.BushoCd || !vue.viewModel.CustomerCd) return;

            if (!!vue.viewModel.CustomerCd && !vue.$refs.PopupSelect_Customer.isValid) return;

            //検索
            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            if (!!vue.prevParams && _.isEmpty(diff(vue.prevParams, params)) && !force) return;
            vue.prevParams = params;

            //検索中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 検索中…",
            });

            axios.post("/DAI01130/Search", params)
                .then(res => {
                    console.log("/DAI01130/Search", res.data);

                    vue.setSearchResult(res.data);

                    progressDlg.dialog("close");
                    vue.DAI01130GridNyukin.setSelection({ rowIndx: 0, colIndx: 1 });
                })
                .catch(err => {
                    progressDlg.dialog("close");
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "データの検索に失敗しました<br/>",
                    });
                });
        },
        setSearchResult: function(data, isBlink) {
            var vue = this;

            //請求データ
            if (!!isBlink) {
                vue.DAI01130GridSeikyu.blinkDiff(data.SeikyuDataList, true);
            } else {
                vue.DAI01130GridSeikyu.options.dataModel.data = data.SeikyuDataList;
                vue.DAI01130GridSeikyu.refreshDataAndView();
            }

            //伝票番号リスト
            vue.DenpyoNoList = data.DenpyoNoList.map(v => {
                v.code = v.伝票Ｎｏ;
                v.label = v.伝票Ｎｏ + "【" + moment(v.入金日付).format("YYYY年MM月DD日") + "入金】"
                return v;
            });

            //入金データ
            var NyukinData;
            if (!!vue.viewModel.DenpyoNo) {
                NyukinData = data.NyukinData.filter(v => v.伝票Ｎｏ == vue.viewModel.DenpyoNo)[0];
            }


            if (!!NyukinData) {
                vue.CurrentNyukinData = NyukinData;

                //摘要/備考
                vue.viewModel.Tekiyo = NyukinData.摘要;
                vue.viewModel.Biko = NyukinData.備考;

                //入金データ
                var NyukinList = _.cloneDeep(vue.DAI01130GridNyukin.pdata);
                NyukinList.forEach(v => v.value = NyukinData[v.kind]);

                if (!!isBlink) {
                    vue.DAI01130GridNyukin.blinkDiff(NyukinList, true);
                } else {
                    vue.DAI01130GridNyukin.rollback();
                    vue.DAI01130GridNyukin.pdata.forEach(v => v.kind == "手形" ? v.value = NyukinData["値引"] : v.value = NyukinData[v.kind]);
                    vue.DAI01130GridNyukin.refreshDataAndView();
                    vue.DAI01130GridNyukin.commit();
                    vue.DAI01130GridNyukin.refresh();
                }
            } else {
                vue.CurrentNyukinData = null;

                vue.viewModel.Tekiyo = null;
                vue.viewModel.Biko = null;
                vue.viewModel.DenpyoNo = null;

                //入金データ
                vue.DAI01130GridNyukin.rollback();
                vue.DAI01130GridNyukin.pdata.forEach(v => v.value = 0);
                vue.DAI01130GridNyukin.refreshDataAndView();
                vue.DAI01130GridNyukin.commit();
                vue.DAI01130GridNyukin.refresh();
            }

            //請求済チェック
            var CheckSeikyu = data.CheckSeikyu * 1;
            var CheckKaikei = data.CheckKaikei * 1;

            vue.checkMsg = !!CheckSeikyu ? "請求済みです" : !!CheckKaikei ? "会計処理済みです" : "";
            vue.footerButtons.find(v => v.id == "DAI01130Grid1_Delete").disabled = !!CheckSeikyu || !!CheckKaikei;
            vue.footerButtons.find(v => v.id == "DAI01130Grid1_Save").disabled = !!CheckSeikyu || !!CheckKaikei;
            //vue.footerButtons.find(v => v.id == "DAI01130Grid1_Save").disabled = !!CheckKaikei;
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
                .filter(v => !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd ? v.コースＣＤ == vue.viewModel.CourseCd : true)
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
        TekiyoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!_.isObject(dataList[0])) {
                dataList = vue.dataList.map(v => {
                    return { Cd: v, CdNm: v || "【無し】" };
                });
            }

            var list = _.cloneDeep(dataList)
                    .map(v => {
                        var ret = v;
                        ret.value = v.Cd;
                        ret.text = v.CdNm;
                        ret.label = ret.text;
                        return ret;
                    })

            return list;
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI01130GridNyukin;

            var target = {
                "入金日付": vue.searchParams.TargetDate,
                "伝票Ｎｏ": vue.viewModel.DenpyoNo,
                "部署ＣＤ": vue.searchParams.BushoCd,
                "得意先ＣＤ": vue.searchParams.CustomerCd,
                "入金区分": 0,
                "現金": grid.pdata.find(r => r.kind == "現金").value * 1,
                "小切手": grid.pdata.find(r => r.kind == "小切手").value * 1,
                "振込": grid.pdata.find(r => r.kind == "振込").value * 1,
                "バークレー": grid.pdata.find(r => r.kind == "振替").value * 1,
                "その他": grid.pdata.find(r => r.kind == "チケット入金").value * 1,
                "相殺": grid.pdata.find(r => r.kind == "振込料").value * 1,
                "値引": grid.pdata.find(r => r.kind == "手形").value * 1,
                "摘要": vue.viewModel.Tekiyo,
                "備考": vue.viewModel.Biko,
                "請求日付": !!vue.CurrentNyukinData ? vue.CurrentNyukinData.請求日付 : "",
                "予備金額１": 0,
                "予備金額２": 0,
                "予備ＣＤ１": 0,
                "予備ＣＤ２": 0,
                "修正日": !!vue.CurrentNyukinData ? vue.CurrentNyukinData.修正日 : "",
                "修正担当者ＣＤ": vue.getLoginInfo().uid,
            };

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI01130/Save",
                    params: {
                        Target: target,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });

                                vue.setSearchResult(res.current, true);
                            } else {
                                if (!!vue.IsChild && !!vue.params.onSaveFunc) {
                                    vue.params.onSaveFunc(vue.viewModel);
                                    $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                } else {
                                    vue.setSearchResult(res.current, false);
                                }
                            }

                            return false;
                        },
                    },
                }
            );
        },
        delete: function() {
            var vue = this;
            var grid = vue.DAI01130GridNyukin;

            var target = {
                "入金日付": vue.searchParams.TargetDate,
                "伝票Ｎｏ": vue.viewModel.DenpyoNo,
                "部署ＣＤ": vue.searchParams.BushoCd,
                "得意先ＣＤ": vue.searchParams.CustomerCd,
                "入金区分": 1,
                "現金": grid.pdata.find(r => r.kind == "現金").value * 1,
                "小切手": grid.pdata.find(r => r.kind == "小切手").value * 1,
                "振込": grid.pdata.find(r => r.kind == "振込").value * 1,
                "バークレー": grid.pdata.find(r => r.kind == "振替").value * 1,
                "その他": grid.pdata.find(r => r.kind == "チケット入金").value * 1,
                "相殺": grid.pdata.find(r => r.kind == "振込料").value * 1,
                "値引": grid.pdata.find(r => r.kind == "手形").value * 1,
                "摘要": vue.viewModel.Tekiyo,
                "備考": vue.viewModel.Biko,
                "請求日付": !!vue.CurrentNyukinData ? vue.CurrentNyukinData.請求日付 : "",
                "予備金額１": 0,
                "予備金額２": 0,
                "予備ＣＤ１": 0,
                "予備ＣＤ２": 0,
                "修正日": !!vue.CurrentNyukinData ? vue.CurrentNyukinData.修正日 : "",
                "修正担当者ＣＤ": vue.getLoginInfo().uid,
            };

            //削除実行
            grid.saveData(
                {
                    uri: "/DAI01130/Delete",
                    params: {
                        Target: target,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: true,
                        title: "入金データ削除確認",
                        message: "入金データを削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });

                                vue.setSearchResult(res.current, true);
                            } else {
                                if (!!vue.IsChild && !!vue.params.onSaveFunc) {
                                    vue.params.onSaveFunc(vue.viewModel);
                                    $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                } else {
                                    vue.setSearchResult(res.current, false);
                                }
                            }

                            return false;
                        },
                    },
                }
            );
        },
    }
}
</script>
