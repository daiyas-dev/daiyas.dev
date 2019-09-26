<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="Busho"
                    :vmodel=viewModel
                    bind="Busho"
                    uri="/Utilities/GetBushoList"
                    codeName="Code"
                    textName="Name"
                    :withCode=true
                    style="width:200px"
                />
            </div>
            <div class="col-md-1">
                <label>配送日付</label>
            </div>
            <div class="col-md-3">
                <DatePickerWrapper
                    id="HaisoDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="HaisoDate"
                    :editable=true
                />
            </div>
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ code: 100 }"
                    :withCode=true
                    :hasNull=false
                    style="width:200px"
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
                    :params="{ kbn: viewModel.CourseKbn }"
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
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
                    :params="{ kbn: viewModel.CourseKbn }"
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=300
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI01020Grid1"
            ref="DAI01020Grid1"
            dataUrl="/DAI01020/Search"
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
#DAI01020Grid1 .pq-group-toggle-none {
    display: none !important;
}
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

import PopupSelect from "@vcs/PopupSelect.vue";
import VueSelect from "@vcs/VueSelect.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01020",
    components: {
        "VueSelect": VueSelect,
        "PopupSelect": PopupSelect,
    },
    computed: {
        courseParam: function() {
            return { kbn: this.CourseKbn };
        },
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "日配持出入力",
            viewModel: {
                Busho: null,
                HaisoDate: null,
                CourseKbn: null,
                CourseStart: null,
                CourseEnd: null,
            },
            DAI01020Grid1: null,
            //UnitList: [],
            MajorList: [],
            BushoList: [],
            CourseList: [],
           //MinorList: [],
            HaisoDate: null,
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
                    //{ title: "識別番号", dataType: "string",  dataIndx: "UID" , editable: false, hidden: true, key: true,},
                    { title: "部署", dataType: "string",  dataIndx: "Busho" , editable: false, hidden: true, },
                    { title: "配送日付", dataType: "string",  dataIndx: "HaisoDate" , editable: false, hidden: true, },
                    { title: "コースCD", dataType: "integer",  dataIndx: "CourseCd" , width: 80, maxWidth: 80, minWidth: 80, editable: true, hidden: false, },
                    {
                        title: "コース名",
                        dataIndx: "CourseName", dataType: "string",
                        width: 100, maxWidth: 150, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "A副",
                        dataIndx: "AHukuCourse", dataType: "integer",
                        width: 50, maxWidth: 100, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "AP",
                        dataIndx: "APCourse", dataType: "integer",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "A特",
                        dataIndx: "ATokuCourse", dataType: "integer",
                        width: 100, maxWidth: 125, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "ラ",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 100, maxWidth: 125, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "ラ大",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "ラ小",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                     {
                        title: "ラ小P",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "幼",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "ﾗｲﾄF",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "S割",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                     {
                        title: "御膳",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                     {
                        title: "ﾍﾙｼｰF",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                     {
                        title: "ﾚﾃﾞｨｰｽ副",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI01010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        var params = $.extend(true, {}, vue.viewModel);

                        //配送日を"YYYYMMDD"形式に編集
                        params.HaisoDate = params.HaisoDate ? params.HaisoDate.replace(/\//g, "") : null;
                        vue.DAI01010Grid1.searchData(params);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: false, shortcut: "F6",
                    onClick: function () {
                    }
                }
            );
        },
        mountedFunc: function(vue) {
        },
        setFooterButtons: function(vue) {
            vue.$root.$emit("setFooterButtons",
                [
                    // { visible: "true", value: "保存", id: "DAI01020Grid1_Save", disabled: true,
                    //     onClick: function () {
                    //         var vm = vue.viewModel;
                    //         var grid = vue.DAI01020Grid1;

                    //         //パラメータの生成
                    //         var params = grid.createSaveParams();

                    //         //PqGridのデータ保存メソッドを呼び出す
                    //         grid.saveData(
                    //             {
                    //                 uri: "/DAI01020/Save",
                    //                 params: params,
                    //                 done: {
                    //                     callback: (gridVue, grid, res) => {
                    //                     },
                    //                 },
                    //             }
                    //         );
                    //     }
                    // },
                    { visible: "true", value: "検索", id: "DAI01020Grid1_Search", disabled: true,
                        onClick: function () {
                            var params = $.extend(true, {}, DAI01020.vue.viewModel);

                            //配送日を"YYYYMMDD"形式に編集
                            params.HaisoDate = params.HaisoDate ? params.HaisoDate.replace(/\//g, "") : null;
                            DAI01020.DAI01020Grid1.searchData(params);
                        }
                    },
                    { visible: "true", value: "印刷", id: "DAI01020Grid1_Printout", disabled: true,
                        onClick: function () {
                        }
                    },
                    { visible: "true", value: "クリア", id: "DAI01020Grid1_Clear", disabled: false,
                        onClick: function () {
                        }
                    },
                    { visible: "true", value: "登録", id: "DAI01020Grid1_Save", disabled: false,
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
            // vue.UnitList = [
            //     { Cd: 1, CdNm: "m3"},
            //     { Cd: 2, CdNm: "kg"},
            //     { Cd: 3, CdNm: "箱"},
            //     { Cd: 4, CdNm: "式"},
            // ];
            vue.MajorList = [
                { Cd: "01", CdNm: "大分類01"},
                { Cd: "02", CdNm: "大分類02"},
                { Cd: "03", CdNm: "大分類03"},
            ];
            vue.BushoList = [
                { Cd: "01", CdNm: "部署01"},
                { Cd: "02", CdNm: "部署02"},
                { Cd: "03", CdNm: "部署03"},
            ];
            vue.CourseList = [
                { Cd: "01", CdNm: "平日01コース阿知須"},
                { Cd: "02", CdNm: "平日02コース"},
                { Cd: "03", CdNm: "平日03コース佐山"},
            ];

            callback();
            return;

            //PqGrid内リストで使用する一覧の取得
            axios.all(
                [
                    // //単位リストの取得
                    // axios.post("/Utilities/GetUnitList"),
                    //大分類リストの取得
                    axios.post("/Utilities/GetMajorList"),
                    //部署リストの取得
                    axios.post("/Utilities/GetBushoList"),
                    //コースリストの取得
                    axios.post("/Utilities/GetCourseList"),
                //     //小分類リストの取得
                //     axios.post("/Utilities/GetMinorList")
                 ]
            ).then(
                axios.spread((responseUnit, responseMajor, responseMinor) => {
                    //var resUnit = responseUnit.data;
                    var resMajor = responseMajor.data;
                    var resBusho = responseBusho.data;
                    var resCourse = responseCourse.data;
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
                    //vue.UnitList = resUnit;
                    vue.MajorList = resMajor;
                    vue.BushoList = resBusho;
                    vue.CourseList = resCourse;
                    //vue.MinorList = resMinor;

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

            $("footer").find("#DAI01010Grid1_Save").prop("disabled", !canSave);
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
