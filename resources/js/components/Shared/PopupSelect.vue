<template>
    <div
        class="form-group d-inline-flex align-items-center mb-0"
        :class='"PopupSelect " + id '
        :style='width ? ("width: " + width + "px") : ""'
    >
        <span v-if="label" class="text-nowrap d-flex align-items-center mr-1" :for="'btn' + id">{{label}}</span>
		<input type="text"
            :id="id"
            :ref="id"
            :class="['form-control', 'target-input',  editable == true ? 'editable' : 'readonly']"
            :style='inputWidth ? ("width: " + inputWidth + "px") : ""'
            v-model="vmodel[bind]"
            :readonly="this.editable == false"
            autocomplete="off"
            v-on=inputListeners
            :disabled="isPreload"
        >
        <button type="button"
            class="selector-button btn btn-info p-0 border-0"
            :id="'btn' + id"
            @click="showList"
            :disabled="isPreload"
        >
            <i class="fa fa-search fa-lg"></i>
        </button>
        <button type="button"
            class="clear-button btn btn-info p-0 border-0"
            :id="'btn' + id + 'Clear'"
            @click="clearValue"
            :disabled="isPreload"
        >
            <i class="fa fa-times fa-lg"></i>
        </button>
        <label v-if="isShowNameLabel == true">{{nameLabel}}</label>
		<input v-if="isShowName == true"
            type="text"
            class="form-control select-name"
            disabled
            :value="selectName"
            :style='nameWidth ? ("width: " + nameWidth + "px") : ""'>
    </div>
</template>

