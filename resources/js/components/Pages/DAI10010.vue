<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
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
                <label>日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                    :onChangedFunc=onTargetDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="Course"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    dataUrl="/Utilities/GetCourseListByCustomer"
                    :params='{ BushoCd: viewModel.BushoCd, CourseKbn: viewModel.CourseKbn, CustomerCd: viewModel.CustomerCd }'
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名" }'
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
                    :nameWidth=400
                    :onAfterSearchFunc=CourseAfterSearchFunc
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :disabled=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-10">
                <PopupSelectKeyDown
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ CustomerCd: null, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
                    bind="CustomerCd"
                    :buddies='{CustomerNm: "CdNm"}'
                    :isPreload=true
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=100
                    :nameWidth=400
                    :isShowAutoComplete=false
                    :onKeyDownEnterFunc=onCustomerChanged
                    :isRealTimeSearch=false
                    :onAfterChangedFunc=onCustomerSelectChanged
                />
                <div style="margin-left:10px;">
                得意先コードを入力後<br/>Enterキーを押して下さい。
                </div>
            </div>
        </div>

        <div style="margin-left:10px; color:red;" id="Syorizumi"></div>

        <PqGridWrapper
            id="DAI10010Grid1"
            ref="DAI10010Grid1"
            dataUrl="/DAI10010/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :options=this.grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onCompleteFunc=onCompleteFunc
            :onAfterSearchFunc=this.onAfterSearchFunc
            :checkChangedFunc=changeChangedFunc
            :autoToolTipDisabled=true
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
            :setCustomTitle=setCustomGridTitle
            :onEnterMove=true
            classes="mt-2 mb-1 mr-0 ml-0"
        />
    </form>
</template>

<style scoped>
.row {
    margin-left: 0px;
    margin-right: 0px;
}
.badge {
    font-size: 15px;
}
</style>
<style>
form[pgid="DAI10010"] .pq-grid .DAI10010_toolbar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
form[pgid="DAI10010"] .pq-grid .DAI10010_toolbar .toolbar_button {
    width: 45px;
    height: 30px;
    padding: 0px;
    padding-left: 3px;
    padding-right: 3px;
    margin: 0px;
    display: flex;
    justify-content: center;
    align-items: center;
}
form[pgid="DAI10010"] .pq-grid .DAI10010_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI10010"] .pq-grid .DAI10010_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI10010"] .pq-grid .DAI10010_toolbar .toolbar_button.ope {
    width: 45px;
}
/* 選択セル/行 */
#DAI10010Grid1.pq-grid .pq-state-select.pq-grid-row{
    background: transparent;
}
#DAI10010Grid1.pq-grid .pq-state-select.pq-grid-row .pq-grid-cell{
    background: transparent;
    color: black;
}

