<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label>担当者ＣＤ</label>
                <input class="form-control text-right" type="text"
                    v-model=viewModel.担当者ＣＤ
                    :readonly=!viewModel.IsNew
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    maxlength="6"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="">担当者名</label>
                <input type="text" class="form-control" v-model="viewModel.担当者名">
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="">担当者カナ</label>
                <input type="text" class="form-control" style="font-size: 15px !important;" v-model="viewModel.担当者名カナ">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">郵便番号</label>
                <input class="form-control p-2" style="width: 90px;" type="tel" maxlength="8" v-model=viewModel.郵便番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>住所１</label>
                <input class="form-control" type="text" v-model=viewModel.住所>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">電話番号</label>
                <input class="form-control p-1" style="width: 120px;" type="text" v-model=viewModel.電話番号>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="">FAX</label>
                <input class="form-control p-1" style="width: 120px;" type="text" v-model=viewModel.ＦＡＸ>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label>部署</label>
                <VueSelect
                    id="BushoCd"
                    ref="BushoCdSelect"
                    :vmodel=viewModel
                    bind="所属部署ＣＤ"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=true
                    customStyle="{ width: 100px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>オペレータ</label>
                <VueCheck
                    bind="オペレータ"
                    :vmodel=viewModel
                    checkedCode="1"
                    customContainerStyle="border-style: none;"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="">ユーザーID</label>
                <input class="form-control p-2" style="width: 120px;" type="text" v-model=viewModel.ユーザーＩＤ>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label class="">パスワード</label>
                <input class="form-control p-2" style="width: 120px;" type="text" v-model=viewModel.パスワード>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
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
            <div class="col-md-4">
                <label class="">権限区分</label>
                <input class="form-control p-2" style="width: 50px;" type="text" v-model=viewModel.権限区分>
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
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "担当者マスタメンテ詳細",
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

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04021_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        //TODO: クリア
                    }
                },
                { visible: "true", value: "削除", id: "DAI04021_Delete", disabled: false, shortcut: "F3",
                    onClick: function () {
                        //TODO: 削除
                    }
                },
                {visible: "false"},
                { visible: "true", value: "登録", id: "DAI04021Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        //TODO: 新規ではない時、所属部署が古いCDの場合どうするか
                        //TODO: 登録

                        if(!vue.viewModel.担当者ＣＤ){
                            $.dialogErr({
                                title: "登録不可",
                                contents: "担当者CDを入力して下さい",
                            })
                            return;
                        }
                        if(!vue.viewModel.担当者名 || !vue.viewModel.所属部署ＣＤ){
                            $.dialogErr({
                                title: "登録不可",
                                contents: "担当者名・部署CDを入力して下さい",
                            })
                            return;
                        }
                        var params = _.cloneDeep(vue.viewModel);

                        params.修正担当者ＣＤ = params.userId;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        //チェックボックス
                        params.オペレータ = params.オペレータ ? 1 : 0;

                        //TODO: 登録用controller method call
                        axios.post("/DAI04021/Save", params)
                            .then(res => {
                            })
                            .catch(err => {
                                console.log(error);
                                //TODO: エラー
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");
        },
    }
}
</script>
