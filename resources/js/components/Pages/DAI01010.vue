<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-3">
                <VueSelect
                    id="Busyo"
                    :vmodel=viewModel
                    bind="Busyo"
                    uri="/Utilities/GetBusyoList"
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
            id="DAI01010Grid1"
            ref="DAI01010Grid1"
            dataUrl="/DAI01010/Search"
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
#DAI01010Grid1 .pq-group-toggle-none {
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
    name: "DAI01010",
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
            ScreenTitle: "検索条件：持出数一覧表",
            viewModel: {
                Busyo: null,
                HaisoDate: null,
                CourseKbn: null,
                CourseStart: null,
                CourseEnd: null,
            },
            DAI01010Grid1: null,
            //UnitList: [],
            MajorList: [],
            BusyoList: [],
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
                    { title: "識別番号", dataType: "string",  dataIndx: "UID" , editable: false, hidden: true, key: true,},
                    { title: "部署", dataType: "string",  dataIndx: "Busyo" , editable: false, hidden: true, },
                    { title: "配送日付", dataType: "string",  dataIndx: "HaisoDate" , editable: false, hidden: true, },
                    { title: "コースCD", dataType: "integer",  dataIndx: "CourseCd" , width: 50, maxWidth: 80, minWidth: 50, editable: true, hidden: true, },
                    {
                        title: "コース名",
                        dataIndx: "CourseName", dataType: "string", key: true,
                        width: 100, maxWidth: 250, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "A副",
                        dataIndx: "AHukuCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "AP",
                        dataIndx: "APCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "A特",
                        dataIndx: "ATokuCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
                        editable: false,
                    },
                    {
                        title: "ラ",
                        dataIndx: "RaCourse", dataType: "integer",
                        width: 50, maxWidth: 80, minWidth: 50,
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
    // props: {
    //     query: Object,
    //     vm: Object,
    // },
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
            vue.BusyoList = [
                { Cd: "01", CdNm: "部署01"},
                { Cd: "02", CdNm: "部署02"},
                { Cd: "03", CdNm: "部署03"},
            ];
            vue.CourseList = [
                { Cd: "01", CdNm: "平日01コース阿知須"},
                { Cd: "02", CdNm: "平日02コース"},
                { Cd: "03", CdNm: "平日03コース佐山"},
            ];
            // vue.MinorList = [
            //     { Cd: "0101", CdNm: "大1小1"},
            //     { Cd: "0102", CdNm: "大1小2"},
            //     { Cd: "0201", CdNm: "大2小1"},
            //     { Cd: "0301", CdNm: "大3小1"},
            //     { Cd: "0302", CdNm: "大3小2"},
            //     { Cd: "0303", CdNm: "大3小3"},
            // ];

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
                    axios.post("/Utilities/GetBusyoList"),
                    //コースリストの取得
                    axios.post("/Utilities/GetCourseList"),
                //     //小分類リストの取得
                //     axios.post("/Utilities/GetMinorList")
                 ]
            ).then(
                axios.spread((responseUnit, responseMajor, responseMinor) => {
                    //var resUnit = responseUnit.data;
                    var resMajor = responseMajor.data;
                    var resBusyo = responseBusyo.data;
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
                    vue.BusyoList = resBusyo;
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
