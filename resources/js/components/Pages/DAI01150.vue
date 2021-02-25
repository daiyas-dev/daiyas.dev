<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-5">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                    :hasNull=true
                    :disabled=false
                />
            </div>
            <div class="col-md-1">
                <label>締区分</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.SimeKbn readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-5">
                <PopupSelectKeyDown
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: null, UserBushoCd: getLoginInfo().bushoCd, BushoCd: viewModel.BushoCd }"
                    bind="CustomerCd"
                    :buddies='{CustomerNm: "CdNm"}'
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
                    :nameWidth=300
                    :isShowAutoComplete=false
                    :onKeyDownEnterFunc=onCustomerChanged
                    :isRealTimeSearch=false
                    :onAfterChangedFunc=onCustomerChanged
                />
            </div>
            <div class="col-md-1">
                <label>締日</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.SimeDate readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label> </label>
            </div>
            <div class="col-md-5" style="margin-left:10px;">
                得意先コードを入力後Enterキーを押して下さい。
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 365px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <label>入金方法</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.NyukinKind readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>対象期間</label>
            </div>
            <div class="col-md-5">
                <input type="number" class="range"
                    v-model="viewModel.Range"
                    style="width: 60px; padding-right: 0px; text-align: center; border: none; border-radius: 4px;"
                    maxlength="3"
                >
                <label>ヶ月以内</label>
            </div>
            <div class="col-md-1">
                <label>入金サイト</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.NyukinTerm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 align-items-end">
                <div class="d-block">
                    <PqGridWrapper
                        id="DAI01150GridSeikyu"
                        ref="DAI01150GridSeikyu"
                        :options=this.gridSeikyuOptions
                        :autoToolTipDisabled=true
                        :SearchOnCreate=false
                        :SearchOnActivate=false
                        :resizeFunc=resizeGrid
                        classes="ml-0 mr-0 mt-1 mb-0"
                    />
                    <PqGridWrapper
                        id="DAI01150GridNyukin"
                        ref="DAI01150GridNyukin"
                        :options=this.gridNyukinOptions
                        :autoToolTipDisabled=true
                        :SearchOnCreate=false
                        :SearchOnActivate=false
                        :resizeFunc=resizeGrid
                        classes="ml-0 mr-0 mt-1 mb-0"
                    />
                </div>
                <div class="urikakeZan ml-2">
                    <label>売掛残</label>
                    <input class="form-control" style="width: 100px; text-align: right;" type="text" :value=viewModel.UrikakeZan readonly tabindex="-1">
                </div>
            </div>
        </div>
    </form>
</template>

