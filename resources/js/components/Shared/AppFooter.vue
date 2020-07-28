<template>
    <footer class="AppFooter">
        <nav id="footer-bar" class="navbar navbar-expand navbar-inverse navbar-dark bg-dark w-100 pt-0 pb-0">
            <div v-if="this.buttons && this.buttons.length" class="w-100 collapse navbar-collapse">
                <ul class="navbar-nav mr-auto">
                    <li v-for="button in this.buttonsOnLeft" v-bind:key="button.id" class="nav-item">
                        <button type="button"
                                :id="button.id ? button.id : null"
                                :disabled="button.disabled"
                                :class=button.class
                                @click="button.onClick"
                                v-html="button.value ? button.value : ''"
                        />
                    </li>
                </ul>
                <ul class="navbar-nav navbar-right">
                    <li v-for="button in this.buttonsOnRight" v-bind:key="button.id" class="nav-item">
                        <button type="button"
                                :id="button.id ? button.id : null"
                                :disabled="button.disabled"
                                :class=button.class
                                @click="button.onClick"
                                v-html="button.value ? button.value : ''"
                        />
                    </li>
                </ul>
            </div>
            <div v-else></div>
        </nav>
    </footer>
</template>

<style scoped>
footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    height: 60px;
}
nav {
    height: 60px;
    min-height: 60px;
    vertical-align: middle;
    border: none;
    background-color: gray;
    padding-top: 3px;
    padding-bottom: 3px;
}

button {
    height: 50px;
    width: calc((100vw / var(--ratio) - 30px - 6px) / 8); /*1行あたりのボタン数は@media使うか*/
    min-width: 50px;
    max-width: 100px;
    float: left;
    margin-top: 2px;
    margin-bottom: 2px;
    margin-right: 5px;
    padding: 0px;
    font-weight: normal;
    line-height: 1;
}
.navbar-nav .nav-item:last-child button {
    margin-right: 0px;
}

button:disabled,
button[disabled] {
    color: #aaaaaa;
}
</style>
<style>
.footer-button-visible-true {
    display: inline-block;
    visibility: visible;
}

.footer-button-visible-false {
    display: inline-block;
    visibility: hidden;
}


.footer-button-visible-false {
    display: inline-block;
    visibility: hidden;
}

@media (max-width: 768px) {
    .footer-button-visible-false {
        display: none;
        visibility: hidden;
    }
}

.footer-button-visible-none {
    display: none;
    visibility: hidden;
}

button.navbar-right {
    margin-right: 0px;
    float: right !important;
}
button .shortcut {
    font-size: 14px;
    font-weight: normal;
}
</style>

<script>
    export default {
        name: "AppFooter",
        data() {
            return {
                userId: null,
                isLogOn: null,
                buttons: [],
                shortcuts: [],
            }
        },
        computed: {
            buttonsOnLeft: function() {
                return this.buttons.filter(b => b.align != "right");
            },
            buttonsOnRight: function() {
                return this.buttons.filter(b => b.align == "right");
            },
        },
        created: function () {
            var vue = this;
            vue.$root.$on("setFooterButtons", this.setFooterButtons);
            $(document).on("keydown", evt => vue.receiveShortcutKey(evt));
        },
        methods: {
            setFooterButtons: function (options) {
                this.shortcuts = [];
                this.buttons = options.map((k, i) => this.createButton(k, i))
            },
            createButton: function (option, idx) {
                var vue = this;

                var defaults = {
                    value: null,
                    id: "footer_button_" + (idx + 1),
                    class: [],
                    align: "left",    // left/right
                    visible: "none",  // true => visibility:visible / false => visibility:hidden / none => display:none;
                    disabled: false,  // true/false
                    onClick: function () { return true;}
                };

                var ret = $.extend(true, {}, defaults, option);

                ret.class = _.concat(defaults.class, option.class || "btn-light");
                ret.class = _.concat(ret.class, "footer-button-visible-" + ret.visible);

                if (ret.shortcut) {
                    ret.value += "<br>" + "<span class='shortcut'>(" + ret.shortcut + ")<span>";
                    vue.shortcuts.push({ key: ret.shortcut, func: ret.onClick });
                }

                return ret;
            },
            receiveShortcutKey: function(evt) {
                var vue = this;

                var sc = _.find(vue.shortcuts, v => {
                    var keys = v.key.split(/ *, */);
                    var match = _.some(keys, key => {
                        var op = key
                                .split(/ *\+ */)
                                .filter(k => !["Shift", "Ctrl", "Alt"].includes(k))
                                .map(k => {
                                    var conv = {
                                        "←" : "ArrowLeft",
                                        "→" : "ArrowRight",
                                        "↑" : "ArrowUp",
                                        "↓" : "ArrowDown",
                                    };

                                    return conv[k] || k;
                                });

                        var isShift = key.includes("Shift");
                        var isCtrl = key.includes("Ctrl");
                        var isAlt = key.includes("Alt");

                        var ret =
                                (
                                    _.some(op, k => [evt.code, evt.key, evt.keyCode, evt.which].includes(k))
                                    ||
                                    _.some(op, k => [evt.code, evt.key, evt.keyCode, evt.which].includes(k.toLowerCase()))
                                )
                                && evt.shiftKey == isShift
                                && evt.ctrlKey == isCtrl
                                && evt.altKey == isAlt;

                        return ret;
                    });

                    return match;
                });
                if (sc && !sc.disabled) {
                    if ($(".ui-widget-overlay:visible").length == 0) {
                        sc.func();
                    }
                    return false;
                } else {
                    return true;
                }
            },
        }
    }
</script>
