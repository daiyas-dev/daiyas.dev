<template>
    <div :class="['w-100', isFloat ? 'Floating' : '']">
        <div :class="[!hasFreezeRightCols ? 'w-100' : '']" :id="this.id"></div>
        <div v-if=hasFreezeRightCols :id='this.id + "_right"' class="right-grid"></div>
    </div>
</template>

<script>
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
            selectionData: null,
            selectionRow: null,
            selectionRowList: [],
            selectionRowCount: null,
            isSelection: false,
            isSelectionFirst: false,
            isSelectionLast: false,
            CountConstraint: null,
            PopupSelect: PopupSelect,
            DatePickerWrapper: DatePickerWrapper,
            _onBeforeCreateFunc: Function,
            _onRefreshFunc: Function,
            _onCompleteFunc: Function,
            _onChangeFunc: Function,
            _onCellSaveFunc: Function,
            _onSelectChangeFunc: Function,
            _onSearchErrorsFunc: Function,
            _onSearchExceptionsFunc: Function,
            _onSaveErrorsFunc: Function,
            _onSaveExceptionsFunc: Function,
            _onBeforeAddRowFunc: Function,
            _onAddRowFunc: Function,
            _onDeleteRowFunc: Function,
            _onChangeExceptsColumns: Array,
            _onBeforeSearchFunc: Function,
            _onAfterSearchFunc: Function,
            _onErrorValsMapFunc: Function,
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
        checkChanged: Boolean,
        checkChangedFunc: Function,
        checkChangedCancelFunc: Function,
        autoEmptyRow: Boolean,
        autoEmptyRowCount: Number,
        autoEmptyRowCheckFunc: Function,
        autoEmptyRowFormula: String,
        autoEmptyRowFunc: Function,
        onAfterAddAutoEmptyRowFunc: Function,
        onBeforeCreateFunc: Function,
        onRefreshFunc: Function,
        onCompleteFunc: Function,
        onChangeFunc: Function,
        onCellSaveFunc: Function,
        onSelectChangeFunc: Function,
        onSearchErrorsFunc: Function,
        onSearchExceptionsFunc: Function,
        onSaveErrorsFunc: Function,
        onSaveExceptionsFunc: Function,
        onBeforeAddRowFunc: Function,
        onAddRowFunc: Function,
        onDeleteRowFunc: Function,
        onChangeExceptsColumns: Array,
        onBeforeSearchFunc: Function,
        onAfterSearchFunc: Function,
        onErrorValsMapFunc: Function,
        canNonSearchInsert: Boolean,
        freezeRightCols: Number,
        onBeforeCellKeyDownFunc: Function,
        onCellKeyDownFunc: Function,
        setMoveNextCell: Function,
        keepSelect: Boolean,
        keepSelectOnce: Boolean,
        isFloat: Boolean,
        isMultiRowSelectable: Boolean,
        maxRowSelectCount: Number,
        autoToolTipDisabled:Boolean,
        setCustomTitle: Function,
        onEnterMove: Boolean,
    },
    computed: {
        isDialog: function() {
            var vue = this;
            return $(vue.$parent.$el).closest(".ui-dialog").length == 1;
        },
        hasFreezeRightCols: function() {
            var vue = this;
            return vue.freezeRightCols > 0;
        },
    },
    created: function () {  //createdは一回きり
        var vue = this;

        //set callback funcs
        vue._onBeforeCreateFunc = vue.onBeforeCreateFunc;
        vue._onRefreshFunc = vue.onRefreshFunc;
        vue._onCompleteFunc = vue.onCompleteFunc;
        vue._onChangeFunc = vue.onChangeFunc;
        vue._onCellSaveFunc = vue.onCellSaveFunc;
        vue._onSelectChangeFunc = vue.onSelectChangeFunc;
        vue._onSearchErrorsFunc = vue.onSearchErrorsFunc;
        vue._onSearchExceptionsFunc = vue.onSearchExceptionsFunc;
        vue._onSaveErrorsFunc = vue.onSaveErrorsFunc;
        vue._onSaveExceptionsFunc = vue.onSaveExceptionsFunc;
        vue._onBeforeAddRowFunc = vue.onBeforeAddRowFunc;
        vue._onAddRowFunc = vue.onAddRowFunc;
        vue._onDeleteRowFunc = vue.onDeleteRowFunc;
        vue._onChangeExceptsColumns = vue.onChangeExceptsColumns;
        vue._onBeforeSearchFunc = vue.onBeforeSearchFunc;
        vue._onAfterSearchFunc = vue.onAfterSearchFunc;
        vue._onErrorValsMapFunc = vue.onErrorValsMapFunc;

        vue.isSearchOnActivate = vue.SearchOnActivate == false ? false : true;

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

        //PqGrid集計関数に合計(整数)追加
        pq.aggregate.TotalInt = function(arr, col) {
            return pq.formatNumber(pq.aggregate.sum(arr, col), "#,###");
        };

        //PqGrid集計関数に合計(小数)追加
        pq.aggregate.TotalFloat = function(arr, col) {
            return pq.formatNumber(pq.aggregate.sum(arr, col), "#,##0.0");
        };

        vue.$parent.$on("panelResize", vue.resize);
    },
    mounted: function () {
        var vue = this;

        //PqGrid基本設定
        var pqGridObj = {
            locale: "ja",
            loading: true,
            editable: false,
            fillHandle: "",
            scrollModel: { autoFit: true, theme: true },
            showTitle: true,
            resizable: false,
            showHeader: true,
            showToolbar: false,
            wrap: false,
            hrap: false,
            rowHt: 33,
            rowHtSum: 33,
            printOptions: {},
            animModel: {
                on: true
            },
            selectionModel: {
                type: "cell",
                mode: "block",
                fireSelectChange: true,
            },
            swipeModel: { on: true },
            collapsible: {
                on: false,
                collapsed: false,
                toggle: false
            },
            editModel: {
                keyUpDown: true,
                clicksToEdit: 2,
                onSave: "nextEdit",
                onTab: "nextEdit",
            },
            editorBegin: function(event, ui) {
                console.log("Editor Begin", ui, ui.$editor);
                if (ui.column.dataType == "integer") {
                    ui.$editor.prop("inputmode ", "numeric");
                } else if (ui.column.dataType == "float") {
                    ui.$editor.prop("inputmode ", "decimal");
                } else if (ui.column.dataType == "string") {
                    ui.$editor.prop("inputmode ", "text");
                }
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
                    if (ui.column.zeroToEmpty != undefined) {
                        if (value == 0) {
                            if (_.isArray(ui.column.zeroToEmpty)) {
                                if (!!ui.column.zeroToEmpty[!!ui.Export ? 1 : 0]) {
                                    return { text: "" };
                                }
                            } else if (!!ui.column.zeroToEmpty) {
                                return { text: "" };
                            }
                        }
                    }
                    if (ui.column.emptyToZero != undefined) {
                        if (value == null || value == undefined) {
                            if (_.isArray(ui.column.emptyToZero)) {
                                if (!!ui.column.emptyToZero[!!ui.Export ? 1 : 0]) {
                                    return { text: "0" };
                                }
                            } else if (!!ui.column.emptyToZero) {
                                return { text: "0" };
                            }
                        }
                    }

                    //nested objectからデータ取得出来るよう拡張
                    if (ui.dataIndx.includes(".") || !ui.rowData[ui.dataIndx]) {
                        try {
                            var val = _.result(ui.rowData, ui.dataIndx);

                            if (val) {
                                ui.cellData = val;
                                ui.formatVal = ["integer", "float"].includes(ui.column.dataType) && ui.column.format
                                    ? pq.formatNumber(val, ui.column.format) : (val + "");
                                //ui.rowData[ui.dataIndx] = val;

                                if (ui.column.editor) {
                                    ui.column.editor.getData = ui => _.result(ui.rowData, ui.dataIndx);
                                }

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
                            ui.column.postRender = function (ui) {
                                var grid = this;

                                $(ui.cell).addClass("select-cell");

                                return ui;
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
                                            config: {
                                                format: !!ui.column.format ? (ui.column.format.includes("HH") ? "HH:mm:ss" : "YYYY/MM/DD") : "YYYY/MM/DD",
                                                dayViewHeaderFormat: "YYYY年MM月",
                                            },
                                            onCalendarHiddenFunc: (event) => {
                                                grid.getEditCell().$editor.trigger($.Event("keydown", {keyCode: 13, which: 13}))
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
                    if (ui.column.psProps) {
                        ui.column.binder = {};
                        ui.column.editor = {
                            type: "textbox",
                            init: function(ui, args) {
                                // console.log("popupselect on grid initialize");
                                var grid = this;
                                var vue = grid.options.vue;

                                var editCell = ui.$cell;
                                var gridCell = grid.getCell(ui);

                                // ui.column.binder = {
                                //     [ui.dataIndx]: ui.rowData[ui.dataIndx],
                                // };

                                // key events?
                                // if (!!window.event && !!window.event.keyCode && !_.isNaN(window.event.key * 1)) {
                                //     if (ui.column.dataType == "integer" || ui.column.dataType == "float") {
                                //         ui.column.binder[ui.dataIndx] = window.event.key;
                                //     }
                                // }

                                //create PopupSelect instance
                                var props = _.cloneDeep(ui.column.psProps);
                                props.id = "PopupSelect_" + ui.dataIndx + "_" + ui.rowIndx;
                                props.ref = "PopupSelect_" + ui.dataIndx + "_" + ui.rowIndx;
                                props.vmodel = ui.rowData;
                                props.bind = props.bind || ui.dataIndx;
                                props.editable = (_.isFunction(ui.column.editable) ? ui.column.editable(ui) : ui.column.editable) || true;
                                props.width = gridCell.width();

                                if (_.isFunction(props.params)) {
                                    props.params = props.params(vue.$parent, grid);
                                }

                                // if (props.buddy) {
                                //     ui.column.binder[props.buddy] = ui.rowData[props.buddy];
                                // }

                                // if (props.buddies.length) {
                                //     _.keys(props.buddies).forEach(k => {
                                //         ui.column.binder[k] = ui.rowData[props.buddies[k]];
                                //     });
                                // }

                                var ps = ui.column.ps;
                                // console.log("ps in grid", ps, !!ps ? ps.ui.rowIndx : null);
                                if (!ps || ps.ui.rowIndx != ui.rowIndx) {
                                    ps = new (VueApp.createInstance(vue.PopupSelect))(
                                        {
                                            propsData: props,
                                        }
                                    );
                                    ps.$mount();
                                    ps.onGrid = true;
                                    ps.grid = grid;
                                    ps.gridCell = gridCell;
                                    ps.ui = ui;
                                    ps.rowData = ui.rowData;
                                    ui.column.ps = ps;
                                }

                                //editor element
                                var element = $(ps.$el);
                                ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};
                                element.on("keyup", (event) => {
                                    // console.log("ps keyup:" + event.which, element.find("input").val());
                                    switch (event.which) {
                                        case 9:
                                            // if (
                                            //     !ps.hasButtonFocus ||
                                            //     (
                                            //         ps.hasButtonFocus &&
                                            //         (
                                            //             $(event.target).hasClass("clear-button") && !event.shiftKey
                                            //             ||
                                            //             $(event.target).hasClass("target-input") && event.shiftKey
                                            //         )
                                            //     )
                                            // ) {
                                            //     vue.setCellState(grid, ui, true);
                                            //     vue.moveNextCell(grid, ui, event.shiftKey);
                                            //     return false;
                                            // }
                                            // return true;
                                        case 13:
                                            // console.log("ps keyup", event.which);
                                            vue.setCellState(grid, ui, true);
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

                                //入力inputにfocus
                                var input = element.find(".target-input");
                                new Promise((resolve, reject) => {
                                    var timer = setInterval(function() {
                                        if (input.is(":enabled")) {
                                            //interval解除
                                            clearInterval(timer);

                                            return resolve();
                                        }
                                    }, 100);
                                })
                                .then(() => {
                                    input.focus();
                                });
                            },
                            getData: function(ui, grid) {
                                if (!!ui.column.psProps && !!ui.column.psProps.getData) {
                                    return ui.column.psProps.getData(ui, grid);
                                }

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
                                        title: "入力内容が正しくありません",
                                        container: "body",
                                        template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                                    });
                            } else {
                                gridCell
                                    .removeClass("ui-state-error")
                                    .tooltip("dispose");
                            }

                            if (ui.column.psProps.htmlRender) {
                                $(ui.cell).html(ui.column.psProps.htmlRender(ui));
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

                    //セル内Autocomplete設定の共通化
                    if (!!ui.column.autocomplete) {
                        ui.column.editor = {
                            type: "textbox",
                            init:  function (ui){
                                var grid = this;
                                var $input = ui.$cell.find("input");
                                $input.attr("autocomplete", "off");

                                // if (ui.column.dataType == "integer") {
                                //     $input.attr("type", "number");
                                // } else {
                                //     $input.attr("type", "text");
                                // }
                                $input.attr("inputmode", "numeric");

                                var config = ui.column.autocomplete;

                                if (!!config.trigger && config.trigger(ui) == false) return;

                                var source = config.source;
                                source = _.isFunction(source) ? source(ui, grid) : source;
                                config.sourceList = source;

                               var rowData = ui.rowData;

                                $input.autocomplete({
                                    source: (request, response) => {
                                        var makeList = () => {
                                            var key = request.term;
                                            console.log("pq autocomplete", key);

                                            if (key == config.autoCompleteKey && !config.autoCompleteList) {
                                                // console.log("getAutoCompleteList: same key " + key);
                                                return config.autoCompleteList;
                                            }

                                            var list = config.AutoCompleteFunc
                                                ? config.AutoCompleteFunc(key, source, vue)
                                                : source
                                                    .filter(v => v.Cd.includes(key))
                                                    .map(v => {
                                                        var ret = v;
                                                        ret.value = v.Cd;
                                                        ret.text = v.CdNm;
                                                        ret.label = ret.value + " : " + ret.text;
                                                        return ret;
                                                    })
                                                    ;

                                            config.autoCompleteKey = key;
                                            config.autoCompleteList = list;

                                            var max = config.AutoCompleteListMax || 500;

                                            if (!config.AutoCompleteNoLimit && (!!config.CountConstraint || list.length > max)) {
                                                var len = config.ActualCounts || list.length;
                                                list = _.slice(list, 0, max - 1);
                                                var msg = "先頭" + max + "件のみ表示しています(" + len + "件中)"
                                                        + "\r\n"
                                                        + "絞り込み条件を追加して下さい";

                                                $input
                                                    .remove("has-error")
                                                    .find("#" + vue.id)
                                                    .tooltip("dispose")
                                                    .tooltip({
                                                        animation: false,
                                                        placement: "auto",
                                                        trigger: "hover",
                                                        title: msg,
                                                        container: "body",
                                                        template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                                                    })
                                                    .tooltip("show")
                                                    ;
                                            } else {
                                                $input.tooltip("dispose");
                                            }

                                            return list;
                                        }

                                        response(makeList());
                                    },
                                    appendTo: $(vue.$el).closest(".ui-dialog, body"),
                                    position: { my: "left top", at: "left bottom", collision: "flip" },
                                    autoFocus: false,
                                    minLength: ui.column.autocomplete.AutoCompleteMinLength || 0,
                                    select : function(event, selectedUi) {
                                        if (!!config.buddy) {
                                            ui.rowData[config.buddy] = selectedUi.item.CdNm;
                                        }
                                        if (!!config.buddies) {
                                            // console.log("set buddies", config.buddies);
                                            _.forIn(config.buddies, (v, k) => ui.rowData[k] = selectedUi.item[v]);
                                        }

                                        console.log("autocomplete selected", _.pick(ui.rowData, _.keys(config.buddies)));
                                        if (!!config.onSelect) config.onSelect(ui.rowData, selectedUi.item, ui);

                                        return true;
                                    },
                                    close: function(event, closedUi) {
                                        console.log("autocomplte close", closedUi);
                                        var key = $input.val();
                                        var list = config.AutoCompleteFunc
                                            ? config.AutoCompleteFunc(key, source, vue)
                                            : source
                                                .filter(v => v.Cd.includes(key))
                                                .map(v => {
                                                    var ret = v;
                                                    ret.value = v.Cd;
                                                    ret.text = v.CdNm;
                                                    ret.label = ret.value + " : " + ret.text;
                                                    return ret;
                                                })
                                                ;

                                        // var match = list.find(v => v == key || v.Cd == key);
                                        var matched = !!config.GetMatchedFunc
                                            ? config.GetMatchedFunc(list, key, ui.rowData)
                                            : list.filter(v => v == key || v.Cd == key);

                                        if (matched.length == 1) {
                                            //エラー項目設定除去
                                            ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};
                                            delete ui.rowData.pq_inputErrors[ui.dataIndx];

                                            if (!!config.buddy) {
                                                ui.rowData[config.buddy] = matched[0].CdNm;
                                            }
                                            if (!!config.buddies) {
                                                // console.log("set buddies", config.buddies);
                                                _.forIn(config.buddies, (v, k) => ui.rowData[k] = matched[0][v]);
                                            }

                                            if (!!config.onSelect) config.onSelect(ui.rowData, matched[0], ui);

                                            if (config.selectSave) {
                                                ui.$editor.trigger($.Event("keydown", {
                                                    keyCode: 13,
                                                    which: 13,
                                                }));
                                            }
                                        } else {
                                            if (key == "" || event.which == 27) return;
                                            if (!!config.noCheck) return;

                                            var msg = matched.length > 1　? "対象で複数に該当します"　: "対象に存在しません";

                                            //エラー項目設定
                                            ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};
                                            ui.rowData.pq_inputErrors[ui.dataIndx] = msg;

                                            if (!!config.buddy) {
                                                ui.rowData[config.buddy] = "";
                                            }
                                            if (!!config.buddies) {
                                                // console.log("set buddies", config.buddies);
                                                _.forIn(config.buddies, (v, k) => ui.rowData[k] = "");
                                            }
                                        }

                                        return true;
                                    },
                                })
                                .focus(function () {
                                    $input.autocomplete("search", "");
                                })
                                .on("keydown", event =>{
                                    if (event.key == "F8") {
                                        $input.autocomplete("search", " ");
                                    }
                                })
                                ;
                            }
                        }
                        ui.column.postRender = function (ui) {
                            var grid = this;
                            var vue = grid.options.vue;
                            var config = ui.column.autocomplete;

                            var gridCell = $(ui.cell);
                            if (!!config.trigger && config.trigger(ui) == false) return ui;

                            gridCell.addClass("autocomplete-cell");

                            ui.rowData.pq_inputErrors = ui.rowData.pq_inputErrors || {};

                            config.sourceList = _.isFunction(config.source) ? config.source(ui, grid) : config.sourceList;

                            if (!!config.sourceList && !!config.sourceList.length) {
                                var key = ui.rowData[ui.dataIndx];

                                if (!_.isEmptyEx(key)) {
                                    var list = config.AutoCompleteFunc
                                        ? config.AutoCompleteFunc(key, config.sourceList, vue)
                                        : config.sourceList
                                            .filter(v => v.Cd.includes(key))
                                            .map(v => {
                                                var ret = v;
                                                ret.value = v.Cd;
                                                ret.text = v.CdNm;
                                                ret.label = ret.value + " : " + ret.text;
                                                return ret;
                                            })
                                            ;

                                    var matched = !!config.GetMatchedFunc
                                        ? config.GetMatchedFunc(list, key, ui.rowData)
                                        : list.filter(v => v == key || v.Cd == key);

                                    if (matched.length != 1) {
                                        var msg = matched.length > 1　? "対象で複数に該当します"　: "対象に存在しません";

                                        //エラー項目設定
                                        ui.rowData.pq_inputErrors[ui.dataIndx] = msg;
                                        if (!!config.buddy) {
                                            ui.rowData[config.buddy] = "";
                                        }
                                        if (!!config.buddies) {
                                            // console.log("set buddies", config.buddies);
                                            _.forIn(config.buddies, (v, k) => ui.rowData[k] = "");
                                        }
                                    } else {
                                        if (!!config.buddy) {
                                            ui.rowData[config.buddy] = matched[0].CdNm;
                                        }
                                        if (!!config.buddies) {
                                            _.forIn(config.buddies, (v, k) => ui.rowData[k] = matched[0][v]);
                                        }
                                    }
                                }
                            }

                            var inputError = ui.rowData.pq_inputErrors[ui.dataIndx];
                            if (inputError) {
                                gridCell
                                    .addClass("ui-state-error")
                                    .tooltip({
                                        animation: false,
                                        placement: "auto",
                                        trigger: "hover",
                                        title: inputError,
                                        container: "body",
                                        template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                                    });
                            } else {
                                gridCell
                                    .removeClass("ui-state-error")
                                    .tooltip("dispose");
                            }

                            if (!!ui.column.tooltip) {
                                var title =  !!ui.column.autocomplete.render
                                    ? ui.column.autocomplete.render(ui)
                                    : ui.rowData[ui.dataIndx]
                                    ;
                                title = _.isObject(title) && !!title.text ? title.text : ui.rowData[ui.dataIndx];
                                var html = !!title && title.startsWith("<");

                                if (!!title) {
                                    $(ui.cell).tooltip({
                                        container: "body",
                                        animation: false,
                                        template: '<div class="tooltip text-overflow" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                                        placement: "auto",
                                        trigger: "hover",
                                        title: title,
                                        html: html,
                                    });

                                    ui.text = title;

                                    return ui;
                                }
                            }

                            return ui;
                        };

                        if (!!ui.column.autocomplete.render) {
                            return ui.column.autocomplete.render(ui);
                        }
                    }

                    return ui;
                },
                editor: {
                    select: true,
                },
                postRender: function(ui) {
                    if (!!ui.column.tooltip) {
                        var title =  !!ui.column.render
                            ? ui.column.render(ui)
                            : ui.rowData[ui.dataIndx]
                            ;
                        title = _.isObject(title) ? ui.rowData[ui.dataIndx] : title;
                        var html = !!title && title.startsWith("<");

                        if (!!title) {
                            $(ui.cell).tooltip({
                                container: "body",
                                animation: false,
                                template: '<div class="tooltip text-overflow" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                                placement: "auto",
                                trigger: "hover",
                                title: title,
                                html: html,
                            });
                        }
                    }
                },
            },
            trackModel: {
                on: true,
            },
            toolbar: {
                items: []
            },
            history: function (event, ui) {
                var grid = this;

                //undo, redoボタンの状態変更
                grid.widget().find(".toolbar_button.undo").prop("disabled", !(ui.canUndo || ui.num_undo > 0));
                grid.widget().find(".toolbar_button.redo").prop("disabled", !(ui.canRedo || ui.num_redo > 0));

                //PqGrid-Toolbar設定
                this.options.vue.setToolbarState();
            },
            refresh: function (event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var id = vue.id;

                if (grid.gridRight) {
                    grid.gridRight.options.dataModel.data = grid.options.dataModel.data;
                    var left = grid.widget().find(".pq-cont-inner.pq-cont-right");
                    if (left.get(0).scrollWidth > left.width()) {
                        grid.gridRight.widget().addClass("scrollable-x");
                    } else {
                        grid.gridRight.widget().removeClass("scrollable-x");
                    }

                    grid.gridRight.refreshView();
                }

                //title
                if (grid.isRight) {
                    grid.options.title = "　";
                    grid._refreshTitle();
                } else {
                    vue.setTitle(grid);
                    grid._refreshTitle();
                }

                vue.resize();

                //パラメータ指定更新関数
                if (vue._onRefreshFunc && vue.grid) {
                    vue._onRefreshFunc(grid);
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

                if (grid.getData().length == 0) {
                    vue.selectionRowCount = 0;
                    vue.selectionRow = null;
                    vue.isSelection = false;
                }

                //PqGrid-Toolbar設定
                vue.setToolbarState(grid);

                //自動空行補完
                vue.addEmptyRow(vue, grid);

                //Bootstrap tooltip設定
                //refreshイベントのタイミングではレンダリングが完了していない(PqGridが見栄えの為か、timerを生成して実行しているため)
                //そのため、この設定もtimerを用いて実施
                // setTimeout(function() {
                //     var selector = "#" + id + " .pq-grid-cell:not(.ui-error-state)";
                //     var isDialog = vue.isDialog;

                //     var checkOverflow = (cell, target) => {
                //         //justify-contentを保持
                //         var justify = $(cell).css("justify-content");

                //         //offset/scrollのwidth/height取得
                //         var offsetWidth = cell.offsetWidth;
                //         var scrollWidth = cell.scrollWidth;
                //         var offsetHeight = cell.offsetHeight;
                //         var scrollHeight = cell.scrollHeight;

                //         if (!$(cell).css("display").includes("flex")) {
                //             //clone生成
                //             var clone = $(cell).clone();

                //             //justify-contentをflex-startに変更
                //             clone.css("justify-content", "flex-start");

                //             //offset/scrollのwidth/height再取得
                //             offsetWidth = clone[0].offsetWidth;
                //             scrollWidth = clone[0].scrollWidth;
                //             offsetHeight = clone[0].offsetHeight;
                //             scrollHeight = clone[0].scrollHeight;
                //         }

                //         var title = $(target).attr("title") || $(target).text().replace(/(, )+$/, "").replace(/, /g, "<br>");

                //         return (offsetWidth < scrollWidth || offsetHeight < scrollHeight)  && !!title ? title : "";
                //     };

                //     $("*").tooltip("hide");
                //     $(selector)
                //         .tooltip({
                //             container: "body",
                //             animation: false,
                //             template: '<div class="tooltip text-overflow" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                //             placement: "auto",
                //             trigger: "hover",
                //             title: function() {
                //                 var cell = this;

                //                 //改行以外のchildrenが存在する場合、その要素を対象
                //                 var children = $(cell).children().filter((i,v) => v.tagName != "BR");

                //                 if (children.length > 0) {
                //                     children.each((i, v) => {
                //                         var title = checkOverflow(cell, v);
                //                         if (!!title) {
                //                             //個別にツールチップ設定
                //                             $(v).tooltip({
                //                                 container: "body",
                //                                 animation: false,
                //                                 placement: "auto",
                //                                 trigger: "hover",
                //                                 html: true,
                //                                 title: title,
                //                             });
                //                         }
                //                     });

                //                     return "";
                //                 } else {
                //                     return checkOverflow(cell, cell);
                //                 }
                //             },
                //         });
                // }, 100);

                //dirty row number cell highlight
                // setTimeout(() => {
                //     grid.getCellsByClass({cls: "pq-cell-dirty"})
                //         .forEach(c => {
                //             $(".pq-grid-number-cell", grid.widget())
                //                 .filter((i, v) => $(v).text() == c.rowIndx + 1)
                //                 .addClass("changed-col-row");
                //             $("[pq-col-indx=" + c.colIndx + "]", grid.widget())
                //                 .addClass("changed-col-row");
                //         });
                // }, 100);
            },
            complete: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                console.log("pqgrid complete")

                //ツールチップ除去
                $(".tooltip").tooltip("dispose");

                //選択状態解除
                if (!vue.keepSelect) {
                    grid.prevSelection = grid.Selection()._areas;
                    grid.Selection().removeAll();
                }

                //完了時更新関数
                if (vue._onCompleteFunc && vue.grid) {
                    vue._onCompleteFunc(grid, ui);
                }

                if (!!grid.pdata && !!grid.options.groupModel.on && !!grid.options.groupModel.dataIndx.length) {
                    vue.setNavigatorSelect(grid);
                }

                //$("body > div").has("a:contains(ParamQuery)").hide();
            },
            render: function(event, ui) {
                //console.log("grid render");
            },
            change: function (event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                // console.log("grid change");

                grid.refreshView();

                //変更時更新関数
                if (vue._onChangeFunc && vue.grid) {
                    vue._onChangeFunc(grid, ui, event);
                }
            },
            click: function (event, ui) {
                //console.log("grid click");
            },
            cellClick: function (event, ui) {
                var grid = this;
                var vue = grid.options.vue;

                if (ui.$td.hasClass("autocomplete-cell") || ui.$td.hasClass("select-cell")) {
                    grid.editCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                }
                if (grid.options.selectionModel.type == "cell") {
                    vue.isSelection = !!grid.Selection()._areas.length;
                }

                // var current = event.currentTarget;
                // var original = event.originalEvent.target;
                // var cb = ui.$td.has(":checkbox");
                // if (cb.length) {
                //     cb[0].click();
                // }
            },
            cellSave: function (event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                // console.log("grid cellSave");

                //変更時更新関数
                if (vue._onCellSaveFunc && vue.grid) {
                    vue._onCellSaveFunc(grid, ui, event);
                }

                if (ui.newVal != ui.oldVal) {
                    ui.rowData[ui.dataIndx] = ui.newVal;
                    this.refreshView();
                }
            },
            scroll: function (event, ui) {
                var grid = this;

                //スクロール中にbootstrap tooltipのゴミが残るので消去(bootstrapの処理にモレ)
                $("body").find("[id^=tooltip]").tooltip("hide");

                //drag handle refresh
                if (grid.options.dragModel.on) {
                    grid.refresh();
                }
            },
            scrollStop: function (event, ui) {
                //console.log("grid scrollStop");
            },
            selectChange: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var id = vue.id;

                //セル選択設定で行選択をしている場合は解除
                if (!vue.isMultiRowSelectable) {
                    if (grid.SelectRow().getSelection().length > 0) {
                        grid.setSelection(null);
                    }
                }

                //exit editor
                var editCell = grid.getEditCell().$cell;
                if (editCell) {
                    var indices = grid.getCellIndices(grid.getEditCell());
                    var next = ui.selection.address()[0];

                    if (next && (indices.rowIndx != next.r1 || indices.colIndx != next.c1)) {
                        // console.log("grid selectChange quit edit");
                        vue.setCellState(grid, indices);
                        grid.quitEditMode();
                    }
                }

                //パラメータ指定更新関数
                if (vue._onSelectChangeFunc && vue.grid) {
                    vue._onSelectChangeFunc(grid, ui);
                }

                if (!grid.isRight) {
                    vue.isSelection = !_.isEmpty(grid.getSelectionData());
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
                var grid = this;
                var vue = grid.options.vue;

                //exit editor
                var editCell = grid.getEditCell().$cell;
                if (editCell) {
                    var indices = grid.getCellIndices(grid.getEditCell());

                    // console.log("grid selectChange quit edit");
                    vue.setCellState(grid, indices);
                    grid.quitEditMode();
                }

                if (grid.options.selectionModel.type == "row") {
                    var isSelection = grid.SelectRow().getSelection().length > 0;

                    vue.isSelectionFirst = isSelection ? _.first(grid.SelectRow().getSelection()).rowIndx == 0 : false;
                    vue.isSelectionLast = isSelection ? _.last(grid.SelectRow().getSelection()).rowIndx == grid.pdata.length - 1 : false;
                    vue.isSelection = isSelection;
                    vue.selectionRowCount = grid.SelectRow().getSelection().length;
                    vue.selectionRow = vue.selectionRowCount > 0 ? grid.SelectRow().getSelection()[0] : null;
                    vue.selectionRowList = grid.SelectRow().getSelection();
                    return;
                }

                if (!vue.isMultiRowSelectable && ui.addList.length > 0) {
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
            beforeRowSelect: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;

                if (vue.isMultiRowSelectable) {
                    return true;
                } else {
                    return (!!window.event && !!window.event.ctrlKey) || !!ui.addList.length;
                }
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
                // console.log("beforeCellKeyDown: " + event.which);

                if (vue.onBeforeCellKeyDownFunc) {
                    var ret = vue.onBeforeCellKeyDownFunc(grid, ui, event);
                    if (!ret) return false;
                }

                if (event.altKey && event.key != "Alt") {
                    $(document).trigger($.Event("keydown", {
                            key: event.key,
                            keyCode: event.keyCode,
                            which: event.which,
                            shiftKey: event.shiftKey,
                            ctrlKey: event.ctrlKey,
                            altKey: event.altKey,
                        })
                    );
                    return false;
                }

                switch(event.which) {
                    case 13:   //"enter"
                    case 32:   //"space"
                        if (event.which == 13 && !!grid.vue.onEnterMove) {
                            return vue.moveNextCell(grid, ui, event.shiftKey);
                        }

                        if (cell.has(":checkbox").length) {
                            cell.find(":checkbox")[0].click();
                        } else if (ui.column.editable) {
                            grid.editCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                        } else {
                            return true;
                        }

                        return false;
                    case 9:
                        return vue.moveNextCell(grid, ui, event.shiftKey);
                    default:
                        return true;
                }
            },
            cellKeyDown: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var $cell = grid.getCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                //console.log("cellKeyDown: " + event.which);

                if (vue.onCellKeyDownFunc) {
                    var ret = vue.onCellKeyDownFunc(grid, ui, event);
                    if (!ret) return false;
                }

                if (event.key == "Backspace") {
                    //Backspace -> Delete
                    $cell.trigger($.Event("keydown", {
                            key: "Delete",
                            keyCode: 46,
                            which: 46,
                            shiftKey: event.shiftKey,
                            ctrlKey: event.ctrlKey,
                            altKey: event.altKey,
                        })
                    );
                    return false;
                }

                if (event.ctrlKey) {
                    switch(event.which) {
                        case 107:   //"+"
                        case 187:   //";"
                            this.addRow({
                                rowIndx: ui.rowIndx,
                                newRow: !!vue._onAddRowFunc ? vue._onAddRowFunc(grid, ui.rowData) : {},
                                checkEditable: false
                            });
                            return false;
                        case 109:   //"-"
                        case 189:   //"-"
                            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                            grid.deleteRow({ rowList: rowList });
                            return false;
                    }
                }

                return true;
            },
            editorKeyDown: function(event, ui) {
                var grid = this;
                var vue = grid.options.vue;
                var cell = this.getCell({rowIndx: ui.rowIndx, dataIndx: ui.dataIndx});
                // console.log("editorKeyDown: " + event.which);

                if (event.isOnce) {
                    return true;
                }

                if (!event.ctrlKey && !event.shiftKey) {
                    switch(event.which) {
                        case 9:
                        case 13:
                        case 38:
                        case 40:
                            //moveNextCell or add new row
                            return vue.moveNextCell(grid, ui, event.shiftKey, event.which);
                    }
                }

                return true;
            },
            beforeFilter: (evt, ui) => {
                //絞り込み条件変更時に値が失われることの対処
                var vue = this;
                var grid = getGrid(evt.target);

                console.log("before filter")
                if (!!grid.pdata && !!grid.options.groupModel.on && !!grid.options.groupModel.dataIndx.length) {
                    vue.setNavigatorSelect(grid);
                }

                if (!ui.rules.length) return;

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
        if (vue._onBeforeCreateFunc) {
            vue._onBeforeCreateFunc(pqGridObj, () => vue.createPqGrid(pqGridObj));
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
            var vue = this;
            var postData = $.extend(true, {}, this.query);

            try {
                //dataModelの設定
                pqGridObj.dataModel = {
                    recIndx: "pq_ri",
                    url: this.SearchOnCreate ? this.dataUrl : null,
                    method: "POST",
                    postData: postData,
                    location: "remote",
                    beforeSend: function(jqXHR, settings) {
                        var grid = this;

                        grid.prevPostData = _.cloneDeep(grid.options.dataModel.postData);

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
                        } else if (res.Data) {
                            vue.CountConstraint = false;
                            res = res.Data;
                        } else {
                            vue.CountConstraint = false;
                        }

                        //削除用検索時初期値の設定
                        res.forEach(v => v.InitialValue = $.extend(true, {}, v));

                        //検索前データの保持
                        grid.prevData = _.cloneDeep(grid.getData());

                        //検索後callbackが指定されていれば実行
                        if (vue._onAfterSearchFunc) {
                            res = vue._onAfterSearchFunc(vue, grid, res);
                        }

                        //PKを比較し、検索前のレコードが全てあるか判定
                        // if (grid.getData() && _.differenceWith(grid.getData(), res, (a, b) => a.PK == b.PK).length == 0) {
                        //     //colModelでkeep設定されている列取得
                        //     var keeps = grid.options.colModel.filter((v) => v.keep).map((v) => v.dataIndx);

                        //     //対象にPK列も追加
                        //     keeps.push("PK");

                        //     //検索前の上記列の値とPKを抽出
                        //     var keepVals = grid.getData().map((v) => _.pick(v, keeps));

                        //     //keepした値で復元
                        //     res.forEach(function(row, i) {
                        //         var keepVal = keepVals.filter((v) => v.PK == row.PK);
                        //         if (keepVal.length == 1) {
                        //             $.extend(true, row, keepVal[0]);
                        //         }
                        //     });
                        // }

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
                // pqGridObj.colModel.push({ title: "自動生成PK", dataType: "integer", dataIndx: "PK", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "プラント", dataType: "string", dataIndx: "Plant", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "作成日", dataType: "string", dataIndx: "CreateDate", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "作成者", dataType: "string", dataIndx: "CreateUser", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "更新日", dataType: "string", dataIndx: "UpdateDate", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "更新者", dataType: "string", dataIndx: "UpdateUser", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "削除日時", dataType: "date", dataIndx: "DeleteDate", editable: false, hidden: true });
                // pqGridObj.colModel.push({ title: "Ver.No.", dataType: "integer", dataIndx: "VersionNo", editable: false, hidden: true });

                //PqGridの変更検知のdeleteがバグっているので検索時の値を保持する項目を追加(検索時の値ではなく、削除時の値でdeleteListに格納される)
                pqGridObj.colModel.push(
                    { title: "初期値", dataType: "string", dataIndx: "InitialValue", editable: false, hidden: true,
                        render: function (ui) {
                            //console.log("initialValue render")

                            if (!!ui.Export) return ui;

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
                var gridId = _.keys(vue.$parent.$refs).find(v => this.id.startsWith(v));
                this.$parent.$data[gridId] = this.grid;

                //bootstrap tooltip
                if (!vue.autoToolTipDisabled) {
                    $(document)
                        .on("mouseleave", ".pq-grid-cell:not(.ui-error-state):visible", event => $(event.target).tooltip("hide"))
                        .on("mouseenter", ".pq-grid-cell:not(.ui-error-state):visible",
                            event => {
                                var $ele = $(event.target);
                                if (!$ele.hasClass("pq-grid-cell")) return true;

                                var targets = [];
                                if (!!$ele.children("div").length) {
                                    targets = $ele.children("div").find("[title]").get();
                                    if (!targets.length) return;
                                } else {
                                    targets = [$ele]
                                }

                                $("*").tooltip("hide");

                                targets.forEach(e => {
                                    var $e = $(e);
                                    var title = $e.attr("title") || $e.text().replace(/(, )+$/, "").replace(/, /g, "<br>");

                                    if (!!title && _.trim(title) != "") {
                                        $e.tooltip({
                                            container: "body",
                                            animation: false,
                                            template: '<div class="tooltip text-overflow" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                                            html: true,
                                            placement: "auto",
                                            trigger: "hover",
                                            title: title,
                                        })
                                        .tooltip("show")
                                        ;
                                    }
                                });

                                return false;
                            });
                }

                //colModelのeditableの設定より、PqGridのclassに入力可不可設定
                var cfg = pqGridObj.columnTemplate.editable ? "editable" : "readonly";
                this.grid.widget().addClass("table_" + cfg);

                //カスタムDropメソッド
                // if (!!pqGridObj.dropModel && !!pqGridObj.dropModel.customDrop) {
                //     this.grid.options.dropModel.orgDrop = this.grid.options.dropModel.drop;
                //     this.grid.options.dropModel.drop = pqGridObj.dropModel.customDrop;
                // }
                // if (!!pqGridObj.dropModel && !!pqGridObj.dropModel.enter) {
                //     this.grid.widget().find(".ui-droppable").mouseenter(event => this.grid.options.dropModel.enter(event, this.grid));
                // }

                if (this.hasFreezeRightCols) {
                    var widget = this.grid.widget();
                    var mr = widget.attr("class").match(/mr-[0-9]+/g);
                    if (mr) {
                        mr.forEach(v => widget.removeClass(v));
                    }

                    //指定数分、メインテーブルでは列非表示設定
                    var rightColsDataIndices = _.takeRight(
                            this.grid.options.colModel.filter(c => !c.hidden),
                            this.freezeRightCols
                        )
                        .map(c => c.dataIndx);
                    var rightColsWidth = _.sum(this.grid.options.colModel
                                            .filter(c => rightColsDataIndices.includes(c.dataIndx) && !c.float)
                                            .map(c => c.width * 1)
                                        );
                    this.grid.options.colModel
                        .filter(c => !c.hidden)
                        .forEach((c, i) => c.hidden = rightColsDataIndices.includes(c.dataIndx));
                    this.grid.options.width = "100%-" + rightColsWidth;
                    widget.addClass("hasRight");

                    //右テーブル用option
                    var rightOptions = _.omit(pqGridObj, "colModel");
                    rightOptions.colModel = _.cloneDeep(pqGridObj.colModel)
                        .map((c, i) => {
                            c.hidden = !rightColsDataIndices.includes(c.dataIndx) || !!c.float;
                            return c;
                        });

                    rightOptions.numberCell = { show: false };
                    rightOptions.width = rightColsWidth + 28;
                    rightOptions.dataModel.location = "local";

                    widget.parent().addClass("d-flex");

                    this.gridRight = $("#" + this.id + "_right")
                        .attr("class", "grid-right mt-2")
                        .pqGrid(rightOptions)
                        .pqGrid("getInstance").grid;

                    this.grid.gridRight = this.gridRight;
                    this.gridRight.isRight = true;
                    this.gridRight.gridLeft = this.grid;

                    this.grid.on("scroll", function() {
                        vue.scrollBoth(this.scrollY());
                    });
                    this.gridRight.on("scroll", function() {
                        vue.scrollBoth(this.scrollY());
                    });

                    this.grid.refreshView();
                }

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
                    // var isChanged = $.map(grid.createSaveParams(), (v) => v.length > 0).includes(true);
                    var changes = grid.createSaveParams();
                    changes.AddList = changes.AddList.filter(r => {
                        var data = _.cloneDeep(r);
                        if (grid.vue.autoEmptyRowFunc) {
                            data = diff(data, grid.vue.autoEmptyRowFunc());
                        }

                        return _.values(data).some(v => v != null && v != undefined);
                    });
                    var isChanged = _.values(changes).some(v => v.length > 0);

                    return isChanged;
                };

                //変更通知メソッド追加
                this.grid.notifyChanged = function(message, cancelCallback) {
                    var grid = this;

                    if (vue.checkChanged && (vue.checkChangedFunc ? vue.checkChangedFunc(grid) : grid.isChanged())) {
                        //確認ダイアログ
                        $.dialogConfirm({
                            title: "内容が変更されています",
                            contents: message || "再検索を行いますか？(変更は破棄されます)",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        if (cancelCallback) {
                                            cancelCallback(grid, () => {
                                                $(this).dialog("close");
                                            });
                                        } else {
                                            $(this).dialog("close");
                                        }
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
                            keyDownHandler: (element, option, event) => {
                                if (event.which == 27) {
                                    $(element).dialog("close");
                                }
                            },
                        });
                    } else {
                        if (cancelCallback) {
                            cancelCallback(grid, () => {
                                $(this).dialog("close");
                            });
                        } else {
                            $(this).dialog("close");
                        }
                    }
                };

                //検索メソッド追加
                this.grid.searchData = function(params, isActivated, beforeCallback, afterCallback) {
                    var grid = this;
                    var vue = grid.options.vue;

                    if (afterCallback) {
                        var newFunc = (grid, ui) => {
                            if (vue.onCompleteFunc) vue.onCompleteFunc(grid, ui);
                            afterCallback(grid, ui);
                            vue._onCompleteFunc = vue.onCompleteFunc;
                        };
                        vue._onCompleteFunc = newFunc;
                    }

                    if (vue.checkChanged && (vue.checkChangedFunc ? vue.checkChangedFunc(grid) : grid.isChanged()) && !isActivated) {
                        //確認ダイアログ
                        $.dialogConfirm({
                            title: "内容が変更されています",
                            contents: "再検索を行いますか？(変更は破棄されます)",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        grid.options.dataModel.url = grid.options.vue.dataUrl

                                        grid.options.dataModel.postData = _.cloneDeep(params || grid.options.dataModel.postData);
                                        if (beforeCallback) {
                                            beforeCallback(grid, () => {
                                                grid.refreshDataAndView();
                                                $(this).dialog("close");
                                            });
                                        } else {
                                            grid.refreshDataAndView();
                                            $(this).dialog("close");
                                        }
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        if (vue.checkChangedCancelFunc) {
                                            vue.checkChangedCancelFunc(grid);
                                        }
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                            keyDownHandler: (element, option, event) => {
                                if (event.which == 27) {
                                    if (vue.checkChangedCancelFunc) {
                                        vue.checkChangedCancelFunc(grid);
                                    }

                                    $(element).dialog("close");
                                }
                            },
                        });
                    } else {
                        grid.options.dataModel.url = grid.options.vue.dataUrl
                        grid.options.dataModel.postData = _.cloneDeep(params || grid.options.dataModel.postData);

                        if (beforeCallback) {
                            beforeCallback(grid, () => {
                                grid.refreshDataAndView();
                            });
                        } else {
                            grid.refreshDataAndView();
                        }
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
                    grid.options.dataModel.dataUF = [];
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
                    changeList.addList = _.intersectionBy(changeList.addList, grid.getData(), grid.options.dataModel.recIndx);
                    changeList.updateList = _.intersectionBy(changeList.updateList, grid.getData(), grid.options.dataModel.recIndx);

                    //空行追加したものに入力した場合、addListではなくupdateListに入るので、addListに移動
                    changeList.updateList.forEach(function(row, i) {
                        //追加済であれば良い
                        if (changeList.addList.map(v => v[grid.options.dataModel.recIndx]).indexOf(row[grid.options.dataModel.recIndx]) != -1) {
                            return;
                        }

                        //初期値とVersionNoを持つ場合はupdate, 持たない場合はadd
                        if (!row.InitialValue && typeof(row.VersionNo) == "undefined") {
                            changeList.addList.push(row);
                        }
                    });

                    //addListにupdateListに追加した行が含まれるので補正、PqGridバグっぽい
                    changeList.addList.forEach(function(v) {
                        var idx  = changeList.updateList.map(v => v[grid.options.dataModel.recIndx]).indexOf(v[grid.options.dataModel.recIndx]);

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
                    var excepts = exceptsColumnArray || grid.options.vue._onChangeExceptsColumns;
                    var updateList = $.extend(true, [], changeList.updateList);
                    updateList.forEach(function(row) {
                        var idx = changeList.updateList.map(v => v[grid.options.dataModel.recIndx]).indexOf(row[grid.options.dataModel.recIndx]);
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

                            //pqGrid propを削除
                            _.keys(row).filter(k => k.startsWith("pq_"))
                            .forEach(k => {
                                delete row[k];
                            });

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
                this.grid.getPlainPData = function() {
                    var grid = this;
                    return grid.pdata.map(r => _.omitBy(r, (v, k) => k.startsWith("pq_") || k == "InitialValue"));
                };

                //保存メソッド追加
                this.grid.saveData = function(options, opti_onEditFunc) {
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
                        params: $.extend(true, (_.isEmpty(options.params) ? grid.createSaveParams() : options.params), options.optional),
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
                    if (opti_onEditFunc) {
                        opti_onEditFunc(vue, grid, defOp, options);
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
                                    vue.$root.$emit("addMessage", op.done.title + "(" + vue.$parent.$data.ScreenTitle + ")");

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
                                console.log("saveFunc Error", error);
                                var status = error.response.status;
                                var errObj = error.response.data;

                                //コールバックの実行
                                var ret = op.error.callback(vue, grid, errObj);

                                if (ret != false) {
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

                                    //メッセージ追加
                                    vue.$root.$emit("addMessage",
                                        op.error.title + "(" + vue.$parent.ScreenTitle + ":" + errObj.message + ")");
                                }

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

                    if (grid.SelectRow().getSelection().length > 0) {
                        return grid.SelectRow().getSelection();
                    }

                    var address = grid.Selection().address();
                    if (address.length == 0) return null;
                    var rowIndx = grid.Selection().address()[0].r1;
                    var rowData = _.cloneDeep(grid.pdata[rowIndx]);

                    if (!!rowData) {
                        if (rowData.InitialValue) {
                            delete rowData.InitialValue;
                        }

                        if (rowData.hasOwnProperty("pq_rowselect")) {
                            delete rowData.pq_rowselect;
                        }
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

                //印刷メソッド追加
                this.grid.print = function(optionSetter) {
                    var grid = this;
                    var vue = grid.options.vue;

                    if (optionSetter) {
                        optionSetter(grid);
                    }

                    vue.exportData("", true);
                };

                this.grid.generateHtml = function(styles, header, maxRowsPerPage, isShowGroupRow, isShowGroupSummaryRow, isGroupPageBreak, isShowGrandSummaryRow, bodyWrapper, headerWrapper) {
                    var grid = this;

                    var colModel = _.cloneDeep(grid.options.colModel);

                    var printCol = grid.options.colModel.map(v => {
                        if (v.hiddenOnExport != undefined) {
                            v.hidden = v.hiddenOnExport;
                        }
                        return v;
                    });
                    grid.options.colModel = printCol;
                    grid.refreshCM();
                    grid.refresh();

                    var table = $($(grid.exportData({ format: "htm", render: true }))[3]).addClass(grid.vue.id);
                    var tableHeaders = table.find("tr").filter((i, v) => !!$(v).find("th").length).get();
                    var tableBodies = table.find("tr").filter((i, v) => !$(v).find("th").length).get();

                    var pdata = _.cloneDeep(grid.pdata.filter(v => !v.pq_hidden));

                    tableBodies.forEach((r, i) => {
                        var rd = pdata[i];

                        if (!rd) {
                            //grand summary
                            $(r).addClass("grand-summary");
                        } else {
                            if (!!rd.pq_gsummary) {
                                $(r).addClass("group-summary");
                                $(r).attr("level", rd.pq_level)
                            } else if (rd.pq_level != undefined) {
                                $(r).addClass("group-row");
                                $(r).attr("level", rd.pq_level)
                            }
                        }
                    });

                    var ret = grid.restructTable(pdata, tableBodies, tableHeaders, styles, header, maxRowsPerPage, isShowGroupRow, isShowGroupSummaryRow, isGroupPageBreak, isShowGrandSummaryRow, bodyWrapper, headerWrapper, table);

                    grid.options.colModel = colModel;
                    grid.refreshCM();
                    grid.refresh();

                    return ret;
                };

                this.grid.restructTable = function(pdata, tableBodies, tableHeaders, styles, header, maxRowsPerPage, isShowGroupRow, isShowGroupSummaryRow, isGroupPageBreak, isShowGrandSummaryRow, bodyWrapper, headerWrapper, table) {
                    var grid = this;
                    var ret = "";

                    //TODO: 暫定対応(10pt以下のフォントでの表示の代わりに、transform: scale(0.x)を用いる余地として、tdのcontentをdivでwrap)
                    if (!!bodyWrapper) {
                        if (_.isBoolean(bodyWrapper)) {
                            tableBodies.forEach(r => $(r).find("td").each((i, e) => $(e).html($("<p>").html($(e).html()))));
                        } else if (_.isFunction(bodyWrapper)) {
                            tableBodies.forEach(r => $(r).find("td").each((i, e) => $(e).html(bodyWrapper(e, r))));
                        }
                    }

                    if (!maxRowsPerPage && !isGroupPageBreak && !grid.options.autoRow) {
                        ret = $("<div>")
                            .append($("<style>").text(styles || ""))
                            .append(header || "")
                            .append(table)
                            ;
                    } else {
                        var chunks = [];
                        var headers = [];

                        var isShow = (r, p, a) => {
                            if ($(r).hasClass("group-row")) {
                                if (_.isArray(isShowGroupRow)) {
                                    var conf = isShowGroupRow[$(r).attr("level") * 1];
                                    if (_.isFunction(conf)) {
                                        return conf(r, p, a);
                                    } else {
                                        return conf;
                                    }
                                } else {
                                    return isShowGroupRow;
                                }
                            } else if ($(r).hasClass("group-summary")) {
                                if (_.isArray(isShowGroupSummaryRow)) {
                                    var conf = isShowGroupSummaryRow[$(r).attr("level") * 1];
                                    if (_.isFunction(conf)) {
                                        return conf(r, p, a);
                                    } else {
                                        return conf;
                                    }
                                } else {
                                    return isShowGroupSummaryRow;
                                }
                            } else if ($(r).hasClass("grand-summary")) {
                                return isShowGrandSummaryRow != false;
                            } else {
                                return true;
                            }
                        };

                        var isBreak = (r, p, a) => {
                            if ($(r).hasClass("group-row")) {
                                if (_.isArray(isGroupPageBreak)) {
                                    var conf = isGroupPageBreak[$(r).attr("level") * 1];
                                    if (_.isFunction(conf)) {
                                        return conf(r, p, a);
                                    } else {
                                        return conf && (!headers.length || !!a.length);
                                    }
                                } else {
                                    return isGroupPageBreak && (!headers.length || !!a.length);
                                }
                            } else {
                                return false;
                            }
                        };

                        var splitChunks = (a, maxRowsPerPage) => {
                            var c = [];
                            if (!grid.options.autoRow) {
                                c = _.chunk(_.cloneDeep(a), maxRowsPerPage);
                            } else {
                                var last = _.reduce(
                                    _.cloneDeep(a),
                                    (acc, row, idx) => {
                                        var rh = 1 + _.max($(row).find("td").map((i,e) => $(e).find("br").length));

                                        if (acc.ri + rh > maxRowsPerPage) {
                                            c.push(acc.chunks);
                                            acc.ri = rh;
                                            acc.chunks = [row];
                                        } else {
                                            acc.ri += rh;
                                            acc.chunks.push(row);
                                        }
                                        return acc;
                                    },
                                    {
                                        ri: 0,
                                        chunks: [],
                                    }
                                );

                                if (!!last.chunks.length) {
                                    c.push(last.chunks);
                                }
                            }
                            return c;
                        };

                        var dr = _.reduce(tableBodies, (a, r, i) => {
                            var p = pdata[i];

                            if (isBreak(r, p, a)) {
                                headers.push(p);

                                if (!!a.length) {
                                    var c;
                                    if (!!maxRowsPerPage) {
                                        c = splitChunks(a, maxRowsPerPage, headers);

                                        if (c.length > 1) {
                                            headers = _.dropRight(headers);
                                            headers.push(..._.range(c.length - 1).map(v => _.last(headers)));
                                            headers.push(p);
                                        }
                                    } else {
                                        c = [_.cloneDeep(a)];
                                    }
                                    chunks.push(c);
                                    a = [];

                                    if (isShow(r, p, a)) {
                                        a.push(r);
                                    }
                                }
                            } else {
                                if (isShow(r, p, a)) {
                                    a.push(r);
                                }
                            }
                            return a;
                        }, []);
                        if (!!dr.length) {
                            var c;
                            if (!!maxRowsPerPage) {
                                c = splitChunks(dr, maxRowsPerPage, headers);
                                if (c.length > 1) {
                                    headers.push(..._.range(c.length - 1).map(v => _.last(headers)));
                                }
                            } else {
                                c = [_.cloneDeep(dr)];
                            }
                            chunks.push(c);
                        }

                        chunks = _(chunks).values().flatten().value();
                        if (!chunks.length) chunks = [null];
                        //console.log("printable chunks", chunks, headers);

                        ret = $("<div>")
                            .append($("<style>").text(styles || ""))
                            .append(
                                chunks.map((chunk, i) => {
                                    var groupLength = !!isGroupPageBreak ? _.groupBy(headers, v => v.pq_gid)[headers[i].pq_gid].length : chunks.length;
                                    var groupPage = i + 1;
                                    if (!!isGroupPageBreak) {
                                        var gls = _.values(_.groupBy(headers, v => v.pq_gid)).map(v => v.length);
                                        for (var gi in gls) {
                                            if (groupPage - gls[gi] > 0) {
                                                groupPage -= gls[gi];
                                            } else {
                                                break;
                                            }
                                        }
                                    }

                                    var page = $("<div>").css("break-before", "page").addClass("page_div")
                                        .append(!!header ? (_.isFunction(header) ? header(headers[i], i, chunks.length, groupPage, groupLength) : header) : "")
                                        .append(
                                            $("<table>").addClass(grid.vue.id)
                                                .append($("<thead>").append(tableHeaders))
                                                .append($("<tbody>").append(chunk))
                                        );

                                    return page.prop("outerHTML");
                                })
                            );

                    }

                    return ret;
                };

                this.grid.generateHtmlFromJson = function(target, styles, header, maxRowsPerPage, isShowheader, byHtml, keyArray, colArray, bodyWrapper, headerWrapper) {
                    var grid = this;
                    var json = target;
                    if (!_.isArray(json)) json = [json];

                    var keys =keyArray || _.keys(json[0]);
                    var headers = !!isShowheader
                        ? $("<tr>").append(
                            keys.map((k, i) => $("<th>")[!!byHtml ? "html" : "text"](!!colArray ? colArray[i] : k))).get()
                        : [];
                    var bodies = json.map(v => $("<tr>").addClass(v.class || "")
                        .append(
                            keys.map(k => !!byHtml ? $("<td>").html(v[k]) : $("<td>").text(v[k]))
                        ).get(0)
                    );
                    var ret = grid.restructTable(json, bodies, headers, styles, header, maxRowsPerPage, null, null, null, null, bodyWrapper, headerWrapper);
                    return ret;
                };

                this.grid.getExcelColumnName = function(val, format) {
                    var grid = this;
                    var num = _.isInteger(val) ? val : grid.options.colModel.find(v => v.dataIndx == val).leftPos + 1;

                    for (var nm = "", a = 1, b = 26; (num -= a) >= 0; a = b, b *= 26) {
                        nm = String.fromCharCode(parseInt((num % b) / a) + 65) + nm;
                    }

                    return !!format ? (x => eval("`" + format + "`")).apply(nm) : nm;
                };

                this.grid.setLocalData = function(data) {
                    var grid = this;
                    var vue = grid.options.vue;

                    var location = grid.options.dataModel.location;
                    grid.options.dataModel.location = "local";
                    grid.options.dataModel.data = data;
                    grid.refreshDataAndView();
                    grid.options.dataModel.location = location;
                };

                //blink
                this.grid.blinkCell = function(rowIndx, dataIndx) {
                    var grid = this;
                    var vue = grid.options.vue;

                    var $cell = grid.getCell({ rowIndx: rowIndx, dataIndx: dataIndx });

                    $cell.addClass("blinking")
                        .delay(3000)
                        .queue(next => {
                            $cell.removeClass("blinking");
                            next();
                        })
                };
                this.grid.blinkRow = function(rowIndx) {
                    var grid = this;
                    var vue = grid.options.vue;

                    var $row = grid.getRow({ rowIndx: rowIndx });

                    $row.removeClass("blinking");
                    $row.addClass("blinking");
                };
                this.grid.blinkDiff = function(compare, exceptEmpty) {
                    var grid = this;
                    var vue = grid.options.vue;

                    var prev = _.cloneDeep(grid.getData().filter(v => grid.options.colModel.filter(c => !!c.key).every(c => !!v[c.dataIndx])))
                        .map(v => {
                            delete v.InitialValue;
                            // delete v.pq_ri;
                            return v;
                        });

                    var dl = diff(prev, compare);

                    if (exceptEmpty) {
                        _.forIn(dl, (v, k) => {
                            var r = _.omitBy(v, (vv, kk) => vv == undefined);
                            if (_.isEmpty(r)) {
                                delete dl[k];
                            } else {
                                dl[k] = r;
                            }
                        })
                    }

                    if (_.isEmpty(dl)) return;

                    var data = compare.map(v => {v.InitialValue = _.cloneDeep(v); return v;});
                    var location = grid.options.dataModel.location;
                    grid.options.dataModel.location = "local";
                    grid.options.dataModel.data = data;
                    grid.refreshDataAndView();
                    grid.options.dataModel.location = location;

                    if (prev.length != compare.length) return;

                    _.forIn(dl, (rowData, i) => {
                        var rowIndx = grid.getRowIndx({rowData: prev[i]}).rowIndx;

                        if (rowIndx != undefined && !_.isEmpty(_.omit(rowData, "pq_ri"))) {
                            _.forIn(rowData, (value, dataIndx) => {
                                grid.blinkCell(rowIndx, dataIndx);
                            });
                        }
                    });
                };



                this.grid.options.loading = false;

                this.grid.vue = vue;
                window[this.grid.widget().prop("id")] = this.grid;
                window.grid = this.grid;

                if (!!this.grid.gridRight) {
                    window[this.grid.widget().prop("id") + "_right"] = this.grid.gridRight;
                    window.gridRight = this.grid.gridRight;
                }

                //PqGridのリサイズ
                this.resize();
            } catch (ex) {
                console.log("pqGrid create exception", ex);
                throw ex;
            }
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
        },
        resize: function () {
            //console.log(this.$options.name + " resize");

            if (this.grid && !!this.grid.options && !this.grid.options.loading) {
                //リサイズ関数が指定されている場合、優先呼び出し
                this.resizeFunc ? this.resizeFunc(this.grid) : this.resizeBase();
            }
        },
        resizeBase: function () {
            if (!this.grid || !this.grid.options) return;

            if (this.options.height) return;

            //PqGridリサイズ基本設定(ヘッダーとフッターの間に収まるように)
            //widget
            var widget = this.grid.widget();
            var container = widget.parent().closest("div");

            //heightを適正に変更
            var oldHeight = widget.outerHeight();

            //body-content
            var content = widget.closest(".body-content");

            //空き領域を計算
            var contentHeight = content.height();
            var elementsHeightSum = _.sum(content.find("form > *")
                .map((i, el) => {
                    if ($(el).hasClass("row")) {
                        return $(el).height() + 4;  //mergin-bottom
                    } else {
                        return $(el).outerHeight(true);
                    }
                })
            )
            - container.outerHeight(true);

            //TODO: 厳密には可変サイズのGridが複数存在する場合を考慮に入れなければならないか？ -> そのような画面設計を避けるか...
            //新サイズ計算
            // var newHeight = oldHeight + contentHeight - elementsHeightSum - 10;
            var newHeight = contentHeight - elementsHeightSum - 10;

            if (!this.isFixedHeight && _.round(newHeight) != _.round(oldHeight)) {
                this.grid.options.height += (_.round(newHeight) - _.round(oldHeight));
                this.grid.refresh();

                if (!!this.grid.gridRight) {
                    this.grid.gridRight.options.height += (_.round(newHeight) - _.round(oldHeight));
                    this.grid.gridRight.refresh();
                }
            }
        },
        onSearchErrors: function(grid, errObj) {
            //activate時の検索の場合、メッセージ表示を抑制
            if (this.isActivated && !grid.options.loading) {
                this.isActivated = false;
                return;
            }

            //パラメータ指定優先でエラー処理関数実行
            this._onSearchErrorsFunc ? this._onSearchErrorsFunc(grid, errObj) : this.onSearchErrorsBase(grid, errObj);
        },
        onSearchErrorsBase: function(grid, errObj) {
            //検索時エラー処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(_onSearchErrorsFunc)を行う
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
            this._onSearchExceptionsFunc ? this._onSearchExceptionsFunc(grid, exObj) : this.onSearchExceptionsBase(grid, exObj);
        },
        onSearchExceptionsBase: function(grid, exObj) {
            //検索時例外処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(_onSearchExceptionsFunc)を行う
            var vue = this;

            //メッセージリストに追加
            if (exObj.isNew) {
                console.log("PqGridWrapper onSearchExceptions");
                console.log(exObj);

                vue.$root.$emit("addMessage", "例外発生:" + (exObj.message || exObj.statusText));

                //例外表示
                $.dialogErr({ errObj: exObj });
                // setTimeout(function() {
                //     vue.$router.push({ path: "/SIP/Exceptions", query: exObj });
                // }, 400);
            }
        },
        onSaveErrors: function(grid, errObj) {
            //パラメータ指定優先でエラー処理関数実行
            this._onSaveErrorsFunc ? this._onSaveErrorsFunc(grid, errObj) : this.onSaveErrorsBase(grid, errObj);
        },
        onSaveErrorsBase: function(grid, errObj) {
            //保存時エラー処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(_onSaveErrorsFunc)を行う
            var vue = this;

            //エラー種別判定
            if (errObj.name == "UniqueConstraintException") {   //一意制約違反

                var keyCols = grid.colModel.filter(c => c.key).map(c => c.dataIndx);
                var keyTitles = grid.colModel.filter(c => c.key).map(c => c.title);
                var setCols = grid.colModel.filter(c => c.key && !c.hidden && c.editable).map(c => c.dataIndx);

                //キーの一致する行を検索し、該当セルにエラー設定
                var errorVals = _.pick(errObj.errors, keyCols);
                if (vue._onErrorValsMapFunc) {
                    errorVals = vue._onErrorValsMapFunc(errorVals);
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
            this._onSaveExceptionsFunc ? this._onSaveExceptionsFunc(grid, exObj) : this.onSaveExceptionsBase(grid, exObj);
        },
        onSaveExceptionsBase: function(grid, exObj) {
            //保存時例外処理基本関数
            //個別に指定したい場合は、当コンポーネントにパラメータ指定(_onSaveExceptionsFunc)を行う
            var vue = this;

            console.log("PqGridWrapper onSaveExceptions");
            console.log(exObj);

            //メッセージリストに追加
            vue.$root.$emit("addMessage", "例外発生:" + (exObj.message || exObj.statusText));

            //例外表示
            $.dialogErr({ errObj: exObj });
            // setTimeout(function() {
            //     vue.$router.push({ path: "/SIP/Exceptions", query: exObj });
            // }, 400);
        },
        setCellState: function(grid, ui, onHistory) {
            var $cell = grid.getEditCell().$cell;

            if ($cell.find(".has-error").length > 0) {
                var tooltip = $cell.find(".target-input").data("bs.tooltip");
                ui.rowData.pq_inputErrors[ui.dataIndx] = !!tooltip ? tooltip.config.title : "入力内容が正しくありません";
            } else {
                if (!!ui.rowData.pq_inputErrors && !!ui.rowData.pq_inputErrors[ui.dataIndx]) {
                    delete ui.rowData.pq_inputErrors[ui.dataIndx];
                }
            }

            //rowData更新
            grid.updateRow({ rowIndx: ui.rowIndx, newRow: ui.column.binder, history: onHistory != false });
        },
        moveNextCell: function(grid, ui, reverse, key) {
            var vue = this;
            var editor = grid.getEditCell().$editor;
            var gridCell = grid.getCell({ rowIndx: ui.rowIndx, colIndx: ui.colIndx });

            //次セル検索 & 移動
            var moveNext = () => {
                var next;

                if (vue.setMoveNextCell) {
                    var ret = vue.setMoveNextCell(grid, ui, reverse, key);
                    if (!ret) return ret;
                }

                var indices = grid.widget()
                    .find(grid.options.selectionModel.onTab == "nextEdit" ? ".pq-grid-cell.cell-editable" : ".pq-grid-cell")
                    .not("[id^='pq-sum-cell']")
                    .not(".cell-disabled")
                    .map((i, c) => grid.getCellIndices({$td: $(c)}))
                    .filter((i, ind) => _.isFunction(ind.column.editable) ? ind.column.editable(ind) : ind.column.editable)
                    ;
                indices = _.sortBy(indices, ["rowIndx", "colIndx"]);


                if (reverse || event.shiftKey) {
                    next = _.find(indices, v => v.rowIndx == ui.rowIndx  && v.colIndx < ui.colIndx)
                        || _.findLast(indices, v => v.rowIndx < ui.rowIndx)
                } else {
                    next = _.find(indices, v => v.rowIndx == ui.rowIndx  && v.colIndx > ui.colIndx)
                        || _.find(indices, v => v.rowIndx > ui.rowIndx)
                }

                if (next) {
                    if (editor) {
                        if (editor.css("display") == "none") {
                            //カスタムeditor
                            editor.trigger($.Event("keydown", {
                                keyCode: 9,
                                which: 9,
                                shiftKey: reverse,
                                isOnce: true,
                            }));
                            return false;
                        } else {
                            //標準editor
                            console.log("editor next cell", ui);
                            return true;
                        }
                    } else {
                        grid.setSelection({ rowIndx: next.rowIndx, colIndx: next.colIndx });
                    }
                    return false;

                } else if (!reverse && !event.shiftKey) {
                    //自動空行補完
                    var rows = vue.addEmptyRow(vue, grid, true);

                    //次セル検索 & 移動
                    if (rows) {
                        return moveNext();
                    } else {
                        if (editor) {
                            var row = {
                                rowIndx: ui.rowIndx,
                                newRow: {
                                    [ui.dataIndx]: ui.$editor.val(),
                                },
                            };
                            grid.updateRow(row);
                            grid.quitEditMode();
                            console.log("editor next cell", ui.rowIndx, row);

                            return false;
                        }
                    }
                }

                return true;
            };

            return moveNext();
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
                    action: function(){
                        vue.exportData("xlsx", true);
                    }
                },
                {
                    name: "ダウンロード",
                    icon: "fas fa-download fa-lg",
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
                                vue.addRow(ui.rowIndx + 1);
                            }
                        },
                        {
                            name: "行削除",
                            icon: "fas fa-minus-square fa-lg",
                            shortcut: "Ctrl - -",
                            action: function(){
                                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                                grid.deleteRow({ rowList: rowList });
                            }
                        },
                    ]
                );
            }

            return (this.BodyContextMenuEditCallback || ((v) => v))(menuItems);
        },
        exportData: function (format, isPrint, withRender) {
            var vue = this;
            var grid = vue.grid;

            if (isPrint) {
                var json = _(grid.pdata).concat(grid.options.summaryData).flatten().value()
                    .map(v => {
                        var base = _.reduce(grid.options.colModel, (acc, c) => { acc[c.dataIndx] = ""; return acc; }, {});
                        var ret = $.extend(true, base, v);
                        return ret;
                    });
                json = JSON.parse(JSON.stringify(json, (k, v) => v || ""));

                var colWidthSum = _.sumBy(grid.options.colModel.filter(c => !c.hidden), c => c.minWidth || c.width);

                var printOptions = {
                    type: grid.options.printOptions.printType || "json",
                    printable: grid.options.printOptions.printable || json,
                    properties: grid.options.colModel
                                    .filter(c => !c.hidden)
                                    .map(c => {
                                        return {
                                            field: c.dataIndx,
                                            displayName: c.title,
                                            columnSize: (((c.minWidth || c.width) / colWidthSum) * 100).toFixed(2) + "%",
                                        };
                                    }),
                    style: `
                            table {
                                table-layout: fixed;
                                margin-left: 0px !important;
                                margin-right: 0px !important;
                                width: 100% !important;
                            }
                            th, td {
                                font-family: "MS UI Gothic";
                                font-size: 10pt;
                                font-weight: normal !important;
                                border-color: black !important;
                                white-space: nowrap;
                                overflow: hidden;
                                margin: 0px;
                                padding-left: 3px;
                                padding-right: 3px;
                            }
                            th {
                                height: 22px !important;
                            }
                            td {
                                height: 22px !important;
                            }
                            `,
                };

                if (grid.options.printOptions.printHeader) printOptions.header = grid.options.printOptions.printHeader;
                if (grid.options.printOptions.printHeaderStyle) printOptions.headerStyle = grid.options.printOptions.printHeaderStyle;
                if (grid.options.printOptions.printGridHeaderStyle) printOptions.gridHeaderStyle = grid.options.printOptions.printGridHeaderStyle;
                if (grid.options.printOptions.printGridStyle) {
                    printOptions.gridStyle = grid.options.printOptions.printGridStyle;
                } else {
                    printOptions.gridStyle = 'border: 1px solid black;';
                }
                if (grid.options.printOptions.printStyles) {
                    printOptions.style += grid.options.printOptions.printStyles;
                }

                printJS(printOptions);

            } else {
                var colModel = _.cloneDeep(grid.options.colModel);

                grid.options.colModel.forEach(v => {
                    if (v.hiddenOnExport != undefined) {
                        v.hidden = v.hiddenOnExport;
                    }
                });

                var blob = grid.exportData(
                    {
                        format: format,
                        sheetName: vue.$parent.ScreenTitle,
                        render: !!withRender,
                    }
                );
                if (typeof blob === "string") {
                    blob = new Blob([blob]);
                }

                var fileName = vue.$parent.ScreenTitle
                            + "_" + moment().format("YYYYMMDDHHmmssSSS")
                            + "." + format.toLowerCase();

                saveAs(blob, fileName);

                grid.options.colModel = colModel;
                grid.refreshCM();
                grid.refresh();
            }
        },
        addEmptyRow: function(vue, grid, forced) {
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

                var actuals = _.sortBy(_.cloneDeep(grid.getData()), "pq_order")
                    // .reverse()
                    // .slice(0, empties)
                    .filter(d => {
                        if (vue.autoEmptyRowCheckFunc) {
                            return vue.autoEmptyRowCheckFunc(d);
                        } else {
                            return _(d).omitBy((v, k) => k.startsWith("pq") || !v).keys().value().length == 0;
                        }
                    })
                    .length
                    ;

                if (empties > actuals || forced) {
                    var cnt = forced ? (empties == actuals ? 0 : 1) : empties - actuals;
                    var rowList = _.fill(Array(cnt), {})
                                    .map((v, i) => {
                                        return {
                                            newRow: vue.autoEmptyRowFunc ? vue.autoEmptyRowFunc(grid) : {},
                                            rowIndx: grid.getData().length + i,
                                        };
                                    });

                    if(cnt == 0 ) return;

                    if (cnt == 1) {
                        grid.addRow({
                            rowIndx: rowList[0].rowIndx,
                            newRow: rowList[0].newRow,
                            checkEditable: false,
                        });
                    } else {
                        grid.addRow({
                            rowList: rowList,
                            checkEditable: false,
                        });
                    }

                    if (!!vue.onAfterAddAutoEmptyRowFunc) {
                        vue.onAfterAddAutoEmptyRowFunc(grid, rowList);
                    }

                    return rowList;
                }
            }

            return;
        },
        scrollBoth: _.debounce(function(val) {
            var vue = this;

            if (val != vue.prevScrollValue) {
                vue.prevScrollValue = val;

                vue.grid.scrollY(val);
                vue.gridRight.scrollY(val);
            }
        }, 0),
        addRow: function(rowIndx, newRow) {
            var vue = this;
            var grid = vue.grid;

            var newRow = newRow || (!!vue._onAddRowFunc ? vue._onAddRowFunc(grid, ui.rowData) : {});

            grid.addRow({
                rowIndx: rowIndx,
                newRow: newRow,
                checkEditable: false
            });

            grid.scrollRow({rowIndxPage: rowIndx});
        },
        setTitle: function(grid) {
            var vue = this;

            var empty = _.sortBy(_.cloneDeep(grid.getData()), "pq_order")
                .filter(d => {
                    if (vue.autoEmptyRowCheckFunc) {
                        return vue.autoEmptyRowCheckFunc(d);
                    } else {
                        return _(d).omitBy((v, k) => k.startsWith("pq") || !v).keys().value().length == 0;
                    }
                })
                .length
                ;

            var title = "";
            if (!grid.options.title || grid.options.title == "&nbsp;") {
                title = $("<div>")
                    .append(
                        $("<span>").addClass("counts")
                    )
                    .prop("outerHTML");
            } else {
                title = grid.widget().find(".pq-grid-title > div").prop("outerHTML");
            }

            var counts = "";
            if (!!grid.options.filterModel && !!grid.options.filterModel.rules && !!grid.options.filterModel.rules.length) {
                counts = "件数: "
                    + (grid.pdata.filter(v => !v.pq_hidden && v.pq_level == undefined).length - empty)
                    + " / "
                    + (grid.getData().length - empty)
                    + " [フィルタ中]";
            } else {
                counts = "件数: " + (grid.getData().length - empty);
            }
            var $title = $(title);
            $title.find(".counts").text(counts);
            grid.options.title = $title.prop("outerHTML");

            if (vue.setCustomTitle) {
                grid.options.title = vue.setCustomTitle(grid.options.title, grid);
            }
        },
        setNavigatorSelect: function(grid) {
            var vue = this;

            if (!!grid.pdata && !!grid.options.groupModel.on && !!grid.options.groupModel.dataIndx.length) {
                var groups = grid.options.groupModel.dataIndx
                    .map((v, i) => {
                        return {
                            pq_level: i,
                            dataIndx: v,
                        };
                    })
                    ;

                var title = "";
                if (!grid.options.title || grid.options.title == "&nbsp;") {
                    title = $("<div>")
                        .append(
                            $("<span>").addClass("counts")
                        )
                        .prop("outerHTML");
                } else {
                    title = grid.widget().find(".pq-grid-title > div").prop("outerHTML");
                }
                var $title = $(title);
                $title.children().remove("select");

                groups.forEach(v => {
                    var $sel = $("<select>")
                        .attr("id", grid.options.vue.id + "_gsel" + "_" + v.pq_level)
                        .attr("pq_level", v.pq_level)
                        .attr("dataIndx", v.dataIndx)
                        .css("margin-left", "10px")
                        ;

                    var data = grid.pdata
                        .filter(r => {
                            return !r.pq_gsummary
                                && r.pq_level == v.pq_level
                                && (
                                    v.pq_level == 0
                                    ||
                                    r.parentId == grid.pdata.filter(p => p.pq_level == v.pq_level - 1)[0].pq_gid
                                );
                        });

                    data.forEach((r, i) => {
                        var gid = !!r.parentId ? r.pq_gid.replace(r.parentId + "_", "") : r.pq_gid;
                        $sel.append(
                            $("<option>").prop("value", gid).text(gid).attr("pq_gid", r.pq_gid).attr("selected", i == 0)
                        );
                    });

                    $title.append($sel)
                    grid.options.title = $title.prop("outerHTML");

                });
                grid._refreshTitle();
                groups.forEach(v => {
                    $(document).on("change", "select#" + grid.options.vue.id + "_gsel" + "_" + v.pq_level, event => {
                            var $target = $(event.target);
                            var $option = $($target.find("option")[$target.prop("selectedIndex")]);
                            var pq_gid = $option.attr("pq_gid");

                            var rowIndx = grid.getRowIndx({ rowData: grid.pdata.find(r => r.pq_gid == pq_gid) }).rowIndx
                            grid.scrollRow({ rowIndx: rowIndx });

                            var setAndRefresh = $sel => {
                                $sel.find("option").attr("selected", false);
                                var selected = $($sel.find("option")[$sel.prop("selectedIndex")]);
                                selected.attr("selected", true);
                                var parentId = selected.attr("pq_gid");

                                var level = $sel.attr("pq_level") * 1;
                                var $next =  grid.widget().find("select#" + grid.options.vue.id + "_gsel" + "_" + (level + 1));
                                if (!!$next && !!$next.length) {
                                    $next.children().remove();

                                    var data = grid.pdata.filter(r => !r.pq_gsummary && r.parentId == parentId);
                                    data.forEach((r, i) => {
                                        var gid = !!r.parentId ? r.pq_gid.replace(r.parentId + "_", "") : r.pq_gid;
                                        $next.append(
                                            $("<option>").prop("value", gid).text(gid).attr("pq_gid", r.pq_gid).attr("selected", i == 0)
                                        );
                                    });

                                    setAndRefresh($next);
                                }
                            }

                            setAndRefresh($target);
                        });
                });
                console.log("refresh navigator select")
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

    .pq-grid-title {
        display: flex;
        height: 30px;
        align-items: baseline;
        font-weight: bold;
        padding-bottom: 0 !important;
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
    /* 変更行No.セル */
    .changed-col-row {
        background-color: yellow !important;
    }

    textarea.pq-cell-editor {
        margin-top: 8px;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    /* 入力不可セル */
    .table_editable .pq-grid-cell.cell-readonly,
    .pq-grid-cell.cell-readonly-force
    {
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
        display: inline-flex;
    }
    .tooltip.text-overflow {
        /* max-width: 30vw !important; */
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
    .pq-grid .pq-state-select.pq-grid-row .pq-grid-cell{
        background: steelblue;
        color: white;
    }
    .pq-grid .pq-state-select.pq-grid-row .pq-grid-number-cell {
        background: black !important;
        color: orange;
    }
    svg.pq-grid-overlay
    {
        display: none;
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

    .pq-grid-cell.cell-disabled {
        color: darkgrey !important;
        background-color: darkgrey !important;
        border-color: darkgrey !important;
    }

    /* 合計行 */
    .pq-summary-outer *:not(.tooltip):not(.arrow):not(.tooltip-inner):not(.pq-grid-cell.cell-disabled) {
        font-weight: bold;
        color: black;
        background-color: lightpink !important;
    }

    /* ヘッダ行 */
    .pq-grid-col {
        display: flex;
        align-items: center;
    }

    /* 休日列 */
    .pq-grid-col.holiday_col {
        color: red;
        background-color: lightgray;
    }
    .pq-grid-cell.holiday_col {
        background-color: lightgray;
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
        justify-content: space-evenly;
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

    .hasRight .pq-body-outer .pq-cont-inner.pq-cont-right::-webkit-scrollbar:vertical {
        display: none;
    }

    .grid-right {
        position: absolute;
        right: 18px;
    }

    .grid-right .pq-body-outer .pq-cont-inner.pq-cont-right {
        overflow-x: hidden !important;
    }

    .grid-right.scrollable-x .pq-body-outer .pq-cont-inner.pq-cont-right {
        overflow-x: scroll !important;
    }

    .grid-right .pq-grid-norows {
        display: none !important;
    }

    .pq-drag-handle {
        display: contents !important;
        width: 100px !important;
        height: 100% !important;
        left: 0px !important;
        background-image: none !important;
        background-color: red !important;
        cursor: grab !important;
    }
    .pq-drag-handle::before {
        font-family: "Font Awesome 5 Free";
        content: "\f7a5";
        cursor: grab !important;
    }

    .blinking {
        animation: blink-animation 1s infinite;
    }

    @-webkit-keyframes blink-animation {
        0%, 49% {
            background-color: rgb(117,209,63);
            border: 3px solid #e50000;
        }
        50%, 100% {
            background-color: #e50000;
            border: 3px solid rgb(117,209,63);
        }
    }

    .select-cell,
    .autocomplete-cell
    {
        padding-left: 20px !important;
    }
    .select-cell:before,
    .autocomplete-cell:before
    {
	    width: 20px;
	    height:20px;
	    position: absolute;
	    left: 5px;
        font-family: "Font Awesome 5 Free";
        content: "\f0d7";
    }

    .pq-align-right.select-cell:before,
    .pq-align-right.autocomplete-cell:before
    {
	    left: 0px;
    }

    .pq-editor-inner input[type="number"]::-webkit-outer-spin-button,
    .pq-editor-inner input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .pq-editor-inner .DatePickerWrapper {
        margin-left: 0px !important;
        height: 26px;
    }
    .pq-editor-inner .DatePickerWrapper .target-input {
        width: 100px;
    }
</style>

<style scoped>
.Floating {
    float: left;
}
</style>
