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
                    ref="VueSelectBusho"
                    :withCode=true
                    buddy="BushoNm"
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
                <VueSelect v-else
                    id="Busho"
                    ref="VueSelectBusho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    buddy="BushoNm"
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
                    :params="{ CustomerCd: null, KeyWord: null, UserBushoCd: getLoginInfo().bushoCd }"
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
                    dataUrl="/Utilities/GetProductListForSelectNew"
                    :params='{ CustomerCd: viewModel.顧客コード, TargetDate: searchParams.ClaimDate}'
                    :isPreload=true
                    title="商品名一覧"
                    labelCd="商品コード"
                    labelCdNm="商品名"
                    :showColumns='[
                        { title: "商品区分", dataIndx: "商品区分", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 2 },
                        { title: "売価単価", dataIndx: "売価単価", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 3 },
                        { title: "得意先単価", dataIndx: "得意先単価", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 4 },
                    ]'
                    :popupWidth=750
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}]"
                    :inputWidth=100
                    :nameWidth=300
                    :ProductSelect=onProductChanged
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
                    buddy="クレーム区分名"
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
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=false
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
                    ref="VueSelectUketukeKind"
                    :vmodel=viewModel
                    bind="受付方法"
                    buddy="受付方法名"
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
                    id="ProcDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="ProcDate"
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
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=false
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>クレーム処理品</label>
                <input class="form-control" style="width: 300px;" type="text" v-model=viewModel.クレーム処理品 v-maxBytes="100" />
            </div>
            <div class="col-md-3">
                <label>処理費用</label>
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
                    buddy="原因部署担当"
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
                    :reuse=false
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
                    :reuse=false
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
                <label>ステータス</label>
                <VueCheck
                    id="StatusContinue"
                    ref="VueCheck_StatusContinue"
                    :vmodel=viewModel
                    bind="その後客先失客"
                    buddy="ステータス"
                    checkedCode="0"
                    customContainerStyle="border: none; margin-right: 10px;"
                    :list="[
                        {code: '2', name: '継続', label: '継続'},
                        {code: '0', name: '継続', label: '継続'},
                        {code: '1', name: '継続', label: '継続'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
               <VueCheck
                    id="StatusLost"
                    ref="VueCheck_StatusLost"
                    :vmodel=viewModel
                    bind="その後客先失客"
                    buddy="ステータス"
                    checkedCode="1"
                    customContainerStyle="border: none; margin-right: 10px;"
                    :list="[
                        {code: '2', name: '失客', label: '失客'},
                        {code: '0', name: '失客', label: '失客'},
                        {code: '1', name: '失客', label: '失客'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
                <label style="width: unset; text-align: right">失客日</label>
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
                受付方法名: null,
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
            data.viewModel.BushoNm = vue.params.管轄部門名;

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
            $(vue.$el).parents("div.body-content").css("max-height", $(vue.$el).parents("div.body-content").css("height"));

            if (!vue.params || !vue.params.IsNew) {
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onBushoChanged: function(code, entity) {
            var vue = this;
            if (!!entity) {
                vue.viewModel.BushoNm = entity.CdNm;
                vue.setCourse();
            }
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

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
            var vue = this;

            if (!!vue.viewModel.受付方法) {
                var entity = vue.$refs.VueSelectUketukeKind.entities.find(v => v.code == vue.viewModel.受付方法);

                if (!entity) return
                vue.viewModel.受付方法名 = entity.name;
            }

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.DAI05140 div.title {
                    display: block;
                    text-align: center;
                }
                div.DAI05140 div.header :not(.title) * {
                    font-family: "MS UI Gothic";
                    font-size: 14px;
                }
                div.DAI05140 div.title > h3, div.title > h5 {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
                div.DAI05140 table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border-collapse:collapse;
                }
                div.DAI05140 table tr {
                    border-top: solid 1px black;
                }
                div.DAI05140 table tr:last-child {
                    border-bottom: solid 1px black;
                }
                div.DAI05140 table th, div.DAI05140 table td {
                    font-family: "MS UI Gothic";
                    font-size: 14px;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    border-left: solid 1px black;
                }
                div.DAI05140 table tr th:last-child, div.DAI05140 table tr td:last-child {
                    border-right: solid 1px black;
                }
                div.DAI05140 table.claim tr {
                    height: 30px;
                }
                div.DAI05140 table.claim td {
                    vertical-align: top;
                    padding-top: 8px;
                }
                div.DAI05140 table.claim td[rowspan] {
                    border-bottom: solid 1px black;
                    display: table-cell;
                    vertical-align: middle;
                    text-align: center;
                    padding: 0px 3px;
                }
            `;

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            `
                                <div class="DAI05140">
                                    <div class="header" style="margin-bottom: 10px;">
                                        <div class="title" style="float: left; width: 100%">
                                            <h3>クレーム報告書</h3>
                                        </div>
                                        <div style="float: left; width: 100%;">
                                            <div style="float: left; width: 74%;">&nbsp</div>
                                            <div style="float: left; width: 8%; text-align: center;">作成日</div>
                                            <div style="float: left; width: 18%; text-align: right;">${moment().format("YYYY/MM/DD HH:mm")}</div>

                                            <div style="float: left; width: 100%; display: flex; align-items: flex-end; margin-bottom: 10px;">
                                                <div style="float: left; width: 11%; text-align: left;">クレームID</div>
                                                <div style="float: left; width: 7%; text-align: left;">${_.padStart(vue.viewModel.クレームID, 6, "0")}</div>
                                                <div style="float: right; width: 82%; display: flex; justify-content: flex-end;">
                                                    <table class="stamp" style="width: 480px; margin-top: 10px;">
                                                        <thead>
                                                            <tr>
                                                                <th align="center">会長</th>
                                                                <th align="center">社長</th>
                                                                <th align="center"></th>
                                                                <th align="center">室長</th>
                                                                <th align="center">総務</th>
                                                                <th align="center">支社長</th>
                                                                <th align="center">製造長</th>
                                                                <th align="center">配送長</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr style="height: 60px;">
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                                <td style="width: 60px;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="claim" style="width: 100%">
                                        <colgroup>
                                                <col style="width: 20%;"></col>
                                                <col style="width: calc(100% - 65% - 60px);"></col>
                                                <col style="width: 15%;"></col>
                                                <col style="width: 15%;"></col>
                                                <col style="width: 15%;"></col>
                                                <col style="width: 60px;"></col>
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td>受付日時</td>
                                                <td colspan=5>
                                                    ${moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日").format("YYYY年MM月DD日(dd)") + vue.viewModel.ClaimTime}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>部門</td>
                                                <td>${vue.viewModel.BushoNm}</td>
                                                <td>配送コース</td>
                                                <td colspan=3>${vue.viewModel.CourseNm || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>顧客</td>
                                                <td colspan=2>${vue.viewModel.得意先名 || ""}</td>
                                                <td>平均食数</td>
                                                <td colspan=2 style="text-align: right;">${vue.viewModel.平均食数}食</td>
                                            </tr>
                                            <tr>
                                                <td>顧客担当者</td>
                                                <td colspan=5>${vue.viewModel.顧客担当者名 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>商品</td>
                                                <td colspan=2>${vue.viewModel.商品名 || ""}</td>
                                                <td>単価</td>
                                                <td colspan=2 style="text-align: right;">${vue.viewModel.単価}円</td>
                                            </tr>
                                            <tr>
                                                <td>クレーム種類</td>
                                                <td colspan=5>${vue.viewModel.クレーム区分名 || ""}</td>
                                            </tr>
                                            <tr style="height: 100px;">
                                                <td>クレーム内容</td>
                                                <td colspan=5>${vue.viewModel.クレーム内容 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>受付担当</td>
                                                <td colspan=2>${vue.viewModel.受付担当者名 || ""}</td>
                                                <td>受付方法</td>
                                                <td colspan=2>${vue.viewModel.受付方法名 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>クレーム処理日</td>
                                                <td colspan=5>${moment(vue.viewModel.ProcDate, "YYYY年MM月DD日").format("YYYY年MM月DD日(dd)")}</td>
                                            </tr>
                                            <tr>
                                                <td>クレーム処理者</td>
                                                <td colspan=5>${vue.viewModel.処理担当者名 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>クレーム処理品</td>
                                                <td colspan=2>${vue.viewModel.クレーム処理品 || ""}</td>
                                                <td>処理費用</td>
                                                <td colspan=2 style="text-align: right;">${vue.viewModel.クレーム処理費用}円</td>
                                            </tr>
                                            <tr style="height: 100px;">
                                                <td>客先応答</td>
                                                <td colspan=5>${vue.viewModel.客先反応 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>原因部署</td>
                                                <td colspan=2>${vue.viewModel.部門名 || ""}</td>
                                                <td>原因記入者</td>
                                                <td colspan=2>${vue.viewModel.原因入力担当者名 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>原因部署担当</td>
                                                <td colspan=5>${vue.viewModel.原因部署担当 || ""}</td>
                                            </tr>
                                            <tr style="height: 100px;">
                                                <td>原因</td>
                                                <td colspan=5>${vue.viewModel.原因 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>対策記入者</td>
                                                <td colspan=5>${vue.viewModel.対策入力担当者名 || ""}</td>
                                            </tr>
                                            <tr style="height: 100px;">
                                                <td>対策</td>
                                                <td colspan=5>${vue.viewModel.対策 || ""}</td>
                                            </tr>
                                            <tr>
                                                <td>継続または失客</td>
                                                <td colspan=3>${vue.viewModel.その後客先失客 == "2" ? "" : vue.viewModel.ステータス}</td>
                                                <td rowspan=2>処理済者印</td>
                                                <td rowspan=2></td>
                                            </tr>
                                            <tr>
                                                <td>失客日</td>
                                                <td colspan=3>${moment(vue.viewModel.LostDate, "YYYY年MM月DD日").format("YYYY年MM月DD日(dd)")}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="haccp" style="width: 100%; margin-top: 10px;">
                                        <colgroup>
                                            <col style="width: 25%;">
                                            <col style="width: 25%;">
                                            <col style="width: 25%;">
                                            <col style="width: 25%;">
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td>制定日:${vue.viewModel.HACCP制定日}</td>
                                                <td>改定日:${vue.viewModel.HACCP改定日}</td>
                                                <td>改定番号:${vue.viewModel.HACCP改定番号}</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            `
                        )
                )
                .prop("outerHTML")
                ;

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4; margin: 20px 50px; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
