<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>部署</label>
                <VueSelect
                    id="BushoCd"
                    ref="BushoCdSelect"
                    :vmodel=viewModel
                    bind="部署ＣＤ"
                    uri="/Utilities/GetBushoList"
                    :params="{ cds: null }"
                    :withCode=true
                    :isShowInvalid=true
                    customStyle="{ width: 100px; }"
                    :disabled=!viewModel.IsNew
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="width:100">コース区分</label>
                <VueSelect
                    id="CourseKbn"
                    :vmodel=viewModel
                    bind="コース区分"
                    uri="/Utilities/GetCodeList"
                    :params="{ cd: 19 }"
                    :withCode=true
                    customStyle="{ width: 200px; }"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label>コースＣＤ</label>
                <input class="form-control text-right" type="text" style="width:80px;"
                    id="CourseCd"
                    v-model=viewModel.コースＣＤ
                    :readonly=false
                    :tabindex="viewModel.IsNew ? 0 : -1"
                    maxlength="5"
                    v-int
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-11">
                <label class="">コース名</label>
                <input class="form-control p-2" type="text"
                    v-model=viewModel.コース名
                    id="CourseNm"
                    maxlength="60"
                    v-maxBytes=60

                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-11">
                <label>担当者</label>
                <PopupSelect
                    id="TantoSelect"
                    ref="PopupSelect_Tanto"
                    :vmodel=viewModel
                    bind="担当者ＣＤ"
                    buddy="担当者名"
                    dataUrl="/Utilities/GetTantoList"
                    :params="{ bushoCd: null, KeyWord: TantoKeyWord }"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者ＣＤ"
                    labelCdNm="担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: ''}]"
                    :onChangeFunc=onTantoChanged
                    :inputWidth=80
                    :nameWidth=197
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-11">
                <label>工場区分</label>
                <VueOptions
                    id="KojoKbn"
                    ref="VueOptions_KojoKbn"
                    customLabelStyle="text-align: center;"
                    :vmodel=viewModel
                    bind="工場区分"
                    :list="[
                        {code: '', name: '', label: '指定なし'},
                        {code: '1', name: '工場', label: '工場'},
                        {code: '2', name: '他支店', label: '他支店'},
                    ]"
                />
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
#Page_DAI04081 .CustomerSelect .select-name {
    color: royalblue;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04081",
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
            ScreenTitle: "コースマスタメンテ詳細",
            noViewModel: true,
            DAI04081Grid1: null,
            TantoKeyWord: null,
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

        //初期値：新規なら一覧で表示中の部署ＣＤ
        if(data.viewModel.IsNew){
            data.viewModel.部署ＣＤ = vue.params.BushoCd || "";
            data.viewModel.工場区分 = "";
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "クリア", id: "DAI04081_Clear", disabled: false, shortcut: "F2",
                    onClick: function () {
                        vue.clearDetail();
                    }
                },
                { visible: "true", value: "削除", id: "DAI04081_Delete", disabled: true, shortcut: "F3",
                    onClick: function () {

                        var cd = vue.viewModel.コースＣＤ;
                        if(!cd) return;

                        var params = {BushoCd: vue.viewModel.部署ＣＤ, CourseCd: cd};
                        params.noCache = true;

                        var Message = {
                            "department_code": vue.viewModel.部署ＣＤ,
                            "course_code": vue.viewModel.コースＣＤ,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;

                        $.dialogConfirm({
                            title: "マスタ削除確認",
                            contents: "マスタを削除します。",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        axios.post("/DAI04081/Delete", params)
                                            .then(res => {
                                                DAI04080.conditionChanged();
                                                $(this).dialog("close");
                                                //画面04081を閉じる
                                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                            })
                                            .catch(err => {
                                                console.log(err);
                                            }
                                        );
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
                        console.log("削除", params);
                    }
                },
                { visible: "true", value: "登録", id: "DAI04081Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {

                        //必須入力：工場区分以外
                        if(!vue.viewModel.部署ＣＤ || !vue.viewModel.コースＣＤ || !vue.viewModel.コース名 || !vue.viewModel.担当者ＣＤ){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.viewModel.部署ＣＤ ? "部署が入力されていません。<br/>" : "",
                                    !vue.viewModel.コース区分 ? "コース区分が入力されていません。<br/>" : "",
                                    !vue.viewModel.コースＣＤ ? "コースＣＤが入力されていません。<br/>" : "",
                                    !vue.viewModel.コース名 ? "コース名が入力されていません。<br/>" : "",
                                    !vue.viewModel.担当者ＣＤ ? "担当者が入力されていません。<br/>" : ""
                                ]
                            })
                            if(!vue.viewModel.部署ＣＤ){
                                $(vue.$el).find("#BushoCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#BushoCd").removeClass("has-error");
                            }
                            if(!vue.viewModel.コースＣＤ){
                                $(vue.$el).find("#CourseCd").addClass("has-error");
                            }else{
                                $(vue.$el).find("#CourseCd").removeClass("has-error");
                            }
                             if(!vue.viewModel.コース名){
                                $(vue.$el).find("#CourseNm").addClass("has-error");
                            }else{
                                $(vue.$el).find("#CourseNm").removeClass("has-error");
                            }
                            if(!vue.viewModel.担当者ＣＤ){
                                $(vue.$el).find("#TantoSelect").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TantoSelect").removeClass("has-error");
                            }
                           return;
                        }

                        var params = _.cloneDeep(vue.viewModel);

                        params.工場区分 = params.工場区分 || 0;
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")

                        var Message = {
                            "department_code": vue.viewModel.部署ＣＤ,
                            "course_code": vue.viewModel.コースＣＤ,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        //登録用controller method call
                        axios.post("/DAI04081/Save", params)
                            .then(res => {
                                if(!!res.data.duplicate){
                                    var duplicate = res.data.duplicate;
                                    $.dialogInfo({
                                        title: "登録失敗",
                                        contents: "コースCD:" + duplicate + "が重複しています。",
                                    });
                                }else{
                                    DAI04080.conditionChanged();

                                    //画面を閉じる
                                    $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                }
                            })
                            .catch(err => {
                                console.log(err);
                            }
                        );
                        console.log("登録", params);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

        if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onTantoChanged: function(element, info, comp, isNoMsg, isValid, noSearch) {
            var vue = this;
            console.log("onTantoChanged", info, comp, isValid);
            if (!isValid) {
                vue.TantoKeyWord = comp.selectValue;
            }
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["Cd", "CdNm", "担当者名カナ"];

            if (input == comp.selectValue && comp.isUnique) {
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
                    return keyAND.length == 0
                        || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "【" + (!!v.部署 ? v.部署.部署名 : "部署無し") + "】";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;
            console.log("TantoAutoCompleteFunc:" + input + " = " + list.length);
            return list;
        },
        clearDetail: function(){
            var vue = this;

            $(vue.$el).find(".has-error").removeClass("has-error");

            _.keys(DAI04081.viewModel).forEach(k => DAI04081.viewModel[k] = null);
            vue.viewModel.IsNew = true;
            vue.viewModel.userId = vue.query.userId;

            //初期値
            vue.viewModel.部署ＣＤ = vue.params.BushoCd || vue.params.部署ＣＤ || "";
            vue.viewModel.工場区分 = "";

            //ボタン制御
            $("[shortcut='F3']").prop("disabled", true);
        },
    }
}
</script>
