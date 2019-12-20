<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
            <div class="col-md-2">
                <label>得意先ＣＤ</label>
                <input class="form-control text-right" type="text"
                    :value=viewModel.得意先ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
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
                    uri="/Utilities/GetTantoList"
                    :params="{ bushoCd: null }"
                    :withCode=true
                    customStyle="{ width: 150px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <fieldset class="kihon-info w-100">
                    <legend class="kihon-info">基本情報</legend>
                    <div class="row">
                        <div class="col-md-10">
                            <label class="">得意先名</label>
                            <input type="text" class="form-control" :value="viewModel.得意先名">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="">得意先名カナ</label>
                            <input type="text" class="form-control" style="font-size: 15px !important;" :value="viewModel.得意先名カナ">
                        </div>
                        <div class="col-md-6">
                            <label class="">得意先名略称</label>
                            <input type="text" class="form-control" :value="viewModel.得意先名略称">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="" style="width:360px !important">スマフォ表示用得意先名称</label>
                            <input type="text" class="form-control" :value="viewModel.スマフォ表示用得意先名名称">
                        </div>
                    </div>
                    <div class="row">
                        <!-- <div class="col-md-5">
                            <label>部署</label>
                            <VueSelectBusho　/>
                        </div> -->
                        <div class="col-md-5">
                            <label style="width:90px">部署</label>
                            <VueSelect
                                id="BushoCd"
                                :vmodel=viewModel
                                bind="部署CD"
                                uri="/Utilities/GetBushoList"
                                :params="{ cds: null }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                            />
                        </div>
                        <div class="col-md-5">
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
                            <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.郵便番号>
                        </div>
                        <div class="col-md-9">
                            <label>住所</label>
                            <input class="form-control" type="text" :value=viewModel.住所１>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 offset-md-3">
                            <label></label>
                            <input class="form-control" type="text" :value=viewModel.住所２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="">電話番号1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号１>
                        </div>
                        <div class="col-md-3">
                            <label class="">電話番号2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.電話番号２>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX1</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ１>
                        </div>
                        <div class="col-md-3">
                            <label class="">FAX2</label>
                            <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.ＦＡＸ２>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <fieldset class="delivery-info w-100">
                                <legend class="delivery-info">得意先の担当者</legend>
                                <div class="row">
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
                                        <input class="form-control p-1" type="text" style="width: 100px;" :value=viewModel.得意先担当者電話番号>
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
                                        <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.お届け先郵便番号>
                                    </div>
                                    <div class="col-md-9">
                                        <label>住所</label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所１>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9 offset-md-3">
                                        <label></label>
                                        <input class="form-control" type="text" :value=viewModel.お届け先住所２>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="">電話番号1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">電話番号2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先電話番号２>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX1</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ１>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">FAX2</label>
                                        <input class="form-control p-1" style="width: 100px;" type="text" :value=viewModel.お届け先ＦＡＸ２>
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
                        <label class="" style="margin-left:30px; text-align: left;">状態理由</label>
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
                        <label class="" style="margin-left:30px; text-align: left;">失客日</label>
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
        <div class="row">
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
                            <input class="form-control text-right p-2" style="width: 40px;" type="text" :value=viewModel.締日１>
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 60px;">締日2</label>
                            <input class="form-control text-right p-2" style="width: 40px;" type="text" :value=viewModel.締日２>
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
                            <input class="form-control text-right p-2" style="width: 40px;" type="text" :value=viewModel.支払日>
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
                        <div class="col-md-3">
                            <label class="">集金区分</label>
                            <VueSelect
                                id="ShiharaiKind1"
                                :vmodel=viewModel
                                bind="集金区分"
                                uri="/Utilities/GetCodeList"
                                :params="{ cd: 5 }"
                                :withCode=true
                                customStyle="{ width: 100px; }"
                                :disabled='viewModel.支払方法１ != "4"'
                            />
                        </div>
                        <div class="col-md-3">
                            <label>集金手数料</label>
                            <input class="form-control text-right" type="text" :value=viewModel.集金手数料 :disabled='viewModel.支払方法１ != "4"'>
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
                                            :reuse=true
                                            :existsCheck=true
                                            :inputWidth=100
                                            :nameWidth=235
                                            :onChangeFunc=onBankChanged
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
                                            :editable='viewModel.支払方法１ == "4"'
                                            :reuse=true
                                            :existsCheck=true
                                            :inputWidth=100
                                            :nameWidth=235
                                            :ParamsChangedCheckFunc=BankBranchParamsChangedCheckFunc
                                            :onChangeFunc=onBankBranchChanged
                                            :isShowAutoComplete=true
                                            :AutoCompleteFunc=BankBranchAutoCompleteFunc
                                        />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>記号番号</label>
                                        <input class="form-control p-1" style="min-width: 125px;" type="text" :value=viewModel.記号番号 :disabled='viewModel.支払方法１ != "4"'>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">口座種別</label>
                                        <VueSelect
                                            id="KouzaKind"
                                            :vmodel=viewModel
                                            bind="口座種別"
                                            uri="/Utilities/GetCodeList"
                                            :params="{ cd: 7 }"
                                            :withCode=true
                                            :hasNull=true
                                            customStyle="{ width: 100px; }"
                                            :disabled='viewModel.支払方法１ != "4"'
                                        />
                                    </div>
                                    <div class="col-md-3">
                                        <label>口座番号</label>
                                        <input class="form-control p-1" style="min-width: 125px;" type="text" :value=viewModel.口座番号 :disabled='viewModel.支払方法１ != "4"'>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="">口座名義人</label>
                                        <input class="form-control" type="text" style="font-size: 15px !important;" :value=viewModel.口座名義人 :disabled='viewModel.支払方法１ != "4"'>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <div class="row">
            <fieldset class="fuzoku-info w-100">
                <legend class="fuzoku-info">付属情報</legend>
                <div class="row">
                    <div class="col-md-9 d-block">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>チケット枚数</label>
                                    <input class="form-control text-right p-2" style="width: 80px;" type="text" :value=viewModel.チケット枚数>
                                </div>
                                <div class="col-md-3">
                                    <label style="width: 170px;">サービスチケット枚数</label>
                                    <input class="form-control text-right p-2" style="width: 40px;" type="text" :value=viewModel.サービスチケット枚数>
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
                                <div class="col-md-4">
                                    <label class="">営業担当者</label>
                                    <VueSelect
                                        id="EigyoTantoCd"
                                        :vmodel=viewModel
                                        bind="営業担当者ＣＤ"
                                        buddy="営業担当者名"
                                        uri="/Utilities/GetTantoList"
                                        :params="{ bushoCd: null }"
                                        :withCode=true
                                        customStyle="{ width: 150px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">獲得営業者</label>
                                    <VueSelect
                                        id="KakutokuEigyoCd"
                                        :vmodel=viewModel
                                        bind="獲得営業者ＣＤ"
                                        buddy="獲得営業者名"
                                        uri="/Utilities/GetTantoList"
                                        :params="{ bushoCd: null }"
                                        :withCode=true
                                        customStyle="{ width: 150px; }"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label class="">登録担当者</label>
                                    <VueSelect
                                        id="TourokuTantoCd"
                                        :vmodel=viewModel
                                        bind="登録担当者ＣＤ"
                                        buddy="登録担当者名"
                                        uri="/Utilities/GetTantoList"
                                        :params="{ bushoCd: null }"
                                        :withCode=true
                                        customStyle="{ width: 150px; }"
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
                            <div class="col-md-12">
                                <label style="text-align: center;">顧客メモ</label>
                                <textarea class="form-control mr-1 p-1 memo" type="text" :value=viewModel.備考１>
                                </textarea>
                            </div>
                            <div class="col-md-12">
                                <label style="">発信メモ</label>
                                <textarea class="form-control mr-1 p-1 memo" type="text" :value=viewModel.備考２>
                                </textarea>
                            </div>
                            <div class="col-md-12">
                                <label style="">配送メモ</label>
                                <textarea class="form-control mr-1 p-1 memo" type="text" :value=viewModel.備考３>
                                </textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label style="min-width: 70px;">誕生日１</label>
                            <input class="form-control p-2" type="text" :value=viewModel.誕生日１>
                        </div>
                        <div class="col-md-2">
                            <label style="min-width: 70px;">誕生日２</label>
                            <input class="form-control p-2" type="text" :value=viewModel.誕生日２>
                        </div>
                        <div class="col-md-4">
                            <label class="" style="width:120px !important">規定製造パターン</label>
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
    height: 70px;
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

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            axios.post("/Utilities/GetCustomerList", {CustomerCd: vue.viewModel.得意先CD})
                .then(res => {
                    vue.viewModel = res.data.Data[0];
                })
                .catch(err => {
                    console.log(error);
                    //TODO: エラー
                });
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04041_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04041_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                {visible: "false"},
                {visible: "false"},
              　{ visible: "true", value: "登録", id: "DAI04041Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var params = _.cloneDeep(vue.viewModel);

                        //金融機関CD: nullの0置換
                        params.金融機関CD = params.金融機関CD || 0;
                        params.金融機関支店CD = params.金融機関支店CD || 0;

                        var vue = this;
                        params.電話確認時間_時 = vue.viewModel.発信時間.slice(0,1);
                        params.電話確認時間_分 = vue.viewModel.発信時間.slice(3,4);

                        //TODO: 登録用controller method call
                        console.log("登録", params);
                    }
                },
                { visible: "true", value: "CSV", id: "DAI04041_Csv", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: CSV
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
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
        BankBranchParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd && newVal.BankCd != 0 ;
            console.log("BankBranchParamsChangedCheckFunc", newVal, ret);
            return ret;
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
    }
}
</script>
