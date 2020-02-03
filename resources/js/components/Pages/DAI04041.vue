<template>
    <form id="this.$options.name">
        <div class="row ml-0 mr-0">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
            <div class="col-md-2">
                <label>得意先ＣＤ</label>
                <input class="form-control text-right" type="text"
                    id="CustomerCd"
                    v-model=viewModel.得意先ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    @change="onCustomerCdChanged"
                    maxlength="7"
                    v-int
                >
            </div>
            <div class="col-md-2">
                <label>状態</label>
                <VueSelect
                    id="State"
                    :vmodel=viewModel
                    bind="状態区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 12 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                />
            </div>
            <div class="col-md-3">
                <label>承認日</label>
                <DatePickerWrapper
                    id="ShoninDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="承認日"
                    :editable=true
                />
            </div>
            <div class="col-md-4">
                <label>承認者</label>
                <VueSelect
                    id="ShoninCd"
                    :vmodel=viewModel
                    bind="承認者ＣＤ"
                    buddy="承認者名"
                    uri="/Utilities/GetTantoListForSelect"
                    :params="{ bushoCd: null }"
                    :withCode=true
                    customStyle="{ width: 150px; }"
                />
            </div>
        </div>
        <div class="row ml-0 mr-0">
            <div class="col-md-9">
                <fieldset class="kihon-info w-100">
                    <legend class="kihon-info">基本情報</legend>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="">得意先名</label>
                            <input type="text" class="form-control"
                                v-model="viewModel.得意先名"
                                maxlength=60
                                v-maxBytes=60
                                v-setKana.disabled="res => viewModel.得意先名カナ = res"
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">得意先名カナ</label>
                            <input type="text" class="form-control" style="font-size: 15px !important;"
                                v-model="viewModel.得意先名カナ"
                                maxlength=30
                                v-maxBytes=30
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="">得意先名略称</label>
                            <input type="text" class="form-control" style="font-size: 15px !important;"
                                v-model="viewModel.得意先名略称"
                                maxlength=20
                                v-maxBytes=30
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="" style="width:360px !important">スマフォ表示用得意先名称</label>
                            <input type="text" class="form-control" :value="viewModel.スマフォ表示用得意先名名称">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label style="">部署</label>
                            <VueSelect
                                id="BushoCd"
                                :vmodel=viewModel
                                bind="部署CD"
                                uri="/Utilities/GetBushoList"
                                :params="{ cds: null }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                                :hasNull=true
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="">売掛現金区分</label>
                            <VueSelect
                                id="UriGenKbn"
                                :vmodel=viewModel
                                bind="売掛現金区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 1 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">郵便番号</label>
                            <input class="form-control p-2" style="width: 90px;" type="text"
                                v-model=viewModel.郵便番号
                                maxlength="8"
                                v-maxBytes=8
                            >
                        </div>
                        <div class="col-md-9">
                            <label>住所</label>
                            <input class="form-control" type="text" v-model=viewModel.住所１
                                maxlength="60"
                                v-maxBytes=60
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 offset-md-3">
                            <label></label>
                            <input class="form-control" type="text" v-model=viewModel.住所２
                                maxlength="60"
                                v-maxBytes=60
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">電話番号1</label>
                            <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.電話番号１ maxlength="15">
                        </div>
                        <div class="col-md-3">
                            <label class="">電話番号2</label>
                            <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.電話番号２ maxlength="15">
                        </div>
                        <div class="col-md-3">
                            <label class="slabel">FAX1</label>
                            <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.ＦＡＸ１ maxlength="15">
                        </div>
                        <div class="col-md-3">
                            <label class="slabel">FAX2</label>
                            <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.ＦＡＸ２ maxlength="15">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">得意先の担当者</legend>
                                <div class="row">
                                    <!-- TODO:画面に新規追加した項目のため入力制限未設定 -->
                                    <div class="col-md-3">
                                        <label class="">氏名</label>
                                        <input class="form-control p-1" type="text" :value=viewModel.得意先担当者氏名>
                                    </div>
                                    <div class="col-md-9">
                                        <label>部署</label>
                                        <input class="form-control p-1" type="text" :value=viewModel.得意先担当者部署>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>電話番号</label>
                                        <input class="form-control p-1" type="text" style="width: 130px;" :value=viewModel.得意先担当者電話番号 maxlength="15">
                                    </div>
                                    <div class="col-md-9">
                                        <label>メール</label>
                                        <input class="form-control p-1" type="text" :value=viewModel.得意先担当者メール>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">お届け先</legend>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">郵便番号</label>
                                        <input class="form-control p-2" style="width: 90px;" type="text"
                                            v-model=viewModel.お届け先郵便番号
                                            maxlength="8"
                                            v-maxBytes=8
                                        >
                                    </div>
                                    <div class="col-md-9">
                                        <label>住所</label>
                                        <input class="form-control" type="text"
                                            v-model=viewModel.お届け先住所１
                                            maxlength="60"
                                            v-maxBytes=60
                                        >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9 offset-md-3">
                                        <label></label>
                                        <input class="form-control" type="text"
                                            v-model=viewModel.お届け先住所２
                                            maxlength="60"
                                            v-maxBytes=60
                                        >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">電話番号1</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.お届け先電話番号１ maxlength="15">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">電話番号2</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.お届け先電話番号２ maxlength="15">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="slabel">FAX1</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.お届け先ＦＡＸ１ maxlength="15">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="slabel">FAX2</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text" v-model=viewModel.お届け先ＦＡＸ２ maxlength="15">
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-12">
                        <label class="slabel" style="margin-left:25px; text-align: left;">状態理由</label>
                        <VueSelect
                            id="StateReason"
                            :vmodel=viewModel
                            bind="状態理由区分"
                            uri="/Utilities/GetCodeList"
                            :params="{ cd: 36 }"
                            :withCode=true
                            customStyle="{ width: 100px; }"
                            :disabled='viewModel.状態区分 != "40"'
                        />
                    </div>
                    <div class="col-md-12">
                        <label class="slabel" style="margin-left:25px; text-align: left;">失客日</label>
                        <DatePickerWrapper
                            id="ShikkyakuDate"
                            ref="DatePicker_ShikkyakuDate"
                            format="YYYY年MM月DD日"
                            dayViewHeaderFormat="YYYY年MM月"
                            :vmodel=viewModel
                            bind="失客日"
                            :editable='viewModel.状態区分 == "40"'
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="row ml-0 mr-0">
            <div class="col-md-12">
                <fieldset class="shiharai-info w-100">
                    <legend class="shiharai-info">支払情報</legend>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">締区分</label>
                            <VueSelect
                                id="SimeKbn"
                                :vmodel=viewModel
                                bind="締区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 3 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">締日1</label>
                            <input class="form-control text-right p-2" style="width: 40px;" type="text"
                                v-model=viewModel.締日１
                                maxlength="2"
                                v-int
                            >
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">締日2</label>
                            <input class="form-control text-right p-2" style="width: 40px;" type="text"
                                v-model=viewModel.締日２
                                maxlength="2"
                                v-int
                            >
                        </div>
                        <div class="col-md-2">
                            <label class="">支払サイト</label>
                            <VueSelect
                                id="ShiharaiSite"
                                :vmodel=viewModel
                                bind="支払サイト"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 4 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">支払日</label>
                            <input class="form-control text-right p-2" style="width: 40px;" type="text"
                                v-model=viewModel.支払日
                                maxlength="2"
                                v-int
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9">
                            <label class="">請求先CD</label>
                            <PopupSelect
                                id="BillingSelect"
                                ref="PopupSelect_Billing"
                                :vmodel=viewModel
                                bind="請求先ＣＤ"
                                buddy="請求先名"
                                dataUrl="/Utilities/GetCustomerListForSelect"
                                :params="{ CustomerCd: null, KeyWord: BillingKeyWord }"
                                :isPreload=true
                                title="請求先一覧"
                                labelCd="請求先CD"
                                labelCdNm="請求先名"
                                :showColumns='[
                                ]'
                                :popupWidth=1000
                                :popupHeight=600
                                :isShowName=true
                                :isModal=true
                                :editable=true
                                :reuse=true
                                :existsCheck=true
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                :inputWidth=100
                                :nameWidth=400
                                :onChangeFunc=onBillingChanged
                                :isShowAutoComplete=true
                                :AutoCompleteFunc=BillingAutoCompleteFunc
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="">支払方法1</label>
                            <VueSelect
                                id="ShiharaiKind1"
                                :vmodel=viewModel
                                bind="支払方法１"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 6, sub1: 1 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="">支払方法2</label>
                            <VueSelect
                                id="ShiharaiKind2"
                                :vmodel=viewModel
                                bind="支払方法２"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 6, sub1: 2 }"
                                :withCode=true
                                :hasNull=true
                                customStyle="{ width: 100px; }"
                                :disabled='viewModel.支払方法１ != "1"'

                            />
                        </div>
                        <div class="col-md-2">
                            <label class="">税区分</label>
                            <VueSelect
                                id="TaxKbn"
                                :vmodel=viewModel
                                bind="税区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 20 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="">集金区分</label>
                            <VueSelect
                                id="ShukinKbn"
                                :vmodel=viewModel
                                bind="集金区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 5 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                                :disabled='viewModel.支払方法１ != "4"'
                            />
                        </div>
                        <div class="col-md-2">
                            <label>集金手数料</label>
                            <currency-input
                                class="form-control p-2 text-right" style="width: 80px;"
                                v-model=viewModel.集金手数料
                                :disabled='viewModel.支払方法１ != "4"'
                                maxlength="5"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="kouza-info w-100">
                                <legend class="kouza-info">口座情報</legend>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="">金融機関名</label>
                                        <PopupSelect
                                            id="BankSelect"
                                            ref="PopupSelect_Bank"
                                            :vmodel=viewModel
                                            bind="金融機関CD"
                                            buddy="金融機関名"
                                            dataUrl="/Utilities/GetBankList"
                                            :params="{ BankCd: null, KeyWord: BankKeyWord }"
                                            :SelectorParamsFunc=BankSelectorParamsFunc
                                            :isPreload=true
                                            title="金融機関一覧"
                                            labelCd="金融機関CD"
                                            labelCdNm="金融機関名"
                                            :showColumns='[
                                            ]'
                                            :popupWidth=600
                                            :popupHeight=600
                                            :isShowName=true
                                            :isModal=true
                                            :editable='viewModel.支払方法１ == "4"'
                                            :disabled='viewModel.支払方法１ != "4"'
                                            :reuse=true
                                            :existsCheck=true
                                            :inputWidth=100
                                            :nameWidth=235
                                            :onChangeFunc=onBankChanged
                                            :isShowAutoComplete=true
                                            :AutoCompleteFunc=BankAutoCompleteFunc
                                            :ParamsChangedCheckFunc="BankParamsChangedCheckFunc"
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="" >支店名</label>
                                        <PopupSelect
                                            id="BankBranchSelect"
                                            ref="PopupSelect_BankBranch"
                                            :vmodel=viewModel
                                            bind="金融機関支店CD"
                                            buddy="金融機関支店名"
                                            dataUrl="/Utilities/GetBankBranchList"
                                            :params="{ BankCd: viewModel.金融機関CD, BranchCd: null, KeyWord: BankBranchKeyWord }"
                                            :SelectorParamsFunc=BankBranchSelectorParamsFunc
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
                                            :editable='viewModel.金融機関CD ? viewModel.支払方法１ == "4" : false'
                                            :disabled='viewModel.支払方法１ != "4"'
                                            :reuse=true
                                            :existsCheck=true
                                            :inputWidth=100
                                            :nameWidth=235
                                            :onChangeFunc=onBankBranchChanged
                                            :isShowAutoComplete=true
                                            :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                            :ParamsChangedCheckFunc="BankBranchParamsChangedCheckFunc"
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="KigoNo">記号番号</label>
                                        <input class="form-control p-1" type="text" style="width: 130px;"
                                            maxlength="14"
                                            v-model=viewModel.記号番号
                                            :disabled='(viewModel.支払方法１ != "4") ? true : (viewModel.集金区分 == "2")'
                                            v-int
                                        >
                                    </div>
                                    <div class="col-md-2">
                                        <label class="">口座種別</label>
                                        <VueSelect
                                            id="KouzaKind"
                                            :vmodel=viewModel
                                            bind="口座種別"
                                            uri="/Utilities/GetCodeList"
                                            :params="{ cd: 7 }"
                                            :withCode=true
                                            :hasNull=false
                                            customStyle="{ width: 100px; }"
                                            :disabled='viewModel.支払方法１ != "4"'
                                        />
                                    </div>
                                    <div class="col-md-2">
                                        <label>口座番号</label>
                                        <input class="form-control p-1" style="text-align: right; width: 80px;" type="text"
                                            v-model=viewModel.口座番号
                                            :disabled='viewModel.支払方法１ != "4"'
                                            maxlength="7"
                                            v-int
                                        >
                                    </div>
                                    <div class="col-md-5">
                                        <label class="">口座名義人</label>
                                        <input class="form-control" type="text" style="font-size: 15px !important;"
                                            v-model=viewModel.口座名義人
                                            :disabled='viewModel.支払方法１ != "4"'
                                            maxlength="30"
                                            v-maxBytes="1000"
                                        >
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row ml-0 mr-0 mb-2">
            <fieldset class="fuzoku-info w-100">
                <legend class="fuzoku-info">付属情報</legend>
                <div class="row m-0">
                    <div class="col-md-9 d-block">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>チケット枚数</label>
                                    <input class="form-control text-right p-2" style="width: 50px;"
                                        v-model=viewModel.チケット枚数
                                        type="text"
                                        maxlength="3"
                                        v-int
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label style="width: 170px;">サービスチケット枚数</label>
                                    <input class="form-control text-right p-2" style="width: 50px;"
                                        v-model=viewModel.サービスチケット枚数
                                        type="text"
                                        maxlength="3"
                                        v-int
                                    />
                                </div>
                                <div class="col-md-3">
                                    <label class="">受注方法</label>
                                    <VueSelect
                                        id="JuchuKind"
                                        :vmodel=viewModel
                                        bind="受注方法"
                                        buddy="受注方法名称"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 23 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <label>発信時間</label>
                                    <DatePickerWrapper
                                        id="SendTime"
                                        ref="DatePicker_TakeoutTime"
                                        format="HH時mm分"
                                        dayViewHeaderFormat="YYYY年MM月"
                                        :vmodel=viewModel
                                        bind="発信時間"
                                        :editable=true
                                        customStyle="width: 80px;"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>営業担当者</label>
                                    <PopupSelect
                                        id="EigyoTanto"
                                        ref="PopupSelect_EigyoTanto"
                                        :vmodel=viewModel
                                        bind="営業担当者ＣＤ"
                                        buddy="営業担当者名"
                                        dataUrl="/Utilities/GetTantoListForSelect"
                                        :params="{ bushoCd: null, KeyWord: EigyoKeyWord }"
                                        :isPreload=true
                                        title="営業担当者一覧"
                                        labelCd="営業担当者ＣＤ"
                                        labelCdNm="営業担当者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=true
                                        :existsCheck=true
                                        :onChangeFunc=onEigyoTantoChanged
                                        :inputWidth=80
                                        :nameWidth=160
                                        :isShowAutoComplete=true
                                        :AutoCompleteFunc=EigyoTantoAutoCompleteFunc
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label>獲得営業者</label>
                                    <PopupSelect
                                        id="KakutokuEigyo"
                                        ref="PopupSelect_KakutokuEigyo"
                                        :vmodel=viewModel
                                        bind="獲得営業者ＣＤ"
                                        buddy="獲得営業者名"
                                        dataUrl="/Utilities/GetTantoListForSelect"
                                        :params="{ bushoCd: null, KeyWord: KakutokuKeyWord }"
                                        :isPreload=true
                                        title="獲得営業者一覧"
                                        labelCd="獲得営業者ＣＤ"
                                        labelCdNm="獲得営業者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=true
                                        :existsCheck=true
                                        :onChangeFunc=onKakutokuChanged
                                        :inputWidth=80
                                        :nameWidth=160
                                        :isShowAutoComplete=true
                                        :AutoCompleteFunc=KakutokuAutoCompleteFunc
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>登録担当者</label>
                                    <PopupSelect
                                        id="TorokuTanto"
                                        ref="PopupSelect_TorokuTanto"
                                        :vmodel=viewModel
                                        bind="登録担当者ＣＤ"
                                        buddy="登録担当者名"
                                        dataUrl="/Utilities/GetTantoListForSelect"
                                        :params="{ bushoCd: null, KeyWord: TorokuKeyWord }"
                                        :isPreload=true
                                        title="登録担当者一覧"
                                        labelCd="登録担当者ＣＤ"
                                        labelCdNm="登録担当者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=true
                                        :existsCheck=true
                                        :onChangeFunc=onTorokuChanged
                                        :inputWidth=80
                                        :nameWidth=160
                                        :isShowAutoComplete=true
                                        :AutoCompleteFunc=TorokuAutoCompleteFunc
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9">
                                    <label class="">受注顧客</label>
                                    <PopupSelect
                                        id="JuchuCustomerSelect"
                                        ref="PopupSelect_JuchuCustomer"
                                        :vmodel=viewModel
                                        bind="受注得意先ＣＤ"
                                        buddy="受注得意先名"
                                        dataUrl="/Utilities/GetCustomerListForSelect"
                                        :params="{ CustomerCd: null, KeyWord: JuchuCustomerKeyWord }"
                                        :isPreload=true
                                        title="得意先一覧"
                                        labelCd="得意先CD"
                                        labelCdNm="得意先名"
                                        :showColumns='[
                                        ]'
                                        :popupWidth=1000
                                        :popupHeight=600
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=true
                                        :existsCheck=true
                                        :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                                        :inputWidth=100
                                        :nameWidth=400
                                        :onChangeFunc=onJuchuCustomerChanged
                                        :isShowAutoComplete=true
                                        :AutoCompleteFunc=JuchuCustomerAutoCompleteFunc
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="">味噌汁</label>
                                    <VueSelect
                                        id="MisoKbn"
                                        :vmodel=viewModel
                                        bind="味噌汁区分"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 8 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">納品書発行</label>
                                    <VueSelect
                                        id="NouhinshoKbn"
                                        :vmodel=viewModel
                                        bind="納品書発行区分"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 9 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">請求書敬称</label>
                                    <VueSelect
                                        id="NouhinshoKbn"
                                        :vmodel=viewModel
                                        bind="請求書敬称"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 11 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="">ふりかけ</label>
                                    <VueSelect
                                        id="FurikakeKbn"
                                        :vmodel=viewModel
                                        bind="ふりかけ区分"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 8 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">空き容器回収</label>
                                    <VueSelect
                                        id="KaishuKbn"
                                        :vmodel=viewModel
                                        bind="空き容器回収区分"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 10 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">祝日配送</label>
                                    <VueSelect
                                        id="HolidayDeliveryKbn"
                                        :vmodel=viewModel
                                        bind="祝日配送区分"
                                        uri="/Utilities/GetCodeList"
                                        :params="{ cd: 22 }"
                                        :withCode=true
                                        customStyle="{ width: 100px; }"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <fieldset class="holiday-info w-100">
                                        <legend class="holiday-info">休日登録</legend>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <VueCheck v-for="dd in Weeks"
                                                    :key=dd
                                                    :id="'HolidayConfig_' + dd"
                                                    :ref="'HolidayConfig_' + dd"
                                                    :title="dd"
                                                    :vmodel=HolidayConfig
                                                    :bind="dd"
                                                    uri="/Utilities/GetCodeList"
                                                    :params="{ cd: 13 }"
                                                    :withCode=true
                                                    checkedCode="1"
                                                    customContainerStyle="border-style: groove; margin-right: 5px;"
                                                    customTitleStyle="width: 20px; justify-content: center;"
                                                    customContentStyle="width: 70px"
                                                />
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                    </div>
                    <div class="col-md-3 d-block">
                        <div class="row">
                            <div class="col-md-12 ">
                                <label class="ml-2" style="text-align: left;">顧客メモ</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考１
                                    v-maxBytes="80">
                                </textarea>
                            </div>
                            <div class="col-md-12" >
                                <label class="ml-2" style="text-align: left;">発信メモ</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考２
                                    v-maxBytes="80">
                                </textarea>
                            </div>
                            <div class="col-md-12" >
                                <label class="ml-2" style="text-align: left;">配送メモ</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考３
                                    v-maxBytes="80">
                                </textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label style="width: 85px;">誕生日１</label>
                            <input class="form-control p-2" type="text"
                                v-model=viewModel.誕生日１
                                maxlength="8"
                                v-int
                            >
                        </div>
                        <div class="col-md-2">
                            <label style="width: 85px;">誕生日２</label>
                            <input class="form-control p-2" type="text"
                                v-model=viewModel.誕生日２
                                maxlength="8"
                                v-int
                            >
                        </div>
                        <div class="col-md-4">
                            <label class="" style="width: 140px !important;">規定製造パターン</label>
                            <VueSelect
                                id="SeizoPattern"
                                :vmodel=viewModel
                                bind="既定製造パターン"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 35 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                            <div class="col-md-4">
                                <label class="" style="width:120px;">新規顧客登録日</label>
                                <DatePickerWrapper
                                    id="TorokuDate"
                                    ref="DatePicker_TorokuDate"
                                    format="YYYY年MM月DD日"
                                    dayViewHeaderFormat="YYYY年MM月"
                                    :vmodel=viewModel
                                    bind="新規登録日"
                                    :editable=true
                                />
                            </div>
                    </div>
                </div>
            </fieldset>
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
.memo{
    height: 60px;
}
.slabel{
    min-width: unset !important;
    width: 70px !important;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
<style>
#Page_DAI04041 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";
import VueSelectVue from '../Shared/VueSelect.vue';

export default {
    mixins: [PageBaseMixin],
    name: "DAI04041",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        Weeks: function() {
            return _.range(0, 7).map(v => moment().day(v).format("dd"));
        },
    },
    watch: {
        "viewModel.休日設定": {
            deep: true,
            handler: function(newVal) {
                console.log("viewModel.休日設定", newVal);
                var vue = this;

                if (!newVal) return;

                newVal.split("").forEach((v, i) => {
                    vue.HolidayConfig[moment().day(i).format("dd")] = v;
                });
            },
        },
        "viewModel.集金区分": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                console.log("viewModel.集金区分", newVal);
                var vue = this;
                if(!!newVal && newVal == "1"){
                    vue.viewModel.金融機関CD = "9900";
                }
                if(!!newVal && newVal == "2"){
                //TODO:集金区分=2を選んだ時
                    vue.viewModel.金融機関CD = "1";
                    //TODO:古いリストがはいってくる
                    vue.BankBranchParamsChangedCheckFunc();
                    vue.viewModel.金融機関支店CD = vue.$refs.PopupSelect_BankBranch.dataList[0].Cd;
                }
            },
        },
        "viewModel.金融機関CD": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関CD = "";
                }
            },
        },
        "viewModel.金融機関支店CD": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal == "0") {
                    vue.viewModel.金融機関支店CD = "";
                }
            },
        },
        "viewModel.サービスチケット枚数": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal) {
                    vue.viewModel.サービスチケット枚数 = vue.viewModel.サービスチケット枚数.replace(/^\./, "0.") ;
                }
            },
        },
        "viewModel.電話確認時間_時": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                if (newVal) {
                    vue.viewModel.発信時間 = ('0'+ vue.viewModel.電話確認時間_時).slice(-2) + ('00'+ vue.viewModel.電話確認時間_分).slice(-2);
                }
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "得意先マスタメンテ詳細",
            noViewModel: true,
            DAI04041Grid1: null,
            BillingKeyWord: null,
            BankKeyWord: null,
            BankBranchKeyWord: null,
            JuchuCustomerKeyWord: null,
            EigyoKeyWord: null,
            KakutokuKeyWord: null,
            TorokuKeyWord: null,
            HolidayConfig: {"日":"0","月":"0","火":"0","水":"0","木":"0","金":"0","土":"0"},
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

        //初期値
        data.viewModel.状態区分 = data.viewModel.状態区分 || "30";
        //支払方法２の入力制御のため、初期値設定
        data.viewModel.支払方法１ = data.viewModel.支払方法１ || "1";
        data.viewModel.税区分 = data.viewModel.税区分 || "1";
        data.viewModel.集金区分 = data.viewModel.集金区分 || "1"
        data.viewModel.金融機関CD = data.viewModel.金融機関CD || "9900";
        data.viewModel.金融機関支店CD = data.viewModel.金融機関支店CD || "";

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            if(this.params.IsNew == false || !this.params.IsNew){
                axios.post("/Utilities/GetCustomerList", {CustomerCd: vue.viewModel.得意先CD})
                    .then(res => {
                        vue.viewModel = res.data.Data[0];
                    })
                    .catch(err => {
                        console.log(error);
                        //TODO: エラー
                    }
                );
            }
            vue.footerButtons.push(
                { visible: "true", value: "空No検索", id: "DAI04041_SearchNo", disabled: false, shortcut: "F1",
                    onClick: function () {
                        //TODO: 空いている得意先ＣＤを検索する
                    }
                },
                { visible: "true", value: "クリア", id: "DAI04041_Clear", disabled: false, shortcut: "F2",
                    onClick: function (evt) {
                        //TODO: クリア
                        vue.clearDetail();
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "削除", id: "DAI04041_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "履歴表示", id: "DAI04040_History", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.showHistory();
                    }
                },
                { visible: "true", value: "分配先登録", id: "DAI04040_Bunpaisaki", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.showBunpaisaki();
                    }
                },
                {visible: "false"},
              　{ visible: "true", value: "登録", id: "DAI04041Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {

                        if(!vue.viewModel.得意先ＣＤ || !vue.viewModel.部署CD){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.得意先ＣＤ ? "得意先ＣＤが入力されていません。<br/>" : "",
                                    !vue.viewModel.部署CD ? "部署CDが入力されていません。<br/>" : ""
                                ]
                            })
                            if(!vue.viewModel.得意先ＣＤ){
                                $(vue.$el).find("#CustomerCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#CustomerCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.部署CD){
                                $(vue.$el).find(".BushoCd").addClass("has-error");
                            }else{
                                $(vue.$el).find(".BushoCd").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        //金融機関CD: nullの0置換
                        params.金融機関CD = params.金融機関CD || 0;
                        params.金融機関支店CD = params.金融機関支店CD || 0;

                        if(!!vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間){
                            params.電話確認時間_時 = Number(vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間.slice(0,2));
                            params.電話確認時間_分 = Number(vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間.slice(3,5));
                        }

                        //TODO:保存用日付書式
                        params.承認日 = !!params.承認日 ? moment(vue.viewModel.承認日,"YYYY-MM-DD").format("YYYY-MM-DD HH:mm:ss.SSS") : "";
                        params.失客日 = !!params.失客日 ? moment(vue.viewModel.失客日,"YYYY-MM-DD").format("YYYYMMDD") : "";
                        params.新規登録日 = !!params.新規登録日 ? moment(vue.viewModel.新規登録日,"YYYY-MM-DD").format("YYYY-MM-DD HH:mm:ss.SSS") : "";

                        params.修正担当者ＣＤ = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //TODO: 登録用controller method call
                        axios.post("/DAI04041/Save", params)
                            .then(res => {
                            })
                            .catch(err => {
                                console.log(error);
                                //TODO: エラー
                            }
                        );
                        console.log("登録", params);
                    }
                },
                {visible: "false"},
            );

            //初期値
            vue.viewModel.承認日 = vue.params.承認日 || moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.viewModel.新規登録日 = vue.params.新規登録日 || moment().format("YYYY-MM-DD HH:mm:ss.SSS");
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
        onCustomerCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByCustomerCd();
        },
        searchByCustomerCd: function() {
            var vue = this;
            var cd = vue.viewModel.得意先ＣＤ;
            if (!cd) return;

            var params = {CustomerCd: cd};
            params.noCache = true;

            axios.post("/DAI04041/GetCustomerListForDetail", params)
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
                                        vue.viewModel.得意先ＣＤ = "";
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                        });
                        $("[shortcut='F3']").prop("disabled", false);
                    }else{
                        //TODO:削除ボタン
                        $("[shortcut='F3']").prop("disabled", true);
                        return;
                    }
                })
                .catch(err => {
                    console.log(err);
                    //TODO: エラー
                }
            )
        },
        onBillingChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBillingChanged", info, comp, isValid);
            if (!isValid) {
                vue.BillingKeyWord = comp.selectValue;
            }
        },
        BillingAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BillingAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onJuchuCustomerChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onJuchuCustomerChanged", info, comp, isValid);
            if (!isValid) {
                vue.JuchuCustomerKeyWord = comp.selectValue;
            }
        },
        JuchuCustomerAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ", "備考１", "備考２", "備考３"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => k.match(/^[0-9\-]{6,}/) != null && !!v.電話番号１ ? v.電話番号１.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => (v.whole + (v.電話番号１ || "")).includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + "【" + v.部署名 + "】" + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("JuchuCustomerAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        BankSelectorParamsFunc: function(params, comp) {
            params.KeyWord = null;
            params.BankCd = null;
            return params;
        },
        BankBranchSelectorParamsFunc: function(params, comp) {
            params.KeyWord = null;
            params.BankCd = vue.viewModel.金融機関CD;
            return params;
        },
        onBankChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankChanged", info, comp, isValid);
            if (!isValid) {
                vue.BankKeyWord = comp.selectValue;
            }
        },
        BankAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
            console.log("BankAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onBankBranchChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankBranchChanged", info, comp, isValid);
            if (!isValid) {
                vue.BankBranchKeyWord = comp.selectValue;
            }
        },
        BankBranchAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BankBranchAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onEigyoTantoChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onEigyoTantoChanged", info, comp, isValid);
            if (!isValid) {
                vue.EigyoKeyWord = comp.selectValue;
            }
        },
        EigyoTantoAutoCompleteFunc: function(input, dataList, comp) {
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
            console.log("EigyoTantoAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onKakutokuChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onKakutokuChanged", info, comp, isValid);
            if (!isValid) {
                vue.KakutokuKeyWord = comp.selectValue;
            }
        },
        KakutokuAutoCompleteFunc: function(input, dataList, comp) {
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
            console.log("KakutokuAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        onTorokuChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onTorokuChanged", info, comp, isValid);
            if (!isValid) {
                vue.TorokuKeyWord = comp.selectValue;
            }
        },
        TorokuAutoCompleteFunc: function(input, dataList, comp) {
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
            console.log("TorokuAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        showHistory: function() {
            var vue = this;

            vue.showColumns = [
                    { title: "状態", dataIndx: "状態", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 0 },
                    { title: "承認日", dataIndx: "承認日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 1 },
                    { title: "承認者", dataIndx: "承認者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 2 },
                    { title: "状態理由", dataIndx: "状態理由", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 3 },
                    { title: "失客日", dataIndx: "失客日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 4 },
                    { title: "営業担当者", dataIndx: "営業担当者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 5 },
                    { title: "処理日", dataIndx: "処理日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 6 },
                    { title: "登録担当者", dataIndx: "登録担当者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 7 },
                    { title: "Cd", dataIndx: "Cd", dataType: "string", hidden: true, colIndx: 8 },
                    { title: "CdNm", dataIndx: "CdNm", dataType: "string", hidden: true, colIndx: 9 },
            ];

            //修正はparamsから、新規はviewmodelから、両方空なら表示不可とする
            var cds = !!vue.params.得意先CD ? vue.params.得意先CD : (!!vue.viewModel.得意先ＣＤ ? vue.viewModel.得意先ＣＤ : "");

            if(!cds){
                $.dialogErr({
                    title: "履歴表示不可",
                    contents: "得意先CDがありません。",
                })
                return;
            }

            PageDialog.showSelector({
                dataUrl: "/Utilities/GetCustomerHistoryList",
                params: {CustomerCd : cds },
                title: "得意先履歴一覧" + "【" + vue.viewModel.得意先ＣＤ + "：" + (!!vue.viewModel.得意先名 ? vue.viewModel.得意先名 : "") + "】",
                isModal: true,
                showColumns: vue.showColumns,
                width: 1000,
                height: 500,
                reuse: true,
            });
        },
        showBunpaisaki: function() {
            var vue = this;
            var cds;

            //修正はparamsから、新規はviewmodelから、両方空なら登録不可とする
            if (!!vue.params.得意先CD) {
                cds = { 得意先CD: vue.params.得意先CD, 部署CD: vue.params.部署CD };
            } else{
                if(!!vue.viewModel.得意先ＣＤ) {
                    cds = { 得意先CD: vue.viewModel.得意先ＣＤ, 部署CD: vue.viewModel.部署CD };
                } else{
                    cds = "";
                }
            }

            if(!cds){
                $.dialogErr({
                    title: "分配先登録不可",
                    contents: "得意先CDがありません。",
                })
                return;
            }

            //DAI04042を子画面表示
            PageDialog.show({
                pgId: "DAI04042",
                params: cds,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 600,
                height: 600,
            });
        },
        clearDetail: function(){
            var vue = this;

            $(vue.$el).find(".has-error").removeClass("has-error");

            _.keys(DAI04041.viewModel).forEach(k => DAI04041.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            vue.viewModel.承認日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.viewModel.新規登録日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.viewModel.状態区分 = "30";
            vue.viewModel.支払方法１ = "1";
            vue.viewModel.税区分 = "1";
            vue.viewModel.集金区分 = "1"
            // vue.viewModel.内外区分 = vue.viewModel.内外区分 || vue.$refs.NaigaiKbn_Select.entities[0].code;
            // vue.viewModel.現在使用FLG = vue.viewModel.現在使用FLG || vue.$refs.RiyoFlg_Select.entities[0].code;

        },
        BankParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!vue.viewModel.金融機関CD && vue.viewModel.金融機関CD != 0;
            return ret;
        },
        BankBranchParamsChangedCheckFunc: function(param) {
            var vue = this;
            var ret = !!vue.viewModel.金融機関CD && vue.viewModel.金融機関CD != 0;
            return ret;
        },

    }
}
</script>
