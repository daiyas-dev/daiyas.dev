<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>得意先ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input type="text" class="form-control"
                    v-model="viewModel.CustomerCd"
                    @input="onCustomerCdChanged"
                    @keydown.enter="() => showDetailByCd()"
                    @keydown.f8="() => showDetailByCd()"
                >
            </div>
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    ref="VueSelectBusho"
                    :hasNull=true
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-2">
                <label>状態</label>
                <VueSelect
                    id="State"
                    :vmodel=viewModel
                    bind="State"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 12 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onStateChanged
                />
            </div>
            <div class="col-md-3">
                <label>承認日</label>
                <DatePickerWrapper
                    id="ShoninDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="ShoninDate"
                    :editable=true
                    :onChangedFunc=onShoninDateChanged
                />
            </div>
        </div>
        <div class="row">
            <!-- <div class="col-md-1">
                <label>承認者</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="ShoninSelect"
                    ref="PopupSelect_Shonin"
                    :vmodel=viewModel
                    bind="ShoninCd"
                    buddy="ShoninNm"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: null }"
                    :isPreload=true
                    title="承認者一覧"
                    labelCd="承認者CD"
                    labelCdNm="承認者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}]"
                    :onAfterChangedFunc=onShoninCdChanged
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ShoninAutoCompleteFunc
                />
            </div> -->
            <div class="col-md-1">
                <label>キーワード</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" :value="viewModel.KeyWord" @input="onKeyWordChanged">
            </div>
            <div class="col-md-3">
                <VueOptions
                    title="検索条件:"
                    customLabelStyle="text-align: center;"
                    id="FilterMode"
                    :vmodel=viewModel
                    bind="FilterMode"
                    :list="[
                        {code: 'AND', name: 'AND', label: '全て含む'},
                        {code: 'OR', name: 'OR', label: 'いずれかを含む'},
                    ]"
                    :onChangedFunc=onFilterModeChanged
                />
            </div>
            <div class="col-md-1">
                <label style="width: unset;">コース内/外</label>
            </div>
            <div class="col-md-3">
                <VueCheckList
                    id="WithCourse"
                    ref="VueCheckList_WithCourse"
                    :vmodel=viewModel
                    bind="WithCourse"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '0', name: 'コース内', label: 'コース内'},
                        {code: '1', name: 'コース外', label: 'コース外'},
                    ]"
                    :onChangedFunc=onWithCourseChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI04040Grid1"
            ref="DAI04040Grid1"
            dataUrl="/Utilities/GetCustomerListForMaintDistinct"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style scoped>
