<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-3">
                <label>クレームID</label>
                <input type="text" id="ClaimID" class="form-control" v-model="viewModel.クレームID" readOnly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>受付日時</label>
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                />
                <DatePickerWrapper
                    id="TargetTime"
                    ref="DatePicker_TargetTime"
                    format="HH時mm分"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetTime"
                    :editable=true
                    customStyle="width: 80px;"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>得意先</label>
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ CustomerCd: null, KeyWord: null }"
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
                    :inputWidth=150
                    :nameWidth=400
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">得意先担当者</label>
                <!-- TODO: 得意先選択時に得意先マスタに追加した得意先担当者から引っ張ってくる？ -->
                <input type="text" id="CustomerTantoNm" class="form-control"
                    v-model="viewModel.顧客担当者名"
                    maxlength=30
                    v-maxBytes=30
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>商品</label>
                <PopupSelect
                    id="ProductSelect"
                    ref="PopupSelect_Product"
                    :vmodel=viewModel
                    bind="商品コード"
                    buddy="商品名"
                    dataUrl="/Utilities/GetProductListForSelect"
                    :params="{ ProductCd: null, KeyWord: ProductKeyWord }"
                    :isPreload=true
                    title="商品名一覧"
                    labelCd="商品コード"
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
                    :AutoCompleteFunc=ProductAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>クレーム区分</label>
                <VueSelect
                    id="ClaimKbn"
                    :vmodel=viewModel
                    bind="クレーム区分コード"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 47 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>クレーム内容</label>
                <textarea class="form-control ml-1 mr-1 p-1" type="text" v-model=viewModel.クレーム内容 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>受付担当者</label>
                <PopupSelect
                    id="UketukeTanto"
                    ref="PopupSelect_UketukeTanto"
                    :vmodel=viewModel
                    bind="受付担当者コード"
                    buddy="受付担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="受付担当者一覧"
                    labelCd="受付担当者コード"
                    labelCdNm="受付担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=UketukeTantoAutoCompleteFunc
                />
            </div>
            <div class="col-md-6">
                <label>受付方法</label>
                <VueSelect
                    id="UketukeKind"
                    :vmodel=viewModel
                    bind="受付方法"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 48 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>クレーム処理日</label>
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TargetDate"
                    :editable=true
                />
            </div>
            <div class="col-md-6">
                <label>処理担当者</label>
                <PopupSelect
                    id="ShoriTanto"
                    ref="PopupSelect_ShoriTanto"
                    :vmodel=viewModel
                    bind="クレーム処理者コード"
                    buddy="処理担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="処理担当者一覧"
                    labelCd="処理担当者コード"
                    labelCdNm="処理担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ShoriTantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label>クレーム処理品</label>
                <input class="form-control" type="text" v-model=viewModel.クレーム処理品 v-maxBytes="100" />
            </div>
            <div class="col-md-4">
                <label>処理費用</label>
                <!-- TODO: カンマ区切り整数 -->
                <input class="form-control ml-1 mr-1 p-1" type="text" v-model=viewModel.クレーム処理費用 v-maxBytes="100" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>客先反応</label>
                <textarea class="form-control ml-1 mr-1 p-1" type="text" v-model=viewModel.客先反応 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>原因部署</label>
                <input class="form-control" type="text" v-model=viewModel.部門名 v-maxBytes="100" />
            </div>
            <div class="col-md-6">
                <label>原因部署担当</label>
                <VueSelect
                    id="UketukeKind"
                    :vmodel=viewModel
                    bind="原因部署担当コード"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 50 }"
                    :withCode=true
                    :hasNull=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>原因</label>
                <textarea class="form-control ml-1 mr-1 p-1" type="text" v-model=viewModel.原因 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>原因記入者</label>
                <PopupSelect
                    id="GeninTanto"
                    ref="PopupSelect_GeninTanto"
                    :vmodel=viewModel
                    bind="原因入力担当者コード"
                    buddy="原因入力担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="原因入力担当者一覧"
                    labelCd="原因入力担当者コード"
                    labelCdNm="原因入力担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=GeninTantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>対策</label>
                <textarea class="form-control ml-1 mr-1 p-1" type="text" v-model=viewModel.対策 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>対策記入者</label>
                <PopupSelect
                    id="TaisakuTanto"
                    ref="PopupSelect_TaisakuTanto"
                    :vmodel=viewModel
                    bind="対策入力担当者コード"
                    buddy="対策入力担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="対策入力担当者一覧"
                    labelCd="対策入力担当者コード"
                    labelCdNm="対策入力担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TaisakuTantoAutoCompleteFunc
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
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
<style>
form[pgid="DAI05140"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI05140"] .multiselect.BushoCd .multiselect__content-wrapper {
    height: 200px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05140",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        searchParams: function() {
            var vue = this;
            var mt = moment(vue.viewModel.対象日付, "YYYY年MM月DD日");
            return {
                TargetDate: mt.isValid() ? mt.format("YYYYMMDD") : null,
                HolidayName: vue.viewModel.名称,
                BushoCdArray: vue.viewModel.BushoArray.map(v => v.code),
            };
        },
        saveParams: function() {
            var vue = this;
            return {
                対象日付: moment(vue.viewModel.対象日付, "YYYY年MM月DD日").format("YYYYMMDD"),
                名称: vue.viewModel.名称,
                対象部署ＣＤ: vue.viewModel.BushoArray.map(v => v.code).join(","),
            };
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: !!vue.params && !!vue.params.isChild ? "クレーム入力" : "随時処理 > クレーム入力",
            noViewModel: true,
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
            data.viewModel.BushoArray = [];
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "false" },
                { visible: "true", value: "登録", id: "DAI05140Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.searchParams.TargetDate || !vue.searchParams.HolidayName){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.searchParams.TargetDate ? "対象日付が入力されていません。" : "",
                                    !vue.searchParams.HolidayName ? "名称が入力されていません。<br/>" : "",
                                ]
                            })
                            if(!vue.searchParams.TargetDate){
                                $(vue.$el).find("#TargetDate").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TargetDate").removeClass("has-error");
                            }
                            if(!vue.searchParams.HolidayName){
                                $(vue.$el).find("#HolidayName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#HolidayName").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.saveParams);
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        axios.post("/DAI05140/Save", params)
                        .then(res => {
                            console.log("4061 save", res);
                            DAI04160.conditionChanged();
                            $(vue.$el).closest(".ui-dialog-content").dialog("close");
                        })
                        .catch(err => {
                            console.log(err);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "祝日マスタの登録に失敗しました"
                            })
                        });
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05140Grid1_Print", disabled: false, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "false" },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            vue.viewModel.TargetDate = vue.params.TargetDate || moment().format("YYYY年MM月DD日");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        print: function() {
            //TODO:
        },
    }
}
</script>
