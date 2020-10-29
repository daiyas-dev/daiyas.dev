<template>
    <form id="this.$options.name">
    <!--
        ファイルアップロード機能解放準備
        <form id="this.$options.name" class="droppable" data-url="/DAI07010/Upload" data-upload-callback="uploadCallback">
    -->
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配送日</label>
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
            <div class="col-md-2">
                <VueOptions
                    customLabelStyle="width: 60px; text-align: center;"
                    id="Kind"
                    :vmodel=viewModel
                    bind="Kind"
                    :list="[
                        {code: 'week', name: '週表示', label: '週表示'},
                        {code: 'month', name: '月表示', label: '月表示'},
                    ]"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース区分</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="CourseKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    :disabled=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CourseSelect"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    buddy="CourseNm"
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ bushoCd: viewModel.BushoCd }"
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
                    :popupWidth=600
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=300
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                />
                <label class="label-blue text-center">配送員</label>
                <input class="form-control p-0 label-blue text-center" style="width: 120px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-7">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListFromCourse"
                    :params="{ bushoCd: viewModel.BushoCd, courseCd: viewModel.CourseCd }"
                    title="得意先一覧"
                    labelCd="得意先CD"
                    labelCdNm="得意先名"
                    :showColumns='[
                        { title: "カナ", dataIndx: "得意先名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150, },
                        { title: "電話番号", dataIndx: "電話番号１", dataType: "string", width: 115, maxWidth: 115, minWidth: 115, },
                        { title: "住所", dataIndx: "住所１", dataType: "string", width: 200, maxWidth: 200, minWidth: 200,  },
                        { title: "コース", dataIndx: "コースＣＤ", dataType: "string", width: 70, maxWidth: 70, minWidth: 70 },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 175, maxWidth: 175, minWidth: 175,  },
                    ]'
                    :popupWidth=1100
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :dataListReset=true
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=300
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                    :ParamsChangedCheckFunc=CustomerParamsChangedCheckFunc
                    :enablePrevNext=true
                />
                <label class="label-blue text-center">TEL</label>
                <input class="form-control p-0 label-blue text-center" style="width: 120px;" type="text" :value=viewModel.TelNo readonly tabindex="-1">
            </div>
            <div class="col-md-4">
                <label class="label-blue text-center">更新者</label>
                <input class="form-control p-0 label-blue text-center" style="width: 140px;" type="text" :value=viewModel.Changer readonly tabindex="-1">
                <label class="label-blue text-center">更新日時</label>
                <input class="form-control p-0 label-blue text-center" style="width: 250px;" type="text" :value=viewModel.ModificationDate readonly tabindex="-1">
            </div>
        </div>
        <PqGridWrapper
            id="DAI07010Grid1"
            ref="DAI07010Grid1"
            dataUrl="/DAI07010/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onCellSaveFunc=onCellSaveFunc
            :onAfterSearchFunc=onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :onBeforeCellKeyDownFunc=onBeforeCellKeyDownFunc
            :setMoveNextCell=setMoveNextCell
            :freezeRightCols=2
            :autoToolTipDisabled=true
            :setCustomTitle=setGridTitle
        />
    </form>
</template>

