<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-2">
                <VueSelectBusho
                    :withCode=true
                    style="width:200px"
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-5">
                <label>配達日付</label>
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
        <PqGridWrapper
            :id='"DAI08009Grid1" + (!!params ? _uid : "")'
            ref="DAI08009Grid1"
            dataUrl="/DAI08009/GetChumonList"
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
form[pgid="DAI08009"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI08009"] .top-wrap {
    align-items: flex-start;
    white-space: pre-wrap !important;
}
form[pgid="DAI08009"] .pq-grid-header-table .pq-td-div {
    height: 25px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08009",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI08009Grid1;
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
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理 > 受注問合せ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CustomerCd: null,
                CustomerNm: null,
                DateStart: null,
                DateEnd: null,
                KeyWord: null,
                FilterMode: "AND",
            },
            DAI08009Grid1: null,
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
                colModel: [
                    {
                        title: "配達時間",
                        dataIndx: "配達時間",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
                    {
                        title: "受注No",
                        dataIndx: "受注No",
                        colModel: [
                            {
                                title: "配達日付",
                                dataIndx: "配達日付",
                                dataType: "string",
                                width: 100, minWidth: 100, maxWidth: 100,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-left").text(ui.rowData.受注Ｎｏ)
                                        )
                                        .append(
                                            $("<div>").addClass("text-center").addClass("w-100").text(moment(ui.rowData.配達日付).format("YYYY/MM/DD"))
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "顧客名",
                        dataIndx: "得意先名",
                        colModel: [
                            {
                                title: () => {
                                    return $("<div>").addClass("d-flex")
                                        .append(
                                            $("<div>").addClass("text-center").text("電話番号").width(125)
                                        )
                                        .append(
                                            $("<div>").addClass("text-center").text("FAX").width(125)
                                        )
                                        .prop("outerHTML");
                                },
                                dataIndx: "電話番号１",
                                dataType: "string",
                                width: 250, minWidth: 250, maxWidth: 250,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("d-flex")
                                                .append(
                                                    $("<div>").addClass("text-left").text(ui.rowData.得意先ＣＤ).width(75)
                                                )
                                                .append(
                                                    $("<div>").addClass("text-left").text(ui.rowData.得意先名)
                                                )
                                        )
                                        .append(
                                            $("<div>").addClass("d-flex")
                                                .append(
                                                    $("<div>").addClass("text-left").html(ui.rowData.電話番号１ || "&nbsp").width(125)
                                                )
                                                .append(
                                                    $("<div>").addClass("text-left").html(ui.rowData.ＦＡＸ１ || "&nbsp")
                                                )
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "住所/配達先",
                        dataIndx: "住所",
                        dataType: "string",
                        width: 200, minWidth: 200,
                        render: ui => {
                            var $div = $("<div>")
                                .append(
                                    $("<div>").addClass("text-left").html(ui.rowData.住所 || "&nbsp")
                                )
                                .append(
                                    $("<div>").addClass("text-center").html(ui.rowData.配達先 || "&nbsp")
                                )
                                ;

                            return $div.prop("outerHTML");
                        },
                    },
                    {
                        title: "エリア",
                        dataIndx: "エリアＣＤ",
                        colModel: [
                            {
                                title: "配達区分",
                                dataIndx: "配達区分",
                                dataType: "string",
                                width: 150, minWidth: 150, maxWidth: 150,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.エリアＣＤ + ":" + (ui.rowData.エリア名称 || "&nbsp"))
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.配達名称 || "&nbsp")
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "地域",
                        dataIndx: "地域区分",
                        colModel: [
                            {
                                title: "税区分",
                                dataIndx: "税区分",
                                dataType: "string",
                                width: 75, minWidth: 75, maxWidth: 75,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.地区名称 || "&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.税名称 || "&nbsp")
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "得意先ＣＤ",
                        dataIndx: "得意先ＣＤ",
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
                { visible: "true", value: "検索", id: "DAI08009_Search", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                // { visible: "true", value: "印刷", id: "DAI08009_Print", disabled: false, shortcut: "F6",
                //     onClick: function () {
                //         vue.print();
                //     }
                // },
                { visible: "true", value: "CSV", id: "DAI08009_Download", disabled: false, shortcut: "F7",
                    onClick: function () {
                        vue.downloadCSV();
                        //vue.DAI08009Grid1.vue.exportData("csv", false, true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "詳細", id: "DAI08009Grid1_Detail", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                { visible: "true", value: "新規登録", id: "DAI08009Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.showNewDetail();
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            if (!vue.params) {
                vue.viewModel.DateStart = moment().format("YYYY年MM月DD日");
                vue.viewModel.DateEnd = moment().format("YYYY年MM月DD日");
            } else {
                vue.viewModel.BushoCd = vue.params.BushoCd;
                vue.viewModel.CustomerCd = vue.params.CustomerCd;
                vue.viewModel.DateStart = moment(vue.params.DeliveryDate, "YYYY年MM月DD日").add(-1, "month").startOf("month").format("YYYY年MM月DD日");
                vue.viewModel.DateEnd = vue.params.DeliveryDate;
            }

            //for Child mode
            if (!!vue.params && !!vue.params.IsChild) {
                vue.DAI08009Grid1 = vue.$refs.DAI08009Grid1.grid;
            }

            //watcher
            vue.$watch(
                "$refs.DAI08009Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI08009Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                }
            );
        },
        CustomerParamsChangedCheckFunc: function(newVal, paramsPrev, vue) {
            var ret = !!newVal.UserBushoCd;
            return ret;
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

                //フィルタ変更
                vue.filterChanged();

                //条件変更
                vue.conditionChanged();
            }
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onKeyWordChanged: _.debounce(function(event) {
            var vue = this;

            vue.viewModel.KeyWord = event.target.value;

            //フィルタ変更
            vue.filterChanged();
        }, 300),
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08009Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.searchParams.BushoCd || !vue.searchParams.DateStart || !vue.searchParams.DateEnd) return;

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
            var grid = vue.DAI08009Grid1;

            if (!grid) return;

            var rules = [];

            if (!!vue.viewModel.CustomerCd) {
                rules.push({ dataIndx: "得意先ＣＤ", condition: "equal", value: vue.viewModel.CustomerCd });
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
                v.KeyWord = _.keys(v).filter(k => k != "InitialValue" && k != /^pq.*/).map(k => v[k]).join(",");
                return v;
            });

            vue.filterChanged();

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI08009Grid1;
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
            params.Parent = vue;

            //DAI08010を子画面表示
            PageDialog.show({
                pgId: "DAI08010",
                params: params,
                isModal: true,
                isChild: true,
                width: 1250,
                height: 775,
            });
        },
        showNewDetail: function(rowData) {
            var vue = this;

            var params = {};
            params.IsChild = true;
            params.IsNew = true;
            params.Parent = vue;

            //DAI08010を子画面表示
            PageDialog.show({
                pgId: "DAI08010",
                params: params,
                isModal: true,
                isChild: true,
                width: 1250,
                height: 775,
            });
        },
        print: function () {
        },
        downloadCSV: function() {
            var vue = this;
            var grid = vue.DAI08009Grid1;

            var csv = '\ufeff' + '配達時間,受注NO,配達日付,得意先ＣＤ,得意先名,電話番号１,ＦＡＸ１,住所,配達先,エリアＣＤ,エリア名称,配達名称,地区名称,税名称\n'
            grid.pdata.forEach(el => {
                var line = el['配達時間']
                             + ',' + this.cnvNull(el['受注Ｎｏ'])
                             + ',' + this.cnvNull(moment(el['配達日付']).format("YYYY/MM/DD"))
                             + ',' + this.cnvNull(el['得意先ＣＤ'])
                             + ',' + this.cnvNull(el['得意先名'])
                             + ',' + this.cnvNull(el['電話番号１'])
                             + ',' + this.cnvNull(el['ＦＡＸ１'])
                             + ',' + this.cnvNull(el['住所'])
                             + ',' + this.cnvNull(el['配達先'])
                             + ',' + this.cnvNull(el['エリアＣＤ'])
                             + ',' + this.cnvNull(el['エリア名称'])
                             + ',' + this.cnvNull(el['配達名称'])
                             + ',' + this.cnvNull(el['地区名称'])
                             + ',' + this.cnvNull(el['税名称'])
                              + '\n';
                csv += line;
            })
            let blob = new Blob([csv], { type: 'text/csv' })
            let link = document.createElement('a')
            link.href = window.URL.createObjectURL(blob)
            var nowtime=moment().format('YYYYMMDDHHmmss');
            link.download = '仕出処理_受注問合せ_'+ nowtime +'.csv'
            link.click()
        },
        cnvNull: function(pVal) {
            if(pVal==null)
            {
                return '';
            }
            else
            {
                return pVal;
            }
        },
    }
}
</script>
