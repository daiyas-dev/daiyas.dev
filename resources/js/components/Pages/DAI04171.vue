<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label>商品ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input class="form-control text-right p-2" type="text"
                    id="ProductCd"
                    v-model=viewModel.商品ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onProductCdChanged"
                    maxlength="5"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label style="width: unset;">既定製造パターン</label>
            </div>
            <div class="col-md-8">
                <VueSelect
                    id="Pattern"
                    :vmodel=viewModel
                    bind="既定製造パターン"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 35 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onPatternChanged
                    :disabled=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">商品詳細</legend>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="width:100">部署グループ</label>
                            <VueSelect
                                id="BushoGroup"
                                ref="BushoGroup_Select"
                                :vmodel=viewModel
                                bind="部署グループ"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 18 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="">商品名</label>
                            <input type="text" class="form-control" style="width:360px"
                                v-model="viewModel.商品名"
                                id="ProductName"
                                maxlength="24"
                                v-maxBytes=60
                            >
                        </div>
                        <div class="col-md-12">
                            <label class="">商品略称</label>
                            <input type="text" class="form-control" style="width:230px"
                                v-model="viewModel.商品略称"
                                id="ProductAbbr"
                                maxlength=20
                                v-maxBytes=20
                            >
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">商品区分</label>
                            <VueSelect
                                id="ProductKbn"
                                :vmodel=viewModel
                                bind="商品区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 14 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="">売価単価</label>
                            <currency-input
                                class="form-control p-2 text-right" style="width: 100px;"
                                v-model=viewModel.売価単価
                                maxlength="9"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">弁当区分</label>
                            <VueSelect
                                id="BentoKbn"
                                ref="BentoKbn_Select"
                                :vmodel=viewModel
                                bind="弁当区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 15 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">副食区分</label>
                            <VueSelect
                                id="SubKbn"
                                :vmodel=viewModel
                                bind="副食ＣＤ"
                                uri="/Utilities/GetMainSubListForSelect"
                                :params="{ bentoKbn: 1 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">主食区分</label>
                            <VueSelect
                                id="MainKbn"
                                :vmodel=viewModel
                                bind="主食ＣＤ"
                                uri="/Utilities/GetMainSubListForSelect"
                                :params="{ bentoKbn: 2 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-12">
                            <label class="width:100">表示区分</label>
                            <VueSelect
                                id="DisplayKbn"
                                ref="DisplayKbn_Select"
                                :vmodel=viewModel
                                bind="表示ＦＬＧ"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 17 }"
                                :withCode=true
                                customStyle="{ width: 200px; }"
                            />
                        </div>
                        <div class="col-md-12">
                            <label>部数単位</label>
                            <currency-input
                                class="form-control p-2 text-right" style="width: 80px;"
                                type="tel"
                                v-model=viewModel.部数単位
                                maxlength="6"
                            />
                        </div>
                    </div>
                </fieldset>
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
    min-width: 100px;
}
fieldset div[class^="col-md"] label {
    min-width: 90px;
    text-align: left;
    margin-left: 10px;
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
    margin: 1;
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
</style>
<style>
#Page_DAI04171 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04171",
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
            ScreenTitle: "製造品マスタメンテ詳細",
            noViewModel: true,
            DAI04171Grid1: null,
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        //currency-input項目はString->Numberに変換
        data.viewModel.売価単価 = (data.viewModel.売価単価 || 0) * 1;
        data.viewModel.部数単位 = (data.viewModel.部数単位 || 0) * 1;

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04171_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.clearDetail();
                    }
                },
                { visible: "true", value: "削除", id: "DAI04171_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        var cd = vue.viewModel.商品ＣＤ;
                        if(!cd) return;

                        var params = _.cloneDeep(vue.viewModel);
                        params.noCache = true;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04171/Delete", params)
                                            .then(res => {
                                                DAI04170.conditionChanged();
                                                $(this).dialog("close");
                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            })
                                            .catch(err => {
                                                console.log(err);
                                                $.dialogErr({
                                                    title: "異常終了",
                                                    contents: "製造品マスタの削除に失敗しました"
                                                })
                                            }
                                        );
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
                { visible: "true", value: "登録", id: "DAI04171Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.viewModel.商品ＣＤ || !vue.viewModel.商品名 || !vue.viewModel.商品略称 || !vue.viewModel.商品区分){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.商品ＣＤ ? "商品CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.商品名 ? "商品名が入力されていません。<br/>" : "",
                                    !vue.viewModel.商品略称 ? "商品略称が入力されていません。<br/>" : "",
                                    !vue.viewModel.商品区分 ? "商品区分が入力されていません。" : "",

                                ]
                            })
                            if(!vue.viewModel.商品ＣＤ){
                                $(vue.$el).find("#ProductCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#ProductCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.商品名){
                                $(vue.$el).find("#ProductName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#ProductName").removeClass("has-error");
                            }
                            if(!vue.viewModel.商品略称){
                                $(vue.$el).find("#ProductAbbr").addClass("has-error");
                            }else{
                                $(vue.$el).find("#ProductAbbr").removeClass("has-error");
                            }
                            if(!vue.viewModel.商品区分){
                                $(vue.$el).find(".ProductKbn").addClass("has-error");
                            }else{
                                $(vue.$el).find(".ProductKbn").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.ｸﾞﾙｰﾌﾟ区分 = 0;
                        params.売価単価 = params.売価単価 || 0;
                        params.副食ＣＤ = params.副食ＣＤ || 0;
                        params.主食ＣＤ = params.主食ＣＤ || 0;
                        params.部数単位 = params.部数単位 || 0;

                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録中ダイアログ
                        var progressDlg = $.dialogProgress({
                            contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 登録中…",
                        });

                        axios.post("/DAI04171/Save", params)
                            .then(res => {
                                progressDlg.dialog("close");
                                DAI04170.conditionChanged();
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            })
                            .catch(err => {
                                progressDlg.dialog("close");
                                console.log(err);
                                $.dialogErr({
                                    title: "異常終了",
                                    contents: "製造品マスタの登録に失敗しました"
                                })
                            }
                        );
                        $(this).dialog("close");
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onProductCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByProductCd();
        },
        onPatternChanged: function(code, entities) {
            var vue = this;

            vue.searchByProductCd();
        },
        searchByProductCd: function() {
            var vue = this;
            if (!vue.viewModel.商品ＣＤ || !vue.viewModel.既定製造パターン) return;

            var params = {noCache: true};

            axios.post("/DAI04170/Search", params)
                .then(res => {
                    var match = res.data
                        .find(r => r.商品ＣＤ == vue.viewModel.商品ＣＤ && r.既定製造パターン == vue.viewModel.既定製造パターン);

                    if (!!match) {
                        $.dialogConfirm({
                            title: "マスタ編集確認",
                            contents: "マスタを編集しますか？",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        vue.viewModel = match;

                                        vue.viewModel.売価単価 = (vue.viewModel.売価単価 || 0 ) * 1;
                                        vue.viewModel.部数単位 = (vue.viewModel.部数単位 || 0 ) * 1;

                                        //ボタン制御
                                        $("[shortcut='F3']").prop("disabled", false);
                                        $(this).dialog("close");
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
                        $("[shortcut='F3']").prop("disabled", false);
                    }else{
                        //削除ボタン制御
                        $("[shortcut='F3']").prop("disabled", true);
                        return;
                    }
                })
                .catch(err => {
                    console.log(err);
                }
            )
        },
        clearDetail: function(){
            var vue = this;

            _.keys(DAI04171.viewModel).forEach(k => DAI04171.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            vue.viewModel.売価単価 = 0;
            vue.viewModel.部署グループ = vue.$refs.BushoGroup_Select.entities[0].code;
            vue.viewModel.弁当区分 = vue.$refs.BentoKbn_Select.entities[0].code;
            vue.viewModel.表示区分 = vue.$refs.DisplayKbn_Select.entities[0].code;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);

        },
    }
}
</script>
