<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>配送日</label>
            </div>
            <div class="col-md-7">
                <input class="form-control p-0 text-center" type="text" style="width: 100px;" :value=FormattedDeliveryDate readonly tabindex="-1">
                <label class="ml-2 mr-1" style="width: unset; max-width: unset;">注文日時</label>
                <input class="form-control p-0 text-center" type="text" style="width: 135px;" :value=FormattedOrderDate readonly tabindex="-1">
                <label class="ml-2 mr-1">部署</label>
                <input class="form-control" type="text" style="width: 200px;" :value=params.部署名 readonly tabindex="-1">
            </div>
            <div class="col-md-4 justify-content-end">
                <label class="mr-1">状況</label>
                <input class="form-control text-center" type="text" style="width: 100px;" :value=DeliveryInfo readonly tabindex="-1">
                <label class="ml-1 mr-1">締切</label>
                <input class="form-control text-center" type="text" style="width: 150px;" :value=TimeoutInfo readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="max-width: unset;">Web得意先</label>
            </div>
            <div class="col-md-11">
                <input class="form-control" style="width: 150px;" type="text" :value=params.Web得意先ＣＤ readonly tabindex="-1">
                <input class="form-control" type="text" :value=params.Web得意先名 readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: unset; max-width: unset;">得意先(掛売)</label>
            </div>
            <div class="col-md-11">
                <input class="form-control" style="width: 150px;" type="text" :value=params.得意先ＣＤ_掛売 readonly tabindex="-1">
                <input class="form-control" type="text" :value=params.得意先名_掛売 readonly tabindex="-1">
                <label class="ml-1 mr-1">TEL</label>
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=params.電話番号_掛売 readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: unset; max-width: unset;">得意先(現金)</label>
            </div>
            <div class="col-md-11">
                <input class="form-control" style="width: 150px;" type="text" :value=params.得意先ＣＤ_現金 readonly tabindex="-1">
                <input class="form-control" type="text" :value=params.得意先名_現金 readonly tabindex="-1">
                <label  class="ml-1 mr-1">TEL</label>
                <input class="form-control p-0 text-center" style="width: 120px;" type="text" :value=params.電話番号_現金 readonly tabindex="-1">
            </div>
        </div>
        <PqGridWrapper
            :id='"DAI01032Grid1" + (!!params ? _uid : "")'
            ref="DAI01032Grid1"
            dataUrl="/DAI01032/Search"
            :query=this.searchParams
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :onSelectChangeFunc=onSelectChangeFunc
            :setMoveNextCell=setMoveNextCell
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
            classes="mt-2 mb-1 mr-3 ml-3"
        />
    </form>
</template>

