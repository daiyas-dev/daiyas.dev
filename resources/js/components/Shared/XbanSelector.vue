//required bootstrap css
<template>
    <div class="XbanSelector" :id='"XbanSelector" + suffix'>
        <PopupSelect
            v-show="isShow('Seizono')"
            :id='"Seizono" + suffix'
            :ref='"Seizono" + suffix'
            label="製造指図書番号"
            :vmodel=this.selection
            bind="Seizono"
            dataUrl="/Utilities/GetSeizonoList"
            :params=this.SeizonoParams
            labelCd="製造指図書番号"
            labelCdNm="部品名"
            :isModal=true
            :editable=true
            :reuse=true
            :existsCheck=true
        />
        <br v-if="isShow('Seizono')"/>
        <PopupSelect
            v-show="isShow('Seibanx')"
            :id='"Seibanx" + suffix'
            :ref='"Seibanx" + suffix'
            label="製番"
            :vmodel=this.selection
            bind="Seibanx"
            dataUrl="/Utilities/GetForSeibanxListHasBuhindb"
            labelCd="製番"
            labelCdNm="製品名"
            :isModal=true
            :editable=true
            :reuse=true
            :existsCheck=true
        />
        <VueSelect v-show="isShow('Daibanx')" title="大番"   name="Daibanx" :id='"Daibanx" + suffix' :list="this.DaibanxList" :vmodel="this.selection" bind="Daibanx" :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('Chushox')" title="中小番" name="Chushox" :id='"Chushox" + suffix' :list="this.ChushoxList" :vmodel="this.selection" bind="Chushox" :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('Chubanx')" title="中番"   name="Chubanx" :id='"Chubanx" + suffix' :list="this.ChubanxList" :vmodel="this.selection" bind="Chubanx" :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('Shobanx')" title="小番"   name="Shobanx" :id='"Shobanx" + suffix' :list="this.ShobanxList" :vmodel="this.selection" bind="Shobanx" :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('Bubanxx')" title="部番"   name="Bubanxx" :id='"Bubanxx" + suffix' :list="this.BubanxxList" :vmodel="this.selection" bind="Bubanxx" :hasNull=true></VueSelect>
        <br v-if="isShow('Seibanx')"/>
        <PopupSelect
            v-show="isShow('Seibanx2')"
            :id='"Seibanx2" + suffix'
            :ref='"Seibanx2" + suffix'
            label="製番"
            :vmodel=this.selection
            bind="Seibanx"
            dataUrl="/Utilities/GetForSeibanxListHasBuhindb"
            labelCd="製番"
            labelCdNm="製品名"
            :isModal=true
            :editable=true
            :reuse=true
        />
        <VueSelect v-show="isShow('SchYouku')" title="要区" name="SchYouku" :id='"SchYouku" + suffix' :list="this.DaibanxList" :vmodel="this.selection" bind="Daibanx" :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('SchYno')"   title="YNO"  name="SchYno"   :id='"SchYno"   + suffix' :list="this.SchYnoList"  :vmodel="this.selection" bind="SchYno"  :hasNull=true></VueSelect>
        <VueSelect v-show="isShow('SchBno')"   title="BNO"  name="SchBno"   :id='"SchBno"   + suffix' :list="this.SchBnoList"  :vmodel="this.selection" bind="SchBno"  :hasNull=true></VueSelect>
        <br v-if="isShow('Seibanx2')"/>
        <PopupSelect
            v-show="isShow('Orderno')"
            :id='"Orderno" + suffix'
            :ref='"Orderno" + suffix'
            label="手配番号"
            :vmodel=this.selection
            bind="Orderno"
            dataUrl="/Utilities/GetOrdernoList"
            :params=this.OrdernoParams
            labelCd="手配番号"
            labelCdNm="部品名"
            :isModal=true
            :editable=true
            :reuse=true
            :existsCheck=true
        />
    </div>
</template>

