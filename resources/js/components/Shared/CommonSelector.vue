<template>
    <form :id="this.uniqueId" class="CommonSelector">
        <div class="row ml-0 mr-0 mb-2 CustomCotainer" v-if="!!showBushoSelector || !!customElement || !!customElementFunc">
            <div class="col-md-4" v-if="!!showBushoSelector">
                <label style="text-align: center;">部署</label>
                <VueSelectBusho
                    v-if="!!selectBushoDefault"
                    ref="VueSelectBusho"
                    :withCode=true
                    :hasNull=true
                    :onChangedFunc=onBushoCdChanged
                />
                <VueSelect
                    v-else
                    id="Busho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    uri="/Utilities/GetBushoList"
                    :withCode=true
                    :hasNull=true
                    :onChangedFunc=onBushoCdChanged
                />
            </div>
        </div>
        <PqGridWrapper
            :id=this.gridId
            :ref=this.gridId
            :dataUrl=this.dataUrl
            :query=this.searchParams
            classes="ml-0 mr-0"
            :SearchOnCreate=true
            :SearchOnActivate=true
            :keepSelect=true
            :options=this.grid1Options
            :showContextMenu=false
            :onAfterSearchFunc=this.onAfterSearchFunc
            :onCompleteFunc=this.onCompleteFunc
            :autoToolTipDisabled=true
            :setCustomTitle=this.setGridTitle
        />
    </form>
</template>

