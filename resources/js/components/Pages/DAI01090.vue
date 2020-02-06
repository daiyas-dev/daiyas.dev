<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                    :disabled=bushoDisabled
                />
            </div>
            <div class="col-md-1">
                <label>配送日</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月"
                    dayViewHeaderFormat="YYYY年"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/DAI01030/GetCustomerAndCourseList"
                    :params="{ targetDate: FormattedDeliveryDate, keyword: viewModel.CustomerCd }"
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                        { title: "コースCD", dataIndx: "コースCD", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 200, maxWidth: 200, minWidth: 200 }
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
                <label class="label-blue text-center">TEL</label>
                <input class="form-control p-0 text-center label-blue" style="width: 120px;" type="text" :value=viewModel.TelNo readonly tabindex="-1">
                <label class="ml-1 label-blue">{{viewModel.PaymentNm}}</label>
            </div>
        </div>

        <PqGridWrapper
            id="DAI01090Grid1"
            ref="DAI01090Grid1"
            dataUrl="/DAI01090/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
        />
    </form>
</template>

<style>
#DAI01090Grid1 .pq-grid-cell.CustomerNameCell {
    white-space: pre;
}
#DAI01090Grid1 .pq-grid-cell.order-value {
    color: red;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01090",
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
                CourseCd: this.viewModel.CourseCd,
            };
        }
    },
    watch: {
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = _.values(newVal).some(v => !v);
                vue.footerButtons.find(v => v.id == "DAI01090Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日時処理 > 一括注文入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                DeliveryDate: null,
                CourseCd: null,
            },
            DAI01090Grid1: null,
            PatternList: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 2,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                editable: true,
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
                        title: "得意先",
                        dataIndx: "CustomerName", dataType: "string",
                        width: 250, maxWidth: 250, minWidth: 250,
                        cls: "CustomerNameCell",
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                ui.rowData.CustomerName = "合計";
                                ui.cls.push("justify-content-end");
                                return { text: "合計" };
                            } else {
                                var el = $("<div>").addClass("d-flex")
                                    .append($("<label>").css("width", "75px").css("text-align", "right").text(ui.rowData.得意先ＣＤ))
                                    .append($("<div>").css("margin-left", "4px").text(ui.rowData.得意先名))
                                    ;
                                return { text: el.prop("outerHTML") };
                            }
                        },
                        fixed: true,
                    },
                    {
                        title: "製造パターン",
                        dataIndx: "製造パターン", dataType: "string",
                        width: 150, maxWidth: 150, minWidth: 150,
                        editable: true,
                        cls: "pq-dropdown pq-side-icon",
                        selectList: "PatternList",
                        selectLabel: "Cd + ':' + CdNm ",
                        selectNullFirst: false,
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
                { visible: "true", value: "検索", id: "DAI01090Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI01090Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01090Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI01090Grid1;

                        var changes = _.cloneDeep(grid.createSaveParams());

                        var productUpdateList = [];
                        var patternUpdateList = [];

                        changes.UpdateList.forEach((r, i) => {
                            var d = diff(changes.OldList[i], r);

                            _.forIn(d, (v, k) => {
                                if (k.startsWith("商品_")) {
                                    var product = {};
                                    var cd = k.replace("商品_", "");
                                    product.部署ＣＤ = r.部署ＣＤ;
                                    product.得意先ＣＤ = r.得意先ＣＤ;
                                    product.日付 = vue.searchParams.DeliveryDate;
                                    product.商品ＣＤ = cd;
                                    product.見込数 = !v ? 0 : (v * 1);
                                    product.見込入力 = 1;
                                    product.更新フラグ = 0;
                                    product.修正日 = r["予測データ更新日時_" + cd];

                                    productUpdateList.push(product);

                                } else if (k == "製造パターン") {
                                    var pattern = {};
                                    pattern.部署ＣＤ = r.部署ＣＤ;
                                    pattern.製造日 = vue.searchParams.DeliveryDate;
                                    pattern.コースＣＤ = r.コースＣＤ;
                                    pattern.得意先ＣＤ = r.得意先ＣＤ;
                                    pattern.製造パターン = r.製造パターン;
                                    pattern.修正担当者ＣＤ = vue.getLoginInfo().uid;
                                    pattern.修正日 = r.製造パターン更新日時;

                                    patternUpdateList.push(pattern);

                                }
                            });
                        });

                        console.log("01090 save", productUpdateList, patternUpdateList);

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI01090/Save",
                                params: {
                                    ProductList: productUpdateList,
                                    PatternList: patternUpdateList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: true,
                                },
                                done: {
                                    isShow: false,
                                    callback: (gridVue, grid, res)=>{
                                        var compare = vue.onAfterSearchFunc(gridVue, grid, res.edited);
                                        var d = diff(vue.DAI01090Grid1.getPlainPData(), compare);

                                        _.forIn(d, (v, k) => {
                                            var r = _.omitBy(v, (vv, kk) => vv == undefined);
                                            if (_.isEmpty(r)) {
                                                delete d[k];
                                            } else {
                                                d[k] = r;
                                            }
                                        })

                                        if (_.isEmpty(d)) {
                                            grid.commit();
                                        } else {
                                            if (res.skipped) {
                                                $.dialogInfo({
                                                    title: "登録チェック",
                                                    contents: "他で変更されたデータがあります。",
                                                });
                                            }

                                            grid.blinkDiff(compare, true);
                                        }

                                        return false;
                                    },
                                },
                            }
                        );
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            //TODO
            // vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.DeliveryDate = moment("20190905").format("YYYY年MM月DD日");
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
                    grid = vue.DAI01090Grid1;
                    if (!!grid && vue.getLoginInfo().isLogOn) {
                        clearInterval(timer);
                        return resolve(grid);
                    }
                }, 100);
            })
            .then((grid) => {
                grid.showLoading();

                axios.post("/DAI01090/ColSearch", { BushoCd: vue.viewModel.BushoCd })
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
                                        editable: ui => !ui.rowData[ui.dataIndx + "_実績"],
                                        render: ui => {
                                            if (!ui.cellData) {
                                                ui.rowData[ui.dataIndx] = 0;
                                                ui.text = 0;
                                            }
                                            if (ui.rowData[ui.dataIndx + "_注文"] > 0) {
                                                ui.cls.push("order-value");
                                            }
                                            return ui;
                                        },
                                        summary: {
                                            type: "TotalInt",
                                        },
                                        custom: true,
                                    };
                                })
                            )

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

            //条件変更ハンドラ
            vue.conditionChanged();
        },

        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01090Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.CourseCd) return;
            if (!grid.options.colModel.some(v => v.custom)) return;

            grid.searchData(vue.searchParams, false, null, callback);
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            axios.post("/Utilities/GetCodeList", { cd: 35 })
                .then(res => {
                    console.log("製造パターン", res.data);
                    vue.PatternList = res.data;
                    callback();
                })
                .catch(err => {
                    $.dialogErr({
                        title: "異常終了",
                        contents: "各種テーブルの取得に失敗しました<br/>" + err.message,
                    });
                });
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
                                acc = _.isEmpty(acc) ? _.cloneDeep(v) : acc;

                                delete acc.商品ＣＤ;
                                delete acc.見込数;
                                delete acc.CHU商品ＣＤ;
                                delete acc.注文数;
                                delete acc.注文データ更新日時;
                                delete acc.予測データ更新日時;

                                acc["商品_" + v.商品ＣＤ + "_注文"] = (acc["商品_" + v.商品ＣＤ + "_注文"] || 0) + v.注文数 * 1;
                                acc["商品_" + v.商品ＣＤ + "_見込"] = (acc["商品_" + v.商品ＣＤ + "_見込"] || 0) + v.見込数 * 1;

                                acc["予測データ更新日時_" + v.商品ＣＤ] = !acc["予測データ更新日時_" + v.商品ＣＤ] ? v.予測データ更新日時
                                    : (!!v.予測データ更新日時 && moment(v.予測データ更新日時).isAfter(acc["予測データ更新日時_" + v.商品ＣＤ]))
                                        ? v.予測データ更新日時 : acc["予測データ更新日時_" + v.商品ＣＤ];
                                acc["注文データ更新日時_" + v.商品ＣＤ] = !acc["注文データ更新日時_" + v.商品ＣＤ] ? v.注文データ更新日時
                                    : (!!v.注文データ更新日時 && moment(v.注文データ更新日時).isAfter(acc["注文データ更新日時_" + v.商品ＣＤ]))
                                        ? v.注文データ更新日時 : acc["注文データ更新日時_" + v.商品ＣＤ];

                                return acc;
                            },
                            {}
                    )

                    _.keys(ret).forEach(k => {
                        if (k.startsWith("商品_")) {
                            var cd = k.replace("商品_", "").replace("_注文", "").replace("_見込", "");

                            ret["商品_" + cd] = (ret["商品_" + cd + "_注文"] > 0 ? ret["商品_" + cd + "_注文"] : ret["商品_" + cd + "_見込"]) + "";
                            ret["商品_" + cd + "_実績"] = ret["商品_" + cd + "_注文"] > 0;
                        }
                    });

                    return ret;
                })

            groupings = _(groupings).sortBy(v => v.ＳＥＱ * 1).sortBy(v => v.コースＣＤ * 1).value();

            vue.footerButtons.find(v => v.id == "DAI01090Grid1_Save").disabled = !groupings.length;

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