<style>
.XbanSelector {
    display: block;
}
.XbanSelector > div {
    margin-bottom: 7px !important;
}
.XbanSelector  br:first-child,
.XbanSelector  br + br,
.XbanSelector  br:last-child,
.XbanSelector  div[style*="display: none;"] > br
{
    display: none;
}
.XbanSelector [id^="Seizono"] {
    width: 100px;
}
.XbanSelector [id^="Seibanx"] {
    width: 75px;
}
.XbanSelector [id^="Orderno"] {
    width: 100px;
}
.XbanSelector [id^="Daibanx"],
.XbanSelector [id^="Chubanx"],
.XbanSelector [id^="Shobanx"],
.XbanSelector [id^="SchYouku"]
{
    width: 46px;
}
.XbanSelector [id^="Bubanxx"],
.XbanSelector [id^="SchYno"],
.XbanSelector [id^="SchBno"]
{
    width: 54px;
}
.XbanSelector [id^="Chushox"]
{
    width: 62px;
}
.XbanSelector label {
    display: inline;
    width: 40px;
    margin: 0px;
    text-align: left;
    vertical-align: middle;
}
.XbanSelector label[for*="Seizono"] {
    width: 120px;
}
.XbanSelector label[for*="Chushox"] {
    width: 60px;
}
.XbanSelector label[for*="Orderno"] {
    width: 80px;
}
.XbanSelector label[for*="Orderno"] {
    width: 80px;
}
</style>

<script>
import VueDataList from "@vcs/DataList.vue";
import VueSelect   from "@vcs/VueSelect.vue";
import PopupSelect   from "@vcs/PopupSelect.vue";