</style>
<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04040",
    components: {
    },
    computed: {
        FormattedShoninDate: function() {
            var vue = this;
            return vue.viewModel.ShoninDate ? moment(vue.viewModel.ShoninDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 得意先マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                State: null,
                ShoninDate: null,
                ShoninCd: null,
                ShoninNm: null,
                KeyWord: null,
                FilterMode: "AND",
                WithCourse: ["0", "1"],
            },
            DAI04040Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 50,
                rowHt: 30,
                freezeCols: 2,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                trackModel: { on: false },
                historyModel: { on: false },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                colModel: [
                    {
                    title: "部署名",
                    dataIndx: "部署名", dataType: "string", key: true,
                    width: 120, minWidth: 120, maxWidth: 120,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "得意先ＣＤ",
                    dataIndx: "得意先CD", dataType: "integer", key: true, align: "left",
                    width: 90, minWidth: 90, maxWidth: 90,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "得意先名",
                    dataIndx: "得意先名", dataType: "string", key: true,
                    width: 200, minWidth: 200,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "得意先名カナ",
                    dataIndx: "得意先名カナ", dataType: "string", key: true,
                    hidden: true,
                    width: 100, minWidth: 100,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "状態区分",
                    dataIndx: "状態区分", dataType: "string", key: true,
                    hidden: true,
                    width: 50, minWidth: 50,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "承認日",
                    dataIndx: "承認日", dataType: "date", key: true,
                    hidden: true,
                    width: 200, minWidth: 150,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "承認者ＣＤ",
                    dataIndx: "承認者ＣＤ", dataType: "integer", key: true,
                    width: 90, minWidth: 90, maxWidth: 90,
                    hidden: true,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "電話番号１",
                    dataIndx: "電話番号１", dataType: "string", key: true,
                    width: 120, minWidth: 120, maxWidth: 120,
                    hidden: true,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "コースＣＤ",
                    dataIndx: "コースＣＤ", dataType: "integer", key: true, align: "left",
                    width: 90, minWidth: 90, maxWidth: 90,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "コース名",
                    dataIndx: "コース名", dataType: "string", key: true,
                    width: 200, minWidth: 200,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "コース区分",
                    dataIndx: "コース区分", dataType: "string", key: true,
                    hidden: true,
                    editable: false,
                    fixed: true,
                    },
                    {
                    title: "KeyWord",
                    dataIndx: "KeyWord", dataType: "string", key: true,
                    hidden: true,
                    editable: false,
                    fixed: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04040_Search", disabled: false, shortcut: "F8",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "履歴表示", id: "DAI04040Grid1_History", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.showHistory();
                    }
                },
                { visible: "true", value: "分配先登録", id: "DAI04040Grid1_Bunpaisaki", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.showBunpaisaki();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04040Grid1_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04040Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "詳細", id: "DAI04040Grid1_Detail", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04040Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                },
                { visible: "true", value: "単価登録", id: "DAI04040Grid1_Tanka", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.showTanka();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //watcher
            vue.$watch(
                "$refs.DAI04040Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04040Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI04040Grid1_History").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI04040Grid1_Bunpaisaki").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI04040Grid1_Tanka").disabled = cnt == 0 || cnt > 1;
                }
            );

            console.log("Cache keys", myCache.keys());
            console.log("Cache Set Key1", myCache.set("key1", { value: 1 }));
            console.log("Cache Get Key1", myCache.get("key1"));
        },
        onBushoChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCustomerCdChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
         },
        onStateChanged: function(code, entity) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onShoninDateChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onShoninCdChanged: function(element, info, comp, isNoMsg, isValid) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI04040Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {BushoCd: vue.viewModel.BushoCd};
                if (!!vue.viewModel.CustomerCd) {
                    params.CustomerCd = vue.viewModel.CustomerCd;
                }
                grid.searchData(params, false);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onWithCourseChanged: function() {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先CD", condition: "equal", value: vue.viewModel.CustomerCd});
            }
            if (!!vue.viewModel.State) {
                rules.push({ dataIndx: "状態区分", condition: "equal", value: vue.viewModel.State });
            }
            if (!!vue.viewModel.ShoninDate) {
                rules.push({ dataIndx: "承認日", condition: "equal", value: moment(vue.viewModel.ShoninDate,"YYYY-MM-DD").format("YYYY-MM-DD HH:mm:ss.SSS") });
            }
            if (!!vue.viewModel.ShoninCd) {
                rules.push({ dataIndx: "承認者ＣＤ", condition: "equal", value: vue.viewModel.ShoninCd });
            }
            if (vue.viewModel.WithCourse.length == 1) {
                rules.push({ dataIndx: "コースＣＤ", condition: vue.viewModel.WithCourse[0] == "0" ? "notempty" : "empty" });
            }
            if (!!vue.viewModel.KeyWord) {
                var keywords = editKeywords((vue.viewModel.KeyWord || "").split(/[, 、]/g));

                var rulesKeyWord = keywords.map(k => { return { condition: "contain", value: k }; });

                rules.push({ dataIndx: "KeyWord", mode: "OR", crules: rulesKeyWord });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            //列定義初期化
            grid.options.colModel = grid.options.colModel.filter(c => c.fixed);

            var vue = this;

            //キーワード追加
            res = res.map(v => {
                // v.KeyWord = _.values(v).join(",");
                v.KeyWord = _.keys(v).filter(k => (k != "修正日" ) && (k != "InitialValue") && (k != /^pq.*/)).map(k => v[k]).join(",");
                //電話番号からハイフンを消してキーワードに追加
                v.KeyWord += (!!v.電話番号１ ? ("," +  v.電話番号１.replace(/-/g,"")) : "");
                //カタカナを全角⇔半角に変換してキーワードに追加
                v.KeyWord += (!!v.得意先名カナ ? ("," +  Moji(v.得意先名カナ).convert('HK', 'ZK').toString()) : "");
                v.KeyWord += (!!v.得意先名 ? ("," +  Moji(v.得意先名).convert('HK', 'ZK').toString()) : "");
                v.KeyWord += (!!v.得意先名 ? ("," +  Moji(v.得意先名).convert('ZK', 'HK').toString()) : "");
                v.KeyWord += (!!v.コース名 ? ("," +  Moji(v.コース名).convert('HK', 'ZK').toString()) : "");
                v.KeyWord += (!!v.コース名 ? ("," +  Moji(v.コース名).convert('ZK', 'HK').toString()) : "");
                //英数を全角⇔半角に変換してキーワードに追加
                v.KeyWord += (!!v.得意先名カナ ? ("," +  Moji(v.得意先名カナ).convert('HE', 'ZE').toString()) : "");
                v.KeyWord += (!!v.得意先名カナ ? ("," +  Moji(v.得意先名カナ).convert('ZE', 'HE').toString()) : "");
                v.KeyWord += (!!v.得意先名 ? ("," +  Moji(v.得意先名).convert('HE', 'ZE').toString()) : "");
                v.KeyWord += (!!v.得意先名 ? ("," +  Moji(v.得意先名).convert('ZE', 'HE').toString()) : "");
                v.KeyWord += (!!v.コース名 ? ("," +  Moji(v.コース名).convert('HE', 'ZE').toString()) : "");
                v.KeyWord += (!!v.コース名 ? ("," +  Moji(v.コース名).convert('ZE', 'HE').toString()) : "");
                return v;
            });

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            if (!grid) return;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                if (grid.pdata.filter(v => v.得意先CD == vue.viewModel.CustomerCd).length == 1) {
                    params = _.cloneDeep(grid.pdata.find(v => v.得意先CD == vue.viewModel.CustomerCd));
                } else {

                    var rows = grid.SelectRow().getSelection();
                    if (rows.length != 1) return;

                    params = _.cloneDeep(rows[0].rowData);
                }
            }

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
        },
        showDetailByCd: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            if (!grid) return;

            var cd = vue.viewModel.CustomerCd;
            if (!cd) return;

            if (grid.pdata.filter(v => v.得意先CD == vue.viewModel.CustomerCd).length == 1) {
                vue.showDetail();
            } else {
                if (!!grid && vue.getLoginInfo().isLogOn) {
                    var params = {CustomerCd: cd};
                    // grid.searchData(params, false, null, () => vue.showDetail());
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

                                //部署変更
                                vue.viewModel.BushoCd = res.data.Data[0].部署CD;
                                //再建策
                                vue.conditionChanged();
                            }
                        })
                        .catch(err => {
                            console.log(err);
                        })
                }
            }
        },
        showNewDetail: function(rowData) {
            var vue = this;

            var params = { IsNew: true }
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
        },
        ShoninAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["担当者名", "部署.部署名", "担当者名カナ"];

            if (input == comp.selectValue && comp.isUnique) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.担当者ＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.担当者ＣＤ + " : " + v.担当者名 + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.担当者ＣＤ;
                    ret.text = v.担当者名;
                    return ret;
                })
                ;

            return list;
        },
        showHistory: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            if(!grid) return;
            var param;
            var selection = grid.SelectRow().getSelection();

            param = selection[0].rowData.得意先CD

            vue.showColumns = [
                    { title: "状態", dataIndx: "状態", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 0 },
                    { title: "承認日", dataIndx: "承認日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 1 },
                    { title: "承認者", dataIndx: "承認者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 2 },
                    { title: "状態理由", dataIndx: "状態理由", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 3 },
                    { title: "失客日", dataIndx: "失客日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 4 },
                    { title: "営業担当者", dataIndx: "営業担当者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 5 },
                    { title: "処理日", dataIndx: "処理日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 6 },
                    { title: "登録担当者", dataIndx: "登録担当者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 7 },
                    { title: "Cd", dataIndx: "Cd", dataType: "string", hidden: true, colIndx: 8 },
                    { title: "CdNm", dataIndx: "CdNm", dataType: "string", hidden: true, colIndx: 9 },
            ];

            PageDialog.showSelector({
                dataUrl: "/Utilities/GetCustomerHistoryList",
                params: {CustomerCd : param},
                title: "得意先履歴一覧" + "【" + selection[0].rowData.得意先CD + "：" + selection[0].rowData.得意先名 + "】",
                isModal: true,
                showColumns: vue.showColumns,
                width: 1000,
                height: 500,
                reuse: false,
            });
        },
        showBunpaisaki: function(rowData) {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            if(!grid) return;
            var params;
            var selection = grid.SelectRow().getSelection();

            params = selection[0].rowData

            grid.showLoading();

            //事前情報取得
            axios.all(
                [
                    //得意先リストの取得
                    axios.post("/DAI04042/GetCustomerListForSelect", {CustomerCd: params.得意先CD}),
                ]
            ).then(
                axios.spread((responseCustomer) => {
                    var resCustomer = responseCustomer.data;

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

                    if (!checkError(resCustomer)) return;

                    //取得した結果を設定
                    params.CustomerList = resCustomer;

                    grid.hideLoading();

                    PageDialog.show({
                        pgId: "DAI04042",
                        params: params,
                        isModal: true,
                        isChild: true,
                        width: 600,
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
        showTanka: function(rowData) {
            var vue = this;
            var grid = vue.DAI04040Grid1;
            if(!grid) return;
            var params;
            var selection = grid.SelectRow().getSelection();
            if(!selection) return;

            params = selection[0].rowData

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
    }
}
</script>
