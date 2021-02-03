<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :hasNull=true
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-5">
                <label>配達日付</label>
                <DatePickerWrapper
                    id="DeliveryDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryDate"
                    :editable=true
                    :onChangedFunc=onDateChanged
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
                    :hasNull=true
                    :vmodel=viewModel
                    bind="DeliveryKbn"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 31 }"
                    :withCode=true
                    customStyle="{ width: 100px; }"
                    :onChangedFunc=onDeliveryKbnChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>得意先</label>
            </div>
            <div class="col-md-9">
                <PopupSelect
                    id="CustomerSelect"
                    ref="PopupSelect_Customer"
                    :vmodel=viewModel
                    bind="CustomerCd"
                    buddy="CustomerNm"
                    dataUrl="/Utilities/GetCustomerListForSelect"
                    :params="{ KeyWord: viewModel.CustomerCd, UserBushoCd: getLoginInfo().bushoCd }"
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
                    :nameWidth=400
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>出力順</label>
            </div>
            <div class="col-md-5">
                <VueOptions
                    id="PrintOrder"
                    ref="VueOptions_PrintOrder"
                    customItemStyle="text-align: center; margin-right: 10px;"
                    :vmodel=viewModel
                    bind="PrintOrder"
                    :list="[
                        {code: '0', name: '配達順', label: '0:配達順'},
                        {code: '1', name: 'カナ順', label: '1:カナ順'},
                        {code: '2', name: '得意先コード順', label: '2:得意先コード順'},
                        {code: '3', name: '受注Ｎｏ順', label: '3:受注Ｎｏ順'},
                    ]"
                    :onChangedFunc=onPrintOrderChanged
                />
            </div>
            <div class="col-md-2">
               <VueCheck
                    id="VueCheck_PrintOut"
                    ref="VueCheck_PrintOut"
                    :vmodel=viewModel
                    bind="IsPrintOut"
                    checkedCode="1"
                    customContainerStyle="border: none;"
                    :list="[
                        {code: '0', name: 'しない', label: '再出力する'},
                        {code: '1', name: 'する', label: '再出力する'},
                    ]"
                    :onChangedFunc=onPrintoutChanged
                />
            </div>
        </div>
        <PqGridWrapper
            :id='"DAI08020Grid1" + (!!params ? _uid : "")'
            ref="DAI08020Grid1"
            dataUrl="/DAI08020/GetNouhinList"
            :query=searchParams
            :SearchOnCreate=false
            :SearchOnActivate=false
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI08020"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI08020"] .top-wrap {
    align-items: flex-start;
    white-space: pre-wrap !important;
}
form[pgid="DAI08020"] .pq-grid-header-table .pq-td-div {
    height: 25px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08020",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI08020Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        searchParams: function() {
            var vue = this;
            var ms = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日");
            return {
                BushoCd: vue.viewModel.BushoCd,
                CustomerCd: vue.viewModel.CustomerCd,
                DeliveryDate: ms.isValid() ? ms.format("YYYYMMDD") : null,
                DeliveryKbn: vue.viewModel.DeliveryKbn,
                IsPrintOut: vue.viewModel.IsPrintOut,
                PrintOrder: vue.viewModel.PrintOrder,
            };
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理 > 納品書発行",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                CustomerNm: null,
                DeliveryDate: null,
                FilterMode: "AND",
                DeliveryKbn: null,
                IsPrintOut: "0",
                PrintOrder: "0",
            },
            DAI08020Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45, minWidth: 45 },
                autoRow: false,
                rowHt: 50,
                editable: false,
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                trackModel: { on: false },
                historyModel: { on: false },
                filterModel: {
                    on: true,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                // groupModel: {
                //     on: true,
                //     header: false,
                //     grandSummary: true,
                //     indent: 10,
                //     dataIndx: ["受注Ｎｏ"],
                //     showSummary: true,
                //     collapsed: false,
                //     summaryInTitleRow: "collapsed",
                // },
                // summaryData: [
                // ],
                // formulas:[
                // ],
                colModel: [
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                    },
                    {
                        title: "伝票Ｎｏ",
                        dataIndx: "受注Ｎｏ",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "配達日付",
                        dataIndx: "配達日付",
                        dataType: "string",
                        align: "center",
                        width: 180, minWidth: 180, maxWidth: 180,
                    },
                    {
                        title: "配達時間",
                        dataIndx: "配達時間",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        width: 200, minWidth: 200, maxWidth: 200,
                    },
                    {
                        title: "数量",
                        dataIndx: "数量",
                        dataType: "integer",
                        format: "#,##0",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                    {
                        title: "単価",
                        dataIndx: "単価",
                        dataType: "integer",
                        format: "#,##0",
                        width: 80, minWidth: 80, maxWidth: 80,
                    },
                    {
                        title: "金額",
                        dataIndx: "金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "電話番号",
                        dataIndx: "電話番号",
                        dataType: "string",
                        width: 120, minWidth: 120, maxWidth: 120,
                    },
                    {
                        title: "配達先",
                        dataIndx: "配達先",
                        dataType: "string",
                        width: 250, minWidth: 250, maxWidth: 250,
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ",
                        dataType: "string",
                        hidden: true,
                    },
                ],
                rowDblClick: function (event, ui) {
                    vue.showDetail(ui.rowData);
                },
            },
        });
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                {visible: "false"},
                { visible: "true", value: "検索", id: "DAI08020Grid1_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.DAI08020Grid1.searchData(vue.searchParams);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI08020Grid1_CSV", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.DAI08020Grid1.vue.exportData("csv", false, true);
                    }
                },
                { visible: "true", value: "Excel", id: "DAI08020Grid1_Excel", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.DAI08020Grid1.vue.exportData("xlsx", false, true);
                    }
                },
                { visible: "true", value: "印刷", id: "DAI08020Grid1_Print", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.print();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            if (!vue.params) {
                vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");
            } else {
                vue.viewModel.BushoCd = vue.params.BushoCd;
                vue.viewModel.CustomerCd = vue.params.CustomerCd;
                vue.viewModel.DeliveryDate = moment(vue.params.DeliveryDate, "YYYY年MM月DD日").add(-1, "month").startOf("month").format("YYYY年MM月DD日");
                vue.viewModel.DeliveryKbn = vue.params.DeliveryKbn;
            }

            //for Child mode
            if (!!vue.params && !!vue.params.IsChild) {
                vue.DAI08020Grid1 = vue.$refs.DAI08020Grid1.grid;
            }

            //watcher
            vue.$watch(
                "$refs.DAI08020Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI08020Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];
            }

            //フィルタ変更
            vue.filterChanged();
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onDeliveryKbnChanged: function(code, entity) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onPrintoutChanged: function(code, entity) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onPrintOrderChanged: function(code, entities) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08020Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;

            if (!vue.searchParams.DeliveryDate || !vue.searchParams.IsPrintOut || !vue.searchParams.PrintOrder) return;
            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams);
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI08020Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            grid.filter({ oper: "replace", mode: "AND", rules: rules });
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
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI08020Grid1_CSV").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI08020Grid1_Excel").disabled = !res.length;
            vue.footerButtons.find(v => v.id == "DAI08020Grid1_Print").disabled = !res.length;

            return res;
        },
        save: function(){
            var vue=this;
            var grid = vue.DAI08020Grid1;

            //登録データの作成
            var SaveList=[];
            _.forEach(grid.pdata,r=>{
                var SaveItem={};
                SaveItem.key部署ＣＤ=r.key部署ＣＤ;
                SaveItem.key受注Ｎｏ=r.key受注Ｎｏ;
                SaveItem.key配達日付=r.key配達日付;
                SaveList.push(SaveItem);
            });

            //登録実行
            grid.saveData(
                {
                    uri: "/DAI08020/UpdatePrintoutFlag",
                    params: {
                        BushoCd: vue.viewModel.BushoCd,
                        DeliveryDate: vue.viewModel.DeliveryDate,
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
        print: function () {
            var vue = this;
            var grid = vue.DAI08020Grid1;

            //印刷用HTML全体適用CSS
            var targetData = grid.pdata;
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
                }
                div.title {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                div.title > h3 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                    padding-top: 3px;
                    padding-bottom: 3px;
                    padding-left: 62px;
                    padding-right: 46px;
                    letter-spacing: 1.0em;
                    background-color: #c0ffff;
                    border: 1px solid #000000;
                    border-radius: 1px;
                    font-size: 14pt;
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
                    font-family: "MS Mincho";
                    font-size: 11pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 21px;
                    text-align: center;
                }
                td {
                    height: 22px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.header-table th {
                    text-align: left;
                    border: solid 1px black;
                    border-style: solid;
                    border-left-width: 1px;
                    border-top-width: 1px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    font-size: 13.5pt;
                }
                table.header-table tbody tr:not(:first-child):nth-child(-n+7) th {
                    border-top-color: gray;
                    border-top-width: 1px;
                }
                table.header-table tbody tr:nth-child(8) th:nth-child(1),
                table.header-table tbody tr:nth-child(8) th:nth-child(4) {
                    border-bottom-width: 1px;
                }
                table.header-table th:last-child:nth-child(5) {
                    border-right-width: 1px;
                }
                table.header-table tr:nth-child(8) th:last-child:nth-child(4) {
                    border-right-width: 1px;
                }
                table.header-table tbody tr:last-child th {
                    border-bottom-width: 1px;
                }
                table.header-table thead th {
                    font-size: 11pt;
                    height: 22px;
                    text-align: center;
                }
                table.header-table tr:nth-child(2) th:last-child:nth-child(5),
                table.header-table tr:nth-last-child(3) th:nth-child(1) {
                    vertical-align: top;
                    padding-top: 5px;
                    padding-left: 5px;
                }
                table.header-table tr:nth-last-child(3) th:last-child:nth-child(4) {
                    vertical-align: bottom;
                    padding-bottom: 5px;
                    padding-left: 5px;
                    text-align: left;
                }
                table.header-table tbody th {
                    font-size: 9.5pt;
                }
                table.header-table tbody th {
                    height: 25px;
                }
                div.customer-nm  {
                    letter-spacing: 0.1em;
                    margin-top: 25px;
                    margin-bottom: 25px;
                }
                div.title {
                    float: left;
                    width: 46%;
                    padding-left: 170px;
                }
                div.denpyo-no {
                    float: left;
                    width: 22%;
                    border: 1px solid #000000;
                    border-radius: 1px;
                    padding-top: 3px;
                    padding-bottom: 3px;
                }
                table.header-table th:nth-child(1) {
                    width: 30%;
                }
                table.header-table th:nth-child(2),
                table.header-table th:nth-child(3),
                table.header-table th:nth-child(4) {
                    width: 12%;
                    text-align: right;
                    padding-right: 5px;
                }
                table.header-table tr:nth-child(8) th:nth-child(2),
                table.header-table tr:nth-child(9) th:nth-child(1),
                table.header-table tr:nth-child(10) th:nth-child(1) {
                    text-align: center;
                }
                table.header-table tr:nth-child(2) th:last-child > div {
                    white-space: pre-wrap;
                }
                div.hizuke {
                    border-style: solid;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 1px;
                    width: 400px;
                    margin-left: 20px;
                    padding: 3px;
                }
                span {
                    margin-left: 8px;
                    margin-right: 8px;
                }
                hr {
                    margin-top: 30px;
                    margin-bottom: 40px;
                    border-color: black;
                    border-style: dotted;
                    border-top-width: 1px;
                    border-bottom-width: 0px;
                    border-left-width: 0px;
                    border-right-width: 0px;
                    background-color: transparent;
                }
            `;

            var OrderPrint = "pq_ri";
            if (vue.viewModel.PrintOrder=="0") OrderPrint = "pq_ri";    //"エリアＣＤ, 配達順";
            if (vue.viewModel.PrintOrder=="1") OrderPrint = "得意先名カナ";
            if (vue.viewModel.PrintOrder=="2") OrderPrint = "得意先ＣＤ";
            if (vue.viewModel.PrintOrder=="3") OrderPrint = "受注Ｎｏ";

            // 配列化（max7件）
            var ary = _(targetData).groupBy(v => v.受注Ｎｏ).values().map(g => _.chunk(g, 7)).flatten().sortBy(v => v[0]["pq_ri"]).sortBy(v => v[0][OrderPrint] * 1).value();
            var ret = _.reduce(ary, (a, v) => { a.push(v); return a; }, []);
            var target = ret
                .map(v => {
                    var layout = ``;
                    var layout_product_body = ``;
                    var h = v[0];
                    var layout_delivery = `
                        <th rowspan="6">
                            <div>配達先</div>
                            <div>${h.配達先 ? h.配達先 : ""}</div>
                        </th>
                    `;
                    var layout_tel = `
                        <th>
                            <table>
                            <th style="width: 8%; border: none;">電話番号</th>
                            <th style="border: none; text-align: left;">${h.電話番号 ? h.電話番号.replace('\n', "<br>") : ""}</th>
                            </table>
                        </th>
                    `;

                    v.map((r, i) => {
                        var layout_product_tel = i==0 ? `${layout_tel}`: ``;
                        var layout_product_delivery = i==1 ? `${layout_delivery}`: ``;
                        layout_product_body += `
                            <tr>
                                <th>${r.商品名}</th>
                                <th>${r.数量}</th>
                                <th>${pq.formatNumber(r.単価, "#,##0")}</th>
                                <th>${pq.formatNumber(r.金額, "#,##0")}</th>
                                ${layout_product_tel}
                                ${layout_product_delivery}
                            </tr>
                        `;
                    });
                    var product_empty_cnt = 7 - v.length;
                    if(product_empty_cnt < 7){
                        [...Array(product_empty_cnt)].map((r, i) => {
                            var layout_product_delivery = (v.length + i) == 1 ? `${layout_delivery}`: ``;
                            layout_product_body += `
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    ${layout_product_delivery}
                                </tr>
                            `;
                        });
                    }

                    var layout_bikou1 = `
                        <th rowspan="3" colspan="2">
                            <div>備考</div>
                        </th>
                    `;
                    var layout_bikou2 = `
                        <th rowspan="3" colspan="2">
                            <div>備考</div>
                            <div style="margin:5px 0px;width:100%;height:40px;white-space:normal;word-break: break-all;overflow: hidden;">${h.備考}</div>
                            <div>${h.部署ＣＤ} ${h.部署名} 仕上時間 ${h.製造締切時間}</div>
                        </th>
                    `;
                    var layout_product_head = `
                        <thead>
                            <tr>
                                <th>商品名</th>
                                <th>数量</th>
                                <th>単価</th>
                                <th>金額</th>
                                <th>配達</th>
                            </tr>
                        </thead>
                    `;
                    var layout_product1 = `
                        ${layout_product_head}
                        <tbody>
                            ${layout_product_body}
                            <tr>
                                ${layout_bikou1}
                                <th>小 計</th><th>${pq.formatNumber(h.小計, "#,##0")}</th>
                                <th rowspan="3">
                                    <div style="text-align: left;">${h.注文日付}</div>
                                    <div>受付店：${h.会社名称}</div>
                                </th>
                            </tr>
                            <tr>
                                <th>消費税</th><th>${pq.formatNumber(h.消費税, "#,##0")}</th>
                            </tr>
                            <tr>
                                <th>金額合計</th><th>${pq.formatNumber(h.合計, "#,##0")}</th>
                            </tr>
                        </tbody>
                    `;
                    var layout_product2 =  `
                        ${layout_product_head}
                        <tbody>
                            ${layout_product_body}
                            <tr>
                                ${layout_bikou2}
                                <th>小 計</th><th>${pq.formatNumber(h.小計, "#,##0")}</th>
                                <th rowspan="3">
                                    <div style="text-align: left;">${h.注文日付}</div>
                                    <div>受付店：${h.会社名称}</div>
                                </th>
                            </tr>
                            <tr>
                                <th>消費税</th><th>${pq.formatNumber(h.消費税, "#,##0")}</th>
                            </tr>
                            <tr>
                                <th>金額合計</th><th>${pq.formatNumber(h.合計, "#,##0")}</th>
                            </tr>
                        </tbody>
                    `;

                    var layout_common=`
                        <div style="width: 35%; float: right; margin-top: 5px;">
                            <div style="font-size: 12pt;">
                                <span/>${h.会社名称}
                            </div>
                            <div style="font-size: 9pt; margin-top: 3px; text-align: right;">
                                ${h.住所欄}
                            </div>
                            <div style="font-size: 9pt; text-align: right;">
                                ${h.TEL欄}
                            </div>
                        </div>
                        <div style="width: 65%; float: left;">
                            <div style="margin-left: 285px; margin-top: 5px;">
                                (軽減税率対象)
                            </div>
                            <div class="customer-nm">
                                <span/><span>${h.得意先名}</span><span>様</span>
                            </div>
                            <div class="hizuke"><span>納品日：${h.配達日付} 時刻：（${h.配達時間}）</span></div>
                        </div>
                        <div style="width: 35%; float: right; margin-top: 5px;">
                            <div style="font-size: 9pt;"><span/>取引金融機関</div>
                            <div style="font-size: 8pt;">
                                <div style="width: 48%; float: left;">
                                    <div style="margin-top: 3px;">
                                        <span/><span/>${!!h.会社_銀行名1 ? h.会社_銀行名1 : ""}
                                    </div>
                                    <div><span/><span/>${!!h.会社_口座種別名1 ? h.会社_口座種別名1 : ""}　${!!h.会社_口座番号1 ? h.会社_口座番号1 : ""}</div>
                                    <div style="margin-top: 3px;">
                                        <span/><span/>${!!h.会社_銀行名2 ? h.会社_銀行名2 : ""}
                                    </div>
                                    <div><span/><span/>${!!h.会社_口座種別名2 ? h.会社_口座種別名2 : ""}　${!!h.会社_口座番号2 ? h.会社_口座番号2 : ""}</div>
                                </div>
                                <div style="width: 45%; float: left; margin-left: 15px;">
                                    <div>${!!h.会社_支店名1 ? h.会社_支店名1 : ""}</div>
                                    <div>${!!h.会社_口座名義人1 ? h.会社_口座名義人1 : ""}</div>
                                    <div>${!!h.会社_支店名2 ? h.会社_支店名2 : ""}</div>
                                    <div>${!!h.会社_口座名義人2 ? h.会社_口座名義人2 : ""}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    var layout_1=`
                        <div>
                            <div class="header">
                                <div>
                                    <div>
                                        <div class="title">
                                            <h3>請求書</h3>
                                        </div>
                                        <div class="denpyo-no">
                                            <div style="float: left; padding-left: 10px;""> 伝票No. </div>
                                            <div style="float: right; padding-right: 10px;"> ${h.受注Ｎｏ} </div>
                                        </div>
                                    </div>
                                    <table style="width:100%;">
                                        ${layout_common}
                                        <tr>
                                            <td>
                                                <div style="font-size: 10pt;"><span>下記の通り納品いたしました。</span></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="header-table" style="border-width: 0px; margin-bottom: 8px;">
                                        ${layout_product1}
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    var layout_2=`
                        <div>
                            <div class="header">
                                <div>
                                    <div>
                                        <div class="title">
                                            <h3>納品書</h3>
                                        </div>
                                        <div class="denpyo-no">
                                            <span/>伝票No.　${h.受注Ｎｏ}
                                        </div>
                                    </div>
                                    <table style="width:100%;">
                                        ${layout_common}
                                        <tr>
                                            <td>
                                                <div style="font-size: 10pt;"><span>下記の通り納品いたしました。（${h.得意先ＣＤ}）</span></div>
                                            </td>
                                        </tr>
                                    </table">
                                    <table class="header-table" style="border-width: 0px; margin-bottom: 8px;">
                                        ${layout_product2}
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                    var layout = layout_1 + `<div style="clear:left"></div><hr noshade/>` + layout_2;

                    return { contents: layout };
            });
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

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 portrait; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
