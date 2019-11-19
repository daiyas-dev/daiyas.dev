<template>
    <form id="this.$options.name">
        <div class="row">
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
            <div class="col-md-4">
                <label>承認者</label>
                <PopupSelect
                    id="ShoninSelect"
                    ref="PopupSelect_Shonin"
                    :vmodel=viewModel
                    bind="ShoninCd"
                    buddy="ShoninNm"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: viewModel.BushoCd }"
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
                    :onAfterChangedFunc=onShoninCdChanged
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
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
            id="DAI04040Grid1"
            ref="DAI04040Grid1"
            dataUrl="/Utilities/GetCustomerList"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onBeforeCreateFunc=onBeforeCreateFunc
            :onAfterSearchFunc=onAfterSearchFunc
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
    },
    watch: {
        "viewModel.BushoCd": {
            handler: function(newVal) {
                var vue = this;
                var grid = vue.DAI08010Grid1;

                console.log("viewModel.BushoCd", newVal);
                // vue.conditionChanged();
            },
        },
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
                State: null,
                ShoninDate: null,
                ShoninCd: null,
                ShoninNm: null,
                KeyWord: null,
                FilterMode: "AND",
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
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04040_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI04040_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "ダウンロード", id: "DAI04040_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: ダウンロード
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI04040Grid1_Detail", disabled: false, shortcut: "F8",
                    onClick: function () {
                        //TODO: 詳細
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI04040Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                    }
                }
            );
        },
        mountedFunc: function(vue) {
        },
        onBushoChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
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
        conditionChanged: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;

            console.log("DAI04040 conditionChanged", vue.getLoginInfo().isLogOn);

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = $.extend(true, {}, vue.viewModel);
                grid.searchData(params, false);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI04040Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.State) {
                rules.push({ dataIndx: "状態区分", condition: "equal", value: vue.viewModel.State });
            }
            if (!!vue.viewModel.ShoninDate) {
                rules.push({ dataIndx: "承認日", condition: "equal", value: moment(vue.viewModel.ShoninDate).format("YYYY-MM-DD HH:mm:ss.SSS") });
            }
            if (!!vue.viewModel.ShoninCd) {
                rules.push({ dataIndx: "承認者ＣＤ", condition: "equal", value: vue.viewModel.ShoninCd });
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
                    //得意先マスタのカラム情報
                    axios.post("/Utilities/GetColumns", { TableName: "得意先マスタ" }),
                 ]
            ).then(
                axios.spread((responseCustomerCols) => {
                    var resCustomerCols = responseCustomerCols.data;

                    if (resCustomerCols.onError && !!resCustomerCols.errors) {
                        //メッセージリストに追加
                        Object.values(resCustomerCols.errors).filter(v => v)
                            .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                        //ダイアログ
                        $.dialogErr({ errObj: resCustomerCols });

                        return;
                    } else if (resCustomerCols.onException) {
                        //メッセージ追加
                        vue.$root.$emit("addMessage", "得意先マスタ取得失敗(" + vue.page.ScreenTitle + ":" + resCustomerCols.message + ")");

                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "得意先マスタの取得に失敗しました<br/>" + resCustomerCols.message,
                        });

                        return;
                    } else if (resCustomerCols == "") {
                        //完了ダイアログ
                        //ダイアログ
                        $.dialogErr({
                            title: "異常終了",
                            contents: "得意先マスタの取得に失敗しました<br/>" + resCustomerCols.message,
                        });

                        return;
                    }

                    //colModel設定
                    gridOptions.colModel = _.sortBy(resCustomerCols, v => v.ORDINAL_POSITION * 1)
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

                    //callback実行
                    callback();
                })
            )
            .catch(error => {
                //メッセージ追加
                vue.$root.$emit("addMessage", "得意先マスタ検索失敗(" + vue.page.ScreenTitle + ":" + error + ")");

                //ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "得意先マスタの検索に失敗しました<br/>",
                });
            });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            //キーワード追加
            res = res.map(v => {
                v.KeyWord = _.values(v).join(",");
                // delete v.パスワード;
                return v;
            });

            return res;
        },
    }
}
</script>
