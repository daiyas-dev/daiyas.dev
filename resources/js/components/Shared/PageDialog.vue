<template>
    <div>
        <template v-for="target in targets">
            <div :id="target.wrapperId" v-bind:key="target.uniqueId" class="body-content">
                <page-selector v-if="target.pgId"
                    :pgId=target.pgId
                    :vm=target.vm
                    :uniqueId=target.uniqueId
                    :dataUrl=target.dataUrl
                    :params=target.params
                    :title=target.title
                    :labelCd=target.labelCd
                    :labelCdNm=target.labelCdNm
                    :isSelector=target.isSelector
                    :isChild=target.isChild
                    :isCodeOnly=target.isCodeOnly
                    :showColumns=target.showColumns
                    :noViewModel=target.noViewModel
                    :callback=target.callback
                    :selector=target.selector
                    :customElement=target.customElement
                    :customElementFunc=target.customElementFunc
                    :customHeaderObjects=target.customHeaderObjects
                    :customHeaderObjectsFunc=target.customHeaderObjectsFunc
                    :showBushoSelector=target.showBushoSelector
                    :selectBushoDefault=target.selectBushoDefault
                    :customParams=target.customParams
                ></page-selector>
            </div>
        </template>
    </div>
</template>

<style scoped>
</style>
<style>
.ui-dialog-buttonset .btn {
    padding-left: 0px;
    padding-right: 0px;
}
.ui-dialog-buttonset .btn.multiline {
    height: 50px !important;
    line-height: 15px;
}
</style>

<script>
import PageSelector from "@vcs/PageSelector.vue";

