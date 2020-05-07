//required bootstrap css
<template>
    <div class="form-group d-inline-flex align-items-center VueCheckList" :style="_containerStyle" :data-tip="isExists ? null : '選択可能な一覧がありません'">
        <label v-if="title" class="title" :style="_titleStyle">{{title}}</label>
        <div class="d-flex pl-1 pr-1" style="border-style: groove">
            <div v-for="entity in entities" class="radio"
                v-bind:key="entity.code"
            >
                <label class="content" :style="_contentStyle">
                    <input type="checkbox"
                        :disabled=disabled
                        :value=entity.code
                        :name=entity.name
                        :checked=entity.checked
                        @change="onChanged"
                    >{{entity.label}}</label>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "VueCheckListList",
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
        uri: String,
        params: Object,
        list: Array,
        func: Function,
        onChangedFunc: Function,
        isGetName: Boolean,
        withCode: Boolean,
        customContainerStyle: String,
        customTitleStyle: String,
        customContentStyle: String,
        disabled: Boolean,
        ParamsChangedCheckFunc: Function,
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
        entities : {
            deep: true,
            sync: true,
            handler: function(newVal) {
                var vue = this;
                // console.log("VueCheckList entities watcher");
                vue.entities.forEach(e => e.checked = vue.bindValues.includes(e.code));
            },
        },
    },
    computed: {
        bindValues: function() {
            var vue = this;
            return vue.vmodel[vue.bind];
        },
        isExists: function () {
            return (this.entities && this.entities.length && typeof this.entities == "object");
        },
        _id: function() {
            return this.id + "_" + this._uid;
        },
        _containerStyle: function() {
            return this.customContainerStyle || "";
        },
        _titleStyle: function() {
            return this.customTitleStyle || "width: 60px";
        },
        _contentStyle: function() {
            return this.customContentStyle || "width: auto; min-width: auto";
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

            var selection = $(vue.$el).find(":checkbox")
                .filter((i,v) => v.checked)
                .map((i,v) => vue.isGetName ? v.name :v.value)
                .get();
            vue.vmodel[vue.bind] = selection;

            //変更時関数が指定されていれば呼出
            if (vue.onChangedFunc) {
                vue.onChangedFunc(selection);
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
.VueCheckList {
    display: inline-flex;
    align-items:center;
}
.VueCheckList label {
    display: flex;
    margin: 0px;
    text-align: left;
    vertical-align: middle;
}
.VueCheckList label.content.Checked {
    font-weight: bold;
}
.VueCheckList [type=checkbox] {
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
