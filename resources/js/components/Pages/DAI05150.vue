<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelect
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
                <!-- <VueSelectBusho
                    :hasNull=true
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                /> -->
            </div>
            <div class="col-md-5">
                <label>受付日</label>
                <DatePickerWrapper
                    id="DateStart"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateStart"
                    :editable=true
                    :onChangedFunc=onDateChanged
                />
                <label style="width: unset; text-align: center; margin-left: 5px; margin-right: 5px;">～</label>
                <DatePickerWrapper
                    id="DateEnd"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DateEnd"
                    :editable=true
                    :onChangedFunc=onDateChanged
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
                    :inputWidth=150
                    :nameWidth=400
                    :onAfterChangedFunc=onCustomerChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CustomerAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>キーワード</label>
            </div>
            <div class="col-md-5">
                <input type="text" class="form-control" :value="viewModel.KeyWord" @input="onKeyWordChanged">
            </div>
            <div class="col-md-3">
                <VueOptions
                    title="検索条件:"
                    customLabelStyle="text-align: center;"
                    id="FilterMode"
                    :vmodel=viewModel
                    bind="FilterMode"
                    :list="[
                        {code: 'AND', name: 'AND', label: '全て含む'},
                        {code: 'OR', name: 'OR', label: 'いずれかを含む'},
                    ]"
                    :onChangedFunc=onFilterModeChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>ステータス</label>
            </div>
            <div class="col-md-4">
                <VueCheckList
                    id="Status"
                    :vmodel=viewModel
                    bind="Status"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '継続', name: '継続', label: '継続'},
                        {code: '失客', name: '失客', label: '失客'},
                        {code: '未処理', name: '未処理', label: '未処理'},
                    ]"
                    :onChangedFunc=onStatusChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI05150Grid1"
            ref="DAI05150Grid1"
            dataUrl="/DAI05150/GetClaimList"
            :SearchOnCreate=true
            :SearchOnActivate=true
            :options=grid1Options
            :onAfterSearchFunc=onAfterSearchFunc
            :autoToolTipDisabled=true
        />
    </form>
</template>

