<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input class="form-control text-right" type="text"
                    id="BushoCd"
                    v-model=viewModel.部署CD
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onBushoCdChanged"
                    maxlength="6"
                    v-int
                >
            </div>
            <div class="col-md-1">
                <label class="">部署名</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" id="BushoName" style="width: 290px;"
                    v-model="viewModel.部署名"
                    maxlength=24
                    v-maxBytes=24
                    v-setKana="res => {viewModel.部署名カナ = (viewModel.部署名カナ || '') + res.toString(); $forceUpdate();}"
            >
            </div>
            <div class="col-md-1">
                <label class="">部署名カナ</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" style="width: 250px;"
                    v-model="viewModel.部署名カナ"
                    maxlength=20
                    v-maxBytes=20
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">会社名称</label>
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control"
                    v-model="viewModel.会社名称"
                    maxlength=60
                    v-maxBytes=60
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">郵便番号</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-2" style="width: 90px;" type="tel"
                    v-model=viewModel.郵便番号
                    maxlength="8"
                    v-maxBytes=8
                >
            </div>
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-8">
                <input class="form-control" type="text"
                    v-model=viewModel.住所
                    maxlength=60
                    v-maxBytes=60
                >
            </div>
        </div>
        <div class="row align-items-start">
            <div class="col-md-1">
                <label class="">電話番号</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-1" style="width: 120px;" type="tel"
                    v-model=viewModel.電話番号
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
            <div class="col-md-1">
                <label class="">FAX</label>
            </div>
            <div class="col-md-2">
                <input class="form-control p-1" style="width: 120px;" type="tel"
                    v-model=viewModel.FAX
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
            <div class="col-md-1">
                <label class="">ロゴ/印鑑</label>
            </div>
            <div class="col-md-2">
                <div v-if="IsExistImage"
                    class="droppable"
                    :data-url='"/DAI04071/UploadImage?BushoCd=" + viewModel.部署CD'
                    data-upload-callback="uploadLogoCallback"
                    style="width: 85px; height: 85px; background-size: contain; background-repeat: no-repeat;"
                    :style='"background-image: url(" + ImagePath + ");"'
                >
                </div>
                <div v-else
                    class="droppable"
                    :data-url='"/DAI04071/UploadImage?BushoCd=" + viewModel.部署CD'
                    data-upload-callback="uploadLogoCallback"
                    style="width: 85px; height: 85px; background-color: white;"
                >
                </div>
                <span class="ml-1">{{!IsExistImage ? "未設定" : ImageFile == viewModel.部署CD + ".png" ? "登録済" : "未登録"}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">口座情報１</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">金融機関名</label>
                            <PopupSelect
                                id="BankSelect1"
                                ref="PopupSelect_Bank1"
                                :vmodel=viewModel
                                bind="金融機関CD1"
                                buddy="金融機関名称1"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: null, KeyWord: BankKeyWord }"
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名称"
                                :showColumns='[
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onAfterChangedFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="shitenmei">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect1"
                                ref="PopupSelect_BankBranch1"
                                :vmodel=viewModel
                                bind="金融機関支店CD1"
                                buddy="金融機関支店名1"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD1, BranchCd: null, KeyWord: BankBranchKeyWord }"
                                :isPreload=true
                                title="支店一覧"
                                labelCd="支店CD"
                                labelCdNm="支店名"
                                :showColumns='[
                                    { title: "金融機関CD", dataIndx: "銀行CD", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                                    { title: "金融機関名", dataIndx: "銀行名", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 1 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=190
                                :ParamsChangedCheckFunc=BankBranch1ParamsChangedCheckFunc
                                :onAfterChangedFunc=onBankBranchChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                :dataListReset=true
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">口座種別</label>
                            <VueSelect
                                id="KouzaKind"
                                :vmodel=viewModel
                                bind="口座種別1"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 7 }"
                                :withCode=true
                                :hasNull=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-3">
                            <label>口座番号</label>
                            <input class="form-control p-1" style="font-size: 15px !important; width: 90px;" type="text"
                                v-model=viewModel.口座番号1
                                maxlength="7"
                                v-maxBytes=7
                            >
                        </div>
                        <div class="col-md-5">
                            <label class="">口座名義人</label>
                            <input class="form-control" type="text" style="font-size: 15px !important;"
                                v-model=viewModel.口座名義人1
                                maxlength="15"
                                v-maxBytes=30
                            >
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">口座情報２</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">金融機関名</label>
                            <PopupSelect
                                id="BankSelect2"
                                ref="PopupSelect_Bank2"
                                :vmodel=viewModel
                                bind="金融機関CD2"
                                buddy="金融機関名称2"
                                dataUrl="/Utilities/GetBankList"
                                :params="{ BankCd: null }"
                                :isPreload=true
                                title="金融機関一覧"
                                labelCd="金融機関CD"
                                labelCdNm="金融機関名称"
                                :showColumns='[
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onAfterChangedFunc=onBankChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankAutoCompleteFunc
                            />
                        </div>
                        <div class="col-md-6">
                            <label class="shitenmei">支店名</label>
                            <PopupSelect
                                id="BankBranchSelect2"
                                ref="PopupSelect_BankBranch2"
                                :vmodel=viewModel
                                bind="金融機関支店CD2"
                                buddy="金融機関支店名2"
                                dataUrl="/Utilities/GetBankBranchList"
                                :params="{ BankCd: viewModel.金融機関CD2, BranchCd: null, KeyWord: BankBranchKeyWord }"
                                :isPreload=true
                                title="支店一覧"
                                labelCd="支店CD"
                                labelCdNm="支店名"
                                :showColumns='[
                                    { title: "金融機関CD", dataIndx: "銀行CD", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                                    { title: "金融機関名", dataIndx: "銀行名", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 1 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :inputWidth=95
                                :nameWidth=190
                                :ParamsChangedCheckFunc=BankBranch2ParamsChangedCheckFunc
                                :onAfterChangedFunc=onBankBranchChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                :dataListReset=true
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">口座種別</label>
                            <VueSelect
                                id="KouzaKind"
                                :vmodel=viewModel
                                bind="口座種別2"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 7 }"
                                :withCode=true
                                :hasNull=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-3">
                            <label>口座番号</label>
                            <input class="form-control p-1" style="font-size: 15px !important; width: 90px;" type="text"
                                v-model=viewModel.口座番号2
                                maxlength="7"
                                v-maxBytes=7
                            >
                        </div>
                        <div class="col-md-5">
                            <label class="">口座名義人</label>
                            <input class="form-control" type="text" style="font-size: 15px !important;"
                                v-model=viewModel.口座名義人2
                                maxlength="15"
                                v-maxBytes=30
                            >
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <fieldset class="kouza-info w-100">
                    <legend class="kouza-info">モバイル連携情報</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">主要商品CD１</label>
                            <PopupSelect
                                id="ProductSelect1"
                                ref="PopupSelect_Product1"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ1"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForSelect"
                                :params="{ ProductCd: null, KeyWord: ProductKeyWord }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="商品ＣＤ"
                                labelCdNm="商品名"
                                :showColumns='[
                                    { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                                    { title: "売価単価", dataIndx: "売価単価", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 3 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onChangeFunc=onProductChanged
                                :isShowAutoComplete=true
                                :AutoCompleteNoLimit=true
                                :AutoCompleteFunc=ProductAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">主要商品CD２</label>
                            <PopupSelect
                                id="ProductSelect2"
                                ref="PopupSelect_Product2"
                                :vmodel=viewModel
                                bind="モバイル_主要商品ＣＤ2"
                                buddy="商品名"
                                dataUrl="/Utilities/GetProductListForSelect"
                                :params="{ ProductCd: null, KeyWord: ProductKeyWord }"
                                :isPreload=true
                                title="商品名一覧"
                                labelCd="商品ＣＤ"
                                labelCdNm="商品名"
                                :showColumns='[
                                    { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                                    { title: "売価単価", dataIndx: "売価単価", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 3 },
                                ]'
                                :popupWidth=600
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=95
                                :nameWidth=235
                                :onChangeFunc=onProductChanged
                                :isShowAutoComplete=true
                                :AutoCompleteNoLimit=true
                                :AutoCompleteFunc=ProductAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>メッセージ</label>
                            <input class="form-control p-1" type="text" style="font-size: 15px !important;"
                                v-model=viewModel.モバイル_メッセージ
                                maxlength="60"
                                v-maxBytes=60
                            >
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4 busyoGFactory">
                <div class="row">
                    <div class="col-md-12">
                        <label class="groupFactory">部署グループ</label>
                        <VueSelect
                            id="BushoGroup"
                            :vmodel=viewModel
                            bind="部署グループ"
                            uri="/Utilities/GetCodeList"
                            :params="{ cd: 18 }"
                            :withCode=true
                            customStyle="{ width: 100px; }"
                            :hasNull=true
                        />
                    </div>
                    <div class="col-md-12">
                        <label class="groupFactory">工場</label>
                        <VueSelect
                            id="factory"
                            :vmodel=viewModel
                            bind="工場ＣＤ"
                            uri="/Utilities/GetCodeList"
                            :params="{ cd: 37 }"
                            :withCode=true
                            :hasNull=true
                            customStyle="{ width: 100px; }"
                        />
                    </div>
                </div>
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
    margin-left: 40px;
}
.shitenmei{
    min-width: unset !important;
    margin-left: 10px;
}
.busyoGFactory{
    align-items: flex-start;
    margin-block-start: 10px;
}
</style>
<style>
#Page_DAI04071 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04071",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        ImagePath: function() {
            var vue = this;
            return location.origin + "/images/BushoStamp/" + vue.ImageFile;
        },
    },
    watch: {
        "viewModel.金融機関CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関CD1 = "";
                }
            },
        },
        "viewModel.金融機関支店CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0" && !vue.viewModel.金融機関支店名称１) {
                    vue.viewModel.金融機関支店CD1 = "";
                }
            },
        },
        "viewModel.金融機関CD2": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関CD2 = "";
                }
            },
        },
        "viewModel.金融機関支店CD1": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関支店CD1 = "";
                }
            },
        },
        "viewModel.金融機関支店CD2": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0" && !vue.viewModel.金融機関支店名称２) {
                    vue.viewModel.金融機関支店CD2 = "";
                }
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "部署マスタメンテ詳細",
            noViewModel: true,
            ImageFile: null,
            IsExistImage: false,
            DAI04071Grid1: null,
            BankKeyWord: null,
            BankBranchKeyWord: null,
            ProductKeyWord: null,
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
                { visible: "true", value: "クリア", id: "DAI04071_Clear", disabled: false, shortcut: "F2",
                    onClick: function (evt) {
                        vue.clearDetail();
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "削除", id: "DAI04071_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        var cd = vue.viewModel.部署CD;
                        if(!cd) return;

                        var params = {BushoCd: cd};
                        params.noCache = true;

                        var Message = {
                            "department_code": vue.viewModel.部署CD,
                            "course_code": null,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04071/Delete", params)
                                            .then(res => {
                                                DAI04070.conditionChanged();
                                                $(this).dialog("close");
                                                //画面を閉じる
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
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04071Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.viewModel.部署CD || !vue.viewModel.部署名){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.部署CD ? "部署CDが入力されていません。<br/>" : "",
                                    !vue.viewModel.部署名 ? "部署名が入力されていません。" : ""
                                ]
                            })
                            if(!vue.viewModel.部署CD){
                                $(vue.$el).find("#BushoCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BushoCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.部署名){
                                $(vue.$el).find("#BushoName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BushoName").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);
                        params.noCache = true;

                        params.部署グループ = params.部署グループ || 0;
                        params.モバイル_主要商品ＣＤ1 = params.モバイル_主要商品ＣＤ1 || 0;
                        params.モバイル_主要商品ＣＤ2 = params.モバイル_主要商品ＣＤ2 || 0;
                        params.モバイル_主要商品ＣＤ3 = params.モバイル_主要商品ＣＤ3 || 0;
                        params.金融機関CD1 = params.金融機関CD1 || 0;
                        params.金融機関支店CD1 = params.金融機関支店CD1 || 0;
                        params.口座種別1 = params.口座種別1 || 0;
                        params.金融機関CD2 = params.金融機関CD2 || 0;
                        params.金融機関支店CD2 = params.金融機関支店CD2 || 0;
                        params.口座種別2 = params.口座種別2 || 0;
                        params.工場ＣＤ = params.工場ＣＤ || 0;

                        //金融機関CD、金融機関支店CD: nullの0置換
                        params.金融機関CD1 = params.金融機関CD1 || 0;
                        params.金融機関CD2 = params.金融機関CD2 || 0;
                        params.金融機関支店CD1 = params.金融機関支店CD1 || 0;
                        params.金融機関支店CD2 = params.金融機関支店CD2 || 0;

                        params.修正担当者CD = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");

                        var Message = {
                            "department_code": vue.viewModel.部署CD,
                            "course_code": null,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;

                        //ロゴ/印鑑
                        if (!!vue.IsExistImage && vue.ImageFile != vue.viewModel.部署CD + ".png") {
                            params.ImageFile = vue.ImageFile;
                        }

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04071/Save", params)
                            .then(res => {
                                if(!!res.data.duplicate){
                                    progressDlg.dialog("close");
                                    var duplicate = res.data.duplicate;
                                    $.dialogInfo({
                                        title: "登録失敗",
                                        contents: "部署CD:" + duplicate + "が重複しています。",
                                    });
                                    vue.viewModel.部署CD = "";
                                }else{
                                    DAI04070.conditionChanged();
                                    //画面を閉じる
                                    $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                }
                            })
                            .catch(err => {
                                console.log(err);
                            }
                        );
                        console.log("登録", params);
                        $(this).dialog("close");
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

            vue.ImageFile = vue.viewModel.部署CD + ".png";

            axios.get(vue.ImagePath)
                .then(res => vue.IsExistImage = true)
                .catch(err => vue.IsExistImage = false)
                ;
        },
        onBushoCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByBushoCd();
        },
        searchByBushoCd: function() {
            var vue = this;
            var cd = vue.viewModel.部署CD;
            if (!cd) return;

            var params = {BushoCd: cd};
            params.noCache = true;

            axios.post("/DAI04071/GetBushoListForDetail", params)
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

                                        vue.viewModel.IsNew = false;
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        vue.viewModel.部署CD = "";
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
        onBankChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        onProductChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        BankAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "銀行名カナ"];

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
                    ret.label = v.Cd + " : " + v.CdNm + "【" + v.銀行名カナ + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            return list;
        },
        BankBranch1ParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0;
            return ret;
        },
        BankBranch2ParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0;
            return ret;
        },
        onBankBranchChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
        },
        BankBranchAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "支店名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm+ "【" + v.支店名カナ + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            return list;
        },
        ProductAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "商品区分", "各種名称", "売価単価"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "【 " + v.商品区分 + "：" + v.各種名称 + "】"+ "￥" + v.売価単価 ;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            return list;
        },
        clearDetail: function(){
            var vue = this;

            $(vue.$el).find(".has-error").removeClass("has-error");

            _.keys(DAI04071.viewModel).forEach(k => DAI04071.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);

            //PopupSelect
            $(".select-name").val("");
        },
        uploadLogoCallback: function(res) {
            var vue = this;

            if (!!res.result) {
                vue.ImageFile = res.file;
                vue.IsExistImage = true;
            } else {
                $.dialogErr({
                    title: "アップロード失敗",
                    contents: res.message,
                });
                vue.IsExistImage = false;
            }
        },
    }
}
</script>
