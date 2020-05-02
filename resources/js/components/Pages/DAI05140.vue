<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-3">
                <label>クレームID</label>
                <input type="text" id="ClaimID" class="form-control" style="width: 100px;" v-model="viewModel.クレームID" readOnly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>受付日時</label>
                <DatePickerWrapper
                    id="ClaimDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="ClaimDate"
                    :editable=true
                    :onChangedFunc=onClaimDateChanged
                />
                <div class="ml-1 mr-1"></div>
                <DatePickerWrapper
                    id="ClaimTime"
                    ref="DatePicker_ClaimTime"
                    format="HH時mm分"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="ClaimTime"
                    :editable=true
                    customStyle="width: 85px;"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label>部署</label>
                <VueSelectBusho v-if="params.IsNew"
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
                <VueSelect v-else
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-1">
                <label style="max-width: unset; width: 100%; text-align: center;">コース</label>
            </div>
            <div class="col-md-4">
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly tabindex="-1">
                <input class="form-control ml-1" style="width: 300px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
                <label>得意先</label>
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="顧客コード"
                    buddy="得意先名"
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
                    :inputWidth=100
                    :nameWidth=300
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
            <div class="col-md-1">
                <label style="max-width: unset; width: 100%; text-align: center;">平均食数</label>
            </div>
            <div class="col-md-1">
                <currency-input class="form-control text-right" style="width: 100px;" type="text" v-model="viewModel.平均食数" :precision=1 readonly tabindex="-1" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <label class="">得意先担当者</label>
                <!-- TODO: 得意先選択時に得意先マスタに追加した得意先担当者から引っ張ってくる？ -->
                <input type="text" id="CustomerTantoNm" class="form-control" style="width: 300px;"
                    v-model="viewModel.顧客担当者名"
                    maxlength=30
                    v-maxBytes=30
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-7">
                <label>商品</label>
                <PopupSelect
                    id="ProductSelect"
                    ref="PopupSelect_Product"
                    :vmodel=viewModel
                    bind="商品コード"
                    :buddies='{ "商品名": "CdNm" }'
                    dataUrl="/Utilities/GetProductListForSelect"
                    :params='{ CustomerCd: viewModel.顧客コード, TargetDate: searchParams.ClaimDate, ExceptNull: true }'
                    :isPreload=true
                    title="商品名一覧"
                    labelCd="商品コード"
                    labelCdNm="商品名"
                    :showColumns='[
                        { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                        { title: "得意先単価", dataIndx: "得意先単価", dataType: "integer", width: 80, maxWidth: 80, minWidth: 80, colIndx: 3 },
                    ]'
                    :popupWidth=600
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}]"
                    :inputWidth=100
                    :nameWidth=300
                    :onChangeFunc=onProductChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=ProductAutoCompleteFunc
                />
            </div>
            <div class="col-md-1">
                <label style="max-width: unset; width: 100%; text-align: center;">単価</label>
            </div>
            <div class="col-md-1">
                <currency-input class="form-control text-right" style="width: 100px;" type="text" v-model=viewModel.単価 readonly tabindex="-1" />
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
                <textarea class="form-control p-1 memo" type="text" v-model=viewModel.クレーム内容 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>受付担当者</label>
                <PopupSelect
                    id="UketukeTanto"
                    ref="PopupSelect_UketukeTanto"
                    :vmodel=viewModel
                    bind="受付担当者コード"
                    buddy="受付担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
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
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
            <div class="col-md-3">
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
            <div class="col-md-5">
                <label>クレーム処理日</label>
                <DatePickerWrapper
                    id="ProcDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="ProcDate"
                    :editable=true
                />
            </div>
            <div class="col-md-5">
                <label>処理担当者</label>
                <PopupSelect
                    id="ShoriTanto"
                    ref="PopupSelect_ShoriTanto"
                    :vmodel=viewModel
                    bind="クレーム処理者コード"
                    buddy="処理担当者名"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
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
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>クレーム処理品</label>
                <input class="form-control" style="width: 300px;" type="text" v-model=viewModel.クレーム処理品 v-maxBytes="100" />
            </div>
            <div class="col-md-3">
                <label>処理費用</label>
                <!-- TODO: カンマ区切り整数 -->
                <currency-input class="form-control text-right p-1" style="width: 100px;" type="text" v-model=viewModel.クレーム処理費用 v-maxBytes="100" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>客先反応</label>
                <textarea class="form-control p-1 memo" type="text" v-model=viewModel.客先反応 v-maxBytes="400">
                </textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>原因部署</label>
                <input class="form-control" style="width: 300px;" type="text" v-model=viewModel.部門名 v-maxBytes="100" />
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
                <textarea class="form-control p-1 memo" type="text" v-model=viewModel.原因 v-maxBytes="400">
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
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
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
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>対策</label>
                <textarea class="form-control p-1 memo" type="text" v-model=viewModel.対策 v-maxBytes="400">
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
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
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
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>ステータス</label>
               <VueCheck
                    id="StatusContinue"
                    ref="VueCheck_StatusContinue"
                    :vmodel=viewModel
                    bind="その後客先失客"
                    checkedCode="0"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '2', name: '継続', label: '継続'},
                        {code: '0', name: '継続', label: '継続'},
                        {code: '1', name: '継続', label: '継続'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
            </div>
            <div class="col-md-6">
               <VueCheck
                    id="StatusLost"
                    ref="VueCheck_StatusLost"
                    :vmodel=viewModel
                    bind="その後客先失客"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '2', name: '失客', label: '失客'},
                        {code: '0', name: '失客', label: '失客'},
                        {code: '1', name: '失客', label: '失客'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
                <label>失客日</label>
                <DatePickerWrapper
                    id="LostDate"
                    ref="DatePicker_LostDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="LostDate"
                    :editable=LostDateEditable
                    :onChangedFunc=onLostDateChanged
                />
                <input class="form-control ml-3" style="width: 50px;" type="text" :value=viewModel.失客日数 readonly tabindex="-1">
                日後
            </div>
        </div>
        <div class="row" style="height: 30px;"></div>
    </form>
</template>

<style scoped>
label {
    width: 120px;
    min-width: unset;
}
.row {
    margin-bottom: 2px;
}
.memo{
    width: 800px;
    height: 60px;
    max-height: unset;
    line-height: 16px;
    resize: none;
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
        searchParams: function() {
            var vue = this;
            var mc = moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日");
            var mp = moment(vue.viewModel.ProcDate, "YYYY年MM月DD日");
            var ml = moment(vue.viewModel.LostDate, "YYYY年MM月DD日");

            return {
                ClaimDate: (mc.isValid() ? mc : moment()).format("YYYYMMDD"),
                ProcDate: (mp.isValid() ? mp : moment()).format("YYYYMMDD"),
                LostDate: (ml.isValid() ? ml : moment()).format("YYYYMMDD"),
            };
        },
        saveParams: function() {
            var vue = this;
            var mc = moment(vue.viewModel.ClaimDate + " " + vue.viewModel.ClaimTime, "YYYY年MM月DD日 HH時mm分");
            var mp = moment(vue.viewModel.ProcDate, "YYYY年MM月DD日");
            var ml = moment(vue.viewModel.LostDate, "YYYY年MM月DD日");

            return {
                クレームID: vue.viewModel.クレームID,
                受付日時: mc.format("YYYY-MM-DD HH:mm:ss"),
                管轄部門コード: vue.viewModel.BushoCd,
                クレーム区分コード: vue.viewModel.クレーム区分コード,
                顧客コード: vue.viewModel.顧客コード,
                平均食数: vue.viewModel.平均食数,
                顧客担当者名: vue.viewModel.顧客担当者名,
                商品コード: vue.viewModel.商品コード,
                単価: vue.viewModel.単価,
                クレーム内容: vue.viewModel.クレーム内容,
                受付担当者コード: vue.viewModel.受付担当者コード,
                受付方法: vue.viewModel.受付方法,
                クレーム処理日: mp.format("YYYY-MM-DD"),
                クレーム処理者コード: vue.viewModel.クレーム処理者コード,
                クレーム処理品: vue.viewModel.クレーム処理品,
                クレーム処理費用: vue.viewModel.クレーム処理費用,
                客先反応: vue.viewModel.客先反応,
                部門コード: vue.viewModel.部門コード,
                部門名: vue.viewModel.部門名,
                原因部署担当コード: vue.viewModel.原因部署担当コード,
                原因: vue.viewModel.原因,
                原因入力担当者コード: vue.viewModel.原因入力担当者コード,
                原因入力担当者名: vue.viewModel.原因入力担当者名,
                対策: vue.viewModel.対策,
                対策入力担当者コード: vue.viewModel.対策入力担当者コード,
                対策入力担当者名: vue.viewModel.対策入力担当者名,
                その後客先失客: vue.viewModel.その後客先失客,
                失客日: ml.format("YYYY-MM-DD"),
                失客日数: vue.viewModel.失客日数,
                未使用フラグ: vue.viewModel.未使用フラグ,
                修正担当者ＣＤ: vue.viewModel.修正担当者ＣＤ,
                修正日: vue.viewModel.修正日,
                管轄部門名: vue.viewModel.管轄部門名,
                得意先名: vue.viewModel.得意先名,
                クレーム区分名: vue.viewModel.クレーム区分名,
                原因部署名: vue.viewModel.原因部署名,
                原因部署担当: vue.viewModel.原因部署担当,
                ステータス: vue.viewModel.ステータス,
            };
        },
        LostDateEditable: function() {
            var vue = this;
            return vue.viewModel.その後客先失客 == "1";
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: !!vue.params && !!vue.params.IsChild ? "クレーム入力" : "随時処理 > クレーム入力",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                ClaimDate: moment().format("YYYY年MM月DD日"),
                ClaimTime: moment().format("HH時mm分"),
                ProcDate: moment().format("YYYY年MM月DD日"),
                LostDate: moment().format("YYYY年MM月DD日"),
                クレームID: null,
                受付日時: null,
                管轄部門コード: null,
                クレーム区分コード: null,
                顧客コード: null,
                平均食数: 0.0,
                顧客担当者名: null,
                商品コード: null,
                単価: 0,
                クレーム内容: null,
                受付担当者コード: null,
                受付方法: null,
                クレーム処理日: null,
                クレーム処理者コード: null,
                クレーム処理品: null,
                クレーム処理費用: 0,
                客先反応: null,
                部門コード: null,
                部門名: null,
                原因部署担当コード: null,
                原因: null,
                原因入力担当者コード: null,
                原因入力担当者名: null,
                対策: null,
                対策入力担当者コード: null,
                対策入力担当者名: null,
                その後客先失客: "2",
                失客日: null,
                失客日数: null,
                未使用フラグ: null,
                修正担当者ＣＤ: null,
                修正日: null,
                管轄部門名: null,
                得意先名: null,
                クレーム区分名: null,
                原因部署名: null,
                原因部署担当: null,
                ステータス: null,
            },
        });

        if (!!vue.params && !vue.params.IsNew) {
            data.viewModel = $.extend(true, {}, data.viewModel, vue.params);

            data.viewModel.BushoCd = vue.params.管轄部門コード;

            var mt;
            mt = moment(data.viewModel.受付日時);
            data.viewModel.ClaimDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");
            data.viewModel.ClaimTime = (mt.isValid() ? mt : moment()).format("HH時mm分");

            mt = moment(data.viewModel.クレーム処理日);
            data.viewModel.ProcDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");

            mt = moment(data.viewModel.失客日);
            data.viewModel.LostDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");

            data.viewModel.平均食数 = (data.viewModel.平均食数 || 0.0) * 1;
            data.viewModel.単価 = (data.viewModel.単価 || 0) * 1;
            data.viewModel.クレーム処理費用 = (data.viewModel.クレーム処理費用 || 0) * 1;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "false" },
                { visible: "true", value: "登録", id: "DAI05140Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.saveParams.受付日時 || !vue.saveParams.顧客コード
                        || !vue.saveParams.商品コード || !vue.saveParams.クレーム区分コード
                        || !vue.saveParams.受付担当者コード || !vue.saveParams.受付方法){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.saveParams.受付日時 ? "受付日時が入力されていません。" : "",
                                    !vue.saveParams.顧客コード ? "得意先が入力されていません。<br/>" : "",
                                    !vue.saveParams.商品コード ? "商品ＣＤが入力されていません。<br/>" : "",
                                    !vue.saveParams.クレーム区分コード ? "クレーム区分が入力されていません。<br/>" : "",
                                    !vue.saveParams.受付担当者コード ? "受付担当者が入力されていません。<br/>" : "",
                                    !vue.saveParams.受付方法 ? "受付方法が入力されていません。<br/>" : "",
                                ]
                            })

                            $(vue.$el).find("#ClaimDate")[!vue.saveParams.ClaimDate ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#ClaimTime")[!vue.saveParams.ClaimTime ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#CustomerSelect")[vue.saveParams.顧客コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#ProductSelect")[vue.saveParams.商品コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find(".ClaimKbn")[vue.saveParams.クレーム区分コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#UketukeTanto")[vue.saveParams.受付担当者コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find(".UketukeKind")[vue.saveParams.受付方法 == null ? "addClass" : "removeClass"]("has-error");

                            return;
                        }

                        var params = _.cloneDeep(vue.saveParams);
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        axios.post("/DAI05140/Save", params)
                        .then(res => {
                            DAI05150.conditionChanged();
                            $(vue.$el).closest(".ui-dialog-content").dialog("close");
                        })
                        .catch(err => {
                            console.log(err);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "クレームの登録に失敗しました"
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

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onBushoChanged: function(code, entity) {
            var vue = this;
            vue.setCourse();
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;
            if (!!entity) {
                vue.viewModel.BushoCd = entity.部署CD;
            }
            var params = {
                CustomerCd: vue.viewModel.顧客コード,
                noCache: true,
            };

            if (_.values(params).some(v => !v)) {
                return;
            }

            axios.post("/DAI05140/GetAverage", params)
            .then(res => {
                if (!!res.data) {
                    vue.viewModel.平均食数 = res.data.平均食数 * 1;
                } else {
                    vue.viewModel.平均食数 = 0.0;
                }
            });
            vue.setCourse();
        },
        onClaimDateChanged: function() {
            var vue = this;
            vue.viewModel.失客日数 = moment(vue.viewModel.LostDate, "YYYY年MM月DD日")
                .diff(moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日"), "days");
            vue.setCourse();
        },
        setCourse: function() {
            var vue = this;

            var params = {
                BushoCd: vue.viewModel.BushoCd,
                CustomerCd: vue.viewModel.顧客コード,
                TargetDate: moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                noCache: true,
            };

            if (_.values(params).some(v => !v)) {
                return;
            }

            axios.post("/Utilities/GetCourseListByCustomer", params)
            .then(res => {
                if (!!res.data.length) {
                    vue.viewModel.CourseCd = res.data[0].コースCD;
                    vue.viewModel.CourseNm = res.data[0].コース名;
                } else {
                    vue.viewModel.CourseCd = null;
                    vue.viewModel.CourseNm = null;
                }
            });
        },
        onProductChanged: function(code, entity) {
            var vue = this;
            if (!!entity) {
                vue.viewModel.単価 = (entity.得意先単価 || entity.売価単価) * 1;
            }
        },
        onStatusChanged: function(code, entity) {
            var vue = this;
        },
        onLostDateChanged: function() {
            var vue = this;
            vue.viewModel.失客日数 = moment(vue.viewModel.LostDate, "YYYY年MM月DD日")
                .diff(moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日"), "days");
        },
        CustomerAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.replace(/-/g, "").includes(k.replace(/-/g, "")) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        ProductAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

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
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "担当者名カナ"];

            if (input == comp.selectValue && comp.isUnique) {
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
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        print: function() {
            //TODO:
        },
    }
}
</script>
