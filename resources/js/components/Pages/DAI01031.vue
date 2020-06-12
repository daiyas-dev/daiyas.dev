<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-2">
                <label>コース</label>
            </div>
            <div class="col-md-10">
                <PopupSelect
                    id="CourseSelect"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ BushoCd: viewModel.BushoCd, TargetDate: viewModel.TargetDate }"
                    :isPreload=true
                    title="コース一覧"
                    labelCd="コースCD"
                    labelCdNm="コース名"
                    :showColumns='[
                        { title: "担当者ＣＤ", dataIndx: "担当者ＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100 },
                        { title: "担当者名", dataIndx: "担当者名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100 }
                    ]'
                    :popupWidth=600
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :inputWidth=80
                    :nameWidth=210
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :hideSearchButton=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <label>担当者</label>
            </div>
            <div class="col-md-10">
                <input class="form-control label-blue" style="width: 80px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 275px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 align-items-start">
                <PqGridWrapper
                    id="DAI01031Grid1"
                    ref="DAI01031Grid1"
                    dataUrl="/DAI01031/Search"
                    :query="searchParams"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onCompleteFunc=onCompleteFunc
                    :autoToolTipDisabled=true
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
.row {
    margin-left: 4px;
    margin-right: 4px;
}
</style>
<style>
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI01031",
    components: {
    },
    computed: {
        searchParams: function() {
            return {
                CustomerCd: this.viewModel.CustomerCd,
                BushoCd: this.viewModel.BushoCd,
                CourseCd: this.viewModel.CourseCd,
                TargetDate: moment(this.viewModel.TargetDate, "YYYY年MM月DD日").format("YYYYMMDD"),
                noCache: true,
            };
        }
    },
    watch: {
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "コース実績問合せ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                TargetDate: null,
                CourseCd: null,
                CourseNm: null,
                TantoCd: null,
                TantoNm: null,
                CustomerCd: null,
                CustomerNm: null,
            },
            DAI01031Grid1: null,
            grid1Options: {
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                freezeCols: 2,
                width: "100%",
                height: 450,
                editable: false,
                filterModel: {
                    on: false,
                    header: false,
                    menuIcon: true,
                    hideRows: true,
                },
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                colModel: [
                    {
                        title: "コード",
                        dataIndx: "得意先ＣＤ",
                        dataType: "integer",
                        key: true,
                        width: 100, minWidth: 100, maxWidth: 100,
                        render: ui => {
                            if (ui.rowData.得意先ＣＤ == vue.viewModel.CustomerCd) {
                                ui.style.push("background:#ADD8E6; color:black;");
                            }
                            return ui;
                        },
                    },
                    {
                        title: "得意先名",
                        dataIndx: "得意先名",
                        dataType: "string",
                        render: ui => {
                            if (ui.rowData.得意先名 == vue.viewModel.CustomerNm) {
                                ui.style.push("background:#ADD8E6; color:black;");
                            }
                            return ui;
                        },
                    },
                    {
                        title: "状態",
                        dataIndx: "状態",
                        dataType: "string",
                        width: 50, minWidth: 50, maxWidth: 50,
                    },
                ],
                formulas: [
                ],
            },
        });

        if (!!vue.params || !!vue.query) {
            data.viewModel = $.extend(true, {}, vue.params, vue.query);
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "再表示", id: "DAI01031_Refresh", disabled: false, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            vue.$root.$on("DAI01030_Deactivated", vue.parentDeactivated);
            vue.$root.$on("DAI01030_BushoChanged", vue.onBushoChanged);
        },
        onCompleteFunc: function(grid, ui) {
            var vue = this;

            if (grid.pdata.length > 0) {
                var rowIndx = (grid.pdata.filter(v => v.得意先ＣＤ == vue.viewModel.CustomerCd)[0].ＳＥＱ) - 1;
                grid.setSelection({ rowIndx: rowIndx });
            }
        },
        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        conditionChanged: function(forced) {
            var vue = this;
            var grid = vue.DAI01031Grid1;

            if (!!grid && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && !!vue.viewModel.TargetDate;
                var courseChanged = !grid.prevPostData || grid.prevPostData.CourseCd != vue.viewModel.CourseCd;

                if (required && (forced || courseChanged)) {
                    grid.searchData(vue.searchParams);
                }
            }
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["コース名", "担当者名"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => v.コースＣＤ.startsWith(k))
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.コースＣＤ + " : " + v.コース名 + "【" + v.担当者名 + "】";
                    ret.value = v.コースＣＤ;
                    ret.text = v.コース名;
                    return ret;
                })
                ;

            return list;
        },
        parentDeactivated: function() {
            var vue = this;
            $(vue.$el).closest(".ui-dialog-content").dialog("close");
        },
        onBushoChanged: function(BushoCd) {
            var vue = this;
            if (BushoCd != vue.viewModel.BushoCd) {
                $(vue.$el).closest(".ui-dialog-content").dialog("close");
            }
        },
    }
}
</script>
