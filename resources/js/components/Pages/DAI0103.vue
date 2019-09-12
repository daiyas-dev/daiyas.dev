<template>
    <form id="this.$options.name">
        <div class="row m-1">
            <div class="col-md-12">
                <div class="row m-1">
                    <div class="col-md-1">
                        <label for="Busyo">部署</label>
                    </div>
                    <div class="col-md-11 form-inline">
                        <VueSelect
                            id="Busyo"
                            :vmodel=viewModel
                            bind="Busyo"
                            dataUrl="/Utilities/GetBusyoList"
                            :hasNull=true
                            style="min-width: 200px;"
                        />
                        <button
                            id="btn_group_end"
                            type="button"
                            class="btn btn-primary"
                        ><span>グループ終了</span></button>
                        <button
                            id="btn_group_search"
                            type="button"
                            class="btn btn-primary"
                        ><span>グループ検索</span></button>
                    </div>
                </div>
                <div class="row m-1">
                    <div class="col-md-1">
                        <label for="HaisoDate">配送日</label>
                    </div>
                    <div class="col-md-11 form-inline">
                        <DatePickerWrapper
                            id="HaisoDate"
                            ref="DatePicker_Date"
                            lbel="配送日"
                            :vmodel=viewModel
                            bind="HaisoDate"
                            :config=HaisoDateConfig
                            :editable=true
                        />
                        <label for="HaisoTime">時間</label>
                        <input type="time" id="HaisoTime" disabled="true" class="form-control"/>
                    </div>
                </div>
                <div class="row m-1">
                    <div class="col-md-1">
                        <label>得意先</label>
                    </div>
                    <div class="col-md-11 form-inline">
                        <input type="text" class="form-control" id="CustomerCd" style="width:70px" />
                        <input type="text" class="form-control" id="CustomerName" style="width:300px" disabled="true"/>
                        <button
                            id="btn_search"
                            type="button"
                            class="btn btn-primary"
                        ><span>検索</span></button>
                        <label for="Tel">TEL：</label>
                        <input type="text" class="form-control" id="Tel" style="width:100px" disabled="true"/>
                    </div>
                </div>
                <div class="row m-1">
                    <div class="col-md-1">
                        <label>担当者</label>
                    </div>
                    <div class="col-md-11 form-inline">
                        <input type="text" class="form-control" id="PersonnelCd" style="width:70px" disabled="true"/>
                        <input type="text" class="form-control" id="PersonnelName" style="width:300px" disabled="true"/>
                    </div>
                </div>
                <div class="row m-1">
                    <div class="col-md-1">
                        <label>コース</label>
                    </div>
                    <div class="col-md-11 form-inline">
                        <input type="text" class="form-control" id="CourseCd" style="width:70px" disabled="true"/>
                        <input type="text" class="form-control" id="CourseName" style="width:200px" disabled="true"/>
                    </div>
                </div>
                <div class="row m-1 btn_medium_height">
                    <div class="col-md-1"/>
                    <div class="col-md-1 m-1">
                        <label style="background: #B5ACE0; padding: 3px; text-align:center;">F8 : 検索</label>
                    </div>
                    <div class="col-md-1"/>
                    <div class="col-md-1">
                        <button id="btn_save" type="button" class="btn-normal btn_middle" style="width: 95%; height: 100%;"><span>登録</span></button>
                    </div>
                    <div class="col-md-1">
                        <button id="btn_undelivered" type="button" class="btn_middle" style="width: 95%; height: 100%;" disabled="true"><span>未配達</span></button>
                    </div>
                    <div class="col-md-1">
                        <button id="btn_back" type="button" class="btn-light btn_middle" style="width: 95%; height: 100%;"><span>戻る</span></button>
                    </div>
                    <div class="col-md-1">
                        <button id="btn_back" type="button" class="btn_middle" style="width: 95%; height: 100%;"><span>先へ</span></button>
                    </div>
                    <div class="col-md-1"/>
                    <div class="col-md-1">
                        <button id="btn_all" type="button" class="btn_middle" style="width: 95%; height: 100%;"><span>全表示</span></button>
                    </div>
                </div>
                <!-- TODO:ボタン作成途中aaaa -->
            </div>
        </div>
        <PqGridWrapper
            id="DAI0103Grid1"
            ref="DAI0103Grid1"
            dataUrl="/DAI0103/Search"
            classes="mt-2 ml-3 mr-3"
            :query=this.viewModel
            :SearchOnCreate=true
            :SearchOnActivate=true
            :onRefreshFunc=this.onRefreshGridFunc
            :options=this.grid1Options
            :autoEmptyRow=false
            :autoEmptyRowCount=1
            autoEmptyRowFormula="3n"
        />
        <div class="row m-1">
            <div class="col form-inline">
                <label style="width: 562px; text-align: right;">合計</label>
                <input type="text" class="form-control" disabled="true" style="width: 60px; padding: 0px;"/>
                <input type="text" class="form-control" disabled="true" style="width: 100px; padding: 0px;"/>
                <input type="text" class="form-control" disabled="true" style="width: 60px; padding: 0px;"/>
                <input type="text" class="form-control" disabled="true" style="width: 100px; padding: 0px;"/>
            </div>
        </div>
        <div class="row m-1">
            <div class="col-md-1">
                <label for="Bikou">備考</label>
            </div>
            <div class="col-md-11">
                    <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
                    <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
                     <VueSelect
                        title.width="200px"
                        id="Bikou"
                        :vmodel=viewModel
                        bind="Bikou"
                        dataUrl="/Utilities/GetBikouList"
                        :hasNull=true
                        class="bikou"
                        style="width: 100%"
                    />
      </div>
        </div>
    </form>
