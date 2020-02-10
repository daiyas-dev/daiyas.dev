<template>
    <div
        class="form-group d-inline-flex align-items-center mb-0"
        :class='"PopupSelect " + id '
        :style='width ? ("width: " + width + "px") : ""'
    >
        <span v-if="label" class="text-nowrap d-flex align-items-center mr-1" :for="'btn' + id">{{label}}</span>
		<input
            type="text"
            :id="id"
            :ref="id"
            :class="[
                'form-control',
                'target-input',
                editable == true ? 'editable' : 'readonly',
                readOnly == true ? 'readOnly' : '',
                hideSearchButton && hideClearButton && !enablePrevNext ? 'mr-1' : '',
            ]"
            :style='inputWidth ? ("width: " + inputWidth + "px") : ""'
            v-model="vmodel[bind]"
            :readonly="this.editable == false"
            autocomplete="off"
            v-on=inputListeners
            :disabled=isDisabled
        >
        <button type="button"
            v-if=!hideSearchButton
            :class="[
                'selector-button',
                'btn',
                'btn-info',
                'p-0',
                'border-0',
                readOnly == true ? 'd-none' : ''
            ]"
            :id="'btn' + id"
            @click="showList"
            :disabled=isDisabled
            :tabIndex="hasButtonFocus ? 0 : -1"
        >
            <i class="fa fa-search fa-lg"></i>
        </button>
        <button type="button"
            v-if="enablePrevNext"
            :class="[
                'prev-button',
                'btn',
                'btn-info',
                'p-0',
                'border-0',
                readOnly == true ? 'd-none' : ''
            ]"
            :id="'btn_prev' + id"
            @click="prevList"
            :disabled=isDisabled
            :tabIndex="hasButtonFocus ? 0 : -1"
        >
            <i class="fa fa-caret-left fa-lg"></i>
        </button>
        <button type="button"
            v-if="enablePrevNext"
            :class="[
                'next-button',
                'btn',
                'btn-info',
                'p-0',
                'border-0',
                readOnly == true ? 'd-none' : ''
            ]"
            :id="'btn_next' + id"
            @click="nextList"
            :disabled=!isNextEnabled
            :tabIndex="hasButtonFocus ? 0 : -1"
        >
            <i class="fa fa-caret-right fa-lg"></i>
        </button>
        <button type="button"
            v-if=!hideClearButton
            :class="[
                'clear-button',
                'btn',
                'btn-info',
                'p-0',
                'border-0',
                readOnly == true ? 'd-none' : ''
            ]"
            :id="'btn' + id + 'Clear'"
            @click="clearValue"
            :disabled=isDisabled
            :tabIndex="hasButtonFocus ? 0 : -1"
        >
            <i class="fa fa-times fa-lg"></i>
        </button>
        <label v-if="isShowNameLabel == true">{{nameLabel}}</label>
		<input v-if="isShowName == true"
            type="text"
            :class="[
                'form-control',
                'select-name',
                'label-blue',
                readOnly == true ? 'readOnly' : ''
            ]"
            :disabled="!isNameEditable"
            :value="selectName"
            :style='nameWidth ? ("width: " + nameWidth + "px") : ""'
        >
    </div>
</template>

<style scoped>
.PopupSelect label:first {
    width: auto;
}
.PopupSelect .target-input {
    font-size: 15px;
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.PopupSelect .target-input.readOnly {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}
.PopupSelect .btn {
    box-shadow: none;
}
.PopupSelect .clear-button,
.PopupSelect .prev-button,
.PopupSelect .next-button,
.PopupSelect .selector-button {
    width: 35px !important;
    border-left-width: 0px !important;
    display: flex;
    justify-content: center;
    align-items: center;
}
.PopupSelect .selector-button,
.PopupSelect .prev-button,
.PopupSelect .next-button {
    border-radius: 0px;
}
.PopupSelect .prev-button,
.PopupSelect .next-button {
    width: 30px !important;
    background-color:forestgreen;
    border-color: forestgreen;
}
.PopupSelect .clear-button {
    background-color: red;
    border-color: red;
    border-radius: 0px;
}
.PopupSelect .clear-button:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}
.PopupSelect .select-name {
    font-size: 15px;
    border-left-width: 0px;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
}
.PopupSelect .select-name.readOnly {
    border-left-width: 1px;
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}
.readonly {
    background-color: white;
}
</style>
<style>
.ui-autocomplete {
    max-height: 30vh;
    overflow-y: scroll;
    overflow-x: hidden;
    padding-right: 0px;
}
</style>

