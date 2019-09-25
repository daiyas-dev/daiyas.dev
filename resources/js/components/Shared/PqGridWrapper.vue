<template>
    <div :id="this.id"></div>
</template>

<script>
import pqgrid from "pqgrid";
window.pq = pqgrid;

pq.aggregate.IntegerTotal = function(arr, col) {
    return pq.formatNumber(pq.aggregate.sum(arr, col), "#,##0");
};
pq.aggregate.FloatTotal = function(arr, col) {
    return pq.formatNumber(pq.aggregate.sum(arr, col), "##,###.0");
};

//localize
import "pqgrid/localize/pq-localize-ja.js";

//inner component
import PopupSelect from "@vcs/PopupSelect.vue";
import DatePickerWrapper from "@vcs/DatePickerWrapper.vue";

window.getGrid = (selector) => $(selector).pqGrid("getInstance").grid;

export default {
    name: "PqGridWrapper",
    data() {
        return {
            grid: null,
            meta: null,
            scrollX: null,
            scrollY: null,
            cellErrors: null,
            searchErrors: null,
            searchExceptions: null,
            saveErrors: null,
            saveExceptions: null,
            isSearchOnActivate: true,
            isActivated: false,
            CountConstraint: null,
            PopupSelect: PopupSelect,
            DatePickerWrapper: DatePickerWrapper,
        }
    },
    props: {
        id: String,
        classes: String,
        SearchOnCreate: Boolean,
        SearchOnActivate: Boolean,
        dataUrl: String,
        query: Object,
        metaUrl: String,
        options: Object,
        isFixedHeight: Boolean,
        resizeFunc: Function,
        showContextMenu: Boolean,
        contextMenu: Object,
        checkChanged: Boolean,
        autoEmptyRow: Boolean,
        autoEmptyRowCount: Number,
        autoEmptyRowFormula: String,
        autoEmptyRowFunc: Function,
        onBeforeCreateFunc: Function,
        onRefreshFunc: Function,
        onCompleteFunc: Function,
        onSelectChangeFunc: Function,
        onSearchErrorsFunc: Function,
        onSearchExceptionsFunc: Function,
        onSaveErrorsFunc: Function,
        onSaveExceptionsFunc: Function,
        onBeforeAddRowFunc: Function,
        onAddRowFunc: Function,
        onDeleteRowFunc: Function,
        onChangeExceptsColumns: Array,
        onAfterSearchFunc: Function,
        onErrorValsMapFunc: Function,
        canNonSearchInsert: Boolean,
    },
    computed: {
        isDialog: function() {
            var vue = this;
            return $(vue.$parent.$el).closest(".ui-dialog").length == 1;
        },
    },
    created: function () {  //createdは一回きり
        var vue = this;

        vue.isSearchOnActivate = vue.SearchOnActivate == false ? false : true;

        //HTML5 menu polyfill
        $.contextMenu("html5");

        vue.$root.$on("resize", vue.resize);
        vue.$root.$on("plantChanged", function(info) {
            //リロード時等は前回のプラントが残っていないので、その場合は削除しない
            if (!_.isEmpty(info.prevPlant) && info.prevPlant != info.user.plant) {
                if (vue.grid && vue.grid.pdata && vue.grid.pdata.length > 0) {
                    vue.grid.pdata = [];
                    vue.grid.refreshDataAndView();
                }
            }
        });

        //detect filter dialog
        setInterval(() => {
            var conditions = [
                    "",
                    "equal",
                    "notequal",
                    "begin",
                    "notbegin",
                    "end",
                    "notend",
                    "contain",
                    "notcontain",
                    "empty",
                    "notempty",
                    "great",
                    "gte",
                    "less",
                    "lte",
                    "between",
                    "range",
                    "regexp",
                ];

            var conditionsSelect = $("div.pq-filter-menu.pq-popup select");
            if (conditionsSelect.length == 1) {
                var vals = conditionsSelect.children().map((i, o) => o.value);
                if (_.join(conditions) != _.join(vals)) {
                    var options = conditionsSelect.children().sort((a, b) => conditions.indexOf(a.value) > conditions.indexOf(b.value) ? 1 : -1);
                    var selectedIndex = _.findIndex(options.map((i,o) => $(o).prop("selected")), v => !!v);
                    conditionsSelect.append(options);
                    conditionsSelect.prop("selectedIndex", selectedIndex);
                }
            }
        }, 100);

        vue.$parent.$on("panelResize", vue.resize);
    },
    mounted: function () {
        var vue = this;

        //PqGrid基本設定
        var pqGridObj = {
            locale: "ja",
            loading: true,
            editable: false,
            scrollModel: { autoFit: true, theme: true },
            showTitle: false,
            resizable: false,
            showHeader: true,
            showToolbar: false,
            wrap: false,
            hrap: false,
            rowHt: 33,
            rowHtSum: 33,
            animModel: {
                on: true
            },
            selectionModel: {
                type: "cell",
                mode: "block",
                fireSelectChange: true
            },
            swipeModel: { on: true },
            collapsible: {
                on: false,
                collapsed: false,
                toggle: false
            },
            editModel: {
                keyUpDown: true,
                clicksToEdit: 2
            },
            editorEnd: function(event, ui) {
                //getData呼出のcolumn取り違えと同様に、cell及びcell内コンポーネントの表示状態の制御にもバグあり
                //ツールチップが表示されたままとなるので、editorを終了するタイミングで、ツールチップを非表示
                $("*").tooltip("hide");
            },
            historyModel: {
                on: true,
                allowInvalid: true,
                checkEditable: true,
                checkEditableAdd: true,
            },
            filterModel: {
                on: true,
                header: true,
                menuIcon: true,
                hideRows: true,
            },
            sortModel: {
                single: false,
            },
            contextMenu: {
                on: true,
                head: true,
                items: function(event, ui){
                    return (ui.$th? vue.getHeaderContextMenu: vue.getBodyContextMenu)(event, ui);
                }
            },
            postRenderInterval: -1,
            columnTemplate: {
                editable: true,
                resizable: false,
                render: function(ui) {
                    //editable class設定
                    ui.cls = ui.cls || [];
                    if(typeof(ui.column.editable) == "function"){
                        ui.cls.push(ui.column.editable(ui) ? "cell-editable" :  "cell-readonly");
                    } else {
                        ui.cls.push(ui.column.editable ? "cell-editable" :  "cell-readonly");
                    }

                    var value = ui.rowData[ui.dataIndx];
                    if (ui.column.cautionNegative && $.isNumeric(value)) {
                        if (value < 0) {
                            ui.cls.push("cell-negative-value");
                        } else {
                            ui.cls.push("cell-positive-value");
                        }
                    }

                    //nested objectからデータ取得出来るよう拡張
                    if (!ui.rowData[ui.dataIndx]) {
                        try {
                            var val = eval("ui.rowData." + ui.dataIndx)

                            if (val) {
                                ui.cellData = val;
                                ui.formatVal = ["integer", "float"].includes(ui.column.dataType) && ui.column.format
                                    ? pq.formatNumber(val, ui.column.format) : (val + "");
                                ui.rowData[ui.dataIndx] = val;
                                ui.column.editor.getData = ui => _.result(ui.rowData, ui.dataIndx, val);

                                if (ui.column.cautionNegative && $.isNumeric(val)) {
                                    if (val < 0) {
                                        ui.cls.push("cell-negative-value");
                                    } else {
                                        ui.cls.push("cell-positive-value");
                                    }
                                }

                                return ui.formatVal;
                            }
                        } catch(e) {
                            return "";
                        }
                    }

                    //セル内Select設定の共通化
                    if (ui.column.selectList) {
                        var list = ui.rowData[ui.column.selectList] || this.options.vue.$parent.$data[ui.column.selectList];
                        var predicate = ui.column.predicate;

                        //対象リストが取得されていた場合、Select表示の設定
                        if (list) {
                            if (ui.column.selectLabel) {
                                list.map((v) => v.label = ((Cd, CdNm) => eval(ui.column.selectLabel))(v.Cd, v.CdNm));
                            }

                            ui.column.editor = {
                                type: "select",
                                options: function(ui) {
                                    if (!!predicate) {
                                        return _.cloneDeep(list).filter(v => predicate(ui.rowData, ui.column, v));
                                    } else {
                                        return list;
                                    }
                                },
                                valueIndx: "Cd",
                                labelIndx: ui.column.selectLabel ? "label" : "CdNm",
                                prepend: ui.column.selectNullFirst == true ? { "": "" } : null,
                            };
                            ui.column.render = function (ui) {
                                if (!ui.cellData) return ui;

                                var editor = ui.column.editor;
                                var item = editor.options(ui).filter((v) => v[editor.valueIndx] == ui.cellData);

                                var text = item.length == 1 ? item[0][editor.labelIndx] : "";

                               if (typeof ui.column.editable == "function") {
                                    ui.cls.push(ui.column.editable(ui) ? "cell-editable" : "cell-readonly");
                               } else {
                                    ui.cls.push(ui.column.editable ? "cell-editable" : "cell-readonly");
                               }

                                return { text: text };
                            };
                        }
                    }

                    //セル内DatePicker設定の共通化
                    if (ui.column.dataType == "date" && ui.column.editable) {
                        ui.column.binder = {};
                        ui.column.editor = {
                            type: "textbox",
                            init: function(ui) {
                                var grid = this;
                                var vue = grid.options.vue;

                                var editCell = ui.$cell;
                                var gridCell = grid.getCell(ui);

                                ui.column.binder = {
                                    [ui.dataIndx]: ui.rowData[ui.dataIndx],
                                };

                                //create DatePickerWrapper instance
                                var dp = new (VueApp.createInstance(vue.DatePickerWrapper))(
                                    {
                                        propsData: {
                                            id: "DatePicker_" + ui.dataIndx + "_" + ui.rowIndx,
                                            ref: "DatePicker_" + ui.dataIndx + "_" + ui.rowIndx,
                                            vmodel: ui.column.binder,
                                            bind: ui.dataIndx,
                                            width: gridCell.width(),
                                            editable: true,
                                            hideButton: true,
                                            onCalendarHiddenFunc: (event) => {
                                                //grid.getEditCell().$editor.trigger($.Event("keydown", {keyCode: 13, which: 13}))
                                            },
                                        }
                                    }
                                );
                                dp.$mount();

                                //editor element
                                var element = $(dp.$el);
                                element
                                    .addClass("ml-1")
                                    .find("input").on("keydown", event => {
                                        switch (event.which) {
                                            case 9:
                                                vue.setCellState(grid, ui);
                                                vue.moveNextCell(grid, ui, event.shiftKey);
                                                return false;
                                            case 13:
                                                vue.setCellState(grid, ui);
                                                vue.moveNextCell(grid, ui);
                                                return false;
                                            case 27:
                                                grid.quitEditMode();
                                                return false;
                                        }
                                    });

                                //元々のinputを除去
                                editCell.find("input").hide();

                                //セルに格納
                                editCell.append(element);
                                element.show();
                                setTimeout(() => element.find(".target-input").focus(), 100);
                            },
                            getData: function(ui, grid) {
                                return ui.$cell.find(".target-input").val();
                            },
                        };

                        if (this.options.filterModel.on) {
                            ui.column.filter = $.extend(
                                true,
                                {
                                    init: (ui) => {
                                        var grid = this;
                                        if (ui.filterUI.crules.length > 0 && ui.filterUI.crules[0].condition == "between") {
                                            //Bootstrap4 datetimepicker
                                            ui.$cell.find(".pq-grid-hd-search-field")
                                                .each((i, el) => $(el).parent().css("position", "relative"))
                                                .datetimepicker({
                                                    locale: "ja",
                                                    format: "YYYY/MM/DD",
                                                    dayViewHeaderFormat: "YYYY/MM",
                                                    useCurrent: false,
                                                    widgetParent: "body",
                                                })
                                                .on("dp.show", (event) => {
                                                    var $dp = $(event.target);
                                                    var top = ($dp.offset().top - 300);
                                                    var left = $dp.offset().left;
                                                    if ($dp.offset().top - 400 <= 0) {
                                                        top = $dp.offset().top + $dp.outerHeight();
                                                    }
                                                    $(".bootstrap-datetimepicker-widget").css({
                                                        top: top + "px",
                                                        left: left + "px",
                                                        bottom: "auto",
                                                        right: "auto",
                                                    });
                                                })
                                                .on("dp.change", (event) => {
                                                    var $dp = $(event.target);
                                                    var idx = ui.$cell.find(".pq-grid-hd-search-field").index($dp);

                                                    var col = grid.options.colModel.filter(c => c.dataIndx == ui.dataIndx);

                                                    if (col[0].filter.crules.filter(r => r.condition == "between")[0][idx == 0 ? "value" : "value2"] != $dp.val()) {
                                                        col[0].filter.crules.filter(r => r.condition == "between")[0][idx == 0 ? "value" : "value2"] = $dp.val();
                                                        ui.filterUI.crules = col[0].filter.crules;

                                                        var newRules = _.flatten(
                                                            grid.options.colModel
                                                                .filter(c => c.filter && c.filter.on)
                                                                .map(c => {
                                                                    return c.filter.crules
                                                                        .filter(r => r.value || r.value2)
                                                                        .map(r => {
                                                                            r.dataIndx = c.dataIndx;
                                                                            return r;
                                                                        });
                                                                })
                                                                .filter(v => v.length > 0)
                                                        );

                                                        grid.filter({ rules: newRules });
                                                    }
                                                });
                                        }
                                        return ui;
                                    },
                                    listener: (ui) => {
                                    },
                                },
                                ui.column.filter || {},
                            );
                            ui.column.filterFn = (ui) => {
                            };
                        }
                    }

                    //セル内PopupSelect設定の共通化
                    if (ui.column.dataUrl && ui.column.editable) {
                        ui.column.binder = {};
                        ui.column.editor = {
                            type: "textbox",
                            init: function(ui) {
                                var grid = this;
                                var vue = grid.options.vue;

                                var editCell = ui.$cell;
                                var gridCell = grid.getCell(ui);

                                ui.column.binder = {
                                    [ui.dataIndx]: ui.rowData[ui.dataIndx],
                                    [ui.column.buddy]: ui.rowData[ui.column.buddy],
                                };

                                //create PopupSelect instance
                                var dp = new (VueApp.createInstance(vue.PopupSelect))(
                                    {
                                        propsData: {
                                            id: "PopupSelect_" + ui.dataIndx + "_" + ui.rowIndx,
                                            ref: "PopupSelect_" + ui.dataIndx + "_" + ui.rowIndx,
                                            vmodel: ui.column.binder,
                                            bind: ui.dataIndx,
                                            buddy: ui.column.buddy,
                                            dataUrl: ui.column.dataUrl,
                                            params: ui.column.params,
                                            title: ui.column.selectorTitle || (ui.column.title + "一覧"),
                                            labelCd: ui.column.labelCd || (ui.column.title + "コード"),
                                            labelCdNm: ui.column.labelCdNm || (ui.column.title + "名称"),
                                            isGetName: ui.column.isGetName || false,
                                            isModal: ui.column.isModal || true,
                                            editable: ui.column.editable || true,
                                            reuse: ui.column.reuse || true,
                                            existsCheck: ui.column.existsCheck || true,
                                            width: gridCell.width(),
                                        }
                                    }
                                );
                                dp.$mount();

                                //editor element
                                var element = $(dp.$el);
                                ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};
                                element.on("keydown", (event) => {
                                    switch (event.which) {
                                        case 9:
                                            if (($(event.target).hasClass("clear-button") && !event.shiftKey) ||
                                                ($(event.target).hasClass("target-input") && event.shiftKey)) {
                                                vue.setCellState(grid, ui);
                                                vue.moveNextCell(grid, ui, event.shiftKey);
                                                return false;
                                            }
                                            return true;
                                        case 13:
                                            vue.setCellState(grid, ui);
                                            vue.moveNextCell(grid, ui);
                                            return false;
                                        case 27:
                                            grid.quitEditMode();
                                            return false;
                                    }
                                })
                                .addClass("ml-1")
                                ;

                                //元々のinputを除去
                                editCell.find("input").hide();

                                //セルに格納
                                editCell.append(element);
                                element.show();
                                setTimeout(() => element.find(".target-input").focus(), 100);
                            },
                            getData: function(ui, grid) {
                                return ui.$cell.find(".target-input").val();
                            },
                        };
                        ui.column.postRender = function (ui) {
                            var grid = this;
                            var vue = grid.options.vue;

                            var gridCell = $(ui.cell);

                            ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};
                            if (ui.rowData.pq_inputErrors[ui.dataIndx]) {
                                gridCell
                                    .addClass("ui-state-error")
                                    .tooltip({
                                        animation: false,
                                        placement: "auto",
                                        trigger: "hover",
                                        title: ui.rowData.pq_inputErrors[ui.dataIndx],
                                        container: "body",
                                        template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                                    });
                            } else {
                                gridCell
                                    .removeClass("ui-state-error")
                                    .tooltip("dispose");
                            }

                            return ui;
                        };
                    }

                    //セル内textarea設定の共通化
                    if (!!ui.column.editor && ui.column.editor.type == "textarea") {
                        ui.column.editor.init = (ui) => {
                            var grid = this;
                            var vue = grid.options.vue;

                            var editCell = ui.$cell;
                            var gridCell = grid.getCell(ui);

                            editCell.find("textarea")
                                .addClass("form-control")
                                .css("max-width", gridCell.outerWidth())
                                .css("max-height", gridCell.outerHeight() * 2)
                                ;
                        };

                        //styles
                        // ui.column.editor.style = (ui) => {
                        //     var grid = this;
                        //     var vue = grid.options.vue;

                        //     var editCell = ui.$cell;
                        //     var gridCell = grid.getCell(ui);

                        //     return "max-width: " + gridCell.width() + "px; " +
                        //            "max-height: " + gridCell.height() + "px; ";
                        // };

                        //Enterでの改行可能
                        ui.column.editModel = { keyUpDown: false, saveKey: '' };
                    }

                    return ui;
                },
            },
            trackModel: {
                on: true,
            },
            toolbar: {
                items: [
                    {
                        name: "insertRow",
                        type: "button",
                        label: "<i class='fa fa-plus-square'></i>" + "行追加",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            var row = grid.SelectRow().getSelection();

                            var range;
                            try {
                                range = grid.Selection().getSelection();

                                if (!!grid.pdata && !grid.pdata[range[0].rowIndx]) {
                                    range = [];
                                }
                            } catch(e) {
                                range = [];
                            }

                            var idx = row.length > 0 ? (row[0].rowIndx + 1) : range.length > 0 ? (range[0].rowIndx + 1) : (!!grid.pdata && grid.pdata.length > 0) ? grid.pdata.length : 0 ;

                            //grouping時の補正
                            var isGrouping = !!grid.options.groupModel && grid.options.groupModel.on;
                            if (isGrouping) {
                                idx = idx == 0 ? 0 : (idx - grid.pdata.slice(0, idx).filter(v => v.pq_level == 0).length);
                            }

                            var newRow = grid.options.vue.onBeforeAddRowFunc ? grid.options.vue.onBeforeAddRowFunc(grid, range[0]) : {};

                            var ridx = grid.addRow({ rowData: newRow, rowIndx: idx, checkEditable: false, history: !isGrouping });

                            //パラメータ指定行追加関数の実行
                            grid.options.vue.onAddRowFunc ? grid.options.vue.onAddRowFunc(grid, grid.pdata[ridx], ridx) : null;

                            grid.refreshView();
                        },
                        attr: "insertRow",
                        options: { disabled: false },
                    },
                    {
                        name: "deleteRow",
                        type: "button",
                        label: "<i class='fa fa-minus-square'></i>" + "行削除",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;

                            grid.quitEditMode();

                            var row = grid.SelectRow().getSelection();

                            var range;
                            try {
                                range = grid.Selection().getSelection();
                            } catch(e) {
                                range = [];
                            }

                            var idx = row.length > 0 ? row[0].rowIndx : range.length > 0 ? range[0].rowIndx : 0;
                            var pq_ri = grid.pdata[idx].pq_ri;

                            //dataModel.dataでのindexを取得
                            idx = _.findIndex(grid.options.dataModel.data, {pq_ri: pq_ri});

                            var isGrouping = !!grid.options.groupModel && grid.options.groupModel.on;

                            //パラメータ指定行削除関数の実行
                            grid.options.vue.onDeleteRowFunc ? grid.options.vue.onDeleteRowFunc(grid, idx) : grid.deleteRow({ rowIndx: idx, history: !isGrouping });

                            grid.refreshView();
                        },
                        attr: "deleteRow",
                        options: { disabled: true },
                    },
                    { type: "separator" },
                    {
                        name: "cutRange",
                        type: "button",
                        label: "<i class='fa fa-cut'></i>" + "切り取り",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            grid.cut();
                        },
                        attr: "cutRange",
                        options: { disabled: true },
                    },
                    {
                        name: "copyRange",
                        type: "button",
                        label: "<i class='fa fa-copy'></i>" + "コピー",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            grid.copy();
                        },
                        attr: "copyRange",
                        options: { disabled: true },
                    },
                    {
                        name: "pasteRange",
                        type: "button",
                        label: "<i class='fa fa-paste'></i>" + "貼り付け",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            grid.paste();
                        },
                        attr: "pasteRange",
                        options: { disabled: true },
                    },
                    { type: "separator" },
                    {
                        name: "clear",
                        type: "button",
                        label: "<i class='fa fa-ban'></i>" + "クリア",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;

                            var range = grid.Range({r1: 0, rc: grid.pdata.length });
                            range.clear();
                        },
                        attr: "clear",
                        options: { disabled: false },
                    },
                    { type: "separator" },
                    {
                        name: "undo",
                        type: "button",
                        label: "<i class='fa fa-undo'></i>" + "元に戻す",
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            grid.history({ method: "undo" });
                        },
                        attr: "undo",
                        options: { disabled: true },
                    },
                    {
                        name: "redo",
                        type: "button",
                        label: "<i class='fa fa-repeat'></i>" + "やり直し",  /* FontAwesome5 => fa-redo */
                        listener: function (grid) {
                            grid = (grid instanceof $.Event) ? this : grid;
                            grid.history({ method: "redo" });
                        },
                        attr: "redo",
                        options: { disabled: true },
                    },
                    {
                        name: "CountConstraintViolation",
                        type: "<br><label class='CountConstraintViolation'></label>",
                        attr: "CountConstraintViolation",
                        style: "display: none;",
                    },
                ]
            },
            history: function (event, ui) {
                //console.log("grid history");

                //toolbarボタンの状態変更
                $("button[undo]", this.toolbar()).button("option", { disabled: !(ui.canUndo || ui.num_undo > 0) });
                $("button[redo]", this.toolbar()).button("option", { disabled: !(ui.canRedo || ui.num_redo > 0) });

                //PqGrid-Toolbar設定
                this.options.vue.setToolbarState();
            },
            refresh: function (event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var id = vue.$el.id;

                //データ読込済みの場合、計算式及び集計行の為の再描画(最新版のFormulaにより解決した模様なのでC/O)
                //if (grid.pdata && grid.pdata.length > 0 && !grid.isRendered) {
                //    grid.isRendered = true;
                //    grid.refreshView();
                //    return false;
                //} else {
                //    grid.isRendered = false;
                //}

                vue.resize();

                //ヘッダ行中央寄せ
                //$("#" + id + " .pq-grid-col *").css("text-align", "center");
                //$("#" + id + " .pq-grid-col *").css("vertical-align", "middle");

                //NumberCell選択時に行選択
                $("#" + id + " .pq-grid-number-cell").off("click").on("click", vue.selectRow);

                //パラメータ指定更新関数
                if (vue.onRefreshFunc && vue.grid) {
                    vue.onRefreshFunc(grid);
                }

                //検索時エラー/例外設定
                if (vue.searchErrors) {
                    vue.onSearchErrors(grid, vue.searchErrors);
                    //初回更新終了設定
                    vue.searchErrors.isNew = false;
                } else if (vue.searchExceptions) {
                    vue.onSearchExceptions(grid, vue.searchExceptions);
                    //初回更新終了設定
                    vue.searchExceptions.isNew = false;
                }

                //保存時エラー/例外設定
                if (vue.saveErrors) {
                    vue.onSaveErrors(grid, vue.saveErrors);
                    //初回更新終了設定
                    vue.saveErrors.isNew = false;
                } else if (vue.saveExceptions) {
                    vue.onSaveExceptions(grid, vue.saveExceptions);
                    //初回更新終了設定
                    vue.saveExceptions.isNew = false;
                }

                //件数制約違反設定
                if (!!vue.CountConstraint) {
                    var CountConstraintViolation = grid.options.toolbar.items.filter(v => v.name == "CountConstraintViolation")[0];
                    if (CountConstraintViolation) {
                        var labelViolation = "<label class='CountConstraintViolation'></label>";
                        if (vue.CountConstraint.isViolation) {
                            labelViolation = "<label class='CountConstraintViolation'>"
                                           + "表示上限を超えています。適切な条件を設定してください。"
                                           + "(" + vue.CountConstraint.ActualLength + "/" + vue.CountConstraint.ConstraintLength + ")"
                                           + "</label>";
                        }

                        if (CountConstraintViolation.type != labelViolation) {
                            CountConstraintViolation.type = labelViolation;

                            if (!grid.options.showToolbar) {
                                grid.options.toolbar.items
                                    .filter(item => ["button", "separator"].includes(item.type))
                                    .forEach(item => item.style = "display: none;");

                                if (grid.options.toolbar.items.some(item => item.style != "display: none;")) {
                                    CountConstraintViolation.type = "<br>" + CountConstraintViolation.type;
                                }

                                grid.options.showToolbar = true;
                            }

                            grid.refreshToolbar();
                        }
                    }
                }

                //PqGrid-Toolbar設定
                vue.setToolbarState(grid);

                //自動空行補完
                if (!grid.loading && !!grid.pdata && vue.autoEmptyRow) {
                    var empties = 0;

                    if (!!vue.autoEmptyRowFormula && vue.autoEmptyRowFormula.includes("n")) {
                        //calcurate n value
                        var expr1 = Algebra.parse(vue.autoEmptyRowFormula);
                        var expr2 = Algebra.parse(grid.pdata.length + " + a");

                        for (var n = 0; true; n++) {
                            var eq = new Algebra.Equation(expr1.eval({ n: n }), expr2);
                            var ret = eq.solveFor("a").toString() * 1;

                            if (ret >= 0) {
                                empties = ret;
                                break;
                            }
                        }
                    } else if (!!vue.autoEmptyRowCount && _.isInteger(vue.autoEmptyRowCount)) {
                        empties = vue.autoEmptyRowCount;
                    } else {
                        empties = 1;
                    }

                    var actuals = _.cloneDeep(grid.pdata)
                        .reverse()
                        .slice(0, empties)
                        .map(d => _(d).omitBy((v, k) => k.startsWith("pq") || !v).keys().value().length)
                        .filter(v => v == 0)
                        .length
                        ;

                    if (empties > actuals) {
                        grid.addRow({
                            rowList: _.fill(Array(empties - actuals), {})
                                        .map((v, i) => {
                                            return {
                                                newRow: grid.autoEmptyRowFunc ? grid.autoEmptyRowFunc(grid) : {},
                                                rowIndx: grid.pdata.length + i,
                                            };
                                        }),
                            checkEditable: false,
                        });
                    }
                }

                //Bootstrap tooltip設定
                //refreshイベントのタイミングではレンダリングが完了していない(PqGridが見栄えの為か、timerを生成して実行しているため)
                //そのため、この設定もtimerを用いて実施
                setTimeout(function() {
                    var selector = "#" + id + " .pq-grid-cell:not(.ui-error-state)";
                    var isDialog = vue.isDialog;

                    var checkOverflow = (cell, target) => {
                        //justify-contentを保持
                        var justify = $(cell).css("justify-content");

                        //offset/scrollのwidth/height取得
                        var offsetWidth = cell.offsetWidth;
                        var scrollWidth = cell.scrollWidth;
                        var offsetHeight = cell.offsetHeight;
                        var scrollHeight = cell.scrollHeight;

                        if (justify != "flex-start") {
                            //clone生成
                            var clone = $(cell).clone();

                            //justify-contentをflex-startに変更
                            clone.css("justify-content", "flex-start");

                            //offset/scrollのwidth/height再取得
                            offsetWidth = clone.offsetWidth;
                            scrollWidth = clone.scrollWidth;
                            offsetHeight = clone.offsetHeight;
                            scrollHeight = clone.scrollHeight;
                        }

                        var title = $(target).attr("title") || $(target).text().replace(/(, )+$/, "").replace(/, /g, "<br>");

                        return (offsetWidth < scrollWidth || offsetHeight < scrollHeight)  && !!title ? title : "";
                    };

                    $("*").tooltip("hide");
                    $(selector)
                        .tooltip({
                            container: "body",
                            animation: false,
                            template: '<div class="tooltip text-overflow" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                            placement: "auto",
                            trigger: "hover",
                            title: function() {
                                var cell = this;

                                //改行以外のchildrenが存在する場合、その要素を対象
                                var children = $(cell).children().filter((i,v) => v.tagName != "BR");

                                if (children.length > 0) {
                                    children.each((i, v) => {
                                        var title = checkOverflow(cell, v);
                                        if (!!title) {
                                            //個別にツールチップ設定
                                            $(v).tooltip({
                                                container: "body",
                                                animation: false,
                                                placement: "auto",
                                                trigger: "hover",
                                                html: true,
                                                title: title,
                                            });
                                        }
                                    });

                                    return "";
                                } else {
                                    return checkOverflow(cell, cell);
                                }
                            },
                        });
                }, 100);
            },
            complete: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;

                //完了時更新関数
                if (vue.onCompleteFunc && vue.grid) {
                    vue.onCompleteFunc(grid, ui);
                }

                $("body > div").has("a:contains(ParamQuery)").hide();
            },
            render: function(event, ui) {
                //console.log("grid render");
            },
            change: function (event, ui) {
                //console.log("grid change");

                this.refreshView();
            },
            click: function (event, ui) {
                //console.log("grid click");
            },
            cellClick: function (event, ui) {
                //TODO: checkbox toggle by cell click
                // var current = event.currentTarget;
                // var original = event.originalEvent.target;
                // var cb = ui.$td.has(":checkbox");
                // if (cb.length) {
                //     cb[0].click();
                // }
            },
            cellSave: function (event, ui) {
                if (ui.newVal != ui.oldVal) {
                    ui.rowData[ui.dataIndx] = ui.newVal;
                    this.refreshView();
                }
            },
            scroll: function (event, ui) {
                //スクロール中にbootstrap tooltipのゴミが残るので消去(bootstrapの処理にモレ)
                $("body").find("[id^=tooltip]").tooltip("hide");
            },
            scrollStop: function (event, ui) {
                //console.log("grid scrollStop");
            },
            selectChange: function(event, ui) {
                //console.log("grid selectChange");

                var grid = this;
                var vue = grid.options.vue;
                var id = vue.$el.id;

                //セル選択設定で行選択をしている場合は解除
                if (grid.SelectRow().getSelection().length > 0) {
                    grid.setSelection(null);
                }

                //exit editor
                var editCell = grid.getEditCell().$cell;
                if (editCell) {
                    var indices = grid.getCellIndices(grid.getEditCell());
                    var next = ui.selection.address()[0];

                    if (next && (indices.rowIndx != next.r1 || indices.colIndx != next.c1)) {
                        vue.setCellState(grid, indices);
                        grid.quitEditMode();
                    }
                }

                //パラメータ指定更新関数
                if (vue.onSelectChangeFunc && vue.grid) {
                    vue.onSelectChangeFunc(grid, ui);
                }

                //PqGrid-Toolbar設定
                vue.setToolbarState();
            },
            selectEnd: function(event, ui) {
                //console.log("grid selectEnd");

                //PqGrid-Toolbar設定
                this.options.vue.setToolbarState();
            },
            rowSelect: function(event, ui) {
                //console.log("grid rowSelect");

                if (ui.addList.length > 0) {
                    var r = ui.addList[0].rowIndx;

                    this.setSelection(null);

                    //表示しているcolIndxのmax/min
                    var cols = this.colModel.filter(c => !c.hidden).map(c => c.leftPos);
                    var c1 =  Math.min.apply(null, cols);
                    var c2 =  Math.max.apply(null, cols);

                    this.Range({ r1: r, rc: 1, c1: c1, c2: c2 }).select();
                }

                //PqGrid-Toolbar設定
                //this.options.vue.setToolbarState();
            },
            cellRightClick: function( event, ui ) {
                //console.log("grid cellRightClick");

                var r = ui.rowIndx, c = ui.colIndx;
                var range = this.Selection()._areas[0];
                var row = this.SelectRow().getSelection();

                if (row.length > 0) {
                    if (row[0].rowIndx != r) {
                        //選択行以外右クリック
                        this.setSelection(null);
                        this.setSelection({
                            rowIndx: ui.rowIndx,
                            colIndx: this.options.selectionModel.row ? null : ui.colIndx
                        });
                    }
                } else {
                    if (!(range && (range.r1 <= r && r <= range.r2 && range.c1 <= c && c <= range.c2))) {
                        //現選択範囲以外右クリック
                        this.setSelection(null);
                        this.setSelection({
                            rowIndx: ui.rowIndx,
                            colIndx: this.options.selectionModel.row ? null : ui.colIndx
                        });
                    }
                }

                //PqGrid-Toolbar設定
                this.options.vue.setToolbarState();

                return true;
            },
            beforeCellKeyDown: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var cell = grid.getCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});

                switch(event.which) {
                    case 13:   //"enter"
                    case 32:   //"space"
                        if (cell.has(":checkbox").length) {
                            cell.find(":checkbox")[0].click();
                        } else if (ui.column.editable) {
                            grid.editCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                        }

                        return false;
                }

                return true;
            },
            cellKeyDown: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var cell = this.getCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});

                if (event.ctrlKey) {
                    switch(event.which) {
                        case 107:   //"+"
                        case 187:   //";"
                            this.addRow({
                                rowIndx: ui.rowIndx + 1,
                                newRow: !!vue.onAddRowFunc ? vue.onAddRowFunc(grid, ui.rowData) : {},
                                checkEditable: false
                            });
                            return false;
                        case 109:   //"-"
                        case 189:   //"-"
                            this.deleteRow({ rowIndx: ui.rowIndx });
                            return false;
                    }
                }

                return true;
            },
            beforeFilter: (evt, ui) => {
                //絞り込み条件変更時に値が失われることの対処
                var vue = this;
                var grid = getGrid(evt.target);
                var values = grid.widget().find("[name='" + ui.rules[0].dataIndx + "'].pq-grid-hd-search-field").map((i, v) => $(v).val());
                var rule = !!ui.rules[0].crules ? ui.rules[0].crules[0] : ui.rules[0];

                rule.value = rule.value || values[0];
                rule.value2 = rule.value2 || values[1];

                if (grid.columns[ui.rules[0].dataIndx].dataType == "date" && rule.value == rule.value2) {
                    rule.value2 = moment(rule.value2).add(1, "days").add(-1, "seconds").format("YYYY/MM/DD HH:mm:ss");
                }
            },
        };

        //VueComponent参照設定
        pqGridObj.vue = vue;

        //事前処理指定
        if (vue.onBeforeCreateFunc) {
            vue.onBeforeCreateFunc(pqGridObj, () => vue.createPqGrid(pqGridObj));
        } else {
            //PqGrid生成
            vue.createPqGrid(pqGridObj);
        }
    },
    beforeUpdated: function () {
        //console.log(this.$options.name + " BeforeUpdated:");
    },
    updated: function () {
        //console.log(this.$options.name + " Updated:");
    },
    activated: function () {    //画面再表示時はこのイベント
        //console.log(this.$options.name + " Activated:");

        //PqGridのリサイズ
        this.resize();

        //スクロール位置の復元
        if (this.grid) {
            if (this.isSearchOnActivate && !this.grid.loading) {
                this.isActivated = true;
                //再検索
                this.grid.searchData(null,true);
            }

            this.grid.scrollX(this.scrollX || 0);
            this.grid.scrollY(this.scrollY || 0);
        }
    },
    deactivated: function () {
        //console.log(this.$options.name + " Deactivated:");

        if (this.grid) {
            //スクロール位置の保持
            this.scrollX = this.grid.scrollX();
            this.scrollY = this.grid.scrollY();
        }
    },
    destroyed: function () {
        //console.log(this.$options.name + " Destroyed:");
    },
    methods: {
        createPqGrid: function(pqGridObj) {
            var postData = $.extend(true, {}, this.query);

            //dataModelの設定
            pqGridObj.dataModel = {
                recIndx: "pq_ri",
                url: this.SearchOnCreate ? this.dataUrl : null,
                method: "POST",
                postData: postData,
                location: "remote",
                beforeSend: function(jqXHR, settings) {
                    //Laravel CSRF Token
                    jqXHR.setRequestHeader("X-CSRF-TOKEN", Laravel.csrfToken);
                },
                getData: function(res) {
                    var grid = this;
                    var vue = grid.options.vue;

                    //画面項目のエラー表示/tooltip設定解除
                    $(".panel .form-control:enabled", $(grid.options.vue.$parent.$el)).closest("div:not(.input-group)").removeClass("has-error");
                    $(".panel .form-control:enabled", $(grid.options.vue.$parent.$el)).tooltip("dispose");

                    //PqGrid内のエラー設定及びtooltipを解除
                    $(".pq-grid .ui-state-error", $(grid.options.vue.$parent.$el)).removeClass("ui-state-error").tooltip("dispose");

                    //検索時エラー/例外オブジェクトの解放
                    vue.searchErrors = null;
                    vue.searchExceptions = null;

                    //保存時エラー/例外オブジェクトの解放
                    vue.saveErrors = null;
                    vue.saveExceptions = null;

                    //初回フラグの設定
                    res.isNew = true;

                    if (res.onError) {
                        //エラー設定
                        vue.searchErrors = res;
                        return { data: [] };
                    } else if (res.onException) {
                        //例外設定
                        vue.searchExceptions = res;
                        return { data: [] };
                    } else if (res.CountConstraint) {
                        //件数制約違反設定
                        vue.CountConstraint = res.CountConstraint;

                        res = res.Data;
                    }

                    //削除用検索時初期値の設定
                    res.forEach(v => v.InitialValue = $.extend(true, {}, v));

                    //検索後callbackが指定されていれば実行
                    if (vue.onAfterSearchFunc) res = vue.onAfterSearchFunc(vue, grid, res);

                    //検索前データの保持
                    grid.prevData = _.cloneDeep(grid.getData());

                    //PKを比較し、検索前のレコードが全てあるか判定
                    if (grid.getData() && _.differenceWith(grid.getData(), res, (a, b) => a.PK == b.PK).length == 0) {
                        //colModelでkeep設定されている列取得
                        var keeps = grid.options.colModel.filter((v) => v.keep).map((v) => v.dataIndx);

                        //対象にPK列も追加
                        keeps.push("PK");

                        //検索前の上記列の値とPKを抽出
                        var keepVals = grid.getData().map((v) => _.pick(v, keeps));

                        //keepした値で復元
                        res.forEach(function(row, i) {
                            var keepVal = keepVals.filter((v) => v.PK == row.PK);
                            if (keepVal.length == 1) {
                                $.extend(true, row, keepVal[0]);
                            }
                        });
                    }

                    //grouping時deleteListの取得バグ対処の為に検索結果を保持
                    grid.searchResult = _.cloneDeep(res);

                    return { data: res };
                },
                error: function(xhr, status, ex) {
                    //console.log("grid getData error");

                    var grid = this;
                    var vue = grid.options.vue;

                    //但し、連続通信によるabortは除外
                    if (status == "abort") {
                        return { data: [] };
                    }

                    vue.searchErrors = null;
                    vue.searchExceptions = null;

                    //例外設定
                    vue.searchExceptions = {};
                    vue.searchExceptions.onException = true;
                    vue.searchExceptions.isNew = true;
                    vue.searchExceptions.statusText = xhr.statusText + "(" + xhr.state() + ") on " + grid.options.dataModel.url;
                    vue.searchExceptions.errors = xhr.responseText || JSON.stringify(xhr);
                    grid.refreshView();
                },
            };

            //メタ情報が設定されている場合、それをcolModelに利用
            if (this.meta) {
                pqGridObj.colModel = this.meta;
            }

            //追加オプションを導入
            $.extend(true, pqGridObj, this.options);

            //共通カラムを導入
            pqGridObj.colModel.push({ title: "自動生成PK", dataType: "integer", dataIndx: "PK", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "プラント", dataType: "string", dataIndx: "Plant", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "作成日", dataType: "string", dataIndx: "CreateDate", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "作成者", dataType: "string", dataIndx: "CreateUser", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "更新日", dataType: "string", dataIndx: "UpdateDate", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "更新者", dataType: "string", dataIndx: "UpdateUser", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "削除日時", dataType: "date", dataIndx: "DeleteDate", editable: false, hidden: true });
            pqGridObj.colModel.push({ title: "Ver.No.", dataType: "integer", dataIndx: "VersionNo", editable: false, hidden: true });

            //PqGridの変更検知のdeleteがバグっているので検索時の値を保持する項目を追加(検索時の値ではなく、削除時の値でdeleteListに格納される)
            pqGridObj.colModel.push(
                { title: "初期値", dataType: "string", dataIndx: "InitialValue", editable: false, hidden: true,
                    render: function (ui) {
                        //console.log("initialValue render")

                        if (ui.attr.filter(v => v.includes("-sum")).length > 0) {
                            //集計行は除外
                            return ui;
                        }

                        //初回設定時に行の全ての値を保持
                        if (!ui.rowData[ui.dataIndx]) {
                            var values = $.extend(true, {}, ui.rowData);
                            ui.rowData[ui.dataIndx] = values;
                            return ui;
                        }
                    }
                }
            );

            //初期表示時検索設定がfalseの場合、生成するまでurlを設定しない
            var url = pqGridObj.dataModel.url || this.dataUrl;
            pqGridObj.dataModel.url = this.SearchOnCreate ? url : null;

            //PqGrid生成
            this.grid = $("#" + this.id)
                .attr("class", this.classes || "ml-3 mt-2 mr-3")
                .pqGrid(pqGridObj)
                .pqGrid("getInstance").grid;

            //PqGrid参照設定
            if (!this.grid) return;
            this.$parent.$data[this.id] = this.grid;

            //colModelのeditableの設定より、PqGridのclassに入力可不可設定
            var cfg = pqGridObj.columnTemplate.editable ? "editable" : "readonly";
            this.grid.widget().addClass("table_" + cfg);

            //urlの再設定
            this.grid.options.dataModel.url = this.SearchOnCreate ? url : null;

            this.grid.concatCellData = function(upper, lower) {
                return ((upper && (upper + "").trim()) ? (upper + "").trim() : "-") + "\n"
                    +  ((lower && (lower + "").trim()) ? (lower + "").trim() : "-");
            };


            //変更有無判定メソッド追加
            this.grid.isChanged = function() {
                var grid = this;

                //変更有無判定
                var isChanged = $.map(grid.createSaveParams(), (v) => v.length > 0).includes(true);

                return isChanged;
            };

            //検索メソッド追加
            this.grid.searchData = function(params, isActivated) {
                var grid = this;

                if (grid.options.vue.checkChanged && grid.isChanged() && !isActivated) {
                    //確認ダイアログ
                    $.dialogConfirm({
                        title: "内容が変更されています",
                        contents: "検索を行いますか？(変更は破棄されます)",
                        buttons:[
                            {
                                text: "はい",
                                class: "btn btn-primary",
                                click: function(){
                                    grid.options.dataModel.url = grid.options.vue.dataUrl

                                    grid.options.dataModel.postData = _.cloneDeep(params || grid.options.dataModel.postData);
                                    grid.refreshDataAndView();

                                    $(this).dialog("close");
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
                } else {
                    grid.options.dataModel.url = grid.options.vue.dataUrl
                    grid.options.dataModel.postData = _.cloneDeep(params || grid.options.dataModel.postData);
                    grid.refreshDataAndView();
                }
            };

            //検索結果クリアメソッド追加
            this.grid.clearData = function(initialArray, callback) {
                var grid = this;
                var vue = grid.options.vue;

                initialArray = initialArray || [];

                var location = grid.options.dataModel.location;

                grid.searchResult = null;
                grid.options.dataModel.location = "local";
                grid.options.dataModel.data = initialArray;
                grid.options.dataModel.postData = {};
                grid.refreshDataAndView();
                grid.options.dataModel.location = location;

                vue.CountConstraint = null;
                var CountConstraintViolation = grid.options.toolbar.items.filter(v => v.name == "CountConstraintViolation")[0];
                if (CountConstraintViolation) {
                    CountConstraintViolation.type = "<label class='CountConstraintViolation'></label>";
                    grid.refreshToolbar();
                }

                if (callback) {
                    callback(grid);
                }
            }

            //保存パラメータ生成メソッド追加
            this.grid.createSaveParams = function(exceptsColumnArray) {
                var grid = this;

                //PqGridの変更リストを編集用にdeepcopy
                var changeList = _.cloneDeep(grid.getChanges());
                try {
                    changeList = $.extend(
                        true,
                        {},
                        JSON.parse(JSON.stringify(changeList, (k, v) => {
                        //JSON.parse($.safeJsonStringify(changeList, (k, v) => {
	                        if (k == "clickTarget") return;
                            var ret = k == "parent" ? null : v == undefined ? null : v; //値がundefinedの要素を考慮し、nullに置換
                            return ret;
                        }))
                    );
                } catch(ex) {
                    console.log("JSON parse/stringify raize exception");
                    console.log(ex);
                    console.log(changeList);
                }

                //grouping時、changeListの取得がバグっているので対処
                var isGrouping = !!grid.options.groupModel && grid.options.groupModel.on;
                if (isGrouping) {
                    //pq_levelを持つ行は除外
                    changeList.deleteList = changeList.deleteList.filter(v => v.pq_level == undefined);

                    //検索時の値から差分抽出
                    var org = _.sortBy(grid.searchResult, "PK");
                    var cur = _.sortBy(grid.options.dataModel.data, "PK");
                    var deletedList = _.xorBy(org, cur, "PK").filter(v => !!v.PK);

                    //deleteListに設定
                    changeList.deleteList = deletedList;

                    //updateListにdeleteListに登録した行が入っている場合の対処(PqGridが履歴からupdateListを作っている模様)
                    changeList.updateList = changeList.updateList.filter(v => !deletedList.map(d => d.PK).includes(v.PK));
                }

                //PqGridバグ対応
                //2行追加し、上位行を削除した場合は問題ないが、下位行を削除した場合はそれがUpdateListに残ってしまう
                //PqGridの変更リストに対し、この時点でのPqGrid内容と比較して存在しないものは削除
                changeList.addList = _.intersectionBy(changeList.addList, grid.getData(), "pq_ri");
                changeList.updateList = _.intersectionBy(changeList.updateList, grid.getData(), "pq_ri");

                //空行追加したものに入力した場合、addListではなくupdateListに入るので、addListに移動
                changeList.updateList.forEach(function(row, i) {
                    //追加済であれば良い
                    if (changeList.addList.map(v => v.pq_ri).indexOf(row.pq_ri) != -1) {
                        return;
                    }

                    //初期値とVersionNoを持つ場合はupdate, 持たない場合はadd
                    if (!row.InitialValue && typeof(row.VersionNo) == "undefined") {
                        changeList.addList.push(row);
                    }
                });

                //addListにupdateListに追加した行が含まれるので補正、PqGridバグっぽい
                changeList.addList.forEach(function(v) {
                    var idx  = changeList.updateList.map(v => v.pq_ri).indexOf(v.pq_ri);

                    if (idx != -1) {
                        changeList.updateList.splice(idx, 1);
                        changeList.oldList.splice(idx, 1);
                    }
                });

                //入力可能項目に入力がないaddListは除外
                changeList.addList = changeList.addList.filter(function(row, i) {
                    var isNotNull = false;
                    $.each(row, function(k, v) {
                        if (grid.columns[k] && grid.columns[k].editable && v) {
                            isNotNull = true;
                            return;
                        }
                    });

                    return isNotNull;
                });

                //例外指定以外で変更されているデータを持たない行は除外
                var excepts = exceptsColumnArray || grid.options.vue.onChangeExceptsColumns;
                var updateList = $.extend(true, [], changeList.updateList);
                updateList.forEach(function(row) {
                    var idx = changeList.updateList.map(v => v.pq_ri).indexOf(row.pq_ri);
                    var oldRow = changeList.oldList[idx];

                    var exists = !excepts ? true : Object.keys(oldRow).filter((k) => !excepts.includes(k)).length > 0;

                    if (!exists) {
                        changeList.updateList.splice(idx, 1);
                        changeList.oldList.splice(idx, 1);
                    }
                });

                //oldListは変更前のカラムの値だけなので、updateListにextendしたものに置換
                changeList.oldList = $.extend(true,
                    [],
                    changeList.updateList,
                    JSON.parse(JSON.stringify(changeList.oldList, (k, v) => v == undefined ? null : v)) //値がundefinedの要素を考慮し、nullに置換
                );

                //deleteListは検索時の値ではなく削除時の値が設定されてしまうため、検索時の値に置換
                changeList.deleteList = changeList.deleteList
                    .filter(v => !!v.InitialValue)
                    .map(v => v.InitialValue);

                //nestしたViewModelの対応(取りあえず1階層のみ)
                $.each(changeList, function(k, list) {
                    $.each(list, function(k, row) {
                        //nestしたキーを取得
                        var nestKeys = Object.keys(row).filter((k) => k.includes("."));

                        //nestしたViewModelに値設定
                        nestKeys.forEach(function(k) {
                            var nk = k.split(".");
                            row[nk[0]] = row[nk[0]] || {};
                            row[nk[0]][nk[1]] = row[k];
                            delete row[k];
                        });

                        //objectのキーを取得
                        var objectKeys = Object.keys(row).filter((k) => !!row[k] && typeof row[k] == "object" && k != "InitialValue" && k != "pq_cellcls");

                        //nestしたViewModelに変更が無ければnullにする
                        objectKeys.forEach(function(k) {
                            if (!!row[k] && !!row["InitialValue"] && !!row["InitialValue"][k] && grid.options.vue._.isEqual(row[k], row["InitialValue"][k])) {
                                delete row[k];
                            }
                        });

                        //初期値objectを削除
                        delete row["InitialValue"];

                        //grouping objectを削除
                        delete row["parent"];
                    });
                });

                //保存用パラメータとして設定
                var params = {
                    AddList: changeList.addList,
                    UpdateList: changeList.updateList,
                    OldList: changeList.oldList,
                    DeleteList: changeList.deleteList,
                };

                return params;
            };

            //保存メソッド追加
            this.grid.saveData = function(options, optionEditFunc) {
                var grid = this;
                var vue = grid.options.vue;

                //groupingを行っているPqGridのparent propety対処
                var params = _.cloneDeep(options.params);
                options.params = $.extend(
                    true,
                    {},
                    JSON.parse(JSON.stringify(params, (k, v) => {
                        var ret = k == "parent" ? null : v == undefined ? null : v;
                        return ret;
                    }))
                );
                var optional = _.cloneDeep(options.optional);
                options.optional = $.extend(
                    true,
                    {},
                    JSON.parse(JSON.stringify(optional, (k, v) => {
                        var ret = k == "parent" ? null : v == undefined ? null : v;
                        return ret;
                    }))
                );

                var defOp = {
                    uri: "",
                    params: $.extend(true, grid.createSaveParams(), options.optional),
                    confirm: {
                        isShow: true,
                        title: "確認",
                        message: "変更内容を保存します。宜しいですか？",
                    },
                    done: {
                        isShow: true,
                        title: "正常終了",
                        message: "変更内容の保存が完了しました。",
                        callback: (vue, grid, res)=>{},
                    },
                    error: {
                        isShow: true,
                        title: "異常終了",
                        message: "変更内容の保存に失敗しました",
                        callback: (vue, grid, error)=>{},
                    },
                };

                var op = $.extend(true, defOp, options);

                //オプション編集関数
                if (optionEditFunc) {
                    optionEditFunc(vue, grid, defOp, options);
                }

                //createSaveParamsの結果ではなく、直接パラメータ指定の場合を考慮し、nestしたViewModelの対処及びInitialValueの除去
                $.each(op.params, function(k, list) {

                    if (Array.isArray(list)) {
                        $.each(list, function(k, row) {
                            //nestしたキーを取得
                            var nestKeys = Object.keys(row).filter((k) => k.includes("."));

                            //nestしたViewModelに値設定
                            nestKeys.forEach(function(k) {
                                var nk = k.split(".");
                                row[nk[0]] = row[nk[0]] || {};
                                row[nk[0]][nk[1]] = row[k];
                                delete row[k];
                            });

                            //objectのキーを取得
                            var objectKeys = Object.keys(row).filter((k) => !!row[k] && typeof row[k] == "object" && k != "InitialValue" && k != "pq_cellcls");

                            //nestしたViewModelに変更が無ければnullにする
                            objectKeys.forEach(function(k) {
                                if (!!row[k] && !!row["InitialValue"] && !!row["InitialValue"][k] && grid.options.vue._.isEqual(row[k], row["InitialValue"][k])) {
                                    delete row[k];
                                }
                            });

                            //初期値objectを削除
                            delete row["InitialValue"];
                        });
                    }
                });

                //保存処理
                var saveFunc = function() {
                    //保存中ダイアログ
                    var saveDlg = $.dialogProgress({
                        contents: "<i class='fa fa-spinner fa-spin' style='font-size: 24px; margin-right: 5px;'></i> 保存中…",
                    });

                    //保存時エラー/例外オブジェクトの解放
                    vue.saveErrors = null;
                    vue.saveExceptions = null;

                    //PqGrid内のエラー設定及びtooltipを解除
                    $(".pq-grid .ui-state-error", $(vue.$parent.$el)).removeClass("ui-state-error").tooltip("dispose");

                    //post dataの保存
                    grid.options.dataModel.postSaveData = op.params;

                    axios.post(op.uri, op.params)
                        .then(response => {

                            var res = response.data;

                            if (res.CountConstraint) {
                                //件数制約違反設定
                                vue.CountConstraint = res.CountConstraint;
                            }

                            //コールバックの実行
                            var ret = op.done.callback(vue, grid, res);

                            if (ret != false) {
                                //PqGridのコミット、データ再取得
                                grid.commit();
                                grid.refreshDataAndView();

                                //メッセージ追加
                                vue.$root.$emit("addMessage", op.done.title + "(" + vue.$parent.page.ScreenTitle + ")");

                                if (op.done.isShow) {
                                    //完了ダイアログ
                                    $.dialogInfo({
                                        title: op.done.title,
                                        contents: op.done.message,
                                    });
                                }
                            }

                            //保存中ダイアログ閉じる
                            saveDlg.dialog("close");
                        })
                        .catch(error => {
                            var status = error.response.status;
                            var errObj = error.response.data;

                            if (status == 422) {
                                //validation error
                                errObj.isNew = true;
                                vue.saveErrors = errObj;
                                vue.onSaveErrors(grid, errObj);
                                //初回更新終了設定
                                vue.saveErrors.isNew = false;
                            } else {
                                //exception error
                                errObj.isNew = true;
                                vue.saveExceptions = errObj;
                                vue.onSaveExceptions(grid, errObj);
                                //初回更新終了設定
                                vue.onSaveExceptions.isNew = false;
                            }

                            //コールバックの実行
                            op.error.callback(vue, grid, errObj);

                            //メッセージ追加
                            vue.$root.$emit("addMessage",
                                op.error.title + "(" + vue.$parent.ScreenTitle + ":" + errObj.message + ")");

                            //保存中ダイアログ閉じる
                            saveDlg.dialog("close");
                        });
                };


                if (op.confirm.isShow) {
                    //確認ダイアログ
                    $.dialogConfirm({
                        title: op.confirm.title,
                        contents: op.confirm.message,
                        buttons:[
                            {
                                text: "はい",
                                class: "btn btn-primary",
                                click: function(){
                                    $(this).dialog("close");

                                    //保存処理
                                    saveFunc();
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
                } else {
                    //保存処理
                    saveFunc();
                }
            };

            //選択範囲データ取得メソッド追加
            this.grid.getSelectionData = function() {
                var grid = this;
                var temp = {};
                grid.Selection().getSelection().map(function(c) {
                    if (c.rowData) {
                        temp[c.rowIndx] = temp[c.rowIndx] || {};
                        temp[c.rowIndx][c.dataIndx] = c.rowData[c.dataIndx];
                    }
                });
                var values = Object.values(temp);
                return values.length == 1 ? values[0] : values;
            };

            //選択行データ取得メソッド追加
            this.grid.getSelectionRowData = function() {
                var grid = this;

                var address = grid.Selection().address();
                if (address.length == 0) return null;
                var rowIndx = grid.Selection().address()[0].r1;
                var rowData = grid.pdata[rowIndx];

                if (rowData.InitialValue) {
                    delete rowData.InitialValue;
                }

                if (rowData.hasOwnProperty("pq_rowselect")) {
                    delete rowData.pq_rowselect;
                }

                return rowData;
            };

            //CSVダウンロードメソッド追加
            this.grid.downloadCsv = function(filename, editor, callback) {
                var blob = this.exportData({
                    format: "csv",
                    nopqdata: true,
                    render: true
                });

                if (editor) {
                    blob = editor(blob);
                }

                $.downloadContents(blob, filename, callback);
            };

            this.grid.options.loading = false;

            //PqGridのリサイズ
            this.resize();
        },
        //PqGrid-Toolbar設定
        setToolbarState: function(grid) {
            grid = grid || this.grid;

            if (!grid.options.showToolbar) return;

            var row;
            var range;
            try {
                row = grid.SelectRow().getSelection();
            } catch(e) {
                row = [];
            }

            var range;
            try {
                range = grid.Selection().getSelection();
            } catch(e) {
                range = [];
            }

            var isRow = row.length > 0;
            var isCell = range.length == 1;
            var isRange = range.length > 1;

            //1行の全セル選択は行選択とみなす
            if (isRange) {
                var cols = this.grid.colModel.filter(c => !c.hidden).map(c => c.leftPos);
                var cMin =  Math.min.apply(null, cols);
                var cMax =  Math.max.apply(null, cols);

                var r1 = Math.min.apply(null, range.map(c => c.rowIndx));
                var r2 = Math.max.apply(null, range.map(c => c.rowIndx));
                var c1 = Math.min.apply(null, range.map(c => c.colIndx));
                var c2 = Math.max.apply(null, range.map(c => c.colIndx));

                isRow = r1 == r2 && c1 == cMin && c2 == cMax;
                isRange = !isRow;
            }

            var noSelect = (!grid.pdata || grid.pdata.length == 0) || (!isRow && !isRange && !isCell);

            var hasEditableCell = range.filter(c => grid.getCell({ rowIndx: c.rowIndx, colIndx: c.colIndx }).hasClass("cell-editable")).length > 0;

            var hasDisabled = range.map(c => c.rowData ? c.rowData.disabled : false).includes(true);
            var hasGrouped = range.map(c => c.rowData ? c.rowData.pq_level != undefined : false).includes(true);

            var canInsert = this.canNonSearchInsert || grid.searchResult;
            var canClear = grid.options.dataModel.data.length > 0;

            $("button[insertRow]", grid.toolbar()).button("option", { disabled: !canInsert });
            $("button[deleteRow]", grid.toolbar()).button("option", { disabled: noSelect || isRange || hasDisabled || hasGrouped });

            $("button[cutRange]", grid.toolbar()).button("option", { disabled: noSelect  || (!hasEditableCell && !isRow) || hasDisabled || hasGrouped});
            $("button[copyRange]", grid.toolbar()).button("option", { disabled: noSelect });
            $("button[pasteRange]", grid.toolbar()).button("option", { disabled: noSelect || (!hasEditableCell && !isRow) || hasDisabled || hasGrouped });
            $("button[clear]", grid.toolbar()).button("option", { disabled: !canClear });

            //コンテキストメニュー設定
            this.setCellContextMenu(grid);
        },
        //コンテキストメニュー設定
        setCellContextMenu: function(grid) {
            if (this.showContextMenu == false) return;

            grid = grid || this.grid;

            //行選択とセル及びセル範囲選択の取得方法が異なるため
            //どちらなのかを判断して、コンテキストメニューに与えるselectorを決定
            var row = grid.SelectRow().getSelection();

            var range;
            try {
                range = grid.Selection().getSelection();
            } catch(e) {
                range = [];
            }

            var rangeIds = range.map(v => grid.getCell({ rowIndx: v.rowIndx, colIndx: v.colIndx }).attr("id"))
                .filter(v => !!v)
                .map(v => "#" + v)
                .join();

            var selector = row.length == 1 ? ("#" + this.id + " .pq-grid-row.pq-state-select") : rangeIds;

            //未選択時は設定しない
            if (!selector) return;

            //コンテキストメニュー生成
            var items = this.createContextMenu();

            //コンテキストメニューイベント設定
            $.contextMenu({
                selector: selector,
                items: items,
                position: function(opt, x, y) {
                    //表示位置の調整
                    var overX = x + opt.$menu.width()  - opt.$menu.parent().width();
                    var overY = y + opt.$menu.height() - opt.$menu.parent().height();

                    var top  = overY > 0 ? (y - overY) : y;
                    var left = overX > 0 ? (x - opt.$menu.width() - 10) : x;

                    opt.$menu.css({ top: top, left: left });
                },
            });
        },
        //コンテキストメニュー生成
        createContextMenu: function() {
            var grid = this.grid;
            var toolbars = grid.options.toolbar.items;

            var items = {};
            toolbars.forEach(function(v, i) {
                if (v.type == "separator") {
                    items["separator" + i] = "";
                } else if (v.type == "button") {
                    items[v.name] = {
                        icon: function(opt, $itemElement, itemKey, item){
                            //PqGridのtoolbarでの定義を利用
                            $itemElement.html(v.label);
                            return 'context-menu-icon-updated';
                        },
                        callback: function(itemKey) {
                            v.listener(grid);
                        },
                        disabled: function() {
                            return $("button[" + v.name + "]", grid.toolbar()).button("option", "disabled");
                        },
                    };
                }
            });

            if (this.contextMenu) {
                if (this.contextMenu.mode == "append") {
                    if (Object.keys(items).length > 0) {
                        items["separator" + items.length] = "";
                    }
                    $.extend(true, items, this.contextMenu.items);
                } else {
                    items = this.contextMenu.items;
                }
            }

            return items;
        },
        selectRow: function(event) {
            var r = ($(event.target).text() - 1);

            this.grid.setSelection(null);

            //表示しているcolIndxのmax/min
            var cols = this.grid.colModel.filter(c => !c.hidden).map(c => c.leftPos);
            var c1 =  Math.min.apply(null, cols);
            var c2 =  Math.max.apply(null, cols);

            this.grid.Range({ r1: r, rc: 1, c1: c1, c2: c2 }).select();

            //PqGrid-Toolbar設定
            this.setToolbarState();
        },
        resize: function () {
            //console.log(this.$options.name + " resize");

            if (this.grid && !!this.grid.options && !this.grid.options.loading) {
                //リサイズ関数が指定されている場合、優先呼び出し
                this.resizeFunc ? this.resizeFunc(this.grid) : this.resizeBase();
            }
        },
        resizeBase: function () {
            //PqGridリサイズ基本設定(ヘッダーとフッターの間に収まるように)
            if (!this.grid || !this.grid.options) return;

            //widget
            var widget = this.grid.widget();

            //heightを適正に変更
            var oldHeight = widget.outerHeight();

            //body-content
            var content = widget.closest(".body-content");

            //空き領域を計算
            var contentHeight = content.height();
            var elementsHeightSum = _.sum(content.find("form > *").map((i, el) => $(el).outerHeight(true)));

            //TODO: 厳密には可変サイズのGridが複数存在する場合を考慮に入れなければならないか？ -> そのような画面設計を避けるか...
            //新サイズ計算
            var newHeight = oldHeight + contentHeight - elementsHeightSum;

            if (!this.isFixedHeight && _.round(newHeight) != _.round(oldHeight)) {
                this.grid.options.height += (_.round(newHeight) - _.round(oldHeight));
                this.grid.refresh();
            }
        },
        onSearchErrors: function(grid, errObj) {
            //activate時の検索の場合、メッセージ表示を抑制
            if (this.isActivated && !grid.options.loading) {
                this.isActivated = false;
                return;
            }

            //パラメータ指定優先でエラー処理関数実行
            this.onSearchErrorsFunc ? this.onSearchErrorsFunc(grid, errObj) : this.onSearchErrorsBase(grid, errObj);
        },
        onSearchErrorsBase: function(grid, errObj) {
            //検索時エラー処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(onSearchErrorsFunc)を行う
            var vue = this;

            //画面項目のエラー表示設定
            $(".form-control:enabled", $(vue.$parent.$el))
                .filter((i, v) => {
                        var errorKeys = Object.keys(errObj.errors).filter(k => errObj.errors[k]);
                        return errorKeys.includes(v.id.replace("_selector", ""))
                            || ($(v).parent(".PopupSelect").length > 0 && errorKeys.includes(v.id.replace(/_*.+$/, "")))
                            || _.intersection(errorKeys, v.classList).length > 0;
                    }
                )
                .each(function(i, v) {
                    $(v).tooltip({
                        animation: false,
                        placement: "auto",
                        trigger: "hover",
                        title: errObj.errors[v.id.replace("_selector", "")],
                        container: "body",
                        template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    });
                })
                .closest("div:not(.input-group)").addClass("has-error");

            if (errObj.isNew) {
                //メッセージリストに追加
                Object.values(errObj.errors).filter(v => v)
                    .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));

                //メッセージダイアログ
                $.dialogErr({ errObj: errObj });
            }
        },
        onSearchExceptions: function(grid, exObj) {
            //パラメータ指定優先で例外処理関数実行
            this.onSearchExceptionsFunc ? this.onSearchExceptionsFunc(grid, exObj) : this.onSearchExceptionsBase(grid, exObj);
        },
        onSearchExceptionsBase: function(grid, exObj) {
            //検索時例外処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(onSearchExceptionsFunc)を行う
            var vue = this;

            //メッセージリストに追加
            if (exObj.isNew) {
                console.log("PqGridWrapper onSearchExceptions");
                console.log(exObj);

                vue.$root.$emit("addMessage", "例外発生:" + (exObj.message || exObj.statusText));

                //例外画面へ遷移
                //PqGridが内部タイマーを用いて300ms後の遅延レンダリングを行っており、即遷移するとエラーとなる為
                //遷移処理では若干のマージンを取り、400ms待機実行させる(本来はPqGridがtriggerもしくはイベントを準備すべき)
                setTimeout(function() {
                    vue.$router.push({ path: "/SIP/Exceptions", query: exObj });
                }, 400);
            }
        },
        onSaveErrors: function(grid, errObj) {
            //パラメータ指定優先でエラー処理関数実行
            this.onSaveErrorsFunc ? this.onSaveErrorsFunc(grid, errObj) : this.onSaveErrorsBase(grid, errObj);
        },
        onSaveErrorsBase: function(grid, errObj) {
            //保存時エラー処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(onSaveErrorsFunc)を行う
            var vue = this;

            //エラー種別判定
            if (errObj.name == "UniqueConstraintException") {   //一意制約違反

                var keyCols = grid.colModel.filter(c => c.key).map(c => c.dataIndx);
                var keyTitles = grid.colModel.filter(c => c.key).map(c => c.title);
                var setCols = grid.colModel.filter(c => c.key && !c.hidden && c.editable).map(c => c.dataIndx);

                //キーの一致する行を検索し、該当セルにエラー設定
                var errorVals = _.pick(errObj.errors, keyCols);
                if (vue.onErrorValsMapFunc) {
                    errorVals = vue.onErrorValsMapFunc(errorVals);
                }
                grid.getData()
                    .filter(row => keyCols.every(k => _.isEqualWith(row[k], errorVals[k], (v, o) => _.trim(v) == _.trim(o))))
                    .forEach(function(row) {
                        var rowIndx = grid.getRowIndx({ rowData: row }).rowIndx;
                        setCols.forEach(function(col) {
                            var cell = grid.getCell({ rowIndx: rowIndx, dataIndx: col });

                            cell.addClass("ui-state-error")     //class設定
                                .tooltip("dispose")     //tooltip削除
                                .tooltip({      //tooltip設定
                                    animation: false,
                                    placement: "auto",
                                    trigger: "hover",
                                    template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                                    title: cell.text() && cell.text().trim() ? (cell.text() + ":" + errObj.message) : errObj.message,
                                });
                        });
                    });

                if (errObj.isNew) {
                    //メッセージダイアログ
                    $.dialogErr({
                        title: errObj.message,
                        contents: keyTitles.join(", ") + "が同じデータは保存できません。",
                    });
                }

            } else if (errObj.name == "ExclusiveControlException") {   //排他制御違反
                if (errObj.isNew) {
                    //メッセージダイアログ
                    $.dialogErr({
                        title: errObj.message,
                        contents: errObj.errors,
                    });
                }
            } else {    //通常のエラー表示

                //画面項目のエラー表示設定
                var inputErrors = _.pickBy(errObj.errors, (v, k) => !["AddList", "UpdateList", "DeleteList"].some(c => k.includes(c)));
                $(".form-control:enabled", $(vue.$parent.$el))
                    .filter((i, v) => Object.keys(inputErrors).filter(k => errObj.errors[k]).some(k => k.includes(v.id.replace("_selector", ""))))
                    .each(function(i, v) {
                        var _err = _.pickBy(errObj.errors, (val, key) => key.endsWith(v.id));
                        $(v).tooltip("dispose")
                            .tooltip({
                                animation: false,
                                placement: "auto",
                                trigger: "hover",
                                title: Object.values(_err)[0],
                                container: "body",
                                template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                            });
                    })
                    .closest("div:not(.input-group)").addClass("has-error");

                //PqGrid項目のエラー表示設定
                var gridErrors = _.pickBy(errObj.errors, (v, k) => ["AddList", "UpdateList", "DeleteList"].some(c => k.includes(c)));
                Object.keys(gridErrors).filter(k => errObj.errors[k])
                    .forEach(function(k) {
                        var listName = k.replace(/\.[^\.]+$/, "");
                        var dataIndx = k.replace(listName + ".", "");
                        var colName = grid.columns[dataIndx].title;

                        var msg = errObj.errors[k]
                                    .map(e => e.replace(listName + "." + dataIndx, colName))
                                    .join("<br>");

                        var rowData = _.get(grid.options.dataModel.postSaveData, listName);
                        var rowIndx = grid.getRowIndx({ rowData: rowData }).rowIndx;

                        var cell = grid.getCell({ rowIndx: rowIndx, dataIndx: dataIndx });

                        if (cell.hasClass("cell-readonly")) {
                            return;
                        }

                        if (grid.columns[dataIndx].hidden) {
                            errObj.errors[k] = undefined;
                            return;
                        }
                        errObj.errors[k] = [msg];

                        //class設定
                        cell.addClass("ui-state-error");

                        //tooltip設定
                        cell.tooltip("dispose")
                            .tooltip({
                                animation: false,
                                placement: "auto",
                                trigger: "hover",
                                container: "body",
                                template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                                title: cell.text() && cell.text().trim() ? (cell.text() + ":" + msg) : msg,
                            });
                    });

                if (errObj.isNew) {
                    //メッセージダイアログ
                    $.dialogErr({ errObj: errObj });

                    //メッセージリストに追加
                    Object.values(errObj.errors).flat().filter(v => v)
                        .forEach(v => vue.$root.$emit("addMessage", v.replace(/(^\"|\"$)/g, "")));
                }
            }
        },
        onSaveExceptions: function(grid, exObj) {
            //パラメータ指定優先で例外処理関数実行
            this.onSaveExceptionsFunc ? this.onSaveExceptionsFunc(grid, exObj) : this.onSaveExceptionsBase(grid, exObj);
        },
        onSaveExceptionsBase: function(grid, exObj) {
            //保存時例外処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(onSaveExceptionsFunc)を行う
            var vue = this;

            console.log("PqGridWrapper onSaveExceptions");
            console.log(exObj);

            //メッセージリストに追加
            vue.$root.$emit("addMessage", "例外発生:" + (exObj.message || exObj.statusText));

            //例外画面へ遷移
            //PqGridが内部タイマーを用いて300ms後の遅延レンダリングを行っており、即遷移するとエラーとなる為
            //遷移処理では若干のマージンを取り、400ms待機実行させる(本来はPqGridがtriggerもしくはイベントを準備すべき)
            setTimeout(function() {
                vue.$router.push({ path: "/SIP/Exceptions", query: exObj });
            }, 400);
        },
        setCellState: function(grid, ui) {
            var $cell = grid.getEditCell().$cell;
            if ($cell.find(".has-error").length > 0) {
                var title = $cell.find(".target-input").data("bs.tooltip").config.title;
                ui.rowData.pq_inputErrors[ui.dataIndx] = title;
            } else {
                if (!!ui.rowData.pq_inputErrors && !!ui.rowData.pq_inputErrors[ui.dataIndx]) {
                    delete ui.rowData.pq_inputErrors[ui.dataIndx];
                }
            }
            //rowData更新
            grid.updateRow({ rowIndx: ui.rowIndx, newRow: ui.column.binder });
        },
        moveNextCell: function(grid, ui, reverse) {
            //次のセルへ移動
            (grid.getEditCell().$editor || grid.getCell({ rowIndx: ui.rowIndx, colIndx: ui.colIndx })).trigger($.Event("keydown", {keyCode: 9, which: 9, shiftKey: reverse}));
        },
        getHeaderContextMenu: function(event, ui) {
            var vue = this;
            var grid = vue.grid;
            var options = grid.options;

            var menuItems = [
                {
                    name: "フィルタ" + (options.filterModel.header ? "非表示" : "表示"),
                    icon: "fas fa-filter",
                    action: function(){
                        options.filterModel.header = !options.filterModel.header;
                        grid.refreshHeader();
                    }
                },
                "separator",
                {
                    name: "並べ替え",
                    icon: "fas fa-sort fa-lg",
                    disabled: !options.sortModel.on,
                    subItems:[
                        {
                            name: "なし",
                            icon: "fas fa-times fa-lg",
                            disabled: options.sortModel.sorter.filter(s => s.dataIndx == ui.column.dataIndx).length == 0,
                            action: function(evt, ui, item){
                                var sorter = options.sortModel.sorter.filter(s => s.dataIndx != ui.column.dataIndx);
                                grid.sort({sorter: sorter});
                            }
                        },
                        {
                            name: "昇順",
                            icon: "fas fa-sort-" + (["integer", "float"].includes(ui.column.dataType) ? "numeric" : "alpha") + "-down fa-lg",
                            disabled: options.sortModel.sorter.filter(s => s.dataIndx == ui.column.dataIndx && s.dir == "up").length == 1,
                            action: function(evt, ui, item){
                                var sorter;
                                if (options.sortModel.sorter.filter(s => s.dataIndx == ui.column.dataIndx).length  == 1) {
                                    sorter = options.sortModel.sorter
                                        .map(s => {
                                            if (s.dataIndx == ui.column.dataIndx) s.dir = "up";
                                            return s;
                                        });
                                } else {
                                    options.sortModel.sorter.push({ dataIndx: ui.column.dataIndx, dir: "up" });
                                    sorter = options.sortModel.sorter;
                                }
                                grid.sort({sorter: sorter});
                            }
                        },
                        {
                            name: "降順",
                            icon: "fas fa-sort-" + (["integer", "float"].includes(ui.column.dataType) ? "numeric" : "alpha") + "-down-alt fa-lg",
                            disabled: options.sortModel.sorter.filter(s => s.dataIndx == ui.column.dataIndx && s.dir == "down").length == 1,
                            action: function(evt, ui, item){
                                var sorter;
                                if (options.sortModel.sorter.filter(s => s.dataIndx == ui.column.dataIndx).length  == 1) {
                                    sorter = options.sortModel.sorter
                                        .map(s => {
                                            if (s.dataIndx == ui.column.dataIndx) s.dir = "down";
                                            return s;
                                        });
                                } else {
                                    options.sortModel.sorter.push({ dataIndx: ui.column.dataIndx, dir: "down" });
                                    sorter = options.sortModel.sorter;
                                }
                                grid.sort({sorter: sorter});
                            }
                        },
                    ]
                }
            ]

            return (this.HeaderContextMenuEditCallback || ((v) => v))(menuItems);
        },
        getBodyContextMenu: function(event, ui) {
            var vue = this;
            var grid = vue.grid;
            var options = grid.options;

            var freezeColsSubItems = [
                (options.freezeCols ? {
                    name: "なし",
                    action: function(){
                        options.freezeCols = 0;
                        grid.refresh();
                    }
                } : null),
                (options.freezeCols ? "separator" : null),
            ];

            options.colModel
                .filter(c => !c.hidden)
                .sort((a, b) => a.leftPos > b.leftPos ? 1 : -1)
                .forEach((c, i) => {
                    freezeColsSubItems.push({
                        name: i + 1,
                        disabled: options.freezeCols == i + 1,
                        action: function(){
                            options.freezeCols = i + 1;
                            grid.refresh();
                        }
                    });
                });

            var menuItems = [
                {
                    name: "列固定",
                    icon: "fas fa-columns fa-lg",
                    subItems: freezeColsSubItems,
                },
                {
                    name: "印刷",
                    icon: "fas fa-print fa-lg",
                    subItems: [
                        {
                            name: "CSVファイル",
                            icon: "fas fa-file-csv fa-lg",
                            action: function(){
                                vue.exportData("csv", true);
                            }
                        },
                        {
                            name: "Excelファイル",
                            icon: "fas fa-file-excel fa-lg",
                            action: function(){
                                vue.exportData("xlsx", true);
                            }
                        },
                    ]
                },
                {
                    name: "ダウンロード",
                    icon: "fas fa-download fa-lg",
                    subItems: [
                        {
                            name: "CSVファイル",
                            icon: "fas fa-file-csv fa-lg",
                            action: function(){
                                vue.exportData("csv");
                            }
                        },
                        {
                            name: "Excelファイル",
                            icon: "fas fa-file-excel fa-lg",
                            action: function(){
                                vue.exportData("xlsx");
                            }
                        },
                    ]
                },
            ];

            if (options.editable) {
                menuItems = _.concat(
                    menuItems,
                    [
                        "separator",
                        {
                            name: "元に戻す",
                            icon: "fas fa-undo fa-lg",
                            shortcut: "Ctrl - Z",
                            disabled: !grid.History().canUndo(),
                            action: function(evt, ui){
                                grid.History().undo();
                            }
                        },
                        {
                            name: "やり直し",
                            icon: "fas fa-redo fa-lg",
                            shortcut: "Ctrl - Y",
                            disabled: !grid.History().canRedo(),
                            action: function(evt, ui){
                                grid.History().redo();
                            }
                        },
                        "separator",
                        {
                            name: "コピー",
                            icon: "fas fa-copy fa-lg",
                            shortcut: "Ctrl - C",
                            action: function(){
                                this.copy();
                            }
                        },
                        {
                            name: "貼り付け",
                            icon: "fas fa-paste fa-lg",
                            shortcut: "Ctrl - P",
                            action: function(){
                                this.paste();
                            }
                        },
                        "separator",
                        {
                            name: "行追加",
                            icon: "fas fa-plus-square fa-lg",
                            shortcut: "Ctrl - +",
                            action: function(){
                                this.addRow({
                                    rowIndx: ui.rowIndx + 1,
                                    newRow: !!vue.onAddRowFunc ? vue.onAddRowFunc(grid, ui.rowData) : {},
                                    checkEditable: false
                                });
                            }
                        },
                        {
                            name: "行削除",
                            icon: "fas fa-minus-square fa-lg",
                            shortcut: "Ctrl - -",
                            action: function(){
                                this.deleteRow({ rowIndx: ui.rowIndx });
                            }
                        },
                    ]
                );
            }

            return (this.BodyContextMenuEditCallback || ((v) => v))(menuItems);
        },
        exportData: function (format, isPrint) {
            var vue = this;
            var grid = vue.grid;

            var blob = grid.exportData({ format: format });
            if (typeof blob === "string") {
                blob = new Blob([blob]);
            }

            var fileName = vue.$parent.ScreenTitle
                         + "_" + moment().format("YYYYMMDDHHmmssSSS")
                         + "." + format.toLowerCase();

            if (isPrint) {

            } else {
                saveAs(blob, fileName);
            }
        },
    }
};

</script>

<style>
    /* スクロールバー設定 */
    .pq-body-outer .pq-cont-inner.pq-cont-right {
        overflow-x: auto !important;
        overflow-y: scroll !important;
    }

    /* セル */
    .pq-grid-cell {
        color: black;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        text-overflow: ellipsis !important;
        min-width: 0;
    }
    .pq-grid-cell.text-breakable {
        white-space: normal !important;
        word-wrap: break-word !important;
        align-items: normal;
        text-overflow: ellipsis !important;
        overflow: hidden;
    }
    /* 入力可能セル */
    .pq-grid-cell.cell-editable {
        /* background-color: white; */
    }

    textarea.pq-cell-editor {
        margin-top: 8px;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    /* 入力不可セル */
    .table_editable .pq-grid-cell.cell-readonly {
        background-color: darkgray;
    }
    /* マイナス値セル */
    .pq-grid-cell.cell-negative-value {
        color: red;
    }
    /* エラーセル */
    .pq-grid-cell.ui-state-error {
        color: black !important;
        background-color: lightpink !important;
    }

    /* ヘッダーセル */
    [id^=pq-head-cell] div {
        text-align: center !important;
        vertical-align: middle;
    }

    /* ツールチップ */
    .pq-grid-cell + .tooltip {
        transform: translateY(10px);
        max-width: 30vw !important;
    }
    .pq-grid-cell + .tooltip .tooltip-inner {
        width: auto;
    }
    .tooltip.text-overflow {
        max-width: 30vw !important;
    }
    /* エラーセル ツールチップ */
    .pq-grid-cell.ui-state-error + .tooltip .tooltip-inner {
        background-color: red;
    }
    .pq-grid-cell.ui-state-error + .tooltip.left .arrow {
        border-left-color: red;
    }
    .pq-grid-cell.ui-state-error + .tooltip.right .arrow {
        border-right-color: red;
    }

    /* 選択セル/行 */
    .pq-state-select.ui-state-highlight > div.pq-grid-cell
    {
        color: black;
        background-color: rgba(0, 0, 0, 0.1);
    }
    svg.pq-grid-overlay
    {
        background-color: transparent;
    }
    svg.pq-grid-overlay:not(.pq-head-overlay):not(.pq-number-overlay)
    {
        border: none;
        background-color: rgba(0, 0, 0, 0.1);
    }

    /* 小計行 */
    .pq-grid-row.pq-summary-row {
        font-weight: bold;
        color: black;
        background-color: lightyellow !important;
    }

    /* 合計行 */
    .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner) {
        font-weight: bold;
        color: black;
        background-color: lightpink !important;
    }

    /* ツールバーボタン */
    .pq-toolbar > button {
        width: 100px;
        padding: 5px 5px;
    }

    /* ツールバーボタン内容 */
    .pq-toolbar  i.fa,
    .context-menu-item.context-menu-icon-updated > i
    {
        font-family: FontAwesome;
        margin-right: 5px;
    }

    /* 行border */
    .pq-td-border-right > .pq-grid-row > div.pq-grid-cell {
        border-bottom-color: #cfcfff;
    }

    /* ソート時列タイトル */
    .pq-grid-col.pq-col-sort-asc,
    .pq-grid-col.pq-col-sort-desc {
	    color: black;
    }

    /* ソート時アイコン */
    .ui-state-active .ui-icon, .ui-button:active .ui-icon {
        background-image: url(/Content/themes/base/images/ui-icons_222222_256x240.png);
    }

    /* 縦横揃え(PqGridが旧式の為、補正) */
    .pq-align-left{
        justify-content: flex-start;
    }
    .pq-align-center{
        justify-content: center;
    }
    .pq-align-right{
        justify-content: flex-end;
    }

    /* 保存中ダイアログ */
    .progress-dialog .ui-dialog-content {
        display: flex;
        justify-content: center;
        align-items: center;
        border: solid 1px gray;
        margin: 10px;
        font-size: 24px;
    }

    /* NumberCells(左端) */
    .pq-grid-number-cell {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* toolbar件数制約違反メッセージ */
    .pq-toolbar > .CountConstraintViolation {
        display: none;
        color: red;
        font-weight: bold;
        width: 99%;
        padding-left: 5px;
        padding-right: 5px;
    }
    .pq-toolbar > .CountConstraintViolation:not(:empty) {
        display: block;
    }

    /*ロード中...*/
    .pq-loading > .pq-loading-mask {
        width: 180px;
        height: 60px;
    }
    div.pq-loading-mask > div{
        width: auto;
        height: 60px;
        display: flex;
        align-items: center;
        font-size: x-large;
        background-size: 15%;
        padding-left: 45px;
    }
</style>

<style scoped>
</style>