</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";
import PopupSelectKeyDown from "@vcs/PopupSelectKeyDown.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI10010",
    components: {
        "PopupSelectKeyDown": PopupSelectKeyDown,
    },
    computed: {
        searchParams: function() {
            return {
                BushoCd: this.viewModel.BushoCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").isValid() ? moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD") : null,
                CourseCd: this.viewModel.CourseCd || 0,
                CustomerCd: this.viewModel.CustomerCd,
            };
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "売上明細入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                CustomerCd: null,
                CustomerNm: null,
                PaymentCd: null,
                PaymentNm: null,
                EatCd: null,
            },
            isLocked: null,
            LockKbn: null,
            DAI10010Grid1: null,
            ProductList: [],
            DiscountList: [
                { Cd: "0", CdNm: "" },
                { Cd: "1", CdNm: "チケット値引" },
                { Cd: "2", CdNm: "契約値引" },
                { Cd: "3", CdNm: "おかずのみ値引" },
                { Cd: "4", CdNm: "クレーム値引" },
                { Cd: "5", CdNm: "その他" },
            ],
            PaymentKbnList: [],
            EatKbnList: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI10010_toolbar",
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
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                    [
                        "金額",
                        function(rowData){
                            var ret = 0;
                            if (rowData["売上売掛現金区分"]==3)
                            {
                                //バークレーの時の処理
                                ret = rowData["売掛現金区分"] == 0 ? rowData["現金金額"] :rowData["掛売金額"];
                            }
                            else
                            {
                                //バークレー以外の時の処理
                                if (!!rowData["個数"]){
                                    var ret = rowData["単価"] * rowData["個数"];
                                } else {
                                    ret = rowData["売掛現金区分"] == 0 ? rowData["現金金額"] :rowData["掛売金額"];
                                }
                            }
                            return ret;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ", dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                        key: true,
                        editable: true,
                        autocomplete: {
                            trigger: () => !!vue.viewModel.CustomerCd && vue.getProductList().length,
                            source: () => vue.getProductList(),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm", "商品区分": "商品区分", },
                            onSelect: (rowData, selected) => {
                                rowData["単価"] = selected["売価単価"];
                                rowData["金額"] = rowData["単価"] * rowData["個数"];
                                rowData["主食ＣＤ"] = selected["主食ＣＤ"];
                                rowData["副食ＣＤ"] = selected["副食ＣＤ"];

                                if(selected["Cd"]==800)
                                {
                                    rowData["売上売掛現金区分"] = 0;
                                    console.log("800");
                                }
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200,
                        editable: false,
                    },
                    {
                        title: "商品区分",
                        dataIndx: "商品区分", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "主食ＣＤ",
                        dataIndx: "主食ＣＤ", dataType: "integer",
                        editable: true,
                        hidden: true,
                    },
                    {
                        title: "副食ＣＤ",
                        dataIndx: "副食ＣＤ", dataType: "integer",
                        editable: true,
                        hidden: true,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                    },
                    {
                        title: "個数",
                        dataIndx: "個数",
                        dataType: "integer",
                        format: "#,###",
                        width: 40, maxWidth: 40, minWidth: 40,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額",
                        dataIndx: "金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "値引",
                        dataIndx: "値引",
                        dataType: "integer",
                        format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData[ui.dataIndx]) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "備考",
                        dataIndx: "備考",
                        dataType: "string",
                        align: "left",
                        width: 150, maxWidth: 150, minWidth: 150,
                        editable: true,
                    },
                    // {
                    //     title: "値引事由",
                    //     dataIndx: "値引事由",
                    //     dataType: "integer",
                    //     align: "left",
                    //     width: 150, maxWidth: 150, minWidth: 150,
                    //     editable: true,
                    //     selectList: "DiscountList",
                    //     selectLabel: "Cd == 0 ? '' : (Cd + ')' + CdNm)",
                    //     selectNullFirst: false,
                    // },
                    {
                        title: "売掛現金区分",
                        dataIndx: "売上売掛現金区分",
                        dataType: "integer",
                        align: "left",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                        selectList: "PaymentKbnList",
                        selectLabel: "Cd + ')' + CdNm ",
                        selectNullFirst: false,
                    },
                    {
                        title: "食事区分",
                        dataIndx: "食事区分",
                        dataType: "integer",
                        align: "left",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                        selectList: "EatKbnList",
                        selectLabel: "Cd == 0 ? '' : (Cd + ')' + CdNm)",
                        selectNullFirst: false,
                    },
                    {
                        title: "コースＣＤ",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, data.viewModel, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "行削除", id: "DAI10010Grid1_DeleteRow", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "主副食区分", id: "DAI10010Grid1_MainSubKbn", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.showMainSubKbn();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI10010Grid1_Search", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI10010_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        var grid = vue.DAI10010Grid1;

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

                        var changes = grid.createSaveParams();
                        changes.AddList = changes.AddList.filter(r => _.values(diff(grid.vue.autoEmptyRowFunc(), r)).some(v => !!v));
                        var SaveList = _.concat(changes.AddList, changes.UpdateList);
                        var DeleteList = grid.getChanges().deleteList;

                        var check_result = [];
                        check_result = SaveList.filter(v => v.商品ＣＤ == null);
                        if(!!check_result[0])
                        {
                            $.dialogInfo({
                                title: "登録チェック",
                                contents: "商品コードを入力して下さい。",
                            });
                            return false;
                        }

                        //売上データ明細の型に整形
                        SaveList.forEach((v, i) => {
                            v.日付 = moment(vue.searchParams.TargetDate).format("YYYY-MM-DD");
                            v.部署ＣＤ = vue.searchParams.BushoCd;
                            v.コースＣＤ = v.コースＣＤ || vue.searchParams.CourseCd;
                            v.行Ｎｏ = v.行Ｎｏ || (!!vue.params ? (vue.params.CustomerIndex || 0): 0);
                            v.得意先ＣＤ = vue.searchParams.CustomerCd;
                            v.受注Ｎｏ = _.max(grid.pdata.map(v => v.受注Ｎｏ)) || 0;
                            v.配送担当者ＣＤ = vue.viewModel.TantoCd || 999;
                            v.現金個数 = (v.売上売掛現金区分 == 0 ? v.個数 : 0) || 0;
                            v.現金金額 = (v.売上売掛現金区分 == 0 ? v.金額 : 0) || 0;
                            v.現金値引 = (v.売上売掛現金区分 == 0 ? v.値引 : 0) || 0;
                            v.現金値引事由ＣＤ = (v.売上売掛現金区分 == 0 ? v.値引事由 : 0) || 0;
                            v.掛売個数 = (v.売上売掛現金区分 != 0 ? v.個数 : 0) || 0;
                            v.掛売金額 = (v.売上売掛現金区分 != 0 ? v.金額 : 0) || 0;
                            v.掛売値引 = (v.売上売掛現金区分 != 0 ? v.値引 : 0) || 0;
                            v.掛売値引事由ＣＤ = (v.売上売掛現金区分 != 0 ? v.値引事由 : 0) || 0;
                            v.請求日付 = v.請求日付 = v.売上売掛現金区分 != 0 ? (!!v.請求日付 ? moment(v.請求日付).format("YYYYMMDD") : "") : "";
                            v.予備金額１ = v.単価 || 0;
                            v.予備金額２ = 0;
                            v.売掛現金区分 = v.売上売掛現金区分;
                            v.予備ＣＤ２ = 0;
                            v.分配元数量 = v.分配元数量 || 0;
                            v.修正担当者ＣＤ = vue.getLoginInfo().uid;

                            delete v.コース名;
                            delete v.ＳＥＱ;
                            delete v.得意先名;
                            delete v.売掛現金区分名称;
                            delete v.得意先売掛現金区分;
                            delete v.得意先売掛現金区分名称;
                            delete v.売上売掛現金区分;
                            delete v.売上売掛現金区分名称;
                            delete v.商品名;
                            delete v.単価;
                            delete v.個数;
                            delete v.値引;
                            delete v.値引事由;
                            delete v.金額;
                            delete v.今回請求額;
                        });

                        DeleteList = DeleteList.map(v => {
                            var ret = {};
                            ret.日付 = v.日付;
                            ret.部署ＣＤ = vue.searchParams.BushoCd;
                            ret.コースＣＤ = v.コースＣＤ || vue.searchParams.CourseCd;
                            ret.行Ｎｏ = v.行Ｎｏ;
                            ret.得意先ＣＤ = vue.searchParams.CustomerCd;
                            ret.明細行Ｎｏ = v.明細行Ｎｏ;
                            ret.受注Ｎｏ = v.受注Ｎｏ;

                            return ret;
                        });

                        //保存実行
                        grid.saveData(
                            {
                                uri: "/DAI10010/Save",
                                params: {
                                    SaveList: SaveList,
                                    DeleteList: DeleteList,
                                },
                                optional: vue.searchParams,
                                confirm: {
                                    isShow: true,
                                    title: "確認 " + vue.viewModel.CustomerNm,
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
                                            if (!!vue.params) {
                                                grid.commit();
                                                if (!!vue.params && !!vue.params.ParentGrid) {
                                                    vue.params.ParentGrid.refreshDataAndView();
                                                }

                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            } else {
                                                grid.clearData();
                                                vue.viewModel.CustomerCd = null;
                                                vue.viewModel.CustomerCd = null;
                                                vue.viewModel.CourseCd = null;
                                                vue.viewModel.CourseNm = null;
                                                vue.viewModel.TantoCd = null;
                                                vue.viewModel.PaymentCd = null;
                                                vue.viewModel.PaymentNm = null;
                                            }
                                        }

                                        return false;
                                    },
                                },
                            }
                        );
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            //日付の初期値 -> 当日
            if (!vue.viewModel.TargetDate) {
                vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");
            }

            //watcher
            vue.$watch(
                "$refs.DAI10010Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI10010Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI10010Grid1_MainSubKbn").disabled = cnt == 0 || cnt > 1;
                }
            );

            if (!!vue.params || !!vue.query) {
                //PqGrid読込待ち
                var grid = vue.DAI10010Grid1;
                new Promise((resolve, reject) => {
                    var timer = setInterval(function () {
                        grid = vue.DAI10010Grid1;
                        if (!!grid && vue.getLoginInfo().isLogOn) {
                            clearInterval(timer);
                            return resolve(grid);
                        }
                    }, 500);
                })
                .then(grid => {
                    console.log("10010 early search")
                    vue.prevPostData = _.cloneDeep(vue.searchParams);
                    vue.searchData(vue.searchParams);
                    if('CustomerCd' in vue.searchParams)
                    {
                        vue.LockUpdate(vue.searchParams.CustomerCd);
                    }
                });
            }
        },
        onBushoChanged: function(code, entities) {
            var vue = this;
        },
        onTargetDateChanged: function(code, entity) {
            var vue = this;

            //コース区分変更
            axios.post(
                "/Utilities/GetCourseKbnFromDate",
                {TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD")}
            )
                .then(res => {
                    vue.viewModel.CourseKbn = res.data.コース区分;

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
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.BushoCd && !!newVal.CourseKbn && !!newVal.CustomerCd;
            console.log("CourseParamsChangedCheckFunc", newVal, ret);
            return ret;
        },
        CourseAfterSearchFunc: function(comp) {
            var vue = this;

            if (!!vue.params && !!vue.params.IsBunpai) {
                comp.selectValue = vue.viewModel.CourseCd = vue.params.CourseCd;
                comp.selectName = vue.viewModel.CourseNm = vue.params.CourseNm;
            } else if (!!vue.params && !!vue.params.IsSeikyu) {
                comp.selectValue = vue.viewModel.CourseCd = vue.params.CourseCd;
                comp.selectName = vue.viewModel.CourseNm = vue.params.CourseNm;
            } else if (!!vue.params && !!vue.params.IsSeikyuOutput) {
                comp.selectValue = vue.viewModel.CourseCd = vue.params.CourseCd;
                comp.selectName = vue.viewModel.CourseNm = vue.params.CourseNm;
            } else {
                var match = comp.dataList.filter(v => v.得意先ＣＤ == vue.viewModel.CustomerCd);
                if (match.length == 1) {
                    comp.selectValue = vue.viewModel.CourseCd = match[0].Cd;
                    comp.selectName = vue.viewModel.CourseNm = match[0].CdNm;
                    comp.selectRow = match;
                    vue.viewModel.TantoCd = match[0].担当者ＣＤ
                } else if (match.length > 1) {
                    comp.selectValue = vue.viewModel.CourseCd = "";
                    comp.selectName = vue.viewModel.CourseNm = "複数コース該当";
                } else {
                    comp.selectValue = vue.viewModel.CourseCd = "";
                    comp.selectName = vue.viewModel.CourseNm = "コース無し";
                }
            }

            return false;
        },
        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        CustomerAfterSearchFunc: function(comp) {
            var vue = this;
            if (!vue.params) {
                comp.focus(true);
            }
        },
        //得意先を入力してEnterキーを押下した後の処理
        onCustomerChanged: function(code, entity,comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];
                vue.viewModel.CustomerCd = code;
                vue.viewModel.CustomerNm= entity["CdNm"];
                vue.viewModel.PaymentCd = entity["売掛現金区分"];
                vue.viewModel.PaymentNm = ["現金", "掛売"][vue.viewModel.PaymentCd];
                if (entity["支払方法１"] == 5) {
                    vue.viewModel.PaymentCd = 2;
                    vue.viewModel.PaymentNm = "チケット";
                }

                var params = _.cloneDeep(vue.searchParams);
                params.CustomerCd = code;
                if (!vue.ProductList.length) {
                    //商品リスト検索
                    axios.post("/DAI10010/GetProductList", params)
                        .then(res => {
                            vue.ProductList = res.data;
                            //条件変更ハンドラ
                            vue.conditionChanged();
                        })
                        .catch(err => {
                            console.log("/DAI10010/GetProductList Error", err)
                            $.dialogErr({
                                title: "商品マスタ検索失敗",
                                contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                            });
                        });
                } else {
                    vue.conditionChanged();
                }
                vue.LockUpdate(code);

            }
        },
        //得意先を虫眼鏡で選択したあとの処理
        onCustomerSelectChanged: function(code, entity, comp) {
            var vue = this;
            var grid = vue.DAI10010Grid1;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];
                vue.viewModel.PaymentCd = entity["売掛現金区分"];
                vue.viewModel.PaymentNm = ["現金", "掛売"][vue.viewModel.PaymentCd];
                if (entity["支払方法１"] == 5) {
                    vue.viewModel.PaymentCd = 2;
                    vue.viewModel.PaymentNm = "チケット";
                }

                var params = _.cloneDeep(vue.searchParams);

                if (!!vue.params && !!vue.params.IsBunpai) {
                    params.CustomerCd = vue.params.ParentCustomerCd;
                }

                if (!vue.ProductList.length) {
                    //商品リスト検索
                    axios.post("/DAI10010/GetProductList", params)
                        .then(res => {
                            vue.ProductList = res.data;
                            //条件変更ハンドラ
                            vue.conditionChanged();
                        })
                        .catch(err => {
                            console.log("/DAI10010/GetProductList Error", err)
                            $.dialogErr({
                                title: "商品マスタ検索失敗",
                                contents: "商品マスタの検索に失敗しました" + "<br/>" + err.message,
                            });
                        });
                } else {
                    vue.conditionChanged();
                }
            }
            vue.LockUpdate(code);
        },
        LockUpdate: function(CustomerCd){
            //請求状況検索
            if(!CustomerCd){
                return;
            }
            var vue = this;
            var params = _.cloneDeep(vue.searchParams);
            params.CustomerCd = CustomerCd;
            vue.isLocked = false;
            axios.post("/DAI10010/GetSeikyuData", params)
                .then(res => {
                    console.log("請求状況", res.data);
                    if(!!res.data)
                    {
                        vue.isLocked = res.data.length == 0 ? false : moment(res.data[0].請求日付).isAfter(vue.searchParams.TargetDate);
                    }
                })
                .catch(err => {
                    console.log("/DAI10010/u Error", err)
                    $.dialogErr({
                        title: "請求状況",
                        contents: "請求状況の検索に失敗しました" + "<br/>" + err.message,
                    });
                });
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI10010Grid1;
            console.log("10010 conditionChanged", vue.viewModel);

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd || !vue.viewModel.TargetDate || !vue.viewModel.CustomerCd || vue.ProductList.length == 0) return;
            if (!vue.viewModel.CourseCd && vue.viewModel.CourseNm != "コース無し") return;

            if (!force && (_.isEqual(grid.prevPostData, vue.searchParams))) return;
            if (!force && (_.isEqual(vue.prevPostData, vue.searchParams))) return;
            vue.searchData(vue.searchParams);
        },
        searchData: function(params) {
            var vue = this;
            var grid = vue.DAI10010Grid1;

            var p = _.cloneDeep(params || vue.searchParams);

            if (!p.CustomerCd) return;

            if (!!vue.params && !!vue.params.IsBunpai) {
                p.ParentCustomerCd = vue.params.ParentCustomerCd;
            }

            //日付開始日取得 2020/10/09
            //p.TargetDate = moment(p.TargetDate).format("YYYY/MM/01");

            vue.prevPostData = _.cloneDeep(p);
            p.noCache = true;

            grid.showLoading();
            axios.post("/DAI10010/Search", p)
            .then(res => {
                vue.ProductList = res.data.ProductList;

                var SalesList = res.data.SalesList;

                var UrikakeList = res.data.UrikakeList;

                var data = SalesList.filter(v => !!v.商品ＣＤ);
                data.forEach(v => {
                    v.個数 = (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数 ) * 1 + v.分配元数量 * 1;
                    v.単価 = !!v.予備金額１ ? v.予備金額１ : vue.ProductList.find(p => p.商品ＣＤ == v.商品ＣＤ).売価単価 * 1;
                    v.値引 = (v.売掛現金区分 == 0 ? v.現金値引 : v.掛売値引 ) * 1;
                    v.値引事由 = (v.売掛現金区分 == 0 ? v.現金値引事由ＣＤ : v.掛売値引事由ＣＤ ) * 1;
                });

                grid.setLocalData(data);

                vue.isLocked = UrikakeList.length == 0 ? false : true;
                if (vue.isLocked == true)
                {
                    vue.LockKbn = "【会計処理済】";
                }
                else
                {
                    vue.LockKbn = "";
                    vue.isLocked = SalesList.length == 0 ? false : moment(SalesList[0].請求日付).isAfter(vue.searchParams.TargetDate);
                    if (vue.isLocked == true)
                    {
                        vue.LockKbn = "【請求済】";
                    }
                }

                document.getElementById("Syorizumi").innerText = vue.LockKbn;
                vue.footerButtons.find(v => v.id == "DAI10010_Save").disabled = vue.isLocked;
            })
            .finally(() => {
                grid.hideLoading();
            })
            ;
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];
            if (input == null || input == undefined) return dataList;

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "担当者名"];

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
                    ret.label = v.Cd + " : " + v.CdNm + "【" + v.担当者名 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
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
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getProductList: function() {
            var vue = this;
            return vue.ProductList;
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

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
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //事前情報取得
            axios.all(
                [
                    //売掛現金区分リストの取得
                    axios.post("/Utilities/GetCodeList", { cd: 34 }),
                    //食事区分リストの取得
                    axios.post("/Utilities/GetCodeList", { cd: 38 }),
                ]
            )
            .then(
                axios.spread((responsePayment, responseEat) => {
                    vue.PaymentKbnList = responsePayment.data;
                    vue.EatKbnList = responseEat.data;
                    vue.EatKbnList.unshift({Cd: "0", CdNm: ""});

                    if(callback) callback();
                })
            )
            .catch(err => {
                console.log(err);
                if (!!grid) grid.hideLoading();

                if (!err.message) return;

                //失敗ダイアログ
                $.dialogErr({
                    title: "各種テーブル検索失敗",
                    contents: "各種テーブル検索に失敗しました" + "<br/>" + err.message,
                });
            });
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;
            vue.setGridFocus(grid);
        },
        setGridFocus: function(grid, ui) {
            var vue = this;
            grid = grid || vue.DAI10010Grid1;

            if (grid.pdata.length > 0) {
                var data = grid.pdata[0];
                var colIndx = !data["商品ＣＤ"] ? grid.columns["商品ＣＤ"].leftPos
                    : _(grid.columns).pickBy((v, k) => k.endsWith("個数") && !v.hidden).values().value()[0].leftPos;
                grid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        setCustomGridTitle: function(title) {
            var vue = this;
            return title;
        },
        onAfterSearchFunc: function (grieVue, grid, res) {
            var vue = this;
            vue.ProductList = res.ProductList;

            var data = res.SalesList.filter(v => !!v.商品ＣＤ);
            data.forEach(v => {
                v.個数 = (v.売掛現金区分 == 0 ? v.現金個数 : v.掛売個数 ) * 1;
                v.単価 = !!v.予備金額１ ? v.予備金額１ : vue.ProductList.find(p => p.商品ＣＤ == v.商品ＣＤ).売価単価 * 1;
                v.値引 = (v.売掛現金区分 == 0 ? v.現金値引 : v.掛売値引 ) * 1;
                v.値引事由 = (v.売掛現金区分 == 0 ? v.現金値引事由ＣＤ : v.掛売値引事由ＣＤ ) * 1;
            });

            vue.isLocked = res.length == 0 ? false : moment(res[0].請求日付).isAfter(vue.searchParams.TargetDate);

            vue.footerButtons.find(v => v.id == "DAI10010_Save").disabled = vue.isLocked;

            return data;
        },
        changeChangedFunc: function(grid) {
            var vue = this;

            var changes = !!grid.getChanges().updateList.length;
            return changes;
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "値引事由": 0,
                "売上売掛現金区分": (vue.viewModel.PaymentCd || 0) * 1,
                "食事区分": (vue.viewModel.EatCd || 2) * 1,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品ＣＤ"];
        },
        addRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI10010Grid1;
            var rowIndx = grid.pdata.length;

            grid.addRow({
                rowIndx: rowIndx,
                newRow: {"売掛現金区分": vue.viewModel.PaymentCd || 0},
                checkEditable: false
            });

            grid.scrollRow({rowIndxPage: rowIndx});

        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI10010Grid1;

            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
            grid.deleteRow({ rowList: rowList });
        },
        showMainSubKbn: function() {
            var vue = this;
            var grid = vue.DAI10010Grid1;

            var selection = grid.SelectRow().getSelection();

            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;

            var rowData = rows[0].rowData;
            var rowIndx = rows[0].rowIndx;

            var data = _.cloneDeep(rowData);

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                BushoNm: vue.viewModel.BushoNm,
                ProductCd: data.商品ＣＤ,
                ProductNm: data.商品名,
                MainCd: data.主食ＣＤ,
                SubCd: data.副食ＣＤ,
                setCode: (main, sub) => grid.updateRow({ rowIndx: rowIndx, newRow: { 主食ＣＤ: main, 副食ＣＤ: sub }, history: true }),
            };

            PageDialog.show({
                pgId: "DAI10011",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 500,
                height: 230,
            });
        },
    }
}
</script>
