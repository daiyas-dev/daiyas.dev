<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-primary w-75">引渡詳細</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <PqGridWrapper
                    id="DAI01061Grid1_Send"
                    ref="DAI01061Grid1_Send"
                    dataUrl="/DAI01061/GetSendList"
                    :query="{ BushoCd: params.BushoCd, TargetDate: params.TargetDate, CourseKbn: params.CourseKbn, CourseCd: params.CourseCd }"
                    :SearchOnCreate=true
                    :SearchOnActivate=false
                    :options=grid1Options
                    :isMultiRowSelectable=true
                    :autoToolTipDisabled=true
                    :autoEmptyRow=true
                    :autoEmptyRowCount=1
                    :autoEmptyRowFunc=autoEmptyRowFunc
                    :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <span class="badge badge-warning w-75">受取詳細</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <PqGridWrapper
                    id="DAI01061Grid1_Receive"
                    ref="DAI01061Grid1_Receive"
                    dataUrl="/DAI01061/GetReceiveList"
                    :query="{ BushoCd: params.BushoCd, TargetDate: params.TargetDate, CourseKbn: params.CourseKbn, CourseCd: params.CourseCd }"
                    :SearchOnCreate=true
                    :SearchOnActivate=false
                    :options=grid1Options
                    :isMultiRowSelectable=true
                    :autoToolTipDisabled=true
                    :autoEmptyRow=true
                    :autoEmptyRowCount=1
                    :autoEmptyRowFunc=autoEmptyRowFunc
                    :autoEmptyRowCheckFunc=autoEmptyRowCheckFunc
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
.row {
    margin-left: 0px;
    margin-right: 0px;
}
.badge {
    font-size: 15px;
}
</style>
<style>
form[pgid="DAI01061"] .pq-grid .DAI01061_toolbar {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
form[pgid="DAI01061"] .pq-grid .DAI01061_toolbar .toolbar_button {
    width: 45px;
    height: 30px;
    padding: 0px;
    padding-left: 3px;
    padding-right: 3px;
    margin: 0px;
    display: flex;
    justify-content: center;
    align-items: center;
}
form[pgid="DAI01061"] .pq-grid .DAI01061_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI01061"] .pq-grid .DAI01061_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI01061"] .pq-grid .DAI01061_toolbar .toolbar_button.ope {
    width: 45px;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01061",
    components: {
    },
    computed: {
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "移動明細入力",
            noViewModel: true,
            conditionTrigger: true,
            DAI01061Grid1_Send: null,
            DAI01061Grid1_Receive: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "single", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI01061_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                vue.addRowFunc(this, event);
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                vue.deleteRowFunc(this, event);
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete ',
                            options: { disabled: true },
                        },
                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 35,
                freezeCols: 2,
                width: "100%",
                height: 275,
                editable: true,
                columnTemplate: {
                    editable: true,
                    sortable: false,
                },
                trackModel: { on: true },
                historyModel: { on: true },
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: false,
                    hideRows: true,
                },
                colModel: [
                    {
                        title: "コース",
                        dataIndx: "コースＣＤ",
                        dataType: "integer",
                        hidden: true,
                    },
                    {
                        title: "コース名",
                        dataIndx: "コース名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "相手コース",
                        dataIndx: "相手コースＣＤ",
                        dataType: "string",
                        key: true,
                        psProps: {
                            dataUrl: "/Utilities/GetCourseList",
                            params: vue.getCoursePsParamsInGrid,
                            bind: "相手コースＣＤ",
                            buddies: { "相手コース名": "CdNm" },
                            isPreload: true,
                            title: "コース一覧",
                            labelCd: "コースCD",
                            labelCdNm: "コース名",
                            popupWidth: 600,
                            popupHeight: 600,
                            isShowName: true,
                            isModal: true,
                            reuse: true,
                            existsCheck: true,
                            inputWidth: 60,
                            nameWidth: 280,
                            isShowAutoComplete: true,
                            AutoCompleteFunc: vue.CourseAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            getData: (ui, grid) => {
                                console.log("psprops getData", ui.$cell.find(".target-input").val());
                                return ui.$cell.find(".target-input").val();
                            },
                            htmlRender: ui => {
                                var $el = $("<div>")
                                    .addClass("d-flex")
                                    .append($("<div>").text(ui.rowData.相手コースＣＤ).width(40).addClass("text-right"))
                                    .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                    .append($("<div>").text(ui.rowData.相手コース名))
                                    ;

                                return $el[0];
                            },
                        },
                    },
                    {
                        title: "相手コース名",
                        dataIndx: "相手コース名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "商品",
                        dataIndx: "商品ＣＤ",
                        dataType: "string",
                        key: true,
                        psProps: {
                            dataUrl: "/DAI01061/GetTargetProducts",
                            params: vue.getProductPsParamsInGrid,
                            bind: "商品ＣＤ",
                            buddies: { "商品名": "CdNm" },
                            isPreload: true,
                            title: "移動商品一覧",
                            labelCd: "移動商品CD",
                            labelCdNm: "移動商品名",
                            popupWidth: 600,
                            popupHeight: 600,
                            isShowName: true,
                            isModal: true,
                            reuse: true,
                            existsCheck: true,
                            inputWidth: 60,
                            nameWidth: 280,
                            isShowAutoComplete: true,
                            AutoCompleteFunc: vue.ProductAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            getData: (ui, grid) => {
                                console.log("psprops getData", ui.$cell.find(".target-input").val());
                                return ui.$cell.find(".target-input").val();
                            },
                            htmlRender: ui => {
                                var $el = $("<div>")
                                    .addClass("d-flex")
                                    .append($("<div>").text(ui.rowData.商品ＣＤ).width(40).addClass("text-right"))
                                    .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                    .append($("<div>").text(ui.rowData.商品名))
                                    ;

                                return $el[0];
                            },
                        },
                    },
                    {
                        title: "商品名",
                        dataIndx: "商品名",
                        dataType: "string",
                        hidden: true,
                    },
                    {
                        title: "移動数",
                        dataIndx: "個数",
                        dataType: "integer",
                        width: 75, maxWidth: 75, minWidth: 75,
                    },
                ],
                formulas: [
                ],
            },
        });

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "登録", id: "DAI01061_Save", disabled: false, shortcut: "F9",
                    onClick: function () {
                        var gridS = vue.DAI01061Grid1_Send;
                        var gridR = vue.DAI01061Grid1_Receive;

                        var checkError = grid => !!grid.widget().find(".has-error").length || !!grid.widget().find(".ui-state-error").length;

                        var hasErrorS = checkError(gridS);
                        var hasErrorR = checkError(gridR);

                        if(hasErrorS || hasErrorR){
                            $.dialogErr({
                                title: "入力値エラー",
                                contents: "エラー項目があるため、登録できません。",
                            });
                            return;
                        }

                        var checkRequire = grid => grid.pdata.map(r => [r.相手コースＣＤ, r.商品ＣＤ, r.個数]).every(r => r.every(v => !!v) || r.every(v => !v));

                        var requireS = checkRequire(gridS);
                        var requireR = checkRequire(gridR);

                        if(!requireS || !requireR){
                            $.dialogErr({
                                title: "未入力項目",
                                contents: "未入力項目があるため、登録できません。",
                            });
                            return;
                        }


                        //引渡詳細変更内容
                        var ps = gridS.createSaveParams();

                        //受取詳細変更内容
                        var pr = gridR.createSaveParams();
                        //受取詳細はコースと相手コースをswap
                        _.forIn(pr, (v, k) => v.forEach(r => {
                            var tcd = r.コースＣＤ;
                            r.コースＣＤ = r.相手コースＣＤ;
                            r.相手コースＣＤ = tcd;

                            return r;
                        }));

                        //変更内容のmerge
                        var pp = _.assignWith(ps, pr, (o, s) => _.concat(o, s));

                        //コース/相手コース未指定は除外
                        _.forIn(pp,
                            (v, k) => {
                                var list = v.filter(r => {
                                    return  r.コースＣＤ != null && r.コースＣＤ != undefined
                                        &&  r.相手コースＣＤ != null && r.相手コースＣＤ != undefined;
                                })
                                .map(r => {
                                    delete r.コース名;
                                    delete r.相手コース名;
                                    delete r.商品名;
                                    delete r.商品区分;
                                    return r;
                                })
                                ;
                                pp[k] = list;
                            }
                        );

                        if (_.values(pp).every(v => !v.length)) {
                            //変更無し
                            $.dialogInfo({
                                title: "変更無し",
                                contents: "データが変更されていません。",
                            });
                            return;
                        }

                        $.dialogConfirm({
                            title: "確認",
                            contents: "変更内容を保存します。宜しいですか？",
                            buttons:[
                                {
                                    text: "はい",
                                    class: "btn btn-primary",
                                    click: function(){
                                        $(this).dialog("close");

                                        //保存実行
                                        gridS.saveData(
                                            {
                                                uri: "/DAI01061/Save",
                                                params: pp,
                                                optional: { BushoCd: vue.params.BushoCd, TargetDate: vue.params.TargetDate, CourseKbn: vue.params.CourseKbn, CourseCd: vue.params.CourseCd },
                                                confirm: {
                                                    isShow: false,
                                                },
                                                done: {
                                                    isShow: false,
                                                    callback: (gridVue, grid, res)=>{
                                                        console.log("res", res);

                                                        if (!!res.skip) {
                                                            $.dialogInfo({
                                                                title: "登録チェック",
                                                                contents: "他で変更されたデータがあります。",
                                                            });
                                                            gridS.blinkDiff(res.send);
                                                            gridR.blinkDiff(res.receive);
                                                        } else {
                                                            vue.$root.$emit("DAI01060_updateCheck");
                                                            $(vue.$el).closest(".ui-dialog-content").dialog("close");
                                                        }

                                                        return false;
                                                    },
                                                },
                                            }
                                        );
                                    }
                                },
                                {
                                    text: "いいえ",
                                    class: "btn btn-danger",
                                    click: function(){
                                        $(this).dialog("close");
                                    }
                                },
                            ],
                        });
                    }
                },
            );
        },
        mountedFunc: function(vue) {
        },
        CourseAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm + "[" + v.担当者名 + "]";
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getCoursePsParamsInGrid: (vue, grid) => {
            return { BushoCd: vue.params.BushoCd, CourseKbn: vue.params.CourseKbn, };
        },
        ProductAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.Cd.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.Cd + " : " + v.CdNm;
                    ret.value = v.Cd;
                    ret.text = v.CdNm;
                    return ret;
                })
                ;

            return list;
        },
        getProductPsParamsInGrid: (vue, grid) => {
            return { BushoCd: vue.params.BushoCd, CourseKbn: vue.params.CourseKbn, CourseCd: vue.params.CourseCd, };
        },
        autoEmptyRowFunc: function(grid) {
            var vue = this;
            console.log("01061 autoEmptyRow");

            return {
                "部署ＣＤ": vue.params.BushoCd,
                "コースＣＤ": vue.params.CourseCd,
                "コース名": vue.params.CourseNm,
                "相手コースＣＤ": null,
                "相手コース名": null,
                "日付": vue.params.TargetDate,
                "個数": null,
                "確認フラグ": 0,
                "相手確認フラグ": 0,
            };
        },
        autoEmptyRowCheckFunc: function(rowData) {
            return !rowData["相手コースＣＤ"] || !rowData["商品ＣＤ"];
        },
        addRowFunc: function(grid, event) {
            var vue = this;

            var rowIndx = grid.pdata.length;

            grid.addRow({
                rowIndx: rowIndx,
                newRow: {},
                checkEditable: false
            });

            grid.scrollRow({rowIndxPage: rowIndx});

        },
        deleteRowFunc: function(grid, event) {
            var vue = this;

            //選択行なし
            if(!grid.SelectRow().getSelection().length){
                return;
            }

            var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
            grid.deleteRow({ rowList: rowList });
        },
    }
}
</script>