export default {
    name: "XbanSelector",
    data() {
        return {
            selection: {
                Seizono: "",
                Seibanx: "",
                Daibanx: "",
                Chushox: "",
                Chubanx: "",
                Shobanx: "",
                Bubanxx: "",
                SchYno: "",
                SchBno: "",
                Orderno: "",
            },
            selectionPrev: {
                Seizono: "",
                Seibanx: "",
                Daibanx: "",
                Chushox: "",
                Chubanx: "",
                Shobanx: "",
                Bubanxx: "",
                SchYno: "",
                SchBno: "",
                Orderno: "",
            },
            includesPrev: Array,
            excludesPrev: Array,
            XbanList: [],
            DaibanxList: [],
            ChushoxList: [],
            ChubanxList: [],
            ShobanxList: [],
            BubanxxList: [],
            SchYnoList: [],
            SchBnoList: [],
            noWatch: false,
            CountConstraint: null,
            SeizonoParams: {},
            OrdernoParams: {},
        }
    },
    props: {
        includes: Array,
        excludes: Array,
        vmodel: Object,
        suffix: String,
        params: Object,
        onChangeFunc: Function,
        noRelation: Boolean,
        hasRelation: Boolean,
        isUniqCond: Boolean,
    },
    computed: {
        _noRelation: function() {
            return this.hasRelation != true;
        },
    },
    watch: {
        includes: {
            deep: true,
            handler: function(newVal) {
                var vue = this;

                if (!_.isEqual(newVal, vue.includesPrev)) {
                    vue.includesChanged(newVal);
                }
             },
        },
        excludes: {
            deep: true,
            handler: function(newVal) {
                var vue = this;

                if (!_.isEqual(newVal, vue.excludesPrev)) {
                    vue.excludesChanged(newVal);
                }
            },
        },
        selection: {
            //sync: true,
            deep: true,
            handler: function(newVal) {
                var vue = this;
                //console.log("XbanSelector selection watcher");

                var values = _.cloneDeep(newVal);
                vue.selectionChanged(values);
            },
        },
    },
    components: {
        "VueDataList": VueDataList,
        "VueSelect"  : VueSelect,
        "PopupSelect"  : PopupSelect,
    },
    created: function () {
        this.$root.$on("plantChanged", this.plantChanged);
        this.$root.$on("accountChanged", this.accountChanged);
    },
    mounted: function () {
        //連動リスト用一覧取得
        this.getXbanList(this.selection, this.updateList);
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
            //連動リスト用一覧取得し直し
            this.getXbanList(this.selection, this.updateList);
        },
        accountChanged: function() {
        },
        selectionChanged: _.debounce(function(values) {
            var vue = this;

            if (vue.noWatch) {
                return;
            }

            //変更差分
            var df = $.diff(vue.selectionPrev, values);

            //変更なし
            if (Object.keys(df).length == 0) {
                return;
            }

            if (_.has(df, "Seibanx")) {
                //製番変更時

                var SeibanxComp = df.Seibanx;
                if (!df.Seibanx) {
                    values.Daibanx = null;
                    values.Chushox = null;
                    values.Chubanx = null;
                    values.Shobanx = null;
                    values.Bubanxx = null;
                    values.SchYno = null;
                    values.SchBno = null;
                } else {
                    SeibanxComp = $.padLeft($.trim(values.Seibanx), 7);
                }

                //リスト検索
                vue.getXbanList({ Seibanx: SeibanxComp }, () => {
                    if (!_.isEqual(values.Seibanx, vue.selection.Seibanx)) return;

                    if (vue.XbanList.length == 1) {
                        //1件の場合はその内容を選択
                        var Xban = _.cloneDeep(vue.XbanList[0]);

                        //同じ値が復活しないように考慮
                        Xban = $.extend(Xban, df);
                        Xban.Seibanx = SeibanxComp;

                        //単一条件指定時
                        if (vue.isUniqCond && !!df.Seibanx) {
                            Xban.Seizono = "";
                            Xban.Orderno = "";
                        }

                        vue.selectionPrev = _.cloneDeep(Xban);
                        vue.selection = _.cloneDeep(Xban);

                    } else {
                        values.Seibanx = SeibanxComp;

                        values.Daibanx = null;
                        values.Chushox = null;
                        values.Chubanx = null;
                        values.Shobanx = null;
                        values.Bubanxx = null;
                        values.SchYno = null;
                        values.SchBno = null;

                        if (vue._noRelation != true) {
                            values.Seizono = "";
                            values.Orderno = "";
                        }

                        vue.selectionPrev = _.cloneDeep(values);
                        vue.selection = _.cloneDeep(values);
                    }

                    //大番以下のリスト更新
                    vue.updateList();

                    if (vue._noRelation != true) {
                        //製造指図書番号及び手配番号の検索パラメータ更新
                        vue.SeizonoParams = {
                            Seibanx: vue.selection.Seibanx,
                            Daibanx: vue.selection.Daibanx,
                            Chubanx: vue.selection.Chubanx,
                            Shobanx: vue.selection.Shobanx,
                        };
                        vue.OrdernoParams = {
                            Seibanx: vue.selection.Seibanx,
                            Daibanx: vue.selection.Daibanx,
                            Chubanx: vue.selection.Chubanx,
                            Shobanx: vue.selection.Shobanx,
                        };
                    }

                    //呼出元のViewModelに反映
                    vue.reflectViewModel(vue.selection);

                    //変更時関数が指定されている場合、実行
                    if (vue.onChangeFunc) {
                        vue.onChangeFunc(vue.$el, vue.selection);
                    }
                });

                return;

            } else if (_.has(df, "Seizono")) {
                //製造指図書番号変更時

                if (vue._noRelation == true) {
                    //非連動時

                    //単一条件指定時
                    if (vue.isUniqCond && !!df.Seizono) {
                        values.Seibanx = null;
                        values.Daibanx = null;
                        values.Chushox = null;
                        values.Chubanx = null;
                        values.Shobanx = null;
                        values.Bubanxx = null;
                        values.SchYno = null;
                        values.SchBno = null;
                        values.Orderno = null;
                    }

                    vue.noWatch = true;
                    vue.selectionPrev = _.cloneDeep(values);
                    vue.selection = _.cloneDeep(values);
                    vue.noWatch = false;
                } else {
                    //連動時
                    var params = {"Seizono": df.Seizono};

                    //リスト検索
                    vue.getXbanList(params, () => {
                        if (vue.XbanList.length == 1) {
                            //1件の場合はその内容を選択
                            var Xban = _.cloneDeep(vue.XbanList[0]);

                            //同じ値が復活しないように考慮
                            Xban = $.extend(Xban, df);

                            vue.selectionPrev = _.cloneDeep(Xban);
                            vue.selection = _.cloneDeep(Xban);

                            //製造指図書番号の検索パラメータ更新
                            vue.SeizonoParams = {
                                Seibanx: vue.selection.Seibanx,
                                Daibanx: vue.selection.Daibanx,
                                Chubanx: vue.selection.Chubanx,
                                Shobanx: vue.selection.Shobanx,
                            };

                        } else {
                            vue.selectionPrev = _.cloneDeep(values);
                            vue.selection = _.cloneDeep(values);

                            //製造指図書番号の検索パラメータ更新
                            vue.SeizonoParams = {"Keyword": df.Seizono};
                        }

                        //大番以下のリスト更新
                        vue.updateList();

                        //呼出元のViewModelに反映
                        vue.reflectViewModel(vue.selection);

                        //変更時関数が指定されている場合、実行
                        if (vue.onChangeFunc) {
                            vue.onChangeFunc(vue.$el, vue.selection);
                        }
                    });

                    return;
                }
            } else if (_.has(df, "Orderno")) {
                //手配番号変更時

                if (vue._noRelation == true) {
                    //非連動時

                    //単一条件指定時
                    if (vue.isUniqCond && !!df.Orderno) {
                        values.Seibanx = null;
                        values.Daibanx = null;
                        values.Chushox = null;
                        values.Chubanx = null;
                        values.Shobanx = null;
                        values.Bubanxx = null;
                        values.SchYno = null;
                        values.SchBno = null;
                        values.Seizono = null;
                    }

                    vue.noWatch = true;
                    vue.selectionPrev = _.cloneDeep(values);
                    vue.selection = _.cloneDeep(values);
                    vue.noWatch = false;
                } else {
                    //連動時
                    var params = {"Keyword": df.Orderno};

                    //リスト検索
                    vue.getXbanList(params, () => {
                        if (vue.XbanList.length == 1) {
                            //1件の場合はその内容を選択
                            var Xban = _.cloneDeep(vue.XbanList[0]);

                            //同じ値が復活しないように考慮
                            Xban = $.extend(Xban, df);

                            vue.selectionPrev = _.cloneDeep(Xban);
                            vue.selection = _.cloneDeep(Xban);

                            //手配番号の検索パラメータ更新
                            vue.OrdernoParams = {
                                Seibanx: vue.selection.Seibanx,
                                Daibanx: vue.selection.Daibanx,
                                Chubanx: vue.selection.Chubanx,
                                Shobanx: vue.selection.Shobanx,
                            };

                        } else {
                            vue.selectionPrev = _.cloneDeep(values);
                            vue.selection = _.cloneDeep(values);

                            //手配番号の検索パラメータ更新
                            vue.OrdernoParams = {"Keyword": df.Orderno};
                        }

                        //大番以下のリスト更新
                        vue.updateList();

                        //呼出元のViewModelに反映
                        vue.reflectViewModel(vue.selection);

                        //変更時関数が指定されている場合、実行
                        if (vue.onChangeFunc) {
                            vue.onChangeFunc(vue.$el, vue.selection);
                        }
                    });

                    return;
                }
            } else if (df.Chushox) {
                //中小番の変更時
                //中番/小番の整合性を取る為の編集
                vue.selection.Chubanx = values.Chushox ? values.Chushox.slice(0, 2) : null;
                vue.selection.Shobanx = values.Chushox ? values.Chushox.slice(-2) : null;
            } else {
                //その他変更時
                //中小番及びYNO/BNOの整合性を取る為の編集
                if (values.Chubanx && values.Shobanx) vue.selection.Chushox = values.Chubanx + values.Shobanx;
                if (values.Shobanx && values.Bubanxx) vue.selection.SchYno = values.Shobanx.slice(-1) + values.Bubanxx.slice(0, 2);
                if (values.Bubanxx && values.Edabanx) vue.selection.SchBno = values.Bubanxx.slice(-1) + values.Edabanx;
            }

            if (vue._noRelation != true) {
                //大番以下のリスト更新
                vue.updateList();

                //製造指図書番号及び手配番号の検索パラメータ更新
                vue.SeizonoParams = {
                    Seibanx: vue.selection.Seibanx,
                    Daibanx: vue.selection.Daibanx,
                    Chubanx: vue.selection.Chubanx,
                    Shobanx: vue.selection.Shobanx,
                };
                vue.OrdernoParams = {
                    Seibanx: vue.selection.Seibanx,
                    Daibanx: vue.selection.Daibanx,
                    Chubanx: vue.selection.Chubanx,
                    Shobanx: vue.selection.Shobanx,
                };
            }

            vue.selectionPrev = _.cloneDeep(vue.selection);

            //呼出元のViewModelに反映
            vue.reflectViewModel(vue.selection);

            //変更時関数が指定されている場合、実行
            if (this.onChangeFunc) {
                this.onChangeFunc(this.$el, vue.selection);
            }
        }, 300),
        getXbanList: function (params, callback) {
            var vue = this;
            var url = "/Utilities/GetXbanList";

            axios.post(url, params)
                .then(response => {
                    var res = response.data

                    if (!!res && (res.onError || res.onException)) {
                        vue.XbanList = [];

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

                    vue.XbanList = res.map(v => {
                        //v.Chushox = v.Chubanx + v.Shobanx;
                        if (v.Chubanx == null ||v.Shobanx == null) {
                            return v;
                        } else {
                            v.Chushox = v.Chubanx + v.Shobanx;
                        }
                        v.SchYno  = (!!v.Shobanx && v.Shobanx.length > 1 && !!v.Bubanxx && v.Bubanxx.length > 1) ? (v.Shobanx.slice(-1) + v.Bubanxx.slice(0, 2)) : null;
                        v.SchBno  = (!!v.Bubanxx && v.Bubanxx.length > 1 && !!v.Edabanx) ? (v.Bubanxx.slice(-1) + v.Edabanx) : null;

                        return v;
                    });

                    if (callback) callback();
                })
                .catch(error => {
                    vue.XbanList = [];

                    //エラーダイアログ
                    $.dialogErr({
                        title: error.message
                    });
                    console.log(error);

                    //他コンポーネントに通知
                    vue.$root.$emit("addMessage", url + "で例外発生" + JSON.stringify(params));
                });
        },
        extractList: function(xban) {
            var XbanList = this.XbanList;
            var selection = this.selection;

            var result = [];

            switch (xban) {
                case "Daibanx":
                case "Chubanx":
                case "Shobanx":
                case "Bubanxx":
                    //大番/中番/小番/部番選択リスト抽出
                    result = _.uniq(
                        XbanList.filter(v => {
                            return  (v.Seibanx == selection.Seibanx) &&
                                    (xban == "Daibanx" ? true
                                        : (v.Daibanx == (selection.Daibanx || v.Daibanx) &&
                                            (xban == "Chubanx" ? true
                                                : (v.Chubanx == (selection.Chubanx || v.Chubanx) &&
                                                    (xban == "Shobanx" ? true
                                                        : (v.Shobanx == (selection.Shobanx || v.Shobanx) &&
                                                            (xban == "Bubanxx" ? true
                                                                : v.Bubanxx == (selection.Bubanxx || v.Bubanxx)
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                    ;
                        })
                        .map(v => v[xban])
                        .filter(v => !$.isEmptyObject($.trim(v)))
                    ).sort();

                    break;

                case "Chushox":
                    //中小番選択リスト抽出
                    result = _.uniq(
                        XbanList.filter(v => {
                            return  v.Seibanx == selection.Seibanx && v.Daibanx == (selection.Daibanx || v.Daibanx);
                        })
                        .map(v => v.Chushox)
                        .filter(v => !$.isEmptyObject($.trim(v)))
                    ).sort();

                    break;

                case "SchYno":
                    //YNO選択リスト抽出
                    result = _.uniq(
                        XbanList.filter(v => {
                            return  v.Seibanx == selection.Seibanx && v.Daibanx == (selection.Daibanx || v.Daibanx);
                        })
                        .map(v => v.SchYno)
                        .filter(v => !$.isEmptyObject($.trim(v)))
                    ).sort();

                    break;

                case "SchBno":
                    //BNO選択リスト抽出
                    result = _.uniq(
                        XbanList.filter(v => {
                            return  v.Seibanx == selection.Seibanx
                                && v.Daibanx == (selection.Daibanx || v.Daibanx)
                                    && v.SchYno == (selection.SchYno || v.SchYno);
                        })
                        .map(v => v.SchBno)
                        .filter(v => !$.isEmptyObject($.trim(v)))
                    ).sort();

                    break;

            }

            //リストに存在しない選択値はクリア
            if (!_.isEmpty($.trim(selection[xban])) && !result.includes(selection[xban])) {
                selection[xban] = null;
            }

            return result;
        },
        updateList: function() {
            this.DaibanxList = this.extractList("Daibanx").map(v => { return { code: v, name: v }; });
            this.ChushoxList = this.extractList("Chushox").map(v => { return { code: v, name: v }; });
            this.ChubanxList = this.extractList("Chubanx").map(v => { return { code: v, name: v }; });
            this.ShobanxList = this.extractList("Shobanx").map(v => { return { code: v, name: v }; });
            this.BubanxxList = this.extractList("Bubanxx").map(v => { return { code: v, name: v }; });
            this.SchYnoList  = this.extractList("SchYno").map(v => { return { code: v, name: v }; });
            this.SchBnoList  = this.extractList("SchBno").map(v => { return { code: v, name: v }; });
        },
        reset: function() {
            _.forOwn(this.selection, (v, k) => this.selection[k] = k == "noWatch" ? v : "");
        },
        reflectViewModel: function(selection) {
            var vue = this;

            if (!!vue.vmodel) {
                _.forIn(vue.selection, (v, k) => {
                    var val = vue.isShow(k) ? v : null;

                    if (_.trim(vue.vmodel[k]) != _.trim(val)) {
                        vue.vmodel[k] = val;
                    }
                });
            }
        },
        isShow: function(name) {
            return (!this.includes || this.includes == 0 || this.includes.includes(name))
                    &&
                   (!this.excludes || this.excludes == 0 || !this.excludes.includes(name))
        },
        includesChanged: _.debounce(function(values) {
            var vue = this;
            vue.includesPrev = values;

            //bindingされているobjectへの値反映
            vue.reflectViewModel(vue.selection);

        }, 300),
        excludesChanged: _.debounce(function(values) {
            var vue = this;
            vue.excludesPrev = values;

            //bindingされているobjectへの値反映
            vue.reflectViewModel(vue.selection);

        }, 300),
    }
}
</script>
