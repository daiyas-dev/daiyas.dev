<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-12 align-items-start">
                <PqGridWrapper
                    id="DAI04042Grid1"
                    ref="DAI04042Grid1"
                    dataUrl="/Utilities/GetBunpaisakiList"
                    :query="{ CustomerCd: params.得意先CD, BushoCd: params.部署CD }"
                    :SearchOnCreate=true
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    :resizeFunc=onMainGridResize
                    :isMultiRowSelectable=true
                    :autoToolTipDisabled=true
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button {
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
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI04042"] .pq-grid .DAI04042_toolbar .toolbar_button.ope {
    width: 45px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04042",
    components: {
    },
    computed: {
        FormattedKojoKbn: function() {
            var vue = this;
            return vue.viewModel.KojoKbn ? moment(vue.viewModel.KojoKbn, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "分配得意先入力",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
                MngCd: null,
                Kind: null,
                KindNm: null,
                StartDate: null,
                EndDate: null,
                Memo: null,
            },
            DAI04042Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI04042_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRowFunc();
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRowFunc();
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: false },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false},
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                freezeCols: 2,
                width: "100%",
                height: 450,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
                colModel: [
                    {
                        title: "得意先",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                        key: true,
                        editable: true,
                        width: 125, maxWidth: 125, minWidth: 125,
                        autocomplete: {
                            source: () => vue.GetCustomerListForSelect(),
                            bind: "得意先ＣＤ",
                            buddies: { "得意先名": "CdNm" },
                            AutoCompleteFunc: vue.CustomerAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                        },
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        editable: false,
                    },
                ],
                formulas: [
                ],
            },
        });

        var targets;
        if (!!vue.params || !!vue.query) {
            data.viewModel.BushoCd
            targets = (vue.params || vue.query).targets;
        }

        if (!targets) return data;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "行追加", id: "DAI04042_AddRow", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.addRowFunc();
                    }
                },
                { visible: "true", value: "行削除", id: "DAI04042_DeleteRow", disabled: false, shortcut: "F3",
                    onClick: function () {
                        vue.deleteRowFunc();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04042_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO:現行では、メッセージ「親得意先と同一のコードは使用できません。」と所属部署が違う場合に「得意先が存在しません」がでる。
                        //登録可能条件を、同じ所属部署かつ親得意先コード以外、とするか。所属部署は関係なく親得意先コード以外とするか。
                        //過去データは、親得意先コードが入っているデータが多くある。

                        if((grid.widget().find(".has-error").length == 0 ) && (grid.widget().find(".ui-state-error").length == 0 )){
                            vue.saveBunpaisaki();
                        }else{
                            $.dialogErr({
                                title: "入力値エラー",
                                contents: "登録できません。",
                            })
                        }
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        GetCustomerListForSelect: function() {
            var vue = this;
            return vue.params.CustomerList;
        },
        conditionChanged: function(forced) {
            var vue = this;
            var grid1 = vue.DAI04042Grid1;

            if (!!grid1 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && (!!vue.viewModel.MngCd && !vue.viewModel.MngCd.includes("新規"));
                var bushoChanged = !grid1.prevPostData || grid1.prevPostData.bushoCd != vue.viewModel.BushoCd;
                var courseChanged = !grid1.prevPostData || grid1.prevPostData.courseCd != vue.viewModel.CourseCd;
                var mngCdChanged = !grid1.prevPostData || grid1.prevPostData.mngCd != vue.viewModel.MngCd;

                if (required && (forced || bushoChanged || courseChanged || mngCdChanged)) {
                    grid1.searchData({ bushoCd: vue.viewModel.BushoCd, courseCd: vue.viewModel.CourseCd, mngCd: vue.viewModel.MngCd });
                }
            }
        },
        saveBunpaisaki: function() {
            var vue = this;
            var grid = vue.DAI04042Grid1;
            //空行はparamsからを除く
            var bunpaisakiList = grid.pdata.filter(v => !!v.得意先ＣＤ).map((v,i) => v.得意先ＣＤ);

            var Message = {
                "department_code": null,
                "course_code": null,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };

            var params = { CustomerCd: DAI04042.params.得意先CD, Bunpaisaki: bunpaisakiList, Message: Message };
            params.noCache = true;

            axios.post("/DAI04042/UpdateBunpaisakiList", params)
            .then(res => {
                //画面を閉じる
                $(vue.$el).closest(".ui-dialog-content").dialog("close");
            })
            .catch(err => {
                console.log(err);
            });

        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            res = res.map(v => {
                return v;
            });

            return res;
        },
        onMainGridResize: grid => {
        },
        CustomerAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ"];

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
        addRowFunc: function() {
            var grid = DAI04042Grid1;
            var rowIndx = grid.SelectRow().getSelection().length == 0
                ? 0
                : (grid.SelectRow().getFirst() + 1);

            grid.addRow({
                rowIndx: rowIndx,
                newRow: {},
                checkEditable: false
            });

            grid.scrollRow({rowIndxPage: rowIndx});

        },
        deleteRowFunc: function() {
            var vue = this;
            var grid = vue.DAI04042Grid1;

            //選択行なし
            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var row = grid.SelectRow().getSelection()[0].rowData;

            //選択行が未保存のデータなら、画面上行削除のみ
            if(!row.InitialValue){
                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                grid.deleteRow({ rowList: rowList });

                return;
            }

            //選択行の初期値から削除対象のキーを取得
            var bunpaisakiCd = row.InitialValue.得意先ＣＤ;

            var Message = {
                "department_code": null,
                "course_code": null,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };

            //選択中の得意先CDの受注得意先をnullで更新
            var params = { CustomerCd: DAI04042.params.得意先CD, Bunpaisaki: bunpaisakiCd, Message: Message };
            params.noCache = true;

            axios.post("/DAI04042/DeleteBunpaisakiList", params)
            .then(res => {
                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                grid.deleteRow({ rowList: rowList });
            })
            .catch(err => {
                console.log(err);
            });
        },
    }
}
</script>
