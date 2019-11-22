<template>
    <form :id="this.uniqueId" class="CommonSelector">
        <PqGridWrapper
            :id=this.gridId
            :ref=this.gridId
            :dataUrl=this.dataUrl
            :query=this.query
            classes="ml-0 mr-0"
            :SearchOnCreate=true
            :SearchOnActivate=true
            :keepSelect=true
            :options=this.grid1Options
            :showContextMenu=false
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=this.onCompleteFunc
        />
    </form>
</template>

<style>
.CommonSelector .pq-grid {
    visibility: hidden;
}
.CommonSelector .pq-grid-cell.pq-focus {
    border: none !important;
    outline: none !important;
}
.CommonSelector [name=SearchStrings] {
    width: 300px;
    max-width: none;
}
.CommonSelector svg.pq-grid-overlay:not(.pq-head-overlay):not(.pq-number-overlay)
{
    border: none;
    background-color: rgba(255, 255, 0, 0.2);
}
</style>

<script>
/** 以下、VueComponent設定 **/
import PqGridWrapper from "@vcs/PqGridWrapper.vue";

export default {
    name: "CommonSelector",
    data() {
        return {
            viewModel: {},
            page: null,
            count: null,
            keyword: null,
        }
    },
    props: {
        pgId: String,
        dataUrl: String,
        title: String,
        labelCd: String,
        labelCdNm: String,
        isCodeOnly: Boolean,
        showColumns: Array,
        maxRec: Object,
        uniqueId: String,
        callback: Function,
        query: Object,
        vm: Object,
    },
    components: {
        "PqGridWrapper": PqGridWrapper,
    },
    watch: {
        count: {
            deep: true,
            handler: function(newVal) {
                //先頭行を選択
                var gridVue = this.$refs[this.gridId];
                var grid = $(gridVue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                grid.setSelection({ rowIndx: 0 });
            },
        }
    },
    computed: {
        gridId: function(){
            return "Grid_" + this.uniqueId;
        },
        grid1Options: function() {
            var comp = this;

            return {
                selectionModel: { type: "row", mode: "single", row: true },
                scrollModel: { autoFit: true, timeout: 0 },
                rowHtHead: 31,
                rowHt: 35,
                numberCell: { width: 55, minWidth: 55 },
                columnTemplate: {
                    editable: false,
                    sortable: true,
                },
                filterModel: {
                    on: true,
                    header: false,
                },
                colModel: [
                    {
                        title: this.labelCd, dataIndx: "Cd",
                        width: 100, minWidth: 100, maxWidth: comp.isCodeOnly ? "100%" : 150,
                        dataType: "string",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: this.labelCdNm,
                        dataIndx: "CdNm",
                        dataType: "string",
                        hidden: comp.isCodeOnly,
                        filter: { crules: [{ condition: "contain" }] },
                    },
                ],
			    toolbar: {
				    items: [
					    {
                            name: "SearchStrings",
						    type: "textbox",
                            attr: 'name="SearchStrings" tabIndex=-1 searchIndex=0 prevString="" autocomplete="off" style="width: calc(100% - 110px) !important;"',
                            cls: "SearchStrings",
                            label: "<i class='fa fa-search ml-1'></i>" + "キーワード ",
					    },
                        {
                            name: "CountConstraintViolation",
                            type: "<br><label class='CountConstraintViolation'></label>",
                            attr: "CountConstraintViolation",
                            style: "display: none;",
                        },
				    ]
			    },
                showToolbar: true,
                rowDblClick: function(event, ui) {
                    var selectBtn = this.widget()
                                        .closest(".ui-dialog")
                                        .find(".ui-dialog-buttonpane .ui-button")
                                        .filter((i, v) => $(v).text() == "選択");

                    if (selectBtn.length == 1) {
                        selectBtn[0].click();
                    }
                },
            };
        },
    },
    created: function () {  //createdは一回きり
        console.log("CommonSelecotr created");

        //コンフリクトを避けるため、名前空間を定義
        window[this.uniqueId] = window[this.uniqueId] || {};
        window[this.uniqueId] = $.extend(true,
            window[this.uniqueId],
            {
                //タイトル
                ScreenTitle: this.title,

                //Vueコンポーネント参照
                vue: null,

                initialize: function() {
                },
            }
        );
        this.page = window[this.uniqueId];
        this.page.vue = this;

        //画面タイトル設定
        this.$emit("setTitle", { name: this.page.ScreenTitle, back: "" });

        //ユニークidが設定されていたら、ダイアログタイトル設定
        if (this.$parent.uniqueId) {
            this.$root.$emit("setDialogTitle", { uniqueId: this.$parent.uniqueId,  title: this.page.ScreenTitle });
        }

        //ViewModel設定
        $.extend(true, this.viewModel, this.vm);

        this.$root.$on('resize', this.resize);
    },
    mounted: function () {
        var vue = this;

        console.log("CommonSelecotr mounted");

        //vueをbindしたエレメント
        var ele = $(this.$el);

        //親div
        var parent = ele.closest("div");
        //PqGrid
        var grid = ele.find(".pq-grid");

        //親divのリサイズハンドラ設定
        parent.resize(function() {
            console.log("parent resize");

            //HTML Element以外は除外
            if (ele[0].nodeType != 1) return;

            var pw = parent.width();
            var ph = parent.height();
            //ele.height(ph);

            grid.width(pw).height(ph);
            grid.pqGrid("option", "width", pw);
            grid.pqGrid("option", "height", ph);
            grid.pqGrid("refreshView");
        });

        //dialog open event handler
        parent.dialog({ open: (event, ui) => {
            var grid = $(event.target).closest(".ui-dialog").find(".pq-grid").pqGrid("getInstance").grid;
            grid.refreshToolbar();
            grid.refreshView();
        }});

        //keydown event handler
        $(document).on("keydown", "*",  parent, (event) => {
            var dlg = event.data;

            try {
                if (!dlg.dialog("isOpen")) {
                    return true;
                }
            } catch(ex) {
                return true;
            }

            var key = event.which;
            var grid = dlg.closest(".ui-dialog").find(".pq-grid").pqGrid("getInstance").grid;

            //if (!grid.pdata || grid.pdata.length == 0) return false;

            var rowIndx = grid.Selection().getSelection().length ? grid.Selection().getSelection()[0].rowIndx : 0;

            switch(key) {
                case 9:     //Tab
                    var selectBtn = dlg.closest(".ui-dialog")
                                       .find(".ui-dialog-buttonpane .btn")
                                       .filter((i, v) => $(v).text() == "選択");

                    if (selectBtn.length == 1) {
                        //選択ボタンがあればfocus
                        selectBtn[0].focus();

                        //イベントキャンセル
                        return false;
                    }
                    break;
                case 13:    //Enter
                    if (event.target.name == "SearchStrings") {
                        //toolbarの表示テキストが選択中の場合、次検索
                        var target = event.target;
                        target.searchIndex++;
                        grid.options.vue.$parent.searchRow(grid, target, target.value, target.searchIndex);

                        //イベントキャンセル
                        return false;
                    } else {
                        var selectBtn = dlg.closest(".ui-dialog")
                                           .find(".ui-dialog-buttonpane .btn")
                                           .filter((i, v) => $(v).text() == "選択");

                        if (selectBtn.length == 1) {
                            //選択ボタンがあれば押下
                            selectBtn[0].click();

                            //イベントキャンセル
                            return false;
                        }
                    }
                    break;
                case 27:    //ESC
                    var closeBtn = dlg.closest(".ui-dialog")
                                        .find(".ui-dialog-buttonpane .ui-button")
                                        .filter((i, v) => $(v).text() == "閉じる");

                    if (closeBtn.length == 1) {
                        //閉じるボタンがあれば押下
                        closeBtn[0].click();

                        //イベントキャンセル
                        return false;
                    }
                    break;
                case 38:    //up
                    //上の行に移動
                    grid.setSelection({ rowIndx: rowIndx == 0 ? 0 : --rowIndx });
                    return false;
                    break;
                case 40:    //down
                    //下の行に移動
                    grid.setSelection({ rowIndx: rowIndx == grid.pdata.length - 1 ? rowIndx : ++rowIndx });
                    return false;
                    break;
                case 114:   //F3
                    if (event.target.name == "SearchStrings") {
                        //toolbarの表示テキストが選択中の場合、次検索
                        var target = event.target;
                        target.searchIndex++;
                        grid.options.vue.$parent.selectRow(grid, target, target.value, target.searchIndex);
                    } else {
                        //toolbarの表示テキストを選択
                        if (grid.options.showToolbar) {
                            var target = grid.toolbar().find("[name=SearchStrings]")[0];
                            target.focus();
                        }
                    }
                    return false;
                    break;
                case 70:    //F
                    if (!event.ctrlKey) return true;   //Ctrl + Fでなければ抜ける

                    //toolbarの表示テキストを選択
                    if (grid.options.showToolbar) {
                        var target = grid.toolbar().find("[name=SearchStrings]")[0];
                        target.focus();
                    }

                    return false;
                    break;
                default:
                    if (event.currentTarget.name == "SearchStrings") {
                        event.stopPropagation();
                    }
                    return true;
                    break;
            }
        });

        //toolbarの置き換え
        var pqgrid = grid.pqGrid("getInstance").grid;
        pqgrid.options.toolbar = this.grid1Options.toolbar;
        pqgrid.refreshToolbar();

        this.focused();
    },
    beforeUpdated: function () {
    },
    updated: function () {
    },
    activated: function () {    //画面再表示時はこのイベント
        // console.log("CommonSelecotr activated");
        this.focused();
    },
    deactivated: function () {
        // console.log("CommonSelecotr deactivated");
    },
    destroyed: function () {
        // console.log("CommonSelecotr destroyed");
    },
    methods: {
        focused: function() {
            console.log(this.$options.pgId + " Focused:");

            //リサイズ
            this.resize();
        },
        resize: function(size) {
        },
        onAfterSearchFunc: function (vue, grid, res) {
            var row = res[0];

            //コード及び名称以外の取得情報のカラム追加
            vue.$parent.showColumns.forEach(c => {
                //colModelに追加
                var col = {
                    title:  c ? c.title : k,
                    hidden: !!c.hidden,
                    dataIndx: c.dataIndx,
                    dataType: c.dataType || "string",
                    width: c.width || null,
                    maxWidth: c.maxWidth || null,
                    minWidth: c.minWidth || null,
                };

                var prev = grid.options.colModel.filter(c => c.dataIndx == col.dataIndx)[0];
                if (!!prev) {
                    grid.options.colModel[grid.options.colModel.indexOf(prev)] = col;
                } else {
                    if (c.colIndx == undefined) {
                        grid.options.colModel.push(col);
                    } else {
                        grid.options.colModel.splice(c.colIndx, 0, col);
                    }
                }
            });

            if (vue.$parent.showColumns.length > 0) {
                //colModel更新
                grid.refreshCM();
            }

            //データ件数更新
            vue.$parent.$set(vue.$parent.$data, "count", res.length);

            //callback指定時、実行
            if (vue.$parent.callback) {
                vue.$parent.callback(grid);
                grid.widget().css("visibility", "visible");
            } else {
                grid.widget().css("visibility", "visible");
            }

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = grid.options.vue.$parent;
            var target = $(vue.$el).find("[name=SearchStrings]")[0];
            target.value = vue.keyword || "";

            $(target).on("input", (ev => {
                console.log("SearchStrings input", ev.target.value);
                vue.selectRow(grid, ev.target, ev.target.value, ev.target.searchIndex);
            }));

            vue.selectRow(grid, target, target.value, 0);
        },
        searchRow: function(grid, target, str, idx, noSearch) {
            var vue = this;
            grid = grid || vue.page[vue.gridId];

            var isMatchAll = grid.pdata.length > 0 &&
                grid.pdata.every(v => {
                    return !!v.Cd && v.Cd.startsWith(str) || !!v.CdNm && v.CdNm.includes(str)
                })
            ;

            if (isMatchAll && (!vue.keyword || vue.keyword == str)) {
                //該当する行の選択
                vue.keyword = vue.keyword || str
                vue.selectRow(grid, target, str, idx);
            } else {
                if (noSearch != true && !!grid.options.vue.CountConstraint) {
                    //再検索
                    var params = _.cloneDeep(vue.query);

                    vue.keyword = str;
                    params.Keyword = str.includes("*") ? str.replace(/\*/g, "%") : ("%" + str + "%");

                    grid.searchData(params);
                } else {
                    //該当する行の選択
                    vue.selectRow(grid, target, str, idx);
                }
            }
        },
        selectRow: function(grid, target, str, idx) {
            console.log("CommonSelector selectRow");
            var vue = this;
            grid = grid || vue.page[vue.gridId];

            str = str || target.value;
            target.focus();

            if (str != target.prevString) {
                target.prevString = str;
                target.searchIndex = 0;
            }

            if (str == "") return;

            idx = idx || target.searchIndex;
            var hits = grid.pdata.filter(v => (!!v.Cd && v.Cd.toString().includes(str)) || (!!v.CdNm && v.CdNm.toString().includes(str)));

            if (idx == hits.length) {
                idx = 0;
                target.searchIndex = 0;
            }
            var hit = hits[idx];

            if (hit) {
                var rowIndx = grid.getRowIndx({ rowData: hit }).rowIndx;
                var nowIndx = grid.Selection().getSelection()[0].rowIndx;

                if (rowIndx != nowIndx) {
                    var buf = grid.toolbar().find(".CountConstraintViolation").text() ? 1 : 0;

                    grid.scrollRow({ rowIndx: rowIndx + buf });
                    grid.setSelection({ rowIndx: rowIndx });
                } else {
                    var selectBtn = $(vue.$el).closest(".ui-dialog")
                                       .find(".ui-dialog-buttonpane .btn")
                                       .filter((i, v) => $(v).text() == "選択");

                    if (selectBtn.length == 1) {
                        //選択ボタンがあればclick
                        selectBtn[0].click();

                        //イベントキャンセル
                        return false;
                    }
                }
            }
            target.focus();

            return !!hit;
        },
    }
}
</script>
