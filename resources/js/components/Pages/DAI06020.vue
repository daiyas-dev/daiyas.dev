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
            <div class="col-md-5">
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
                    :nameWidth=300
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
            <div class="col-md-4">
                <label style="width: 120px;">前チケット番号</label>
                <input class="form-control p-0 text-center label-blue" style="width: 80px;" type="text" :value=viewModel.LastTicketNo readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>印刷枚数</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="range"
                    v-model="viewModel.InsatsuMaisu"
                    style="width: 80px; padding-right: 0px; text-align: center; border: none; border-radius: 4px;"
                    maxlength="2"
                    min="1"
                    @input="onValidPeriodChanged"
                >枚
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>発行日付</label>
            </div>
            <div class="col-md-8">
                <VueCheck
                    id="VueCheck_IsPrintIssueDate"
                    ref="VueCheck_IsPrintIssueDate"
                    :vmodel=viewModel
                    bind="IsPrintIssueDate"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '印刷なし', label: '印刷あり'},
                        {code: '1', name: '印刷あり', label: '印刷あり'},
                    ]"
                />
                <DatePickerWrapper
                    id="IssueDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="IssueDate"
                    :editable=true
                    :onChangedFunc=onIssueDateChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>有効期間</label>
            </div>
            <div class="col-md-4">
                <input type="number" class="range"
                    v-model="viewModel.ValidPeriod"
                    style="width: 80px; padding-right: 0px; text-align: center; border: none; border-radius: 4px;"
                    maxlength="2"
                    min="1"
                    @input="onValidPeriodChanged"
                >ヶ月
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>有効期限</label>
            </div>
            <div class="col-md-8">
                <VueCheck
                    id="VueCheck_IsPrintExpireDate"
                    ref="VueCheck_IsPrintExpireDate"
                    :vmodel=viewModel
                    bind="IsPrintExpireDate"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: '印刷なし', label: '印刷あり'},
                        {code: '1', name: '印刷あり', label: '印刷あり'},
                    ]"
                />
                <DatePickerWrapper
                    id="ExpireDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月DD日"
                    :vmodel=viewModel
                    bind="ExpireDate"
                    :editable=false
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>印刷担当者</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="InsatsuTantoCd"
                    :vmodel=viewModel
                    bind="InsatsuTantoCd"
                    uri="/Utilities/GetTantoList"
                    :params="{ tantoCd: 20 }"
                    :withCode=true
                    :hasNull=false
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI06020Grid1"
            ref="DAI06020Grid1"
            dataUrl="/DAI06020/Search"
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
#DAI06020Grid1 .pq-group-toggle-none {
    display: none !important;
}
#DAI06020Grid1 .pq-group-icon {
    display: none !important;
}
#DAI06020Grid1 .pq-td-div {
    display: flex;
    line-height: 13px !important;
    justify-content: center;
    align-items: center;
    height: 50px;
}
#DAI06020Grid1 .pq-grid-cell {
    align-items: flex-start;
}
#DAI06020Grid1 .pq-td-div span {
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
    name: "DAI06020",
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
            ScreenTitle: "チケット > チケット印刷",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                CustomerCd: null,
                CustomerName: null,
                ProductCd: null,
                ProductName: null,
                InsatsuMaisu:"1",
                InsatsuTantoCd:null,
                LastTicketNo:null,
                NowTicketNo:null,
                TicketMaisu:null,
                SVTicketMaisu:null,
                IssueDate: null,
                IsPrintIssueDate:"1",
                ValidPeriod:"12",
                ExpireDate: null,
                IsPrintExpireDate:"1",
            },
            DAI06020Grid1: null,
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
                freezeCols: 2,
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
                        title: "商品ＣＤ",
                        dataIndx: "商品ＣＤ", dataType: "string",align:"right",
                        width: 90, minWidth: 90, maxWidth: 90,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名", dataType: "string",
                        width: 230, minWidth: 230, maxWidth: 230,
                        tooltip: true,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "チケット数",
                        dataIndx: "チケット枚数",
                        dataType: "integer",
                        format: "#,###",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "サービス数",
                        dataIndx: "サービスチケット枚数",
                        dataType: "float",
                        format: "#,###.0",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名", dataType: "string",align:"right",
                        hidden: true,
                    },
                ],
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "検索", id: "DAI06020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "実行", id: "DAI06020Grid1_Save", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.save();
                        vue.print();
                    }
                }
            );
        },
        mountedFunc: function(vue) {
            var vue = this;
            //日付の初期値 -> 当日
            vue.viewModel.IssueDate = moment().format("YYYY年MM月DD日");
        },
        onIssueDateChanged: function(code, entities) {
            this.UpdateExpireDate();
        },
        onValidPeriodChanged: function(code, entities) {
            var vue = this;
            this.UpdateExpireDate();
        },
        onCustomerChanged: function(code, entities) {
            var vue = this;
            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(callback) {
            var vue = this;
            var grid = vue.DAI06020Grid1;
            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.viewModel.BushoCd) return;
            if (!vue.viewModel.CustomerCd) return;
            var params = $.extend(true, {}, vue.viewModel);
            grid.searchData(params, false, null, callback);
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var vue = this;
            if(res.length==0)
            {
                vue.footerButtons.find(v => v.id == "DAI06020Grid1_Save").disabled = true;
                vue.viewModel.LastTicketNo=null;
                return res;
            }
            vue.viewModel.LastTicketNo=res[0].チケット管理番号 == null ? 0 : res[0].チケット管理番号;
            vue.footerButtons.find(v => v.id == "DAI06020Grid1_Save").disabled = (!vue.viewModel.LastTicketNo);
            return res;
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
            vue.viewModel.ExpireDate = moment(vue.viewModel.IssueDate,"YYYY年MM月DD日").add(vue.viewModel.ValidPeriod,'month').add(-1,'day').format("YYYY年MM月DD日");
        },
        save:function()
        {
            var vue=this;
            var grid = vue.DAI06020Grid1;
            if(vue.viewModel.BushoCd==null)
            {
                return;
            }
            if(vue.viewModel.CustomerCd==null)
            {
                return;
            }
            if(vue.viewModel.InsatsuMaisu==null)
            {
                return;
            }

            //登録データの作成
            var SaveList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.得意先名=r.得意先名;
                SaveItem.商品ＣＤ=r.商品ＣＤ;
                SaveItem.商品名=r.商品名;
                SaveItem.単価=r.単価;
                SaveItem.チケット数=r.チケット枚数;
                SaveItem.サービス数=r.サービスチケット枚数;
                SaveList.push(SaveItem);
            });

            //登録実行
            vue.viewModel.CustomerName=SaveList[0].得意先名;
            vue.viewModel.ProductCd=SaveList[0].商品ＣＤ;
            vue.viewModel.ProductName=SaveList[0].商品名;
            vue.viewModel.Price=SaveList[0].単価;
            vue.viewModel.TicketMaisu=SaveList[0].チケット数;
            vue.viewModel.SVTicketMaisu=SaveList[0].サービス数;
            vue.viewModel.NowTicketNo=(vue.viewModel.LastTicketNo*1)+1;//チケット印刷をするため、チケット番号(開始値)を退避
            grid.saveData(
                {
                    uri: "/DAI06020/Save",
                    params: {
                        CustomerCd:vue.viewModel.CustomerCd,
                        TicketNo:vue.viewModel.NowTicketNo*1,
                        InsatsuMaisu:vue.viewModel.InsatsuMaisu*1,
                        IssueDate:moment(vue.viewModel.IssueDate, "YYYY年MM月DD日").format("YYYY/MM/DD"),
                        ExpireDate:moment(vue.viewModel.ExpireDate, "YYYY年MM月DD日").format("YYYY/MM/DD"),
                        TicketSu:vue.viewModel.TicketMaisu,
                        SvTicketSu:vue.viewModel.SVTicketMaisu,
                        InsatsuTantoCd:vue.viewModel.InsatsuTantoCd,
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
        },
        print:function()
        {
            var vue = this;
            var grid = vue.DAI06020Grid1;

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                    margin: 0px !important;
                }
                table {
                    table-layout: fixed;
                    margin-left: 0px;
                    margin-right: 0px;
                    width: 100%;
                    border-spacing: unset;
                    border: solid 0px black;
                }
                th, td {
                    font-family: "ＭＳ Ｐ明朝";
                    font-size: 10pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                    height:80px;
                }
                td.ticket-outer{
                    width:calc(89vw/3);
                    height:calc(100vh/9);
                    padding: 0px 15px 0px 15px;
                }
                div.ticket-content{
                    display:inline-block;
                    vertical-align:top;
                    line-height: 1.0;
                }
                .pagebreak {
                break-before: page;
                }
                div[style="break-before: page;"],
                div[style="break-before: auto;"],
                div[style="page-break-before: always;"] {
                    margin-top: 0px !important;
                    margin-bottom: 0px !important;
                    margin-right: 5px !important;
                    margin-left: 5px !important;
                }
            `;

            /*
                pageFunc()
                ページ(1シート)作成関数
                引数：ticketno チケット番号
            */
            var pageFunc=(p_ticketno)=>{
                /*
                    ticketFunc()
                    チケット(1片)作成関数
                    引数：ticketno チケット番号
                    引数：seq      チケット通し番号
                    引数：kind     チケット種類 0=通常のチケット / 1=サービス券 / 2=サービス半券
                */
                var ticketFunc=(ticketno,seq,kind)=>{
                    var str_TicketNo = ( '000000' + ticketno ).slice( -6 );
                    var str_seq = ( '00' + seq ).slice( -2 );

                    var str_customer_name=vue.viewModel.CustomerName.substr(0,24);//得意先名は先頭から24文字を取得
                    var str_service = kind==1 ? "(サービス)": kind==2 ? "(サービス半券)": "";
                    var str_product_style=(kind==1 || kind==2) ? "color:green;background-color:peachpuff":"color:red;" ;//商品名のスタイル設定(通常チケット:背景色=なし,文字色=赤 / サービス:背景色=オレンジ,文字色=緑)
                    var str_product_name = kind==1 ? vue.viewModel.ProductName.substr(0,7) : kind==2 ? vue.viewModel.ProductName.substr(0,5) : vue.viewModel.ProductName.substr(0,12);//商品名。通常チケットは先頭から12文字、サービスチケットは先頭から7文字、サービス半券は先頭から5文字を取得

                    var str_IssueDate= vue.viewModel.IsPrintIssueDate=='1' ? moment(vue.viewModel.IssueDate, "YYYY年MM月DD日").format("YYYY/MM/DD") : "";
                    var str_ExpireDate=vue.viewModel.IsPrintExpireDate=='1' ? moment(vue.viewModel.ExpireDate, "YYYY年MM月DD日").format("YYYY/MM/DD") : "";
                    var str_icon_filename=vue.viewModel.BushoCd==701?"701.jpg":"101.jpg";
                    return `
                        <div>
                            <div style="width:96%;">
                                <div class="ticket-content" style="padding:1px 0px 0px 1px;">
                                    <img src="${window.location.origin}/images/TicketIcon/${str_icon_filename}" style="width:20px;">
                                </div>
                                <div class="ticket-content" style="width:85%;padding-top:5px;margin-bottom:5px;">
                                    <div class="ticket-content" style="width:75%;word-wrap:break-word;">${str_customer_name}</div>
                                    <div class="ticket-content" style="text-align:right; float:right">様<br/>(${vue.viewModel.CustomerCd})</div>
                                    <div style="clear:both"></div>
                                </div>
                                <div style="width:97%;text-align:center;margin-bottom:5px;${str_product_style}">
                                    ${str_product_name}${str_service}&nbsp;&nbsp;&yen;${vue.viewModel.Price}
                                </div>
                                <div class="ticket-content">発行:</div>
                                <div class="ticket-content" style="font-size:9pt;">${str_IssueDate}</div>
                                <div class="ticket-content" style="text-align:right; padding-left: 1px;">有効期限:</div>
                                <div class="ticket-content" style="text-align:right; font-size:9pt;">${str_ExpireDate}</div>
                                <div style="clear:both"></div>
                                <div class="ticket-content" >株式会社ダイヤス食品</div>
                                <div class="ticket-content" style="text-align:right; padding-left:23px;">№${str_TicketNo}${str_seq}</div>
                                <div style="clear:both"></div>
                            </div>
                        </div>
                    `;
                };

                var ticket_notify=`
                        <div style="text-decoration:underline;font-size:10pt;line-height:18px;">
                        ※有効期限以降の使用はできません。<br/>
                        ※記載されているお客様以外の方は<br/>
                        使用できません。<br/>
                        </div>
                    `;
                var ticket_empty=`
                        <div style="text-align:center;font-size:13pt;">
                        ＊＊＊＊＊
                        </div>
                    `;

                //チケット枚数の取得
                var maisu= 27<vue.viewModel.TicketMaisu*1 ? 27 : vue.viewModel.TicketMaisu*1;

                //サービス券枚数の算出
                var service_half_maisu=0;
                var service_full_maisu=0;
                if((vue.viewModel.SVTicketMaisu*1)==0.5)
                {
                    //サービス半券枚数(サービス数が0.5のとき1枚)
                    service_half_maisu= 27<=maisu? 0 : 1;
                }
                else if((vue.viewModel.SVTicketMaisu*1)>=1)
                {
                    //サービス券枚数(サービスが1以上のとき、その枚数。小数は切り上げ。例：1.5ならサービス券を2枚)
                    var aki_maisu=27-maisu*1;
                    var service_full_maisu=Math.ceil(vue.viewModel.SVTicketMaisu*1);
                    if(aki_maisu<service_full_maisu)
                    {
                        service_full_maisu=aki_maisu;
                    }
                }

                var layout_content=[];

                //指定枚数のチケットを作成する
                var i=0;
                [...Array(maisu)].map(r=>{
                        layout_content[i]={val:i,data:ticketFunc(p_ticketno,i+1,0)};
                        i++;
                    });

                //指定枚数のサービスチケットを作成する
                if(0<service_full_maisu)
                {
                    [...Array(service_full_maisu)].map(r=>{
                            layout_content[i]={val:i,data:ticketFunc(p_ticketno,i+1,1)};
                            i++;
                        });
                }
                //指定枚数のサービス半チケットを作成する
                if(0<service_half_maisu)
                {
                    [...Array(service_half_maisu)].map(r=>{
                            layout_content[i]={val:i,data:ticketFunc(p_ticketno,i+1,2)};
                            i++;
                        });
                }

                //notifyを作成する
                if((maisu*1 + service_full_maisu*1 + service_half_maisu*1)<27)
                {
                    layout_content[i]={val:i,data:ticket_notify};
                    i++;
                }

                //emptyを作成する
                var empty_su = 27 - maisu - service_full_maisu - service_half_maisu - 1;
                if(0<empty_su)
                {
                    [...Array(empty_su)].map(r=>{
                            layout_content[i]={val:i,data:ticket_empty};
                            i++;
                        });
                }

                //チケットを用紙の左上から縦並びに並べ替える
                var layout_content = _.sortBy(layout_content,v=>v.val%9);

                //出力htmlの作成
                var layout_page_html=`<table style="width:100%;">`;
                var j=0;
                [...Array(9)].map(r=>{
                        layout_page_html += `
                            <tr>
                                <td class="ticket-outer">
                                    ${layout_content[j].data}
                                </td>
                                <td class="ticket-outer">
                                    ${layout_content[j+1].data}
                                </td>
                                <td class="ticket-outer">
                                    ${layout_content[j+2].data}
                                </td>
                            </tr>
                        `;
                        j+=3;
                    });
                layout_page_html+=`</table>`;
                return layout_page_html;
            };

            //印刷ページ数の取得
            var InsatsuMaisu=vue.viewModel.InsatsuMaisu*1;
            var page=0;
            var layout=``;
            [...Array(InsatsuMaisu)].map(r=>{
                //ページの追加
                layout += page!=0?`<div class="pagebreak"></div>`:``;
                layout += pageFunc((vue.viewModel.NowTicketNo*1)+page);
                page++;
            });

            //出力実行
            var target ={ contents: layout };
            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtmlFromJson(
                                target,
                                ``,
                                null,
                                1,
                                false,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //TODO:カラー印刷はブラウザから印刷設定を制御できないようなので、定義のみ記述しました。
            var printOptions = {
                type: "raw-html",
                style: "@media print and (color) { @page { size: 210mm 302mm portrait; }}",
                printable: printable,
            };
            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").conten
        },
    }
}
</script>
