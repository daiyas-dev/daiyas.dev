<template>
    <div :style="_style" class="form-group d-inline-flex align-items-center VueMultiSelect" :data-tip="isExists ? null : '選択可能な一覧がありません'">
        <label v-if="title" class="" :for="_id">{{title}}</label>
        <multiselect
            :id="_id"
            :class="id"
            v-model="selected"
            label="label"
            track-by="code"
            :options="entities"
            :searchable="false"
            :close-on-select="false"
            :multiple="true"
            :taggable="true"
            :disabled=disabled
            :allowEmpty=hasNull
            :preselectFirst=!hasNull
            @input="onChanged"
            placeholder=""
            selectLabel="選択"
            selectedLabel="選択中"
            deselectLabel="選択解除"
        >
        </multiselect>
    </div>
</template>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>
</style>

<style>
.VueMultiSelect {
    min-width: 300px;
}
.multiselect {
    display: block;
    position: relative;
    min-height: unset;
    text-align: left;
    color: black;
}
.multiselect__select {
    width: 20px;
    height: 30px;
    padding: 2px;
}
.multiselect__tags {
    height: 30px;
    min-height: unset;
    display: block;
    padding: 3px 20px 0 2px;
    border-radius: 4px;
    background: #fff;
}
</style>

<script>
import Multiselect from "vue-multiselect";

export default {
    name: "VueMultiSelect",
    components: {
        Multiselect
    },
    data() {
        return {
            entities: [],
            selected: null,
        }
    },
    props: {
        title: String,
        id: String,
        codeName: String,
        textName: String,
        bind: String,
        buddy: String,
        vmodel: Object,
        hasNull: Boolean,
        uri: String,
        params: Object,
        list: Array,
        func: Function,
        onLoadFunc: Function,
        onChangedFunc: Function,
        withCode: Boolean,
        customStyle: String,
        disabled: Boolean,
        ParamsChangedCheckFunc: Function,
        isShowInvalid: Boolean,
    },
    watch: {
        selected: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                vue.vmodel[vue.bind] = newVal;
            },
        },
        params: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                // console.log("VueMultiSelect param watcher", newVal);

                if (!_.isEqual(newVal, vue.paramsPrev, (v, o) => v == o)) {
                    if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(newVal, vue.paramsPrev)) {
                        return;
                    }

                    vue.paramsPrev = _.cloneDeep(newVal);
                    vue.setEntities();
                }
            },
        },
        list: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                vue.setEntities();
            },
        },
        entities: {
            deep: true,
            handler: function(newVal) {
                var comp = this;
                if (this.onLoadFunc) {
                    this.onLoadFunc(comp, newVal);
                }
                if (!this.hasNull && newVal.length && !_.find(newVal, { code: this.vmodel[this.bind]})) {
                    // console.log("VueMultiSelect eintities watcher invalid code", this.vmodel[this.bind]);
                    if (this.isShowInvalid) {
                        this.entities.unshift({
                            code: this.vmodel[this.bind],
                            name: null,
                            label: this.vmodel[this.bind] + ":該当無し",
                            invalid: true,
                        });
                        $(this.$el).addClass("has-error");
                        this.invalid = true;
                    } else {
                        this.vmodel[this.bind] = newVal[0].code;
                        this.invalid = newVal[0].invalid;
                        if (this.buddy) {
                            this.vmodel[this.buddy] = newVal[0].name;
                        }
                    }

                    if (this.onChangedFunc) {
                        this.onChangedFunc(null, newVal);
                    }
                }
            }
        },
    },
    computed: {
        isExists: function () {
            return (this.entities && this.entities.length && typeof this.entities == "object");
        },
        _id: function() {
            return this.id + "_" + this._uid;
        },
        _style: function() {
            return this.customStyle || "width: unset;";
        },
    },
    created: function () {
        var vue = this;

        vue.$root.$on("plantChanged", vue.plantChanged);
        vue.$root.$on("accountChanged", vue.accountChanged);
    },
    mounted: function () {
        var vue = this;
        if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(vue.params, vue.params)) {
            return;
        }

        vue.setEntities();
    },
    methods: {
        plantChanged: function() {
            this.setEntities();
        },
        accountChanged: function() {
        },
        onChanged: function (event) {
            var vue = this;
            // console.log("vue-multiselect onchanged", vue.selected)

            //変更時関数が指定されていれば呼出
            if (vue.onChangedFunc) {
                vue.onChangedFunc(vue.selected, vue.entities);
            }

            return false;
        },
        setEntities: function () {
            if (this.list) {
                this.entities = this.list;
            } else if (this.uri) {
                this.getList(this.uri, this.params, this);
            } else if (this.func) {
                this.entities = this.func.callee(this.func.params);
            }
        },
        getList: function (uri, params, component) {
            var vue  = this;

            axios.post(uri, params)
                .then(response => {
                    if (response.data && (response.data.onError || response.data.onException)) {
                        //view更新
                        component.entities = [];

                        return;
                    } else if (response.data.CountConstraint) {
                        //件数制約違反設定
                        component.CountConstraint = response.data.CountConstraint;

                        response.data = response.data.Data;
                    }

                    var entities = $.map(response.data, function (v, i) {

                        var code = v[component.codeName || "Cd"];
                        var name = v[component.textName || "CdNm"];
                        // var text = (component.withCode ? (code + ":") : "") + v[component.textName || "CdNm"];
                        var text = (component.withCode ? (code + ":") : "") + ( v[component.textName || "CdNm"] == null ? "" : v[component.textName || "CdNm"]);

                        return {
                            code: code,
                            name: name,
                            label: text,
                            info: v,
                            invalid: false,
                        };
                    });

                    //view更新
                    // console.log("VueMultiSelect set entities", entities);
                    component.entities = entities;
                })
                .catch(error => {
                    console.log(uri + " Error!");
                    console.log(error);

                    //他コンポーネントに通知
                    component.$root.$emit("addMessage", uri + "で例外発生" + JSON.stringify(params));

                    //view更新
                    component.entities = [];
                });
        },
        focus: function() {
            var vue = this;

            var select = $(vue.$el).find("select");
            if (select.is(":disabled")) {
                return false;
            } else {
                select.focus();
                return true;
            }
        },
        // addTag: function(newTag) {
        //     const tag = {
        //         name: newTag,
        //         code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
        //     }
        //     this.options.push(tag)
        //     this.value.push(tag)
        // }
    }
}
</script>
