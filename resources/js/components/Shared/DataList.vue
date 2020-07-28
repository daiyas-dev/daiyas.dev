//required bootstrap css
<template>
    <div :data-tip="isExists ? null : '選択可能な一覧がありません'" class="VueDataList">
        <label v-if="title" :for="idSelector">{{title}}</label>
        <input type="text" class="form-control data-input" autocomplete="off"
            :id="idSelector"
            :style="css"
            :list="(entities && entities.length) ? (id + 'List') : ''"
            @change="onChanged"
        />
        <select class="form-control selector"></select>
        <input type="hidden" :id="id" :value="value" />
        <input type="hidden" v-if="buddyId" :id="buddyId" />
    </div>
</template>

<script>
    export default {
        name: "VueDataList",
        data() {
            return {
                entities: [],
                CountConstraint: null,
            }
        },
        props: {
            title: String,
            id: String,
            buddy: String,
            vmodel: Object,
            value: String,
            codeName: String,
            textName: String,
            labelText: String,
            css: String,
            list: Array,
            dataUrl: String,
            func: Function,
            params: Object,
            changed: Function,
            listHeight: String,
        },
        computed: {
            isExists: function () {
                return (this.entities && this.entities.length && typeof this.entities == "object");
            },
            idSelector: function() {
                return this.id + "_selector";
            },
            buddyId: function () {
                return this.buddy ? this.buddy
                        :this.id.includes("Cd") ? (this.id.replace(/Cd$/, "") + "NM")
                            : this.id.includes("NM") ? (this.id.replace(/NM$/, "") + "Cd")
                                : (this.id + "NM");
            },
            bindValue: function() {
                return this.$parent.viewModel ? this.$parent.viewModel[this.id] : null;
            },
            bindBuddyValue: function() {
                return this.$parent.viewModel ? this.$parent.viewModel[this.buddyId] : null;
            },
            "--listHeight": function() {
                return this.listHeight;
            },
        },
        watch: {
            bindValue: {
                handler: function(newVal) {
                    var vue = this;

                    //値を設定
                    var item = vue.entities.filter(v => v.value == newVal)[0];
                    if (item) {
                        $(vue.$el).find("#" + vue.idSelector).val(item.label || item.text);
                        $(vue.$el).find("#" + vue.id).val(item.value);
                        $(vue.$el).find("#" + vue.buddyId).val(item.text);
                    }

                    //onChange発火
                    //vue.onChanged(event);
                },
            },
            entities: {
                handler: function(newVal) {
                    var vue = this;

                    //値を設定
                    var item = newVal.filter(v => v.value == vue.bindValue)[0];

                    if (item) {
                        $(vue.$el).find("#" + vue.idSelector).val(item.label || item.text);
                        $(vue.$el).find("#" + vue.id).val(item.value);
                        $(vue.$el).find("#" + vue.buddyId).val(item.text);
                    }

                    //onChange発火
                    //vue.onChanged(event);
                },
            },
        },
        created: function () {
            //console.log(this.$options.name + " Created:");

            this.$root.$on("plantChanged", this.plantChanged);
            this.$root.$on("accountChanged", this.accountChanged);
        },
        mounted: function () {
            var vue = this;
            //console.log(this.$options.name + " Mounted:");
            //var dt = new Date()
            //console.log(this.id + " mounted:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());

            //show selector
            var $div = $(vue.$el);
            var $input = $div.find("#" + vue.idSelector);
            $div.find(".selector")
                .css("position", "absolute")
                .css("top", $div.position().top + "px")
                .css("left", $div.outerWidth() - 10 + "px")
                .css("width", "20px")
                .css("height", $input.outerHeight() + "px")
                .css("display", "block")
                ;

            vue.setEntities();
        },
        beforeUpdated: function () {
            //console.log(this.$options.name + " BeforeUpdated:");
        },
        updated: function () {
            //var dt = new Date()
            //console.log(this.id + " updated:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());
        },
        activated: function () {
            //console.log(this.$options.name + " Activated:");
        },
        deactivated: function () {
            //console.log(this.$options.name + " Deactivated:");
        },
        destroyed: function () {
            //console.log(this.$options.name + " Destroyed:");
        },
        methods: {
            plantChanged: function() {
                this.setEntities();
            },
            accountChanged: function() {
            },
            onChanged: function (event) {
                console.log(this.$options.name + " onChanged:");

                if (this.changed) {
                    this.changed(event, $("#" + this.id).val());
                }

                //呼出元Vueコンポーネントのidと紐づくdataとbind
                if (this.$parent && this.$parent.viewModel) {
                    this.$parent.$set(this.$parent.viewModel, this.id, $("#" + this.id).val());
                    this.$parent.$set(this.$parent.viewModel, this.buddyId, $("#" + this.buddyId).val());
                    this.$parent.$forceUpdate();
                }

                return false;
            },
            setEntities: function () {
                //console.log(this.$options.name + " Computed entities:");

                if (this.list) {
                    this.entities = this.list;
                } else if (this.dataUrl) {
                    this.getList(this.dataUrl, this.params, this);
                } else if (this.func) {
                    this.entities = this.func.callee(this.func.params)
                }

                //jQuery Autocomplte生成
                this.createAutoComplete();
            },
            getList: function (dataUrl, params, component) {
                //var dt = new Date()
                //console.log(this.id + " getList:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());

                axios.post(dataUrl, params)
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

                        component.entities = $.map(response.data, function (v, i) {

                            var code = v[component.codeName || "Cd"];
                            var text = v[component.textName || "CdNm"];

                            var label = text;

                            if (component.labelText) {
                                label = eval(component.labelText);
                            }

                            return {
                                value: code,
                                text: text,
                                label: label,
                            };
                        });

                        //var dt = new Date()
                        //console.log(this.id + " getList End:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());
                        //jQuery Autocomplte生成
                        component.createAutoComplete();
                    })
                    .catch(error => {
                        console.log(dataUrl + " Error!");
                        console.log(error);

                        //console.log(error)
                        //他コンポーネントに通知
                        component.$root.$emit("addMessage", dataUrl + "で例外発生" + JSON.stringify(params));

                        component.entities = [];
                    });
            },
            createAutoComplete: function() {
                var vue = this;
                //var dt = new Date()
                //console.log(this.id + " createAutoComplete Start:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());

                //jQuery Autocomplte生成
                var widget = $("#" + this.idSelector).autocomplete({
                    vue: this,
                    source: this.entities,
                    appendTo: $("#" + this.idSelector).parent(),
                    select : function(event, ui) {
                        var vue = $(this).autocomplete("option").vue;

                        //選択した値を設定
                        $(this).val(ui.item.label);
                        $("#" + vue.id).val(ui.item.value);
                        $("#" + vue.buddyId).val(ui.item.text);

                        //onChange発火
                        vue.onChanged(event);

                        return false;
                    },
                    position: { my: "left top", at: "left bottom", collision: "flip" },
                    autoFocus: false,
                    minLength: 0,
                })
                    .focus(function() {
                        $(this).autocomplete("search", $(this).val());
                        $(this).autocomplete("widget").css("max-height", vue.listHeight || "calc(30vh / var(--ratio))");
                    })
                    .keydown(function(event) {
                        if (event.keyCode == 38 || event.keyCode == 40) { //↑↓キー
                            //フォーカスの当たっている項目から値を取得
                            var label = $(".ui-state-active", $("ul.ui-autocomplete:visible")).text();
                            var temp = $(this).autocomplete("option").source.filter(v => v.label == label);
                            var value = temp.length == 1 ? temp[0].value : null;
                            var text = temp.length == 1 ? temp[0].text : null;

                            //入力項目に値設定
                            var vue = $(this).autocomplete("option").vue;
                            $(this).val(label);
                            $("#" + vue.id).val(value);
                            $("#" + vue.buddyId).val(text);
                        }
                    })
                    .change(function() {
                        console.log("autocomplete change");
                        var vue = $(this).autocomplete("option").vue;

                        if (!$(this).val()) {
                            //未入力は保持している選択値クリア
                            $("#" + vue.id).val("");
                            $("#" + vue.buddyId).val("");
                        } else {
                            //入力されている場合
                            var source = $(this).autocomplete("option").source;
                            if (source.length > 0) {
                                var item = source.filter(v => v.label == this.value);
                                if (item.length == 1) {
                                    //リストに一致する場合、選択値を設定
                                    $("#" + vue.id).val(item[0].value);
                                    $("#" + vue.buddyId).val(item[0].text);
                                } else {
                                    //不一致の場合、入力値をそのまま設定
                                    $("#" + vue.id).val(this.value);
                                    $("#" + vue.buddyId).val(this.value);
                                }
                            }
                        }
                        //onChange発火
                        vue.onChanged(event);
                    });

                //初期設定
                var source = widget.autocomplete("option").source;
                if (this.value && source.length > 0) {
                    var item = source.filter(v => v.value == this.value);
                    if (item.length == 1) {
			            widget.data("ui-autocomplete")._trigger("select", "autocompleteselect", {item: item[0]});
                    } else {
                        $(this.$el).find("#" + this.idSelector).val(this.value);
                        $(this.$el).find("#" + this.id).val(this.value);
                        $(this.$el).find("#" + this.buddyId).val(this.value);
                    }
                }

                //dt = new Date()
                //console.log(this.id + " createAutoComplete End:" + dt.toLocaleTimeString() + "." + dt.getMilliseconds());
            },
        }
    }
</script>
<style scoped>
.data-input {
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.selector {
    display: none;
    width: 15px !important;
    padding: none;
    border-left-width: 0;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
}
</style>
<style>
.ui-autocomplete {
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 20px;
}
#jquery-ui-autocomplete label {
    float: left;
    margin-right: 5px;
    color: black;
    font-size: 14px;
}

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