export default {
    name: "page-dialog",
    data() {
        return {
            targets: [],
            buttons: [],
        };
    },
    props: {
    },
    components: {
        "PageSelector": PageSelector,
    },
    created: function () {
        this.$on("setDialogTitle", this.setDialogTitle);
        this.$on("setDialogButtons", this.setDialogButtons);

        window.PageDialog = this;
    },
    mounted: function () {
    },
    beforeUpdated: function () {
    },
    updated: function () {
    },
    destroyed: function () {
    },
    methods: {
        showSelector: function (options) {

            options.buttons = options.buttons || [];

            //再利用が指定されている場合、表示済みダイアログを検索
            if (options.reuse) {
                var reuseTargets = options.pgId ?
                        //pgId指定時はそれで検索
                        this.targets.filter(v => v.pgId == options.pgId)
                        //そうでない場合(=共通選択画面の場合)、取得元URL及びパラメータで検索
                        : this.targets.filter(v => {
                                    return v.dataUrl
                                        && v.dataUrl == options.dataUrl
                                        && _.isEqual(v.params, options.params)
                                }
                            );

                if (reuseTargets.length > 0) {
                    //取得出来た場合は再表示
                    var reuseTarget = reuseTargets[0];

                    var buttons = options.buttons.map(function(v) {
                        return {
                            text: v.text,
                            class: v.class,
                            target: v.target,
                            shortcut: v.shortcut,
                            click: function() {
                                var uniqueId = $(this).dialog("option").target.uniqueId;
                                var vue = window[uniqueId].vue;
                                var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                                var ret = v.click(vue, grid);
                                if (ret != false) $(this).dialog("close");
                            },
                        };
                    });

                    if (options.hasClose != false) {
                        buttons.push(
                            {
                                text: "閉じる",
                                class: "btn btn-danger",
                                shortcut: "ESC",
                                click: function(){
                                    $(this).dialog("close");
                                }
                            });
                    }

                    //callback指定時、実行
                    if (options.callback) {
                        var grid = reuseTarget.wrapper.find(".pq-grid").pqGrid("getInstance").grid;

                        //再検索
                        if (!_.isEqual(grid.prevPostData, grid.options.dataModel.postData)) {
                            grid.searchData();
                        }
                        grid.widget().css("visibility", "hidden");
                        options.callback(grid);
                    }
                    if (options.selector) {
                        var grid = reuseTarget.wrapper.find(".pq-grid").pqGrid("getInstance").grid;
                        options.selector(grid);
                    }

                    //ボタン編集
                    buttons = this.editButtons(buttons);

                    //option再設定後、表示
                    reuseTarget.wrapper.dialog("option", "title", options.title);
                    reuseTarget.wrapper.dialog("option", "isModal", options.isModal || false);
                    reuseTarget.wrapper.dialog("option", "buttons", buttons);
                    reuseTarget.wrapper.dialog("option", "open", options.open);
                    reuseTarget.wrapper.dialog("open");

                    return;
                }
            }

            //pgIdの重複をチェックし、ユニークとなるsuffixを付加
            var uniqueId = options.pgId ?
                (options.pgId + "_"
                    + (this.targets.filter(v => v.pgId == options.pgId).length + 1)) :
                (options.dataUrl.replace(/^.+\//g, "_") + "_"
                    + (this.targets.filter(v => {
                            return !!v.dataUrl && !!options.dataUrl && v.dataUrl.replace(/^.+\//g, "_") == options.dataUrl.replace(/^.+\//g, "_");
                        }).length + 1));

            //ラッパーdivのID
            var wrapperId = "Wrapper_" + uniqueId;

            //表示対象オブジェクト
            var target = {
                pgId: null,
                vm: options.vm,
                dataUrl: options.dataUrl,
                params: options.params,
                title: options.title,
                labelCd: options.labelCd,
                labelCdNm: options.labelCdNm,
                uniqueId: uniqueId,
                wrapperId: wrapperId,
                isModal: options.isModal || false,
                isCodeOnly: options.isCodeOnly || false,
                showColumns: options.showColumns || [],
                isSelector: true,
                isChild: true,
                noViewModel: true,
                callback: options.callback,
                selector: options.selector,
                customElement: options.customElement,
                customElementFunc: options.customElementFunc,
                customHeaderObjects: options.customHeaderObjects,
                customHeaderObjectsFunc: options.customHeaderObjectsFunc,
                showBushoSelector: options.showBushoSelector,
                selectBushoDefault: options.selectBushoDefault,
                customParams: options.customParams,
            };
            this.targets.push(target);

            new Promise((resolve, reject) => {
                var timer = setInterval(function() {
                    var wrapper = $("#" + wrapperId);

                    if (wrapper.length == 1) {
                        //interval解除
                        clearInterval(timer);

                        //wrapperIdのdiv設定が完了したら、resolve
                        return resolve(wrapper);
                    }
                }, 100);
            })
            .then((wrapper) => {
                //optionsからボタン生成
                var buttons = options.buttons.map(function(v) {
                    return {
                        text: v.text,
                        class: v.class,
                        target: v.target,
                        shortcut: v.shortcut,
                        click: function() {
                            var uniqueId = $(this).dialog("option").target.uniqueId;
                            var vue = window[uniqueId].vue;
                            var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                            var ret = v.click(vue, grid);
                            if (ret!= false) $(this).dialog("close");
                        },
                    };
                });

                if (options.hasClose != false) {
                    buttons.push(
                        {
                            text: "閉じる",
                            class: "btn btn-danger",
                            shortcut: "ESC",
                            click: function(){
                                $(this).dialog("close");
                            }
                        });
                }

                //ボタン編集
                buttons = this.editButtons(buttons);

                //wrapperを基にdialog生成
                wrapper.dialogChild({
                    target: target,
                    modal: target.isModal,
                    width: options.width || 600,
                    height: options.height || 500,
                    resizable: options.resizable,
                    reuse: options.reuse || false,
                    buttons: buttons,
                    opened: options.opened,
                    beforeClose: options.onBeforeClose || this.beforeClose,
                });

                //画面IDの編集
                var pgId = options.pgId || options.dataUrl ? "CommonSelector" : null;

                //画面IDの指定
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.pgId = pgId);

                //wrapperの保管
                //wrapper.dialog("option", "target.pgId", options.pgId);
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.wrapper = wrapper);

                return;
            });
        },
        beforeClose: (event, ui) => {
            console.log("dialog beforeClose");
            if (!window.event) return true;

            var d = $(event.target);
            var pg = d.find(".pq-grid");
            var content = d.closest(".ui-dialog-content");

            //ESC設定通常ボタン
            var escbtn = d.closest(".ui-dialog").find(".ui-dialog-buttonset button.btn-primary[shortcut=ESC]")[0];
            if (!!escbtn && window.event.key == "Escape") return false;

            var loading = pg
                .map((i, v) => $(v).pqGrid("getInstance").grid)
                .get()
                .filter(g => g.$loading.is(":visible"))
                .some(v => !!v);
            if (!!loading) return false;

            var changed = pg
                .map((i, v) => $(v).pqGrid("getInstance").grid)
                .get()
                .filter(g => g.isChanged());

            if (changed.length == 0 && $(window.event.target).attr("shortcut") == "ESC") return true;

            var editting = pg
                .map((i, v) => $(v).pqGrid("getInstance").grid)
                .get()
                .some(g => !_.isEmpty(g.getEditCell()));
            var isEscOnEditor = !!window.event && window.event.key == "Escape"
                && (
                    $(window.event.target).hasClass("target-input") ||
                    $(window.event.target).hasClass("pq-cell-editor")
                );

            if (changed.length == 0) return !editting && !isEscOnEditor;

            if (!!content.attr("closable")) return true;
            changed[0].notifyChanged(
                "終了して宜しいですか？(変更は破棄されます)",
                (grid, callback) => {
                    content.attr("closable", true);
                    content.dialog("close");
                    callback();
                }
            );
            return false;
        },
        show: function (options) {
            options.buttons = options.buttons || [];

            //再利用が指定されている場合、表示済みダイアログを検索
            if (options.reuse) {
                var reuseTargets = options.pgId ? this.targets.filter(v => v.pgId == options.pgId)  //pgId指定時はそれで検索
                        //そうでない場合(=共通選択画面の場合)、取得元URL及びパラメータで検索
                        : this.targets.filter(v => {
                                    return v.dataUrl
                                        && v.dataUrl.replace(/^.+\//g, "_") == options.dataUrl.replace(/^.+\//g, "_")
                                        && _.isEqual(v.params, options.params)
                                }
                            );

                if (reuseTargets.length > 0) {
                    //取得出来た場合は再表示
                    var reuseTarget = reuseTargets[0];

                    var buttons = options.buttons.map(function(v) {
                        return {
                            text: v.text,
                            class: v.class,
                            target: v.target,
                            shortcut: v.shortcut,
                            click: function() {
                                var pgId = $(this).dialog("option").target.pgId;
                                var uniqueId = $(this).dialog("option").target.uniqueId;

                                var page = window[uniqueId] || window[pgId];
                                var vue = page ? page.vue : null;
                                var grid = page ? (page["Grid_" + uniqueId] || page[Object.keys(page).find(k => k.includes("Grid"))]): null;
                                var ret = v.click(vue, grid);
                                if (ret != false) $(this).dialog("close");
                            },
                        };
                    });

                    if (options.hasClose != false) {
                        buttons.push(
                            {
                                text: "閉じる",
                                class: "btn btn-danger",
                                shortcut: "ESC",
                                click: function(){
                                    $(this).dialog("close");
                                }
                            });
                    }

                    //callback指定時、実行
                    if (options.callback) {
                        var grid = reuseTarget.wrapper.find(".pq-grid").pqGrid("getInstance").grid;
                        grid.widget().css("visibility", "hidden");
                        options.callback(grid);
                    }

                    //ボタン編集
                    buttons = this.editButtons(buttons);

                    //option再設定後、表示
                    reuseTarget.wrapper.dialog("option", "title", options.title);
                    reuseTarget.wrapper.dialog("option", "isModal", options.isModal || false);
                    reuseTarget.wrapper.dialog("option", "buttons", buttons);
                    reuseTarget.wrapper.dialog("open");

                    return;
                }
            }

            //pgIdの重複をチェックし、ユニークとなるsuffixを付加
            var uniqueId = options.pgId + "_" + (this.targets.filter(v => v.pgId == options.pgId).length + 1);

            //ラッパーdivのID
            var wrapperId = "Wrapper_" + uniqueId;

            //表示対象オブジェクト
            var target = {
                pgId: null,
                params: options.params,
                uniqueId: uniqueId,
                wrapperId: wrapperId,
                isModal: options.isModal || false,
                isChild: options.isChild || false,
                noViewModel: options.noViewModel || false,
            };
            this.targets.push(target);

            new Promise((resolve, reject) => {
                var timer = setInterval(function() {
                    var wrapper = $("#" + wrapperId);

                    if (wrapper.length == 1) {
                        //interval解除
                        clearInterval(timer);

                        //wrapperIdのdiv設定が完了したら、resolve
                        return resolve(wrapper);
                    }
                }, 100);
            })
            .then((wrapper) => {
                //optionsからボタン生成
                var buttons = options.buttons.map(function(v) {
                    return {
                        text: v.text,
                        class: v.class,
                        target: v.target,
                        shortcut: v.shortcut,
                        click: function() {
                            var pgId = $(this).dialog("option").target.pgId;
                            var uniqueId = $(this).dialog("option").target.uniqueId;

                            var page = window[uniqueId] || window[pgId];
                            var vue = page.vue;
                            var grid = page ? (page["Grid_" + uniqueId] || page[Object.keys(page).find(k => k.includes("Grid"))]): null;
                            v.click(vue, grid);

                            if (v.isClose != false) {
                                $(this).dialog("close");
                            }
                        },
                    };
                });

                if (options.hasClose != false) {
                    buttons.push(
                        {
                            text: "閉じる",
                            class: "btn btn-danger",
                            shortcut: "ESC",
                            click: function(){
                                $(this).dialog("close");
                            }
                        });
                }

                //ボタン編集
                buttons = this.editButtons(buttons);

                //wrapperを基にdialog生成
                wrapper.dialogChild({
                    target: target,
                    modal: target.isModal,
                    width: options.width || 500,
                    height: options.height || 500,
                    resizable: options.resizable,
                    reuse: options.reuse || false,
                    buttons: buttons,
                    beforeClose: options.onBeforeClose || this.beforeClose,
                });

                //画面IDの指定
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.pgId = options.pgId);

                //wrapperの保管
                wrapper.dialog("option", "target.pgId", options.pgId);
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.wrapper = wrapper);
            });
        },
        editButtons: function(buttons) {
            buttons.forEach(v => {
                if (!!v.shortcut && !v.text.includes(v.shortcut)) {
                    v.text += "\r\n" + "(" + v.shortcut + ")";
                }
            });
            return buttons;
        },
        hide: function (pgId) {
            $("#closeBtn").click();
        },
        setDialogTitle: function(info) {
            var uniqueId = info.uniqueId;
            var title = info.title;

            //ダイアログタイトルの設定
            this.targets.filter(v => v.uniqueId == uniqueId && !!v.wrapper)
                    .forEach(v => v.wrapper.dialog("option", "title", title));
        },
        setDialogButtons: function(info) {
            var uniqueId = info.uniqueId;

            //ダイアログボタン向けにoption変更
            var buttons = _.cloneDeep(info.buttons)
                .filter(v => v.value != "終了" && v.shortcut != "F12")
                .map(v => {
                    if (eval(v.visible) == false) {
                        return {
                            text: "",
                            class: "btn btn-primary invisible",
                            disabled: true,
                        };
                    }

                    var text = v.value.includes(v.shortcut) ? v.value : (v.value + "\r\n" + "(" + v.shortcut + ")");
                    text = text.replace(/<br\/*>/g, "\r\n");
                    var multi = !!text.match(/\r\n/g) && text.match(/\r\n/g).length > 1;

                    return {
                            text: text,
                            class: "btn btn-primary" + (multi ? " multiline" : ""),
                            shortcut: v.shortcut,
                            click: v.onClick,
                            disabled: v.disabled || false,
                    };
                });

                buttons.push(
                    {
                        text: "閉じる\r\n(ESC)",
                        class: "btn btn-danger",
                        shortcut: "ESC",
                        click: function(){
                            $(this).dialog("close");
                        }
                    }
                );

            //ダイアログボタンの設定
            this.targets.filter(v => v.uniqueId == uniqueId && !!v.wrapper)
                    .forEach(v => v.wrapper.dialog("option", "buttons", buttons));
        },
    }
};
</script>
