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
                    v-setKana="res => viewModel.会社名カナ = res.toString()"
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
        searchParams: function() {
            var vue = this;
            var mc = moment(vue.viewModel.ClaimDate, "YYYY年MM月DD日");
            var mp = moment(vue.viewModel.ProcDate, "YYYY年MM月DD日");
            var ml = moment(vue.viewModel.LostDate, "YYYY年MM月DD日");

            return {
                ClaimDate: (mc.isValid() ? mc : moment()).format("YYYYMMDD"),
                ProcDate: (mp.isValid() ? mp : moment()).format("YYYYMMDD"),
                LostDate: (ml.isValid() ? ml : moment()).format("YYYYMMDD"),
            };
        },
        saveParams: function() {
            var vue = this;
            var mc = moment(vue.viewModel.ClaimDate + " " + vue.viewModel.ClaimTime, "YYYY年MM月DD日 HH時mm分");
            var mp = moment(vue.viewModel.ProcDate, "YYYY年MM月DD日");
            var ml = moment(vue.viewModel.LostDate, "YYYY年MM月DD日");

            return {
                クレームID: vue.viewModel.クレームID,
                受付日時: mc.format("YYYY-MM-DD HH:mm:ss"),
                管轄部門コード: vue.viewModel.BushoCd,
                クレーム区分コード: vue.viewModel.クレーム区分コード,
                顧客コード: vue.viewModel.顧客コード,
                平均食数: vue.viewModel.平均食数,
                顧客担当者名: vue.viewModel.顧客担当者名,
                商品コード: vue.viewModel.商品コード,
                単価: vue.viewModel.単価,
                クレーム内容: vue.viewModel.クレーム内容,
                受付担当者コード: vue.viewModel.受付担当者コード,
                受付方法: vue.viewModel.受付方法,
                クレーム処理日: mp.format("YYYY-MM-DD"),
                クレーム処理者コード: vue.viewModel.クレーム処理者コード,
                クレーム処理品: vue.viewModel.クレーム処理品,
                クレーム処理費用: vue.viewModel.クレーム処理費用,
                客先反応: vue.viewModel.客先反応,
                部門コード: vue.viewModel.部門コード,
                部門名: vue.viewModel.部門名,
                原因部署担当コード: vue.viewModel.原因部署担当コード,
                原因: vue.viewModel.原因,
                原因入力担当者コード: vue.viewModel.原因入力担当者コード,
                原因入力担当者名: vue.viewModel.原因入力担当者名,
                対策: vue.viewModel.対策,
                対策入力担当者コード: vue.viewModel.対策入力担当者コード,
                対策入力担当者名: vue.viewModel.対策入力担当者名,
                その後客先失客: vue.viewModel.その後客先失客,
                失客日: ml.format("YYYY-MM-DD"),
                失客日数: vue.viewModel.失客日数,
                未使用フラグ: vue.viewModel.未使用フラグ,
                修正担当者ＣＤ: vue.viewModel.修正担当者ＣＤ,
                修正日: vue.viewModel.修正日,
                管轄部門名: vue.viewModel.管轄部門名,
                得意先名: vue.viewModel.得意先名,
                クレーム区分名: vue.viewModel.クレーム区分名,
                原因部署名: vue.viewModel.原因部署名,
                原因部署担当: vue.viewModel.原因部署担当,
                ステータス: vue.viewModel.ステータス,
            };
        },
        LostDateEditable: function() {
            var vue = this;
            return vue.viewModel.その後客先失客 == "1";
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
                        if(!vue.saveParams.受付日時 || !vue.saveParams.顧客コード
                        || !vue.saveParams.商品コード || !vue.saveParams.クレーム区分コード
                        || !vue.saveParams.受付担当者コード || !vue.saveParams.受付方法){
                            $.dialogErr({
                                title: "登録不可",
                                contents: [
                                    !vue.saveParams.受付日時 ? "受付日時が入力されていません。" : "",
                                    !vue.saveParams.顧客コード ? "得意先が入力されていません。<br/>" : "",
                                    !vue.saveParams.商品コード ? "商品ＣＤが入力されていません。<br/>" : "",
                                    !vue.saveParams.クレーム区分コード ? "クレーム区分が入力されていません。<br/>" : "",
                                    !vue.saveParams.受付担当者コード ? "受付担当者が入力されていません。<br/>" : "",
                                    !vue.saveParams.受付方法 ? "受付方法が入力されていません。<br/>" : "",
                                ]
                            })

                            $(vue.$el).find("#ClaimDate")[!vue.saveParams.ClaimDate ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#ClaimTime")[!vue.saveParams.ClaimTime ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#CustomerSelect")[vue.saveParams.顧客コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#ProductSelect")[vue.saveParams.商品コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find(".ClaimKbn")[vue.saveParams.クレーム区分コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find("#SidasiTanto")[vue.saveParams.受付担当者コード == null ? "addClass" : "removeClass"]("has-error");
                            $(vue.$el).find(".SidasiKind")[vue.saveParams.受付方法 == null ? "addClass" : "removeClass"]("has-error");

                            return;
                        }

                        var params = _.cloneDeep(vue.saveParams);
                        params.修正担当者ＣＤ = vue.getLoginInfo().uid;
                        params.修正日 = moment().format("YYYY-MM-DD HH:mm:ss.SSS")
                        params.noCache = true;

                        $(vue.$el).find(".has-error").removeClass("has-error");

                        axios.post("/DAI04010/Save", params)
                        .then(res => {
                            DAI05150.conditionChanged();
                            $(vue.$el).closest(".ui-dialog-content").dialog("close");
                        })
                        .catch(err => {
                            console.log(err);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "クレームの登録に失敗しました"
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
                    });

                    vue.viewModel = $.extend(true, vue.viewModel, data);
                });
        },
        TantoAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = input.split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v);
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