<style>
.CommonSelector .pq-grid {
    visibility: visible;
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
import VueSelect from "@vcs/VueSelect.vue";
import VueSelectBusho from "@vcs/VueSelectBusho.vue";

export default {
    name: "CommonSelector",
    data() {
        var vue = this;

        var data = {
            viewModel: {},
            page: null,
            count: null,
            keyword: null,
            countAll: 0,
            current: null,
            scrollParams: {},
        };

        if (!!vue.customParams) {
            data.customElementParams = _.cloneDeep(vue.customParams);
        }

        return data;
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
        selector: Function,
        customElement: String,
        customElementFunc: Function,
        customHeaderObjects: Array,
        customHeaderObjectsFunc: Function,
        showBushoSelector: Boolean,
        selectBushoDefault: Boolean,
        customParams: Object,
        query: Object,
        vm: Object,
    },
    components: {
        "PqGridWrapper": PqGridWrapper,
        "VueSelect": VueSelect,
        "VueSelectBusho": VueSelectBusho,
    },
    watch: {
        count: {
            deep: true,
            handler: function(newVal) {
                //先頭行を選択
                // var gridVue = this.$refs[this.gridId];
                // var grid = $(gridVue.$el).find(".pq-grid").pqGrid("getInstance").grid;
                // grid.setSelection({ rowIndx: 0 });
            },
        }
    },
    computed: {
        gridId: function(){
            return "Grid_" + this.uniqueId;
        },
        grid1Options: function() {
            var vue = this;

            return {
                selectionModel: { type: "row", mode: "block", row: true, toggle: false, },
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
                        width: 100, minWidth: 100, maxWidth: vue.isCodeOnly ? "100%" : 150,
                        dataType: "string",
                        align: "center",
                        filter: { crules: [{ condition: "contain" }] },
                    },
                    {
                        title: this.labelCdNm,
                        dataIndx: "CdNm",
                        dataType: "string",
                        hidden: vue.isCodeOnly,
                        filter: { crules: [{ condition: "contain" }] },
                    },
                ],
			    toolbar: {
				    items: [
					    {
                            name: "SearchStrings",
						    type: "textbox",
                            attr: 'name="SearchStrings" searchIndex=0 prevString="" autocomplete="off" style="width: 250px !important;"',
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
                    console.log("common selector dbclick")
                    var selectBtn = this.widget()
                                        .closest(".ui-dialog")
                                        .find(".ui-dialog-buttonpane .ui-dialog-buttonset button")
                                        .filter((i, v) => $(v).text().includes("選択"));

                    if (selectBtn.length == 0) {
                        selectBtn = this.widget()
                                        .closest(".ui-dialog")
                                        .find(".ui-dialog-buttonpane .ui-dialog-buttonset button.btn-primary");
                    }

                    if (selectBtn.length == 1) {
                        selectBtn[0].click();
                    }
                },
                scrollStop: function (event, ui) {
                    var grid = eval("this");

                    if (!vue.query || !vue.query.Chunk) return;

                    var last = grid.getViewPortIndx().finalV;
                    if (grid.pdata.length - last < vue.query.Chunk / 3) {
                        var params = _.cloneDeep(grid.prevPostData || vue.query);
                        params.Start = vue.current + 1;
                        params.noCache = true;

                        if (params.Start > vue.countAll) return;

                        if (_.isEqual(params, vue.scrollParams)) return;

                        grid.showLoading();
                        axios.post(vue.dataUrl, params)
                            .then(res => {
                                var row = res.data[0];
                                vue.countAll = row.Count;
                                vue.current = row.End;

                                vue.scrollParams = _.cloneDeep(params);

                                grid.pdata.push(...row.Result);
                                grid.refreshView();
                                grid.hideLoading();
                            })
                            .catch(err => {
                                console.log(err);
                                grid.hideLoading();
                                $.dialogErr({
                                    title: "異常終了",
                                    contents: "検索に失敗しました" + "<br/>" + err.message,
                                });
                            });
                    }
                },
            };
        },
        searchParams: function() {
            var vue = this;
            return $.extend(true, vue.query, vue.vm);
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
            this.$parent.$emit("setDialogTitle", { uniqueId: this.$parent.uniqueId,  title: this.page.ScreenTitle });
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
            // console.log("CommonSelector dialog open")
            // var grid = $(event.target).closest(".ui-dialog").find(".pq-grid").pqGrid("getInstance").grid;
            // grid.refreshToolbar();
            // grid.refreshView();
        }});

        //toolbarの置き換え
        var pqgrid = grid.pqGrid("getInstance").grid;
        var toolbar = _.cloneDeep(vue.grid1Options.toolbar);

        if (!!vue.customHeaderObjects) {
            toolbar.items.splice(1, 0, ...vue.customHeaderObjects);
        }

        if (!!vue.customHeaderObjectsFunc) {
            toolbar.items.splice(1, 0, ...vue.customHeaderObjectsFunc(pqgrid));
        }

        pqgrid.options.toolbar = toolbar;
        pqgrid.refreshToolbar();

        pqgrid.toolbar().find("input").each((i, e) => {
            $(e).on("keydown", event => {
                if (event.which == 13) {
                    vue.conditionChanged(true);
                    return false;
                }
            });
        });

        //コード及び名称以外の取得情報のカラム追加
        vue.showColumns.forEach(c => {
            //colModelに追加
            var col = {
                title:  c ? c.title : k,
                hidden: !!c.hidden,
                dataIndx: c.dataIndx,
                dataType: c.dataType || "string",
                align: c.align || null,
                format: c.format || null,
                width: c.width || null,
                maxWidth: c.maxWidth || null,
                minWidth: c.minWidth || null,
                tooltip: c.tooltip || null,
            };

            if (c.render) {
                col.render = c.render;
            }

            var prev = pqgrid.options.colModel.filter(c => c.dataIndx == col.dataIndx)[0];
            if (!!prev) {
                pqgrid.options.colModel[pqgrid.options.colModel.indexOf(prev)] = col;
            } else {
                if (c.colIndx == undefined) {
                    pqgrid.options.colModel.push(col);
                } else {
                    pqgrid.options.colModel.splice(c.colIndx, 0, col);
                }
            }
        });

        //キーワード検索用
        pqgrid.options.colModel.push(
            {
                title:  "keyword",
                dataIndx: "keyword",
                dataType: "string",
                hidden: true,
            }
        );

        //colModel更新
        pqgrid.refreshCM();

        if (!!vue.customElement) {
            $(vue.$el).find(".CustomCotainer").append($(vue.customElement));
        }

        if (!!vue.customElementFunc) {
            vue.customElementFunc(vue, $(vue.$el).find(".CustomCotainer"));
        }

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
            var vue = this;
            var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;

            //リサイズ
            vue.resize();
            grid.toolbar().find("input.SearchStrings").focus();
        },
        resize: function(size) {
        },
        setGridTitle: function (title, grid) {
            var vue = this;

            if (!!vue.query.Chunk) {
                return title + " [総件数:" + vue.countAll + "]";
            } else {
                return title;
            }
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;
            var row = res[0];

            if (res.length == 1 && !!row.End) {
                vue.countAll = row.Count;
                vue.current = row.End;
                var ret = _.cloneDeep(row.Result);
                return ret;
            } else {
                //データ件数更新
                gridVue.$parent.$set(gridVue.$parent.$data, "count", res.length);
            }

            //callback指定時、実行
            if (gridVue.$parent.callback) {
                gridVue.$parent.callback(grid);
                grid.widget().css("visibility", "visible");
            } else {
                grid.widget().css("visibility", "visible");
            }

            var result = res.map(v => {
                v.keyword = "||" + _.values(v).filter(v => !_.isObject(v)).join("||") + "||";
                return v;
            });

            return res;
        },
        onCompleteFunc: function(grid, ui) {
            var vue = grid.options.vue.$parent;
            var target = $(vue.$el).find("[name=SearchStrings]")[0];
            target.value = vue.keyword || "";

            // $(target).on("input", _.debounce(
            //     ev => {
            //         console.log("SearchStrings input");
            //         vue.keyword = ev.target.value;

            //         if (!grid) return;

            //         vue.conditionChanged();
            //     }, 1000
            // ));

            if (!target.value && !!vue.selector) {
                vue.selector(grid);
            } else {
                vue.selectRow(grid, target, target.value, 0);
            }
        },
        onBushoCdChanged: function(code, entity) {
            var vue = this;
            console.log("common selector onbuchochanged")

            vue.conditionChanged();
        },
        conditionChanged: function(force) {
            var vue = this;
            var grid = $(vue.$el).find(".pq-grid").pqGrid("getInstance").grid;

            if (!grid) return;

            vue.keyword = grid.toolbar().find("input.SearchStrings").val();
            var keywords = editKeywords((vue.keyword || "").split(/[, 、]/g));

            var customParams = grid.toolbar().find("input:not(.SearchStrings)")
                .filter((i, e) => !!$(e).val())
                .map((i, e) => {
                    return {
                        [$(e).attr("name")]: $(e).val()
                    };
                })
                .get();

            if (!!vue.query.Chunk) {
                console.log("search chunk")
                var params = _.cloneDeep(_.omit(vue.query, ["Parent"]));

                params.Start = 1;
                params.Keyword = vue.keyword;
                params.Keywords = keywords;

                customParams.forEach(p => {
                    params = $.extend(true, params, p);
                });

                if (!!vue.viewModel.BushoCd) {
                    params.BushoCd = vue.viewModel.BushoCd;
                }

                if (!!vue.customElementParams) {
                    params = _.extend(params, vue.customElementParams);
                }

                if (!!force || !_.isEqual(params, grid.prevPostData)) {
                    grid.searchData(params);
                }
            } else {
                var rules = [];

                var kcrules = keywords.map(k => {
                    return { condition: "contain", value: k };
                });
                if (!!kcrules.length) {
                    rules.push({ dataIndx: "keyword", mode: "OR", crules: kcrules });
                }
                if (!!vue.viewModel.BushoCd) {
                    rules.push({ dataIndx: "部署ＣＤ", condition: "equal", value: vue.viewModel.BushoCd });
                }

                grid.filter({ oper: "replace", mode: "AND", rules: rules });
            }
        },
        // searchRow: function(grid, target, str, idx, noSearch) {
        //     var vue = this;
        //     grid = grid || vue.page[vue.gridId];

        //     var isMatchAll = grid.pdata.length > 0 &&
        //         grid.pdata.every(v => {
        //             return !!v.Cd && v.Cd.startsWith(str) || !!v.CdNm && v.CdNm.includes(str)
        //         })
        //     ;

        //     if (isMatchAll && (!vue.keyword || vue.keyword == str)) {
        //         //該当する行の選択
        //         vue.keyword = vue.keyword || str
        //         vue.selectRow(grid, target, str, idx);
        //     } else {
        //         if (noSearch != true && !!grid.options.vue.CountConstraint) {
        //             //再検索
        //             var params = _.cloneDeep(vue.query);

        //             vue.keyword = str;
        //             params.Keyword = str.includes("*") ? str.replace(/\*/g, "%") : ("%" + str + "%");

        //             grid.searchData(params);
        //         } else {
        //             //該当する行の選択
        //             vue.selectRow(grid, target, str, idx);
        //         }
        //     }
        // },
        selectRow: function(grid, target, str, idx) {
            console.log("CommonSelector selectRow");
            var vue = this;
            grid = grid || vue.page[vue.gridId];

            if(grid.pdata.length == 1) {
                grid.SelectRow().add({rowIndx: 0});
                grid.SelectRow().setFirst(0);
                return;
            }

            str = str || target.value;

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

                var selection = grid.Selection().getSelection();
                var nowIndx = !!selection.length ? selection[0].rowIndx : null;

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

            return !!hit;
        },
    }
}
</script>
