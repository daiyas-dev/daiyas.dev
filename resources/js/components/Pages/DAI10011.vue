<template>
    <form id="this.$options.name">
        <!-- prevent jQuery Dialog open autofucos -->
        <span class="ui-helper-hidden-accessible"><input type="text"/></span>
        <div class="row">
            <div class="col-md-2">
                <label>商品CD</label>
            </div>
            <div class="col-md-10">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.ProductCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 265px;" type="text" :value=viewModel.ProductNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>主食CD</label>
            </div>
            <div class="col-md-10">
                <PopupSelect
                    id="MainSelect"
                    ref="PopupSelect_Main"
                    :vmodel=viewModel
                    dataUrl="/Utilities/GetMainSubList"
                    :params="{ BushoCd: viewModel.BushoCd }"
                    bind="MainCd"
                    :buddies='{MainNm: "CdNm"}'
                    :isPreload=true
                    title="主食一覧"
                    labelCd="主食CD"
                    labelCdNm="主食名"
                    :popupWidth=1000
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                    :inputWidth=100
                    :nameWidth=200
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=MainSubAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>副食CD</label>
            </div>
            <div class="col-md-10">
                <PopupSelect
                    id="Sub"
                    ref="PopupSelect_Sub"
                    :vmodel=viewModel
                    dataUrl="/Utilities/GetMainSubList"
                    :params="{ BushoCd: viewModel.BushoCd }"
                    bind="SubCd"
                    :buddies='{ SubNm: "CdNm" }'
                    :dataListReset=true
                    title="副食一覧"
                    labelCd="副食CD"
                    labelCdNm="副食名"
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}, {Cd: '0'}]"
                    :inputWidth=100
                    :nameWidth=200
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=MainSubAutoCompleteFunc
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
.row {
    margin-left: 0px;
    margin-right: 0px;
}
.badge {
    font-size: 15px;
}
</style>
<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI10011",
    components: {
    },
    computed: {
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "商品詳細",
            noViewModel: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                ProductCd: null,
                ProductNm: null,
                MainCd: null,
                MainNm: null,
                SubCd: null,
                SubNm: null,
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, data.viewModel, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "設定", id: "DAI10011_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        vue.params.setCode(vue.viewModel.MainCd || 0, vue.viewModel.SubCd || 0);
                        $(vue.$el).closest(".ui-dialog-content").dialog("close");
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        MainSubAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];
            if (input == null || input == undefined) return dataList;
            if (input == "0") return dataList;

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "【商品区分: " + v.商品区分 + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
    }
}
</script>
