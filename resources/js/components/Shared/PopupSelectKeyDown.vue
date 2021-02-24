<template>
    <div
        class="form-group d-inline-flex align-items-center mb-0"
        :class='"PopupSelectKeyDown " + id '
        :style='width ? ("width: " + width + "px") : ""'
    >
        <span v-if="label" class="text-nowrap d-flex align-items-center mr-1" :for="'btn' + id">{{label}}</span>
		<input
            type="text"
            inputmode="numeric"
            :id="id"
            :ref="id"
            :class="[
                'form-control',
                'target-input',
                editable == true ? 'editable' : 'readonly',
                readOnly == true ? 'readOnly' : '',
                hideSearchButton && hideClearButton && !enablePrevNext ? 'mr-1' : '',
                hideSearchButton && hideClearButton && !enablePrevNext ? 'noButton' : '',
            ]"
            :style='inputWidth ? ("width: " + inputWidth + "px") : ""'
            :value="showText"
            :readonly="this.editable == false"
            autocomplete="off"
            :disabled=isDisabled
            @keydown.enter=onKeyDownEnter
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
            :disabled=!isPrevEnabled
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
            @change=onNameChanged
            v-model="selectName"
            :style='nameWidth ? ("width: " + nameWidth + "px") : ""'
        >
    </div>
</template>

<style scoped>
.PopupSelectKeyDown label:first {
    width: auto;
}
.PopupSelectKeyDown .target-input {
    font-size: 15px;
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.PopupSelectKeyDown .target-input.readOnly,
.PopupSelectKeyDown .target-input.noButton
{
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}
.PopupSelectKeyDown .btn {
    box-shadow: none;
}
.PopupSelectKeyDown .clear-button,
.PopupSelectKeyDown .prev-button,
.PopupSelectKeyDown .next-button,
.PopupSelectKeyDown .selector-button {
    width: 35px !important;
    border-left-width: 0px !important;
    display: flex;
    justify-content: center;
    align-items: center;
}
.PopupSelectKeyDown .selector-button,
.PopupSelectKeyDown .prev-button,
.PopupSelectKeyDown .next-button {
    border-radius: 0px;
}
.PopupSelectKeyDown .prev-button,
.PopupSelectKeyDown .next-button {
    width: 30px !important;
    background-color:forestgreen;
    border-color: forestgreen;
}
.PopupSelectKeyDown .clear-button {
    background-color: red;
    border-color: red;
    border-radius: 0px;
}
.PopupSelectKeyDown .clear-button:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}
.PopupSelectKeyDown .select-name {
    font-size: 15px;
    border-left-width: 0px;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
}
.PopupSelectKeyDown .select-name.readOnly {
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
    max-height: calc(30vh / var(--ratio));
    overflow-y: scroll;
    overflow-x: hidden;
    padding-right: 0px;
}
</style>

