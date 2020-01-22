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
                <label>配送日</label>
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
                <label>コースCD</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
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
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>

        <PqGridWrapper
            id="DAI01080Grid1"
            ref="DAI01080Grid1"
            dataUrl="/DAI01080/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01080Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI01080Grid1 .pq-group-icon {
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
    name: "DAI01080",
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
            ScreenTitle: "日時処理 > 配送予定入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DeliveryDate: null,
                CourseStart: null,
            },
            DAI01080Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHt: 25,
                rowHtSum: 25,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
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
                    cancel: false,
                    type: "remote",
                    sorter:[ { dataIndx: "ＳＥＱ", dir: "up" } ]
                },
                groupModel: {
                },
                summaryData: [
                ],
                formulas:[
                    [
                        "CustumerName",
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
                        title: "得意先",
                        dataIndx: "CustumerName", dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                        fixed: true,
                    },
                    {
                        title: "製造パターン",
                        dataIndx: "製造パターン", dataType: "string",
                        width: 200, maxWidth: 200, minWidth: 200,
                        fixed: true,
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
                { visible: "true", value: "検索", id: "DAI01080Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

                        vue.DAI01080Grid1.searchData(params);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                        vue.DAI01080Grid1.searchData(params, false, null, () => vue.DAI01080Grid1.print(vue.setPrintOptions));
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
            grid.options.printOptions.printHeader =
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
            grid.options.printOptions.printStyles =
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
                    grid = vue.DAI01080Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                axios.post("/DAI01080/ColSearch", { BushoCd: vue.viewModel.BushoCd })
                    .then(response => {
                        var res = _.cloneDeep(response.data);

                         var newCols = grid.options.colModel
                            .filter(v => !!v.fixed)
                            .concat(
                                res.map((v, i) => {
                                    return {
                                        title: v.商品略称,
                                        dataIndx: "商品_" + v.商品ＣＤ,
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
                                        custom: true,
                                    };
                                })
                            )

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

        changeColVisible: function(colModel) {
            var vue = this;
            var grid = vue.DAI01080Grid1;
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

            //条件変更ハンドラ
            vue.conditionChanged();
        },

        onCourseChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更ハンドラ
            vue.filterChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01080Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

            var params = $.extend(true, {}, vue.viewModel);

            //配送日を"YYYYMMDD"形式に編集
            params.DeliveryDate = params.DeliveryDate ? moment(params.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;

            //コース開始/終了はフィルタするので除外
            delete params.CourseStart;
            delete params.CourseEnd;

            grid.searchData(params, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01080Grid1;

            if (!grid) return;

            var rules = [];
            var crules = [];
            if (vue.viewModel.CourseStart != undefined && vue.viewModel.CourseStart != "") {
                crules.push({ condition: "gte", value: vue.viewModel.CourseStart * 1 });
            }
            if (vue.viewModel.CourseEnd != undefined && vue.viewModel.CourseEnd != "") {
                crules.push({ condition: "lte", value: vue.viewModel.CourseEnd * 1 });
            }

            if (crules.length) {
                rules.push({ dataIndx: "コースＣＤ", mode: "AND", crules: crules });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            console.log("01170 onAfterSearchFunc", res);

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
                                if (v.見込数 * 1 > 0) {
                                    acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0) + v.見込数 * 1;
                                    acc["合計個数"] = (acc["合計個数"] || 0) + v.見込数 * 1;
                                     if (v.注文数 * 1 > 0) {
                                        acc["合計個数"]  = 0;
                                     }
                                }
                                if (v.注文数 * 1 > 0) {
                                    acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0) + v.注文数 * 1;
                                    acc["合計個数"] = (acc["合計個数"] || 0) + v.注文数 * 1;
                                }

                                return acc;
                            },
                            {}
                    );

                    ret.GroupKey = ret.コースＣＤ + " " + ret.コース名;
                    ret.配送コース名 = ret.コースＣＤ + " " + ret.コース名;

                    return ret;

                })

            groupings = _(groupings).sortBy(v => v.ＳＥＱ * 1).sortBy(v => v.コースＣＤ * 1).value();

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
    }
}
</script>
