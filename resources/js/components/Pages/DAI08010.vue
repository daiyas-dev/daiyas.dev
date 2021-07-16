<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
        <div class="row">
            <div class="col-md-1">
                <label>配達日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日(dd)"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    customStyle="width: 165px;"
                    :editable=true
                    :onChangedFunc=onDeliveryDateChanged
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">時間</label>
                <DatePickerWrapper
                    id="DeliveryTime"
                    ref="DatePicker_DeliveryTime"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryTime"
                    :editable=true
                    customStyle="width: 85px;"
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">持出時間</label>
                <DatePickerWrapper
                    id="TakeoutTime"
                    ref="DatePicker_TakeoutTime"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="TakeoutTime"
                    :editable=true
                    customStyle="width: 85px;"
                />
            </div>
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :onChangedFunc=onBushoChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>受注No.</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="OrderNo"
                    ref="PopupSelect_OrderNo"
                    :vmodel=viewModel
                    bind="OrderNo"
                    dataUrl="/DAI08010/GetOrderNoList"
                    :params="{ TargetDate: searchParams.DeliveryDate, BushoCd: searchParams.BushoCd }"
                    :isPreload=true
                    :dataListReset=true
                    :ParamsChangedCheckFunc=OrderNoParamsChangedCheckFunc
                    title="受注No.一覧"
                    labelCd="受注No."
                    :showColumns='[
                        { title: "配達先１", dataIndx: "配達先１", dataType: "string", width: 200, maxWidth: 200, minWidth: 200, },
                        { title: "配達先２", dataIndx: "配達先２", dataType: "string", width: 200, maxWidth: 200, minWidth: 200, },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=false
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :onAfterChangedFunc=onOrderNoChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=OrderNoAutoCompleteFunc
                    :enablePrevNext=true
                />
                <label style="width: 120px; max-width: unset; text-align: center;">注文日付</label>
                <input class="form-control p-0 text-center" style="width: 130px;" type="text" :value=viewModel.OrderDate readonly tabindex="-1">
                <label class="text-center">時間</label>
                <input class="form-control p-0 text-center" style="width: 80px;" type="text" :value=viewModel.OrderTime readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>顧客</label>
            </div>
            <div class="col-md-5">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=CustomerInfo
                    bind="得意先ＣＤ"
                    buddy="得意先名"
                    dataUrl="/DAI08010/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd }"
                    :isPreload=true
                    title="顧客一覧"
                    labelCd="顧客CD"
                    labelCdNm="顧客名"
                    :showColumns='[
                        { title: "部署名", dataIndx: "部署名", dataType: "string", width: 120, maxWidth: 120, minWidth: 120, colIndx: 0 },
                    ]'
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :isNameEditable=true
                    :onNameChangedFunc=onCustomerNameChanged
                    :dataListReset=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=150
                    :nameWidth=400
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-2">
                <label class="w-50 text-center">売掛現金区分</label>
                <VueSelect
                    id="UriGenKbn"
                    :vmodel=CustomerInfo
                    bind="売掛現金区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 1 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">顧客名ｶﾅ</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control CustomerNameKana" v-model="CustomerInfo.得意先名カナ"
                    @input="preventFnKeys"
                    @keydown="preventFnKeys"
                >
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL1</label>
                <input class="form-control p-2 TelNo1" style="width: 155px;" type="text" v-model=CustomerInfo.電話番号１>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">TEL2</label>
                <input class="form-control p-2 TelNo2" style="width: 155px;" type="text" v-model=CustomerInfo.電話番号２>
            </div>
            <div class="col-md-2">
                <label class="w-100 text-center">Fax</label>
                <input class="form-control p-2 Fax" style="width: 155px;" type="text" v-model=CustomerInfo.ＦＡＸ１>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>郵便番号</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-2" style="width: 90px;" type="text" v-model=CustomerInfo.郵便番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" v-model=CustomerInfo.住所１>
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">配達先</label>
            </div>
            <div class="col-md-5">
                <input class="form-control" type="text" v-model=OrderInfo.配達先１>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" v-model=CustomerInfo.住所２>
            </div>
            <div class="col-md-5 offset-md-1">
                <input class="form-control" type="text" v-model=OrderInfo.配達先２>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>エリア</label>
            </div>
            <div class="col-md-3">
                <PopupSelect
                    id="AreaSelect"
                    ref="PopupSelect_Area"
                    :vmodel=OrderInfo
                    bind="エリアＣＤ"
                    dataUrl="/DAI08010/GetCourseList"
                    :params="{ BushoCd: viewModel.BushoCd, WithZero: true }"
                    :dataListReset=true
                    :exceptCheck="[{ Cd: 0 }]"
                    title="エリア一覧"
                    labelCd="エリアCD"
                    labelCdNm="エリア名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">地域区分</label>
                <VueSelect
                    id="TiikiKbn"
                    :vmodel=OrderInfo
                    bind="地域区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 32 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-2">
                <VueOptions
                    title="AMPM"
                    customLabelStyle="width: 60px; text-align: center;"
                    id="AmPmKbn"
                    :vmodel=OrderInfo
                    bind="AMPM区分"
                    :list="[
                        {code: '0', name: 'AM', label: 'AM'},
                        {code: '1', name: 'PM', label: 'PM'},
                    ]"
                />
            </div>
            <div class="col-md-2">
                <label class="text-center">税区分</label>
                <VueSelect
                    id="TaxKbn"
                    :vmodel=OrderInfo
                    bind="税区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 20 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onTaxKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>配達区分</label>
            </div>
            <div class="col-md-1">
                <VueSelect
                    id="DeliveryKbn"
                    :vmodel=OrderInfo
                    bind="配達区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 31 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-2">
                <VueOptions
                    title="連絡"
                    customLabelStyle="width: 60px; text-align: center;"
                    id="RenrakuKbn"
                    :vmodel=OrderInfo
                    bind="連絡区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 30 }"
                    :withCode=false
                />
            </div>
            <div class="col-md-4">
                <label class="text-center">担当者</label>
                <PopupSelect
                    id="TantoSelect"
                    ref="PopupSelect_Tanto"
                    :vmodel=OrderInfo
                    bind="担当者ＣＤ"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{}"
                    :isPreLoad=true
                    :dataListReset=true
                    title="担当者一覧"
                    labelCd="担当者CD"
                    labelCdNm="担当者名"
                    :showColumns='[
                        { title: "担当者名カナ", dataIndx: "担当者名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150 },
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
            <div class="col-md-4">
                <label>営業担当者</label>
                <PopupSelect
                    id="EigyoTantoSelect"
                    ref="PopupSelect_EigyoTanto"
                    :vmodel=OrderInfo
                    bind="営業担当者ＣＤ"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{}"
                    :isPreLoad=true
                    :dataListReset=true
                    title="営業担当者一覧"
                    labelCd="担当者CD"
                    labelCdNm="担当者名"
                    :showColumns='[
                        { title: "担当者名カナ", dataIndx: "担当者名カナ", dataType: "string", width: 150, maxWidth: 150, minWidth: 150 },
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=150
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <PqGridWrapper
            :id='"DAI08010Grid1" + (!!params ? _uid : "")'
            ref="DAI08010Grid1"
            dataUrl="/DAI08010/Search"
            :query=this.searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :checkChanged=true
            :options=this.grid1Options
            :autoToolTipDisabled=true
            :onAfterSearchFunc=onAfterSearchFunc
            :onCompleteFunc=onCompleteFunc
            :onSelectChangeFunc=onSelectChangeFunc
            :autoEmptyRow=true
            :autoEmptyRowCount=1
            :autoEmptyRowFunc=autoEmptyRowFunc
            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
            :onRefreshFunc=onRefreshFunc
        />
        <div class="row">
            <div class="col-md-4">
            </div>
            <div class="col-md-1">
                <label>預り日付</label>
            </div>
            <div class="col-md-2">
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DepoDate"
                    :editable=true
                />
            </div>
            <div class="col-md-2">
                <label style="width: 67px;">預り金</label>
                <currency-input class="form-control text-right" style="width: 100px;" type="text" v-model=OrderInfo.預り金 />
            </div>
            <div class="col-md-2">
                <label style="width: 67px;">差額</label>
                <currency-input class="form-control text-right" style="width: 100px;" type="text" :value=PriceDiff readonly tabindex="-1" />
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-1">
                <label>製造用特記</label>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" style="max-height: unset;" v-model=OrderInfo.製造用特記 rows=3 />
            </div>
            <div class="col-md-1">
                <label class="w-100 text-center">事務用特記</label>
            </div>
            <div class="col-md-5">
                <textarea class="form-control" style="max-height: unset;" v-model=OrderInfo.事務用特記 rows=3 />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
            </div>
            <div class="col-md-2 update-info">
                <label class="label-blue">修正者</label>
                <label class="label-blue" style="width: auto;">{{OrderInfo.修正担当者名}}</label>
            </div>
            <div class="col-md-3 update-info">
                <label class="label-blue">修正日時</label>
                <label class="label-blue" style="width: auto;">{{LastEditDate}}</label>
            </div>
            <div class="col-md-1">
                <label style="width: 100%; color: red; text-align: right;">{{checkMsg}}</label>
            </div>
        </div>
    </form>
</template>

<style scoped>
label {
    max-width: 60px;
}
.update-info .label-blue {
    max-width: unset !important;
}
div[class^="col-md"] > label {
    max-width: unset;
    line-height: 16px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
</style>
<style>
#Page_DAI08010 .CustomerSelect .select-name {
    color: royalblue;
}
#DAI08010Grid1 svg.pq-grid-overlay {
    display: block;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08010",
    components: {
    },
    computed: {
        searchParams: function() {
            var mt = moment(this.viewModel.DeliveryDate, "YYYY年MM月DD日");
            return {
                BushoCd: this.viewModel.BushoCd,
                DeliveryDate: !!mt.isValid() ? mt.format("YYYYMMDD") : null,
                OrderNo: this.viewModel.OrderNo,
                CustomerCd: this.CustomerInfo.得意先ＣＤ,
            };
        },
        LastEditDate: function() {
            var vue = this;
            var mt = moment(vue.OrderInfo.修正日)
            return mt.isValid() ? mt.format("YYYY/MM/DD HH:mm:ss") : "";
        },
    },
    watch: {
        "CustomerInfo.得意先名": {
            handler: function(newVal) {
                var vue = this;
                var disabled = !vue.CustomerInfo.得意先名
                vue.footerButtons.find(v => v.id == "DAI08010Grid1_Save").disabled = disabled;
            },
        },
        "CustomerInfo.得意先ＣＤ": {
            handler: function(newVal) {
                var vue = this;
                var disabled = !vue.CustomerInfo.得意先ＣＤ
                vue.footerButtons.find(v => v.id == "DAI08010Grid1_showOrderList").disabled = disabled;
            },
        },
        "OrderInfo.預り金": {
            handler: function(newVal) {
                var vue = this;
                var grid = vue.DAI08010Grid1;
                var sum = _.sum(grid.pdata.map(r => (r.金額 || 0) * 1));
                vue.PriceDiff = sum - vue.OrderInfo.預り金 * 1;
            }
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理->仕出注文入力",
            noViewModel: true,
            IsFirstFocus: false,
            viewModel: {
                OrderNo: null,
                BushoCd: null,
                DeliveryDate: null,
                DeliveryTime: null,
                OrderDate: null,
                OrderTime: null,
                TakeoutTime: null,
                DepoDate: null,
            },
            CustomerInfo: {
                得意先ＣＤ: null,
                得意先名: null,
                得意先名カナ: null,
                得意先名略称: null,
                郵便番号: null,
                住所１: null,
                住所２: null,
                電話番号１: null,
                ＦＡＸ１: null,
                電話番号２: null,
                ＦＡＸ２: null,
                お届け先郵便番号: null,
                お届け先住所１: null,
                お届け先住所２: null,
                お届け先電話番号１: null,
                お届け先ＦＡＸ１: null,
                お届け先電話番号２: null,
                お届け先ＦＡＸ２: null,
                部署CD: null,
                売掛現金区分: null,
                電話確認区分: null,
                締区分: null,
                締日１: null,
                締日２: null,
                支払サイト: null,
                支払日: null,
                集金区分: null,
                請求先ＣＤ: null,
                支払方法１: null,
                支払方法２: null,
                集金手数料: null,
                金融機関CD: null,
                金融機関支店CD: null,
                記号番号: null,
                口座種別: null,
                口座番号: null,
                口座名義人: null,
                チケット枚数: null,
                サービスチケット枚数: null,
                営業担当者ＣＤ: null,
                獲得営業者ＣＤ: null,
                登録担当者ＣＤ: null,
                受注得意先ＣＤ: null,
                配送ｸﾞﾙｰﾌﾟＣＤ: null,
                受注方法: null,
                電話確認時間_時: null,
                電話確認時間_分: null,
                味噌汁区分: null,
                ふりかけ区分: null,
                納品書発行区分: null,
                空き容器回収区分: null,
                既定製造パターン: null,
                請求書敬称: null,
                請求書区分別頁: null,
                請求内訳区分: null,
                備考１: null,
                備考２: null,
                備考３: null,
                ＷＥＢ表示区分: null,
                取扱区分: null,
                ＩＤ: null,
                パスワード: null,
                状態区分: null,
                承認日: null,
                承認者ＣＤ: null,
                状態理由区分: null,
                受注顧客区分: null,
                休日設定: null,
                新規登録日: null,
                税区分: null,
                税処理: null,
                祝日配送区分: null,
                誕生日１: null,
                誕生日２: null,
                失客日: null,
                修正担当者ＣＤ: null,
                修正日: null,
            },
            OrderInfo: {
                受注Ｎｏ: null,
                配達日付: null,
                配達時間: null,
                注文日付: null,
                注文時間: null,
                エリアＣＤ: "0",
                地域区分: null,
                AMPM区分: "0",
                税区分: "0",
                配達区分: "0",
                連絡区分: "0",
                製造用特記: null,
                事務用特記: null,
                預り日付: undefined,
                預り金: 0,
                修正担当者名: null,
                修正日: null,
            },
            CustomerInfoInit: {
                得意先ＣＤ: null,
                得意先名: null,
                得意先名カナ: null,
                得意先名略称: null,
                郵便番号: null,
                住所１: null,
                住所２: null,
                電話番号１: null,
                ＦＡＸ１: null,
                電話番号２: null,
                ＦＡＸ２: null,
                お届け先郵便番号: null,
                お届け先住所１: null,
                お届け先住所２: null,
                お届け先電話番号１: null,
                お届け先ＦＡＸ１: null,
                お届け先電話番号２: null,
                お届け先ＦＡＸ２: null,
                部署CD: null,
                売掛現金区分: 1,
                電話確認区分: 0,
                締区分: 0,
                締日１: 0,
                締日２: 0,
                支払サイト: 0,
                支払日: 0,
                集金区分: 1,
                請求先ＣＤ: null,
                支払方法１: 1,
                支払方法２: 0,
                集金手数料: 0,
                金融機関CD: 0,
                金融機関支店CD: 0,
                記号番号: null,
                口座種別: 0,
                口座番号: 0,
                口座名義人: null,
                チケット枚数: 0,
                サービスチケット枚数: 0,
                営業担当者ＣＤ: null,
                獲得営業者ＣＤ: null,
                登録担当者ＣＤ: null,
                受注得意先ＣＤ: null,
                配送ｸﾞﾙｰﾌﾟＣＤ: 0,
                受注方法: 1,
                電話確認時間_時: 0,
                電話確認時間_分: 0,
                味噌汁区分: 0,
                ふりかけ区分: 0,
                納品書発行区分: 0,
                空き容器回収区分: 1,
                既定製造パターン: 0,
                請求書敬称: 2,
                請求書区分別頁: 0,
                請求内訳区分: 0,
                備考１: null,
                備考２: null,
                備考３: null,
                ＷＥＢ表示区分: 0,
                取扱区分: 0,
                ＩＤ: null,
                パスワード: null,
                状態区分: 30,
                承認日: null,
                承認者ＣＤ: 0,
                状態理由区分: 0,
                受注顧客区分: 0,
                休日設定: "0000000",
                新規登録日: null,
                税区分: 1,
                税処理: 0,
                祝日配送区分: 0,
                誕生日１: null,
                誕生日２: null,
                失客日: null,
                修正担当者ＣＤ: null,
                修正日: null,
            },
            OrderInfoInit: {
                部署ＣＤ: null,
                受注Ｎｏ: null,
                配達日付: null,
                配達時間: null,
                製造締切時間: null,
                得意先ＣＤ: null,
                注文日付: null,
                注文時間: null,
                担当者ＣＤ: null,
                営業担当者ＣＤ: null,
                配達先１: null,
                配達先２: null,
                エリアＣＤ: "0",
                地域区分: "0",
                AMPM区分: "0",
                配達区分: "0",
                連絡区分: "0",
                税区分: "0",
                預り日付: undefined,
                預り金: 0,
                製造用特記: null,
                事務用特記: null,
                合計数量: null,
                合計金額: null,
                合計消費税: null,
                納品書発行フラグ: 0,
                予備金額１: 0,
                予備金額２: 0,
                予備ＣＤ１: 0,
                予備ＣＤ２: 0,
                修正担当者ＣＤ: null,
                修正日: null,
            },
            DAI08010Grid1: null,
            TaxList: [],
            ProductList: [],
            CustomerInfoOrg: null,
            OrderInfoOrg: null,
            checkMsg: null,
            PriceDiff: null,
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
                height: 180,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
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
                    [
                        "sortIndx",
                        function(rowData) {
                            return (rowData["商品ＣＤ"] * 1) || 99999;
                        }
                    ],
                    [
                        "金額",
                        function(rowData) {
                            return vue.calcPrice(rowData["単価"], rowData["数量"]);
                        }
                    ],
                    [
                        "消費税",
                        function(rowData) {
                            return vue.calcTax(rowData["単価"], rowData["数量"]);
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "sortIndx",
                        dataIndx: "sortIndx", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "コード",
                        dataIndx: "商品ＣＤ", dataType: "string",
                        width: 120, maxWidth: 120, minWidth: 120,
                        editable: true,
                        key: true,
                        autocomplete: {
                            source: () => vue.getProductList(),
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm", "商品単価": "売価単価", "商品種類": "商品種類", },
                            onSelect: rowData => {
                                rowData["単価"] = rowData["商品単価"]
                                rowData["金額"] = rowData["単価"] * rowData["数量"];
                            },
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            GetMatchedFunc: vue.ProductAutoCompleteGetMatched,
                            selectSave: true,
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        editable: true,
                    },
                    {
                        title: "備考",
                        dataIndx: "備考", dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                        editable: true,
                    },
                    {
                        title: "数量",
                        dataIndx: "数量", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "単価",
                        dataIndx: "単価", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                    },
                    {
                        title: "商品単価",
                        dataIndx: "商品単価", dataType: "integer", format: "#,##0",
                        hidden: true,
                    },
                    {
                        title: "金額",
                        dataIndx: "金額", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "消費税",
                        dataIndx: "消費税", dataType: "integer", format: "#,##0",
                        width: 100, maxWidth: 100, minWidth: 100,
                        editable: true,
                        render: ui => {
                            if (!ui.rowData.pq_grandsummary) {
                                if (!(ui.rowData[ui.dataIndx] * 1)) {
                                    return { text: "" };
                                }
                            }
                            return ui;
                        },
                        summary: {
                            type: "TotalInt",
                        },
                    },
                    {
                        title: "提げ袋",
                        dataIndx: "提げ袋", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                    },
                    {
                        title: "風呂敷",
                        dataIndx: "風呂敷", dataType: "integer", format: "#,##0",
                        width: 75, maxWidth: 75, minWidth: 75,
                        editable: true,
                    },
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI08010Grid1_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.clearOrder();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI08010Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "伝票削除", id: "DAI08010Grid1_DeleteOrder", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.deleteOrder();
                    }
                },
                { visible: "true", value: "行削除", id: "DAI08010Grid1_DeleteRow", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.deleteRow();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "商品種類<br>マスタメンテ", id: "DAI08010Grid1_showProductMaint", disabled: false, shortcut: "F8",
                    onClick: function () {
                        vue.showProductMaint();
                    }
                },
                { visible: "true", value: "登録", id: "DAI08010Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.saveOrder();
                    }
                },
                { visible: "true", value: "受注問合せ", id: "DAI08010Grid1_showOrderList", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.showOrderList();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            if (!vue.params || !!vue.params.IsNew) {
                vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
                vue.viewModel.DeliveryTime = moment().format("HH時mm分");

                vue.viewModel.OrderDate = vue.viewModel.DeliveryDate;
                vue.viewModel.OrderTime = vue.viewModel.DeliveryTime;

                vue.CustomerInfo.売掛現金区分='1';
            } else {
                vue.viewModel.BushoCd = vue.params.部署ＣＤ;
                vue.viewModel.DeliveryDate = moment(vue.params.配達日付).format("YYYY年MM月DD日");
                vue.viewModel.OrderNo = vue.params.受注Ｎｏ;
            }

            //for Child mode
            if (!!vue.params && !!vue.params.IsChild) {
                vue.DAI08010Grid1 = vue.$refs.DAI08010Grid1.grid;
            }

            //watcher
            vue.$watch(
                "$refs.DAI08010Grid1.selectionRowCount",
                cnt => {
                    vue.footerButtons.find(v => v.id == "DAI08010Grid1_DeleteRow").disabled = cnt == 0 || cnt > 1;
                }
            );
            vue.$watch(
                "$refs.DAI08010Grid1.isSelection",
                isSelection => {
                    vue.footerButtons.find(v => v.id == "DAI08010Grid1_DeleteRow").disabled = !isSelection;
                }
            );
        },
        preventFnKeys: function(event) {
            var key = event.key || "";
            var code = event.code || "";
            if (!!key.match(/F\d+/) || !!code.match(/F\d+/)) {
                event.stopPropagation();
            }
        },
        CourseAfterSearchFunc: function(comp) {
            var vue = this;

            if (!comp.dataList.find(v => v.Cd == "0")) {
                comp.dataList.unshift({ Cd: "0", CdNm: "コース無し" });
            }
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            // 条件変更ハンドラ
            vue.conditionChanged();
        },
        onDeliveryDateChanged: function(code, entities) {
            var vue = this;

            // 条件変更ハンドラ
            vue.conditionChanged();
        },
        onTakeoutTimeChanged: function(code, entities) {
            var vue = this;
        },
        onOrderNoChanged: function(code, entity, comp) {
            var vue = this;

            if (!code) {
                //同一内容で別の日の注文を入力することがあるため入力内容をクリアしない
                //vue.clearOrder(true);
            }

            if (code != vue.OrderInfo.受注Ｎｏ || entity.配達日付 != vue.OrderInfo.配達日付) {
                vue.CustomerInfo.得意先ＣＤ = entity.得意先ＣＤ;
                vue.conditionChanged();
            }
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                // 条件変更ハンドラ
                vue.conditionChanged();
            }
        },
        onCustomerNameChanged: function(name) {
            var vue = this;
            vue.CustomerInfo.得意先名 = name;
        },
        onTaxKbnChanged: function() {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            grid.pdata.forEach(r => {
                r.金額 = vue.calcPrice(r.単価, r.数量);
                r.消費税 = vue.calcTax(r.単価, r.数量);
            });
            grid.refreshView();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.searchParams.BushoCd || !vue.searchParams.DeliveryDate) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.showLoading();

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            axios.all(
                [
                    //消費税率リストの取得
                    axios.post("/DAI08010/GetTaxList", params),
                    //商品リストの取得
                    axios.post("/DAI08010/GetProductList", params),
                ]
            ).then(
                axios.spread((responseTax, responseProduct) => {
                    grid.hideLoading();
                    vue.TaxList = responseTax.data;
                    vue.ProductList = responseProduct.data;

                    if (!!vue.searchParams.CustomerCd) {
                        grid.searchData(vue.searchParams);
                    } else {
                        grid.refresh();
                    }
                })
            )
            .catch(err => {
                console.log(err);

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "マスタの検索に失敗しました" + "<br/>" + err.message,
                });
            });

        },
        getProductList: function() {
            var vue = this;
            vue.ProductList.forEach(v => {
                v.Cd = v.商品ＣＤ;
                v.CdNm = v.商品名;
                return v;
            });
            return vue.ProductList;
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList || !dataList.length) return [];

            var keywords = (input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
                    ret.label = v.Cd + " : " + v.CdNm + "[" + v.商品種類名 + "]";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        ProductAutoCompleteGetMatched: function(dataList, key, rowData) {
            return dataList.filter(v => v.商品種類 == rowData.商品種類 && v.商品ＣＤ == key);
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;
            var data = res[0];

            vue.CustomerInfo = _.cloneDeep(data.CustomerInfo || vue.CustomerInfoInit);
            vue.CustomerInfoOrg = _.cloneDeep(data.CustomerInfo || null);

            if (!!data.OrderInfo && data.OrderInfo.受注Ｎｏ == vue.viewModel.OrderNo) {
                data.OrderInfo.合計数量 = (data.OrderInfo.合計数量 || 0) * 1;
                data.OrderInfo.合計金額 = (data.OrderInfo.合計金額 || 0) * 1;
                data.OrderInfo.合計消費税 = (data.OrderInfo.合計消費税 || 0) * 1;
                data.OrderInfo.預り金 = (data.OrderInfo.預り金 || 0) * 1;

                vue.OrderInfo = _.cloneDeep(data.OrderInfo);
                vue.OrderInfoOrg = _.cloneDeep(data.OrderInfo);
            } else {
                vue.OrderInfo = _.cloneDeep(vue.OrderInfoInit);
                vue.OrderInfoOrg = _.cloneDeep(null);

                //担当者情報を置き換える(担当者=ログイン者、営業担当者=得意先マスタの獲得営業者)
                vue.OrderInfo.担当者ＣＤ = vue.getLoginInfo().uid;
                vue.OrderInfo.営業担当者ＣＤ = vue.CustomerInfo.獲得営業者ＣＤ;
            }

            vue.OrderInfo.配達先１ = vue.OrderInfo.配達先１ || vue.CustomerInfo.お届け先住所１;
            vue.OrderInfo.配達先２ = vue.OrderInfo.配達先２ || vue.CustomerInfo.お届け先住所２;

            vue.viewModel.OrderNo = vue.OrderInfo.受注Ｎｏ;

            var mt;
            mt = moment(vue.OrderInfo.配達日付);
            if (mt.isValid()) {
                vue.viewModel.DeliveryDate = mt.format("YYYY年MM月DD日");
            }
            mt = moment(vue.OrderInfo.配達時間, "HH:mm");
            if (mt.isValid()) {
                vue.viewModel.DeliveryTime = mt.format("HH時mm分");
            } else {
                //vue.viewModel.DeliveryTime = moment().format("HH時mm分");
            }
            mt = moment(vue.OrderInfo.製造締切時間, "HH:mm");
            if (mt.isValid()) {
                vue.viewModel.TakeoutTime = mt.format("HH時mm分");
            } else {
                //vue.viewModel.TakeoutTime = null;
            }
            mt = moment(vue.OrderInfo.注文日付);
            if (mt.isValid()) {
                vue.viewModel.OrderDate = mt.format("YYYY年MM月DD日");
            } else {
                //vue.viewModel.OrderDate = moment().format("YYYY年MM月DD日");
            }
            mt = moment(vue.OrderInfo.注文時間, "HH:mm");
            if (mt.isValid()) {
                vue.viewModel.OrderTime = mt.format("HH時mm分");
            } else {
                //vue.viewModel.OrderTime = moment().format("HH時mm分");
            }
            mt = moment(vue.OrderInfo.預り日付);
            if (mt.isValid()) {
                vue.viewModel.DepoDate = mt.format("YYYY年MM月DD日");
            } else {
                vue.viewModel.DepoDate = null;
            }

            //請求/会計済チェック
            var CheckSeikyu = data.CheckSeikyu * 1;
            var CheckKaikei = data.CheckKaikei * 1;

            vue.checkMsg = !!CheckSeikyu ? "請求済みです" : !!CheckKaikei ? "会計処理済みです" : "";
            vue.footerButtons.find(v => v.id == "DAI08010Grid1_DeleteOrder").disabled = !!CheckSeikyu || !!CheckKaikei;
            vue.footerButtons.find(v => v.id == "DAI08010Grid1_Save").disabled = !!CheckSeikyu || !!CheckKaikei;

            var list = [];
            if (!!data.OrderInfo && data.OrderInfo.受注Ｎｏ == vue.viewModel.OrderNo) {
                list = data.MeisaiList;
                list.forEach(v => {
                    v.InitialValue = _.cloneDeep(v);
                    return v;
                });
            }

            return list;
        },
        calcPrice: function(unit, count) {
            var vue = this;

            var info = vue.TaxList.find(t => t.注文税区分 == vue.OrderInfo.税区分);

            if (!info) {
                return unit * count;
            }

            var tax = (info.消費税率 || 0) * 0.01;

            switch (vue.OrderInfo.税区分) {
                case "0":
                    return Math.floor(unit * count * (1 + tax));
                case "1":
                    return unit * count;
                case "2":
                    return unit * count;
            }
        },
        calcTax: function(unit, count) {
            var vue = this;

            var info = vue.TaxList.find(t => t.注文税区分 == vue.OrderInfo.税区分);

            if (!info) {
                return 0;
            }

            var tax = (info.消費税率 || 0) * 0.01;

            switch (vue.OrderInfo.税区分) {
                case "0":
                    return Math.floor(unit * count * tax);
                case "1":
                    return Math.floor(unit * count * (1 - 1 / (1 + tax)));
                case "2":
                    return 0;
            }
        },
        onRefreshFunc: function(vueGrid) {
            var vue = this;

            var sum = _.sum(vueGrid.pdata.map(r => (r.金額 || 0) * 1));
            vue.PriceDiff = sum - vue.OrderInfo.預り金 * 1;
            return true;
        },
        onCompleteFunc: function(vueGrid, ui) {
            var vue = this;

            if (vueGrid.pdata.length > 0) {
                var data = vueGrid.pdata[0];
                var colIndx = !data["商品ＣＤ"] ? vueGrid.columns["商品ＣＤ"].leftPos : vueGrid.columns["数量"].leftPos;
                vueGrid.setSelection({ rowIndx: 0, colIndx: colIndx });
            }
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;

            return {
                "数量": 0,
                "単価": 0,
                "金額": 0,
                "消費税": 0,
                "提げ袋": 0,
                "風呂敷": 0,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["商品名"];
        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI08010Grid1;

            if(!grid.Selection()._areas.length){
                return;
            }

            var rowIndx = grid.Selection()._areas[0].r1;
            grid.deleteRow({ rowIndx: rowIndx });
        },
        onSelectChangeFunc: function(grid, ui) {
        },
        OrderNoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = (input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["得意先名", "住所１", "住所２", "配達先１", "配達先２"];

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
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
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

            var list = _.sortBy(dataList, v => v.部署CD == vue.viewModel.BushoCd ? 0 : 1)
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
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
        CourseAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = [];

            var list = dataList
                .filter(v => v.部署ＣＤ == vue.viewModel.BushoCd || v.コースＣＤ == 0)
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
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        OrderNoParamsChangedCheckFunc: function(newVal, oldVal) {
            var ret = !!newVal.TargetDate && !!newVal.BushoCd;
            return ret;
        },
        CustomerParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;

            var ret = !!newVal.UserBushoCd;
            return ret;
        },
        CourseParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;

            var ret = !!newVal.BushoCd;
            return ret;
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["担当者名", "部署.部署名", "担当者名カナ"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.担当者ＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.担当者ＣＤ + " : " + v.担当者名 + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.担当者ＣＤ;
                    ret.text = v.担当者名;
                    return ret;
                })
                ;

            return list;
        },
        clearOrder: function(keepDate) {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            vue.CustomerInfo = _.cloneDeep(vue.CustomerInfoInit);
            vue.CustomerInfoOrg = null;

            vue.OrderInfo = _.cloneDeep(vue.OrderInfoInit);
            vue.OrderInfoOrg = null;

            vue.viewModel.OrderNo = null;

            if (!keepDate) {
                vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
                vue.viewModel.DeliveryTime = moment().format("HH時mm分");
                vue.viewModel.TakeoutTime = null;
            }
            vue.viewModel.OrderDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.OrderTime = moment().format("HH時mm分");

            vue.checkMsg = "";

            $(vue.$el).find(".has-error").removeClass("has-error");

            grid.clearData();
        },
        saveOrder: function() {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            $(vue.$el).find(".BushoCd")[!vue.viewModel.BushoCd ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find(".DeliveryDate .target-input")[!vue.viewModel.DeliveryDate ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find(".DeliveryTime .target-input")[!vue.viewModel.DeliveryTime ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find(".TakeoutTime .target-input")[!vue.viewModel.TakeoutTime  ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find("#CustomerSelect .select-name")[!vue.CustomerInfo.得意先名 ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find(".CustomerNameKana")[!vue.CustomerInfo.得意先名カナ ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find(".TelNo1")[!vue.CustomerInfo.電話番号１ ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find("#TantoSelect")[!vue.OrderInfo.担当者ＣＤ ? "addClass" : "removeClass"]("has-error");
            $(vue.$el).find("#EigyoTantoSelect")[!vue.OrderInfo.営業担当者ＣＤ ? "addClass" : "removeClass"]("has-error");

            if(!vue.viewModel.BushoCd || !vue.viewModel.DeliveryDate || !vue.viewModel.DeliveryTime || !vue.viewModel.TakeoutTime
            || !vue.CustomerInfo.得意先名 || !vue.CustomerInfo.得意先名カナ || !vue.CustomerInfo.電話番号１
            || !vue.OrderInfo.担当者ＣＤ || !vue.OrderInfo.営業担当者ＣＤ){
                $.dialogErr({
                    title: "登録不可",
                    contents: [
                        !vue.viewModel.BushoCd ? "部署が入力されていません。<br/>" : "",
                        !vue.viewModel.DeliveryDate ? "配達日付が入力されていません。<br/>" : "",
                        !vue.viewModel.DeliveryTime ? "配達時間が入力されていません。<br/>" : "",
                        !vue.viewModel.TakeoutTime  ? "持出時間が入力されていません。<br/>" : "",
                        !vue.CustomerInfo.得意先名 ? "得意先名が入力されていません。<br/>" : "",
                        !vue.CustomerInfo.得意先名カナ ? "得意先名カナが入力されていません。<br/>" : "",
                        !vue.CustomerInfo.電話番号１ ? "電話番号が入力されていません。<br/>" : "",
                        !vue.OrderInfo.担当者ＣＤ ? "担当者が入力されていません。<br/>" : "",
                        !vue.OrderInfo.営業担当者ＣＤ ? "営業担当者が入力されていません。<br/>" : "",
                    ]
                })
                return;
            }

            var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

            var hasError = checkError(grid);

            if(hasError){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "エラー項目があるため、登録できません。",
                });
                return;
            }

            var checkRequire = grid => grid.pdata.map(r => [r.商品名]).every(r => r.every(v => !!v) || r.every(v => !v));

            var require = checkRequire(grid);

            if(!require){
                $.dialogErr({
                    title: "未入力項目",
                    contents: "未入力項目があるため、登録できません。",
                });
                return;
            }

            var exec = () => {
                var date = moment().format("YYYY-MM-DD 00:00:00.000");

                //CustomerInfo
                var CustomerInfo = _.cloneDeep(vue.CustomerInfo);

                CustomerInfo.部署CD = vue.viewModel.BushoCd;
                CustomerInfo.得意先名略称 = CustomerInfo.得意先名;
                CustomerInfo.営業担当者ＣＤ = vue.OrderInfo.営業担当者ＣＤ;
                CustomerInfo.獲得営業者ＣＤ = vue.OrderInfo.営業担当者ＣＤ;
                CustomerInfo.登録担当者ＣＤ = vue.OrderInfo.担当者ＣＤ;
                CustomerInfo.お届け先住所１ = vue.OrderInfo.配達先１;
                CustomerInfo.お届け先住所２ = vue.OrderInfo.配達先２;
                CustomerInfo.承認日 = !!CustomerInfo.得意先ＣＤ ? CustomerInfo.承認日 : date;
                CustomerInfo.新規登録日 = !!CustomerInfo.得意先ＣＤ ? CustomerInfo.新規登録日 : date;
                CustomerInfo.修正担当者ＣＤ = vue.getLoginInfo().uid;

                if (!_.keys(diff(vue.CustomerInfoOrg, CustomerInfo)).length) {
                    CustomerInfo = null;
                }

                //OrderInfo
                var OrderInfo = _.cloneDeep(vue.OrderInfo);
                OrderInfo.部署ＣＤ = !!OrderInfo.受注Ｎｏ ? OrderInfo.部署ＣＤ : vue.viewModel.BushoCd;
                OrderInfo.得意先ＣＤ = vue.$refs.PopupSelect_Customer.selectValue;
                OrderInfo.配達日付 = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYY-MM-DD 00:00:00.000");
                OrderInfo.配達時間 = moment(vue.viewModel.DeliveryTime, "HH時mm分").format("HH:mm");
                OrderInfo.製造締切時間 = moment(vue.viewModel.TakeoutTime, "HH時mm分").format("HH:mm");
                OrderInfo.注文日付 = moment(vue.viewModel.OrderDate, "YYYY年MM月DD日").format("YYYY-MM-DD 00:00:00.000");
                OrderInfo.注文時間 = moment(vue.viewModel.OrderTime, "HH時mm分").format("HH:mm");
                OrderInfo.預り日付 = !!vue.viewModel.DepoDate ? moment(vue.viewModel.DepoDate, "YYYY年MM月DD日").format("YYYYMMDD") : null;
                OrderInfo.合計数量 = _.sum(grid.pdata.map(r => (r.数量 || 0) * 1));
                OrderInfo.合計金額 = _.sum(grid.pdata.map(r => (r.金額 || 0) * 1));
                OrderInfo.合計消費税 = _.sum(grid.pdata.map(r => (r.消費税 || 0) * 1));
                OrderInfo.修正担当者ＣＤ = vue.getLoginInfo().uid;

                if (!_.keys(diff(vue.OrderInfoOrg, OrderInfo)).length) {
                    OrderInfo = null;
                }

                var SaveList = _.cloneDeep(grid.getPlainPData().filter(v => !!v.商品名));

                //注文明細データの型に整形
                SaveList.forEach((v, i) => {
                    v.部署ＣＤ = v.部署ＣＤ || vue.viewModel.BushoCd;
                    v.受注Ｎｏ = v.受注Ｎｏ || vue.OrderInfo.受注Ｎｏ;
                    v.配達日付 = v.配達日付 || moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    v.明細Ｎｏ = v.明細Ｎｏ;
                    v.商品種類 = v.商品種類;
                    v.商品ＣＤ = v.商品ＣＤ || 0;
                    v.商品名 = v.商品名;
                    v.備考 = v.備考;
                    v.数量 = v.数量;
                    v.単価 = v.単価;
                    v.金額 = v.金額;
                    v.消費税 = v.消費税 || 0;
                    v.提げ袋 = v.提げ袋 || 0;
                    v.風呂敷 = v.風呂敷 || 0;
                    v.予備金額１ = v.予備金額１ || 0;
                    v.予備金額２ = v.予備金額２ || 0;
                    v.予備ＣＤ１ = v.予備ＣＤ１ || 0;
                    v.予備ＣＤ２ = v.予備ＣＤ２ || 0;
                    v.修正担当者ＣＤ = vue.getLoginInfo().uid;

                    delete v.sortIndx;
                    delete v.商品単価;
                });

                var DeleteList = grid.getChanges().deleteList
                    .map(v => {
                        var ret = _.cloneDeep(v.InitialValue);

                        return ret;
                    });

                console.log("8010 save", CustomerInfo, OrderInfo, SaveList, DeleteList);

                //保存実行
                grid.saveData(
                    {
                        uri: "/DAI08010/Save",
                        params: {
                            CustomerInfo: CustomerInfo,
                            OrderInfo: OrderInfo,
                            SaveList: SaveList,
                            DeleteList: DeleteList,
                        },
                        optional: this.searchParams,
                        confirm: {
                            isShow: false,
                        },
                        done: {
                            isShow: false,
                            callback: (gridVue, grid, res)=>{
                                if (!!res.edited) {
                                    $.dialogInfo({
                                        title: "登録チェック",
                                        contents: "他で変更されたデータがあります。",
                                    });
                                    grid.blinkDiff(res.current);
                                } else {
                                    grid.commit();
                                    if (vue.viewModel.OrderNo == res.params.OrderNo) {
                                        grid.refreshDataAndView();
                                    } else {
                                        vue.viewModel.OrderNo = res.params.OrderNo;
                                        vue.conditionChanged();
                                    }
                                    if (!!res.isDeleteUriage) {
                                        $.dialogInfo({
                                            title: "仕出し売上削除",
                                            contents: "注文変更により、仕出しに対応する売上を削除しました。\n"
                                                + "「仕出し配送予定入力」画面で売上反映を再度おこなってください。",
                                        });
                                    }
                                }
                                if (!!vue.params && !!vue.params.Parent) {
                                    vue.params.Parent.conditionChanged(true);
                                }

                                return false;
                            },
                        },
                    }
                );
            };

            if (vue.viewModel.BushoCd != vue.CustomerInfo.部署CD && !!vue.CustomerInfo.得意先ＣＤ) {
                $.dialogConfirm({
                    title: "確認",
                    contents: "得意先の部署が違います！！\n登録しますか？",
                    buttons:[
                        {
                            text: "はい",
                            class: "btn btn-primary",
                            click: function(){
                                $(this).dialog("close");

                                //実行
                                exec();
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
            } else {
                exec();
            }
        },
        deleteOrder: function() {
            var vue = this;
            var grid = vue.DAI08010Grid1;

            //削除実行
            grid.saveData(
                {
                    uri: "/DAI08010/Delete",
                    params: { noCache: true },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "注文データ削除確認",
                        message: "注文データを削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            console.log("res", res);

                            if (!!res.edited && !!res.edited.length) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.edited);
                            } else {
                                grid.clearData();
                                if (!!res.isDeleteUriage) {
                                    $.dialogInfo({
                                        title: "仕出し売上削除",
                                        contents: "注文削除により、仕出しに対応する売上を削除しました。",
                                    });
                                }
                                if (!!vue.params && !!vue.params.Parent) {
                                    vue.params.Parent.conditionChanged(true);
                                }
                            }

                            vue.conditionChanged();

                            return false;
                        },
                    },
                }
            );
        },
        showOrderList: function() {
            var vue = this;

            var params = _.cloneDeep(vue.searchParams);

            params.IsChild = true;
            params.IsNew = false;

            //DAI08009を子画面表示
            PageDialog.show({
                pgId: "DAI08009",
                params: params,
                isModal: true,
                isChild: true,
                reuse: false,
                width: 1250,
                height: 775,
            });
        },
        showProductMaint: function() {
            var vue = this;

            var cd = vue.viewModel.BushoCd;
            if (!cd) return;

            var params = { BushoCd: cd };
            params.IsNew = false;
            params.IsChild = true;
            params.Parent = vue;

            //DAI04180を子画面表示
            PageDialog.show({
                pgId: "DAI04180",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 1000,
                height: 750,
            });
        },
        updateProduct: function() {
            var vue = this;
            var grid = vue.DAI01030Grid1;

            var params = _.cloneDeep(vue.searchParams);
            params.noCache = true;

            //商品リスト検索
            axios.post("/DAI08010/GetProductList", params)
                .then(res => {
                    vue.ProductList = res.data;
                })
                .catch(err => {
                    $.dialogErr({
                        title: "商品種類マスタ検索失敗",
                        contents: "商品種類マスタの検索に失敗しました" + "<br/>" + err.message,
                    });
                });
        },
    }
}
</script>
