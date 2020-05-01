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
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.CourseCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 300px;" type="text" :value=viewModel.CourseNm readonly tabindex="-1">
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
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.平均食数 readonly tabindex="-1">
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
                    :buddies='{ "商品名": "CdNm", "単価": "売価単価" }'
                    dataUrl="/Utilities/GetProductListForSelect"
                    :params="{ ProductCd: null, KeyWord: null }"
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
                <input class="form-control" style="width: 100px;" type="text" :value=viewModel.単価 readonly tabindex="-1">
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
                <input class="form-control p-1" style="width: 100px;" type="text" v-model=viewModel.クレーム処理費用 v-maxBytes="100" />
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
                        {code: '0', name: '継続', label: '継続'},
                        {code: '1', name: '継続', label: '継続'},
                        {code: '2', name: '継続', label: '継続'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
            </div>
            <div class="col-md-4">
               <VueCheck
                    id="StatusLost"
                    ref="VueCheck_StatusLost"
                    :vmodel=viewModel
                    bind="その後客先失客"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '失客', label: '失客'},
                        {code: '1', name: '失客', label: '失客'},
                        {code: '2', name: '失客', label: '失客'},
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
                />
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
            var mt = moment(vue.viewModel.対象日付, "YYYY年MM月DD日");
            return {
                ClaimDate: mt.isValid() ? mt.format("YYYYMMDD") : null,
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
                ClaimDate: null,
                ClaimTime: null,
                ProcDate: null,
                クレームID: null,
                受付日時: null,
                管轄部門コード: null,
                クレーム区分コード: null,
                顧客コード: null,
                平均食数: null,
                顧客担当者名: null,
                商品コード: null,
                単価: null,
                クレーム内容: null,
                受付担当者コード: null,
                受付方法: null,
                クレーム処理日: null,
                クレーム処理者コード: null,
                クレーム処理品: null,
                クレーム処理費用: null,
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
                その後客先失客: null,
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
            data.viewModel = _.cloneDeep(vue.params);
            data.viewModel.BushoCd = vue.params.管轄部門コード;

            var mt;
            mt = moment(data.viewModel.受付日時);
            data.viewModel.ClaimDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");
            data.viewModel.ClaimTime = (mt.isValid() ? mt : moment()).format("HH時mm分");

            mt = moment(data.viewModel.クレーム処理日);
            data.viewModel.ProcDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");

            mt = moment(data.viewModel.失客日);
            data.viewModel.LostDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");

        } else {
            var mt = moment();
            data.viewModel.ClaimDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");
            data.viewModel.ClaimTime = (mt.isValid() ? mt : moment()).format("HH時mm分");
            data.viewModel.ProcDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");
            data.viewModel.LostDate = (mt.isValid() ? mt : moment()).format("YYYY年MM月DD日");
            data.viewModel.その後客先失客 = "2";
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "false" },
                { visible: "true", value: "登録", id: "DAI05140Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.searchParams.ClaimDate || !vue.searchParams.HolidayName){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.searchParams.ClaimDate ? "対象日付が入力されていません。" : "",
                                    !vue.searchParams.HolidayName ? "名称が入力されていません。<br/>" : "",
                                ]
                            })
                            if(!vue.searchParams.ClaimDate){
                                $(vue.$el).find("#ClaimDate").addClass("has-error");
                            }else{
                                $(vue.$el).find("#ClaimDate").removeClass("has-error");
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

            vue.viewModel.ClaimDate = vue.params.ClaimDate || moment().format("YYYY年MM月DD日");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onBushoChanged: function(code, entity) {
            var vue = this;
        },
        onCustomerChanged: function(code, entity) {
            var vue = this;
        },
        onProductChanged: function(code, entity) {
            var vue = this;
        },
        onStatusChanged: function(code, entity) {
            var vue = this;
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
