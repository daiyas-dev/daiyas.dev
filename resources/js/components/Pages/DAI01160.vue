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
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=false
                />
            </div>
            <div class="col-md-1">
                <label>コース開始</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseStart"
                    ref="PopupSelect_CourseStart"
                    :vmodel=viewModel
                    bind="CourseStart"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
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
                    :onAfterChangedFunc=onCourseStartChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 offset-md-3">
                <label>コース終了</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CourseEnd"
                    ref="PopupSelect_CourseEnd"
                    :vmodel=viewModel
                    bind="CourseEnd"
                    dataUrl="/Utilities/GetCourseList"
                    :params='{ bushoCd: viewModel.BushoCd, courseKbn: viewModel.CourseKbn }'
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{ Cd: 9999 }]"
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseEndChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
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
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 配送集計表",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,

                CourseKbn: null,
                CourseStart: null,
                CourseEnd: null,
                ColHeader : [],
            },
            DAI01160Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 1,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
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
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "順",
                        dataIndx: "順", dataType: "integer",
                        width: 50, minWidth: 50,
                        fixed: true,
                    },
                    {
                        title: "顧客",
                        dataIndx: "得意先名", dataType: "string",
                        width: 250, minWidth: 250,
                        fixed: true,
                    },
                    {
                        title: "T",
                        dataIndx: "受注方法表示", dataType: "string",
                        align : "center",
                        width: 50, minWidth: 50, maxWidth: 50,
                        fixed: true,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号", dataType: "string",
                        align : "center",
                        width: 150, minWidth: 150,
                        fixed: true,
                    },
                ],
                printSize: "A4",    //TODO: deprecated
                printDirection: "portrait", //"landscape"   //TODO: deprecated
                printHeader: null,
                printHeaderStyle: null,
                printGridHeaderStyle: null,
                printGridStyle: null,
                printStyles: null,
            },
        });
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
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        vue.DAI01160Grid1.searchData(params, false, null, () => vue.DAI01160Grid1.print(vue.setPrintOptions));
                    }
                }
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

            axios.post("/DAI01160/ColSearch", { BushoCd: vue.viewModel.BushoCd })
                .then(response => {
                    var res = response.data;

                    var newCols = grid.options.colModel
                        .filter(v => !!v.fixed)
                        .concat(
                            res.map(v => {
                                return {
                                    title: v.各種略称,
                                    dataIndx: (v.サブ各種CD2 == "0" ? "商品_" : "単価_") + v.サブ各種CD1,
                                    sub1: v.サブ各種CD1,
                                    sub2: v.サブ各種CD2,
                                    dataType: "integer",
                                    format: "#,###",
                                    width: 50, maxWidth: 50, minWidth: 50,
                                    summary: {
                                        type: "TotalInt",
                                    },
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

                    //列定義更新
                    grid.options.colModel = newCols;
                    grid.refreshCM();

                    //条件変更ハンドラ
                    vue.conditionChanged();
                })
                .catch(error => {
                    console.log(error);

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "各種テーブル検索失敗",
                        contents: "各種テーブル検索に失敗しました" + "<br/>" + error.message,
                    });
                });
        },
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseStartChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        onCourseEndChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: _.debounce(function(callback) {
            var vue = this;
            var grid = vue.DAI01160Grid1;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01160Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 500);
            })
            .then((grid) => {
                var params = $.extend(true, {}, vue.viewModel);

                //配送日を"YYYYMMDD"形式に編集
                params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

                //コース開始/終了はフィルタするので除外
                delete params.CourseStart;
                delete params.CourseEnd;

                grid.searchData(params, false, null, callback);
            });
        }, 300),
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01160Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (!!vue.viewModel.CourseStart) {
                crules.push({ condition: "gte", value: vue.viewModel.CourseStart });
            }
            if (!!vue.viewModel.CourseEnd) {
                crules.push({ condition: "lte", value: vue.viewModel.CourseEnd });
            }

            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (vue, grid, res) {
            //集計
            var groupings = _(res)
                .groupBy(v => v.得意先ＣＤ)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            r,
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? v : acc;
                                acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0) + v.個数;
                                acc["単価_" + v.得意先単価商品ＣＤ] = v.得意先単価;

                                if (!grid.options.colModel.some(c => c.sub1 == v.商品ＣＤ)) {
                                    acc["他"] = (acc["他"] || 0) + v.個数;
                                }

                                return acc;
                            },
                            {}
                    );

                    return ret;

                })

            console.log("01160 groupings before sort", groupings);
            groupings = _.sortBy(groupings, v => v.順 * 1);
            console.log("01160 groupings after sort", groupings);

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
    }
}
</script>
