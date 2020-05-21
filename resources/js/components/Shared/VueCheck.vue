//required bootstrap css
<template>
    <div
        class="form-group d-inline-flex align-items-center VueCheck"
         :class="[ _checked == true ? 'Checked' : '' ]"
        :style="_containerStyle"
        :data-tip="isExists ? null : '選択可能な一覧がありません'"
    >
        <label v-if="title" class="title" :style="_titleStyle">{{title}}</label>
        <label class="content" :style="_contentStyle" :class="[ _checked == true ? 'Checked' : '' ]">
            <input type="checkbox"
                class="form-control"
                v-model="vmodel[bind]"
                :true-value="_trueValue"
                :false-value="_falseValue"
                :name="bind"
                :disabled=disabled
                @change="onChanged"
            >{{_valueLabel}}</label>
    </div>
</template>

<script>
export default {
    name: "VueCheck",
    data() {
        return {
            entities: [],
            CountConstraint: null,
            paramPrev: null,
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
        onChangedFunc: Function,
        withCode: Boolean,
        customContainerStyle: String,
        customTitleStyle: String,
        customContentStyle: String,
        disabled: Boolean,
        ParamsChangedCheckFunc: Function,
        checkedCode: String,
    },
    watch: {
        params: {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                //console.log("VueSelect param watcher", newVal);

                if (!_.isEqual(newVal, vue.paramsPrev, (v, o) => v == o)) {
                    if (vue.ParamsChangedCheckFunc && !vue.ParamsChangedCheckFunc(newVal, vue.paramsPrev)) {
                        return;
                    }

                    vue.paramsPrev = _.cloneDeep(newVal);
                    vue.setEntities();
                }
            },
        },
        entities: {
            deep: true,
            handler: function(newVal) {
                if (!this.hasNull && newVal.length && !_.find(newVal, { code: this.vmodel[this.bind]})) {
                    this.vmodel[this.bind] = newVal[0].code;
                    if (this.buddy) {
                        this.vmodel[this.buddy] = newVal[0].name;
                    }

                    if (this.onChangedFunc) {
                        this.onChangedFunc(newVal[0].code, newVal);
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
        _containerStyle: function() {
            return this.customContainerStyle || "border-style: groove;";
        },
        _titleStyle: function() {
            return this.customTitleStyle || "width: 60px";
        },
        _contentStyle: function() {
            return this.customContentStyle || "width: auto; min-width: auto";
        },
        _checkedCode: function() {
            var vue = this;
            return vue.checkedCode || "1";
        },
        _checked: function() {
            var vue = this;
            return vue.vmodel[vue.bind] === vue._checkedCode;
        },
        _trueValue: function() {
            var vue = this;
            return vue._checkedCode;
        },
        _falseValue: function() {
            var vue = this;
            return vue.entities.length ? (vue.entities.find(v => v.code != vue._checkedCode) || {}).code : false;
        },
        _valueLabel: function() {
            var vue = this;
            return vue.entities.length ? (vue.entities.find(v => v.code === vue.vmodel[vue.bind]) || {}).label : "";
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
            this.setEntities();
        },
        accountChanged: function() {
        },
        onChanged: function (event) {
            var vue = this;

            var code = $(event.target).attr(event.target.checked + "-value");

            if (vue.buddy) {
                vue.vmodel[vue.buddy] = _.find(vue.entities, v => v.code == code).name;
            }

            //変更時関数が指定されていれば呼出
            if (vue.onChangedFunc) {
                vue.onChangedFunc(code, vue.entities);
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
                        var text = (component.withCode ? (code + ":") : "") + v[component.textName || "CdNm"];

                        return {
                            code: code,
                            name: name,
                            label: text,
                            info: v,
                        };
                    });

                    //view更新
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
    }
}
</script>

<style scoped>
.VueCheck {
    display: inline-flex;
    align-items:center;
}
.VueCheck label {
    display: flex;
    margin: 0px;
    text-align: left;
    vertical-align: middle;
}
.VueCheck label.content.Checked {
    font-weight: bold;
}
.VueCheck [type=checkbox] {
    width: 20px;
    height: 20px;
    margin-right: 5px;
}

</style>

<style>
[data-tip] {
    position: relative;
}

[data-tip]:before {
    content: '';
    display: none;
    content: '';
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-bottom: 5px solid darkorange;
    position: absolute;
    top: 30px;
    left: 50px;
    z-index: 8;
    font-size: 0;
    line-height: 0;
    width: 0;
    height: 0;
}

[data-tip]:after {
    display: none;
    content: attr(data-tip);
    position: absolute;
    font-family: inherit;
    font-size: 14px;
    top: 35px;
    left: 0px;
    padding: 0px 5px;
    background: darkorange;
    color: #fff;
    z-index: 9;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    white-space: nowrap;
    word-wrap: normal;
}

[data-tip]:not(.has-error):hover:before,
[data-tip]:not(.has-error):hover:after {
    display: block;
}
</style>
