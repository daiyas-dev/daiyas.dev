<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ BushoCd: viewModel.BushoCd, CourseCd:null, KeyWord: null }"
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
                    :nameWidth=250
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>調整ID</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="AdjustmentID"
                    :vmodel=viewModel
                    bind="AdjustmentID"
                    uri="/DAI06030/SearchAdjustmentID"
                    :params="{ CustomerCd: viewModel.CustomerCd }"
                    :withCode=false
                    :onChangedFunc=onAdjustmentIDChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>調整日</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="AdjustmentDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="AdjustmentDate"
                    :editable=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>商品名</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-0 text-center label-blue" style="width: 300px;" type="text" :value=viewModel.ProductName readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: 90px;">チケット内数</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.TicketCount readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: 90px;">サービス内数</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.SVTicketCount readonly tabindex="-1">
            </div>
        </div>

        <div class="row">
            <div class="col-md-1">
            </div>
            <div class="col-md-1">
                <label>システム残</label>
            </div>
            <div class="col-md-1">
                <label>実残</label>
            </div>
            <div class="col-md-1">
                <label>差</label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: 90px;">チケット残数</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.TicketZanSystem readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.TicketZanJitsu>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.TicketZanSa readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label style="width: 90px;">サービス残数</label>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.SVTicketZanSystem readonly tabindex="-1">
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.SVTicketZanJitsu>
            </div>
            <div class="col-md-1">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.SVTicketZanSa readonly tabindex="-1">
            </div>
        </div>

        <div class="row">
            <div class="col-md-1">
                <label>調整理由</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="AdjustmentReason"
                    ref="VueOptions_AdjustmentReason"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="AdjustmentReason"
                    :list="[
                        {code: '1', name: '理由1', label: '理由1'},
                        {code: '2', name: '理由2', label: '理由2'},
                    ]"
                />
            </div>
        </div>
        <div class="row" :hidden='viewModel.AdjustmentReason != 2'>
            <div class="col-md-1">
                <label>買取額</label>
            </div>
            <div class="col-md-4">
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.Kingaku>円
            </div>
        </div>
    </form>
</template>

<style>
label{
    width: 80px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI06030",
    components: {
    },
    props: {
        query: Object,
        vm: Object,
    },
    computed: {
    },
    data() {
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "チケット > チケット残数調整",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                CustomerCd: null,
                AdjustmentID:null,
                AdjustmentDate: null,
                AdjustmentReason:"1",
                ProductCd:null,
                ProductName:null,
                TicketCount:null,
                SVTicketCount:null,
                TicketZanSystem:null,
                TicketZanJitsu:null,
                TicketZanSa:null,
                SVTicketZanSystem:null,
                SVTicketZanJitsu:null,
                SVTicketZanSa:null,
                Kingaku:null,
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "実行", id: "DAI06030Grid1_Save", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.Save();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //日付の初期値 -> 当日
            vue.viewModel.AdjustmentDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.InsatsuTantoCd=vue.getLoginInfo()["uid"];
        },
        onCustomerChanged: function(code, entities) {
            var vue = this;
            axios.post("/DAI06030/SearchCustomer", { BushoCd: vue.viewModel.BushoCd , CustomerCd: vue.viewModel.CustomerCd })
                .then(response => {
                    var resCustomer = _.cloneDeep(response.data[0]);
                    if(resCustomer.ProductData!=null && 0<resCustomer.ProductData.length)
                    {
                        var resProducts = _.cloneDeep(resCustomer.ProductData[0]);
                        vue.viewModel.ProductCd=resProducts.商品ＣＤ;
                        vue.viewModel.ProductName=resProducts.商品名;
                    }
                    else{
                        vue.viewModel.ProductCd="";
                        vue.viewModel.ProductName="";
                    }
                    if(resCustomer.TicketData!=null && 0<resCustomer.TicketData.length)
                    {
                        var resTickets = _.cloneDeep(resCustomer.TicketData[0]);
                        vue.viewModel.TicketCount=resTickets.チケット枚数*1;
                        vue.viewModel.SVTicketCount=(resTickets.サービスチケット枚数*1).toFixed(1);
                    }
                    else{
                        vue.viewModel.TicketCount="";
                        vue.viewModel.SVTicketCount="";
                    }
                });
        },
        onAdjustmentIDChanged: function(code, entities) {
            var vue = this;

            if(vue.viewModel.AdjustmentID==0)
            {
                vue.viewModel.TicketZanSystem=0;
                vue.viewModel.TicketZanJitsu=0;
                vue.viewModel.TicketZanSa=0;
                vue.viewModel.SVTicketZanSystem=0;
                vue.viewModel.SVTicketZanJitsu="0.0";
                vue.viewModel.SVTicketZanSa=0;

                vue.footerButtons.find(v => v.id == "DAI06030Grid1_Save").disabled = false;
            }
            else{
                axios.post("/DAI06030/SearchAdjustmentInfo", { AdjustmentID: vue.viewModel.AdjustmentID })
                    .then(response => {
                        vue.viewModel.TicketZanSystem=response.data.チケット減数;
                        vue.viewModel.TicketZanJitsu=0;
                        vue.viewModel.TicketZanSa=vue.viewModel.TicketZanSystem*1 - vue.viewModel.TicketZanJitsu*1;
                        vue.viewModel.SVTicketZanSystem=response.data.SV減数;
                        vue.viewModel.SVTicketZanJitsu="0.0";
                        vue.viewModel.SVTicketZanSa=vue.viewModel.SVTicketZanSystem*1 - vue.viewModel.SVTicketZanJitsu*1;

                        vue.footerButtons.find(v => v.id == "DAI06030Grid1_Save").disabled = false;
                    });
            }
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
                .filter(v => (!!vue.viewModel.BushoCd) ? (v.部署CD == vue.viewModel.BushoCd) : true)
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
        Save:function()
        {
            var vue=this;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }
            if(vue.viewModel.CustomerCd==null)
            {
                return;
            }
            if(vue.viewModel.AdjustmentDate==null)
            {
                return;
            }

            //理由が理由2の場合、金額を更新する。
            var Kingaku=0;
            if(vue.viewModel.AdjustmentReason==2)
            {
                Kingaku=vue.viewModel.Kingaku;
            }
            var AdjustmentDate=moment(vue.viewModel.AdjustmentDate,"YYYY年MM月DD日").format("YYYY/MM/DD");
            var ShuseiTantoCd=vue.getLoginInfo()["uid"];

            //登録実行
            console.log("save");
            //axios.post("/DAI06030/Save", { AdjustmentID: vue.viewModel.AdjustmentID })
            axios.post("/DAI06030/Save", {
                    AdjustmentID: vue.viewModel.AdjustmentID,
                    CustomerCd: vue.viewModel.CustomerCd,
                    AdjustmentDate: AdjustmentDate,
                    AdjustmentReason: vue.viewModel.AdjustmentReason,
                    ProductCd: vue.viewModel.ProductCd,
                    TicketZanSa: vue.viewModel.TicketZanSa,
                    SVTicketZanSa: vue.viewModel.SVTicketZanSa,
                    Kingaku: Kingaku,
                    ShuseiTantoCd:ShuseiTantoCd
                 })
                .then(response => {
                });
        },
    }
}
</script>