<style scoped>
label {
    max-width: 80px;
}
.update-info .label-blue {
    max-width: unset !important;
    margin-right: 4px;
}
</style>
<style>
#Page_DAI07010 .CustomerSelect .select-name {
    color: royalblue;
}
#Page_DAI07010 .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
    font-weight: bold;
    color: black;
    background-color: white !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI07010",
    components: {
    },
    computed: {
        FirstDay: function() {
            var vue = this;
            var date = vue.viewModel.TargetDate ? moment(vue.viewModel.TargetDate, "YYYY年MM月DD日") : moment();
            var first = vue.viewModel.Kind == "week" ? date.day(1) : date.date(1);
            return first.format("YYYYMMDD");
        },
        TargetDays: function() {
            var vue = this;
            var last = vue.viewModel.Kind == "week" ? moment(vue.FirstDay).add(6, "days")
                        : moment(vue.FirstDay).add(1, "months");
            return last.diff(moment(vue.FirstDay), "days");
        },
        searchParams: function() {
            var vue = this;
            return {
                BushoCd: vue.viewModel.BushoCd,
                CourseCd: vue.viewModel.CourseCd,
                CustomerCd: vue.viewModel.CustomerCd,
                TargetDate: moment(vue.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                FirstDay: vue.FirstDay,
                TargetDays: vue.TargetDays,
            };
        },
    },
    watch: {
        "viewModel.Kind": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                var text = newVal == "week" ? "週" : "月";
                vue.footerButtons.find(v => v.id == "DAI07010_PrevRange").value = "前の" + text;
                vue.footerButtons.find(v => v.id == "DAI07010_NextRange").value = "次の" + text;

                //条件変更ハンドラ
                // vue.conditionChanged();
            }
        },
        searchParams: {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                var disabled = !newVal.TargetDate || !newVal.CourseCd || !newVal.CustomerCd;
                vue.footerButtons.find(v => v.id == "DAI07010Grid1_Search").disabled = disabled;
            },
        },
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "個人宅 > 得意先別週間売上入力",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                CustomerInfo: null,
                BushoCd: null,
                BushoNm: null,
                TargetDate: null,
                CourseKbn: null,
                CustomerCd: null,
                CustomerNm: null,
                TantoCd: null,
                TantoNm: null,
                CourseCd: null,
                CourseNm: null,
                Kind: null,
                Changer: null,
                ModificationDate: null,
            },
            uploadData: null,
            DAI07010Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "block", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 30,
                freezeCols: 4,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
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
                        "数量合計",
                        function(rowData){
                            return _(rowData)
                                .pickBy((v, k) => moment(k).isValid())
                                .values()
                                .sumBy(v => v * 1);
                        }
                    ],
                    [
                        "金額合計",
                        function(rowData){
                            return _(rowData)
                                .pickBy((v, k) => moment(k).isValid())
                                .values()
                                .sumBy(v => v * 1 * (rowData["単価"] * 1));
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コード",
                        dataIndx: "商品CD", dataType: "integer",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: false,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200,
                        editable: false,
                    },
                    {
                        title: "区分",
                        dataIndx: "食事区分名", dataType: "string",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: false,
                    },
                    {
                        title: "コピー",
                        dataIndx: "コピー", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                        render: ui => {
                            if (ui.rowData.pq_grandsummary) {
                                //集計行
                                ui.rowData["コピー"] = "合計";
                                return { text: "合計" };
                            } else {
                                //0非表示
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        editor: {
                            type: "textbox",
                            getData: function(ui, grid) {
                                var cell = ui.$cell;
                                grid = grid || this;

                                //PqGridから稀にこのcolumnでないuiを引数にcallされる
                                //恐らくPqGridのバグ(内部で設定しているsetTimeoutと画面のレンダリングが同期取れていないと推測)
                                if (ui.dataIndx != "コピー") {
                                    //uiのdataIndxから正しいgetDataをcallしてやる
                                    var editor = grid.columns[ui.dataIndx].editor;
                                    if (!!editor && !!editor.getData) {
                                        return editor.getData(ui, grid);
                                    }
                                }

                                var newVal = grid.getEditCell().$editor.val();
                                //事前設定
                                ui.rowData[ui.dataIndx] = newVal;

                                return newVal;
                            },
                        },
                    },
                    {
                        title: "数量合計",
                        dataIndx: "数量合計",
                        dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                        format: "#,##0",
                        editable: false,
                        render: ui => {
                            if (ui.rowData[ui.dataIndx] == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "金額合計",
                        dataIndx: "金額合計",
                        dataType: "integer",
                        width: 100, maxWidth: 100, minWidth: 100,
                        format: "#,##0",
                        editable: false,
                        render: ui => {
                            if (ui.rowData[ui.dataIndx] == 0) {
                                return { text: "" };
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "再検索", id: "DAI07010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "前の得意先", id: "DAI07010_PrevCustomer", disabled: true, shortcut: "Alt + ←",
                    onClick: function () {
                        vue.$refs.PopupSelect_Customer.prevList();
                    }
                },
                { visible: "true", value: "次の得意先", id: "DAI07010_NextCustomer", disabled: true, shortcut: "Alt + →",
                    onClick: function () {
                        vue.$refs.PopupSelect_Customer.nextList();
                    }
                },
                { visible: "true", value: "前の週", id: "DAI07010_PrevRange", disabled: false, shortcut: "Alt + ↑",
                    onClick: function () {
                        vue.viewModel.TargetDate =
                            moment(vue.viewModel.TargetDate, "YYYY年MM月DD日")
                                .add(vue.viewModel.Kind == "week" ? -7 : -1, vue.viewModel.Kind == "week" ? "days" : "months")
                                .format("YYYY年MM月DD日");
                    }
                },
                { visible: "true", value: "次の週", id: "DAI07010_NextRange", disabled: false, shortcut: "Alt + ↓",
                    onClick: function () {
                        vue.viewModel.TargetDate =
                            moment(vue.viewModel.TargetDate, "YYYY年MM月DD日")
                                .add(vue.viewModel.Kind == "week" ? 7 : 1, vue.viewModel.Kind == "week" ? "days" : "months")
                                .format("YYYY年MM月DD日");
                    }
                },
                {visible: "false"},
                { visible: "true", value: "コピー実行", id: "DAI07010_ExecCopy", disabled: false, shortcut: "F6",
                    onClick: function () {
                        var grid = vue.DAI07010Grid1;

                        var rowList = grid.getData()
                            .filter(v => v.コピー != "0")
                            .map(v => grid.getRowIndx({rowData: v }))
                            .map(v => vue.copyValues(grid, v.rowIndx, v.rowData["コピー"]));

                        grid.updateRow({ rowList: rowList });
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI07010Grid1_Save", disabled: false, shortcut: "F9, Ctrl + S",
                    onClick: function () {
                        vue.save();
                    },
                }
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.Kind = "week";

            vue.viewModel.TargetDate = moment().format("YYYY年MM月DD日");

            vue.beforeSearchCallback(vue.DAI07010Grid1, () => vue.DAI07010Grid1.refresh());

            //watcher
            vue.$watch(
                "$refs.PopupSelect_Customer.isPrevEnabled",
                (newVal) => {
                    // console.log("isCustomerPrevEnabled watcher: " + newVal);
                    vue.footerButtons.find(v => v.id == "DAI07010_PrevCustomer").disabled = !newVal;
                }
            );
            vue.$watch(
                "$refs.PopupSelect_Customer.isNextEnabled",
                (newVal) => {
                    // console.log("isCustomerNextEnabled watcher: " + newVal);
                    vue.footerButtons.find(v => v.id == "DAI07010_NextCustomer").disabled = !newVal;
                }
            );

            vue.$refs.PopupSelect_Course.focus();
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //vue.getProductList(code);
        },
        onTargetDateChanged: function(code, entities) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseChanged: function(code, entity) {
            var vue = this;

            vue.viewModel.TantoCd = !!entity ? entity.担当者ＣＤ : "";
            vue.viewModel.TantoNm = !!entity ? entity.担当者名 : "";

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;

            if (!!entity) {
                vue.viewModel.CustomerInfo = entity;
                vue.viewModel.TelNo = entity.電話番号１;
            }


            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI07010Grid1;

            if (!vue.conditionTrigger) return;

            if (vue.getLoginInfo().isLogOn
                && vue.viewModel.BushoCd
                && vue.viewModel.TargetDate
                && vue.viewModel.CourseCd
                && vue.viewModel.CustomerCd
            ) {
                grid.searchData(vue.searchParams, false, vue.beforeSearchCallback);
            } else if (vue.viewModel.TargetDate) {
                vue.beforeSearchCallback(grid, () => grid.refresh());
            }
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            // var comp = vue.$refs.PopupSelect_Course;
            // if (input == comp.selectValue && comp.isUnique) return dataList;

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

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
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            // var comp = vue.$refs.PopupSelect_Customer;
            // if (input == comp.selectValue && comp.isUnique) return dataList;

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["得意先名", "得意先名カナ", "得意先名略称", "備考１", "備考２", "備考３"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        ||_.some(keyOR, k => v.得意先ＣＤ.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.得意先ＣＤ + " : " + v.得意先名 + "【" + v.電話番号１ + "】";
                    ret.value = v.得意先ＣＤ;
                    ret.text = v.得意先名;
                    return ret;
                })
                ;

            // console.log("CustomerAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;

            return !!newVal.bushoCd && vue.$refs.VueSelectBusho.ready;
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.bushoCd && !!newVal.courseCd && vue.isUniqueCourse(newVal.courseCd);
            // console.log("CustomerParamsChangedCheckFunc:" + ret);
            return ret;
        },
        isUniqueCourse: function(course) {
            var vue = this;
            return vue.$refs.PopupSelect_Course.dataList.filter(v => v.Cd == course).length == 1;
        },
        beforeSearchCallback: function(grid, callback) {
            var vue = this;

            var params = {
                from: vue.FirstDay,
                to: moment(vue.FirstDay).add(vue.TargetDays, "days").format("YYYY/MM/DD"),
            };

            // if (vue.PrevParamsHoliday && _.isEqual(vue.PrevParamsHoliday, params && !vue.viewModel.BushoCd)) {
            //     callback();
            //     return;
            // }

            // vue.PrevParamsHoliday = _.cloneDeep(params);

            //事前情報取得
            axios.all(
                [
                    //祝日マスタ検索
                    axios.post(
                        "/Utilities/GetHolidayList",
                        {
                            from: vue.FirstDay,
                            to: moment(vue.FirstDay).add(vue.TargetDays, "days").format("YYYY/MM/DD"),
                            BushoCd: vue.viewModel.BushoCd,
                            noCache: true,
                        }
                    ),
                    //祝日配送区分検索
                    axios.post(
                        "/DAI07010/IsHolidayDeriveryEnabled",
                        {
                            CustomerCd: vue.viewModel.CustomerCd,
                            noCache: true,
                        }
                    ),
                 ]
            ).then(
                axios.spread((responseHoliday, responseEnabled) => {
                    var resHoliday = responseHoliday.data;
                    var resEnabled = responseEnabled.data == "0";

                    if (resHoliday.onError && !!resHoliday.errors) {
                        //メッセージリストに追加
                        Object.values(resHoliday.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resHoliday });

                        return;
                    } else if (resHoliday.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "祝日リスト取得失敗(" + vue.page.ScreenTitle + ":" + resHoliday.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "祝日リストの取得に失敗しました<br/>" + resHoliday.message,
                        });

                        return;
                    }

                    //指定週の祝日
                    var holidays = resHoliday.map(v => moment(v.対象日付).format("YYYYMMDD"));

                    //対象日
                    var days = _.range(0, vue.TargetDays)
                        .map(v => moment(vue.FirstDay).add(v, "days"))
                        .map(v => {
                            return {
                                date: v.format("YYYYMMDD"),
                                label: v.format("MM/DD"),
                                weekdays: v.format("ddd") + "曜",
                                isHoliday: !!resEnabled ? false : (holidays.includes(v.format("YYYYMMDD")) || v.format("d") == "0"),
                            };
                        });

                    //colModelの設定
                    grid.options.colModel = grid.options.colModel.filter(c => !c.custom);
                    days.forEach(v => {
                        grid.options.colModel.push(
                            {
                                title: v.label + "<br>" + v.weekdays,
                                dataIndx: v.date,
                                dataType: "integer",
                                width: 75, maxWidth: 75, minWidth: 75,
                                format: "#,##0",
                                editable: !v.isHoliday,
                                cls: v.isHoliday ? "holiday_col" : "",
                                clsHead: v.isHoliday ? "holiday_col" : "",
                                render: ui => {
                                    if (ui.rowData[ui.dataIndx] == 0) {
                                        return { text: "" };
                                    }
                                    return ui;
                                },
                                editor: {
                                    type: "textbox",
                                    init: function(ui) {
                                        if (ui.cellData == 0) {
                                            ui.$editor.val("");
                                        }
                                    },
                                    getData: function(ui, grid) {
                                        var cell = ui.$cell;
                                        grid = grid || this;

                                        //PqGridから稀にこのcolumnでないuiを引数にcallされる
                                        //恐らくPqGridのバグ(内部で設定しているsetTimeoutと画面のレンダリングが同期取れていないと推測)
                                        if (ui.dataIndx != v.date) {
                                            //uiのdataIndxから正しいgetDataをcallしてやる
                                            var editor = grid.columns[ui.dataIndx].editor;
                                            if (!!editor && !!editor.getData) {
                                                return editor.getData(ui, grid);
                                            }
                                        }

                                        var oldVal = ui.rowData[ui.dataIndx];
                                        var newVal = grid.getEditCell().$editor.val();


                                        return oldVal == 0 && !newVal ? 0 : newVal;
                                    },
                                },
                                summary: {
                                    type: "TotalInt",
                                },
                                custom: true,
                            }
                        );
                    });

                    grid.refreshCM();

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                console.log(error);
                vue.$root.$emit("addMessage", "マスタ検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.viewModel.ModificationDate ="";
            vue.viewModel.Changer = "";

            console.log(res[0].UriageDataList);
            if(res[0].UriageDataList.length != 0 && !!res[0].UriageDataList){
                vue.viewModel.ModificationDate = res[0].UriageDataList[0].修正日 ? moment(res[0].UriageDataList[0].修正日).format("YYYY年MM月DD日 HH:mm:ss"):null;
                vue.viewModel.Changer = res[0].UriageDataList[0].更新者
            }

            return vue.editSearchResult(res);
        },
        editSearchResult: function (res) {
            var vue = this;
            var grid = vue.DAI07010Grid1;

            var UriageDataList = _.values(_.groupBy(res[0].UriageDataList, v => v.商品CD))
                .map(r => {
                    var ret = _.reduce(
                        r,
                        (a, v, i) => {
                            a.部署CD = v.部署CD;
                            a.コースCD = v.コースCD;
                            a.得意先CD = v.得意先CD;
                            a.売掛現金区分 = v.売掛現金区分;
                            a.商品CD = v.商品CD;
                            a.商品名 = v.商品名;
                            a.商品区分 = v.商品区分;
                            a.食事区分 = v.食事区分;
                            a.食事区分名 = v.食事区分名;
                            a.単価 = v.単価;

                            var d = moment(v.日付).format("YYYYMMDD");

                            a[d] = (a[d] || 0) + v.現金個数 * 1 + v.掛売個数 * 1;
                            a[d + "_情報"] = (a[d + "_情報"] || []);
                            a[d + "_情報"].push(_.cloneDeep(v));

                            return a;
                        },
                        {}
                    );
                    return ret;
                });

            //コピーを0埋め
            UriageDataList = UriageDataList.map(v => {
                v["コピー"] = 0;
                return v;
            });

            //直前表示内容からの補完
            grid.prevData
                .filter(v => {
                    return v.部署CD == vue.viewModel.BushoCd
                        && v.コースCD == vue.viewModel.CourseCd
                        && v.得意先CD == vue.viewModel.CustomerCd
                        && v.コピー != "0";
                })
                .forEach(v => {
                    UriageDataList.find(r => {
                        return v.部署CD == r.部署CD
                            && v.コースCD == r.コースCD
                            && v.得意先CD == r.得意先CD
                            && v.商品CD  == r.商品CD;
                    }).コピー = v.コピー;
                })

            //請求済チェック
            var CheckSeikyu = res[0].CheckSeikyu * 1;
            var CheckKaikei = res[0].CheckKaikei * 1;

            vue.checkMsg = !!CheckSeikyu ? "請求済" : !!CheckKaikei ? "会計処理済" : "";
            vue.footerButtons.find(v => v.id == "DAI07010Grid1_Save").disabled = !!CheckSeikyu || !!CheckKaikei;
            //vue.footerButtons.find(v => v.id == "DAI07010Grid1_Save").disabled = !!CheckKaikei;

            //2020/10/27 追加
            var SeikyuList = res[0].GetSeikyu ;
            var SeikyuDay = "";
            var lmt = 0;
            var AddMsg = "";

            if(SeikyuList.length > 0){
                if(SeikyuList.length > 5){
                    lmt = 4
                    AddMsg = " 他)"
                } else{
                    lmt = SeikyuList.length - 1
                    AddMsg = ")"
                }
                for (var i = 0; i <= lmt; i++){
                    SeikyuDay += ", " + moment(SeikyuList[i]["請求日付"], "YYYYMMDD").format("M/D");
                }
                SeikyuDay = SeikyuDay.substr(2, SeikyuDay.length -1);
                vue.checkMsg +=  " " + "(" + SeikyuDay + AddMsg ;
            }
            return UriageDataList;
        },
        setSearchResult: function (res, isBlink) {
            var vue = this;
            var grid = vue.DAI07010Grid1;

            var ret = vue.editSearchResult(res);

            if (!!isBlink) {
                grid.blinkDiff(ret, true);
            } else {
                grid.setLocalData(ret)
            }
        },
        setGridTitle: function(title, grid) {
            var vue = this;
            return title + (!!vue.checkMsg ? (" :" + "<span style='color: red;'>" + vue.checkMsg + "</span>") : "");
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.getData().length) {
                if (vue.uploadData && vue.uploadData.length) {

                    var rowList = grid.getData()
                        .filter(v => vue.uploadData.map(u => u.商品CD).includes(v.商品CD))
                        .map(v => {
                            var rowIndx = grid.getRowIndx({rowData: v}).rowIndx;
                            var uploadVals = vue.uploadData.find(u => u.商品CD == v.商品CD);

                            var ret = vue.copyValues(grid, rowIndx, 0);

                            _.keys(ret.newRow).forEach(k => {
                                if (!!uploadVals[k]) {
                                    ret.newRow[k] = uploadVals[k];
                                }
                            });

                            return ret;
                        })

                    grid.updateRow({ rowList: rowList });

                    vue.uploadData = null;
                }

                //set focus
                var selection = grid.prevSelection;
                if (!selection || selection.length == 0) {
                    grid.setSelection({ rowIndx: 0, colIndx: grid.columns["コピー"].leftPos });
                } else {
                    var r = selection[0].r1;
                    var c = selection[0].c1;
                    if (grid.getCell({rowIndx: r, colIndx: c}).length == 1) {
                        grid.setSelection({ rowIndx: r, colIndx: c });
                    } else if (grid.getCell({rowIndx: 0, colIndx: c}).length == 1) {
                        grid.setSelection({ rowIndx: 0, colIndx: c });
                    } else if (grid.getCell({rowIndx: r, colIndx: 0}).length == 1) {
                        grid.setSelection({ rowIndx: 0, colIndx: _.last(grid.options.colModel.filter(c => !c.hidden)).leftPos });
                    } else {
                        grid.setSelection({ rowIndx: 0, colIndx: grid.columns["コピー"].leftPos });
                    }
                }
            }
        },
        copyValues: function(grid, rowIndx, value, isUpdate) {
            var vue = this;

            //対象列
            var valueCols = grid.options.colModel
                .filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid());

            //rowData更新
            var newVals = _.reduce(
                    grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid()),
                    (acc, c) => { acc[c.dataIndx] = value; return acc; },
                    {}
                );

            var target = { rowIndx: rowIndx, newRow: newVals, history: true };
            if (isUpdate) {
                grid.updateRow(target);
            }
            return target;
        },
        onCellSaveFunc: function(grid, ui, event) {
            var vue = this;
            // console.log("onCellSaveFunc");
            // console.log(ui);
            if (ui.dataIndx == "コピー") {
                vue.copyValues(grid, ui.rowIndx, ui.value, true);
            }
        },
        onBeforeCellKeyDownFunc: function(grid, ui, event) {
            var vue = this;

            // console.log("onBeforeCellKeyDownFunc");
            if (event.key == "Delete") {
                // console.log(ui);

                var selections = grid.Selection()._areas;
                var cols = grid.options.colModel.filter(c => !c.hidden && c.editable && (c.dataIndx == "コピー" || moment(c.dataIndx).isValid()));
                var rowList = [];

                _.range(selections[0].r1, selections[0].r2 + 1)
                    .forEach(r => {
                        cols.filter(c => c.leftPos >= selections[0].c1 && c.leftPos <= selections[0].c2)
                            .forEach(c => {
                                var indices = grid.getCellIndices({ $td: grid.getCell({rowIndx: r, colIndx: c.leftPos})});
                                var val = indices.rowData[indices.dataIndx];

                                if (val != "0") {
                                    if (indices.dataIndx == "コピー") {
                                        indices.rowData[indices.dataIndx] = 0;
                                        grid.refreshCell({rowIndx: indices.rowIndx, dataIndx: "コピー"});
                                        rowList.push(vue.copyValues(grid, indices.rowIndx, 0));
                                    } else {
                                        rowList.push({ rowIndx: indices.rowIndx, newRow: { [indices.dataIndx]: "0" }, history: true });
                                    }
                                }
                            })
                    });

                if (rowList.length) {
                    // console.log(rowList);
                    grid.updateRow({ rowList: rowList });
                }

                return false;
            }
            return true;
        },
        setMoveNextCell: function(grid, ui, reverse) {
            // console.log("setMoveNextCell");

            if (ui.dataIndx == "コピー") {
                if (grid.getEditCell().$editor) {
                    grid.saveEditCell();

                    if (event && event.key == "Tab") {
                        grid.setSelection({
                            rowIndx: ui.rowIndx,
                            colIndx: _.first(grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())).leftPos
                        });
                    } else {
                        grid.setSelection({ rowIndx: ui.rowIndx + 1, colIndx: grid.columns["コピー"].leftPos });
                    }
                } else {
                    if (reverse) {
                        grid.setSelection({
                            rowIndx: ui.rowIndx -1,
                            colIndx: _.last(grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())).leftPos
                        });
                    } else {
                        grid.setSelection({
                            rowIndx: ui.rowIndx,
                            colIndx: _.first(grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())).leftPos
                        });
                    }
                }
            } else {
                if (grid.getEditCell().$editor) {
                    grid.saveEditCell();
                }

                var availableCols = grid.options.colModel
                    .filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())
                    .map(c => c.leftPos);

                if (reverse) {
                    if (availableCols.includes(ui.colIndx - 1)) {
                        grid.setSelection({rowIndx: ui.rowIndx, colIndx: ui.colIndx - 1});
                    } else {
                        grid.setSelection({
                            rowIndx: ui.rowIndx,
                            colIndx: grid.columns["コピー"].leftPos,  //_.last(grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())).leftPos
                        });
                    }
                } else {
                    if (availableCols.includes(ui.colIndx + 1)) {
                        grid.setSelection({rowIndx: ui.rowIndx, colIndx: ui.colIndx + 1});
                    } else {
                        grid.setSelection({
                            rowIndx: ui.rowIndx + 1,
                            colIndx: grid.columns["コピー"].leftPos,  //_.first(grid.options.colModel.filter(c => !c.hidden && c.editable && moment(c.dataIndx).isValid())).leftPos
                        });
                    }
                }
            }

            return false;
        },
        uploadCallback: function(res) {
            var vue = this;
            var grid = vue.DAI07010Grid1;

            console.log("uploadCallback", res);

            var prev = grid.options.dataModel.postData;

            vue.uploadData = res.Array;

            if (!!prev
                && prev.BushoCd == res.BushoCd
                && prev.CourseCd == res.CourseCd
                && prev.CustomerCd == res.CustomerCd
            ) {
                grid.refreshDataAndView();
            } else {
                vue.conditionTrigger = false;

                vue.viewModel.BushoCd = res.BushoCd;
                vue.viewModel.CourseCd = res.CourseCd;
                vue.viewModel.CustomerCd = res.CustomerCd;

                vue.conditionTrigger = true;
            }
        },
        save: function() {
            var vue = this;
            var grid = vue.DAI07010Grid1;

            var UpdateList = grid.getChanges().updateList;
            var OldList = grid.getChanges().oldList;

            if (!UpdateList.length) {
                UpdateList = grid.getPlainPData().filter(v => (v.数量合計 || 0) * 1 > 0);
                OldList = grid.getPlainPData().filter(v => (v.数量合計 || 0) * 1 > 0)
                    .map(r => _.pickBy(r, (v, k) => /^\d{8}$/.test(k)));
            }

            var SaveList = _.flatten(
                UpdateList.map((upd, i) => {
                    var old = OldList[i];
                    var list = _.keys(old).map(k => {
                        return {
                            "日付": k,
                            "部署ＣＤ": upd.部署CD,
                            "コースＣＤ": upd.コースCD,
                            "行Ｎｏ": !!upd[k + "_情報"] ? upd[k + "_情報"][0].行Ｎｏ : null,
                            "得意先ＣＤ": upd.得意先CD,
                            "明細行Ｎｏ": !!upd[k + "_情報"] ? upd[k + "_情報"][0].明細行Ｎｏ : null,
                            "受注Ｎｏ": 0,
                            "配送担当者ＣＤ": vue.viewModel.TantoCd,
                            "商品ＣＤ": upd.商品CD,
                            "商品区分": upd.商品区分,
                            "現金個数": upd.売掛現金区分 == 0 ? upd[k] * 1 : 0,
                            "現金金額": upd.売掛現金区分 == 0 ? upd[k] * 1 * upd.単価 * 1 : 0,
                            "現金値引": 0,
                            "現金値引事由ＣＤ": 0,
                            "掛売個数": upd.売掛現金区分 == 1 ? upd[k] * 1 : 0,
                            "掛売金額": upd.売掛現金区分 == 1 ? upd[k] * 1 * upd.単価 * 1 : 0,
                            "掛売値引": 0,
                            "掛売値引事由ＣＤ": 0,
                            "請求日付": null,
                            "予備金額１": 0,
                            "予備金額２": 0,
                            "売掛現金区分": upd.売掛現金区分,
                            "予備ＣＤ２": 0,
                            "主食ＣＤ": 0,
                            "副食ＣＤ": 0,
                            "分配元数量": 0,
                            "食事区分": upd.食事区分,
                            "備考": "",
                            "修正担当者ＣＤ": vue.getLoginInfo().uid,
                            "修正日": !!upd[k + "_情報"] ? upd[k + "_情報"][0].修正日 : null,
                        };
                    });
                    return list;
                })
            );

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI07010/Save",
                    params: {
                        SaveList: SaveList,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: true,
                        title: "売上入力確認",
                        message: "更新します。よろしいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });

                                //grid blink
                                vue.setSearchResult(res.current, true);
                            } else {
                                vue.setSearchResult(res.current, false);
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
