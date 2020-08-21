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
            <div class="col-md-1">
                <label>配送日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
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
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseCd"
                    ref="PopupSelect_CourseCd"
                    :vmodel=viewModel
                    bind="CourseCd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd }'
                    :isPreload=true
                    :dataListReset=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 0 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI07020Grid1"
            ref="DAI07020Grid1"
            dataUrl="/DAI07020/Search"
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
#DAI07020Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI07020Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI07020Grid1 .pq-grid-cell.holiday {
    color: red;
}
#DAI07020Grid1 .info-col > * {
    border-style: solid;
    border-color: #a8c4dc;
    border-width: 0px;
    border-right-width: 1px;
}
#DAI07020Grid1 .info-col > *:last-child {
    border-right-width: 0px;
}
#DAI07020Grid1 .biko-cell {
    border-style: solid;
    border-color: #a8c4dc;
    border-width: 0px;
    border-top-width: 1px;
}
#DAI07020Grid1 .pq-grid-cell {
    align-items: flex-start;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI07020",
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
                TargetDateFrom: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(1).format("YYYYMMDD"),
                TargetDateTo: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").day(6).format("YYYYMMDD"),
                CourseCd: vue.viewModel.CourseCd,
            };
        },
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate;
                vue.footerButtons.find(v => v.id == "DAI07020Grid1_Search").disabled = disabled;
            },
        },
        "DAI07020Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI07020Grid1_Printout").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "個人宅 > 個人配送集計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                TargetDateFrom: null,
                TargetDateTo: null,
                CourseCd: null,
                CourseNm: null,
            },
            BushoInfo: null,
            DAI07020Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: false, title: "No.", resizable: false, },
                autoRow: true,
                stripeRows: true,
                freezeCols: 5,
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
                    header: false,
                    grandSummary: false,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "順",
                        dataIndx: "順", dataType: "integer",
                        width: 45, maxWidth: 45, minWidth: 45,
                        render: ui => {
                            return { text: ui.rowData.順 + "<br>" + "<br>" + "<br>" };
                        },
                    },
                    {
                        title: "得意先",
                        dataIndx: "得意先", dataType: "string",
                        width: 150, minWidth: 150,
                        render: ui => {
                            var text = $("<div>").addClass("w-100")
                                .append($("<div>").text(ui.rowData.得意先ＣＤ))
                                .append($("<div>").text(ui.rowData.得意先名))
                                .append($("<div>").css("text-align", "right").text(ui.rowData.電話番号１))
                                .prop("outerHTML")
                                ;

                            return { text: text };
                        },
                    },
                    {
                        title: "区分",
                        dataIndx: "食事区分名", dataType: "string",
                        width: 45, maxWidth: 45, minWidth: 45,
                    },
                ],
            },
        });

        data.grid1Options.colModel.push(..._.range(0, 6)
            .map(v => moment().day(1).add(v, "days"))
            .map(d => {
                return {
                    title: d.format("M/D") + "<br>" + d.format("(ddd)"),
                    dataIndx: d.format("MMDD"),
                    dataType: "string",
                    width: 75, maxWidth: 75, minWidth: 75,
                }
            })
        );

        data.grid1Options.colModel.push(
            {
                title: $("<span>").addClass("d-inline-block").addClass("w-100").addClass("info-col")
                    .append($("<span>").text("").addClass("d-inline-block").css("width", "80px"))
                    .append($("<span>").text("今週・売上").addClass("d-inline-block").css("width", "80px"))
                    .append($("<span>").text("次週・売上").addClass("d-inline-block").css("width", "80px"))
                    .append($("<span>").text("").addClass("d-inline-block").css("width", "calc(100% - 240px)"))
                    .prop("outerHTML")
                ,
                colModel: [
                    {
                        title: $("<span>").addClass("d-inline-block").addClass("w-100").addClass("info-col")
                            .append($("<span>").text("未集金").addClass("d-inline-block").css("width", "80px"))
                            .append($("<span>").text("今週・請求").addClass("d-inline-block").css("width", "80px"))
                            .append($("<span>").text("次週・請求").addClass("d-inline-block").css("width", "80px"))
                            .append($("<span>").text("住所").addClass("d-inline-block").css("width", "calc(100% - 240px)"))
                            .prop("outerHTML")
                        ,
                        dataIndx: "情報",
                        dataType: "string",
                        width: 500, minWidth: 500,
                        render: ui => {
                            var html = $("<div>").addClass("w-100")
                                .append(
                                    $("<div>").addClass("d-flex").addClass("info-col")
                                        .append(
                                            $("<div>").css("width", "100px")
                                                .append(
                                                    $("<div>").text(ui.rowData.締支払).addClass("text-center")
                                                )
                                                .append(
                                                    $("<div>").text(pq.formatNumber(ui.rowData.未集金, "#,##0")).addClass("text-right").css("padding-right", "10px").css("font-weight", "bold")
                                                )
                                        )
                                        .append(
                                            $("<div>").css("width", "100px")
                                                .append(
                                                    $("<div>").text(pq.formatNumber(ui.rowData.今週売上, "#,##0")).addClass("text-right").css("padding-right", "10px").css("font-weight", "bold")
                                                )
                                                .append(
                                                    $("<div>").text(pq.formatNumber(ui.rowData.今週請求, "#,##0")).addClass("text-right").css("padding-right", "10px").css("font-weight", "bold")
                                                )
                                        )
                                        .append(
                                            $("<div>").css("width", "100px")
                                                .append(
                                                    $("<div>").text(pq.formatNumber(ui.rowData.次週売上, "#,##0")).addClass("text-right").css("padding-right", "10px")
                                                )
                                                .append(
                                                    $("<div>").text(pq.formatNumber(ui.rowData.次週請求, "#,##0")).addClass("text-right").css("padding-right", "10px")
                                                )
                                        )
                                        .append(
                                            $("<div>").text(ui.rowData.住所).css("width", "calc(100% - 300px)").css("padding-left", "10px")
                                        )
                                )
                                .append($("<div>").text(ui.rowData.備考１).addClass("biko-cell"))
                                .prop("outerHTML");

                            return { text: html };
                        },
                    },
                ],
                width: 500, minWidth: 500,
                custom: true,
            },
        );

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI07020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI07020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entity, entities) {
            var vue = this;

            if (!!entity) {
                vue.BushoInfo = entity.info;
            }

            //検索条件変更
            vue.conditionChanged();
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;
            var grid = vue.DAI07020Grid1;

            var days = _.range(0, 6).map(v => moment(vue.searchParams.TargetDate).day(1).add(v, "days"));
            var holidays = [];

            grid.options.colModel
                .filter(c => !!c.dataIndx && c.dataIndx.match(/^\d+$/))
                .forEach((c, i) => {
                    var d = days[i];
                    c.title = d.format("M/D") + "<br>" + d.format("(ddd)")
                    c.dataIndx = d.format("MMDD");
                });

            grid.refreshCM();

            //検索条件変更
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI07020Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate) return;

            if (!force && !!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                return;
            }

            grid.searchData(vue.searchParams, false, null);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI07020Grid1;

            if (!grid) return;

            var rules = [];
            if (!!vue.viewModel.CourseCd) {
                rules.push({ dataIndx: "コースＣＤ", condition: "equal", value: vue.viewModel.CourseCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var DeliveryList = res[0].DeliveryList;
            var SeikyuList = res[0].SeikyuList;
            var UriageListDiff = res[0].UriageListDiff;
            var NyukinListDiff = res[0].NyukinListDiff;
            var UriageListThisWeek = res[0].UriageListThisWeek;
            var NyukinListThisWeek = res[0].NyukinListThisWeek;
            var UriageListNextWeek = res[0].UriageListNextWeek;
            var NyukinListNextWeek = res[0].NyukinListNextWeek;

            //集計
            var groupings = _(DeliveryList)
                .groupBy(v => v.コースＣＤ)
                .values()
                .filter(g => g.some(r => !!r.日付))
                .flatten()
                .groupBy(v => v.コースＣＤ + "_" + v.順)
                .values()
                .map((g, i) => {
                    var rows = _(g)
                        .sortBy(v => v.コースＣＤ + "_" + v.順 + "_" + v.食事区分)
                        .groupBy(v => v.コースＣＤ + "_" + v.順 + "_" + v.食事区分)
                        .values()
                        .map((r, i) => {
                            var ret = _.reduce(
                                    r,
                                    (acc, v, j) => {
                                        acc.コースＣＤ = v.コースＣＤ;
                                        acc.コース名 = v.コース名;
                                        acc.順 = v.順;
                                        acc.得意先ＣＤ = v.得意先ＣＤ;
                                        acc.得意先名 = v.得意先名;
                                        acc.電話番号１ = v.電話番号１;
                                        acc.食事区分名 = v.食事区分名;

                                        var ymd = moment(v.日付).format("MMDD");

                                        acc[ymd + "_個数"] = (acc[ymd + "_個数"] || 0)
                                            + (v.現金個数 || 0) * 1  + (v.掛売個数 || 0) * 1;

                                        acc[ymd] = (!!acc[ymd] ? (acc[ymd] + "\n") : "")
                                            + (
                                                (v.現金個数 || 0) * 1 + (v.掛売個数 || 0) * 1 == 0
                                                    ? ""
                                                    : (v.商品略称 + _.repeat("\n" + v.商品略称, ((v.現金個数 || 0) * 1 + (v.掛売個数 || 0) * 1 - 1)))
                                            );

                                        acc.住所 = v.住所;
                                        acc.備考１ = v.備考１;
                                        acc.締支払 = v.締支払;

                                        return acc;
                                    },
                                    {}
                            );
                            ret.OrginalArray = r;

                            return ret;
                        })
                        .value()
                        ;

                    if (rows.length < 3) {
                        rows.push(..._.range(0, 3 - rows.length).map(v => {
                            return {
                                "コースＣＤ": g[0].コースＣＤ,
                                "コース名": g[0].コース名,
                                "順": g[0].順,
                                "得意先ＣＤ": g[0].得意先ＣＤ,
                                "得意先名": g[0].得意先名,
                                "電話番号１": g[0].電話番号１,
                                "食事区分名": null,
                                "個数": 0,
                            };
                        }));
                    }

                    var result = _.reduce(
                        rows,
                        (acc, r, i) => {
                            if (_.isEmpty(acc)) {
                                var dk = _.keys(r).filter(k => k.match(/^\d+$/));
                                dk.forEach(k => r[k] = r[k + "_個数"] > 0 ? r[k] : null);

                                acc = r;
                                return acc;
                            }

                            var dk = _.uniq(_.keys(acc).filter(k => k.match(/^\d+$/)).concat(_.keys(r).filter(k => k.match(/^\d+$/)))).sort();
                            var dv = dk.map(k => acc[k]);
                            var max = _.max(dv.filter(v => !!v).map(v => (v.match(/\n/g) || []).length));

                            var add = (s, d, m) => !d ? s  : (!s ? (_.repeat("\n", m + 1) + d) : (s + _.repeat("\n", m - (s.match(/\n/g) || []).length + 1) + d));

                            dk.forEach(k => acc[k] = r[k + "_個数"] > 0 ? add(acc[k], r[k], max) : acc[k]);
                            acc.食事区分名 = add(acc.食事区分名, r.食事区分名, max);

                            return acc;
                        },
                        {}
                    );

                    return result;
                })
                .map(r => {
                    r.未集金 = ((SeikyuList.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).今回請求額 || 0) * 1
                        + ((UriageListDiff.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).売上 || 0) * 1
                        - ((NyukinListDiff.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).入金 || 0) * 1
                        ;
                    r.今週売上 = ((UriageListThisWeek.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).売上 || 0) * 1;
                    r.今週請求 = r.未集金 + r.今週売上
                        - ((NyukinListThisWeek.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).入金 || 0) * 1;
                    r.次週売上 = ((UriageListNextWeek.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).売上 || 0) * 1;
                    r.次週請求 =  r.今週請求 + r.次週売上
                        - ((NyukinListNextWeek.find(v => v.得意先ＣＤ == r.得意先ＣＤ) || {}).入金 || 0) * 1;

                    return r;
                })
                .value()
                ;

            return groupings;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .filter(v => v.部署ＣＤ == vue.viewModel.BushoCd)
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            return list;
        },
        print: function() {
            var vue = this;
            var grid = vue.DAI07020Grid1;

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
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                div.title {
                    display: block;
                    text-align: center;
                }
                div.title > h3, div.title > h5 {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
                div {
                    font-family: "MS UI Gothic";

                }
            `;

            grid.Group().option({ "on": true, "dataIndx": ["コースＣＤ"]});

            var headerFunc = (header, idx, length) => {

                var CourseCd="";
                var CourseNm="";
                if(header.pq_level==0)
                {
                    CourseCd= header.children[0].コースＣＤ;
                    CourseNm= header.children[0].コース名;
                }else{
                    CourseCd= header.コースＣＤ;
                    CourseNm= header.コース名;
                }
                var TargetDateFrom = moment(vue.viewModel.TargetDate, "YYYYMMDD").day(1).format("YYYY/MM/DD(ddd)");
                var TargetDateTo = moment(vue.viewModel.TargetDate, "YYYYMMDD").day(6).format("YYYY/MM/DD(ddd)");

                return `
                    <div class="header">
                        <div style="float: left; width: 26%;">
                            <div style="height: 43px;"></div>
                            <div style="clear: left;" class="header-box">
	                            <div style="float: left; width: 17%; text-align: center;">日付</div>
	                            <div style="float: left; width: 79%;">${moment(vue.viewModel.TargetDate, "YYYYMMDD").format("YYYY年MM月DD日")}</div>
                            </div>
                            <div style="clear: left;" class="header-box">
	                            <div style="float: left; width: 17%; text-align: center;">コース</div>
	                            <div style="float: left; width: 17%; text-align: center;">${CourseCd}</div>
	                            <div style="float: left; width: 60.6%;">${CourseNm}</div>
                            </div>
                        </div>
                        <div class="title" style="float: left; height: 70px; width: 47%">
                            <h3>* * * <span></span>配送集計表(個人宅)<span></span> * * *</h3>
                            <div>
                                ${TargetDateFrom}
                                <span>～</span>
                                ${TargetDateTo}
                            </div>
                        </div>
                        <div style="float: left; width: 27%">
                            <div style="margin-bottom: 3px;">
                                ${vue.viewModel.BushoNm}
                            </div>
                            <div style="margin-bottom: 3px;">
                                ${vue.BushoInfo.会社名称}
                            </div>
                            <div style="margin-bottom: 3px;">
                                    Tel<span></span>${vue.BushoInfo.電話番号}
                            </div>
                            <div style="margin-bottom: 3px;">
                                    Fax<span></span>${vue.BushoInfo.FAX}
                            </div>
                        </div>
                        <div style="float: left; width: 27%;"  class="header-box">
                            <div style="float: left; width: 19%; text-align: center;">作成日</div>
                            <div style="float: left; width: 37%; text-align: center;">${moment().format("YYYY/MM/DD")}</div>
                            <div style="float: left; width: 18.9%; text-align: center;">PAGE</div>
                            <div style="float: left; width: 19%; text-align: center;">${idx + 1}/${length}</div>
                        </div>
                    </div>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    div.header > div > div > span {
                                        padding-left: 8px;
                                        padding-right: 8px;
                                    }
                                    div.header-box > div{
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                        padding-left: 3px;
                                        padding-top: 3px;
                                        height: 17px;
                                    }
                                    div.header-box > div:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI07020Grid1 thead tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI07020Grid1 thead tr:nth-child(1) th:nth-child(1) {
                                        border-left-width: 1px;
                                    }
                                    table.DAI07020Grid1 thead tr:nth-child(2) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 0px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI07020Grid1 thead tr th:nth-child(10),
                                    table.DAI07020Grid1 tr th:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI07020Grid1 tr td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI07020Grid1 tr:last-child td {
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI07020Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI07020Grid1 tr td:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI07020Grid1 tr th:nth-child(1) {
                                        width: 2.8%;
                                    }
                                    table.DAI07020Grid1 tr th:nth-child(2) {
                                        width: 13%;
                                    }
                                    table.DAI07020Grid1 tr th:nth-child(3) {
                                        width: 3.3%;
                                    }
                                    table.DAI07020Grid1 tr th:nth-child(4),
                                    table.DAI07020Grid1 tr th:nth-child(5),
                                    table.DAI07020Grid1 tr th:nth-child(6),
                                    table.DAI07020Grid1 tr th:nth-child(7),
                                    table.DAI07020Grid1 tr th:nth-child(8),
                                    table.DAI07020Grid1 tr th:nth-child(9) {
                                        width: 5.5%;
                                    }
                                    table.DAI07020Grid1 tr td:nth-child(3),
                                    table.DAI07020Grid1 tr td:nth-child(4),
                                    table.DAI07020Grid1 tr td:nth-child(5),
                                    table.DAI07020Grid1 tr td:nth-child(6),
                                    table.DAI07020Grid1 tr td:nth-child(7),
                                    table.DAI07020Grid1 tr td:nth-child(8),
                                    table.DAI07020Grid1 tr td:nth-child(9) {
                                        vertical-align: top;
                                    }
                                    table.DAI07020Grid1 thead tr th:last-child > span > span,
                                    table.DAI07020Grid1 td:nth-child(10) > div > div > div {
                                        float: left;
                                    }
                                    table.DAI07020Grid1 thead tr th:last-child > span > span:nth-child(1),
                                    table.DAI07020Grid1 thead tr th:last-child > span > span:nth-child(2),
                                    table.DAI07020Grid1 thead tr th:last-child > span > span:nth-child(3){
                                        width: 14% !important;
                                    }
                                    table.DAI07020Grid1 div.info-col > div{
                                        width: 12% !important;
                                    }
                                    table.DAI07020Grid1 thead tr:nth-child(1) th:last-child > span > span:nth-child(2){
                                       margin-left: 14%;
                                    }
                                    div.info-col > * {
                                       border-style: solid;
                                       border-width: 1px;
                                       border-left-width: 0px;
                                       border-top-width: 0px;
                                       border-right-width: 1px;
                                       border-bottom-width: 0px;
                                       text-align: right;
                                    }
                                    div.info-col > *:last-child {
                                       border-right-width: 0px;
                                       text-align: left;
                                    }
                                    table.DAI07020Grid1 div.info-col > div > div,
                                    table.DAI07020Grid1 div.info-col > div,
                                    table.DAI07020Grid1 div.biko-cell {
                                       padding-right: 5px !important;
                                       padding-left: 5px !important;
                                    }
                                    table.DAI07020Grid1 div.info-col > div:nth-child(1) > div:nth-child(1){
                                       text-align: center;
                                    }
                                    table.DAI07020Grid1 td div.biko-cell {
                                        border-style: solid;
                                        border-width: 0px;
                                        border-top-width: 1px;
                                        padding-top: 2px;
                                    }
                                    table.DAI07020Grid1 td:nth-child(10) > div > div:last-child {
                                        clear: left;
                                    }
                                    .header div:not(.title) {
                                        font-size: 10pt;
                                    }
                                    table.DAI07020Grid1 tbody div{
                                        padding-top: 2px;
                                        padding-bottom: 3px;
                                    }
                                    table.DAI07020Grid1 th{
                                        padding-top: 2px;
                                        padding-bottom: 2px;
                                        vertical-align: top;
                                    }
                                    table.DAI07020Grid1 th:nth-child(2){
                                        text-align: left;
                                    }
                                    table.DAI07020Grid1 th:nth-child(3){
                                        vertical-align: bottom;
                                        padding-bottom: 3px;
                                    }
                                    table.DAI07020Grid1 th:nth-child(4),
                                    table.DAI07020Grid1 th:nth-child(5),
                                    table.DAI07020Grid1 th:nth-child(6),
                                    table.DAI07020Grid1 th:nth-child(7),
                                    table.DAI07020Grid1 th:nth-child(8),
                                    table.DAI07020Grid1 th:nth-child(9){
                                        vertical-align: bottom;
                                        padding-bottom: 3px;
                                    }
                                    table.DAI07020Grid1 td:nth-child(3),
                                    table.DAI07020Grid1 td:nth-child(4),
                                    table.DAI07020Grid1 td:nth-child(5),
                                    table.DAI07020Grid1 td:nth-child(6),
                                    table.DAI07020Grid1 td:nth-child(7),
                                    table.DAI07020Grid1 td:nth-child(8),
                                    table.DAI07020Grid1 td:nth-child(9){
                                        padding-top: 5px;
                                    }
                                    table.DAI07020Grid1 tr td:nth-child(1){
                                        height: 50px !important;
                                    }
                                    table.DAI07020Grid1 td:nth-child(10) > div,
                                    table.DAI07020Grid1 td:nth-child(10) > div > div > div,
                                    table.DAI07020Grid1 td:nth-child(10) > div > div:nth-child(1) {
                                        padding-top: 0px;
                                    }
                                    table.DAI07020Grid1 td:nth-child(10) > div > div > div:nth-child(4) {
                                        padding-top: 2.5px;
                                    }
                                    table.DAI07020Grid1 td:nth-child(10) {
                                        vertical-align: baseline;
                                        padding: 0px;
                                    }
                                    div[style="break-before: page;"],
                                    div[style="break-before: auto;"],
                                    div[style="page-break-before: always;"] {
                                        margin-top: 20px !important;
                                        margin-bottom: 20px !important;
                                        margin-right: 20px !important;
                                        margin-left: 20px !important;
                                    }
                                `,
                                headerFunc,
                                43,
                                false,
                                false,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "on": false });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
