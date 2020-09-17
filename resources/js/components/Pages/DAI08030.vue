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
            <div class="col-md-1">
                <label>配達日付</label>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-5">
                <label class="text-center">時間</label>
                <DatePickerWrapper
                    id="DeliveryTimeStart"
                    ref="DatePicker_DeliveryTimeStart"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryTimeStart"
                    :editable=true
                    customStyle="width: 85px;"
                    :onChangedFunc=onTimeChanged
                />
                <label style="width: unset; text-align: center; margin-left: 5px; margin-right: 5px;">～</label>
                <DatePickerWrapper
                    id="DeliveryTimeEnd"
                    ref="DatePicker_DeliveryTimeEnd"
                    format="HH時mm分"
                    :config="{stepping: 5}"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="DeliveryTimeEnd"
                    :editable=true
                    customStyle="width: 85px;"
                    :onChangedFunc=onTimeChanged
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>エリア</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="AreaSelect"
                    ref="PopupSelect_Area"
                    :vmodel=viewModel
                    bind="AreaCd"
                    dataUrl="/DAI08030/GetCourseList"
                    :params="{ BushoCd: viewModel.BushoCd, WithZero: true }"
                    :dataListReset=true
                    title="エリア一覧"
                    labelCd="エリアCD"
                    labelCdNm="エリア名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=200
                    :onAfterChangedFunc=onAreaChanged
                    :isShowAutoComplete=true
                />
            </div>
            <div class="col-md-1">
                <label style="width: unset;">配達区分</label>
            </div>
            <div class="col-md-2">
                <VueCheckList
                    id="DeliveryKbn"
                    ref="VueCheckList_DeliveryKbn"
                    :vmodel=viewModel
                    bind="DeliveryKbn"
                    customTitleStyle="justify-content: center;"
                    customContentStyle="width: auto; margin-right: 15px;"
                    :list="[
                        {code: '0', name: '配達', label: '配達'},
                        {code: '1', name: '店渡し', label: '店渡し'},
                    ]"
                    :onChangedFunc=onDeliveryKbnChanged
                />
            </div>
        </div>
        <PqGridWrapper
            id="DAI08030Grid1"
            ref="DAI08030Grid1"
            dataUrl="/DAI08030/GetChumonList"
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
form[pgid="DAI08030"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI08030"] .top-wrap {
    align-items: flex-start;
    white-space: pre-wrap !important;
}
form[pgid="DAI08030"] .pq-grid-header-table .pq-td-div {
    height: 25px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI08030",
    components: {
    },
    computed: {
        hasSelectionRow: function() {
            var vue = this;
            var grid = vue.DAI08030Grid1;
            return !!grid && !!grid.getSelectionRowData();
        },
        searchParams: function() {
            var vue = this;
            var ms = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日");
            return {
                BushoCd: vue.viewModel.BushoCd,
                DeliveryDate: ms.isValid() ? ms.format("YYYYMMDD") : null,
            };
        },
    },
    watch: {
    },
    data() {
        var vue = this;
        return $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理 > 配送予定入力",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                DeliveryDate: null,
                DeliveryTimeStart: null,
                DeliveryTimeEnd: null,
                AreaCd: null,
                DeliveryKbn: ["0", "1"],
            },
            DAI08030Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI08030_toolbar",
                    items: [
                        {
                            name: "first",
                            type: "button",
                            label: "<i class='fa fa-angle-double-up fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toFirst" title="先頭へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "upward",
                            type: "button",
                            label: "<i class='fa fa-angle-up fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toUpward" title="上へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "downward",
                            type: "button",
                            label: "<i class='fa fa-angle-down fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toDownward" title="下へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "last",
                            type: "button",
                            label: "<i class='fa fa-angle-double-down fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toLast" title="末尾へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 45, minWidth: 45 },
                autoRow: false,
                rowHt: 75,
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
                draggable: true,
                dragModel: {
                    on: true,
                    diHelper: ["配達時間", "得意先名"],
                    cssHelper: {
                        opacity: 0.8,
                        position:"absolute",
                        height: 25,
                        width: "auto",
                        overflow: "hidden",
                        background: "white",
                        border: "1px groove",
                        boxShadow:"4px 4px 2px gray",
                        zIndex: 4001,
                    },
                    dragNodes: (rd, evt) => {
                        var grid = eval("this");
                        var target = grid.SelectRow().getSelection().map(v => v.rowData);
                        target = target.length && target.includes(rd) ? target : [rd];
                        return target;
                    },
                    beforeDrop: function(evt, ui) {
                        var grid = eval("this");

                        // if (!evt.ctrlKey) {
                        //     var nodes = _.cloneDeep(grid.Drag().getUI().nodes);
                        //     grid.deleteNodes(nodes);
                        // }
                    },
                },
                dropModel: {
                    on: true,
                    isDroppable: function(evt, ui) {
                        var grid = eval("this");

                        var dragGrid = $(evt.target).closest(".pq-grid").pqGrid("getInstance").grid;
                        var dropGrid = ui.$cell.closest(".pq-grid").pqGrid("getInstance").grid;

                        var ret = true;
                        // if (dragGrid.widget().prop("id") != dropGrid.widget().prop("id")) {
                        //     var nodes = dragGrid.Drag().getUI().nodes;
                        //     ret = _.intersectionBy(nodes, dropGrid.getData(), "得意先ＣＤ").length == 0;
                        // }

                        return ret;
                    },
                    enter: function(event, grid) {
                    },
                },
                moveNode: ( event, ui ) => {
                    var grid = $(event.target).pqGrid("getInstance").grid;

                    //reset pq_ri
                    grid.pdata.forEach((v, i) => v.pq_ri = i)

                    // vue.resetData(grid);
                },
                colModel: [
                    {
                        title: "No",
                        dataIndx: "配達順",
                        dataType: "string",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                    },
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
                                title: "配達区分",
                                dataIndx: "配達区分名",
                                dataType: "string",
                                width: 100, minWidth: 100, maxWidth: 100,
                                render: ui => {
                                    var $div = $("<div>").addClass("w-100")
                                        .append(
                                            $("<div>").addClass("text-right").html(ui.rowData.受注Ｎｏ || "&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html("&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.配達区分名 || "&nbsp")
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "得意先",
                        dataIndx: "得意先名",
                        colModel: [
                            {
                                title: "住所/配達先",
                                dataIndx: "配達先",
                                dataType: "string",
                                width: 250, minWidth: 250,
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
                                            $("<div>").addClass("text-left").html(ui.rowData.住所 || "&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-center").html(ui.rowData.配達先 || "&nbsp")
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "電話",
                        dataIndx: "電話番号１",
                        colModel: [
                            {
                                title: "連絡区分",
                                dataIndx: "連絡区分名",
                                dataType: "string",
                                width: 125, minWidth: 125, maxWidth: 125,
                                render: ui => {
                                    var $div = $("<div>")
                                        .append(
                                            $("<div>").addClass("text-right").html(ui.rowData.電話番号１ || "&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html("&nbsp")
                                        )
                                        .append(
                                            $("<div>").addClass("text-left").html(ui.rowData.連絡区分名 || "&nbsp")
                                        )
                                        ;

                                    return $div.prop("outerHTML");
                                },
                            },
                        ],
                    },
                    {
                        title: "エリア",
                        dataIndx: "エリアＣＤ",
                        dataType: "string",
                        width: 150, minWidth: 150, maxWidth: 150,
                        render: ui => {
                            var $div = $("<div>")
                                .append(
                                    $("<div>").addClass("text-left").html(!!ui.rowData.エリアＣＤ ? _.padStart(ui.rowData.エリアＣＤ, 3, "0") : "&nbsp")
                                )
                                .append(
                                    $("<div>").addClass("text-left").html(ui.rowData.エリア名 || "&nbsp")
                                )
                                .append(
                                    $("<div>").addClass("text-left").html("&nbsp")
                                )
                                ;

                            return $div.prop("outerHTML");
                        },
                    },
                    {
                        title: "数量",
                        dataIndx: "合計数量",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "金額",
                        dataIndx: "合計金額",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "消費税",
                        dataIndx: "合計消費税",
                        dataType: "integer",
                        format: "#,##0",
                        width: 100, minWidth: 100, maxWidth: 100,
                    },
                    {
                        title: "完了時間",
                        dataIndx: "完了時間",
                        dataType: "date",
                        align: "center",
                        width: 75, minWidth: 75, maxWidth: 75,
                        render: ui => {
                            var mt = moment(ui.rowData.完了時間);
                            return {
                                text: !!mt.isValid() ? mt.format("HH:mm") : null,
                                style: "color: blue;",
                            };
                        },
                    },
                    {
                        title: "配達時間",
                        dataIndx: "配達時間",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "エリアＣＤ",
                        dataIndx: "エリアＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "配達区分",
                        dataIndx: "配達区分",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "Helper",
                        dataIndx: "Helper",
                        dataType: "string",
                        hidden: true,
                        render: ui => {
                            return { text: ui.rowData.配達時間 + " " + ui.rowData.得意先名 };
                        },
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
                { visible: "true", value: "検索", id: "DAI08030_Search", disabled: false, shortcut: "F6",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "注文入力", id: "DAI08030Grid1_Detail", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.showDetail();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "個別売上反映", id: "DAI08030Grid1_UpdUriage", disabled: true, shortcut: "F7",
                    onClick: function () {
                        vue.updateUriage();
                    }
                },
                { visible: "true", value: "売上反映", id: "DAI08030Grid1_UpdUriageAll", disabled: true, shortcut: "F8",
                    onClick: function () {
                        vue.updateUriage(true);
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI08030Grid1_Save", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.save();
                    }
                },
                {visible: "false"},
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.DeliveryDate = moment().format("YYYY年MM月DD日");

            //watcher
            vue.$watch(
                "$refs.DAI08030Grid1.selectionRowCount",
                cnt => {
                    console.log("selectionRowCount watcher: " + cnt);
                    vue.footerButtons.find(v => v.id == "DAI08030Grid1_Detail").disabled = cnt == 0 || cnt > 1;
                    vue.footerButtons.find(v => v.id == "DAI08030Grid1_UpdUriage").disabled = cnt == 0 || cnt > 1;
                }
            );

            vue.$watch("$refs.DAI08030Grid1.isSelection", ret => vue.changeToolbarButtons(vue.DAI08030Grid1, vue.$refs.DAI08030Grid1));
            vue.$watch("$refs.DAI08030Grid1.isSelectionFirst", ret => vue.changeToolbarButtons(vue.DAI08030Grid1, vue.$refs.DAI08030Grid1));
            vue.$watch("$refs.DAI08030Grid1.isSelectionLast", ret => vue.changeToolbarButtons(vue.DAI08030Grid1, vue.$refs.DAI08030Grid1));
        },
        changeToolbarButtons: function(grid, gridVue) {
            var isDisabled = !gridVue.isSelection;
            var isFirst = gridVue.isSelectionFirst;
            var isLast = gridVue.isSelectionLast;

            grid.widget().find(".toolbar_button.toFirst").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toUpward").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toDownward").prop("disabled", isDisabled || isLast);
            grid.widget().find(".toolbar_button.toLast").prop("disabled", isDisabled || isLast);
        },
        moveNodesSelf: (event, grid, vue) => {
            var $btn = $(event.currentTarget);

            var nodes = grid.SelectRow().getSelection().map(v => v.rowData);

            var moveAt = 0;
            if ($btn.hasClass("toFirst")) {
                moveAt = 0;
                grid.moveNodes(nodes, moveAt);
            } else if ($btn.hasClass("toUpward")) {
                grid.SelectRow().getSelection().forEach(v => {
                    if (v.rowIndx != 0) {
                        grid.moveNodes([v.rowData], v.rowIndx - 1);
                        moveAt = moveAt < v.rowIndx - 1 ? (v.rowIndx - 1) : moveAt ;
                    } else {
                        moveAt = 0;
                    }
                });
            } else if ($btn.hasClass("toDownward")) {
                grid.SelectRow().getSelection().forEach(v => {
                    if (v.rowIndx != grid.pdata.length - 1) {
                        grid.moveNodes([v.rowData], v.rowIndx + 1 + 1);
                        moveAt = moveAt > v.rowIndx + 1 ? moveAt : v.rowIndx + 1;
                    } else {
                        moveAt = grid.pdata.length - 1;
                    }
                });
            } else if ($btn.hasClass("toLast")) {
                moveAt = grid.pdata.length - 1;
                grid.moveNodes(nodes, moveAt + 1);
            }
            console.log("moveNodesSelf", nodes, moveAt);

            grid.vue.isSelectionFirst = moveAt == 0;
            grid.vue.isSelectionLast = moveAt == grid.pdata.length - 1;

            grid.scrollRow({rowIndxPage: moveAt});
        },
        onBushoChanged: function(code, entities) {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onDateChanged: function() {
            var vue = this;

            //条件変更
            vue.conditionChanged();
        },
        onTimeChanged: function() {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onAreaChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        onDeliveryKbnChanged: function(code, entities) {
            var vue = this;

            //フィルタ変更
            vue.filterChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = vue.DAI08030Grid1;

            if (!grid || !vue.getLoginInfo().isLogOn) return;
            if (!vue.searchParams.BushoCd || !vue.searchParams.DeliveryDate) return;

            if (!force && _.isEqual(grid.options.dataModel.postData, vue.searchParams)) return;

            grid.searchData(vue.searchParams);
        },
        filterChanged: function() {
            var vue = this;
            var grid = vue.DAI08030Grid1;

            if (!grid) return;

            var rules = [];

            var dcrules = [];
            if (!!vue.viewModel.DeliveryTimeStart) {
                dcrules.push({ condition: "gte", value: moment(vue.viewModel.DeliveryTimeStart, "HH時mm分").format("HH:mm") });
            }
            if (!!vue.viewModel.DeliveryTimeEnd) {
                dcrules.push({ condition: "lte", value: moment(vue.viewModel.DeliveryTimeEnd, "HH時mm分").format("HH:mm") });
            }

            if (dcrules.length) {
                rules.push({ dataIndx: "配達時間", mode: "AND", crules: dcrules });
            }

            if (!!vue.viewModel.AreaCd) {
                rules.push({ dataIndx: "エリアＣＤ", condition: "equal", value: vue.viewModel.AreaCd });
            }

            if (!!vue.viewModel.DeliveryKbn.length) {
                var scrules = vue.viewModel.DeliveryKbn.map(s => { return { condition: "contain", mode: "OR", value: s }; });
                rules.push({ dataIndx: "配達区分", mode: "OR", crules: scrules });
            }
            grid.filter({ oper: "replace", mode: "AND", rules: rules });
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            vue.footerButtons.find(v => v.id == "DAI08030Grid1_Save").disabled = !res || !res.length;
            vue.footerButtons.find(v => v.id == "DAI08030Grid1_UpdUriageAll").disabled = !res || !res.length;

            return res;
        },
        showDetail: function(rowData) {
            var vue = this;
            var grid = vue.DAI08030Grid1;
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
            params.部署ＣＤ = vue.searchParams.BushoCd;
            params.配達日付 = vue.searchParams.DeliveryDate;

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
        save: function() {
            var vue = this;
            var grid = vue.DAI08030Grid1;

            var date = moment().format("YYYY-MM-DD HH:mm:ss");

            var SaveList = _(_.cloneDeep(grid.pdata))
                .groupBy(v => v.エリアＣＤ)
                .values()
                .map(g => g.map((v, i) => {
                    //仕出しエリアデータの型に整形
                    var ret = {};
                    ret.部署ＣＤ = v.部署ＣＤ;
                    ret.受注Ｎｏ = v.受注Ｎｏ;
                    ret.注文日付 = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    ret.エリアＣＤ = v.エリアＣＤ;
                    ret.配達順 = i + 1;
                    ret.得意先ＣＤ = v.得意先ＣＤ;
                    ret.修正担当者ＣＤ = vue.getLoginInfo().uid;
                    ret.修正日 = date;

                    return ret;
                }))
                .flatten()
                .value()
                ;

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI08030/Save",
                    params: {
                        SaveList: SaveList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: false,
                    },
                    done: {
                        isShow: false,
                    },
                }
            );
        },
        updateUriage: function(isAll) {
            var vue = this;
            var grid = vue.DAI08030Grid1;

            var date = moment().format("YYYY-MM-DD HH:mm:ss");

            var SaveList = _.cloneDeep(!!isAll ? grid.pdata : grid.SelectRow().getSelection().map(r => r.rowData))
                .filter(v => !!v.配達順)
                .map(v => {
                    var ret = {};
                    ret.配達日付 = moment(vue.viewModel.DeliveryDate, "YYYY年MM月DD日").format("YYYYMMDD");
                    ret.部署ＣＤ = v.部署ＣＤ;
                    ret.受注Ｎｏ = v.受注Ｎｏ;
                    ret.修正担当者ＣＤ = vue.getLoginInfo().uid;
                    ret.修正日 = date;

                    return ret;
                });

            //保存実行
            grid.saveData(
                {
                    uri: "/DAI08030/UpdateUriage",
                    params: {
                        SaveList: SaveList,
                    },
                    optional: this.searchParams,
                    confirm: {
                        isShow: true,
                        title: "売上反映",
                        message: "注文データより売上データを作成します。\n宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.edited) {
                                $.dialogInfo({
                                    title: "登録チェック",
                                    contents: "他で変更されたデータがあります。",
                                });
                                grid.blinkDiff(res.current);
                                return false;
                            } else {
                                return true;
                            }
                        },
                    },
                }
            );
        },
    }
}
</script>