<script>
export default {
    name: "PopupSelect",
    data() {
        return {
            selectValue: "",
            selectValuePrev: "",
            selectRow: {},
            dataList: [],
            autoCompleteKey: null,
            autoCompleteList: [],
            selectName: "",
            noMsg: false,
            isValid: false,
            isUnique: false,
            CountConstraint: null,
            ActualCounts: null,
            searchParams: null,
            dataUrlPrev: null,
            paramsPrev: null,
            errorMsg: null,
            AutoCompleteFocusSkip: false,
            isLoading: true,
            AutoCompleteListMaxDefault: 100,
        }
    },
    props: {
        id: String,
        label: String,
        vmodel: Object,
        bind: String,
        buddy: String,
        buddies: Object,
        dataUrl: String,
        params: Object,
        embedded: Boolean,
        container: Object,
        width: Number,
        inputWidth: Number,
        title: String,
        labelCd: String,
        labelCdNm: String,
        popupWidth: Number,
        popupHeight: Number,
        isModal: Boolean,
        editable: Boolean,
        readOnly: Boolean,
        reuse: Boolean,
        existsCheck: Boolean,
        exceptCheck: Array,
        isGetName: Boolean,
        isCodeOnly: Boolean,
        showColumns: Array,
        onChangeFunc: Function,
        onAfterChangedFunc: Function,
        index: Number,
        isShowName: Boolean,
        isShowNameLabel: Boolean,
        isNameEditable: Boolean,
        nameWidth: Number,
        showTextFunc: String,
        isPreload: Boolean,
        onBeforeFunc: Function,
        isTrim: Boolean,
        hasButtonFocus: Boolean,
        isShowAutoComplete: Boolean,
        AutoCompleteFunc: Function,
        AutoCompleteMinLength: Number,
        AutoCompleteNoLimit: Boolean,
        AutoCompleteListMax: Number,
        ParamsChangedCheckFunc: Function,
        enablePrevNext: Boolean,
        SelectorParamsFunc: Function,
        disabled: Boolean,
        hideSearchButton: Boolean,
        hideClearButton: Boolean,
    },
    computed: {
        showText: function() {
            var comp = this;
            return !!showTextFunc ? eval(showTextFunc(comp)) : this.selectValue;
        },
        nameLabel: function() {
            return this.isGetName ? (this.labelCd || this.label || "コード") : (this.labelCdNm || "名称");
        },
        isError: function() {
            return !!this.selectValue && Object.keys(this.selectRow).length == 0;
        },
        $input: function() {
            return $(this.$el).find(".target-input")[0];
        },
        inputListeners: function () {
            var comp = this

            var ev = {};
            ev.change = comp.onChange;
            if (comp.editable == false && comp.readOnly != true) {
                ev.click = comp.showList;
            }

            return Object.assign(
                {},
                this.$listeners,
                ev,
            )
        },
        isDisabled: function() {
            var vue = this;
            return vue.isPreload ? (vue.disabled || vue.isLoading) : vue.disabled;
        },
        isPrevEnabled: function() {
            var vue = this;
            var ret = !!vue.dataList
                && !!vue.dataList.length
                && !_.isEmpty(vue.selectRow)
                && vue.dataList.indexOf(vue.selectRow) != 0;
            //console.log("isPrevEnabled:" + ret);
            return ret;
        },
        isNextEnabled: function() {
            var vue = this;
            var ret = !!vue.dataList
                && !!vue.dataList.length
                &&
                (
                    _.isEmpty(vue.selectRow)
                    ||
                    vue.dataList.indexOf(vue.selectRow) != vue.dataList.length - 1
                );
            //console.log("isNextEnabled:" + ret);
            return ret;
        },
    },
    watch: {
        //TODO: 大量データ対応の一環として停止(動的にdataUrlを変更する処理が無いため)
        //dataUrl: {
        //    sync: true,
        //    handler: function(newVal, oldVal) {
        //        if (!_.isEqual(newVal, this.dataUrlPrev)) {
        //            this.dataUrlPrev = newVal;
        //            this.getDataList();
        //        }
        //    },
        //},
        params: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                // console.log(vue.id + " PopupSelect params watch", newVal, vue.paramsPrev);

                if (!_.isEqual(newVal, vue.paramsPrev, (v, o) => v == o)) {
                    if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(newVal, vue.paramsPrev, vue)) {
                        return;
                    }


                    vue.paramsPrev = _.cloneDeep(newVal);
                    vue.getDataList(newVal, (res) => {
                        vue.setSelectValue(vue.vmodel[vue.bind], true, false);
                        if (!!vue.vmodel[vue.bind] && !vue.isValid) {
                            if (vue.isShowAutoComplete) {
                                if (!$(vue.$el).find("#" + vue.id).autocomplete("widget").is(":visible")) {
                                    $(vue.$el).find("#" + vue.id).autocomplete("search");
                                }
                            }
                        }
                    });
                }
            },
        },
        vmodel : {
            deep: true,
            sync: true,
            handler: function(newVal, oldVal) {
                var vue = this;
                var value = _.cloneDeep(vue.vmodel[vue.bind]);

                if (!_.isEqual(_.trim(vue.selectValue), _.trim(value))) {
                    // if (vue.isShowAutoComplete) {
                    //     if (!vue.showAutoComplete(newVal)) {
                    //         return;
                    //     }
                    // }
                    // if (!vue.isShowAutoComplete) {
                    //     vue.setSelectValue(value, true);
                    // }
                    if (vue.isShowAutoComplete && !!vue.selectValue && vue.isUnique) {
                        if ($(vue.$el).find("#" + vue.id).autocomplete("widget").is(":visible")) {
                            return;
                        }
                    }

                    vue.setSelectValue(value, true);
                }
            },
        },
        selectValue: {
            sync: true,
            handler: function(newVal) {
                var vue = this;
                var value = _.cloneDeep(newVal);

                if (vue.isDebounce == false) return;

                vue.setSelectValue(value, vue.noMsg);
                vue.noMsg = false;
                vue.isDebounce = true;
            },
        },
    },
    created: function () {
        var vue = this;

        vue.$root.$on("plantChanged", vue.plantChanged);
        vue.$root.$on("accountChanged", vue.accountChanged);

        if (vue.isPreload) {
            console.log(vue.id + " create preload", vue.params);
            if (!!vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(vue.params, vue.paramsPrev, vue)) {
                return;
            }

            vue.dataList = null;
            // $(vue.$el).children().prop("disabled", true);

            vue.paramsPrev = _.cloneDeep(vue.params);
            vue.getDataList(vue.params, (res) => {
                $(vue.$el).children().not(".select-name").prop("disabled", vue.isDisabled);
                $(vue.$el).find(".select-name").prop("disabled", vue.isDisabled || !vue.isNameEditable);
                if (vue.vmodel[vue.bind]) {
                    vue.setSelectValue(vue.vmodel[vue.bind], true);
                }
            });
        } else {
            if (vue.vmodel[vue.bind]) {
                vue.setSelectValue(vue.vmodel[vue.bind], true);
            }
        }
    },
    mounted: function () {
        // console.log("PopupSelect mounted", this.id);
    },
    beforeUpdated: function () {
    },
    updated: function () {
    },
    activated: function () {
    },
    deactivated: function () {
    },
    destroyed: function () {
    },
    methods: {
        accountChanged: function() {
            var vue = this;
            // console.log("PopupSelect accountChanged");

            if (vue.isPreload) {
                console.log(vue.id + " accountChanged", vue.params);
                if (!!vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(vue.params, vue.paramsPrev, vue)) {
                    return;
                }

                vue.dataList = null;
                // $(vue.$el).children().prop("disabled", true);

                vue.getDataList(vue.params, (res) => {
                    $(vue.$el).children().not(".select-name").prop("disabled", vue.isDisabled);
                    $(vue.$el).find(".select-name").prop("disabled", vue.isDisabled || !vue.isNameEditable);
                    if (vue.vmodel[vue.bind]) {
                        vue.setSelectValue(vue.vmodel[vue.bind], true);
                    }
                });
            } else {
                if (vue.vmodel[vue.bind]) {
                    vue.setSelectValue(vue.vmodel[vue.bind], true);
                }
            }
        },
        clearValue: function() {
            var vue = this;

            $(vue.$el).find("#" + vue.id).val("");

            vue.selectValue = "";
            vue.selectName = "";
            vue.selectRow = {};

            if (vue.onChangeFunc) {
                //変更時functionが指定されている場合は呼び出す
                var ret = vue.onChangeFunc($(vue.$el).find("#" + vue.id)[0], vue.selectRow, vue, true, false);

                //戻り値がfalseの場合、処理中断
                if (ret == false) {
                    vue.noMsg = false;
                    return;
                }
            }

            if (!!vue.vmodel && !!vue.bind) {
                vue.vmodel[vue.bind] = "";
                if (vue.buddy) {
                    vue.vmodel[vue.buddy] = "";
                }
                if (!!vue.buddies) {
                    _.forIn(vue.buddies, (v, k) => vue.vmodel[k] = "");
                }
            }

            if (vue.onAfterChangedFunc) {
                vue.onAfterChangedFunc("");
            }
        },
        getDataList: function(params, callback) {
            var vue = this;

            if (vue.isShowAutoComplete) {
                vue.disableAutoComplete();
            }

            params = _.cloneDeep(params || vue.params || {});

            if (_.trim(vue.selectValue)) {
                params.selectValue = vue.selectValue;

                var key = vue.selectValue + "";
                params.KeyWord = key.includes("*") ? key.replace(/\*/g) : key;
            }

            vue.searchParams = _.cloneDeep(params);

            axios.post(vue.dataUrl, params)
                .then(response => {
                    var res = response.data;

                    if (!!params && !vue.isShowAutoComplete && !_.isEqual($.trim(params.selectValue), $.trim(vue.selectValue))) {
                        // console.log("PopupSelect already value chenged:" + params.selectValue + " -> " + vue.selectValue);
                        return;
                    }

                    if (!!res && (res.onError || res.onException)) {
                        //エラーダイアログ
                        $.dialogErr({
                            title: res.message,
                            contents: res.errors,
                        });
                        console.log(res);

                        return;
                    } else if (!!res.CountConstraint) {
                        //件数制約違反設定
                        vue.CountConstraint = res.CountConstraint * 1;
                        vue.ActualCounts = res.ActualCounts * 1;

                        res = res.Data;
                    } else if (res.Data) {
                        vue.CountConstraint = null;
                        res = res.Data;
                    } else {
                        vue.CountConstraint = null;
                    }

                    //データリスト保持
                    if (!!res.length) {
                        vue.dataList = res;
                    }
                    // console.log(vue.id + " get dataList");
                    // console.log(vue.dataList);

                    vue.isLoading = false;

                    if (vue.isShowAutoComplete) {
                        vue.autoCompleteKey = null;
                        vue.autoCompleteList = [];
                        vue.setAutoComplete();
                    }

                    //callback実行
                    if (callback) {
                        callback(res);
                    }
                })
                .catch(error => {
                    if (!!params && !!params.selectValue && !_.isEqual(params.Cd, vue.selectValue)) {
                        console.log("PopupSelect already value chenged(Error):" + params.selectValue + " -> " + vue.selectValue);
                        console.log(error);
                        return;
                    }

                    //他コンポーネントに通知
                    vue.$root.$emit("addMessage", vue.dataUrl + "で例外発生" + JSON.stringify(params));

                    //エラーダイアログ
                    var target = vue.labelCd || vue.label || "コード";
                    $.dialogErr({
                        title: target + "一覧取得失敗",
                        contents: error.message
                    });
                    console.log(error);
                });
        },
        showList: function() {
            var vue = this;

            //PqGrid表示時の選択状態復元callback
            var callback = function(grid) {
                if (grid) {
                    //gridのloading待ちpromise
                    new Promise((resolve, reject) => {
                        var timer = setInterval(function() {
                            if (!!grid && !grid.options.loading) {
                                //interval解除
                                clearInterval(timer);

                                //wrapperIdのdiv設定が完了したら、resolve
                                return resolve(grid);
                            }
                        }, 10);
                    })
                    .then((grid) => {
                        if (vue.selectValue || _.keys(vue.selectRow).length) {
                            var rowIndx = grid.getRowIndx({ rowData: vue.selectRow }).rowIndx
                                || grid.getRowIndx({ rowData: grid.pdata.filter(v => v[vue.isGetName ? "CdNm" : "Cd"] == vue.selectValue)[0] }).rowIndx
                                || 0;

                            grid.scrollRow({ rowIndx: rowIndx });
                            grid.setSelection({ rowIndx: rowIndx });
                        } else {
                            if (_.keys(grid.getSelectionData()).length == 0) {
                                grid.scrollRow({ rowIndx: 0 });
                            }
                        }
                        grid.widget().css("visibility", "visible");
                    });
                }
            };

            var showSelector = function(dataUrl, params) {
                PageDialog.showSelector({
                    dataUrl: dataUrl,
                    params: params,
                    title: vue.title || (vue.label + "一覧"),
                    labelCd: vue.labelCd || vue.label || "コード",
                    labelCdNm: vue.labelCdNm || "名称",
                    isModal: vue.isModal,
                    isCodeOnly: vue.isCodeOnly,
                    showColumns: vue.showColumns,
                    width: vue.popupWidth || null,
                    height: vue.popupHeight || null,
                    reuse: vue.reuse,
                    callback: callback,
                    buttons: [
                        {
                            text: "選択",
                            class: "btn btn-primary",
                            shortcut: "Enter",
                            target: (params || {}).target,
                            click: function(gridVue, grid) {

                                var selection = grid.Selection().getSelection();
                                if (selection.length > 0) {
                                    var rowIndx = selection[0].rowIndx;
                                    var rowData = grid.getRowData({ rowIndx: rowIndx });

                                    //コード及び名称の指定された値を取得
                                    var value = rowData[vue.isGetName ? "CdNm" : "Cd"];
                                    var name  = rowData[vue.isGetName ? "Cd" : "CdNm"];

                                    //画面項目に設定
                                    if (vue.vmodel && vue.bind) {
                                        vue.vmodel[vue.bind] = value;
                                        if (vue.buddy) {
                                            vue.vmodel[vue.buddy] = name;
                                        }
                                        if (!!vue.buddies) {
                                            _.forIn(vue.buddies, (v, k) => vue.vmodel[k] = rowData[v]);
                                        }
                                    }
                                    if (this.target) {
                                        this.target.val(value);
                                    }

                                    //保持データに設定
                                    vue.selectValue = value;
                                    vue.selectName = name;
                                    vue.selectRow = rowData;
                                    vue.dataList = grid.getData();
                                }
                            },
                        },
                    ],
                });
            }

            if (vue.onBeforeFunc) {
                vue.onBeforeFunc((res) => {
                    var newParams = _.cloneDeep(vue.params) || {};
                    newParams = $.extend(true, newParams, res);
                    showSelector(vue.dataUrl, newParams);
                });
            } else {
                var params = _.cloneDeep(vue.params);

                if (vue.SelectorParamsFunc) {
                    params = vue.SelectorParamsFunc(params, vue);
                }

                showSelector(vue.dataUrl, params);
            }
        },
        onChange: function(event) {
            var vue = this;
            vue.selectValue = event.target.value;
        },
        setSelectValue: function(newVal, noMsg, isDebounce) {
            var vue = this;
            console.log("ps setSelectValue", newVal);

            if (isDebounce != false) {
                vue.setSelectValueByDebounce(newVal, noMsg);
            } else {
                vue.execSetSelectValue(newVal, noMsg, false);
            }
        },
        setSelectValueByDebounce: _.debounce(function(newVal, noMsg) {
            var vue = this;

            vue.execSetSelectValue(newVal, noMsg, true);
        }, 50),
        execSetSelectValue: function(newVal, noMsg, isDebounce) {
            var vue = this;

            //componentに保持していない場合は設定し、selectValueのwatcherに移譲
            if (vue.selectValue != newVal && isDebounce != false) {
                vue.noMsg = noMsg;
                vue.isDebounce = isDebounce;
                vue.selectValue = newVal;
                return;
            } else {
                vue.noMsg = noMsg;
                vue.isDebounce = isDebounce;
                vue.selectValue = newVal;
            }

            //elementに設定
            $(vue.$el).find("#" + vue.id).val(newVal);

            //値設定関数object
            var setValue = function() {
                try {

                    var rowData = vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);
                    if (!rowData && vue.isShowAutoComplete && vue.autoCompleteList.length == 1) {
                        rowData = vue.autoCompleteList[0];
                    }

                    //入力有り、かつチェック指定されている場合は、存在チェック
                    vue.isValid = checkValue(!_.isEmpty($.trim(vue.selectValue)) && vue.existsCheck);
                    vue.isUnique = !!rowData;

                    //選択行データに設定
                    vue.selectName = !rowData ? "" : rowData[vue.isGetName ? "Cd" : "CdNm"];
                    vue.selectRow = !rowData ? {} : rowData;

                    if (vue.onChangeFunc) {
                        //変更時functionが指定されている場合は呼び出す
                        var ret = vue.onChangeFunc($(vue.$el).find("#" + vue.id)[0], vue.selectRow, vue, noMsg, vue.isValid);

                        //戻り値がfalseの場合、処理中断
                        if (ret == false) {
                            vue.noMsg = false;
                            return;
                        }
                    }

                    //bindingが指定されている場合、反映
                    if (!!vue.vmodel && !!vue.bind) {
                        var parent = vue.$parent;

                        if (parent) {
                            parent.$set(vue.vmodel, vue.bind, newVal);
                            if (vue.buddy) {
                                parent.$set(vue.vmodel, vue.buddy, vue.selectName);
                            }
                            if (!!vue.buddies) {
                                _.forIn(vue.buddies, (v, k) => parent.$set(vue.vmodel, k, vue.selectRow[v]));
                            }
                            parent.$forceUpdate();
                        } else {
                            vue.vmodel[vue.bind] = newVal;
                            if (vue.buddy) {
                                vue.vmodel[vue.buddy] = vue.selectName;
                            }
                            if (!!vue.buddies) {
                                _.forIn(vue.buddies, (v, k) => vue.vmodel[k] = vue.selectRow[v]);
                            }
                        }
                    }

                    if (vue.onAfterChangedFunc) {
                        vue.onAfterChangedFunc(newVal, vue.selectRow, vue);
                    }

                } catch(ex) {
                    console.log("PopupSelect setValue Exception", ex);
                }
            }

            //値チェック関数object
            var checkValue = function(check) {
                //対象外指定されている場合
                if (!!vue.exceptCheck && vue.exceptCheck.some(v => _.keys(v).some(k => v[k] == newVal))) {
                    //エラー項目設定解除
                    var $container = vue.embedded ? $(vue.$el).parent() : $(vue.$el);
                    var $target = vue.embedded ? $(vue.$el).parent() : $(vue.$el).find("#" + vue.id);

                    $(vue.$el)
                        .removeClass("has-error")
                        .find("#" + vue.id).tooltip("dispose");

                    vue.errorMsg = null;

                    return true;
                }

                //検索結果から、現在の値を含むものを抽出
                var rowData = vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);
                if (!rowData && vue.isShowAutoComplete && vue.autoCompleteList.length == 1) {
                    rowData = vue.autoCompleteList[0];
                }
                console.log("ps check", newVal, vue.dataList);

                if (!rowData && check) {
                    //現在の値を含むものが無い場合、エラーとする
                    console.log("ps check error", newVal, vue.dataList);

                    vue.errorMsg = vue.isShowAutoComplete && !!rowData && vue.autoCompleteList.length > 1
                        ? ((vue.title || (vue.label + "一覧")) + "で複数該当します")
                        : ((vue.title || (vue.label + "一覧")) + "に存在しません");

                    //エラー項目設定
                    $(vue.$el)
                        .addClass("has-error")
                        .find("#" + vue.id).tooltip({
                            animation: false,
                            placement: "auto",
                            trigger: "hover",
                            title: vue.errorMsg,
                            container: "body",
                            template: '<div class="tooltip has-error" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                        });

                    //エラーダイアログ
                    if (!noMsg) {
                        $.dialogErr({
                            title: (vue.title || (vue.label + "一覧")) + "存在チェック",
                            contents: vue.errorMsg,
                            closeFunc: () => vue.$input.focus(),
                        });
                    }

                    //AutoComplete
                    if (vue.isShowAutoComplete) {
                        if (!vue.CountConstraint || vue.dataList.length == 1) {
                            if (!$(vue.$el).find("#" + vue.id).autocomplete("widget").is(":visible")) {
                                $(vue.$el).find("#" + vue.id).autocomplete("search");
                            }
                        }
                    }
                } else {
                    //エラー項目設定解除
                    var $container = vue.embedded ? $(vue.$el).parent() : $(vue.$el);
                    var $target = vue.embedded ? $(vue.$el).parent() : $(vue.$el).find("#" + vue.id);

                    $(vue.$el)
                        .removeClass("has-error")
                        .find("#" + vue.id).tooltip("dispose");

                    vue.errorMsg = null;
                }

                return !!rowData;
            };

            //Popupから選択した場合は、元データが保持されているので現在の値と比較
            if (newVal && newVal != vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) {
                //表示項目名
                var target = vue.labelCd || vue.label || "コード";

                //検索結果から、現在の値を含むものを抽出
                var rowData = !!vue.dataList ? vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]) : null;
                if (!rowData && vue.isShowAutoComplete && vue.autoCompleteList.length == 1) {
                    rowData = vue.autoCompleteList[0];
                }

                //該当するdataListがある場合、それを使用
                if (!!vue.dataList && vue.dataList.length && !!rowData) {
                    //値設定callback実行
                    setValue();
                } else if (vue.isShowAutoComplete) {
                    setValue();
                } else if (vue.isPreload) {
                    //事前読込時
                    if (vue.dataList == null) {
                        //データ取得待ちpromise
                        new Promise((resolve, reject) => {
                            var timer = setInterval(function() {
                                if (vue.dataList != null) {
                                    //interval解除
                                    clearInterval(timer);

                                    return resolve();
                                }
                            }, 10);
                        })
                        .then(() => {
                            setValue();
                        });
                    } else {
                        setValue();
                    }
                } else {
                    //dataListが無い場合は再検索
                    var params = _.cloneDeep(vue.params) || {};
                    params[vue.bind] = newVal;
                    params.KeyWord = newVal;
                    vue.getDataList(params, (res) => setValue());
                }
            } else {
                //値未設定の場合は、そのままcallback実行
                setValue();
            }
        },
        disableAutoComplete: function() {
            var vue = this;
            var input = $(vue.$el).find("#" + vue.id);
            if (input.data("ui-autocomplete")) {
                input.autocomplete("disable");
            }
        },
        setAutoComplete: function() {
            var vue = this;
            var input = $(vue.$el).find("#" + vue.id);

            input.autocomplete({
                source: (request, response) => {
                    var makeList = () => {
                        var list = vue.getAutoCompleteList(request.term);

                        var max = vue.AutoCompleteListMax || vue.AutoCompleteListMaxDefault;
                        if (!vue.AutoCompleteNoLimit && (!!vue.CountConstraint || list.length > max)) {
                            var len = vue.ActualCounts || list.length;
                            list = _.slice(list, 0, max - 1);
                            var msg = "先頭" + max + "件のみ表示しています(" + len + "件中)"
                                    + "\r\n"
                                    + "絞り込み条件を追加して下さい";
                            console.log("ps constraint", vue.CountConstraint, vue.ActualCounts, vue.dataList.length, max, len);
                            $(vue.$el)
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
                                .tooltip("show");
                        } else {
                            $(vue.$el).find("#" + vue.id).tooltip("dispose");
                        }

                        return list;
                    }

                    if (!!vue.CountConstraint) {
                        var params = {
                            KeyWord: request.term,
                        };
                        if (!vue.isUnique && (params.KeyWord != vue.searchParams.KeyWord)) {
                            console.log("Autocomplte New KeyWord", params.KeyWord);
                            vue.getDataList(params, res => {
                                // console.log("Autocomplte New KeyWord", params.KeyWord, vue.dataList.length, vue.ActualCounts);
                                response(makeList());
                            });
                        } else {
                            // console.log("Autocomplte Same KeyWord", params.KeyWord, vue.dataList.length, vue.ActualCounts);
                            return response(makeList());
                        }
                    } else {
                        return response(makeList());
                    }
                },
                appendTo: $(vue.$el).closest(".ui-dialog, body"),
                select : function(event, ui) {
                    console.log("autocomplete select:" + input.val());
                    //選択した値を設定
                    vue.selectRow = ui.item;
                    vue.execSetSelectValue(ui.item.value, true, false);

                    if (!!vue.onGrid && !!vue.grid) {
                        $(vue.$el).trigger($.Event("keyup", {
                            keyCode: 9,
                            which: 9,
                            isOnce: true,
                        }));
                    }

                    return false;
                },
                close: function(event, ui) {
                    // console.log("autocomplete close", event, ui);
                    if (!$(vue.$el).hasClass("has-error")) {
                        input.tooltip("dispose");
                    }

                    if (!!vue.onGrid && input.val() != vue.selectValue) {
                        vue.execSetSelectValue(input.val(), true, false);
                    }
                },
                position: { my: "left top", at: "left bottom", collision: "flip" },
                autoFocus: false,
                delay: 200,
                minLength: vue.AutoCompleteMinLength || 0,
            })
            .focus(function(ev) {
                // console.log("autocomplete focus:" + input.val() + " skip:" + vue.AutoCompleteFocusSkip);
                if (vue.AutoCompleteFocusSkip) {
                    vue.AutoCompleteFocusSkip = false;
                } else {
                    if (!input.autocomplete("widget").is(":visible")) {
                        console.log(vue.id + " autocomplete search", "focus");
                        input.autocomplete("search", input.val());
                    }
                }
            })
            .click(function(ev) {
                // console.log("autocomplete click:" + input.val());
                input.focus();
            })
            .keydown(function(ev) {
                switch (ev.which) {
                    case 13:
                        input.autocomplete("close");
                        if (vue.onGrid) {
                            return true;
                        } else {
                            var focusables = input.closest("form").find(":input:enabled:focusable").not("[tabindex=-1]");
                            var curIdx = focusables.index(input);
                            var nextIdx = curIdx + (ev.ctrlKey ? -1 : 1);
                            if (!!focusables[nextIdx]) {
                                focusables[nextIdx].focus();
                            }
                            return false;
                        }
                    case 9:
                        input.autocomplete("close");
                        return true;
                    default:
                        return true;
                }
            })
            .change(function() {
                //console.log("autocomplete change:" + input.val() + "/" + vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]);

                if (input.val() == vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) return;

                input.autocomplete("close");
                // vue.execSetSelectValue(vue.autoCompleteList.length == 1 ? vue.autoCompleteList[0].Cd : input.val(), true, false);
            });
            input.autocomplete("enable");
        },
        getAutoCompleteList: function(key) {
            var vue = this;

            if (key == vue.autoCompleteKey && !vue.autoCompleteList) {
                // console.log("getAutoCompleteList: same key " + key);
                return vue.autoCompleteList;
            }

            var match = vue.dataList.filter(v => v[vue.isGetName ? "CdNm" : "Cd"] == key);
            var list = vue.AutoCompleteFunc ? vue.AutoCompleteFunc(key, vue.dataList, vue)
                : vue.dataList
                    .filter(v => v[vue.isGetName ? "CdNm" : "Cd"].includes(key))
                    .map(v => {
                        var ret = v;
                        ret.value = v[vue.isGetName ? "CdNm" : "Cd"];
                        ret.text = v[!vue.isGetName ? "CdNm" : "Cd"];
                        ret.label = ret.value + " : " + ret.text;
                        return ret;
                    })
                    ;

            // console.log("getAutoCompleteList:" + key + " = " + list.length + "[" + match.length + "]");

            vue.autoCompleteKey = key;
            vue.autoCompleteList = list;

            return list;
        },
        prevList: function() {
            var vue = this;
            var prev = vue.dataList[vue.dataList.indexOf(vue.selectRow) - 1];
            vue.setSelectValue(prev[vue.isGetName ? "CdNm" : "Cd"], true)
        },
        nextList: function() {
            var vue = this;
            var next = vue.dataList[!vue.selectRow ? 0 : (vue.dataList.indexOf(vue.selectRow) + 1)];
            vue.setSelectValue(next[vue.isGetName ? "CdNm" : "Cd"], true)
        },
        focus: function() {
            var vue = this;
            $(vue.$el).find(".target-input").focus();
        },
    }
}
</script>

