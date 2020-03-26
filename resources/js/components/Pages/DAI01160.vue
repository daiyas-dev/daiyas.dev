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
            <div class="col-md-1">
                <label>配送日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
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
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
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
        <div class="row">
            <div class="col-md-3">
                <label>コース別</label>
                <VueOptions
                    id="SummaryKind"
                    ref="VueOptions_SummaryKind"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="SummaryKind"
                    :list="[
                        {code: '1', name: 'コース別', label: 'コース別'},
                        {code: '2', name: 'コース合計', label: 'コース合計'},
                    ]"
                    :onChangedFunc=onSummaryKindChanged
                />
            </div>
            <div class="col-md-3">
                <VueCheck
                    id="VueCheck_IncludeJuchu"
                    ref="VueCheck_IncludeJuchu"
                    :vmodel=viewModel
                    bind="IsIncludeJuchu"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '含まない', label: 'チェック無し：受注情報含まない'},
                        {code: '1', name: '含む', label: 'チェック有り：受注情報含む'},
                    ]"
                    :onChangedFunc=onIncludeJuchuChanged
                />
            </div>
            <div class="col-md-3">
                <VueCheck
                    id="VueCheck_BikoOutput"
                    ref="VueCheck_BikoOutput"
                    :vmodel=viewModel
                    bind="IsBikoOutput"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: 'チェック無し：備考出力しない'},
                        {code: '1', name: 'する', label: 'チェック有り：備考出力する'},
                    ]"
                    :onChangedFunc=onBikoOutputChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01160Grid1"
            ref="DAI01160Grid1"
            dataUrl="/DAI01160/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01160Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01160Grid1 .pq-group-icon {
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
    name: "DAI01160",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日次処理 > 配送集計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                SummaryKind: null,
                IsIncludeJuchu: "1",
                IsBikoOutput: "0",
                CourseKbn: null,
                CourseCd: null,
                ColHeader : [],
                dd: null,
            },
            DAI01160Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 35,
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
                    on: true,
                    header: false,
                    grandSummary: true,
                    indent: 20,
                    dataIndx: ["GroupKey"],
                    showSummary: [true],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "顧客名",
                        function(rowData){
                            return _.padStart(rowData.得意先ＣＤ, 6, "0") + " " + rowData.得意先名;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "GroupKey",
                        dataIndx: "GroupKey", dataType: "string",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "integer",
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "順",
                        dataIndx: "順", dataType: "integer",
                        width: 35, minWidth: 35, maxWidth: 35,
                        fixed: true,
                    },
                    {
                        title: "顧客",
                        dataIndx: "顧客名", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        fixed: true,
                    },
                    {
                        title: "T",
                        dataIndx: "受注方法表示", dataType: "string",
                        align : "center",
                        width: 35, minWidth: 35, maxWidth: 35,
                        fixed: true,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号", dataType: "string",
                        align : "center",
                        width: 125, minWidth: 125, maxWidth: 125,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_grandsummary) {
                                //総計行
                                ui.rowData["電話番号"] = "総計";
                                return { text: "総計" };
                            }
                            if (!!ui.rowData.pq_gsummary) {
                                //小計行
                                ui.rowData["電話番号"] = "コース計";
                                return { text: "コース計" };
                            }
                            return ui;
                        },
                    },
                    {
                        title: "配送コース名",
                        dataIndx: "配送コース名", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        fixed: true,
                        render: ui => {
                            if (!!ui.rowData.pq_gid) {
                                //グループ行
                                ui.rowData["配送コース名"] = ui.rowData.pq_gid;
                                return { text: ui.rowData.pq_gid };
                            }
                            return ui;
                        },
                    },
                ],
            },
        });

        data.grid1Options.filter = this.setNavigator;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01160Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

                        vue.DAI01160Grid1.searchData(params);
                    }
                },
                // { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                //     onClick: function () {
                //         var params = $.extend(true, {}, vue.viewModel);

                //         //配送日を"YYYYMMDD"形式に編集
                //         params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                //         vue.DAI01160Grid1.searchData(params, false, null, () => vue.DAI01160Grid1.print(vue.setPrintOptions));
                //     }
                // },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Print", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment();
        },
        setPrintOptions: function(grid) {
            var vue = this;

            //PqGrid Print options
            grid.options.printHeader =
                `
                    <style>
                        .header-table {

                        }
                        .header-table th {
                            font-family: "MS UI Gothic";
                            font-size: 10pt;
                            font-weight: normal !important;
                            border: solid 1px black !important;
                            white-space: nowrap;
                            overflow: hidden;
                            margin: 0px;
                            padding-left: 3px;
                            padding-right: 3px;
                        }
                        .header-table tr:last-child th{
                            border-bottom-width: 0px !important;
                        }
                    </style>
                    <h3 style="text-align: center; margin: 0px; margin-bottom: 10px;">* * 持ち出し数一覧表 * *</h3>
                    <table style="border-collapse: collapse; width: 100%;" class="header-table">
                        <colgroup>
                                <col style="width:4.58%;"></col>
                                <col style="width:4.60%;"></col>
                                <col style="width:9.00%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                                <col style="width:5.45%;"></col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>日付</th>
                                <th colspan="5">${moment().format("YYYY年MM月DD日 dddd")}</th>
                            </tr>
                            <tr>
                                <th>部署</th>
                                <th>${vue.viewModel.BushoCd}</th>
                                <th colspan="4">${vue.viewModel.BushoNm}</th>
                                <th colspan="6" style="border-top-width: 0px !important;"></th>
                                <th colspan="2">作成日</th>
                                <th colspan="2">${moment().format("YYYY/MM/DD")}</th>
                                <th>PAGE</th>
                                <th>1</th>
                            </tr>
                        </thead>
                    </table>
                `;
            grid.options.printStyles =
                `
                    tr td:nth-child(1) {
                        font-size: 9pt;
                    }
                    tr td:nth-child(n+2) {
                        text-align: right;
                    }
                `;
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //列定義更新
            vue.refreshCols();
        },
        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01160Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                axios.post("/DAI01160/ColSearch", { BushoCd: vue.viewModel.BushoCd })
                    .then(response => {
                        var res = _.cloneDeep(response.data);
                        var cd2eq0 = res.filter(v => v.サブ各種CD2 == "0");
                        var cd2eq1 = res.filter(v => v.サブ各種CD2 == "1");

                        //サブ各種CD2=0が15件, サブ各種CD2=1が4件に満たない場合を考慮して補完/カット
                        var filler = {
                            各種略称: "",
                            サブ各種CD1: "",
                            サブ各種CD2: "",
                        };
                        cd2eq0 = _.assign(_.fill(new Array(15), filler), cd2eq0).slice(0, 15);
                        cd2eq1 = _.assign(_.fill(new Array(4), filler), cd2eq1).slice(0, 4);

                        var newCols = grid.options.colModel
                            .filter(v => !!v.fixed)
                            .concat(
                                cd2eq0.map((v, i) => {
                                    return {
                                        title: v.各種略称,
                                        dataIndx: "商品_" + v.サブ各種CD1,
                                        sub1: v.サブ各種CD1,
                                        sub2: v.サブ各種CD2,
                                        dataType: "integer",
                                        format: "#,###",
                                        width: 50, maxWidth: 50, minWidth: 50,
                                        render: ui => {
                                            // hide zero
                                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                                return { text: "" };
                                            }
                                            return ui;
                                        },
                                        summary: {
                                            type: "TotalInt",
                                        },
                                        cd2eq0Idx: i,
                                        custom: true,
                                    };
                                })
                            )
                            .concat(
                                cd2eq1.map((v, i) => {
                                    return {
                                        title: v.各種略称,
                                        dataIndx: "単価_" + v.サブ各種CD1,
                                        sub1: v.サブ各種CD1,
                                        sub2: v.サブ各種CD2,
                                        dataType: "integer",
                                        format: "#,###",
                                        width: 50, maxWidth: 50, minWidth: 50,
                                        render: ui => {
                                            // hide zero
                                            if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                                return { text: "" };
                                            }
                                            return ui;
                                        },
                                        cd2eq1Idx: i,
                                        custom: true,
                                    };
                                })
                            );

                        //他追加
                        newCols.splice(
                            newCols.findIndex(c => c.sub2=="1"),
                            0,
                            {
                                title: "他",
                                dataIndx: "他",
                                dataType: "integer",
                                format: "#,###",
                                width: 50, maxWidth: 50, minWidth: 50,
                                render: ui => {
                                    // hide zero
                                    if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            }
                        );

                        //締追加
                        newCols.splice(
                            newCols.findIndex(c => c.sub2=="1"),
                            0,
                            {
                                title: "締",
                                dataIndx: "締方法",
                                dataType: "string",
                                width: 50, maxWidth: 50, minWidth: 50,
                            }
                        );

                        //ﾐ,ﾌ追加
                        newCols.push(
                            {
                                title: "ﾐ",
                                dataIndx: "みそしる",
                                dataType: "string",
                                width: 50, maxWidth: 50, minWidth: 50,
                            }
                        );
                        newCols.push(
                            {
                                title: "ﾌ",
                                dataIndx: "ふりかけ",
                                dataType: "string",
                                width: 50, maxWidth: 50, minWidth: 50,
                            }
                        );

                        //合計
                        newCols.push(
                            {
                                title: "合計",
                                dataIndx: "合計個数",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                summary: {
                                    type: "TotalInt",
                                },
                            }
                        );
                        newCols.push(
                            {
                                title: "合計",
                                dataIndx: "合計金額",
                                dataType: "integer",
                                format: "#,###",
                                width: 100, maxWidth: 100, minWidth: 100,
                                render: ui => {
                                    // hide zero
                                    if (ui.rowData[ui.dataIndx] * 1 == 0) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            }
                        );
                        //備考
                        newCols.push(
                            {
                                title: "備考",
                                dataIndx: "備考",
                                dataType: "string",
                                width: 200, minWidth: 200,
                            }
                        );

                        //条件別列変更
                        vue.changeColVisible(newCols);

                        if (!!grid) grid.hideLoading();

                        //条件変更ハンドラ
                        vue.conditionChanged();
                    });
            })
            .catch(error => {
                console.log(error);
                if (!!grid) grid.hideLoading();

                //失敗ダイアログ
                $.dialogErr({
                    title: "各種テーブル検索失敗",
                    contents: "各種テーブル検索に失敗しました" + "<br/>" + error.message,
                });
            });
        },
        onSummaryKindChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01160Grid1;

            //条件別列変更
            vue.changeColVisible();

            //集計変更
            grid.Group()[vue.viewModel.SummaryKind == "1" ? "expandAll" : "collapseAll"]();
        },
        onBikoOutputChanged: function(code, entities) {
            var vue = this;

            //条件別列変更
            vue.changeColVisible();
        },
        changeColVisible: function(colModel) {
            var vue = this;
            var grid = vue.DAI01160Grid1;
            var cols = colModel || _.cloneDeep(grid.options.colModel);

            //順, 顧客名, 受注方法表示の列はコース合計時は非表示
            cols.filter(v => ["順", "顧客名", "受注方法表示"].includes(v.dataIndx))
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2";
                });

            //配送コース名の列はコース別時は非表示
            cols.filter(v => v.dataIndx == "配送コース名")
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "1";
                });

            //サブ各種CD2=0の列はコース別 && 備考出力時は10件まで
            cols.filter(v => v.cd2eq0Idx != undefined)
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "1" &&
                               vue.viewModel.IsBikoOutput == "1" &&
                               v.cd2eq0Idx > 10;
                });

            //サブ各種CD2=1の列はコース合計 || 備考出力時は非表示
            cols.filter(v => v.cd2eq1Idx != undefined)
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2" ||
                               vue.viewModel.IsBikoOutput == "1";
                });

            //電話番号,締方法,みそしる,ふりかけの列はコース合計 || 備考出力時は非表示
            cols.filter(v => ["電話番号", "締方法", "みそしる", "ふりかけ"].includes(v.dataIndx))
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2" ||
                               vue.viewModel.IsBikoOutput == "1";
                });

            //合計個数の列はコース別時は非表示
            cols.filter(v => v.dataIndx == "合計個数")
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "1";
                });

            //合計金額の列はコース合計 || 備考非出力時は非表示
            cols.filter(v => v.dataIndx == "合計金額")
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2" ||
                               vue.viewModel.IsBikoOutput == "0";
                });

            //備考の列はコース合計 || 備考非出力時は非表示
            cols.filter(v => v.dataIndx == "備考")
                .forEach(v => {
                    v.hidden = vue.viewModel.SummaryKind == "2" ||
                               vue.viewModel.IsBikoOutput == "0";
                });

            //列定義更新
            grid.options.colModel = cols;
            grid.refreshCM();
            grid.refresh();
        },
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

           //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD")}
            )
                .then(res => {
                    vue.viewModel.CourseKbn = res.data.コース区分;

                    //条件変更ハンドラ
                    vue.conditionChanged();
                })
                .catch(err => {
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "祝日マスタの検索に失敗しました<br/>",
                    });
                });
         },
        onIncludeJuchuChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseCdChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01160Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

            var params = $.extend(true, {}, vue.viewModel);

            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //コースはフィルタするので除外
            delete params.CourseCd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01160Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CourseCd != undefined && vue.viewModel.CourseCd != "") {
                crules.push({ condition: "equal", value: vue.viewModel.CourseCd * 1 });
            }

            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            //集計
            var groupings = _(res)
                .groupBy(v => v.得意先ＣＤ)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["得意先名"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                if (v.個数 * 1 > 0) {
                                    acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0) + v.個数 * 1;
                                    acc["合計個数"] = (acc["合計個数"] || 0) + v.個数 * 1;
                                }

                                if (!!v.得意先単価JSON) {
                                    var vals = JSON.parse(v.得意先単価JSON);
                                    _.keys(vals).forEach(k => {
                                        acc["単価_" + k] = vals[k];
                                    });
                                }

                                if (!grid.options.colModel.some(c => c.sub1 == v.商品ＣＤ) && v.個数 * 1 > 0) {
                                    acc["他"] = (acc["他"] || 0) + v.個数 * 1;
                                }

                                if (v.金額 * 1 > 0) {
                                    acc["合計金額"] = (acc["合計金額"] || 0) + v.金額 * 1;
                                }

                                return acc;
                            },
                            {}
                    );

                    ret.GroupKey = ret.コースＣＤ + " " + ret.コース名;
                    ret.配送コース名 = ret.コースＣＤ + " " + ret.コース名;

                    return ret;

                })

            groupings = _(groupings).sortBy(v => v.順 * 1).sortBy(v => v.コースＣＤ * 1).value();

            return groupings;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
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
        setNavigator: function(evt, ui) {
            var vue = this;
            console.log("setNavigator", evt, ui);
        },
        print: function() {
            //TODO:以下作業途中：01250参考
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
                }
                div.header-table {
                    font-family: "MS UI Gothic";
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    height: 22px;
                }
                div.header-table span {
                    margin-right: 25px;
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
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 21px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                div.header{
                    font-family: "MS UI Gothic";
                    font-size: 10pt;
                    font-weight: normal;
                    margin-bottom: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    height: 22px;
                    width: 100%;
                }
                div.header > div > div {
                	margin-bottom: 5px;
                }
                div.header span {
                    margin-right: 8px;
                }
                span{
                	padding-left: 8px;
                }
				#a-box, #d-box, #g-box {
					float: left;
					width: 25%
				}
				#b-box, #e-box, #h-box {
					float: left;
					width: 30%;
				}
				#c-box, #f-box, #i-box {
					float: right;
					width: 35%;
				}
				div #c-box{
					text-align: right;
                }
                div #d-box {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
            `;

            //TODO：曜日
            var weekday = ["日", "月", "火", "水", "木", "金", "土"];
            var target = new Date(moment(vue.viewModel.DeliveryDate, "YYYYMMDD"));
            var youbi = weekday[target.getDay()];

            var headerFunc = (chunk, idx, length) => {
                //TODO:コース名と曜日
                return `
                    <div class="title">
                        <h3>* * * 配送集計表 * * *</h3>
                    </div>
                    <div class="header">
                        <div>
                            <div id="a-box" style="margin-left:10px;">
                                ${vue.viewModel.BushoNm}
                            </div>
                            <div id="b-box"></div>
                            <div id="c-box">
                                <span>作成日</span>
                                <span>${moment().format("YYYY年MM月DD日")}</span>
                                <span>PAGE</span>
                                <span>${idx + 1}/</span>
                                ${length}
                            </div>
                        </div>
                        <div style="clear: both;">
                            <div id="d-box">
                                <div style="float: left;">${moment(vue.viewModel.DeliveryDate, "YYYYMMDD").format("YY/MM/DD")}(${youbi})</div>
                                <div style="float: left; margin-left:10px;">そのページのコース名</div>

                            </div>
                            <div id="e-box"></div>
                            <div id="f-box">
                                株式会社
                                <span/>ダイヤス食品
                            </div>
                        </div>
                        <div style="clear: both;">
                            <div id="g-box"></div>
                            <div id="h-box"></div>
                            <div id="i-box">
                                Tel
                                <span/>0836-32-1113
                                <span/>Fax
                                <span/>0836-21-4700
                            </div>
                        </div>
                    </div>
                `;
            };

            //TODO:どちらもコース計の行が出ていない
            var styleByCourse =`
                table.DAI01160Grid1 thead tr th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr td:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr th:nth-child(2) {
                    width: 20.0%;
                }
                table.DAI01160Grid1 tr th:nth-child(3) {
                    width: 2.5%;
                }
                table.DAI01160Grid1 tr th:nth-child(4) {
                    width: 10.0%;
                }
                table.DAI01160Grid1 tr th:nth-child(26),
                table.DAI01160Grid1 tr th:nth-child(27){
                    width: 2.0%;
                }
                table.DAI01160Grid1 tbody td {
                    height: 24px;
                }
            `;

            var styleByCourseWithBiko =`
                table.DAI01160Grid1 thead tr th {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr th:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr td {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr td:last-child {
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 0px;
                    border-right-width: 1px;
                    border-bottom-width: 1px;
                }
                table.DAI01160Grid1 tr th:nth-child(2) {
                    width: 20.0%;
                }
                table.DAI01160Grid1 tr th:nth-child(3) {
                    width: 2.5%;
                }
                table.DAI01160Grid1 tr th:nth-child(15) {
                    width: 8.0%;
                }
                table.DAI01160Grid1 tr th:nth-child(16){
                    width: 33.0%;
                }
                table.DAI01160Grid1 tbody td {
                    height: 25px;
                }
            `;

            //TODO:参考：DAI01180 複数のCSS
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            vue.DAI01160Grid1.generateHtml(
                                vue.viewModel.SummaryKind == 2 ? styleCourseSummary :
                                    (vue.viewModel.IsBikoOutput == 1 ? styleByCourseWithBiko : styleByCourse),
                                headerFunc,
                                vue.viewModel.SummaryKind == 2 ? 20 : (vue.viewModel.IsBikoOutput == 1 ? 22 : 23),
                                vue.viewModel.SummaryKind == 2 ? [false, true] : false,
                                true,
                                vue.viewModel.SummaryKind == 2 ? [true, false] : [false, true],
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
            //TODO: 印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
