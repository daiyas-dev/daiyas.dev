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
                    :withCode=true
                    :hasNull=true
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
                    :onChangedFunc=onAdjustmentDateChanged
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
                    :onChangedFunc=onChanged
                />
            </div>
        </div>
        <div class="row" :hidden='viewModel.AdjustmentReason != 2'>
            <div class="col-md-1">
                <label>買取額</label>
            </div>
            <div class="col-md-4">
                <input type="number"
                    v-model="viewModel.ValidPeriod"
                    style="width: 100px; padding-right: 0px; text-align: center; border: none; border-radius: 4px;"
                    maxlength="6"
                    min="0"
                    @input="onValidPeriodChanged"
                >円
            </div>
        </div>
        <PqGridWrapper
            id="DAI06030Grid1"
            ref="DAI06030Grid1"
            dataUrl="/DAI06030/Search"
            :query=this.viewModel
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=this.grid1Options
            :onAfterSearchFunc=this.onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style>
#DAI06030Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06030Grid1 .pq-group-icon {
    display: none !important;
}
#DAI06030Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI06030Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI06030Grid1 .pq-td-div span {
    line-height: inherit;
    text-align: center;
}
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
                InsatsuMaisu:"5",
                InsatsuTantoCd:null,
                LastTicketNo:null,
                AdjustmentDate: null,
                IsPrintAdjustmentDate:"1",
                ValidPeriod:"12",
                ExpireDate: null,
                IsPrintExpireDate:"1",
                AdjustmentReason:"1",
                AdjustmentID:null,
                ProductName:null,
                TicketCount:null,
                SVTicketCount:null,
                TicketZanSystem:null,
                TicketZanJitsu:null,
                TicketZanSa:null,
                SVTicketZanSystem:null,
                SVTicketZanJitsu:null,
                SVTicketZanSa:null,
            },
            DAI06030Grid1: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 40,
                rowHt: 30,
                freezeCols: 0,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "remote",
                },
                groupModel: {
                    on: false,
                },
                summaryData: [
                ],
                formulas: [
                ],
                colModel: [
                    {
                        title: "",
                        dataIndx: "商品ＣＤ", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "システム残",
                        dataIndx: "システム残", dataType: "string",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "実残",
                        dataIndx: "実残",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "差",
                        dataIndx: "差",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI06030Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "実行", id: "DAI06030Grid1_Save", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.Save();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //日付の初期値 -> 当日
            //TODO:
            // vue.viewModel.AdjustmentDate = moment().format("YYYY年MM月DD日");
            vue.viewModel.AdjustmentDate = moment("20190903").format("YYYY年MM月DD日");
            vue.viewModel.InsatsuTantoCd=vue.getLoginInfo()["uid"];
            vue.viewModel.AdjustmentID="677";
        },
        onChanged: function(code, entities) {
            //TODO:開発用ダミー関数
        },
        onAdjustmentDateChanged: function(code, entities) {
        },
        onValidPeriodChanged: function(code, entities) {
            console.log("onValidPeriodChanged!");
            var vue = this;
            window.respc=_.cloneDeep(vue.viewModel);//TODO:
            //console.log("ValidPeriod="+vue.viewModel.ValidPeriod);
            this.UpdateExpireDate();
        },
        onCustomerChanged: function(code, entities) {
            var vue = this;
                axios.post("/DAI06030/SearchAdjustmentID", { CustomerCd: vue.viewModel.CustomerCd })
                    .then(response => {
                        var res = _.cloneDeep(response.data);
                        //TODO:調整IDプルダウンを更新する;
                        axios.post("/DAI06030/SearchCustomer", { BushoCd: vue.viewModel.BushoCd , CustomerCd: vue.viewModel.CustomerCd })
                            .then(response => {
                                var resCustomer = _.cloneDeep(response.data[0]);
                                if(resCustomer.ProductData!=null && 0<resCustomer.ProductData.length)
                                {
                                    var resProducts = _.cloneDeep(resCustomer.ProductData[0]);
                                    vue.viewModel.ProductName=resProducts.商品名;
                                }
                                else{
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
                    });
        },
        onAdjustmentIDChanged: function(code, entities) {
            var vue = this;
            axios.post("/DAI06030/SearchAdjustmentInfo", { AdjustmentID: vue.viewModel.AdjustmentID })
                .then(response => {
                    window.resb=_.cloneDeep(response);//TODO:
                    vue.viewModel.TicketZanSystem=response.data.チケット減数;
                    vue.viewModel.TicketZanJitsu=0;
                    vue.viewModel.TicketZanSa=vue.viewModel.TicketZanSystem*1 - vue.viewModel.TicketZanJitsu*1;
                    vue.viewModel.SVTicketZanSystem=response.data.SV減数;
                    vue.viewModel.SVTicketZanJitsu="0.0";
                    vue.viewModel.SVTicketZanSa=vue.viewModel.SVTicketZanSystem*1 - vue.viewModel.SVTicketZanJitsu*1;
                });
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06030Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            if (!vue.viewModel.CustomerCd) return;
            var params = $.extend(true, {}, vue.viewModel);
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            console.log("onAfterSearchFunc");//TODO:
            window.resres=_.cloneDeep(res);//TODO:
            vue.viewModel.LastTicketNo=res[0].チケット管理番号 == null ? 0 : res[0].チケット管理番号;
            return res;
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
        UpdateExpireDate:function()
        {
            //発行日付と有効期限から有効期限日を求める
            var vue = this;
            vue.viewModel.ExpireDate = moment(vue.viewModel.AdjustmentDate,"YYYY年MM月DD日").add(vue.viewModel.ValidPeriod,'month').add(-1,'day').format("YYYY年MM月DD日");
        },
        Save:function()
        {
            console.log("save");//TODO:
            var vue=this;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }
            if(vue.viewModel.CustomerCd==null)
            {
                return;
            }

            //登録データの作成
            /*
            var SaveList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.商品ＣＤ=r.商品ＣＤ;
                SaveItem.単価=r.単価;
                SaveList.push(SaveItem);
            });
            */

            //登録実行
            axios.post("/DAI06030/Save", { AdjustmentID: vue.viewModel.AdjustmentID })
                .then(response => {
                });
            /*
            grid.saveData(
                {
                    uri: "/DAI06030/Save",
                    params: {
                        InsatsuMaisu:vue.viewModel.InsatsuMaisu,
                        CustomerCd:vue.viewModel.CustomerCd,
                        TicketNo:(vue.viewModel.LastTicketNo*1)+1,
                        AdjustmentDate:moment(vue.viewModel.AdjustmentDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                        ExpireDate:moment(vue.viewModel.ExpireDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                        TicketSu:0,
                        SvTicketSu:0.0,
                        InsatsuTantoCd:vue.viewModel.InsatsuTantoCd,
                        ShuseiTantoCd:vue.getLoginInfo()["uid"],
                        SaveList: SaveList,
                    },
                    optional: vue.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                        },
                    },
                }
            );
            */
        },
    }
}
</script>
