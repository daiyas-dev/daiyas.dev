<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>会社名</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" id="CompanyName" style="width: 290px;"
                    v-model="viewModel.会社名"
                    maxlength=24
                    v-maxBytes=24
                    v-setKana="res => {viewModel.会社名カナ = (viewModel.会社名カナ || '') + res.toString(); $forceUpdate();}"
            >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>会社名カナ</label>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" style="width: 250px;"
                    v-model="viewModel.会社名カナ"
                    maxlength=20
                    v-maxBytes=20
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>郵便番号</label>
            </div>
            <div class="col-md-2">
                <input class="form-control" style="width: 100px;" type="tel"
                    v-model=viewModel.郵便番号
                    maxlength="8"
                    v-maxBytes=8
                >
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>住所</label>
            </div>
            <div class="col-md-8">
                <input class="form-control" type="text"
                    v-model=viewModel.住所
                    maxlength=60
                    v-maxBytes=60
                >
            </div>
        </div>
        <div class="row align-items-start">
            <div class="col-md-1">
                <label>電話番号</label>
            </div>
            <div class="col-md-3">
                <input class="form-control" style="width: 130px;" type="tel"
                    v-model=viewModel.電話番号
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
            <div class="col-md-1">
                <label>締日</label>
            </div>
            <div class="col-md-2">
                <currency-input class="form-control text-right" style="width: 50px;" type="tel"
                    v-model=viewModel.自社締日
                    maxlength="2"
                    v-maxBytes=2
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>FAX</label>
            </div>
            <div class="col-md-3">
                <input class="form-control" style="width: 130px;" type="tel"
                    v-model=viewModel.ＦＡＸ
                    maxlength="12"
                    v-maxBytes=12
                >
            </div>
            <div class="col-md-1">
                <label>売上伝票No.</label>
            </div>
            <div class="col-md-3">
                <currency-input class="form-control text-right" style="width: 200px;" type="text" v-model="viewModel.売上伝票Ｎｏ" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>代表取締役</label>
            </div>
            <div class="col-md-3">
                <input class="form-control" type="text" style="width: 250px;"
                    v-model=viewModel.代表取締役
                    maxlength=20
                    v-maxBytes=20
                >
            </div>
            <div class="col-md-1">
                <label>入金伝票No.</label>
            </div>
            <div class="col-md-3">
                <currency-input class="form-control text-right" style="width: 200px;" type="text" v-model="viewModel.入金伝票Ｎｏ" />
            </div>
        </div>
        <div class="row" style="height: 30px;"></div>
        <div class="row" v-for="r in Repeater" v-bind:key="r.idx">
            <div class="col-md-1">
                <label>単価{{r.idx}}</label>
            </div>
            <div class="col-md-2">
                <currency-input class="form-control text-right" style="width: 150px;" type="text" v-model='viewModel["単価" + r.code]' />
            </div>
            <div class="col-md-1">
                <label>原価率{{r.idx}}</label>
            </div>
            <div class="col-md-1">
                <currency-input class="form-control text-right" style="width: 75px;" type="text" v-model='viewModel["原価率" + r.code]' />
            </div>
            <div class="col-md-4" v-show="r.idx <= 3">
                <label style="width: 120px;">{{r.idx == 1 ? "HACCP制定日" : (r.idx == 2 ? "HACCP改定日" : (r.idx == 3 ? "HACCP改定番号" : ""))}}</label>
                <input v-if="r.idx == 1" class="form-control" type="text" style="width: 180px;"
                    v-model=viewModel.HACCP制定日
                    maxlength=20
                    v-maxBytes=20
                >
                <input v-if="r.idx == 2" class="form-control" type="text" style="width: 180px;"
                    v-model=viewModel.HACCP改定日
                    maxlength=20
                    v-maxBytes=20
                >
                <currency-input v-if="r.idx == 3" class="form-control text-right" style="width: 100px;" type="text" v-model="viewModel.HACCP改定番号" />
            </div>
        </div>
        <div class="row" style="height: 30px;"></div>
        <div class="row">
            <div class="col-md-1">
                <label>値引</label>
            </div>
            <div class="col-md-2">
                <currency-input class="form-control text-right" style="width: 150px;" type="text" v-model="viewModel.値引" />
            </div>
            <div class="col-md-1">
                <label>手当申請額</label>
            </div>
            <div class="col-md-1">
                <currency-input class="form-control text-right" style="width: 75px;" type="text" v-model="viewModel.手当申請額" />
            </div>
            <div class="col-md-1">
                <label>仕出し製造者</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="SidasiTanto"
                    ref="PopupSelect_SidasiTanto"
                    :vmodel=viewModel
                    bind="仕出し製造者ＣＤ"
                    dataUrl="/Utilities/GetTantoListForSelect"
                    :isPreload=true
                    title="担当者一覧"
                    labelCd="担当者コード"
                    labelCdNm="担当者名"
                    :showColumns='[
                    ]'
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=160
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=TantoAutoCompleteFunc
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
label {
    width: 120px;
    min-width: unset;
}
.row {
    margin-bottom: 2px;
}
.memo{
    width: 800px;
    height: 60px;
    max-height: unset;
    line-height: 16px;
    resize: none;
}
</style>
<style>
form[pgid="DAI04010"] .multiselect.BushoCd .multiselect__tags {
    height: unset;
    padding-top: 10px;
}
form[pgid="DAI04010"] .multiselect.BushoCd .multiselect__content-wrapper {
    height: 200px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04010",
    components: {
    },
    computed: {
        Repeater: function() {
            return _.range(1, 8).map(v => {
                return {
                    idx: v,
                    code: window.Moji(v + "").convert("HE", "ZE").toString(),
                };
            });
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > 管理マスタメンテ",
            noViewModel: true,
            viewModel: {
                管理ＫＥＹ: null,
                会社名: null,
                会社名カナ: null,
                郵便番号: null,
                住所: null,
                電話番号: null,
                ＦＡＸ: null,
                代表取締役: null,
                自社締日: null,
                単価１: null,
                単価２: null,
                単価３: null,
                単価４: null,
                単価５: null,
                単価６: null,
                単価７: null,
                値引: null,
                売上伝票Ｎｏ: null,
                入金伝票Ｎｏ: null,
                原価率１: null,
                原価率２: null,
                原価率３: null,
                原価率４: null,
                原価率５: null,
                原価率６: null,
                原価率７: null,
                手当申請額: null,
                仕出し製造者ＣＤ: null,
                HACCP制定日: null,
                HACCP改定日: null,
                HACCP改定番号: null,
                修正担当者ＣＤ: null,
                修正日: null,
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "false" },
                { visible: "true", value: "登録", id: "DAI04010Grid1_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var params = _.cloneDeep(vue.viewModel);

                        //補完
                        _.forIn(params, (v, k, o) => {
                            if (k.startsWith("単価") || k.startsWith("原価率") || k.endsWith("伝票Ｎｏ")
                            || k == "HACCP改定番号" || k == "値引" || k == "手当申請額") {
                                o[k] = (v || 0) * 1;
                            }
                            if (k == "自社締日") {
                                o[k] = (v || 99) * 1;
                            }
                        });

                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        axios.post("/DAI04010/Save", params)
                        .then(res => {
                        })
                        .catch(err => {
                            console.log(err);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "管理マスタの登録に失敗しました"
                            })
                        });
                    }
                },
                { visible: "false" },
            );
        },
        mountedFunc: function(vue) {
            $(vue.$el).parents("div.body-content").addClass("Scrollable");

            axios.post("/DAI04010/Load")
                .then(res => {
                    var data = _.cloneDeep(res.data);
                    _.forIn(data, (v, k, o) => {
                        if (k.startsWith("単価") || k.startsWith("原価率") || k.endsWith("伝票Ｎｏ")
                         || k == "HACCP改定番号" || k == "値引" || k == "手当申請額") {
                            o[k] = (v || 0) * 1;
                        }
                        if (k == "自社締日") {
                            o[k] = (v || 99) * 1;
                        }
                    });

                    vue.viewModel = $.extend(true, vue.viewModel, data);
                });
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

            return list;
        },
    }
}
</script>
