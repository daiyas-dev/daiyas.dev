<template>
    <form id="this.$options.name">
        <PqGridWrapper
            id="PID0101Grid1"
            ref="PID0101Grid1"
            dataUrl="/PID0101/Search"
            classes="mt-2 ml-3 mr-3"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onBeforeCreateFunc=this.onBeforeCreateGridFunc
            :onRefreshFunc=this.onRefreshGridFunc
            :onAddRowFunc=this.onAddRowFunc
            :options=this.grid1Options
            :autoEmptyRow=false
            :autoEmptyRowCount=1
        />
    </form>
</template>

<style>
#PID0101Grid1 .pq-group-toggle-none {
    display: none !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

import PopupSelect from "@vcs/PopupSelect.vue";

export default {
    mixins: [PageBaseMixin],
    name: "PID0101",
    components: {
        "PopupSelect": PopupSelect,
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "分類集計",
            PID0101Grid1: null,
            UnitList: [],
            MajorList: [],
            MinorList: [],
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 35,
                rowHt: 35,
                editable: true,
                columnTemplate: {
                    editable: true,
                    sortable: true,
                },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                groupModel: {
                    on: true,
                    header: false,
                    dataIndx: ["GroupKey"],
                    collapsed: [false],
                    merge: false,
                    showSummary: [true],
                    grandSummary: true,
                    summaryEdit: false,
                    icon: ["pq-group-toggle-none"],
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                    sorter: [
                        { dataIndx: "MinorNo", dir: "up" },
                    ],
                },
                formulas: [
                    [
                        "GroupKey",
                        function(rowData){
                            if (!!rowData.MajorNo && !!rowData.MinorNo &&
                                rowData.MinorNo.startsWith(rowData.MajorNo) &&
                                (!!rowData.Volume && !!rowData.Unit && !!rowData.UPrice1000)
                            ) {
                                return rowData.MajorNo;
                            } else {
                                return rowData.GroupKey;
                            }
                        }
                    ],
                    [
                        "TPrice1000",
                        function(rowData){
                            if (!!rowData.Volume && rowData.UPrice1000 && !isNaN(rowData.Volume * rowData.UPrice1000)) {
                                return Decimal.mul(rowData.Volume, rowData.UPrice1000).toNumber();
                            } else {
                                return null;
                            }
                        }
                    ],
                    ["UPrice", function(rowData){
                        var price = rowData.UPrice1000 * 1000;
                        price = (price == 0 || isNaN(price)) ? null : price;
                        return price;
                    }],
                    ["TPrice", function(rowData){
                        var price = rowData.TPrice1000 * 1000;
                        price = (price == 0 || isNaN(price)) ? null : price;
                        return price;
                    }],
                ],
                colModel: [
                    { title: "集計キー", dataType: "string",  dataIndx: "GroupKey" , editable: false, hidden: true, },
                    { title: "識別番号", dataType: "string",  dataIndx: "UID" , editable: false, hidden: true, key: true,},
                    {
                        title: "大分類",
                        dataIndx: "MajorNo", dataType: "string", key: true,
                        width: 100, maxWidth: 150, minWidth: 100,
                        selectList: "MajorList", selectLabel: "Cd + ':' + CdNm ", selectNullFirst: true,
                        editable: function(ui) {
                            return !!ui.rowData ? ui.rowData.pq_level == undefined : false;
                        },
                    },
                    {
                        title: "小分類",
                        dataIndx: "MinorNo", dataType: "string", key: true,
                        width: 150, maxWidth: 150, minWidth: 150,
                        selectList: "MinorList", selectLabel: "Cd + ':' + CdNm ", selectNullFirst: true,
                        predicate: function(rowData, column, val) {
                            var MajorNo = rowData.MajorNo;
                            if (!MajorNo) return false;
                            return val.Cd.startsWith(rowData.MajorNo);
                        },
                        editable: function(ui) {
                            return !!ui.rowData ? ui.rowData.pq_level == undefined : false;
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "Volume", dataType: "float", format: "##,###",
                        width: 50, maxWidth: 100, minWidth: 50,
                    },
                    {
                        title: "単位",
                        dataIndx: "Unit", dataType: "string",
                        width: 100, maxWidth: 100, minWidth: 100,
                        selectList: "UnitList", selectNullFirst: true,
                    },
                    {
                        title: "単価(千円)",
                        dataIndx: "UPrice1000", dataType: "float", format: "##,###.00",
                        width: 100, maxWidth: 125, minWidth: 100,
                    },
                    {
                        title: "試算(千円)",
                        dataIndx: "TPrice1000", dataType: "float", format: "##,###.0",
                        width: 100, maxWidth: 125, minWidth: 100,
                        editable: false,
                        summary: {
                            type: "SubTotal",
                        },
                    },
                    { title: "単価", dataIndx: "UPrice", dataType: "float", hidden: true },
                    { title: "試算", dataIndx: "TPrice", dataType: "float", hidden: true },
                    {
                        title: "備考",
                        dataIndx: "Memo", dataType: "string",
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            //PqGrid集計関数に小計追加
            pq.aggregate.SubTotal = function(arr, col) {
                return pq.formatNumber(pq.aggregate.sum(arr, col), "##,###.0");
            };
        },
        mountedFunc: function(vue) {
        },
        setFooterButtons: function(vue) {
            vue.$root.$emit("setFooterButtons",
                [
                    { visible: "true", value: "保存", id: "PID0101Grid1_Save", disabled: true,
                        onClick: function () {
                            var vm = vue.viewModel;
                            var grid = vue.PID0101Grid1;

                            //パラメータの生成
                            var params = grid.createSaveParams();

                            //PqGridのデータ保存メソッドを呼び出す
                            grid.saveData(
                                {
                                    uri: "/PID0101/Save",
                                    params: params,
                                    done: {
                                        callback: (gridVue, grid, res) => {
                                        },
                                    },
                                }
                            );
                        }
                    },
                    { visible: "true", value: "XXX", id: "PID0101Grid1_XXX", disabled: true,
                        onClick: function () {
                        }
                    },
                    {
                        visible: "true", value: "終了", align: "right",
                        class: "btn-danger",
                        onClick: function() {
                            //確認ダイアログ
                            $.dialogConfirm({
                                title: "確認",
                                contents: "終了してよろしいですか？",
                                buttons:[
                                    {
                                        text: "はい",
                                        class: "btn btn-primary",
                                        click: function(){
                                            $(this).dialog("close");
                                            vue.$root.$emit("execLogOff");
                                        }
                                    },
                                    {
                                        text: "いいえ",
                                        class: "btn btn-danger",
                                        click: function(){
                                            $(this).dialog("close");
                                        }
                                    },
                                ],
                            });
                        }
                    },
                ]
            );
        },
        onBeforeCreateGridFunc: function(gridOptions, callback) {
            var vue = this;

            vue.UnitList = [
                { Cd: 1, CdNm: "m3"},
                { Cd: 2, CdNm: "kg"},
                { Cd: 3, CdNm: "箱"},
                { Cd: 4, CdNm: "式"},
            ];
            vue.MajorList = [
                { Cd: "01", CdNm: "大1"},
                { Cd: "02", CdNm: "大2"},
                { Cd: "03", CdNm: "大3"},
            ];
            vue.MinorList = [
                { Cd: "0101", CdNm: "大1小1"},
                { Cd: "0102", CdNm: "大1小2"},
                { Cd: "0201", CdNm: "大2小1"},
                { Cd: "0301", CdNm: "大3小1"},
                { Cd: "0302", CdNm: "大3小2"},
                { Cd: "0303", CdNm: "大3小3"},
            ];

            callback();
            return;

            //PqGrid内リストで使用する一覧の取得
            axios.all(
                [
                    //単位リストの取得
                    axios.post("/Utilities/GetUnitList"),
                    //大分類リストの取得
                    axios.post("/Utilities/GetMajorList"),
                    //小分類リストの取得
                    axios.post("/Utilities/GetMinorList")
                ]
            ).then(
                axios.spread((responseUnit, responseMajor, responseMinor) => {
                    var resUnit = responseUnit.data;
                    var resMajor = responseMajor.data;
                    var resMinor = responseMinor.data;

                    if (resUnit.onError && !!resUnit.errors) {
                        //メッセージリストに追加
                        Object.values(resUnit.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resUnit });

                        return;
                    } else if (resUnit.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "単位リスト取得失敗(" + vue.page.ScreenTitle + ":" + resUnit.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "単位リストの取得に失敗しました<br/>" + resUnit.message,
                        });

                        return;
                    } else if (resUnit == "") {
                        //完了ダイアログ
                        $.dialogErr({
                            title: "単位リスト無し",
                            contents: "該当データは存在しません" ,
                        });

                        return;
                    }

                    if (resMajor.onError && !!resMajor.errors) {
                        //メッセージリストに追加
                        Object.values(resMajor.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resMajor });

                        return;
                    } else if (resMajor.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "大分類リスト取得失敗(" + vue.page.ScreenTitle + ":" + resMajor.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "大分類リストの取得に失敗しました<br/>" + resMajor.message,
                        });

                        return;
                    } else if (resMajor == "") {
                        //完了ダイアログ
                        $.dialogErr({
                            title: "大分類リスト無し",
                            contents: "該当データは存在しません" ,
                        });

                        return;
                    }

                    if (resMinor.onError && !!resMinor.errors) {
                        //メッセージリストに追加
                        Object.values(resMinor.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resMinor });

                        return;
                    } else if (resMinor.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "小分類リスト取得失敗(" + vue.page.ScreenTitle + ":" + resMinor.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "小分類リストの取得に失敗しました<br/>" + resMinor.message,
                        });

                        return;
                    } else if (resMinor == "") {
                        //完了ダイアログ
                        $.dialogErr({
                            title: "小分類リスト無し",
                            contents: "該当データは存在しません" ,
                        });

                        return;
                    }

                    //取得した結果を設定
                    vue.UnitList = resUnit;
                    vue.MajorList = resMajor;
                    vue.MinorList = resMinor;

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "マスタ検索失敗(" + vue.page.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "マスタの検索に失敗しました<br/>",
                });
            });
        },
        onRefreshGridFunc: function(grid) {
            var vue = this;
            var canSave = grid.isChanged();

            $("footer").find("#PID0101Grid1_Save").prop("disabled", !canSave);
        },
        onAddRowFunc: function(grid, rowData) {
            var newRow = {
                GroupKey: rowData.GroupKey,
                MajorNo: rowData.MajorNo,
                MinorNo: rowData.MinorNo,
            };
            return newRow;
        },
    }
}
</script>
