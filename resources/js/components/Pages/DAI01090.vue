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
                    customStyle="width: 100px"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelectKeyDown
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ CustomerCd: null, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
                    :inputWidth=150
                    :nameWidth=400
                    :onKeyDownEnterFunc=onCustomerChanged
                    :isShowAutoComplete=false
                    :isRealTimeSearch=false
                    :onAfterChangedFunc=onCustomerChanged
                />
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

        <PqGridWrapper
            id="DAI01090Grid1"
            ref="DAI01090Grid1"
            dataUrl="/DAI01090/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :checkChanged=true
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI01090Grid1 svg.pq-grid-overlay {
    display: block;
}
#DAI01090Grid1 .pq-grid-cell.holiday {
    color: red;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";
import PopupSelectKeyDown from "@vcs/PopupSelectKeyDown.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01090",
    components: {
        "PopupSelectKeyDown": PopupSelectKeyDown,
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                DeliveryDate: moment(this.viewModel.DeliveryDate, "YYYY年MM月").format("YYYYMMDD"),
                DateStart: moment(this.viewModel.DeliveryDate, "YYYY年MM月").startOf("month").format("YYYYMMDD"),
                DateEnd: moment(this.viewModel.DeliveryDate, "YYYY年MM月").endOf("month").format("YYYYMMDD"),
                CustomerCd: this.viewModel.CustomerCd,
            };
        },
        bushoDisabled: function() {
            return true;
        },
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
                CustomerCd: null,
                CustomerNm: null,
            },
            DAI01090Grid1: null,
            ProductList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true, column: true, },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                rowHtHead: 50,
                rowHt: 35,
                freezeCols: 1,
                fillHandle: "all",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                editModel: {
                    onSave: null,
                    onTab: "nextFocus",
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
                        title: "配送日",
                        dataIndx: "配送日", dataType: "date", format: "yy/mm/dd", align: "center",
                        width: 100, maxWidth: 100, minWidth: 100,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                ui.rowData.CustomerName = "合計";
                                ui.cls.push("justify-content-end");
                                return { text: "合計" };
                            } else {
                                if (ui.rowData["休日指定"] == 1) {
                                    ui.cls.push("holiday");
                                }
                                return ui;
                            }
                        },
                        fixed: true,
                    },
                ],
                headerCellClick: function (event, ui) {
                    console.log("headerCellClick", event, ui);
                },
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
                { visible: "true", value: "コピー", id: "DAI01090Grid1_Copy", disabled: true, shortcut: "F6",
                    onClick: function () {
                        var grid = vue.DAI01090Grid1;

                        var selected = grid.getSelectionData();
                        if (_.isArray(selected)) {
                            selected = selected[0];
                        }
                        selected = _.pickBy(selected, (v, k) => k.startsWith("商品_"));

                        var startRowIndx = grid.getSelectionRowData().pq_ri;

                        var rowList = grid.getData()
                            .filter(r => r.pq_ri > startRowIndx && r.休日指定 == "0")
                            .map(r => { return { rowIndx: grid.getRowIndx({rowData: r }).rowIndx,  newRow: selected, }; });

                        grid.updateRow({ rowList: rowList });
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI01090Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.saveOrder();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //配送日付の初期値 -> 当日
            vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI01090Grid1.isSelection",
                val => {
                    vue.footerButtons.find(v => v.id == "DAI01090Grid1_Copy").disabled = !val;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
        },
        onDeliveryDateChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;
            var selectname = $(vue.$el).find(".select-name").val();
            if(selectname==""){
                vue.clearData();
                return;
            }

            console.log("カスタマーチェンジ",entity);//TODO:

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.CustomerCd = entity["Cd"];
                vue.viewModel.BushoCd = entity["部署CD"];

                //条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI01090Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.CustomerCd) return;
            console.log("conditionChanged", vue.viewModel);

            vue.refreshCols(() => grid.searchData(vue.searchParams, false, null, callback));
        },
        refreshCols: function(callback) {
            var vue = this;
            var grid = vue.DAI01090Grid1;

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

                //商品リストの取得
                axios.post("/DAI01090/GetProductList", vue.searchParams)
                .then(res => {
                    var list = _.cloneDeep(res.data);

                    var newCols = grid.options.colModel
                        .filter(v => !!v.fixed)
                        .concat(
                            _.uniqBy(list, "商品ＣＤ").map((v, i) => {
                                return {
                                    title: v.商品略称,
                                    dataIndx: "商品_" + v.商品ＣＤ,
                                    dataType: "integer",
                                    format: "#,###",
                                    width: 75, maxWidth: 75, minWidth: 75,
                                    editable: true,
                                    render: ui => {
                                        if (ui.rowData.pq_grandsummary) {
                                            if(ui.cellData == 0) {
                                                return { text: "" };
                                            }
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

                    //商品リスト保持
                    vue.ProductList = res.data;

                    if (!!grid) grid.hideLoading();

                    if(callback) callback();
                })
                .catch(err => {
                    console.log(err);
                    if (!!grid) grid.hideLoading();

                    //失敗ダイアログ
                    $.dialogErr({
                        title: "得意先単価検索失敗",
                        contents: "得意先単価の検索に失敗しました" + "<br/>" + err.message,
                    });
                });
            });
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;

            //集計
            var groupings = _(res)
                .groupBy(v => v.配送日)
                .values()
                .value()
                .map(r => {
                    var ret = _.reduce(
                            _.sortBy(r, ["明細行Ｎｏ"]),
                            (acc, v, j) => {
                                acc = _.isEmpty(acc) ? _.cloneDeep(v) : acc;

                                acc["商品_" + v.商品ＣＤ] = (acc["商品_" + v.商品ＣＤ] || 0)
                                                         + (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数) * 1;

                                acc["明細行Ｎｏ_" + v.商品ＣＤ] = acc["明細行Ｎｏ_" + v.商品ＣＤ] || v.明細行Ｎｏ;

                                acc["注文データ更新日時_" + v.商品ＣＤ] =
                                    !acc["注文データ更新日時_" + v.商品ＣＤ] ? v.修正日 :
                                        (!!v.修正日 && moment(v.修正日).isAfter(acc["注文データ更新日時_" + v.商品ＣＤ])) ? v.修正日 :
                                        acc["注文データ更新日時_" + v.商品ＣＤ];

                                return acc;
                            },
                            {}
                    )

                    return ret;
                })

            groupings = _(groupings).sortBy(v => v.配送日 * 1).value();

            vue.footerButtons.find(v => v.id == "DAI01090Grid1_Save").disabled = !groupings.length;

            return groupings;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                grid.setSelection({ rowIndx: 0, colIndx: 1 });
            }
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
                    ret.label = v.Cd + " : " + "【" + (v.部署名 || "部署無し") + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        saveOrder: function() {
            var vue = this;
            var grid = vue.DAI01090Grid1;

            var changes = _.cloneDeep(grid.createSaveParams());

            var SaveList = [];

            changes.UpdateList.forEach((r, i) => {
                var orderBase = {
                    注文区分: 0,
                    注文日付: !!r.注文日付
                        ? moment(r.注文日付, "YYYYMMDD").format("YYYY-MM-DD")
                        : moment(r.配送日).format("YYYY-MM-DD"),
                    注文時間: r.注文時間 || moment().format("HH:mm:ss"),
                    部署ＣＤ: vue.viewModel.BushoCd,
                    得意先ＣＤ: vue.viewModel.CustomerCd,
                    配送日: r.配送日,
                    入力区分: 0,
                    備考１: r.備考１,
                    備考２: r.備考２,
                    備考３: r.備考３,
                    備考４: r.備考４,
                    備考５: r.備考５,
                    予備金額２: 0,
                    予備ＣＤ１: 0,
                    予備ＣＤ２: 0,
                    修正担当者ＣＤ: vue.getLoginInfo().uid,
                    特記_社内用: r.備考社内,
                    特記_配送用: r.備考配送,
                    特記_通知用: r.備考通知,
                };

                var d = diff(changes.OldList[i], r);

                _.forIn(d, (v, k) => {
                    var cd = k.replace("商品_", "");

                    var product = vue.ProductList.find(p => p.商品ＣＤ == cd && moment(p.適用開始日).isSameOrBefore(moment(r.配送日)));

                    var order = _.cloneDeep(orderBase);

                    order.商品ＣＤ = cd;
                    order.商品区分 = product.商品区分;
                    order.明細行Ｎｏ = r["明細行Ｎｏ_" + cd];
                    order.現金個数 = r.売掛現金区分 == "0" ? v : null;
                    order.現金金額 = r.売掛現金区分 == "0" ? v * product.単価 : null;
                    order.掛売個数 = r.売掛現金区分 == "1" ? v : null;
                    order.掛売金額 = r.売掛現金区分 == "1" ? v * product.単価 : null;
                    order.予備金額１ = product.単価;
                    order.修正日 = r["注文データ更新日時_" + cd];

                    SaveList.push(order);
                });
            });

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI01090/Save",
                    params: {
                        SaveList: SaveList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "確認 " + vue.viewModel.CustomerNm,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (res.edited.length == 0) {
                                grid.commit();
                                vue.conditionChanged();
                                return false;
                            }

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
        },
    }
}
</script>
