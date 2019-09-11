<template>
    <div>
        <template v-for="target in targets">
            <div :id="target.wrapperId" v-bind:key="target.uniqueId" class="body-content">
                <page-selector v-if="target.pgId"
                    :pgId=target.pgId
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
                ></page-selector>
            </div>
        </template>
    </div>
</template>

<style scoped>
</style>

<script>
import PageSelector from "@vcs/PageSelector.vue";

export default {
    name: "page-dialog",
    data() {
        return {
            targets: [],
        };
    },
    props: {
    },
    components: {
        "PageSelector": PageSelector,
    },
    created: function () {
        this.$root.$on("setDialogTitle", this.setDialogTitle);

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
                            click: function() {
                                var uniqueId = $(this).dialog("option").target.uniqueId;
                                var vue = window[uniqueId].vue;
                                var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                                v.click(vue, grid);
                                $(this).dialog("close");
                            },
                        };
                    });

                    if (options.hasClose != false) {
                        buttons.push(
                            {
                                text: "閉じる",
                                class: "btn btn-danger",
                                click: function(){
                                    $(this).dialog("close");
                                }
                            });
                    }

                    //callback指定時、実行
                    if (options.callback) {
                        var grid = reuseTarget.wrapper.find(".pq-grid").pqGrid("getInstance").grid;

                        //同一条件再検索
                        grid.searchData();

                        grid.widget().css("visibility", "hidden");
                        options.callback(grid);
                    }

                    //option再設定後、表示
                    reuseTarget.wrapper.dialog("option", "title", options.title);
                    reuseTarget.wrapper.dialog("option", "isModal", options.isModal || false);
                    reuseTarget.wrapper.dialog("option", "buttons", buttons);
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
                        click: function() {
                            var uniqueId = $(this).dialog("option").target.uniqueId;
                            var vue = window[uniqueId].vue;
                            var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                            v.click(vue, grid);
                            $(this).dialog("close");
                        },
                    };
                });

                if (options.hasClose != false) {
                    buttons.push(
                        {
                            text: "閉じる",
                            class: "btn btn-danger",
                            click: function(){
                                $(this).dialog("close");
                            }
                        });
                }

                //wrapperを基にdialog生成
                wrapper.dialogChild({
                    target: target,
                    modal: target.isModal,
                    width: options.width || 600,
                    height: options.height || 500,
                    resizable: true,
                    reuse: options.reuse || false,
                    buttons: buttons,
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
        show: function (options) {
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
                            click: function() {
                                var pgId = $(this).dialog("option").target.pgId;
                                var uniqueId = $(this).dialog("option").target.uniqueId;

                                var page = window[uniqueId] || window[pgId];
                                var vue = page ? page.vue : null;
                                var grid = page ? (page["Grid_" + uniqueId] || page[Object.keys(page).find(k => k.includes("Grid"))]): null;
                                v.click(vue, grid);

                                $(this).dialog("close");
                            },
                        };
                    });

                    if (options.hasClose != false) {
                        buttons.push(
                            {
                                text: "閉じる",
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
                            click: function(){
                                $(this).dialog("close");
                            }
                        });
                }

                //wrapperを基にdialog生成
                wrapper.dialogChild({
                    target: target,
                    modal: target.isModal,
                    width: options.width || 500,
                    height: options.height || 500,
                    resizable: true,
                    reuse: options.reuse || false,
                    buttons: buttons,
                });

                //画面IDの指定
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.pgId = options.pgId);

                //wrapperの保管
                wrapper.dialog("option", "target.pgId", options.pgId);
                this.targets.filter(v => v.uniqueId == uniqueId).map(v => v.wrapper = wrapper);
            });
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
    }
};
</script>
