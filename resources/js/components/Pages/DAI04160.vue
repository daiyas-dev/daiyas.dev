<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>対象期間</label>
            </div>
            <div class="col-md-5">
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label style="width: unset; text-align: center; margin-left: 5px; margin-right: 5px;">～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-11">
                <VueMultiSelect
                    id="BushoCd"
                    ref="VueMultiSelect_Busho"
                    :vmodel=viewModel
                    bind="BushoArray"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>キーワード</label>
            </div>
            <div class="col-md-5">
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
        </div>
        <PqGridWrapper
            id="DAI04160Grid1"
            ref="DAI04160Grid1"
            dataUrl="/Utilities/GetHolidayList"
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI04160"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04160",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI04160Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        searchParams: function() {
            var vue = this;
            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");
            return {
                BushoCdArray: vue.viewModel.BushoArray.map(v => v.code),
                DateStart: ms.isValid() ? ms.format("YYYYMMDD") : null,
                DateEnd: me.isValid() ? me.format("YYYYMMDD") : null,
            };
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 祝日マスタメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoArray: [],
                DateStart: null,
                DateEnd: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI04160Grid1: null,
            BushoList: [],
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
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
                { visible: "true", value: "検索", id: "DAI04160_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04160_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI04160Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "詳細", id: "DAI04160Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04020Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "取込", id: "DAI04160_Get", disabled: false, shortcut: "F11",
                    onClick: function () {
                        axios.post("/DAI04160/GetHolidaysFromGov", {noCache: true})
                            .then(res => {
                                vue.conditionChanged();
                            })
                            .catch(err => {
                                console.log(err);
                                $.dialogErr({
                                    title: "取込失敗",
                                    contents: "内閣府提供 国民の祝日一覧の取得に失敗しました"
                                });
                            })
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().startOf("month").format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf("month").format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI04160Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI04160Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onDateChanged: function() {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onBushoChanged: function(code, entities) {
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
            var grid = vue.DAI04160Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                grid.searchData(null, false);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04160Grid1;

            if (!grid) return;

            var rules = [];

            var dcrules = [];
            if (!!vue.searchParams.DateStart) {
                dcrules.push({ condition: "gte", value: moment(vue.searchParams.DateStart, "YYYYMMDD").format("YYYY/MM/DD") });
            }
            if (!!vue.searchParams.DateEnd) {
                dcrules.push({ condition: "lte", value: moment(vue.searchParams.DateEnd, "YYYYMMDD").format("YYYY/MM/DD") });
            }

            if (dcrules.length) {
                rules.push({ dataIndx: "対象日付", mode: "AND", crules: dcrules });
            }

            if (!!vue.searchParams.BushoCdArray.length) {
                var bcrules = vue.searchParams.BushoCdArray.map(b => { return { condition: "contain", mode: "OR", value: b }; });
                rules.push({ dataIndx: "対象部署ＣＤ", mode: "OR", crules: bcrules });
            }

            if (!!vue.viewModel.KeyWord) {
                var keywords = vue.viewModel.KeyWord.split(/[, 、　]/)
                    .map(v => _.trim(v))
                    .map(k => k.replace(/^[\+＋]/, ""))
                    .filter(v => !!v);

                var rulesKeyWord = keywords.map(k => { return { condition: "contain", value: k }; });

                rules.push({ dataIndx: "KeyWord", mode: vue.viewModel.FilterMode, condition: "equal", crules: rulesKeyWord });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onBeforeCreateFunc: function(gridOptions, callback) {
            var vue = this;

            //PqGrid表示前に必要な情報の取得
            axios.all(
                [
                    //祝日マスタのカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "祝日マスタ" }),
                    //部署リスト
                    axios.post("/Utilities/GetBushoList"),
                 ]
            ).then(
                axios.spread((responseHolidayCols, responseBushoList) => {
                    vue.BushoList = responseBushoList.data;
                    var resHolidayCols = responseHolidayCols.data;

                    if (resHolidayCols.onError && !!resHolidayCols.errors) {
                        //メッセージリストに追加
                        Object.values(resHolidayCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resHolidayCols });

                        return;
                    } else if (resHolidayCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "祝日マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resHolidayCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "祝日マスタの取得に失敗しました<br/>" + resHolidayCols.message,
                        });

                        return;
                    } else if (resHolidayCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "祝日マスタの取得に失敗しました<br/>" + resHolidayCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resHolidayCols, v => v.ORDINAL_POSITION * 1)
                        // .filter(v => v.COLUMN_NAME != "パスワード")
                        .map(v => {
                            var width = !!v.COLUMN_LENGTH
                                ? (v.COLUMN_LENGTH * (v.DATA_TYPE == "string" && v.COLUMN_LENGTH > 20 ? 5 : 9)) : 100;

                            var titleWidth = Math.ceil((v.COLUMN_NAME.length + 1) / 2) * 15 + 15;
                            if (width < titleWidth) {
                                width = titleWidth;
                            }

                            var model = {
                                title: v.COLUMN_NAME,
                                dataIndx: v.COLUMN_NAME,
                                dataType: v.DATA_TYPE,
                                width: width,
                                minWidth: width,
                                dbLength: v.COLUMN_LENGTH * 1,
                            };

                            if (model.dataType == "date") {
                                model.format = "yy/mm/dd";
                            }

                            return model;
                        });

                    gridOptions.colModel.push(
                        {
                            title: "KeyWord",
                            dataIndx: "KeyWord",
                            dataType: "string",
                            hidden: true,
                        }
                    );

                    gridOptions.colModel.find(c => c.dataIndx == "対象日付").align = "center";
                    gridOptions.colModel.find(c => c.dataIndx == "対象日付").width = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "対象日付").minWidth = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "対象日付").maxWidth = 120;

                    gridOptions.colModel.find(c => c.dataIndx == "名称").width = 200;
                    gridOptions.colModel.find(c => c.dataIndx == "名称").minWidth = 200;
                    gridOptions.colModel.find(c => c.dataIndx == "名称").maxWidth = 200;

                    gridOptions.colModel.find(c => c.dataIndx == "対象部署ＣＤ").title = "対象部署";
                    gridOptions.colModel.find(c => c.dataIndx == "対象部署ＣＤ").render = ui => {
                        if (!ui.rowData.対象部署ＣＤ) {
                            return "全部署";
                        } else {
                            var text =  ui.rowData.対象部署ＣＤ.split(/ *, */g)
                                .map(v => vue.BushoList.find(b => b.部署CD == v).部署名)
                                .filter(v => !!v)
                                .join(", ");
                            return text;
                        }
                    };
                    // gridOptions.colModel.find(c => c.dataIndx == "対象部署ＣＤ").tooltip = true;

                    gridOptions.colModel.find(c => c.dataIndx == "修正担当者ＣＤ").width = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "修正担当者ＣＤ").minWidth = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "修正担当者ＣＤ").maxWidth = 120;

                    gridOptions.colModel.find(c => c.dataIndx == "修正日").align = "center";
                    gridOptions.colModel.find(c => c.dataIndx == "修正日").width = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "修正日").minWidth = 120;
                    gridOptions.colModel.find(c => c.dataIndx == "修正日").maxWidth = 120;

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                console.log("4160 before error", error);
                $.dialogErr({
                    title: "異常終了",
                    contents: "祝日マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = v.名称;
                return v;
            });

            vue.filterChanged();

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI04160Grid1;
            if (!grid) return;

            var params;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                params = _.cloneDeep(rows[0].rowData);
            }

            params.IsNew = false;

            //DAI04161を子画面表示
            PageDialog.show({
                pgId: "DAI04161",
                params: params,
                isModal: true,
                isChild: true,
                width: 700,
                height: 550,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsNew: true}

            //DAI04161を子画面表示
            PageDialog.show({
                pgId: "DAI04161",
                params: params,
                isModal: true,
                isChild: true,
                width: 700,
                height: 550,
            });
        },
    }
}
</script>
