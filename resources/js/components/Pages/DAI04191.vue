<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>銀行ＣＤ</label>
            </div>
            <div class="col-md-1">
                <input class="form-control text-right" type="text"
                    :value=viewModel.銀行CD
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">銀行名</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" :value="viewModel.銀行名">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label class="">銀行名カナ</label>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" style="" :value="viewModel.銀行名カナ">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>郵便フラグ</label>
            </div>
            <div class="col-md-1">
                <input type="checkbox" class="form-control p-1" style="width: 20px;" v-model="IsChecked">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>無効フラグ</label>
            </div>
            <div class="col-md-1">
                <input type="checkbox" class="form-control p-1" style="width: 20px;" v-model="IsUnavailable" disabled="true">
            </div>
        </div>
    </form>
</template>

<style scoped>
span.ModeLabel {
    font-size: 15px;
}
.row {
    margin-bottom: 2px;
}
div[class^="col-md"] > label {
    min-width: 80px;
}
fieldset div[class^="col-md"] label {
    min-width: 90px;
}
fieldset fieldset label {
    margin-right: -5px;
}
fieldset {
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    padding-right: 5px;
    margin: 0;
}
fieldset * {
    font-size: 14px !important;
}
legend {
    font-size: 15px;
    width: auto;
    padding: 0;
    margin: 0;
    margin-left: 5px;
    border-bottom:none;
}
fieldset .row {
    margin-left: 10px;
    margin-right: 0px;
}
textarea {
    max-height: unset;
    line-height: 16px;
    resize: none;
}
.groupFactory{
    width: 100px;
    text-align: left !important;
    margin-left: 30px;
}
</style>
<style>
#Page_DAI04191 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04191",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        IsChecked: function() {
            var vue = this;
            return vue.viewModel.郵便フラグ == 1 ? true : false;
        },
        IsUnavailable: function() {
            var vue = this;
            // TODO:
            // return vue.viewModel.無効フラグ == 1 ? true : false;
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "金融機関名称メンテ詳細",
            noViewModel: true,
            DAI04191Grid1: null,
            BankKeyWord: null,
            BankBranchKeyWord: null,
            grid1Options: {
                selectionModel: { type: "cell", mode: "single", row: true, onTab: "nextEdit" },
                showHeader: true,
                showToolbar: false,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, },
                autoRow: false,
                rowHtHead: 25,
                rowHt: 30,
                height: 200,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: true,
                    mode: "OR",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                sortModel: {
                    on: true,
                    cancel: false,
                    type: "local",
                    sorter:[ { dataIndx: "sortIndx", dir: "up" } ],
                },
                groupModel: {
                    on: true,
                    header: false,
                    grandSummary: true,
                },
                formulas: [
                ],
                colModel: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04191_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                {visible: "false"},
                {visible: "false"},
                { visible: "true", value: "CSV", id: "DAI04191_Csv", disabled: false, shortcut: "F7",
                    onClick: function () {
                        //TODO: CSV
                    }
                },
                { visible: "true", value: "登録", id: "DAI04191Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 登録
                        console.log("登録");
                        return;

                        //var targets = $.extend(true, {}, grid.createSaveParams());
                        var targets = grid.getCellsByClass({cls: "pq-cell-dirty"})
                            .map(v => {
                                return {
                                    "部署CD": v.rowData["部署CD"],
                                    "コースＣＤ": v.rowData["コースＣＤ"],
                                    "商品CD": v.dataIndx,
                                    "個数": v.rowData[v.dataIndx],
                                    "対象日付": v.rowData["対象日付"],
                                };
                            });
                        var conditions = $.extend(true, {}, vue.viewModel);

                        vue.DAI01020Grid1.saveData(
                            {
                                uri: "/DAI01020/Save",
                                params: { targets: targets },
                                //optional: { conditions: conditions },
                                // done: {
                                //     callback: (gridVue, grid, res) => {
                                //         vue.DAI01020Grid1.searchData(params);
                                //     },
                                // },
                            }
                        );
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
        BankSelectorParamsFunc: function(params, comp) {
            params.KeyWord = null;
            params.BankCd = null;
            return params;
        },
        onBankChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankChanged", info, comp, isValid);
            if (!isValid) {
                vue.BankKeyWord = comp.selectValue;
            }
        },
        onProductChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onProductChanged", info, comp, isValid);
            if (!isValid) {
                vue.ProductKeyWord = comp.selectValue;
            }
        },
        BankAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "銀行名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BankAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        ProductAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "商品略称"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("ProductAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        BankBranchParamsChangedCheckFunc: function(newVal, oldVal) {
            var vue = this;
            var ret = !!newVal.BankCd;
            console.log("BankBranchParamsChangedCheckFunc", ret);
            return ret;
        },
        onBankBranchChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onBankBranchChanged", info, comp, isValid);
            if (!isValid) {
                vue.BankBranchKeyWord = comp.selectValue;
            }
        },
        BankBranchAutoCompleteFunc: function(input, dataList) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "支店名カナ"];

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("BankBranchAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
    }
}
</script>