</template>

<style>
.form-group{
    margin-bottom: 0px !important;
}
label{
    margin-bottom: 0px !important;
}
.form-control{
    max-height: 30px;
}
.btn{
    height: 28px;
    padding-top: 2px !important;
    padding-bottom: 2px !important;
}
.btn_medium_height > div{
    padding: 0px;
}
.btn_middle:disabled,
.butbtn_middleton[disabled]{
  border: 1px solid #999999;
  background-color: #cccccc;
  color: #666666;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

import PopupSelect from "@vcs/PopupSelect.vue";
import AppHeader from "@vcs/AppHeader.vue";
import AppFooter from "@vcs/AppFooter.vue";
import VueDataList from "@vcs/DataList.vue";
import VueSelect from "@vcs/VueSelect.vue";
import PqGridWrapper from "@vcs/PqGridWrapper.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI0103",
    components: {
        "PopupSelect": PopupSelect,
        "AppHeader": AppHeader,
        "AppFooter": AppFooter,
        "VueDataList": VueDataList,
        "VueSelect": VueSelect,
        "PqGridWrapper": PqGridWrapper,
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "注文入力",
            DAI0103Grid1: null,
            MajorList: [],
            HaisoDate: null,
            HaisoDateConfig: {
                // DatePickerWrapperの基本設定はYYYY/MM/DD
                // format: "YYYY/MM",
                // dayViewHeaderFormat: "YYYY",
            },
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: {rowHtHead: true, rowHt: true, filter: false},
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 25,
                width: 950,
                editable: true,
                columnTemplate: {
                    editable: true,
                    sortable: true,
                },
                formulas: [
                    [
                        "user_id",
                        (rowData) => !!rowData.name ? rowData.user_id : undefined,
                    ],
                ],
                colModel: [
                    { title: "集計キー", dataType: "string",  dataIndx: "GroupKey" , editable: false, hidden: true, },
                    //{ title: "識別番号", dataType: "string",  dataIndx: "UID" , editable: false, hidden: true, key: true,},
                    { title: "部署", dataType: "string",  dataIndx: "Busyo" , editable: false, hidden: true, },
                    { title: "配送日付", dataType: "string",  dataIndx: "HaisoDate" , editable: false, hidden: true, },
                    { title: "得意先CD", dataType: "string",  dataIndx: "CustomerCd" , editable: false, hidden: true, },
                    { title: "担当者CD", dataType: "string",  dataIndx: "PersonnelCd" , editable: false, hidden: true, },
                    { title: "コースCD", dataType: "integer",  dataIndx: "CourseCd" , editable: false, hidden: true, },
                    {
                        title: "コード",
                        dataIndx: "ProductCode", dataType: "string",
                        width: 50, maxWidth: 80, minWidth: 50,
                    },
                    {
                        title: "商品名",
                        dataIndx: "ProductName", dataType: "string",
                        width: 50, maxWidth: 300, minWidth: 50,
                    },
                    {
                        title: "単価",
                        dataIndx: "Price", dataType: "integer",
                        width: 50, maxWidth: 100, minWidth: 50,
                    },
                    {
                        title: "予定数",
                        dataIndx: "AppoNumber", dataType: "integer",
                        width: 80, maxWidth: 80, minWidth: 80,
                    },
                    {
                        title: '現金',
                        colModel: [
                            { title: "個数", dataIndx: 'CashNumber', dataType: 'string', maxWidth: 60 },
                            { title: "金額", dataIndx: 'CashAmount', dataType: 'string', maxWidth: 100 },
                        ],
                    },
                    {
                        title: '掛売',
                        colModel: [
                            { title: "個数", dataIndx: 'SaleNumber', dataType: 'string', maxWidth: 60 },
                            { title: "金額", dataIndx: 'SaleAmount', dataType: 'string', maxWidth: 100 },
                        ],
                    },
                    {
                        title: "確認",
                        dataIndx: "Check", type: "checkbox",  dataType: 'integer',
                        width: 50, maxWidth: 50, minWidth: 50,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
        },
        setFooterButtons: function(vue) {
            vue.$root.$emit("setFooterButtons",
                [
                    // { visible: "true", value: "保存", id: "DAI0103Grid1_Save", disabled: true,
                    //     onClick: function () {
                    //         var vm = vue.viewModel;
                    //         var grid = vue.DAI0103Grid1;

                    //         //パラメータの生成
                    //         var params = grid.createSaveParams();

                    //         //PqGridのデータ保存メソッドを呼び出す
                    //         grid.saveData(
                    //             {
                    //                 uri: "/DAI0103/Save",
                    //                 params: params,
                    //                 done: {
                    //                     callback: (gridVue, grid, res) => {
                    //                     },
                    //                 },
                    //             }
                    //         );
                    //     }
                    // },

                    //TODO:フッターボタン、コピーしたまま作成途中
                    { visible: "true", value: "検索", id: "DAI0103Grid1_Search", disabled: true,
                        onClick: function () {
                            var params = $.extend(true, {}, DAI0103.vue.viewModel);

                            //配送日を"YYYYMMDD"形式に編集
                            params.HaisoDate = params.HaisoDate ? params.HaisoDate.replace(/\//g, "") : null;
                            DAI0103.DAI0103Grid1.searchData(params);
                        }
                    },
                    { visible: "true", value: "印刷", id: "DAI0103Grid1_Printout", disabled: true,
                        onClick: function () {
                        }
                    },
                    { visible: "true", value: "クリア", id: "DAI0103Grid1_Clear", disabled: false,
                        onClick: function () {
                        }
                    },
                    { visible: "true", value: "登録", id: "DAI0103Grid1_Save", disabled: false,
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

            //TODO: dummy
            vue.MajorList = [
                { Cd: "01", CdNm: "大分類01"},
                { Cd: "02", CdNm: "大分類02"},
                { Cd: "03", CdNm: "大分類03"},
            ];
            callback();
            return;

            //PqGrid内リストで使用する一覧の取得
            axios.all(
                [
                    //大分類リストの取得
                    axios.post("/Utilities/GetMajorList"),
                 ]
            ).then(
                axios.spread((responseUnit, responseMajor, responseMinor) => {
                    //var resUnit = responseUnit.data;
                    var resMajor = responseMajor.data;
                    //var resMinor = responseMinor.data;

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
                    vue.MajorList = resMajor;

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

            $("footer").find("#DAI0101Grid1_Save").prop("disabled", !canSave);
        },
    }
}
</script>