<style scoped>
</style>
<style>
form[pgid="DAI05150"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI05150"] .top-wrap {
    align-items: flex-start;
    white-space: pre-wrap !important;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI05150",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI05150Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        searchParams: function() {
            var vue = this;
            var ms = moment(vue.viewModel.DateStart, "YYYY年MM月DD日");
            var me = moment(vue.viewModel.DateEnd, "YYYY年MM月DD日");
            return {
                BushoCd: vue.viewModel.BushoCd,
                CustomerCd: vue.viewModel.CustomerCd,
                DateStart: ms.isValid() ? ms.format("YYYYMMDD") : null,
                DateEnd: me.isValid() ? me.format("YYYYMMDD") : null,
            };
        },
    },
    watch: {
        "DAI05150Grid1.pdata": {
            deep: true,
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI05150_Print").disabled = !newVal.length;
                vue.footerButtons.find(v => v.id == "DAI05150_Delete").disabled = !newVal.length;
            },
        },
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "随時処理 > クレーム一覧",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                CustomerNm: null,
                DateStart: null,
                DateEnd: null,
                Status: [],
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI05150Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45, minWidth: 45 },
                autoRow: true,
                rowHt: 58,
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
                groupModel: {
                    on: false,
                    header: false,
                    grandSummary: false,
                    dataIndx: ["管轄部門"],
                    showSummary: [false],
                    collapsed: [false],
                    summaryInTitleRow: "collapsed",
                },
                colModel: [
                    {
                        title: "管轄部門",
                        dataIndx: "管轄部門",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "クレームID",
                        dataIndx: "クレームID",
                        dataType: "integer",
                        width: 75, minWidth: 75, maxWidth: 75,
                        render: ui => {
                            return { text: ui.rowData.クレームID + "<br><br>" };
                        },
                    },
                    {
                        title: "受付日時",
                        dataIndx: "受付日時",
                        colModel: [
                            {
                                title: "発生部門",
                                dataIndx: "管轄部門名",
                                dataType: "string",
                                width: 160, minWidth: 160, maxWidth: 160,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-left").text(moment(ui.rowData.受付日時).format("YYYY/MM/DD HH:mm:ss"))
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.管轄部門名)
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "得意先",
                        dataIndx: "得意先",
                        colModel: [
                            {
                                title: "得意先担当者",
                                dataIndx: "得意先担当者",
                                dataType: "string",
                                width: 150, minWidth: 150,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("d-flex")
                                                .append(
                                                    $("<div>").addClass("text-left").text(ui.rowData.顧客コード).width(75)
                                                )
                                                .append(
                                                    $("<div>").addClass("text-left").text(ui.rowData.得意先名)
                                                )
                                        )
                                        .append(
                                            $("<div>").addClass("d-flex")
                                                .append(
                                                    $("<div>").addClass("text-left").html("&nbsp").width(75)
                                                )
                                                .append(
                                                    $("<div>").addClass("text-left").text(ui.rowData.顧客担当者名)
                                                )
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "クレーム区分",
                        dataIndx: "クレーム区分名",
                        dataType: "string",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "原因部署",
                        dataIndx: "原因部署名",
                        colModel: [
                            {
                                title: "原因部署担当",
                                dataIndx: "原因部署担当",
                                dataType: "string",
                                width: 120, minWidth: 120, maxWidth: 120,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.原因部署名)
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.原因部署担当)
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "内容",
                        dataIndx: "クレーム内容",
                        dataType: "string",
                        width: 250, minWidth: 250,
                        cls: "top-wrap",
                    },
                    {
                        title: "継続/失客",
                        dataIndx: "ステータス",
                        colModel: [
                            {
                                title: "失客日",
                                dataIndx: "失客日",
                                dataType: "string",
                                width: 100, minWidth: 100, maxWidth: 100,
                                render: ui => {
                                    var $div = $("<div>").addClass("w-100")
                                        .append(
                                            $("<div>").addClass("text-center").text(ui.rowData.ステータス)
                                        )
                                        .append(
                                            $("<div>").addClass("text-center")
                                                .html(!!ui.rowData.失客日
                                                    ? moment(ui.rowData.失客日, ).format("YYYY/MM/DD")
                                                    : "&nbsp"
                                                )
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "受付日時",
                        dataIndx: "受付日時",
                        dataType: "date",
                        hidden: true,
                    },
                    {
                        title: "管轄部門コード",
                        dataIndx: "管轄部門コード",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "顧客コード",
                        dataIndx: "顧客コード",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "ステータス",
                        dataIndx: "ステータス",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "KeyWord",
                        dataIndx: "KeyWord",
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
                { visible: "true", value: "検索", id: "DAI05150_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged();
                    }
                },
                { visible: "true", value: "印刷", id: "DAI05150_Print", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.print();
                    }
                },
                { visible: "true", value: "CSV", id: "DAI05150_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.DAI05150Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI05150Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI05150Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                },
                {visible: "false"},
                { visible: "false", value: "削除", id: "DAI05150_Delete", disabled: false,
                    onClick: function () {
                        vue.Delete();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
            vue.viewModel.DateEnd = moment().endOf("month").format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI05150Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI05150Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onCustomerChanged: function(code, entity, comp) {
            var vue = this;

            if (!!entity && !_.isEmpty(entity)) {
                vue.viewModel.BushoCd = entity["部署CD"];

                //フィルタ変更
                vue.filterChanged();
            }
        },
        onDateChanged: function() {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        onStatusChanged: function(code, entities) {
            var vue = this;
            var grid = vue.DAI01210Grid1;

            //フィルタ変更
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI05150Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                grid.searchData(null, false);
            }
        },
        onFilterModeChanged: function(code, info) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI05150Grid1;

            if (!grid) return;

            var rules = [];

            var dcrules = [];
            if (!!vue.searchParams.DateStart) {
                dcrules.push({ condition: "gte", value: moment(vue.searchParams.DateStart, "YYYYMMDD").format("YYYY/MM/DD") });
            }
            if (!!vue.searchParams.DateEnd) {
                dcrules.push({ condition: "lte", value: moment(vue.searchParams.DateEnd, "YYYYMMDD").format("YYYY/MM/DD") });
            }

            if (dcrules.length) {
                rules.push({ dataIndx: "受付日時", mode: "AND", crules: dcrules });
            }

            if (!!vue.viewModel.BushoCd) {
                rules.push({ dataIndx: "管轄部門コード", condition: "equal", value: vue.viewModel.BushoCd });
            }

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "顧客コード", condition: "equal", value: vue.viewModel.CustomerCd });
            }

            if (!!vue.viewModel.Status.length) {
                var scrules = vue.viewModel.Status.map(s => { return { condition: "contain", mode: "OR", value: s }; });
                rules.push({ dataIndx: "ステータス", mode: "OR", crules: scrules });
            }

            if (!!vue.viewModel.KeyWord) {
                var keywords = vue.viewModel.KeyWord.split(/[, 、　]/)
                    .map(v => _.trim(v))
                    .map(k => k.replace(/^[\+＋]/, ""))
                    .filter(v => !!v);

                var rulesKeyWord = keywords.map(k => { return { condition: "contain", value: k }; });

                rules.push({ dataIndx: "KeyWord", mode: vue.viewModel.FilterMode, condition: "equal", crules: rulesKeyWord });
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

            //キーワード追加
            res = res.map(v => {
                v.管轄部門 = v.管轄部門コード + ":" + v.管轄部門名;
                v.KeyWord = v.クレーム内容;
                return v;
            });

            vue.filterChanged();

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI05150Grid1;
            if (!grid) return;

            var params;

            var params;
            if (rowData) {
                params = _.cloneDeep(rowData);
            } else {
                var selection = grid.SelectRow().getSelection();

                var rows = grid.SelectRow().getSelection();
                if (rows.length != 1) return;

                params = _.cloneDeep(rows[0].rowData);
            }

            params.IsChild = true;
            params.IsNew = false;

            //DAI05140を子画面表示
            PageDialog.show({
                pgId: "DAI05140",
                params: params,
                isModal: true,
                isChild: true,
                width: 1100,
                height: 700,
            });
        },
        showNewDetail: function(rowData) {

            var params = { IsChild: true, IsNew: true };

            //DAI05140を子画面表示
            PageDialog.show({
                pgId: "DAI05140",
                params: params,
                isModal: true,
                isChild: true,
                width: 1100,
                height: 700,
            });
        },
        //クレーム情報を削除する
        Delete: function() {
            var vue = this;
            var grid = vue.DAI05150Grid1;
            if (!grid) return;
            var rows = grid.SelectRow().getSelection();
            if (rows.length != 1) return;
            var rowData = _.cloneDeep(rows[0].rowData);
            console.log("ロウデータ",rowData);//TODO:

            var params = {
                     claim_id: rowData['クレームID']
                    ,update_user_id: vue.getLoginInfo().uid
                    ,noCache:true
                };

            console.log("パラメータ",params);//TODO:

            $.dialogConfirm({
                title: "削除確認",
                contents: "クレームデータを削除します。",
                buttons:[
                    {
                        text: "はい",
                        class: "btn btn-primary",
                        click: function(){
                            axios.post("/DAI05150/DeleteClaim", params)
                                .then(res => {
                                    DAI05150.conditionChanged();
                                    $(this).dialog("close");
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

        },
        print: function() {
            var vue = this;
            var grid = vue.DAI05150Grid1;

            //TOOD:VueSelectBusho用
            // if (!!vue.viewModel.BushoCd && !vue.BushoInfo) {
            //     var entity = vue.$refs.VueSelectBusho.$refs.BushoCd.entities.find(v => v.code == vue.viewModel.BushoCd);

            //     if (!entity) return
            //     vue.BushoInfo = entity.info;
            // }

            //印刷用HTML全体適用CSS
            var globalStyles = `
                body {
                    -webkit-print-color-adjust: exact;
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
                    font-family: "MS UI Gothic";
                    font-size: 9pt;
                    font-weight: normal;
                    margin: 0px;
                    padding-left: 3px;
                    padding-right: 3px;
                }
                th {
                    height: 16px;
                    text-align: center;
                }
                td {
                    height: 16px;
                    white-space: nowrap;
                    overflow: hidden;
                }
                table.row-table:nth-child(even) {
                	break-before: page;
                }
                table.row-table > tbody > tr > td {
                    border-style: dotted;
                    border-left-width: 0px;
                    border-top-width: 0px;
                    border-right-width: 0px;
                    border-bottom-width: 0px;
                    padding-bottom: 20px;
                }
                table.row-table:nth-child(even) > tbody > tr > td {
                    border-bottom-width: 1px;
                }
                table.row-table > tbody > tr > td:first-child {
                    border-right-width: 1px;
                }
                table.row-table > tbody > tr > td > div {
                    padding-left: 15px;
                    padding-right: 15px;
                }
                div.title {
                    display: block;
                    text-align: center;
                }
                div.title > h3, div.title > h5 {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
            `;

            grid.Group().option({ "on": true });

            var headerFunc = (header, idx, length, chunk, chunks) => {
                var TargetDateFrom = moment(vue.searchParams.DateStart, "YYYYMMDD").format("YYYY/MM/DD");
                var TargetDateTo = moment(vue.searchParams.DateEnd, "YYYYMMDD").format("YYYY/MM/DD");

                var Busho = header.pq_gid.split(":");
                var BushoCd = Busho[0] || "";
                var BushoNm = Busho[1] || "部署無し";

                return `
                    <div class="header">
                        <div class="title" style="float: left; width: 100%">
                            <h3>* * * <span></span>クレーム一覧表<span></span> * * *</h3>
                        </div>
                        <div style="float: left; width: 100%;">
                            <div style="float: left; width: 7%; text-align: center;">部署</div>
                            <div style="float: left; width: 5%; text-align: center;">${BushoCd}</div>
                            <div style="float: left; width: 20%;">${BushoNm}</div>
                            <div style="float: left; width: 36%; text-align: center;">
                                ${TargetDateFrom}
                                <span>～</span>
                                ${TargetDateTo}
                            </div>
                            <div style="float: left; width: 32%;">&nbsp</div>
                            <div style="float: left; width: 68%;">&nbsp</div>
                            <div style="float: left; width: 7%; text-align: right;">作成日</div>
                            <div style="float: left; width: 15%; text-align: center;">${moment().format("YYYY年MM月DD日")}</div>
                            <div style="float: left; width: 5%; text-align: center;">PAGE</div>
                            <div style="float: left; width: 5%; text-align: center;">${idx + 1}/${length}</div>
                        </div>
                    </div>
                `;
            };

            var printable = $("<html>")
                .append($("<head>").append($("<style>").text(globalStyles)))
                .append(
                    $("<body>")
                        .append(
                            grid.generateHtml(
                                `
                                    div.header > div > div > span {
                                        padding-left: 8px;
                                        padding-right: 8px;
                                    }
                                    div.header-box > div{
                                        border-style: solid;
                                        border-left-width: 1px;
                                        border-top-width: 1px;
                                        border-right-width: 0px;
                                        border-bottom-width: 0px;
                                        padding-left: 3px;
                                        padding-top: 3px;
                                        height: 20px;
                                    }
                                    div.header div:not(.title) {
                                        font-size: 12px;
                                    }
                                    div.header-box > div:last-child {
                                        border-right-width: 1px;
                                    }
                                    table.DAI05150Grid1 {
                                        border-collapse:collapse;
                                    }
                                    table.DAI05150Grid1 thead tr:first-child {
                                        border-top: solid 1px black;
                                    }
                                    table.DAI05150Grid1 tbody tr {
                                        border-bottom: dotted 1px black;
                                        height: 37px;
                                        min-height: 37px;
                                    }
                                    table.DAI05150Grid1 tbody tr:first-child {
                                        border-top: solid 1px black;
                                    }
                                    table.DAI05150Grid1 thead tr th {
                                        text-align: left;
                                        padding-left: 2px;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(1) {
                                        width: 6%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(2) {
                                        width: 12%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(3) {
                                        width: 20%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(4) {
                                        width: 7%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(5) {
                                        width: 8%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(6) {
                                        width: 40%;
                                    }
                                    table.DAI05150Grid1 thead tr th:nth-child(7) {
                                        width: 6%;
                                    }
                                    table.DAI05150Grid1 tbody tr td {
                                        text-align: left;
                                        vertical-align: top;
                                        padding-left: 2px;
                                        white-space: pre-wrap;
                                    }
                                    table.DAI05150Grid1 tbody tr td div {
                                        white-space: pre-wrap;
                                    }
                                    table.DAI05150Grid1 tbody tr td div.d-flex {
                                        display: flex;
                                    }
                                    table.DAI05150Grid1 tbody tr td div[style='width: 75px;'] {
                                        width: 20% !important;
                                        min-width: 20%;
                                    }
                                `,
                                headerFunc,
                                45,
                                false,
                                false,
                                true,
                            )
                        )
                )
                .prop("outerHTML")
                ;

            //Grouping解除
            grid.Group().option({ "on": false });

            var printOptions = {
                type: "raw-html",
                style: "@media print { @page { size: A4 landscape; margin: 10px 20px; } }",
                printable: printable,
            };

            printJS(printOptions);
            //印刷用HTMLの確認はデバッグコンソールで以下を実行
            //$("#printJS").contents().find("html").html()
        },
    }
}
</script>
