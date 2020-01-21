<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label>税区分</label>
                <input class="form-control text-right" type="text"
                    id="TaxKbn"
                    v-model=viewModel.税区分
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">消費税名称</label>
                <input type="text" id="TaxName" class="form-control" v-model="viewModel.消費税名称">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="">消費税率</label>
                <input type="text" class="form-control text-right" v-model="viewModel.消費税率">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>内外区分</label>
                <VueSelect
                    id="NaigaiKbn"
                    :vmodel=viewModel
                    bind="内外区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 20 }"
                    codeName="サブ各種CD1"
                    :withCode=true
                    :hasNull=false
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>適用年月</label>
                <!-- TODO:DatePickerが子画面からはみ出る -->
                <DatePickerWrapper
                    id="TekiyoDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="適用年月"
                    :editable=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">現在利用</label>
                <VueSelect
                    id="RiyoFlg"
                    :vmodel=viewModel
                    bind="現在使用FLG"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 21 }"
                    :withCode=true
                    :hasNull=false
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
span.ModeLabel {
    font-size: 15px;
}
.row {
    margin-bottom: 2px;
}
div[class^="col-md"] > label {
    min-width: 80px;
}
fieldset div[class^="col-md"] label {
    min-width: 90px;
}
fieldset fieldset label {
    margin-right: -5px;
}
fieldset {
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    padding-right: 5px;
    margin: 0;
}
fieldset * {
    font-size: 14px !important;
}
legend {
    font-size: 15px;
    width: auto;
    padding: 0;
    margin: 0;
    margin-left: 5px;
    border-bottom:none;
}
fieldset .row {
    margin-left: 10px;
    margin-right: 0px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
.groupFactory{
    width: 100px;
    text-align: left !important;
    margin-left: 30px;
}
</style>
<style>
#Page_DAI04141 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04141",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "消費税率マスタメンテ詳細",
            noViewModel: true,
            DAI04141Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 200,
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
                ],
                colModel: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04141_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04141_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                { visible: "true", value: "登録", id: "DAI04141Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                        if(!vue.viewModel.税区分 || !vue.viewModel.消費税名称){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.税区分 ? "税区分が入力されていません。<br/>" : "",
                                    !vue.viewModel.消費税名称 ? "消費税名称が入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.税区分){
                                $(vue.$el).find("#TaxKbn").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TaxKbn").removeClass("has-error");
                            }
                            if(!vue.viewModel.消費税名称){
                                $(vue.$el).find("#TaxName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TaxName").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.消費税名称 = !!params.消費税名称 ? params.消費税名称 : '';
                        params.消費税率 = !!params.消費税率 ? params.消費税率 : 0;
                        params.適用年月 = !!params.適用年月 ? moment(vue.viewModel.適用年月,"YYYY-MM-DD").format("YYYY-MM-DD") : "";
                        params.修正担当者ＣＤ = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //TODO: 登録用controller method call
                        axios.post("/DAI04141/Save", params)
                            .then(res => {
                                $(vue.$el).find(".has-error").removeClass("has-error");
                            })
                            .catch(err => {
                                console.log(error);
                                //TODO: エラー
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //適用年月の初期値 -> 当日
            vue.viewModel.適用年月 = moment();

            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
    }
}
</script>
