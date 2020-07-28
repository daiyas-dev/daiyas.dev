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
            <div class="col-md-5">
                <label class="text-center mr-2" style="width: auto;">配送日付</label>
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
                    :buddies='{ CourseNm: "コース名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ UserBushoCd: getLoginInfo().bushoCd }'
                    :isPreload=true
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
        <PqGridWrapper
            id="DAI01260Grid1"
            ref="DAI01260Grid1"
            dataUrl="/DAI01260/Search"
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
#DAI01260Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01260Grid1 .pq-group-icon {
    display: none !important;
}
#DAI01260Grid1 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
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
    name: "DAI01260",
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
                vue.footerButtons.find(v => v.id == "DAI01260Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;

        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 得意先分配リスト",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DateStart: null,
                DateEnd: null,
                CourseCd: null,
                CourseNm: null,
            },
            DAI01260Grid1: null,
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
                    on: false,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: false,
                    indent: 0,
                    dataIndx: ["コース", "配送日", "分配元顧客"],
                    showSummary: [false, false, true],
                    collapsed: [false, false, false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "コース",
                        dataIndx: "コース", dataType: "string",
                        width: 65, minWidth: 65, maxWidth: 65,
                        render: ui => {
                            if (ui.rowData.pq_level != 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "配送日",
                        dataIndx: "配送日", dataType: "string",
                        width: 65, minWidth: 65, maxWidth: 65,
                        render: ui => {
                            switch (ui.rowData.pq_level) {
                                case 0:
                                    return { text: "" };
                                case 1:
                                    return ui;
                                case 2:
                                    if (!!ui.Export && !ui.rowData.pq_gsummary) {
                                        return { text: ui.rowData.parentId.split("_")[1] };
                                    } else {
                                        return { text: "" };
                                    }
                                default:
                                    return { text: "" };
                            }
                        },
                    },
                    {
                        title: "分配元顧客",
                        dataIndx: "分配元顧客", dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            if (ui.rowData.pq_level != 2) {
                                return { text: "" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "分配先顧客",
                        dataIndx: "分配先顧客",
                        dataType: "string",
                        width: 250, minWidth: 250,
                    },
                    {
                        title: "商品",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 150, minWidth: 150,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary && !!ui.rowData.pq_gsummary) {
                                if (!ui.Export) {
                                    ui.style.push("justify-content: flex-end;");
                                    ui.style.push("padding-right: 20px;");
                                }
                                return { text: "** 合計 **" };
                            }
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "数量",
                        dataType: "integer",
                        format: "#,###",
                        width: 65, minWidth: 65, maxWidth: 65,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "売上金額",
                        dataIndx: "売上金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "入金額",
                        dataIndx: "入金額",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI01260Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01260Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01260Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01260Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI01260Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI01260Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01260Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseChanged: function(code, entity, comp) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01260Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.DateStart || !vue.viewModel.DateEnd) return;

            if (!!vue.viewModel.CourseCd && !vue.$refs.PopupSelect_Course.isValid) return;

            if (!!grid.prevPostData && _.isEqual(grid.prevPostData, vue.searchParams)) {
                console.log("same condition", _.isEqual(grid.prevPostData, vue.searchParams));
                return;
            }

            grid.searchData(vue.searchParams, false, null, callback);
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            res.forEach(v => {
                v["コース"] = v.コースCD + ":" + v.コース名;
                v["分配元顧客"] = v.分配元得意先CD + ":" + v.分配元得意先名;
                v["分配先顧客"] = v.分配先得意先CD + ":" + v.分配先得意先名;
            });

            vue.footerButtons.find(v => v.id == "DAI01260Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01260Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI01260Grid1_Print").disabled = !res.length;

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

            //部署CDでフィルタ
            var list = dataList
                .filter(v => v.部署ＣＤ == vue.viewModel.BushoCd)
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
                    padding-left: 50px;
                    padding-right: 50px;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    border: solid 1px black;
                    border-radius: 6px;
                    background-color: cyan;
                }
                div.header-table {
                    text-align: center;
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    padding-top: 2px;
                    padding-bottom: 2px;
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
            `;

            var headerFunc = (header, idx, length) => {
                var courseKey = (header.pq_level == 0 ? header.コース : header.parentId).split(/[:_]/);
                var courseCd = courseKey[0];
                var courseNm = courseKey[1];
                console.log("1260 headerFunc", header, courseKey, courseCd, courseNm)
                return `
                    <div class="title">
                        <h3>* * 得意先分配リスト * *</h3>
                    </div>
                    <div class="header-table" style="border-width: 0px">
                        <tr>
                            <th style="width: 5.0%;">対象範囲</th>
                            (
                            <th style="width: 8.5%; text-align: center;">${moment(vue.viewModel.DateStart, "YYYY年MM月DD日").format("YYYY/MM/DD")}</th>
                            <th style="width: 3.0%;">～</th>
                            <th style="width: 8.5%; text-align: center;">${moment(vue.viewModel.DateEnd, "YYYY年MM月DD日").format("YYYY/MM/DD")}</th>
                            )
                        </tr>
                    </div>
                    <table class="header-table" style="border-width: 0px">
                        <thead>
                            <tr>
                                <th style="width: 15%;">${vue.viewModel.BushoNm}</th>
                                <th style="width: 5%;">${courseCd}</th>
                                <th style="width: 15%;">${courseNm}</th>
                                <th style="width: 26%;"></th>
                                <th style="width: 10%;">作成日</th>
                                <th style="width: 15%;">${moment().format("YYYY年MM月DD日 HH:mm")}</th>
                                <th style="width: 8%;">PAGE</th>
                                <th style="width: 6%; text-align: right;">${idx + 1}/${length}</th>
                            </tr>
                        </thead>
                    </table>
                `;
            };

            var maxRow = 31;
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01260Grid1.generateHtml(
                                `
                                    table.DAI01260Grid1 tr:nth-child(1) th {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01260Grid1 tr.grand-summary td {
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01260Grid1 tr th:nth-child(1) {
                                        display: none;
                                    }
                                    table.DAI01260Grid1 tr td:nth-child(1) {
                                        display: none;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(2) {
                                        width: 10.0%;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(3) {
                                        width: 10.0%;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(4) {
                                        width: 35.0%;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(5) {
                                        width: 21.0%;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(6) {
                                        width: 8.0%;
                                    }

                                    table.DAI01260Grid1 tr th:nth-child(7) {
                                        width: 8.0%;
                                    }
                                    table.DAI01260Grid1 tr th:nth-child(8) {
                                        width: 8.0%;
                                    }
                                    table.header-table th {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(4) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.header-table th:nth-child(8) {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 1px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01260Grid1 thead:first-child th {
                                        text-align: left;
                                        padding-left: 5px;
                                    }
                                    table.DAI01260Grid1 {
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 0px;
                                        border-right-width: 1px;
                                        border-bottom-width: 1px;
                                    }
                                    table.DAI01260Grid1 tr:not(.group-row):not(.group-summary):not(:nth-child(1))  td:nth-child(n+4){
                                        border-style: dashed;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01260Grid1 tr.group-row + tr td:nth-child(n+4){
                                        border-style: none !important;
                                    }
                                    table.DAI01260Grid1 tr.group-summary  td:nth-child(n+4) {
                                        border-style: double;
                                        border-left-width: 0px;
                                        border-top-width: 3px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                    table.DAI01260Grid1 tr.group-summary + tr td{
                                        border-style: solid;
                                        border-left-width: 0px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                    }
                                `,
                                headerFunc,
                                maxRow,
                                [false, false, true],
                                [false, false, true],
                                [
                                    true,
                                    (r, p, a) => {
                                        // var ret = a.length % maxRow == 0 || (p.children[0].children.length + 2 + (a.length % maxRow) > maxRow);
                                        var ret = (p.children[0].children.length + 2 + (a.length % maxRow) > maxRow);
                                        if (!!ret) {
                                            console.log("isBreakFunc level 1", p.pq_gid, p.children[0].children.length, a.length, a.length % maxRow, ret);
                                        }
                                        return ret;
                                    },
                                    (r, p, a) => {
                                        // var ret = a.length % maxRow == 0 || (p.children.length + 2 + (a.length % maxRow) > maxRow);
                                        var ret = (p.children.length + 2 + (a.length % maxRow) > maxRow);
                                        if (!!ret) {
                                            console.log("isBreakFunc level 2", p.pq_gid, p.children.length, a.length, a.length % maxRow, ret);
                                        }
                                        return ret;
                                    },
                                ],
                            )
                        )
                )
                .prop("outerHTML")
                ;

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