<style scoped>
label {
    max-width: 60px;
}
.badge {
    font-size: 15px;
}
</style>
<style>
[pgid=DAI01032] .pq_grid svg.pq-grid-overlay {
    display: block;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01032",
    components: {
    },
    computed: {
        FormattedDeliveryDate: function() {
            var vue = this;
            return vue.params.配送日 ? moment(vue.params.配送日).format("YYYY/MM/DD") : null;
        },
        FormattedOrderDate: function() {
            var vue = this;
            return vue.params.注文日時 ? moment(vue.params.注文日時).format("YYYY/MM/DD HH:mm") : null;
        },
        searchParams: function() {
            var vue = this;
            return {
                WebOrderId: vue.params.Web受注ID,
                BushoCd: vue.params.部署CD,
                CustomerCd: vue.params.Web得意先ＣＤ,
                DeliveryDate: moment(vue.params.配送日).format("YYYYMMDD"),
                OrderDate: moment(vue.params.注文日時).format("YYYY-MM-DD HH:mm:ss"),
                EditUser: vue.getLoginInfo().uid,
                IsRegisted: vue.IsRegisted,
            };
        },
        TimeoutInfo: function() {
            var vue = this;

            if (!vue.params.締切時刻) return "締切設定無し";

            var dl = moment(moment(vue.params.配送日).format("YYYY/MM/DD ") + vue.params.締切時刻);
            if (moment().isAfter(dl)) {
                return "締切済: " + dl.format("HH:mm");
            } else {
                var dt = dl.diff(moment(), "days");

                var dur = moment.duration(dl.diff(moment()), "milliseconds");
                var formatter = new Intl.NumberFormat('ja', { minimumIntegerDigits: 2 });
                var dh =  formatter.format(Math.floor(dur.asHours()));
                var dm =  formatter.format(dur.minutes());

                return "あと: " + (!!dt ? (dt + "日") : (dh + ":" + dm));
            }
        },
        DeliveryInfo: function() {
            var vue = this;

            if (vue.IsDeliveried == null) return null;

            return !!vue.IsDeliveried ? "配達済" : "未配達";
        },
    },
    watch: {
        IsRegisted: {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI01032Grid1_Save").value = newVal == "1" ? "変更" : "取込";
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "Web受注内容",
            noViewModel: true,
            viewModel: {
            },
            IsRegisted: "0",
            IsDeliveried: null,
            ProductList: [],
            UserList: [],
            PlaceList: [],
            MemoList: [],
            DAI01032Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                toolbar: {
                    cls: "DAI01032_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRow(this, event);
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRow(this, event);
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: true },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                freezeCols: 6,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                    [
                        "現金金額",
                        function(rowData){
                            return rowData["単価"] * rowData["現金個数"];
                        }
                    ],
                    [
                        "掛売金額",
                        function(rowData){
                            return rowData["単価"] * rowData["掛売個数"];
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "利用者",
                        dataIndx: "利用者CD", dataType: "integer",
                        align: "left",
                        width: 150, maxWidth: 150, minWidth: 150,
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: () => vue.getUserList(),
                            bind: "利用者CD",
                            buddies: { "利用者ID": "CdNm", "得意先ＣＤ": "得意先ＣＤ", "得意先名": "得意先名",  },
                            AutoCompleteFunc: vue.UserAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                            render: ui => {
                                return { text: !!ui.rowData.得意先名 ? (ui.rowData.利用者CD + ": " + ui.rowData.得意先名) : ui.rowData.利用者CD };
                            },
                        },
                    },
                    {
                        title: "利用者名",
                        dataIndx: "備考1", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                    },
                    {
                        title: "利用者ID",
                        dataIndx: "利用者ID", dataType: "string",
                        key: true,
                        hidden: true,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: () => vue.getProductList(),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm", "単価": "単価", "商品区分": "商品区分", },
                            onSelect: rowData => {
                                console.log("onSelect", rowData);
                                rowData["現金金額"] = rowData["単価"] * rowData["現金個数"];
                                rowData["掛売金額"] = rowData["単価"] * rowData["掛売個数"];
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                    {
                        title: "現金",
                        dataIndx: "現金",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "現金個数", dataType: "integer", format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "現金金額", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    {
                        title: "掛売",
                        dataIndx: "掛売",
                        colModel: [
                            {
                                title: "個数",
                                dataIndx: "掛売個数", dataType: "integer", format: "#,##0",
                                width: 75, maxWidth: 75, minWidth: 75,
                                editable: true,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                            {
                                title: "金額",
                                dataIndx: "掛売金額", dataType: "integer", format: "#,##0",
                                width: 100, maxWidth: 100, minWidth: 100,
                                sortable: false,
                                render: ui => {
                                    if (!ui.rowData.pq_grandsummary) {
                                    }
                                    return ui;
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                            },
                        ],
                    },
                    {
                        title: "届け先",
                        dataIndx: "届け先ID", dataType: "integer",
                        align: "left",
                        width: 200, maxWidth: 200, minWidth: 200,
                        editable: ui => !!vue.PlaceList.length,
                        autocomplete: {
                            source: () => vue.getPlaceList(),
                            bind: "届け先ID",
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                            render: ui => {
                                var match = vue.PlaceList.find(v => v.Cd == ui.rowData.届け先ID);
                                return { text: !!match ? match.CdNm : null };
                            },
                        },
                    },
                    {
                        title: "備考",
                        dataIndx: "備考", dataType: "integer",
                        align: "left",
                        width: 200, maxWidth: 200, minWidth: 200,
                        editable: false, //ui => !!vue.MemoList.length,
                        // autocomplete: {
                        //     source: () => vue.getMemoList(),
                        //     bind: "備考ID",
                        //     AutoCompleteMinLength: 0,
                        //     selectSave: true,
                        //     render: ui => {
                        //         var match = vue.MemoList.find(v => v.Cd == ui.rowData.備考ID);
                        //         return { text: !!match ? match.CdNm : null };
                        //     },
                        // },
                    },
                ],
            },
        });

        if (!!vue.params) {
            data.IsRegisted = vue.params.確認;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01032Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "行削除", id: "DAI01032Grid1_DeleteRow", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "Web得意先<br>メンテ", id: "DAI01032Grid1_showCustomerMaint", disabled: true, shortcut: "F7",
                    onClick: function () {
                        vue.showCustomerMaint();
                    }
                },
                { visible: "true", value: "Web得意先<br>単価メンテ", id: "DAI01032Grid1_showProductMaint", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showProductMaint();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "注文取込", id: "DAI01032Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveOrder();
                    }
                },
                { visible: "true", value: "注文入力<br>確認(掛売)", id: "DAI01032Grid1_showOrder_1", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.show01030(1);
                    }
                },
                { visible: "true", value: "注文入力<br>確認(現金)", id: "DAI01032Grid1_showOrder_1", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.show01030(0);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI01032Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI01032Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                }
            );
            vue.$watch(
                "$refs.DAI01032Grid1.isSelection",
                isSelection => {
                    vue.footerButtons.find(v => v.id == "DAI01032Grid1_DeleteRow").disabled = !isSelection;
                }
            );

            //配達済/未配達
            var posts = [];
            if (!!vue.params.得意先ＣＤ_現金) {
                posts.push(axios.post("/api/DAI01030IsDeliveried.php", {CustomerCd: vue.params.得意先ＣＤ_現金, DeliveryDate: vue.searchParams.DeliveryDate, noCache: true}));
            }
            if (!!vue.params.得意先ＣＤ_掛売) {
                posts.push(axios.post("/api/DAI01030IsDeliveried.php", {CustomerCd: vue.params.得意先ＣＤ_掛売, DeliveryDate: vue.searchParams.DeliveryDate, noCache: true}));
            }

            if (!!posts.length) {
                Promise.all(posts)
                    .then(ret => {
                        vue.IsDeliveried = ret.every(v => !!v.data.IsDeliveried);
                    });
            }

            if (!!vue.params && vue.params.Web得意先ＣＤ) {
                //TODO: メンテ機能は現在停止
                // vue.footerButtons.find(v => v.id == "DAI01032Grid1_showCustomerMaint").disabled = false;
                // vue.footerButtons.find(v => v.id == "DAI01032Grid1_showProductMaint").disabled = false;
            }

            vue.footerButtons.find(v => v.id == "DAI01032Grid1_Save").value = vue.IsRegisted == "1" ? "注文変更" : "注文取込";

            vue.conditionChanged(true);
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI01032Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.showLoading();

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //事前検索
            axios.all(
                [
                    //商品リスト検索
                    axios.post("/DAI01032/GetProductList", params),
                    //利用者リスト検索
                    axios.post("/DAI01032/GetUserList", params),
                    //届け先リスト検索
                    axios.post("/DAI01032/GetPlaceList", params),
                    //備考リスト検索
                    axios.post("/DAI01032/GetMemoList", params),
                ]
            ).then(
                axios.spread((responseProduct, responseUser, responsePlace, responseMemo) => {
                    grid.hideLoading();

                    var resProduct = responseProduct.data;
                    var resUser = responseUser.data;
                    var resPlace = responsePlace.data;
                    var resMemo = responseMemo.data;

                    var checkError = (res, name) => {
                        if (res.onError && !!res.errors) {
                            //メッセージリストに追加
                            Object.values(res.errors).filter(v => v)
                                .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                            //ダイアログ
                            $.dialogErr({ errObj: res });

                            return false;
                        } else if (res.onException) {
                            //メッセージ追加
                            vue.$root.$emit("addMessage", name +"リスト取得失敗(" + vue.page.ScreenTitle + ":" + res.message + ")");

                            //ダイアログ
                            $.dialogErr({
                                title: "異常終了",
                                contents: name +"リストの取得に失敗しました<br/>" + res.message,
                            });

                            return false;
                        } else if (res == "") {
                            //完了ダイアログ
                            $.dialogErr({
                                title: name +"リスト無し",
                                contents: "該当データは存在しません" ,
                            });

                            return false;
                        }

                        return true;
                    };

                    if (!checkError(resProduct, "Web得意先単価")) return;
                    if (!checkError(resUser, "利用者")) return;

                    vue.ProductList = resProduct;
                    vue.UserList = resUser;
                    vue.PlaceList = resPlace;
                    vue.MemoList = resMemo;

                    vue.DAI01032Grid1.searchData(vue.searchParams);
                })
            )
            .catch(error => {
                grid.hideLoading();

                //メッセージ追加
                vue.$root.$emit("addMessage", "DB検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "DBの検索に失敗しました<br/>",
                });
            });
        },
        getProductList: function() {
            var vue = this;
            return vue.ProductList;
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList || !dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
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
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getUserList: function() {
            var vue = this;
            return vue.UserList;
        },
        UserAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList || !dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
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
                    ret.label = v.Cd + (!!v.得意先ＣＤ ? (" : " + v.得意先名) : "");
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getPlaceList: function() {
            var vue = this;
            return vue.PlaceList;
        },
        getMemoList: function() {
            var vue = this;
            return vue.MemoList;
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "単価": 0,
                "現金個数": 0,
                "現金金額": 0,
                "掛売個数": 0,
                "掛売金額": 0,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品ＣＤ"];
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                var data = grid.pdata[0];
                var colIndx = !data["商品ＣＤ"] ? grid.columns["商品ＣＤ"].leftPos
                    : _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos;
                grid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            return res;
        },
        onSelectChangeFunc: function(grid, ui) {
        },
        setMoveNextCell: function(grid, ui, reverse) {
            if (grid.getEditCell().$editor) {
                grid.saveEditCell();
            }

            if (ui.dataIndx == "商品ＣＤ") {
                grid.setSelection({
                    rowIndx: ui.rowIndx,
                    colIndx: _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos,
                });
            } else if (ui.dataIndx.includes("個数")) {
                grid.setSelection({
                    rowIndx: ui.rowIndx + 1,
                    colIndx: grid.columns["商品ＣＤ"].leftPos,
                });
            } else {
                return true;
            }

            return false;
        },
        saveOrder: function() {
            var vue = this;
            var grid = vue.DAI01032Grid1;

            var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            var hasError = checkError(grid);

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var checkRequire = grid => grid.pdata.map(r => [r.商品ＣＤ]).every(r => r.every(v => !!v) || r.every(v => !v));

            var require = checkRequire(grid);

            if(!require){
                $.dialogErr({
                    title: "未入力項目",
                    contents: "未入力項目があるため、登録できません。",
                });
                return;
            }

            var changes = _.cloneDeep(grid.createSaveParams());
            var SaveList = changes.AddList.concat(changes.UpdateList).filter(v => !!v.商品ＣＤ);

            //注文データの型に整形
            SaveList.forEach((v, i) => {
                v.修正担当者ＣＤ = vue.getLoginInfo().uid;

                delete v.備考ID;
                delete v.得意先名;
                delete v.商品区分;
                delete v.商品名;
                delete v.現金;
                delete v.掛売;
            });

            var DeleteList = grid.getChanges().deleteList
                .map(v => _.cloneDeep(v.InitialValue));

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI01032/Save",
                    params: {
                        SaveList: SaveList,
                        DeleteList: DeleteList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: vue.IsRegisted == "1" ? "Web受注注文変更" : "Web受注注文取込",
                        message:
                            (
                                vue.IsRegisted == "1"
                                    ? "Web受注の注文内容を変更します。"
                                    : "Web受注の注文内容を取り込みます。"
                            )
                            + "宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.current);
                            } else {
                                if (vue.IsRegisted == "0") {
                                    // $.dialogInfo({
                                    //     title: "Web受注注文取込完了",
                                    //     contents: "Web受注の注文内容を取り込みました。以降の変更は自動で取り込まれます。",
                                    // });
                                    vue.IsRegisted = "1";

                                    if (!!vue.params && !!vue.params.Grid) {
                                        vue.params.Grid.refreshDataAndView();
                                    }
                                }

                                grid.refreshDataAndView();
                            }

                            return false;
                        },
                    },
                }
            );
        },
        deleteOrder: function() {
            var vue = this;
            var grid = vue.DAI01032Grid1;

            //削除実行
            grid.saveData(
                {
                    uri: "/DAI01032/Delete",
                    params: {
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "Web受注注文削除確認",
                        message: "Web受注の注文を削除します。"
                            + (vue.IsRegisted == "1" ? "取込済みの注文も同時に削除します。" : "")
                            + "宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            console.log("res", res);

                            if (!!res.edited && !!res.edited.length) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.edited);
                            } else {
                                grid.clearData();

                                if (!!vue.params && !!vue.params.Grid) {
                                    vue.params.Grid.refreshDataAndView();
                                }

                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            }

                            return false;
                        },
                    },
                }
            );
        },
        addRow: function(grid, event) {
            var vue = this;
        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI01032Grid1;

            if(!grid.Selection()._areas.length){
                return;
            }

            var rowIndx = grid.Selection()._areas[0].r1;
            grid.deleteRow({ rowIndx: rowIndx });
        },
        showCustomerMaint: function() {
            var vue = this;

            var cd = vue.viewModel.CustomerCd;
            if (!cd) return;

            var params = {CustomerCd: cd, noCache: true};
            axios.post("/Utilities/GetCustomerListForMaint", params)
                .then(res => {
                    if (res.data.Data.length == 1) {
                        var params = _.cloneDeep(res.data.Data[0]);
                        params.IsNew = false;
                        params.Parent = vue;

                        //DAI04041を子画面表示
                        PageDialog.show({
                            pgId: "DAI04041",
                            params: params,
                            isModal: true,
                            isChild: true,
                            width: 1200,
                            height: 700,
                        });
                    }
                })
                .catch(err => {
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "得意先マスタの検索に失敗しました"
                    })
                })
        },
        updateCustomer: function() {
            var vue = this;
            var ps = vue.$refs.PopupSelect_Customer;

            ps.getDataList(null, () => {
                console.log("update customer", ps.selectRow);
                var info = ps.dataList.find(v => v.Cd == vue.viewModel.CustomerCd);
                vue.CustomerChanged(info);
            });
        },
        showProductMaint: function() {
            var vue = this;

            var cd = vue.viewModel.CustomerCd;
            if (!cd) return;

            var params = { 得意先ＣＤ: cd, 得意先名: vue.viewModel.CustomerNm };
            params.IsNew = false;
            params.Available = "1";
            params.TargetDate = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");
            params.Parent = vue;

            //DAI04051を子画面表示
            PageDialog.show({
                pgId: "DAI04051",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 880,
                height: 600,
                onBeforeClose: (event, ui) => {
                    console.log("onBeforeClose", event, ui);

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

                    return !editting && !isEscOnEditor;
                }
            });
        },
        updateProduct: function() {
            var vue = this;
            var grid = vue.DAI01032Grid1;

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //商品リスト検索
            axios.post("/DAI01032/GetProductList", params)
                .then(res => {
                    vue.ProductList = res.data;
                    grid.getData()
                        .filter(r => !r.InitialValue && !!r.商品ＣＤ)
                        .forEach(r => {
                            var pd = vue.ProductList.find(p => p.商品ＣＤ == r.商品ＣＤ);
                            if (!!pd) {
                                r.単価 = pd.単価;
                                r.現金金額 = r.単価 * 1 * r.現金個数 * 1;
                                r.掛売金額 = r.単価 * 1 * r.掛売個数 * 1;
                            }
                        });
                    grid.refreshView();
                })
                .catch(err => {
                    console.log("/DAI01032/GetProductList Error", err)
                    $.dialogErr({
                        title: "商品マスタ検索失敗",
                        contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });
        },
        show01030: function(IsKake) {
            var vue = this;

            var params = {
                BushoCd: vue.params.部署CD,
                CustomerCd: vue.params["得意先ＣＤ_" + (!!IsKake ? "掛売" : "現金")],
                CustomerNm: vue.params["得意先名_" + (!!IsKake ? "掛売" : "現金")],
                DeliveryDate: moment(vue.params.配送日).format("YYYY年MM月DD日"),
                IsChild: true,
                Parent: vue,
            };

            PageDialog.show({
                pgId: "DAI01030",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1250,
                height: 775,
            });
        },
    }
}
</script>
