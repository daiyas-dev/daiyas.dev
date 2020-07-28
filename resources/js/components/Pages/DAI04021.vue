<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label>担当者ＣＤ</label>
                <input class="form-control text-right" type="text"
                    id="TantoCd"
                    v-model=viewModel.担当者ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onTantoCdChanged"
                    maxlength="6"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">担当者名</label>
                <input type="text" class="form-control" v-model="viewModel.担当者名"
                    id="TantoName"
                    maxlength=60
                    v-maxBytes=60
                    v-setKana="res => {viewModel.担当者名カナ = (viewModel.担当者名カナ || '') + res.toString(); $forceUpdate();}"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="">担当者カナ</label>
                <input type="text" class="form-control" style="font-size: 15px !important;"
                    v-model="viewModel.担当者名カナ"
                    maxlength=30
                    v-maxBytes=30
                    >
            </div>
        </div>
        <!-- <div class="row">
            <div class="col-md-6">
                <label class="">郵便番号</label>
                <input class="form-control" style="width: 100px;" type="tel"
                    v-model=viewModel.郵便番号
                    maxlength="8"
                    v-maxBytes=8
                    >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>住所１</label>
                <input class="form-control" type="text"
                    v-model=viewModel.住所
                    maxlength=60
                    v-maxBytes=60
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">電話番号</label>
                <input class="form-control" style="width: 130px;" type="tel"
                    v-model=viewModel.電話番号
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">FAX</label>
                <input class="form-control" style="width: 130px;" type="tel"
                    v-model=viewModel.ＦＡＸ
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
        </div> -->
        <div class="row">
            <div class="col-md-8">
                <label>部署</label>
                <VueSelect
                    id="BushoCd"
                    ref="BushoCdSelect"
                    :vmodel=viewModel
                    bind="所属部署ＣＤ"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=!viewModel.IsNew
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mt-1 mb-1">
                <label>オペレータ</label>
                <VueCheck
                    bind="オペレータ"
                    :vmodel=viewModel
                    checkedCode="1"
                    customContainerStyle="border-style: none;"
                    :list="[
                        {code: '0', label: 'チェック無し：オペレータではない'},
                        {code: '1', label: 'チェック有り：オペレータ'}
                    ]"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <label class="">ユーザーID</label>
                <input class="form-control p-2" style="width: 240px;" type="text"
                    v-model=viewModel.ユーザーＩＤ
                    maxlength=20
                    v-maxBytes=20
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <label class="">パスワード</label>
                <input class="form-control p-2" style="width: 240px;" type="text"
                    v-model=viewModel.パスワード
                    maxlength=20
                    v-maxBytes=20
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="width:100">営業業務区分</label>
                <VueSelect
                    id="EigoKbn"
                    ref="EigoKbn_Select"
                    :vmodel=viewModel
                    bind="営業業務区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 46 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="">権限区分</label>
                <input class="form-control p-2" style="width: 50px;" type="text"
                    v-model=viewModel.権限区分
                    maxlength="2"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="">在職区分</label>
                <VueSelect
                    id="ZaishokuKbn"
                    ref="ZaishokuKbn_Select"
                    :vmodel=viewModel
                    bind="在職区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 51 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
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
    min-width: 100px;
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
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
<style>
#Page_DAI04021 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04021",
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
            ScreenTitle: "担当者マスタメンテ詳細",
            noViewModel: true,
            DAI04021Grid1: null,
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

        data.viewModel.オペレータ = data.viewModel.オペレータ || "";

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04021_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.clearDetail();
                    }
                },
                { visible: "true", value: "削除", id: "DAI04021_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {
                        var cd = vue.viewModel.担当者ＣＤ;
                        if(!cd) return;

                        var params = {TantoCd: cd};
                        params.noCache = true;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04021/Delete", params)
                                            .then(res => {
                                                DAI04020.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04021を閉じる
                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            })
                                            .catch(err => {
                                                console.log(err);
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
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04021Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 修正の時、所属部署が古いCDの場合どうするか->そのままの値で保存

                        if(!vue.viewModel.担当者ＣＤ || !vue.viewModel.担当者名 || !vue.viewModel.所属部署ＣＤ){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.担当者ＣＤ ? "担当者CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.担当者名 ? "担当者名が入力されていません。<br/>" : "",
                                    !vue.viewModel.所属部署ＣＤ ? "所属部署CDが入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.担当者ＣＤ){
                                $(vue.$el).find("#TantoCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TantoCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.担当者名){
                                $(vue.$el).find("#TantoName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TantoName").removeClass("has-error");
                            }
                            if(!vue.viewModel.所属部署ＣＤ){
                                $(vue.$el).find(".BushoCd").addClass("has-error");
                            }else{
                                $(vue.$el).find(".BushoCd").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);
                        params.noCache = true;

                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //チェックボックス
                        params.オペレータ = params.オペレータ || 0;

                        var Message = {
                            "department_code": params.所属部署ＣＤ,
                            "course_code": null,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04021/Save", params)
                            .then(res => {
                                //Cache更新
                                axios.post("/Utilities/GetTantoList", {noCache: true, replace: true});
                                axios.post("/Utilities/GetTantoListForSelect", {noCache: true, replace: true});

                                DAI04020.conditionChanged();
                                //画面を閉じる
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            })
                            .catch(err => {
                                console.log(err);
                            }
                        );
                        console.log("登録", params);
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onTantoCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByTantoCd();
        },
        searchByTantoCd: function() {
            var vue = this;
            var cd = vue.viewModel.担当者ＣＤ;
            if (!cd) return;

            var params = {TantoCd: cd};
            params.noCache = true;

            axios.post("/Utilities/GetTantoListForMaint", params)
                .then(res => {
                    if (res.data.length == 1) {
                        $.dialogConfirm({
                            title: "マスタ編集確認",
                            contents: "マスタを編集しますか？",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        vue.viewModel = res.data[0];
                                        $(this).dialog("close");
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        vue.viewModel.担当者ＣＤ = "";
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

            $(vue.$el).find(".has-error").removeClass("has-error");

            _.keys(DAI04021.viewModel).forEach(k => DAI04021.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            vue.viewModel.営業業務区分 = vue.$refs.EigoKbn_Select.entities[0].code;
            //所属部署CDが現在ない部署の場合考慮
            var bushoList = vue.$refs.BushoCdSelect.entities
            vue.viewModel.所属部署ＣＤ = !!bushoList[0].name ? bushoList[0].code : bushoList[1].code;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);

        },
    }
}
</script>