<style>
#DAI01150Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI01150Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI01150Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
label{
    width: 80px;
}
.urikakeZan {
    display: flex;
    align-items: flex-end;
    flex-direction: column;
}
[pgid=DAI01150] .pq-cont-inner.pq-cont-right {
    overflow-x: hidden !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";
import PopupSelectKeyDown from "@vcs/PopupSelectKeyDown.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01150",
    components: {
        "PopupSelectKeyDown": PopupSelectKeyDown,
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
                CustomerCd: vue.viewModel.CustomerCd,
                Range: vue.viewModel.Range,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.CustomerCd;
                vue.footerButtons.find(v => v.id == "DAI01150Grid1_New").disabled = disabled;
                vue.footerButtons.find(v => v.id == "DAI01150Grid1_Search").disabled = disabled;
            },
        },
        "viewModel.Range": {
            handler: function(newVal) {
                var vue = this;
                var gridSeikyu = vue.DAI01150GridSeikyu;
                var gridNyukin = vue.DAI01150GridNyukin;

                var date = !!newVal && newVal > 0
                    ? moment().add("months", newVal * -1)
                    : ""
                    ;
                console.log("filterChanged", date);

                if (!!gridSeikyu) {
                    gridSeikyu.filter({
                        oper: "replace",
                        mode: "AND",
                        rules: [{ dataIndx: "請求日付", condition: "gte", value: date }]
                    });
                }

                if (!!gridNyukin) {
                    gridNyukin.filter({
                        oper: "replace",
                        mode: "AND",
                        rules: [{ dataIndx: "入金日付", condition: "gte", value: date }]
                    });
                }
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 請求入金問合せ",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                CustomerNm: null,
                CourseCd: null,
                CourseNm: null,
                Range: null,
                UrikakeZan: 0,
                SimeKbn: null,
                SimeDate: null,
                NyukinKind: null,
                NyukinTerm: null,
            },
            DAI01150GridSeikyu: null,
            DAI01150GridNyukin: null,
            gridSeikyuOptions: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHt: 25,
                rowHtHead: 25,
                width: 951,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                    render: ui => {
                        ui.cls.push(ui.cellData < 0 ? "cell-negative-value" : "cell-positive-value");
                        return ui;
                    },
                },
                dataModel: {
                    location: "local",
                    data: [],
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
                        title: "請求ID",
                        dataIndx: "請求ＩＤ",
                        dataType: "integer",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        key: true,
                    },
                    {
                        title: "請求日付",
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
                        key: true,
                    },
                    {
                        title: "前月残高",
                        dataIndx: "前月残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "当月入金",
                        dataIndx: "当月入金",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "繰越残高",
                        dataIndx: "繰越残高",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "今回売上",
                        dataIndx: "今回売上",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "消費税",
                        dataIndx: "消費税",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
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
                    },
                    {
                        title: "入金予定日",
                        dataIndx: "入金予定日",
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
                ],
            },
            gridNyukinOptions: {
                selectionModel: { type: "row", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 25,
                rowHt: 25,
                width: 951,
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
                        title: "入金ID",
                        dataIndx: "入金ＩＤ",
                        dataType: "integer",
                        align: "center",
                        width: 100, minWidth: 100, maxWidth: 100,
                        key: true,
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
                        key: true,
                    },
                    {
                        title: "現金",
                        dataIndx: "現金",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "小切手",
                        dataIndx: "小切手",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込",
                        dataIndx: "振込",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振込手数料",
                        dataIndx: "振込手数料",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "振替",
                        dataIndx: "振替",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金券",
                        dataIndx: "金券",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "その他",
                        dataIndx: "その他",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売掛残",
                        dataIndx: "売掛残",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        hidden: true,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!ui.cls) ui.cls = [];
                                ui.cls.push("cell-disabled");
                            } else {
                                return {text: pq.formatNumber(vue.viewModel.UrikakeZan, "#,###")};
                            }
                            return ui;
                        },
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(false, ui.rowData);
                },
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "新規入力", id: "DAI01150Grid1_New", disabled: true, shortcut: "F1",
                    onClick: function () {
                        vue.showDetail(true);
                    }
                },
                { visible: "true", value: "検索", id: "DAI01150Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "明細入力", id: "DAI01150Grid1_Detail", disabled: true,
                    onClick: function () {
                        vue.showDetail(false);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.Range = 10;

            //watcher
            vue.$watch(
                "$refs.PopupSelect_Customer.isValid",
                isValid => {
                    vue.footerButtons.find(v => v.id == "DAI01150Grid1_Search").disabled = !isValid;
                }
            );
            vue.$watch(
                "$refs.DAI01150GridNyukin.selectionRowCount",
                cnt => {
                    console.log("cnt", cnt)
                    vue.footerButtons.find(v => v.id == "DAI01150Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
            vue.viewModel.CustomerCd = null;
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;
            var selectname = $(vue.$el).find(".select-name").val();
            if(selectname==""){
                vue.clearData();
                return;
            }
            console.log("カスタマーチェンジ");//TODO:

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.CustomerCd = entity["Cd"];
            }
            if (!!entity && !!vue.viewModel.CustomerCd) {
                vue.viewModel.BushoCd = entity.部署CD;
                axios.all(
                    [
                        axios.post("/DAI01150/GetCourse", vue.searchParams),
                        axios.post("/DAI01150/GetPaymentInfo", vue.searchParams),
                    ]
                )
                .then(
                    axios.spread((responseCourse, responsePayment) => {
                        vue.viewModel.CourseCd = responseCourse.data.コースＣＤ;
                        vue.viewModel.CourseNm = responseCourse.data.コース名;

                        vue.viewModel.SimeKbn = responsePayment.data.締区分名;
                        vue.viewModel.SimeDate = responsePayment.data.締日１;
                        vue.viewModel.NyukinKind = responsePayment.data.支払方法;
                        vue.viewModel.NyukinTerm = responsePayment.data.支払サイト名 + " " + responsePayment.data.支払日;
                    })
                );
            }

            //条件変更ハンドラ
            console.log("条件変更ハンドラ呼び出し");//TODO:
            vue.conditionChanged(true);
        },

        conditionChanged: function(force, blink) {
            var vue = this;
            var gridSeikyu = vue.DAI01150GridSeikyu;
            var gridNyukin = vue.DAI01150GridNyukin;

            console.log("条件変更ハンドラ カスタマーコード",vue.viewModel.CustomerCd);//TODO:

            if (!gridSeikyu || !gridNyukin || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.CustomerCd) return;

            //検索
            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            if (!!vue.prevParams && _.isEmpty(diff(vue.prevParams, params)) && !force) return;
            vue.prevParams = params;

            //検索中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 検索中…",
            });

            axios.post("/DAI01150/Search", params)
                .then(res => {
                    vue.setSearchResult(res.data, blink);

                    progressDlg.dialog("close");
                    $(vue.$el).find(".range").focus();
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

            //売掛残
            var latest = data.SeikyuDataList[0];
            var nyukin = data.NyukinDataList
                .filter(v => v.入金日付 > (!!latest ? latest.請求日付 : moment().format("YYYY-MM-DD")));

            vue.viewModel.UrikakeZan = (!!latest ? latest.今回請求額 * 1 : 0)
                -
                _.sum(
                    nyukin.map(v => v.現金 * 1
                                    + v.小切手 * 1
                                    + v.振込 * 1
                                    + v.振込手数料 * 1
                                    + v.振替 * 1
                                    + v.金券 * 1
                                    + v.その他 * 1)
                )
                ;
            vue.viewModel.UrikakeZan = Number(vue.viewModel.UrikakeZan).toLocaleString();

            //請求データ
            if (!!isBlink) {
                vue.DAI01150GridSeikyu.blinkDiff(data.SeikyuDataList, true);
            } else {
                vue.DAI01150GridSeikyu.options.dataModel.data = data.SeikyuDataList;
                vue.DAI01150GridSeikyu.refreshDataAndView();
            }

            //入金データ
            if (!!isBlink) {
                vue.DAI01150GridNyukin.blinkDiff(data.NyukinDataList, true);
            } else {
                vue.DAI01150GridNyukin.options.dataModel.data = data.NyukinDataList;
                vue.DAI01150GridNyukin.refreshDataAndView();
            }
            vue.$refs.DAI01150GridNyukin.selectionRowCount = vue.DAI01150GridNyukin.SelectRow().getSelection().length;
        },
        resizeGrid: function(grid) {
            //widget
            var widget = grid.widget();
            var container = widget.closest("div.row");

            //heightを適正に変更
            var oldHeight = widget.outerHeight();

            //body-content
            var content = widget.closest(".body-content");

            //空き領域を計算
            var contentHeight = content.height();
            var elementsHeightSum = _.sum(content.find("form > *")
                .map((i, el) => {
                    if ($(el).hasClass("row")) {
                        return $(el).height() + 4;  //mergin-bottom
                    } else {
                        return $(el).outerHeight(true);
                    }
                })
            )
            - container.outerHeight(true);

            var newHeight = (contentHeight - elementsHeightSum) / 2 - 10;

            if (_.round(newHeight) != _.round(oldHeight)) {
                grid.options.height += (_.round(newHeight) - _.round(oldHeight));
                grid.refresh();
            }
        },
        clearData : function(){
            var vue = this;
            var gridSeikyu = vue.DAI01150GridSeikyu;
            var gridNyukin = vue.DAI01150GridNyukin;
            vue.viewModel.CourseCd = "";
            vue.viewModel.CourseNm = "";
            vue.viewModel.SimeKbn = "";
            vue.viewModel.SimeDate = "";
            vue.viewModel.NyukinKind = "";
            vue.viewModel.NyukinTerm = "";
            gridSeikyu.clearData();
            gridNyukin.clearData();
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
                .filter(v => v.部署CD == vue.viewModel.BushoCd)
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
        showDetail: function(isNew, rowData) {
            var vue = this;
            var grid = vue.DAI01150GridNyukin;

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                CustomerCd: vue.viewModel.CustomerCd,
                TargetDate: moment().format("YYYY年MM月DD日"),
                onSaveFunc: () => {
                    vue.conditionChanged(true, true);
                },
            };

            if (!isNew) {
                var data;
                if (!!rowData) {
                    data = _.cloneDeep(rowData);
                } else {
                    var selection = grid.SelectRow().getSelection();

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    data = _.cloneDeep(rows[0].rowData);
                }

                params.TargetDate = moment(data.入金日付).format("YYYY年MM月DD日");
            }

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
    }
}
</script>
