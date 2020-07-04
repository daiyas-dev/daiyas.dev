<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75 ModeLabel">{{ModeLabel}}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>対象日付</label>
                <DatePickerWrapper
                    id="TargetDate"
                    ref="DatePicker_Date"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="対象日付"
                    :editable=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="">名称</label>
                <input type="text" id="HolidayName" class="form-control"
                    v-model="viewModel.名称"
                    maxlength=30
                    v-maxBytes=30
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>対象部署</label>
                <VueMultiSelect
                    id="BushoCd"
                    ref="VueMultiSelect_Busho"
                    :vmodel=viewModel
                    bind="BushoArray"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    customStyle="{ width: 200px; }"
                    :onLoadFunc=onBushoLoaded
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
    min-width: 80px;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
<style>
form[pgid="DAI04161"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI04161"] .multiselect.BushoCd .multiselect__content-wrapper {
    height: 200px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04161",
    components: {
    },
    computed: {
        ModeLabel: function() {
            var vue = this;
            return vue.viewModel.IsNew == true ? "新規" : "修正";
        },
        searchParams: function() {
            var vue = this;
            var mt = moment(vue.viewModel.対象日付, "YYYY年MM月DD日");
            return {
                TargetDate: mt.isValid() ? mt.format("YYYYMMDD") : null,
                HolidayName: vue.viewModel.名称,
                BushoCdArray: vue.viewModel.BushoArray.map(v => v.code),
            };
        },
        saveParams: function() {
            var vue = this;
            return {
                対象日付: moment(vue.viewModel.対象日付, "YYYY年MM月DD日").format("YYYYMMDD"),
                名称: vue.viewModel.名称,
                対象部署ＣＤ: vue.viewModel.BushoArray.map(v => v.code).join(","),
            };
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "祝日マスタメンテ詳細",
            noViewModel: true,
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
            data.viewModel.BushoArray = [];
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "削除", id: "DAI04161_Delete", disabled: true, shortcut: "F3",
                    onClick: function (evt) {
                        if(!vue.searchParams.TargetDate) return;

                        var params = _.cloneDeep(vue.searchParams);
                        params.noCache = true;
                        var Message = {
                            "department_code": null,
                            "course_code": null,
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
                                        axios.post("/DAI04161/Delete", params)
                                            .then(res => {
                                                DAI04160.conditionChanged();
                                                $(this).dialog("close");
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
                    }
                },
                { visible: "true", value: "登録", id: "DAI04161Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        if(!vue.searchParams.TargetDate || !vue.searchParams.HolidayName){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.searchParams.TargetDate ? "対象日付が入力されていません。" : "",
                                    !vue.searchParams.HolidayName ? "名称が入力されていません。<br/>" : "",
                                ]
                            })
                            if(!vue.searchParams.TargetDate){
                                $(vue.$el).find("#TargetDate").addClass("has-error");
                            }else{
                                $(vue.$el).find("#TargetDate").removeClass("has-error");
                            }
                            if(!vue.searchParams.HolidayName){
                                $(vue.$el).find("#HolidayName").addClass("has-error");
                            }else{
                                $(vue.$el).find("#HolidayName").removeClass("has-error");
                            }
                            return;
                        }

                        var params = _.cloneDeep(vue.saveParams);
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;
                        params.IsNew = vue.viewModel.IsNew;
                        var Message = {
                            "department_code": null,
                            "course_code": null,
                            "custom_data": {
                                "message": "",
                                "values": {
                                    "updateMaster": true,
                                },
                            },
                        };
                        params.Message = Message;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        axios.post("/DAI04161/Save", params)
                        .then(res => {
                            if(!!res.data.duplicate){
                                progressDlg.dialog("close");
                                var duplicate = res.data.duplicate;
                                $.dialogInfo({
                                    title: "登録失敗",
                                    contents: "対象日付:" + duplicate + "が重複しています。",
                                });
                                vue.viewModel.得意先ＣＤ = "対象日付";
                            }else{
                                console.log("4061 save", res);
                                DAI04160.conditionChanged();
                                $(vue.$el).closest(".ui-dialog-content").dialog("close");
                            }
                        })
                        .catch(err => {
                            console.log(err);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "祝日マスタの登録に失敗しました"
                            })
                        });
                    }
                },
                { visible: "false" },
            );
        },
        mountedFunc: function(vue) {
            vue.viewModel.TargetDate = vue.params.TargetDate || moment().format("YYYY年MM月DD日");

            if(this.params.IsNew == false || !this.params.IsNew){
                //修正時：ボタン制御
                $("[shortcut='F3']").prop("disabled", false);
            }
        },
        onBushoLoaded: function(comp, entities) {
            var vue = this;
            if (!!vue.params.対象部署ＣＤ) {
                comp.selected = entities.filter(e => vue.params.対象部署ＣＤ.split(/ *, */g).includes(e.code))
                console.log("4061", comp.selected, entities);
            }
        },
    }
}
</script>
