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
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    buddy="CourseKbnNm"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :hasNull=false
                    :onChangedFunc=onCourseKbnChanged
                />
            </div>
        </div>
        <div class="row">
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
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                    :onAfterChangedFunc=onCourseStartChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
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
                    :dataListReset=true
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
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                    :onAfterChangedFunc=onCourseEndChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01070Grid1"
            ref="DAI01070Grid1"
            dataUrl="https://daiyas-mobile.tk/api/getoverunder"
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
#DAI01070Grid1 .pq-grid-cell.plus-value {
    color: black;
}
#DAI01070Grid1 .pq-grid-cell.minus-value {
    color: red;
}
label{
    width: 80px;
}
#DAI01070Grid1 .pq-grid-header-table .pq-td-div,
#DAI01070Grid1 .pq-title-span {
    line-height: 0.7;
    vertical-align: -webkit-baseline-middle;;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01070",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                DeliveryDate: moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                CourseKbn: this.viewModel.CourseKbn,
            };
        }
    },
    data() {
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 過不足問合せ",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                CourseKbn: null,
                CourseKbnNm: null,
                CourseStart: null,
                CourseEnd: null,
                ColHeader : [],
            },
            DAI01070Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 35, },
                autoRow: false,
                rowHtHead: 40,
                rowHt: 17,
                freezeCols: 6,
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
                    {
                        summaryRow: true,
                        pq_fn:{
                        }
                    },
                ],
                formulas:[
                ],
                colModel: [
                    {
                        title: "",
                        dataIndx: "集計対象",
                        type: "checkbox",
                        cbId: "IncludesSummary",
                        width: 50, minWidth: 50, maxWidth: 50,
                        align: "center",
                        editable: true,
                        editor: false,
                        hiddenOnExport: true,
                        render: ui => {
                            if (ui.rowData.summaryRow) {
                                return "";
                            }
                        },
                        fixed: true,
                    },
                    {
                        dataIndx: "IncludesSummary",
                        dataType: "bool",
                        align: "center",
                        editable: true,
                        cb: {
                            header: true,
                        },
                        hidden: true,
                        fixed: true,
                    },
                    {
                        title: "配送日",
                        dataIndx: "配送日", dataType: "date",
                        format: "yyyy/MM/dd",
                        hidden: true,
                        hiddenOnExport: false,
                        fixed: true,
                    },
                    {
                        title: "コース区分",
                        dataIndx: "コース区分名", dataType: "string",
                        hidden: true,
                        hiddenOnExport: false,
                        fixed: true,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ", dataType: "integer",
                        width: 100, minWidth: 100, maxWidth: 100,
                        fixed: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        fixed: true,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "最新表示", id: "DAI01070Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01070Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI01070Grid1_CSV", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.DAI01070Grid1.vue.exportData("csv");
                    }
                },
                { visible: "false", value: "Excel", id: "DAI01070Grid1_Excel", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.DAI01070Grid1.vue.exportData("xlsx");
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            if (vue.PrevBushoCd != code) {
                vue.PrevBushoCd = code;

                //列定義更新
                vue.refreshCols();
            }
        },
        refreshCols: function() {
            var vue = this;
            var grid;

            //PqGrid読込待ち
            new Promise((resolve, reject) => {
                var timer = setInterval(function () {
                    grid = vue.DAI01070Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                axios.post("/DAI01070/ColSearch", { BushoCd: vue.viewModel.BushoCd })
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
                                        width: 75, maxWidth: 75, minWidth: 75,
                                        render: ui => {
                                            ui.cls.push(ui.cellData < 0 ? "minus-value" : "plus-value");
                                            return ui;
                                        },
                                        custom: true,
                                    };
                                })
                            );

                        //合計行設定
                        grid.options.summaryData[0].pq_fn = { "コース名": "TRIM('合計')" };
                        newCols.forEach((c, i) => {
                            if (!!c.custom) {
                                var cidx = i + 1;
                                var int = Math.floor(i / 26);
                                var mod = i % 26;
                                var getCN = v => String.fromCharCode("A".charCodeAt() + v);
                                var cn = (int > 0 ? getCN(int - 1) : "") + (int > 0 || mod > 0 ? getCN(mod) : "");
                                var range = cn + ":" + cn;

                                grid.options.summaryData[0].pq_fn[c.dataIndx] = "SUMIF(B:B, 'TRUE', " + range + ")";
                            }
                        });

                        //列定義更新
                        grid.options.colModel = newCols;
                        grid.refreshCM();
                        grid.refresh();

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
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD")}
            )
                .then(res => {
                    vue.viewModel.CourseKbn = res.data.コース区分;
                    vue.viewModel.CourseKbnNm = res.data.コース区分名;

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
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01070Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

            grid.searchData(vue.searchParams, false, null, callback);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI01070Grid1;

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
            var vue = this;

            res = res.map(v => {
                return {
	                "チェック": v.check_flag,
	                "コースＣＤ": v.course_code,
	                "コース名": v.course_name,
	                "商品ＣＤ": v.product_code,
	                "個数": v.quantity,
                };
            });
            window.res = res;

            //集計
            var groupings = _(res)
                .groupBy(v => v.コースＣＤ)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["コースＣＤ"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? _.cloneDeep(v) : acc;

                                acc.配送日 = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY/MM/DD");
                                acc.コース区分 = vue.viewModel.CourseKbn;
                                acc.コース区分名 = vue.viewModel.CourseKbnNm;
                                acc.IncludesSummary = true;

                                delete acc.商品ＣＤ;
                                delete acc.個数;

                                var val = v.個数 * 1 || 0;

                                acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0) + val;

                                return acc;
                            },
                            {}
                    );

                    return ret;
                })

            groupings = _(groupings).sortBy(v => v.コースＣＤ * 1).value();

            return groupings;
        },
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.bushoCd && !!newVal.courseKbn;
            return ret;
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