<script>
export default {
    name: "PopupSelectKeyDown",
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
        list: Array,
        dataListReset: Boolean,
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
        onInputFunc: Function,
        onKeyDownEnterFunc: Function,
        onAfterChangedFunc: Function,
        onNameChangedFunc: Function,
        index: Number,
        isShowName: Boolean,
        isShowNameLabel: Boolean,
        isNameEditable: Boolean,
        nameWidth: Number,
        showTextFunc: String,
        isPreload: Boolean,
        noResearch: Boolean,
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
        onAfterSearchFunc: Function,
        isRealTimeSearch: Boolean,
    },
    computed: {
        showText: function() {
            var comp = this;
            return !!comp.showTextFunc ? eval(comp.showTextFunc(comp)) : (comp.selectValue || comp.vmodel[comp.bind]);
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
                && vue.dataList.map(v => v[vue.isGetName ? "CdNm" : "Cd"]).indexOf(vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) != 0;
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
                    vue.dataList.map(v => v[vue.isGetName ? "CdNm" : "Cd"]).indexOf(vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) != vue.dataList.length - 1
                );
            return ret;
        },
        bindValue: function() {
            var vue = this;
            return vue.vmodel[vue.bind];
        },
    },
    watch: {
        params: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                // console.log(vue.id + " PopupSelectKeyDown params watch", newVal, vue.paramsPrev);

                if (!_.isEqual(newVal, vue.paramsPrev, (v, o) => v == o)) {

                    vue.paramsPrev = _.cloneDeep(newVal);

                    if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(newVal, vue.paramsPrev, vue)) {
                        return;
                    }

                    if (vue.isShowAutoComplete) {
                        var list = vue.getAutoCompleteList(newVal.KeyWord);
                        if (!!list && list.length != 0) return;
                    } else {
                        return;
                    }

                    if (!!vue.noResearch) return;
                    vue.getDataList(newVal, (res) => {
                        vue.setSelectValue(vue.vmodel[vue.bind], true, false);
                        if (!!vue.vmodel[vue.bind] && !vue.isValid) {
                            if (vue.isShowAutoComplete) {
                                if (!!vue.$parent.params && !!vue.$parent.params.IsChild) return;

                                if (!$(vue.$el).find("#" + vue.id).autocomplete("widget").is(":visible")) {
                                    $(vue.$el).find("#" + vue.id).autocomplete("search");
                                }
                            }
                        }
                    });
                }
            },
        },
        bindValue: {
            sync: true,
            handler: function(newVal) {
                var vue = this;
                // console.log("ps bindValue watcher:" + vue.bind, vue.bindValue);
                console.log("bindValue", vue._uid, vue.selectRow.Cd);
                if (vue.bindValue != vue.selectValue) {
                    vue.setSelectValue(vue.bindValue, true);
                }
            },
        },
    },
    created: function () {
        var vue = this;

        vue.$root.$on("plantChanged", vue.plantChanged);
        vue.$root.$on("accountChanged", vue.accountChanged);

        if (vue.isPreload) {
            if (!!vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(vue.params, vue.paramsPrev, vue)) {
                return;
            }

            vue.dataList = null;

            vue.paramsPrev = _.cloneDeep(vue.params);
            vue.getDataList(vue.params, (res) => {
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
            // console.log("PopupSelectKeyDown accountChanged");

            if (vue.isPreload) {
                if (!!vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(vue.params, vue.paramsPrev, vue)) {
                    return;
                }

                vue.dataList = null;
                // $(vue.$el).children().prop("disabled", true);

                vue.getDataList(vue.params, (res) => {
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
        onChange: function(event) {
            var vue = this;
            vue.setSelectValue(event.target.value, true);
        },
        onKeyDownEnter: function(event) {
            var vue = this;
            var params = _.cloneDeep(vue.searchParams);
            params.KeyWord=event.target.value;
            axios.post(vue.dataUrl, params)
                .then(response => {
                    var res = response.data;
                    var ret = !!res.Data ? res.Data : res;
                    //データリスト保持
                    if (!!ret.length) {
                        vue.dataList = ret[0];
                        //保持データに設定
                        vue.selectRow = ret[0];
                        vue.selectName = ret[0].CdNm;
                        vue.selectValue = ret[0].Cd;
                    } else if (!!vue.dataListReset) {
                        vue.dataList = [];
                    } else{
                        vue.dataList = [];
                        vue.selectName = null;
                        vue.selectValue = params.KeyWord;
                    }
                    if (!!vue.onKeyDownEnterFunc) {
                        setTimeout(
                            () => vue.onKeyDownEnterFunc(vue.selectValue,vue.selectRow,vue),
                            0
                        );
                    }
                })
                .catch(error => {
                    if (!!params && !!params.selectValue && !_.isEqual(params.Cd, vue.selectValue)) {
                        console.log("PopupSelectKeyDown already value chenged(Error):" + params.selectValue + " -> " + vue.selectValue);
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
        onNameChanged: _.debounce(function(event) {
            var vue = this;
            if (!event.isComposing) {
                if (!!vue.onNameChangedFunc) {
                    vue.onNameChangedFunc(event.target.value);
                }
            }
        }, 300),
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
                vue.onAfterChangedFunc("", null, vue);
            }
            vue.getDataList();
        },
        getDataList: function(params, callback) {
            var vue = this;

            params = _.cloneDeep(params || vue.params || {});

            if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(params, vue.paramsPrev, vue)) {
                return;
            }

            if (vue.isShowAutoComplete) {
                vue.disableAutoComplete();
            }

            if (!!vue.list) {
                vue.dataList = _.cloneDeep(vue.list);
                vue.isLoading = false;

                if (vue.isShowAutoComplete) {
                    vue.autoCompleteKey = null;
                    vue.autoCompleteList = [];
                    vue.setAutoComplete();
                }

                //callback実行
                if (callback) {
                    callback(vue.list);
                }
                return;
            }

            vue.searchParams = _.cloneDeep(params);

            axios.post(vue.dataUrl, params)
                .then(response => {
                    var res = response.data;

                    if (!!res && (res.onError || res.onException)) {
                        //エラーダイアログ
                        $.dialogErr({
                            title: res.message,
                            contents: res.errors,
                        });
                        console.log(res);

                        return;
                    }

                    var ret = !!res.Data ? res.Data : res;

                    //データリスト保持
                    if (!!ret.length) {
                        if (!vue.dataList || !!vue.dataListReset) {
                            vue.dataList = ret;
                        } else if (!_.isEqual(vue.dataList, ret)) {
                            // var ins = ret.filter(v => !vue.dataList.map(v => v.Cd).includes(v.Cd));
                            // vue.dataList = vue.dataList.concat(ins);

                            var list = ret.concat(vue.dataList);
                            list = _.uniqBy(list, "Cd");
                            vue.dataList = list;
                        }
                    } else if (!!vue.dataListReset) {
                        vue.dataList = [];
                    }

                    if (!!res.CountConstraint) {
                        //件数制約違反設定
                        vue.CountConstraint = res.CountConstraint * 1;
                        vue.ActualCounts = res.ActualCounts * 1;
                    } else {
                        if (vue.CountConstraint) {
                            if (vue.dataList.length < vue.CountConstraint) {
                                    vue.CountConstraint = null;
                            }
                        } else {
                            vue.CountConstraint = null;
                        }
                    }

                    vue.isLoading = false;
                    $(vue.$el).children().not(".select-name").prop("disabled", vue.isDisabled);
                    $(vue.$el).find(".select-name").prop("disabled", vue.isDisabled || !vue.isNameEditable);

                    if (vue.isShowAutoComplete) {
                        vue.autoCompleteKey = null;
                        vue.autoCompleteList = [];
                        vue.setAutoComplete();
                    }

                    if (vue.onAfterSearchFunc) {
                        var rs = vue.onAfterSearchFunc(vue);
                        if (rs == false) return;
                    }

                    //callback実行
                    if (callback) {
                        callback(ret);
                    }
                })
                .catch(error => {
                    if (!!params && !!params.selectValue && !_.isEqual(params.Cd, vue.selectValue)) {
                        console.log("PopupSelectKeyDown already value chenged(Error):" + params.selectValue + " -> " + vue.selectValue);
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
                            grid.SelectRow().removeAll();
                            grid.setSelection({ rowIndx: rowIndx });
                        } else {
                            if (_.keys(grid.getSelectionData()).length == 0 && grid.getData().length > 0) {
                                try {
                                    grid.scrollRow({ rowIndx: 0 });
                                    grid.SelectRow().removeAll();
                                    grid.setSelection({ rowIndx: 0 });
                                } catch {
                                }
                            }
                        }
                        grid.widget().css("visibility", "visible");
                    });
                }
            };

            var showSelector = function(dataUrl, params) {
                var p = _.cloneDeep(params) || {};
                p.NoLimit = true,
                PageDialog.showSelector({
                    dataUrl: dataUrl,
                    params: p,
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
                            target: p.target,
                            click: function(gridVue, grid) {
                                if (event.target.name == "SearchStrings" && event.type == "keydown" && (event.key == "Process" || event.which == 13)) {
                                    return false;
                                }

                                var selection = grid.SelectRow().getSelection();
                                var rowIndx = !!selection.length ? selection[0].rowIndx : null;

                                if (rowIndx != null) {
                                    var rowData = grid.getRowData({ rowIndx: rowIndx });

                                    //コード及び名称の指定された値を取得
                                    var value = rowData[vue.isGetName ? "CdNm" : "Cd"];
                                    var name  = rowData[vue.isGetName ? "Cd" : "CdNm"];

                                    //保持データに設定
                                    vue.selectRow = rowData;
                                    vue.selectName = name;
                                    vue.selectValue = value;

                                    if (!!vue.isShowAutoComplete) {
                                        if (!_.find(vue.dataList, rowData)) {
                                            vue.dataList.push(rowData);
                                        }
                                    }

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

                                    if (!!vue.onChangeFunc) {
                                        vue.onChangeFunc(value, rowData, vue);
                                    }

                                    if (!!vue.onAfterChangedFunc) {
                                        vue.onAfterChangedFunc(value, rowData, vue);
                                    }

                                    if (this.target) {
                                        this.target.val(value);
                                    }
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
        setSelectValue: function(newVal, noMsg, item) {
            var vue = this;
            console.log("ps setSelectValue", newVal);
            if (!newVal) {
                console.log("ps setSelectValue empty");
            }

            //elementに設定
            // $(vue.$el).find("#" + vue.id).val(newVal);

            //値設定関数object
            var setValue = function() {
                try {

                    var rowData = item || vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);
                    if (!!newVal && !rowData && vue.isShowAutoComplete && vue.getAutoCompleteList(newVal).length == 1) {
                        rowData = vue.getAutoCompleteList(newVal)[0];
                    }

                    if (!!vue.noResearch && !vue.existsCheck) {
                        rowData = { Cd: newVal, CdNm: "" };
                    }

                    //入力有り、かつチェック指定されている場合は、存在チェック
                    // vue.isValid = checkValue(!_.isEmpty($.trim(vue.selectValue)) && vue.existsCheck);
                    vue.isValid = checkValue((newVal != null && newVal != "") && vue.existsCheck);
                    vue.isUnique = !!rowData;

                    //選択行データに設定
                    vue.selectValue = !rowData ? newVal : rowData[!vue.isGetName ? "Cd" : "CdNm"];
                    vue.selectName = !rowData ? "" : rowData[vue.isGetName ? "Cd" : "CdNm"];
                    vue.selectRow = !rowData ? {} : rowData;

                    console.log("ps setValue", newVal, vue.selectValue, vue.selectName, vue.selectRow);

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
                    if (!!vue.vmodel && !!vue.bind && (!!vue.isValid || vue.selectValue == "")) {
                        var parent = vue.$parent;

                        if (parent) {
                            parent.$set(vue.vmodel, vue.bind, vue.selectValue);
                            if (vue.buddy) {
                                parent.$set(vue.vmodel, vue.buddy, vue.selectName);
                            }
                            if (!!vue.buddies) {
                                _.forIn(vue.buddies, (v, k) => parent.$set(vue.vmodel, k, vue.selectRow[v]));
                            }
                            parent.$forceUpdate();
                        } else {
                            console.log("ps setValue set vmodel", vue.selectValue, vue.selectRow)
                            vue.vmodel[vue.bind] = vue.selectValue;
                            if (vue.buddy) {
                                vue.vmodel[vue.buddy] = vue.selectName;
                            }
                            if (!!vue.buddies) {
                                _.forIn(vue.buddies, (v, k) => vue.vmodel[k] = vue.selectRow[v]);
                            }
                        }
                        if (vue.onAfterChangedFunc) {
                            vue.onAfterChangedFunc(vue.selectValue, vue.selectRow, vue);
                        }
                    }
                } catch(ex) {
                    console.log("PopupSelectKeyDown setValue Exception", ex);
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
                var rowData = item || vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);
                // if (!!newVal && !rowData && vue.isShowAutoComplete && vue.getAutoCompleteList(newVal).length == 1) {
                //     rowData = vue.getAutoCompleteList(newVal)[0];
                // }

                if (!rowData && check) {
                    //現在の値を含むものが無い場合、エラーとする
                    vue.errorMsg = vue.isShowAutoComplete && !!rowData && vue.getAutoCompleteList(newVal).length > 1
                        ? ((vue.title || (vue.label + "一覧")) + "で複数該当します")
                        : ((vue.title || (vue.label + "一覧")) + "に存在しません");

                    //エラー項目設定
                    $(vue.$el)
                        .addClass("has-error")
                        .find("#" + vue.id).tooltip({
                            animation: false,
                            placement: "top",
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
                    return true;
                }

                return !!rowData;
            };

            //Popupから選択した場合は、元データが保持されているので現在の値と比較
            if (newVal && newVal != vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) {
                //表示項目名
                var target = vue.labelCd || vue.label || "コード";

                //検索結果から、現在の値を含むものを抽出
                var rowData = item || !!vue.dataList ? vue.dataList.find(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]) : null;
                if (!rowData && vue.isShowAutoComplete && vue.autoCompleteList.length == 1) {
                    rowData = vue.autoCompleteList[0];
                }

                //該当がある場合、それを使用
                if (!!rowData) {
                    //値設定callback実行
                    setValue();
                } else if (vue.isShowAutoComplete) {
                    var list = vue.getAutoCompleteList(newVal);

                    var isExcept = !!vue.exceptCheck && !!vue.exceptCheck.length && !vue.exceptCheck.some(v => _.keys(v).some(k => v[k] == newVal));
                    if ((list.length == 0 || !!vue.CountConstraint) && !vue.noResearch && !isExcept) {
                    // if (list.length == 0 && !vue.noResearch && !vue.exceptCheck.some(v => _.keys(v).some(k => v[k] == newVal))) {
                        //該当が無い場合は再検索
                        var params = _.cloneDeep(vue.params) || {};
                        params[vue.bind] = newVal;
                        params.KeyWord = newVal;
                        vue.getDataList(params, (res) => setValue());
                    } else {
                        setValue();
                    }
                } else {
                    //該当が無い場合は再検索
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

                            $(vue.$el)
                                .remove("has-error")
                                .find("#" + vue.id)
                                .tooltip("dispose")
                                .tooltip({
                                    animation: false,
                                    placement: "top",
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
                    //選択した値を設定
                    vue.selectRow = ui.item;
                    vue.setSelectValue(ui.item.value, true, ui.item);

                    if (!!vue.onGrid && !!vue.grid) {
                        $(vue.$el).trigger($.Event("keyup", {
                            keyCode: 13,
                            which: 13,
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

                    // if (!!vue.onGrid && input.val() != vue.selectValue) {
                    //     vue.setSelectValue(input.val(), true);
                    // }
                },
                position: { my: "left top", at: "left bottom", collision: "flip" },
                autoFocus: false,
                delay: 100,
                minLength: vue.AutoCompleteMinLength || 0,
            })
            .focus(function(ev) {
                // console.log("autocomplete focus:" + input.val() + " skip:" + vue.AutoCompleteFocusSkip);
                if (vue.AutoCompleteFocusSkip) {
                    vue.AutoCompleteFocusSkip = false;
                    return false;
                } else {
                    if (!input.autocomplete("widget").is(":visible")) {
                        input.autocomplete("search", input.val());
                    }
                }
            })
            .click(function(ev) {
                // console.log("autocomplete click:" + input.val());
                input.focus();
            })
            .keydown(function(ev) {
                if (ev.key == "F8") {
                    vue.showList();
                    return false;
                }

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
            });
            input.autocomplete("enable");
        },
        getAutoCompleteList: function(key) {
            var vue = this;
            console.log("getAutoCompleteList", key);

            try {

            if (key == vue.autoCompleteKey && !vue.autoCompleteList) {
                // console.log("getAutoCompleteList: same key " + key);
                return vue.autoCompleteList;
            }
            if (!vue.dataList || !vue.dataList.length) return [];

            if (!_.isObject(vue.dataList[0])) {
                vue.dataList = vue.dataList.map(v => {
                    return { Cd: v, CdNm: v || "【無し】" };
                });
            }

            var match = vue.dataList.find(v => v[vue.isGetName ? "CdNm" : "Cd"] == key);
            var list = (vue.AutoCompleteFunc && key != null && key != undefined)
                ? vue.AutoCompleteFunc(key, _.cloneDeep(vue.dataList), vue)
                : _.cloneDeep(vue.dataList)
                    .filter(v => v[vue.isGetName ? "CdNm" : "Cd"].includes(key))
                    .map(v => {
                        var ret = v;
                        ret.value = v[vue.isGetName ? "CdNm" : "Cd"];
                        ret.text = v[!vue.isGetName ? "CdNm" : "Cd"];
                        ret.label = ret.value == ""
                            ? (ret.text || "【無し】")
                            : (ret.value + (ret.text != "" && ret.value != ret.text ? (" : " + ret.text) : ""));
                        return ret;
                    })
                    ;

            if (!!match && list.length == 1) {
                list = _.cloneDeep(vue.dataList)
                    .map(v => {
                        var ret = v;
                        ret.value = v[vue.isGetName ? "CdNm" : "Cd"];
                        ret.text = v[!vue.isGetName ? "CdNm" : "Cd"];
                        ret.label = ret.value == ""
                            ? (ret.text || "【無し】")
                            : (ret.value + (ret.text != "" && ret.value != ret.text ? (" : " + ret.text) : ""));
                        return ret;
                    })
                    ;
            }

            // console.log("getAutoCompleteList:" + key + " = " + list.length + "[" + match.length + "]");

            vue.autoCompleteKey = key;
            vue.autoCompleteList = list;

            return list;
            } catch (err) {
                console.log("getAutoCompleteList Error", err, key);
            };
        },
        prevList: function() {
            var vue = this;
            var prev = vue.dataList[
                !vue.selectRow
                ? 0 :
                (vue.dataList.map(v => v[vue.isGetName ? "CdNm" : "Cd"]).indexOf(vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) - 1)
            ];
            vue.setSelectValue(prev[vue.isGetName ? "CdNm" : "Cd"], true)
        },
        nextList: function() {
            var vue = this;
            var next = vue.dataList[
                !vue.selectRow
                ? 0 :
                (vue.dataList.map(v => v[vue.isGetName ? "CdNm" : "Cd"]).indexOf(vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) + 1)
            ];
            vue.setSelectValue(next[vue.isGetName ? "CdNm" : "Cd"], true)
        },
        focus: function(skip) {
            var vue = this;
            vue.AutoCompleteFocusSkip = skip;
            $(vue.$el).find(".target-input").focus();
        },
    }
}
</script>