<style scoped>
.PopupSelect label:first {
    width: auto;
}
.PopupSelect .target-input {
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.PopupSelect .clear-button,
.PopupSelect .selector-button {
    width: 35px !important;
    border-left-width: 0px !important;
    display: flex;
    justify-content: center;
    align-items: center;
}
.PopupSelect .selector-button {
    border-radius: 0px;
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
    border-left-width: 0px;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
}
.readonly {
    background-color: white;
}
</style>

<script>
export default {
    name: "PopupSelect",
    data() {
        return {
            selectValue: "",
            selectRow: {},
            dataList: [],
            selectName: "",
            noMsg: false,
            isValid: false,
            isUnique: false,
            CountConstraint: null,
            searchParams: null,
            dataUrlPrev: null,
            paramsPrev: null,
            errorMsg: null,
        }
    },
    props: {
        id: String,
        label: String,
        vmodel: Object,
        bind: String,
        buddy: String,
        dataUrl: String,
        params: Object,
        embedded: Boolean,
        container: Object,
        width: Number,
        inputWidth: Number,
        title: String,
        labelCd: String,
        labelCdNm: String,
        isModal: Boolean,
        editable: Boolean,
        reuse: Boolean,
        existsCheck: Boolean,
        isGetName: Boolean,
        isCodeOnly: Boolean,
        showColumns: Array,
        onChangeFunc: Function,
        index: Number,
        isShowName: Boolean,
        isShowNameLabel: Boolean,
        nameWidth: Number,
        showTextFunc: String,
        isPreload: Boolean,
        onBeforeFunc: Function,
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
            if (comp.editable == false) {
                ev.click = comp.showList;
            }

            return Object.assign(
                {},
                this.$listeners,
                ev,
            )
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
                if (!_.isEqual(newVal, this.paramsPrev)) {
                    this.paramsPrev = _.cloneDeep(newVal);
                    this.getDataList();
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
            vue.dataList = null;
            $(vue.$el).children().prop("disabled", true);
            vue.getDataList(vue.params, (res) => {
                $(vue.$el).children().prop("disabled", false);
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
        plantChanged: function() {
            var vue = this;

            if (vue.isPreload) {
                vue.dataList = null;
                $(vue.$el).children().prop("disabled", true);
                vue.getDataList(vue.params, (res) => {
                    $(vue.$el).children().prop("disabled", false);
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
        accountChanged: function() {
            var vue = this;

            if (vue.isPreload) {
                vue.dataList = null;
                $(vue.$el).children().prop("disabled", true);
                vue.getDataList(vue.params, (res) => {
                    $(vue.$el).children().prop("disabled", false);
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

            if (!!vue.vmodel && !!vue.bind) {
                vue.vmodel[vue.bind] = "";
                if (vue.buddy) {
                    vue.vmodel[vue.buddy] = "";
                }
            }
        },
        getDataList: function(params, callback) {
            var vue = this;

            params = _.cloneDeep(params || vue.params || {});

            if (_.trim(vue.selectValue)) {
                params.selectValue = vue.selectValue;

                params.Keyword = vue.selectValue.includes("*") ? vue.selectValue.replace(/\*/g, "%") : ("%" + vue.selectValue + "%");
            }

            vue.searchParams = _.cloneDeep(params);

            axios.post(vue.dataUrl, params)
                .then(response => {
                    var res = response.data;

                    if (!!params && !_.isEqual($.trim(params.selectValue), $.trim(vue.selectValue))) {
                        console.log("PopupSelect already value chenged:" + params.selectValue + " -> " + vue.selectValue);
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
                    } else if (res.CountConstraint) {
                        //件数制約違反設定
                        vue.CountConstraint = res.CountConstraint;

                        res = res.Data;
                    }

                    //データリスト保持
                    vue.dataList = res;

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
                    reuse: vue.reuse,
                    callback: callback,
                    buttons: [
                        {
                            text: "選択",
                            class: "btn btn-primary",
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
                showSelector(vue.dataUrl, vue.params);
            }
        },
        onChange: function(event) {
            var vue = this;
            vue.selectValue = event.target.value;
        },
        setSelectValue: function(newVal, noMsg, isDebounce) {
            var vue = this;

            if (isDebounce != false) {
                vue.setSelectValueByDebounce(newVal, noMsg);
            } else {
                vue.execSetSelectValue(newVal, noMsg, false);
            }
        },
        setSelectValueByDebounce: _.debounce(function(newVal, noMsg) {
            var vue = this;

            vue.execSetSelectValue(newVal, noMsg, true);
        }, 100),
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
                var rowData = vue.dataList.filter(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);

                //入力有り、かつチェック指定されている場合は、存在チェック
                vue.isValid = checkValue(!_.isEmpty($.trim(vue.selectValue)) && vue.existsCheck);
                vue.isUnique = rowData.length == 1;

                //選択行データに設定
                vue.selectName = rowData.length == 0 ? "" : rowData[0][vue.isGetName ? "Cd" : "CdNm"];
                vue.selectRow = rowData.length == 0 ? {} : rowData[0];

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
                        parent.$forceUpdate();
                    } else {
                        vue.vmodel[vue.bind] = newVal;
                        if (vue.buddy) {
                            vue.vmodel[vue.buddy] = vue.selectName;
                        }
                    }
                }
            }

            //値チェック関数object
            var checkValue = function(check) {
                //検索結果から、現在の値を含むものを抽出
                var rowData = vue.dataList.filter(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]);

                if (rowData.length == 0 && check) {
                    //現在の値を含むものが無い場合、エラーとする

                    vue.errorMsg = (vue.title || (vue.label + "一覧")) + "に存在しません";

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
                } else {
                    //エラー項目設定解除
                    var $container = vue.embedded ? $(vue.$el).parent() : $(vue.$el);
                    var $target = vue.embedded ? $(vue.$el).parent() : $(vue.$el).find("#" + vue.id);

                    $(vue.$el)
                        .removeClass("has-error")
                        .find("#" + vue.id).tooltip("dispose");

                    vue.errorMsg = null;
                }

                return !rowData.length == 0;
            };

            //Popupから選択した場合は、元データが保持されているので現在の値と比較
            if (newVal && newVal != vue.selectRow[vue.isGetName ? "CdNm" : "Cd"]) {
                //表示項目名
                var target = vue.labelCd || vue.label || "コード";

                //検索結果から、現在の値を含むものを抽出
                var rowData = !!vue.dataList ? vue.dataList.filter(v => newVal == v[vue.isGetName ? "CdNm" : "Cd"]) : null;

                //該当するdataListがある場合、それを使用
                if (!!vue.dataList && vue.dataList.length && rowData.length) {
                    //値設定callback実行
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
                    params.Cd = newVal;
                    params[vue.bind] = newVal;
                    params.Keyword = "%" + newVal + "%";
                    vue.getDataList(params, (res) => setValue());
                }
            } else {
                //値未設定の場合は、そのままcallback実行
                setValue();
            }
        },
    }
}
</script>

