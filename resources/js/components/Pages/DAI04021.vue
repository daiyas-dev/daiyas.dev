<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>担当者ＣＤ</label>
                <input class="form-control text-right" type="text"
                    :value=viewModel.担当者ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">担当者名</label>
                <input type="text" class="form-control" :value="viewModel.担当者名">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">担当者カナ</label>
                <input type="text" class="form-control" style="font-size: 15px !important;" :value="viewModel.担当者名カナ">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">郵便番号</label>
                <input class="form-control p-2" style="width: 90px;" type="text" :value=viewModel.郵便番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10">
                <label>住所</label>
                <input class="form-control" type="text" :value=viewModel.住所>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">電話番号</label>
                <input class="form-control p-1" style="width: 120px;" type="text" :value=viewModel.電話番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">FAX</label>
                <input class="form-control p-1" style="width: 120px;" type="text" :value=viewModel.ＦＡＸ>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>部署</label>
                <VueSelectBusho　/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>オペレータ</label>
                <input type="checkbox" class="form-control p-1" style="width: 20px;" v-model="IsChecked">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">ユーザーID</label>
                <input class="form-control p-2" style="width: 120px;" type="text" :value=viewModel.ユーザーＩＤ>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">パスワード</label>
                <input class="form-control p-2" style="width: 120px;" type="text" :value=viewModel.パスワード>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="width:100">営業業務区分</label>
                <VueSelect
                    id="EigoKbn"
                    :vmodel=viewModel
                    bind="営業業務区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 46 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="">権限区分</label>
                <input class="form-control p-2" style="width: 50px;" type="text" :value=viewModel.権限区分>
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
    min-width: 100px;
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
</style>
<style>
#Page_DAI04021 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04021",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        IsChecked: function() {
            var vue = this;
            return vue.viewModel.オペレータ == 1 ? true : false;
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "仕出処理->仕出注文入力",
            noViewModel: true,
            DAI04021Grid1: null,
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

        if (!!vue.$route && !!vue.$route.query) {
            data.viewModel = vue.$route.query;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
    }
}
</script>
