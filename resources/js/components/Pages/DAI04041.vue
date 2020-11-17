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
                    ref="CustomerCd"
                    v-model=viewModel.得意先ＣＤ
                    autocomplete="off"
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
                                id="CustomerNm"
                                autocomplete="off"
                                v-model="viewModel.得意先名"
                                maxlength=60
                                v-maxBytes=60
                                v-setKana="res => {viewModel.得意先名カナ = (viewModel.得意先名カナ || '') + res.toString(); $forceUpdate();}"
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">得意先名カナ</label>
                            <input type="text" class="form-control" style="font-size: 15px !important;"
                                autocomplete="off"
                                v-model="viewModel.得意先名カナ"
                                maxlength=30
                            >
                        </div>
                        <div class="col-md-6">
                            <label class="">得意先名略称</label>
                            <input type="text" class="form-control" style="font-size: 15px !important;"
                                autocomplete="off"
                                v-model="viewModel.得意先名略称"
                                maxlength=20
                                v-maxBytes=30
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="" style="width:360px !important">スマフォ表示用得意先名称</label>
                            <input type="text" class="form-control"
                                autocomplete="off"
                                v-model="viewModel.得意先名スマホ用"
                                maxlength=15
                                v-maxBytes=30
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label style="">部署</label>
                            <VueSelect
                                id="BushoCd"
                                ref="BushoCdSelect"
                                :vmodel=viewModel
                                bind="部署CD"
                                uri="/Utilities/GetBushoList"
                                :params="{ cds: null }"
                                :withCode=true
                                :isShowInvalid=!viewModel.IsNew
                                customStyle="{ width: 100px; }"
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
                            <input class="form-control p-2" style="width: 90px;" type="tel"
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
                            <input class="form-control p-1" style="width: 130px;" type="tel"
                                v-model=viewModel.電話番号１ maxlength="15" v-maxBytes=15
                            >
                        </div>
                        <div class="col-md-3">
                            <label class="">電話番号2</label>
                            <input class="form-control p-1" style="width: 130px;" type="tel"
                                v-model=viewModel.電話番号２ maxlength="15" v-maxBytes=15
                            >
                        </div>
                        <div class="col-md-3">
                            <label class="slabel">FAX1</label>
                            <input class="form-control p-1" style="width: 130px;" type="tel"
                                v-model=viewModel.ＦＡＸ１ maxlength="15" v-maxBytes=15
                            >
                        </div>
                        <div class="col-md-3">
                            <label class="slabel">FAX2</label>
                            <input class="form-control p-1" style="width: 130px;" type="tel"
                                v-model=viewModel.ＦＡＸ２ maxlength="15" v-maxBytes=15
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">得意先の担当者</legend>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">氏名</label>
                                        <input class="form-control p-1" type="text"
                                            v-model=viewModel.窓口担当者名
                                            maxlength=30
                                            v-maxBytes=60
                                        >
                                    </div>
                                    <div class="col-md-9">
                                        <label>部署</label>
                                        <input class="form-control p-1" type="text"
                                            v-model=viewModel.窓口部署
                                            maxlength=30
                                            v-maxBytes=60
                                        >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>電話番号</label>
                                        <input class="form-control p-1" type="tel" style="width: 130px;"
                                            v-model=viewModel.窓口電話番号
                                            maxlength="15"  v-maxBytes=15
                                        >
                                    </div>
                                    <div class="col-md-9">
                                        <label>メール</label>
                                        <input class="form-control p-1" type="email"
                                            v-model=viewModel.窓口メール
                                            maxlength=254
                                        >
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
                                        <input class="form-control p-2" style="width: 90px;" type="tel"
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
                                        <input class="form-control p-1" style="width: 130px;" type="tel"
                                            v-model=viewModel.お届け先電話番号１ maxlength="15" v-maxBytes=15
                                        >
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">電話番号2</label>
                                        <input class="form-control p-1" style="width: 130px;" type="tel"
                                            v-model=viewModel.お届け先電話番号２ maxlength="15" v-maxBytes=15
                                        >
                                    </div>
                                    <div class="col-md-3">
                                        <label class="slabel">FAX1</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text"
                                            v-model=viewModel.お届け先ＦＡＸ１ maxlength="15" v-maxBytes=15
                                        >
                                    </div>
                                    <div class="col-md-3">
                                        <label class="slabel">FAX2</label>
                                        <input class="form-control p-1" style="width: 130px;" type="text"
                                            v-model=viewModel.お届け先ＦＡＸ２ maxlength="15" v-maxBytes=15
                                        >
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-3" style="align-items: start;">
                <div class="row">
                    <div class="col-md-12 mt-2 mb-2 ml-4 ml-4">
                        <button type="button" class="btn btn-primary mr-2" style="width:110px; height: 50px;" @click="showCourse">
                            コース表示
                        </button>
                        <button type="button" class="btn btn-primary mr-2" style="width:120px; height: 50px;" @click="showFreeCustomerCd">
                            空き番号表示
                        </button>
                    </div>
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
                    <div class="col-md-12">
                        <label class="mt-4 attention" style="width: -webkit-fill-available; margin-right:25px; text-align: right;">［番号］"-"無しで入力［区分］0:主,1:副</label>
                    </div>
                    <div class="col-md-12 align-items-start">
                        <PqGridWrapper
                            :id='"DAI04041Grid1" + (!!params ? _uid : "")'
                            ref="DAI04041Grid1"
                            dataUrl="/DAI04041/GetTelToCustList"
                            :query="{CustomerCd: this.viewModel.得意先ＣＤ}"
                            :SearchOnCreate=true
                            :SearchOnActivate=false
                            :options=this.grid1Options
                            :onAfterSearchFunc=onAfterSearchFunc
                            :autoToolTipDisabled=true
                            :autoEmptyRow=true
                            :autoEmptyRowCount=1
                            :autoEmptyRowFunc=autoEmptyRowFunc
                            :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
                            classes="ml-4 mr-4"
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
                            <input class="form-control text-right p-2" style="width: 40px;" type="text" @input="onSimebi1Changed"
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
                                :params="{ CustomerCd: null, KeyWord: BillingKeyWord, UserBushoCd: getLoginInfo().bushoCd }"
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
                                :exceptCheck="[{Cd: ''}, {Cd: '0'}, {Cd: viewModel.得意先ＣＤ}]"
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
                                            :onAfterChangedFunc=onBankChanged
                                            :isShowAutoComplete=true
                                            :AutoCompleteFunc=BankAutoCompleteFunc
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
                                            :onAfterChangedFunc=onBankBranchChanged
                                            :isShowAutoComplete=true
                                            :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                            :ParamsChangedCheckFunc=BankBranchParamsChangedCheckFunc
                                            :dataListReset=true
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
                                        id="ServiceTicket"
                                        v-model=viewModel.サービスチケット枚数
                                        type="text"
                                        maxlength="3"
                                        @change="onServiceTicketChanged"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <label class="">受注方法</label>
                                    <VueSelect
                                        id="JuchuKind"
                                        ref="JuchuKind"
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
                                        :isPreload=true
                                        title="営業担当者一覧"
                                        labelCd="営業担当者ＣＤ"
                                        labelCdNm="営業担当者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=false
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
                                        :isPreload=true
                                        title="獲得営業者一覧"
                                        labelCd="獲得営業者ＣＤ"
                                        labelCdNm="獲得営業者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=false
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
                                        :isPreload=true
                                        title="登録担当者一覧"
                                        labelCd="登録担当者ＣＤ"
                                        labelCdNm="登録担当者名"
                                        :showColumns='[
                                        ]'
                                        :isShowName=true
                                        :isModal=true
                                        :editable=true
                                        :reuse=false
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
                                        :params="{ CustomerCd: null, KeyWord: JuchuCustomerKeyWord, UserBushoCd: getLoginInfo().bushoCd }"
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
                                        :exceptCheck="[{Cd: ''}, {Cd: '0'}, {Cd: viewModel.得意先ＣＤ}]"
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
                                                    customTitleStyle="width: 25px; justify-content: center;"
                                                    customContentStyle="width: 80px"
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
                                <label class="ml-2" style="text-align: left;">備考(社内)</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考１
                                    v-maxBytes="100">
                                </textarea>
                            </div>
                            <div class="col-md-12" >
                                <label class="ml-2" style="text-align: left;">備考(配送)</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考２
                                    v-maxBytes="100">
                                </textarea>
                            </div>
                            <div class="col-md-12" >
                                <label class="ml-2" style="text-align: left;">備考(通知)</label>
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control ml-1 mr-1 p-1 memo" type="text" v-model=viewModel.備考３
                                    v-maxBytes="100">
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
form[pgid="DAI04041"] .pq-grid .DAI04041_toolbar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
form[pgid="DAI04041"] .pq-grid .DAI04041_toolbar .toolbar_button {
    width: 45px;
    height: 30px;
    padding: 0px;
    padding-left: 3px;
    padding-right: 3px;
    margin: 0px;
    display: flex;
    justify-content: center;
    align-items: center;
}
form[pgid="DAI04041"] .pq-grid .DAI04041_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI04041"] .pq-grid .DAI04041_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI04041"] .pq-grid .DAI04041_toolbar .toolbar_button.ope {
    width: 45px;
}
form[pgid="DAI04041"] .VueCheck.Checked * {
    color: red;
}
.attention{
    color: navy;
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
        "viewModel.得意先名": {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                var str = newVal || "";

                vue.viewModel.得意先名略称 = !!vue.viewModel.得意先名略称 ? vue.viewModel.得意先名略称 : str.slice(0,20);
                vue.viewModel.得意先名スマホ用 = !!vue.viewModel.得意先名スマホ用 ? vue.viewModel.得意先名スマホ用 : str.slice(0,20);
                // if (!!str) {
                //     vue.viewModel.得意先名スマホ用 = vue.viewModel.得意先名スマホ用 || "";
                //     window.getKana(str, res => {
                //         vue.viewModel.得意先名カナ = res;
                //     });
                // }
            },
        },
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

                if(!!vue.$refs.PopupSelect_Bank){

                    //銀行CD
                    if(newVal == "1"){
                        vue.$refs.PopupSelect_Bank.setSelectValue("9900");
                    }
                    if(newVal == "2"){
                        var cd = DAI04041.$refs.PopupSelect_Bank.dataList[0].Cd;
                        if (!!vue.viewModel.金融機関CD) {
                            vue.$refs.PopupSelect_Bank.setSelectValue(vue.viewModel.金融機関CD);
                        } else {
                            vue.$refs.PopupSelect_Bank.setSelectValue(cd);
                        }
                    }
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
                    vue.viewModel.サービスチケット枚数 = vue.viewModel.サービスチケット枚数.replace(/[^0-9\.]/, "") ;
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
                selectionModel: { type: "row", mode: "block", row: true},
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI04041_toolbar",
                    items: [
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRow(this, event);
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: true },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                height: 300,
                editable: true,
                columnTemplate: {
                    editable: true,
                    sortable: true,
                },
                trackModel: { on: false },
                historyModel: { on: true },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                sortModel: {
                    on: false,
                },
                editModel: {
                    clicksToEdit: 2,
                    keyUpDown: false,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: "nextFocus",
                    onTab: "nextFocus",
                },
                colModel: [
                    {
                        title: "電話番号",
                        dataIndx: "Tel_TelNo", dataType: "integer",
                        width: 150, maxWidth: 200, minWidth: 150,
                        editable: true,
                        key: true,
                        align: "left",
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "Tel_CusNo", dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "区分",
                        dataIndx: "Tel_RepFlg", dataType: "integer",
                        width: 50, maxWidth: 50, minWidth: 50,
                        editable: true,
                        align: "right",
                    },
                ],
                formulas: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, _.omit(vue.params, ["Parent"]), _.omit(vue.query, ["Parent"]));
        }

        //入力制御のため
        data.viewModel.支払方法１ = "";
        data.viewModel.集金区分 = "";
        //新規CD入力時の値の変更のため
        data.viewModel.受注方法 = "";
        data.viewModel.納品書発行区分 = "";
        data.viewModel.請求書敬称 = "";
        data.viewModel.空き容器回収区分 = "";
        data.viewModel.祝日配送区分 = "";

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            if(this.params.IsNew == false || !this.params.IsNew){
                //修正
                var param = {CustomerCd: vue.viewModel.得意先CD};
                param.noCache = true;
                axios.post("/Utilities/GetCustomerList", param)
                    .then(res => {
                        vue.viewModel = res.data.Data[0];

                        //currency-input項目、String->Number
                        vue.viewModel.集金手数料 = (vue.viewModel.集金手数料 || 0) * 1;

                    })
                    .catch(err => {
                        console.log(err);
                    }
                );
            } else {
                //新規
                //初期値
                vue.viewModel.状態区分 = "30";
                vue.viewModel.支払方法１ = "1";
                vue.viewModel.集金区分 = "1";
                vue.viewModel.税区分 = "1";
                vue.viewModel.営業担当者ＣＤ = "0";
                vue.viewModel.獲得営業者ＣＤ = "0";
                vue.viewModel.登録担当者ＣＤ = vue.getLoginInfo().uid;
                vue.viewModel.承認日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
                vue.viewModel.新規登録日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            }

            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04041_Clear", disabled: false, shortcut: "F2",
                    onClick: function (evt) {
                        vue.clearDetail();
                        console.log(vue.$attrs.id, evt.target.outerText, $(evt.target).attr("shortcut"));
                    }
                },
                { visible: "true", value: "削除", id: "DAI04041_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        var cd = vue.viewModel.得意先ＣＤ;
                        if(!cd) return;

                        var params = {CustomerCd: cd};
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
                                        axios.post("/DAI04041/Delete", params)
                                            .then(res => {
                                                DAI04040.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04041を閉じる
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
                        console.log("削除", params);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "履歴表示", id: "DAI04041_History", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.showHistory();
                    }
                },
                { visible: "true", value: "分配先登録", id: "DAI04041_Bunpaisaki", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.showBunpaisaki();
                    }
                },
                {visible: "false"},
                {visible: "false"},
              　{ visible: "true", value: "登録", id: "DAI04041_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.saveTokuisaki();
                    }
                },
                { visible: "true", value: "単価登録", id: "DAI04041_Tanka", disabled: false, shortcut: "F10",
                    onClick: function () {
                        vue.showTankaToroku();
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時；電話番号一覧検索
                var vue = this;
                vue.searchTelList();

                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
                $("[shortcut='F5']").prop("disabled", false);
                $("[shortcut='F6']").prop("disabled", false);
            }
        },
        updatedFunc: function(vue) {
            var vue = this;

            var inputs = $(vue.$el).find(":input:enabled:not([tabindex=-1]):not(button):not(textarea)");
            inputs.on("keypress", event => {
                if (event.which == 13) {
                    var idx = inputs.index($(event.target));
                    if (inputs.length == idx + 1) return;
                    inputs[idx + 1].focus();
                    return false;
                }

                return true;
            });
        },
        onCustomerCdChanged: function(code, entities) {
            var vue = this;

            vue.searchByCustomerCd();
        },
        onServiceTicketChanged: function(code, entities) {
            var vue = this;
            var regex = /^\d{1,3}$|\.0$|\.5$/;
            var str = vue.viewModel.サービスチケット枚数.replace(/\.$/, "") ;
            var result = regex.test(str);

            if(!str) {
                $(vue.$el).find("#ServiceTicket").removeClass("has-error");
                return;
            }

            if(!result){
                $.dialogErr({
                    title: "入力値エラー",
                    contents: "数値で入力してください。<br/>小数点は0.5単位で入力してください。",
                })
                $(vue.$el).find("#ServiceTicket").focus();
                $(vue.$el).find("#ServiceTicket").addClass("has-error");

                return;
            }else{
                $(vue.$el).find("#ServiceTicket").removeClass("has-error");
            }
        },
        onSimebi1Changed: function() {
            var vue = this;
            var Simebi1 = vue.viewModel.締日１;

            if(Simebi1 > 0) {
                vue.viewModel.売掛現金区分 = 1

            } //else if(Simebi1 == 0) {
                //vue.viewModel.売掛現金区分 = 0
            //}
        },
        searchTelList: function() {
            //電話番号一覧検索
            var vue = this;
            var grid = vue.DAI04041Grid1;
            console.log("qaws");

            var cd = !!vue.params.得意先CD ? vue.params.得意先CD : (!!vue.viewModel.得意先ＣＤ ? vue.viewModel.得意先ＣＤ: "");
            if(!cd) return;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var params = {CustomerCd: cd};
                grid.searchData(params, false);
            }
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
                                        $(vue.$el).find(".has-error").removeClass("has-error");
                                        vue.viewModel = res.data[0];
                                        vue.viewModel.IsNew = false;
                                        vue.params.IsNew = false; // 更新用に変更

                                        //currency-input項目、String->Number
                                        vue.viewModel.集金手数料 = (vue.viewModel.集金手数料 || 0 ) * 1;

                                        //電話番号一覧検索
                                        vue.searchTelList();

                                        $(this).dialog("close");

                                        //新規 -> 修正：　ボタン制御
                                        $("[shortcut='F3']").prop("disabled", false);
                                        $("[shortcut='F5']").prop("disabled", false);
                                        $("[shortcut='F6']").prop("disabled", false);
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
                    }else{
                        vue.setNewCustomerCd();
                    }
                })
                .catch(err => {
                    console.log(err);
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

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
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
            console.log("TorokuAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        showHistory: function() {
            var vue = this;

            //修正はparamsから、新規はviewmodelから、両方空なら表示不可とする
            var cds = !!vue.params.得意先CD ? vue.params.得意先CD : (!!vue.viewModel.得意先ＣＤ ? vue.viewModel.得意先ＣＤ : "");
            if(!cds){
                $.dialogErr({
                    title: "履歴表示不可",
                    contents: "得意先CDが入力されていません。",
                })
                return;
            }

            vue.showColumns = [
                    { title: "状態", dataIndx: "状態", dataType: "string", width: 80, maxWidth: 80, minWidth: 80, colIndx: 0 },
                    { title: "承認日", dataIndx: "承認日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 1 },
                    { title: "承認者", dataIndx: "承認者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 2 },
                    { title: "状態理由", dataIndx: "状態理由", dataType: "string", width: 150, maxWidth: 250, minWidth: 150, colIndx: 3 },
                    { title: "失客日", dataIndx: "失客日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 4 },
                    { title: "営業担当者", dataIndx: "営業担当者", dataType: "string", width: 100, maxWidth: 120, minWidth: 100, colIndx: 5 },
                    { title: "処理日", dataIndx: "処理日", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 6 },
                    { title: "登録担当者", dataIndx: "登録担当者", dataType: "string", width: 100, maxWidth: 150, minWidth: 100, colIndx: 7 },
                    { title: "Cd", dataIndx: "Cd", dataType: "string", hidden: true, colIndx: 8 },
                    { title: "CdNm", dataIndx: "CdNm", dataType: "string", hidden: true, colIndx: 9 },
            ];

            PageDialog.showSelector({
                dataUrl: "/Utilities/GetCustomerHistoryList",
                params: {CustomerCd : cds },
                title: "得意先履歴一覧" + "【" + vue.viewModel.得意先ＣＤ + "：" + (!!vue.viewModel.得意先名 ? vue.viewModel.得意先名 : "") + "】",
                isModal: true,
                showColumns: vue.showColumns,
                width: 1000,
                height: 500,
                reuse: false,
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
                    contents: "得意先CDが入力されていません。",
                })
                return;
            }

            //事前情報取得
            axios.all(
                [
                    //得意先リストの取得
                    axios.post("/DAI04042/GetCustomerListForSelect",  {CustomerCd: cds.得意先CD}),
                ]
            ).then(
                axios.spread((responseCustomer) => {
                    var resCustomer = responseCustomer.data;

                    var checkError = (res, name) => {
                        if (res.onError && !!res.errors) {
                            //メッセージリストに追加
                            Object.values(res.errors).filter(v => v)
                                .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                            //ダイアログ
                            $.dialogErr({ errObj: res });

                            return false;
                        } else if (res.onException) {
                            //メッセージ追加
                            vue.$root.$emit("addMessage", name +"リスト取得失敗(" + vue.page.ScreenTitle + ":" + res.message + ")");

                            //ダイアログ
                            $.dialogErr({
                                title: "異常終了",
                                contents: name +"リストの取得に失敗しました<br/>" + res.message,
                            });

                            return false;
                        } else if (res == "") {
                            //完了ダイアログ
                            $.dialogErr({
                                title: name +"リスト無し",
                                contents: "該当データは存在しません" ,
                            });

                            return false;
                        }

                        return true;
                    };

                    if (!checkError(resCustomer)) return;

                    //取得した結果を設定
                    var params = {};
                    params.CustomerList = resCustomer;
                    params.得意先CD = cds.得意先CD;
                    params.部署CD = cds.部署CD;

                    PageDialog.show({
                        pgId: "DAI04042",
                        params: params,
                        isModal: true,
                        isChild: true,
                        width: 600,
                        height: 600,
                        onBeforeClose: (event, ui) => {
                            console.log("onBeforeClose", event, ui);

                            if ($(window.event.target).attr("shortcut") == "ESC") return true;

                            var dlg = $(event.target);
                            var editting = dlg.find(".pq-grid")
                                .map((i, v) => $(v).pqGrid("getInstance").grid)
                                .get()
                                .some(g => !_.isEmpty(g.getEditCell()));
                            var isEscOnEditor = !!window.event && window.event.key == "Escape"
                                && (
                                    $(window.event.target).hasClass("target-input") ||
                                    $(window.event.target).hasClass("pq-cell-editor")
                                );

                            return !editting && !isEscOnEditor;
                        }
                    });
                })
            )
            .catch(error => {
                grid.hideLoading();

                //メッセージ追加
                vue.$root.$emit("addMessage", "DB検索失敗(" + vue.ScreenTitle + ":" + error + ")");

                //完了ダイアログ
                $.dialogErr({
                    title: "異常終了",
                    contents: "DBの検索に失敗しました<br/>",
                });
            });
        },
        showTankaToroku: function() {
            var vue = this;
            var cd;

            //修正はparamsから、新規はviewmodelから、両方空なら登録不可とする
            cd = vue.params.得意先CD || !!vue.viewModel.得意先ＣＤ ? vue.viewModel.得意先ＣＤ : "";
            if (!!vue.params.得意先CD) {
                cd = vue.params.得意先CD;
            } else{
                if(!!vue.viewModel.得意先ＣＤ) {
                    cd = vue.viewModel.得意先ＣＤ;
                } else{
                    cd = "";
                }
            }

            if(!cd || !vue.viewModel.得意先名){
                $.dialogErr({
                    title: "単価登録不可",
                    contents: [
                        !cd ? "得意先CDが入力されていません。<br/>" : "",
                        !vue.viewModel.得意先名 ? "得意先名が入力されていません。" : ""
                    ]
                })
                if(!cd){
                    $(vue.$el).find("#CustomerCd").addClass("has-error");
                }else{
                    $(vue.$el).find("#CustomerCd").removeClass("has-error");
                }
                if(!vue.viewModel.得意先名){
                    $(vue.$el).find("#CustomerNm").addClass("has-error");
                }else{
                    $(vue.$el).find("#CustomerNm").removeClass("has-error");
                }
                return;
            }

            //新規なら登録
            if(vue.viewModel.IsNew == true){
                vue.saveTokuisaki();
            }

            var params = {得意先CD: cd, 得意先名: vue.viewModel.得意先名}
            if (!!vue.params.Parent) {
                params.Parent = vue.params.Parent;
            }

            //単価登録画面
            //DAI04051を子画面表示
            PageDialog.show({
                pgId: "DAI04051",
                params: params,
                isModal: true,
                isChild: true,
                resizable: false,
                width: 880,
                height: 600,
                onBeforeClose: (event, ui) => {
                    console.log("onBeforeClose", event, ui);

                    if ($(window.event.target).attr("shortcut") == "ESC") return true;

                    var dlg = $(event.target);
                    var editting = dlg.find(".pq-grid")
                        .map((i, v) => $(v).pqGrid("getInstance").grid)
                        .get()
                        .some(g => !_.isEmpty(g.getEditCell()));
                    var isEscOnEditor = !!window.event && window.event.key == "Escape"
                        && (
                            $(window.event.target).hasClass("target-input") ||
                            $(window.event.target).hasClass("pq-cell-editor")
                        );

                    return !editting && !isEscOnEditor;
                }
            });
        },
        clearDetail: function(){
            var vue = this;

            $(vue.$el).find(".has-error").removeClass("has-error");
            var BusyoCd = vue.viewModel.部署CD;
            console.log("部署コード",BusyoCd);

            _.keys(DAI04041.viewModel).forEach(k => DAI04041.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            //初期値
            vue.viewModel.部署CD = BusyoCd;
            vue.viewModel.状態区分 = "30";
            vue.viewModel.支払方法１ = "1";
            vue.viewModel.集金区分 = "1";
            vue.viewModel.税区分 = "1";
            vue.viewModel.登録担当者ＣＤ = vue.getLoginInfo().uid;
            vue.viewModel.承認日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.viewModel.新規登録日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            vue.HolidayConfig = {"日":"0","月":"0","火":"0","水":"0","木":"0","金":"0","土":"0"};

            //電話番号一覧クリア
            var grid = this.DAI04041Grid1;
            grid.clearData();

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);
            $("[shortcut='F5']").prop("disabled", true);
            $("[shortcut='F6']").prop("disabled", true);
        },
        BankBranchParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0;
            return ret;
        },
        saveTokuisaki: function() {
            var vue = this;

            if(!vue.viewModel.得意先ＣＤ || !vue.viewModel.得意先名 || !vue.viewModel.売掛現金区分){
                $.dialogErr({
                    title: "登録不可",
                    contents: [
                        !vue.viewModel.得意先ＣＤ ? "得意先ＣＤが入力されていません。<br/>" : "",
                        !vue.viewModel.得意先名 ? "得意先名が入力されていません。<br/>" : "",
                        !vue.viewModel.売掛現金区分 ? "売掛現金区分が入力されていません。<br/>" : ""
                    ]
                })
                if(!vue.viewModel.得意先ＣＤ){
                    $(vue.$el).find("#CustomerCd").addClass("has-error");
                }else{
                    $(vue.$el).find("#CustomerCd").removeClass("has-error");
                }
                if(!vue.viewModel.得意先名){
                    $(vue.$el).find("#CustomerNm").addClass("has-error");
                }else{
                    $(vue.$el).find("#CustomerNm").removeClass("has-error");
                }

                //2020/09/18追加
                if(!vue.viewModel.売掛現金区分){
                    $(vue.$el).find("#UriGenKbn").addClass("has-error");
                }else{
                    $(vue.$el).find("#UriGenKbn").removeClass("has-error");
                }
                return;
            }

            var params = _.cloneDeep(vue.viewModel);
            params.noCache = true;

            //金融機関CD: nullの0置換
            params.金融機関CD = params.金融機関CD || 0;
            params.金融機関支店CD = params.金融機関支店CD || 0;

            if(!!vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間){
                params.電話確認時間_時 = Number(vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間.slice(0,2));
                params.電話確認時間_分 = Number(vue.$refs.DatePicker_TakeoutTime.vmodel.発信時間.slice(3,5));
            }else{
                params.電話確認時間_時 = 0;
                params.電話確認時間_分 = 0;
            }

            params.電話確認区分 = 0;
            params.締日１ = params.締日１ || 0;
            params.締日２ = params.締日２ || 0;
            params.支払日 = params.支払日 || 0;
            params.支払方法２ = params.支払方法１== "1" ? params.支払方法２ : null;
            params.記号番号 = params.集金区分 == "1" ? params.記号番号 : null;
            params.集金手数料 = params.集金手数料 || 0;
            params.チケット枚数 = params.チケット枚数 || 0;
            params.サービスチケット枚数 = params.サービスチケット枚数 || 0;
            params.営業担当者ＣＤ = params.営業担当者ＣＤ || 0;
            params.獲得営業者ＣＤ = params.獲得営業者ＣＤ || 0;
            params.登録担当者ＣＤ = params.登録担当者ＣＤ || params.userId;
            params.受注得意先ＣＤ = params.受注得意先ＣＤ || 0;
            params.配送ｸﾞﾙｰﾌﾟＣＤ = params.配送ｸﾞﾙｰﾌﾟＣＤ || 0;
            params.請求書区分別頁 = 0;
            params.請求内訳区分 = 0;
            params.ＷＥＢ表示区分 = 0;
            params.取扱区分 = 0;
            params.ＩＤ = "";
            params.パスワード = "";
            params.承認日 = !!params.承認日 ? moment(vue.viewModel.承認日,"YYYY-MM-DD").format("YYYY-MM-DD HH:mm:ss.SSS") : "";
            params.受注顧客区分 = "";
            params.休日設定 = _.values(vue.HolidayConfig).join().toString().replace(/,/g,"");
            params.新規登録日 = !!params.新規登録日 ? moment(vue.viewModel.新規登録日,"YYYY-MM-DD").format("YYYY-MM-DD HH:mm:ss.SSS") : "";
            params.税処理 = 0;
            params.失客日 = !!params.失客日 ? moment(vue.viewModel.失客日,"YYYY-MM-DD").format("YYYYMMDD") : "";

            params.修正担当者ＣＤ = vue.getLoginInfo().uid;
            params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

            if (!!params.得意先名カナ && params.得意先名カナ.length > 30) {
                params.得意先名カナ = params.得意先名カナ.slice(0,30);
            }

            //新規か修正か
            params.IsNew = params.IsNew || vue.params.IsNew;

            var Message = {
                "department_code": params.部署CD,
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

            //登録中ダイアログ
            var progressDlg = $.dialogProgress({
                contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 登録中…",
            });

            axios.post("/DAI04041/Save", params)
                .then(res => {
                    if(!!res.data.duplicate){
                        progressDlg.dialog("close");
                        var duplicate = res.data.duplicate;
                        $.dialogInfo({
                            title: "登録失敗",
                            contents: "得意先CD:" + duplicate + "が重複しています。",
                        });
                        vue.viewModel.得意先ＣＤ = "";
                    }else{
                        vue.viewModel = res.data.model;

                         var grid1 = vue.DAI04041Grid1;
                        // var tel1 = vue.viewModel.電話番号１.replace(/-/g,"");

                        // if (vue.isUniqueArray(grid1,tel1) == 0) {
                            //電話帳一覧と履歴を更新

                        //2020/10/28
                        res = grid1.pdata.map(v => {
                            return v;
                        });

                        var newrow = _.cloneDeep(res[0]);

                        if (!!params && !!params.電話番号１) {
                            var match = res.filter(v => (v.Tel_TelNo ? v.Tel_TelNo.replace(/-/g,"") : "") == vue.viewModel.電話番号１.replace(/-/g,""));
                            if(match.length == 0){   //電話番号１とリスト内の電話番号が重複していなければ登録
                                newrow.Tel_TelNo = vue.viewModel.電話番号１.replace(/-/g,"");
                                newrow.Tel_CustNo = null;
                                newrow.Tel_RepFlg = 0;
                                newrow.Tel_DelFlg = 0;
                                grid1.pdata.push(newrow);
                            }else{
                                console.log("重複");
                            };
                        }
                            vue.saveTelList();
                            vue.saveHistoryList();

                            grid1.pdata = _.sortBy(grid1.pdata, "Tel_TelNo");
                            grid1.refreshCM();
                            grid1.refresh();

                            progressDlg.dialog("close");

                            if (!!vue.params.Parent) {
                                if (vue.params.Parent.$attrs.pgId == "DAI04040") {
                                    vue.params.Parent.conditionChanged(true);
                                } else if (vue.params.Parent.$attrs.pgId == "DAI01030") {
                                    vue.params.Parent.updateCustomer();
                                } else if (!!vue.params.IsCTI) {
                                    vue.params.Parent.after04041(res.data.model);
                                }
                            }

                            //画面を閉じる
                            //$(vue.$el).closest(".ui-dialog-content").dialog("close");

                            vue.params.IsNew = false; // 新規登録後は更新モードに入る
                        // } else {
                        // progressDlg.dialog("close");
                        // $.dialogErr({
                        // title: "重複チェック",
                        // contents: "リスト内の電話番号が重複しています。",
                        // });
                        // }
                    }
                })
                .catch(err => {
                    progressDlg.dialog("close");
                    console.log(err);
                    $.dialogErr({
                        title: "異常終了",
                        contents: "登録に失敗しました<br/>",
                    });
                }
            );
        },
        //2020/10/15
        // isUniqueArray: function(SendGrid,sendTel) {
        //     var arr = _.cloneDeep(SendGrid.pdata)
        //     .filter(k=> !!k.Tel_TelNo).map(v => {
        //         return v.Tel_TelNo;
        //     })
        //     var ret = _.filter(arr, (val, i, iteratee) => _.includes(iteratee, val, i + 1));
        //     return ret.length;

        // },

        showCourse: function() {
            var vue = this;
            var cds = vue.viewModel.得意先ＣＤ;
            if(!cds){
                $.dialogErr({
                    title: "コース検索表示不可",
                    contents: "得意先CDが入力されていません。",
                })
                return;
            }

            vue.showColumns = [
                    { title: "区分", dataIndx: "コース区分", dataType: "string", width: 50, maxWidth: 50, minWidth: 50, colIndx: 0 },
                    { title: "区分名称", dataIndx: "各種名称", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 1 },
                    { title: "コースCD", dataIndx: "コースＣＤ", dataType: "string", width: 90, maxWidth: 90, minWidth: 90, colIndx: 2 },
                    { title: "コース名", dataIndx: "コース名", dataType: "string", width: 250, maxWidth: 300, minWidth: 200, colIndx: 3 },
                    { title: "Cd", dataIndx: "Cd", dataType: "string", hidden: true, colIndx: 4 },
                    { title: "CdNm", dataIndx: "CdNm", dataType: "string", hidden: true, colIndx: 5 },
            ];

            var params = {CustomerCd: cds, BushoCd: vue.viewModel.部署CD}
            PageDialog.showSelector({
                dataUrl: "/DAI04041/GetCourseListForCustomer",
                params: params,
                title: "コース検索",
                isModal: true,
                showColumns: vue.showColumns,
                width: 600,
                height: 400,
                reuse: true,
            });
        },
        showFreeCustomerCd: function() {
            var vue = this;
            var cds = vue.viewModel.得意先ＣＤ;
            if(!!cds) return;

            //DAI04043を子画面表示
            PageDialog.show({
                pgId: "DAI04043",
                params: {
                    setCustomerCd: cd => {
                        vue.viewModel.得意先ＣＤ = cd;
                        setTimeout(() => vue.setNewCustomerCd(), 100);
                    }
                },
                isModal: true,
                isChild: true,
                resizable: false,
                width: 380,
                height: 380,
            });
        },
        setNewCustomerCd: function() {
            var vue = this;
            var cd = vue.viewModel.得意先ＣＤ;

            //入力した得意先ＣＤを反映
            vue.$refs.PopupSelect_Billing.exceptCheck.push({Cd: cd})
            vue.$refs.PopupSelect_Billing.setSelectValue(cd);
            vue.$refs.PopupSelect_JuchuCustomer.exceptCheck.push({Cd: cd})
            vue.$refs.PopupSelect_JuchuCustomer.setSelectValue(cd);

            vue.viewModel.受注方法 = "2";
            vue.viewModel.納品書発行区分 = "1";
            vue.viewModel.空き容器回収区分 = "2";
            vue.viewModel.祝日配送区分 = "1";
            vue.viewModel.請求書敬称 = "2";
            vue.HolidayConfig.日 = "1";

            return;
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            res = res.map(v => {
                return v;
            });

            // if (!!vue.params && !!vue.params.電話番号１) {
            //     var match = res.filter(v => v.Tel_TelNo.replace(/-/g,"") == vue.params.電話番号１.replace(/-/g,""));
            //     if (match.length == 0) {
            //         res.push(
            //             {
            //                 Tel_TelNo:  vue.params.電話番号１.replace(/-/g,""),
            //                 Tel_CustNo: null,
            //                 Tel_RepFlg: 0,
            //                 Tel_DelFlg: 0,
            //             }
            //         );
            //     }
            // }

            return res;
        },
        saveTelList: function() {
            var vue = this;
            var grid = vue.DAI04041Grid1;

            //電話番号の値がないものは除外、データの整形。
            var saveList = _.cloneDeep(grid.getPlainPData().filter(v => !!v.Tel_TelNo));
            saveList.forEach((v, i) => {
                v.Tel_CustNo = vue.viewModel.得意先ＣＤ;
                v.Tel_RepFlg = v.Tel_RepFlg || 0;
                v.Tel_DelFlg = 0;
                v.Tel_NewDate = moment().format("YYYY/MM/DD");
                v.Tel_UpdDate = moment().format("YYYY/MM/DD");
            });

            var params = { CustomerCd: vue.viewModel.得意先ＣＤ, SaveList: saveList};

            //登録用controller method call
            axios.post("/DAI04041/SaveTelList", params)
                .then(res => {
                })
                .catch(err => {
                    console.log(err);
                }
            );
            console.log("登録", params);

        },
        deleteRow: function(grid, event) {
            var vue = this;

            grid = grid || vue.DAI04041Grid1;

            //選択行なし
            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
            grid.deleteRow({ rowList: rowList });
        },
        autoEmptyRowFunc: function(grid) {

            return {
                "Tel_TelNo": "",
                "Tel_CustNo": "",
                "Tel_RepFlg": "",
            };
        },
        autoEmptyRowCheckFunc: function(rowData){
            //電話番号が未入力の行かどうか
            return !rowData["Tel_TelNo"];

        },
        saveHistoryList: function() {
            var vue = this;
            if(!vue.viewModel.得意先ＣＤ) return;
            if(!vue.query.userId) return;

            var params = {};
            params.得意先ＣＤ = vue.viewModel.得意先ＣＤ;
            params.状態区分 = vue.viewModel.状態区分 || "";
            params.失客理由 = vue.viewModel.状態理由 || "";
            params.失客日 = !!vue.viewModel.失客日 ? moment(vue.viewModel.失客日,"YYYY-MM-DD").format("YYYYMMDD") : "";
            params.承認日 = !!vue.viewModel.承認日 ? moment(vue.viewModel.承認日,"YYYY-MM-DD").format("YYYYMMDD") : "";
            params.承認者ＣＤ = vue.viewModel.承認者ＣＤ || "";
            params.登録日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS");
            params.営業担当者ＣＤ = vue.viewModel.営業担当者ＣＤ || "";
            params.変更者ＣＤ = vue.query.userId;

            //履歴更新用controller method call
            axios.post("/DAI04041/UpdateHistoryList", params)
                .then(res => {
                })
                .catch(err => {
                    console.log(err);
                }
            );
            console.log("履歴更新", params);

        },
    },
}
</script>
