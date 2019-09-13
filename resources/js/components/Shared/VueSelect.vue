//required bootstrap css
<template>
    <div class="form-group d-inline-flex align-items-center VueSelect" :data-tip="isExists ? null : '選択可能な一覧がありません'">
        <label v-if="title" class="" :for="_id">{{title}}</label>
        <select class="form-control" :id="_id" v-model="vmodel[bind]" @change="onChanged"
            style="padding-top: 2px; padding-left: 2px; padding-bottom: 2px;">
            <option v-show="hasNull" value=""></option>
            <template v-if="entities && entities.length">
                <option v-for="entity in entities"
                    v-bind:key="entity.code"
                    v-bind:value="entity.code"
                    :selected="vmodel[bind] == entity.code">
                    {{entity.name}}
                </option>
            </template>
        </select>
    </div>
</template>

<script>
export default {
    name: "VueSelect",
    data() {
        return {
            entities: [],
            CountConstraint: null,
        }
    },
    props: {
        title: String,
        id: String,
        codeName: String,
        textName: String,
        bind: String,
        vmodel: Object,
        hasNull: Boolean,
        uri: String,
        params: Object,
        list: Array,
        func: Function,
        changed: Function,
        withCode: Boolean,
    },
    watch: {
        entities: {
            deep: true,
            handler: function(newVal) {
                if (!this.hasNull && newVal.length && !_.find(newVal, { code: this.vmodel[this.bind]})) {
                    this.vmodel[this.bind] = newVal[0].code;
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
    },
    created: function () {
        this.$root.$on("plantChanged", this.plantChanged);
        this.$root.$on("accountChanged", this.accountChanged);
    },
    mounted: function () {
        this.setEntities();
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
            //変更時関数が指定されていれば呼出
            if (this.changed) {
                this.changed(event);
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
                        var text = (component.withCode ? (code + ":") : "") + v[component.textName || "CdNm"];

                        return {
                            code: code,
                            name: text,
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
.VueSelect {
    display: inline-flex;
    align-items:center;
}
.VueSelect label {
    display: inline;
    margin: 0px;
    text-align: left;
    vertical-align: middle;
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
