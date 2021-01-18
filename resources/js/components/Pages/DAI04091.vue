<template>
    <form id="this.$options.name">
        <div class="row">
            <div class="col-md-1">
                <label>部署</label>
            </div>
            <div class="col-md-4">
                <VueSelect v-if="params.IsCTI || (!!params.targets && params.targets.length > 0 && !!params.targets[0].部署ＣＤ)"
                    id="Busho"
                    ref="VueSelectBusho"
                    :vmodel=viewModel
                    bind="BushoCd"
                    buddy="BushoNm"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    :onChangedFunc=onBushoChanged
                />
                <VueSelectBusho v-else
                    ref="VueSelectBusho"
                    :hasNull=false
                    :onChangedFunc=onBushoChanged
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
                <label class="text-left">部署</label>
            </div>
            <div class="col-md-4">
                <VueSelect v-if="params.IsCTI || (!!params.targets && params.targets.length > 1 && !!params.targets[1].部署ＣＤ)"
                    id="Busho"
                    ref="VueSelectBushoOthers"
                    :vmodel=others
                    bind="BushoCd"
                    buddy="BushoNm"
                    uri="/Utilities/GetBushoList"
                    :hasNull=true
                    :withCode=true
                    :onChangedFunc=onBushoChanged
                />
                <VueSelectBusho v-else
                    ref="VueSelectBushoOthers"
                    :vmodel=others
                    bind="BushoCd"
                    :hasNull=false
                    :onChangedFunc=onBushoChangedOthers
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>コース</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="CourseSelect"
                    ref="PopupSelect_Course"
                    :vmodel=viewModel
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ bushoCd: viewModel.BushoCd }"
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
                    :inputWidth=100
                    :nameWidth=210
                    :onAfterChangedFunc=onCourseChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
                <label class="text-left">コース</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="CourseSelectOthers"
                    ref="PopupSelect_CourseOthers"
                    :vmodel=others
                    bind="CourseCd"
                    :buddies='{ CourseNm: "コース名", TantoCd: "担当者ＣＤ", TantoNm: "担当者名" }'
                    dataUrl="/Utilities/GetCourseList"
                    :params="{ bushoCd: others.BushoCd }"
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
                    :inputWidth=100
                    :nameWidth=210
                    :onAfterChangedFunc=onCourseChangedOthers
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=CourseAutoCompleteFunc
                    :ParamsChangedCheckFunc=CourseParamsChangedCheckFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>担当者</label>
            </div>
            <div class="col-md-4">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=viewModel.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 275px;" type="text" :value=viewModel.TantoNm readonly tabindex="-1">
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
                <label class="text-left">担当者</label>
            </div>
            <div class="col-md-4">
                <input class="form-control label-blue" style="width: 100px;" type="text" :value=others.TantoCd readonly tabindex="-1">
                <input class="form-control ml-1 label-blue" style="width: 275px;" type="text" :value=others.TantoNm readonly tabindex="-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>種別/備考</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="MngCdSelect"
                    ref="PopupSelect_MngCd"
                    :vmodel=viewModel
                    bind="MngCd"
                    :buddies='{ Kind: "一時フラグ", KindNm: "種別", StartDate: "適用開始日", EndDate: "適用終了日", Memo: "備考" }'
                    dataUrl="/Utilities/GetCourseTableMngForMaint"
                    :params="{ BushoCd: viewModel.BushoCd, CourseCd: viewModel.CourseCd, WithNew: true, noCache: true, }"
                    :isPreload=true
                    title="コーステーブル一覧"
                    labelCd="種別"
                    :showColumns='[
                        { title: "コースＣＤ", dataIndx: "コースＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 1 },
                        { title: "適用開始日", dataIndx: "適用開始日", dataType: "date", format: "yy/mm/dd", width: 100, maxWidth: 100, minWidth: 100,},
                        { title: "適用終了日", dataIndx: "適用終了日", dataType: "date", format: "yy/mm/dd", width: 100, maxWidth: 100, minWidth: 100,},
                    ]'
                    :popupWidth=800
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: '新規'}]"
                    :inputWidth=100
                    :nameWidth=210
                    :onAfterChangedFunc=onMngCdChanged
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=MngCdAutoCompleteFunc
                    :ParamsChangedCheckFunc=MngCdParamsChangedCheckFunc
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
                <label class="text-left">種別/備考</label>
            </div>
            <div class="col-md-4">
                <PopupSelect
                    id="MngCdSelectOthers"
                    ref="PopupSelect_MngCdOthers"
                    :vmodel=others
                    bind="MngCd"
                    :buddies='{ Kind: "一時フラグ", KindNm: "種別", StartDate: "適用開始日", EndDate: "適用終了日", Memo: "備考" }'
                    dataUrl="/Utilities/GetCourseTableMngForMaint"
                    :params="{ BushoCd: others.BushoCd, CourseCd: others.CourseCd, WithNew: true, noCache: true, }"
                    :isPreload=true
                    title="コーステーブル一覧"
                    labelCd="種別"
                    :showColumns='[
                        { title: "コースＣＤ", dataIndx: "コースＣＤ", dataType: "integer", width: 100, maxWidth: 100, minWidth: 100, colIndx: 0 },
                        { title: "コース名", dataIndx: "コース名", dataType: "string", width: 100, maxWidth: 100, minWidth: 100, colIndx: 1 },
                        { title: "適用開始日", dataIndx: "適用開始日", dataType: "date", format: "yy/mm/dd", width: 100, maxWidth: 100, minWidth: 100,},
                        { title: "適用終了日", dataIndx: "適用終了日", dataType: "date", format: "yy/mm/dd", width: 100, maxWidth: 100, minWidth: 100,},
                    ]'
                    :popupWidth=800
                    :popupHeight=600
                    :isShowName=true
                    :isModal=true
                    :editable=true
                    :reuse=true
                    :existsCheck=true
                    :exceptCheck="[{Cd: '新規'}]"
                    :inputWidth=100
                    :nameWidth=210
                    :onAfterChangedFunc=onMngCdChangedOthers
                    :isShowAutoComplete=true
                    :AutoCompleteFunc=MngCdAutoCompleteFunc
                    :ParamsChangedCheckFunc=MngCdParamsChangedCheckFunc
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-1">
                <label>適用期間</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="StartDate"
                    ref="DatePicker_StartDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="StartDate"
                    :editable=true
                    :hideButton=true
                />
                <span class="ml-2 mr-2">～</span>
                <DatePickerWrapper
                    id="EndDate"
                    ref="DatePicker_EndDate"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=viewModel
                    bind="EndDate"
                    :editable=true
                    :hideButton=true
                />
            </div>
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
                <label class="text-left">適用期間</label>
            </div>
            <div class="col-md-4">
                <DatePickerWrapper
                    id="StartDateOthers"
                    ref="DatePicker_StartDateOthers"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=others
                    bind="StartDate"
                    :editable=true
                    :hideButton=true
                />
                <span class="ml-2 mr-2">～</span>
                <DatePickerWrapper
                    id="EndDateOthers"
                    ref="DatePicker_EndDateOthers"
                    format="YYYY年MM月DD日"
                    dayViewHeaderFormat="YYYY年MM月"
                    :vmodel=others
                    bind="EndDate"
                    :editable=true
                    :hideButton=true
                />
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 align-items-start">
                <PqGridWrapper
                    id="DAI04091Grid1"
                    ref="DAI04091Grid1"
                    dataUrl="/Utilities/GetCustomerListWithTemp"
                    :query="{ bushoCd: viewModel.BushoCd, courseCd: viewModel.CourseCd, mngCd: viewModel.MngCd, noCache: true, }"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=grid1Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    :onChangeFunc=onChangeGrid
                    :resizeFunc=onMainGridResize
                    :isMultiRowSelectable=true
                    :autoToolTipDisabled=true
                    :setCustomTitle='(title, grid) => "件数: " + grid.getData().filter(v => !!v.得意先ＣＤ).length'
                    classes="mt-1 mb-1"
                />
            </div>
            <div class="col-md-2">
                <div class="btn-group-vertical w-100 d-flex align-items-center moveButtons">
                    <fieldset class="text-center moveSelection">
                        <legend class="moveLegend">選択行</legend>
                        <table>
                            <tr>
                                <td>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveFirst">
                                            <legend class="moveLegend">先頭に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveAt">
                                            <legend class="moveLegend">選択位置に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveLast">
                                            <legend class="moveLegend">末尾に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <fieldset class="text-center moveWhole">
                        <table>
                            <tr>
                                <td>
                                    <legend class="moveLegend">全行</legend>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveFirst">
                                            <legend class="moveLegend">先頭に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-double-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-double-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveAt">
                                            <legend class="moveLegend">選択位置に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-double-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-double-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="w-100 d-flex justify-content-center">
                                        <fieldset class="text-center moveLast">
                                            <legend class="moveLegend">末尾に<span class="moveAction">移動</span></legend>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-light toLeft">
                                                    <span><i class="fa fa-angle-double-left fa-lg"></i>左へ</span>
                                                </button>
                                                <button type="button" class="btn btn-light toRight">
                                                    <span>右へ<i class="fa fa-angle-double-right fa-lg"></i></span>
                                                </button>
                                            </div>
                                        </fieldset>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </div>
            </div>
            <div class="col-md-5 align-items-start">
                <PqGridWrapper
                    id="DAI04091Grid2"
                    ref="DAI04091Grid2"
                    dataUrl="/Utilities/GetCustomerListWithTemp"
                    :query="{ bushoCd: viewModel.BushoCd, courseCd: others.CourseCd, mngCd: others.MngCd, noCache: true, }"
                    :SearchOnCreate=false
                    :SearchOnActivate=false
                    :options=grid2Options
                    :onAfterSearchFunc=onAfterSearchFunc
                    :onChangeFunc=onChangeGrid
                    :resizeFunc=onSubGridResize
                    :isMultiRowSelectable=true
                    :autoToolTipDisabled=true
                    :setCustomTitle='(title, grid) => "件数: " + grid.getData().filter(v => !!v.得意先ＣＤ).length'
                    classes="mt-1 mb-1"
                />
            </div>
        </div>
    </form>
</template>

<style scoped>
fieldset {
    width: 90%;
    display: flex;
    justify-content: center;
    border: 1px solid gray;
    padding: 0;
    padding-bottom: 5px;
    margin: 0;
    margin-bottom: 5px;
}
.moveLegend {
    width: auto;
    font-size: 15px;
    margin: 0;
}
.moveAction {
    font-weight: bold;
    color: green;
}
.moveAction.copy {
    color: orange;
}
.moveButtons button {
    width: 55px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    margin-left: 8px;
    margin-right: 8px;
}
.moveButtons button span {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<style>
form[pgid="DAI04091"] .pq-grid .DAI04091_toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
form[pgid="DAI04091"] .pq-grid .DAI04091_toolbar .toolbar_button {
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
form[pgid="DAI04091"] .pq-grid .DAI04091_toolbar .toolbar_button > i {
    margin: 0px;
}
form[pgid="DAI04091"] .pq-grid .DAI04091_toolbar .toolbar_button > i > span {
    font-size: 12px !important;
}
form[pgid="DAI04091"] .pq-grid .DAI04091_toolbar .toolbar_button.ope {
    width: 45px;
}
form[pgid="DAI04091"] .pq-grid .searchBtn {
    width: 40px;
    height: 30px;
    padding: 0px !important;
    border: none;
    border-radius: 0;
}
</style>

<script>
import PageBaseMixin from "@vcs/PageBaseMixin.vue";

export default {
    mixins: [PageBaseMixin],
    name: "DAI04091",
    components: {
    },
    computed: {
        FormattedKojoKbn: function() {
            var vue = this;
            return vue.viewModel.KojoKbn ? moment(vue.viewModel.KojoKbn, "YYYY年MM月DD日").format("YYYYMMDD") : null;
        },
        grid2Options: function() {
            var vue = this;
            var options = _.cloneDeep(vue.grid1Options);
            options.idx = 1;
            // options.editable = false;
            // options.columnTemplate.editable = false;
            // options.dropModel.on = false;
            return options;
        },
        canSearch: function() {
            var vue = this;

            return !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && !!vue.viewModel.MngCd;
        },
        canSave: function() {
            var vue = this;

            return !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && !!vue.viewModel.MngCd
                && !!vue.viewModel.StartDate && !!vue.viewModel.EndDate;
        },
        canSearchOthers: function() {
            var vue = this;

            return !!vue.others.BushoCd && !!vue.others.CourseCd && !!vue.others.MngCd;
        },
        canSaveOthers: function() {
            var vue = this;

            return !!vue.others.BushoCd && !!vue.others.CourseCd && !!vue.others.MngCd
                && !!vue.others.StartDate && !!vue.others.EndDate;
        },
    },
    watch: {
        canSearch: {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI04091_Search").disabled = !newVal;
                vue.footerButtons.find(v => v.id == "DAI04091_SearchBoth").disabled = !newVal || !vue.canSearchOthers;
            },
        },
        canSave: {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI04091_Save").disabled = !newVal;
                vue.footerButtons.find(v => v.id == "DAI04091_SaveBoth").disabled = !newVal || !vue.canSearchOthers;
            },
        },
        canSearchOthers: {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI04091_SearchOthers").disabled = !newVal;
                vue.footerButtons.find(v => v.id == "DAI04091_SearchBoth").disabled = !newVal || !vue.canSearch;
            },
        },
        canSaveOthers: {
            handler: function(newVal) {
                var vue = this;
                vue.footerButtons.find(v => v.id == "DAI04091_SaveOthers").disabled = !newVal;
                vue.footerButtons.find(v => v.id == "DAI04091_SaveBoth").disabled = !newVal || !vue.canSearch;
            },
        },
    },
    data() {
        var vue = this;
        var data = $.extend(true, {}, PageBaseMixin.data(), {
            ScreenTitle: "マスタメンテ > コーステーブルメンテ",
            noViewModel: true,
            conditionTrigger: true,
            viewModel: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                CourseKbn: null,
                TantoCd: null,
                TantoNm: null,
                MngCd: null,
                Kind: null,
                KindNm: null,
                StartDate: null,
                EndDate: null,
                Memo: null,
            },
            others: {
                BushoCd: null,
                BushoNm: null,
                CourseCd: null,
                CourseNm: null,
                CourseKbn: null,
                TantoCd: null,
                TantoNm: null,
                MngCd: null,
                Kind: null,
                KindNm: null,
                StartDate: null,
                EndDate: null,
                Memo: null,
            },
            DAI04091Grid1: null,
            DAI04091Grid2: null,
            CustomerList: [],
            CustomerListOthers: [],
            grid1Options: {
                idx: 0,
                selectionModel: { type: "row", mode: "block", row: true },
                showHeader: true,
                showToolbar: true,
                toolbar: {
                    cls: "DAI04091_toolbar",
                    items: [
                        {
                            name: "add",
                            type: "button",
                            label: "<i class='fa fa-plus fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;

                                var rowIndx = grid.SelectRow().getSelection().length == 0
                                    ? 0
                                    : (grid.SelectRow().getFirst() + 1);

                                grid.addRow({
                                    rowIndx: rowIndx,
                                    newRow: {},
                                    checkEditable: false
                                });

                                grid.scrollRow({rowIndxPage: rowIndx});
                            },
                            attr: 'class="toolbar_button add" title="行追加"',
                            options: { disabled: false },
                        },
                        {
                            name: "delete",
                            type: "button",
                            label: "<i class='fa fa-minus fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                var rowList = grid.SelectRow().getSelection().map(v => _.pick(v, ["rowIndx"]));
                                grid.deleteRow({ rowList: rowList });
                            },
                            attr: 'class="toolbar_button delete" title="行削除" delete disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "first",
                            type: "button",
                            label: "<i class='fa fa-angle-double-up fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toFirst" title="先頭へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "upward",
                            type: "button",
                            label: "<i class='fa fa-angle-up fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toUpward" title="上へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "downward",
                            type: "button",
                            label: "<i class='fa fa-angle-down fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toDownward" title="下へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "last",
                            type: "button",
                            label: "<i class='fa fa-angle-double-down fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                vue.moveNodesSelf(event, grid, vue);
                            },
                            attr: 'class="toolbar_button toLast" title="末尾へ移動" disabled=disabled',
                            options: { disabled: false },
                        },
                        {
                            name: "undo",
                            type: "button",
                            label: "<i class='fa fa-undo fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                grid.history({ method: "undo" });
                            },
                            attr: 'class="toolbar_button ope undo" title="元に戻す" disabled=disabled',
                            options: { disabled: true },
                        },
                        {
                            name: "redo",
                            type: "button",
                            label: "<i class='fa fa-redo fa-lg'></i>",
                            listener: function (event) {
                                var grid = this;
                                grid.history({ method: "redo" });
                            },
                            attr: 'class="toolbar_button ope redo" title="やり直し" disabled=disabled',
                        },

                    ]
                },
                columnBorders: true,
                fillHandle: "",
                numberCell: { show: true, title: "No.", resizable: false, width: 55, minWidth: 55 },
                autoRow: false,
                rowHtHead: 30,
                rowHt: 30,
                freezeCols: 2,
                width: "100%",
                height: 450,
                editable: true,
                columnTemplate: {
                    editable: false,
                    sortable: false,
                },
                dataModel: { recIndx: "得意先ＣＤ" },
                trackModel: { on: true },
                historyModel: { on: true },
                editModel: {
                    clicksToEdit: 1,
                    keyUpDown: true,
                    saveKey: $.ui.keyCode.ENTER,
                    onSave: null,
                    onTab: null,
                },
                filterModel: {
                    on: false,
                    mode: "AND",
                    header: false,
                    menuIcon: false,
                    hideRows: false,
                },
                draggable: true,
                dragModel: {
                    on: true,
                    diHelper: ["コード", "名称"],
                    cssHelper: {
                        opacity: 0.8,
                        position:"absolute",
                        height: 25,
                        width: "auto",
                        overflow: "hidden",
                        background: "white",
                        border: "1px groove",
                        boxShadow:"4px 4px 2px gray",
                        zIndex: 4001,
                    },
                    dragNodes: (rd, evt) => {
                        var grid = eval("this");
                        // console.log("dragNodes", evt);
                        var target = grid.SelectRow().getSelection().map(v => v.rowData);
                        target = target.length && target.includes(rd) ? target : [rd];
                        //target = _.cloneDeep(target).map(v => _.omitBy(v, (v, k) => k.startsWith("pq") || k == "InitialValue"));
                        return target;
                    },
                    beforeDrop: function(evt, ui) {
                        var grid = eval("this");

                        if (!evt.ctrlKey) {
                            var nodes = _.cloneDeep(grid.Drag().getUI().nodes);
                            console.log("beforeDrop deleteNodes", nodes);
                            grid.deleteNodes(nodes);
                        }
                    },
                },
                dropModel: {
                    on: true,
                    isDroppable: function(evt, ui) {
                        var grid = eval("this");

                        var dragGrid = $(evt.target).closest(".pq-grid").pqGrid("getInstance").grid;
                        var dropGrid = ui.$cell.closest(".pq-grid").pqGrid("getInstance").grid;

                        var ret = true;
                        if (dragGrid.widget().prop("id") != dropGrid.widget().prop("id")) {
                            var nodes = dragGrid.Drag().getUI().nodes;
                            ret = _.intersectionBy(nodes, dropGrid.getData(), "得意先ＣＤ").length == 0;
                        }

                        return ret;
                    },
                    enter: function(event, grid) {
                        // console.log("dropModel enter", event, grid, grid.Drag().getUI())

                        // var dragGrid = $(evt.target).closest(".pq-grid").pqGrid("getInstance").grid;
                        // var dropGrid = ui.$cell.closest(".pq-grid").pqGrid("getInstance").grid;

                        // var ret = true;
                        // if (dragGrid.widget().prop("id") != dropGrid.widget().prop("id")) {
                        //     var nodes = dragGrid.Drag().getUI().nodes;
                        //     ret = _.intersectionBy(nodes, dropGrid.getData(), "得意先ＣＤ").length == 0;
                        // }

                        // return ret;
                    },
                    // customDrop: function(evt, ui) {
                    //     var grid = eval("this");
                    //     console.log("customDrop", grid.options.dropModel.orgDrop)

                    //     grid.options.dropModel.orgDrop(evt, ui);
                    // },
                    // drop: function(evt, ui) {
                    //     console.log("drop", event, ui);

                    //     var from = ui.draggable.closest(".pq-grid").pqGrid("getInstance").grid;
                    //     var to = eval("this");
                    //     var isSame = _.isEqual(from, to);

                    //     var fromNodes = from.Drag().getUI().nodes;
                    //     var toNodes = _.cloneDeep(fromNodes).map(v => {
                    //         delete v.pq_ri;
                    //         return v;
                    //     });

                    //     var moveAt = ui.rowIndx;
                    //     var isCopy = event.ctrlKey;

                    //     console.log("drop", fromNodes, toNodes);

                    //     if (isSame) {
                    //         to.moveNodes(fromNodes , moveAt);
                    //         // to.addNodes(nodes , moveAt);
                    //         // from.deleteNodes(nodes);
                    //     } else {
                    //         if (!isCopy) {
                    //             from.deleteNodes(fromNodes);
                    //         }
                    //         to.addNodes(toNodes , moveAt);
                    //     }
                    //     to.scrollRow({rowIndxPage: moveAt + (fromNodes.length - 1)});
                    // },
                },
                moveNode: ( event, ui ) => {
                    var grid = $(event.target).pqGrid("getInstance").grid;
                    console.log("moveNode", event, ui);

                    //reset pq_ri
                    grid.pdata.forEach((v, i) => v.pq_ri = i)

                    // vue.resetData(grid);
                },
                formulas: [
                    [
                        "Content",
                        function(rowData){
                            return _.keys(rowData)
                                .filter(k => !k.startsWith("pq") && k != "Content" && k != "InitialValue")
                                .map(k => rowData[k])
                                .join(",");
                        }
                    ],
                    [
                        "コード",
                        function(rowData){
                            return rowData.得意先ＣＤ;
                        }
                    ],
                    [
                        "名称",
                        function(rowData){
                            return rowData.得意先名;
                        }
                    ],
                    [
                        "pq_label",
                        function(rowData){
                            var $el = $("<div>")
                                .addClass("d-flex")
                                .append($("<div>").text(rowData.得意先ＣＤ || rowData.コード).width(60).addClass("text-right"))
                                .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                                .append($("<div>").text(rowData.得意先名 || rowData.名称))
                                ;

                            return $el[0].outerHTML;
                        }
                    ],
                ],
                colModel: [
                    {
                        title: "コード",
                        dataIndx: "コード",
                        dataType: "string",
                        key: true,
                        editable: true,
                        width: 100, maxWidth: 100, minWidth: 100,
                        autocomplete: {
                            source: ui => vue.GetCustomerListForSelect(ui),
                            bind: "得意先ＣＤ",
                            buddies: { "得意先名": "CdNm" },
                            AutoCompleteFunc: vue.CustomerAutoCompleteFuncInGrid,
                            AutoCompleteMinLength: 0,
                            selectSave: true,
                        },
                    },
                    {
                        title: "",
                        dataIndx: "SearchBtn",
                        dataType: "string",
                        editable: false,
                        width: 40, maxWidth: 40, minWidth: 40,
                        render: ui => {
                            var $btn = $("<button>")
                                .attr("type", "button")
                                .addClass("btn")
                                .addClass("btn-info")
                                .addClass("searchBtn")
                                .append(
                                    $("<i>").addClass("fa").addClass("fa-search").addClass("fa-lg")
                                )
                                ;

                            return $btn.prop("outerHTML");
                        },
                        postRender: ui => {
                            var g = eval("this");
                            var gridCell = $(ui.cell);
                            var model = g.options.idx == 0 ? vue.viewModel : vue.others;

                            gridCell.find("button").on("click", ev => {
                                PageDialog.showSelector({
                                    dataUrl: "/DAI04091/GetCustomerListForSelect",
                                    params: { BushoCd: model.BushoCd },
                                    title: "得意先一覧",
                                    labelCd: "得意先ＣＤ",
                                    labelCdNm: "得意先名",
                                    isModal: true,
                                    showColumns: [],
                                    width: 600,
                                    height: 600,
                                    reuse: true,
                                    selector: (cGrid) => {
                                        console.log("4091 selector");
                                        var cd = ui.rowData.得意先ＣＤ || ui.rowData.コード;
                                        var hits = cGrid.search({ row: { Cd: cd } });
                                        var rowIndx = !hits.length ? 0 : hits[0].rowIndx;
                                        cGrid.SelectRow().removeAll();
                                        cGrid.setSelection({ rowIndx: rowIndx });
                                        setTimeout(
                                            () => cGrid.scrollRow( { rowIndxPage: rowIndx } ),
                                            100
                                        );
                                    },
                                    buttons: [
                                        {
                                            text: "選択",
                                            class: "btn btn-primary",
                                            shortcut: "Enter",
                                            click: function(gridVue, grid) {
                                                if (event.target.name == "SearchStrings" && event.type == "keydown" && (event.key == "Process" || event.which == 13)) {
                                                    return false;
                                                }

                                                var rowIndx = grid.SelectRow().getFirst();

                                                if (rowIndx != undefined) {
                                                    var rowData = grid.getRowData({ rowIndx: rowIndx });
                                                    ui.rowData.得意先ＣＤ = ui.rowData.コード = rowData.Cd;
                                                    ui.rowData.得意先名 = ui.rowData.名称 = rowData.CdNm;
                                                    g.refresh();
                                                }
                                            },
                                        },
                                    ],
                                });

                                ev.preventDefault();
                                return false;
                            });

                            return ui;
                        },
                    },
                    {
                        title: "名称",
                        dataIndx: "名称",
                        dataType: "string",
                    },
                    // {
                    //     title: "pq_label",
                    //     dataIndx: "pq_label",
                    //     dataType: "string",
                    //     hidden: false,
                    // },
                    // {
                    //     title: "得意先",
                    //     dataIndx: "得意先ＣＤ",
                    //     dataType: "string",
                    //     key: true,
                    //     psProps: {
                    //         dataUrl: "/DAI04091/GetCustomerListForSelect",
                    //         //params: { bushoCd: () => { var val = !!vue.viewModel ? vue.viewModel.BushoCd : null; console.log("psProps params", vue, val); return val; } },
                    //         params: vue.getCustomerPsParamsInGrid,
                    //         bind: "得意先ＣＤ",
                    //         buddies: { "得意先名": "CdNm" },
                    //         isPreload: true,
                    //         title: "得意先一覧",
                    //         labelCd: "得意先CD",
                    //         labelCdNm: "得意先名",
                    //         popupWidth: 600,
                    //         popupHeight: 600,
                    //         isShowName: true,
                    //         isModal: true,
                    //         reuse: true,
                    //         existsCheck: true,
                    //         inputWidth: 90,
                    //         nameWidth: 195,
                    //         onAfterChangedFunc: vue.onCustomerChangedInGrid,
                    //         isShowAutoComplete: true,
                    //         AutoCompleteFunc: vue.CustomerAutoCompleteFuncInGrid,
                    //         ParamsChangedCheckFunc: vue.CustomerParamsChangedCheckFuncInGrid,
                    //         getData: (ui, grid) => {
                    //             console.log("psprops getData", ui.$cell.find(".target-input").val());
                    //             return ui.$cell.find(".target-input").val();
                    //         },
                    //         htmlRender: ui => {
                    //             var $el = $("<div>")
                    //                 .addClass("d-flex")
                    //                 .append($("<div>").text(ui.rowData.得意先ＣＤ).width(60).addClass("text-right"))
                    //                 .append($("<div>").text(":").addClass("pl-1").addClass("pr-1"))
                    //                 .append($("<div>").text(ui.rowData.得意先名))
                    //                 ;

                    //             return $el[0];
                    //         },
                    //     },
                    // },
                    // {
                    //     title: "部署ＣＤ",
                    //     dataIndx: "部署ＣＤ",
                    //     dataType: "integer",
                    //     hidden: true,
                    //     key: true,
                    // },
                    // {
                    //     title: "ＳＥＱ",
                    //     dataIndx: "ＳＥＱ",
                    //     dataType: "integer",
                    //     hidden: true,
                    //     key: true,
                    // },
                ],
            },
        });

        var targets;
        if (!!vue.params || !!vue.query) {
            targets = (vue.params || vue.query).targets;
        }

        if (!targets) return data;

        if (targets[0]) {
            data.viewModel.BushoCd = targets[0].部署ＣＤ;
            data.viewModel.CourseCd = targets[0].コースＣＤ;
            data.viewModel.MngCd = targets[0].管理ＣＤ;
            data.viewModel.CourseKbn = targets[0].コース区分;
        }

        if (targets[1]) {
            data.others.BushoCd = targets[1].部署ＣＤ;
            data.others.CourseCd = targets[1].コースＣＤ;
            data.others.MngCd = targets[1].管理ＣＤ;
            data.others.CourseKbn = targets[1].コース区分;
        }

        return data;
    },
    methods: {
        createdFunc: function(vue) {
            vue.footerButtons.push(
                { visible: "true", value: "再検索(左)", id: "DAI04091_Search", disabled: true, shortcut: "F2",
                    onClick: function () {
                        vue.conditionChanged(true);
                    }
                },
                { visible: "true", value: "保存(左)", id: "DAI04091_Save", disabled: true, shortcut: "F3",
                    onClick: function () {
                        vue.saveCourse();
                    }
                },
                { visible: "true", value: "削除(左)", id: "DAI04091_Delete", disabled: true, shortcut: "F4",
                    onClick: function () {
                        vue.deleteCourse();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "再検索(両方)", id: "DAI04091_SearchBoth", disabled: true, shortcut: "F5",
                    onClick: function () {
                        vue.conditionChangedBoth(true);
                    }
                },
                { visible: "true", value: "保存(両方)", id: "DAI04091_SaveBoth", disabled: true, shortcut: "F6",
                    onClick: function () {
                        vue.saveCourseBoth();
                    }
                },
                {visible: "false"},
                { visible: "true", value: "再検索(右)", id: "DAI04091_SearchOthers", disabled: true, shortcut: "F9",
                    onClick: function () {
                        vue.conditionChangedOthers(true);
                    }
                },
                { visible: "true", value: "保存(右)", id: "DAI04091_SaveOthers", disabled: true, shortcut: "F10",
                    onClick: function () {
                        vue.saveCourseOthers();
                    }
                },
                { visible: "true", value: "削除(右)", id: "DAI04091_DeleteOthers", disabled: true, shortcut: "F11",
                    onClick: function () {
                        vue.deleteCourseOthers();
                    }
                },
            );
        },
        mountedFunc: function(vue) {
            //keydown handler
            $(document)
            .on("keydown", event => {
                if (event.key == "Control") {
                    $(vue.$el).find(".moveAction").text("コピー").addClass("copy");
                }
                return true;
            })
            .on("keyup", event => {
                if (event.key == "Control") {
                    $(vue.$el).find(".moveAction").text("移動").removeClass("copy");
                }
                return true;
            });

            var empties = _.range(0, 15)
            .map(v => {
                return {
                    部署ＣＤ: null,
                    コースＣＤ: null,
                    ＳＥＱ: null,
                    得意先ＣＤ: null,
                    得意先名: null,
                };
            });

            vue.DAI04091Grid1.setLocalData(_.cloneDeep(empties));
            vue.DAI04091Grid2.setLocalData(_.cloneDeep(empties));

            vue.footerButtons.find(v => v.id == "DAI04091_Search").disabled = !vue.canSearch;
            vue.footerButtons.find(v => v.id == "DAI04091_SearchOthers").disabled = !vue.canSearchOthers;
            vue.footerButtons.find(v => v.id == "DAI04091_SearchBoth").disabled = !vue.canSearch || !vue.canSearchOthers;

            //move node buttons
            $(vue.$el).find(".moveButtons .btn").on("click", event => vue.moveNodes(event, vue));

            //watcher
            vue.$watch("$refs.DAI04091Grid1.isSelection", ret => vue.changeToolbarButtons(vue.DAI04091Grid1, vue.$refs.DAI04091Grid1));
            vue.$watch("$refs.DAI04091Grid1.isSelectionFirst", ret => vue.changeToolbarButtons(vue.DAI04091Grid1, vue.$refs.DAI04091Grid1));
            vue.$watch("$refs.DAI04091Grid1.isSelectionLast", ret => vue.changeToolbarButtons(vue.DAI04091Grid1, vue.$refs.DAI04091Grid1));
            vue.$watch("$refs.DAI04091Grid2.isSelection", ret => vue.changeToolbarButtons(vue.DAI04091Grid2, vue.$refs.DAI04091Grid2));
            vue.$watch("$refs.DAI04091Grid2.isSelectionFirst", ret => vue.changeToolbarButtons(vue.DAI04091Grid2, vue.$refs.DAI04091Grid2));
            vue.$watch("$refs.DAI04091Grid2.isSelectionLast", ret => vue.changeToolbarButtons(vue.DAI04091Grid2, vue.$refs.DAI04091Grid2));
        },
        changeToolbarButtons: function(grid, gridVue) {
            var isDisabled = !gridVue.isSelection;
            var isFirst = gridVue.isSelectionFirst;
            var isLast = gridVue.isSelectionLast;

            grid.widget().find(".toolbar_button.delete").prop("disabled", isDisabled);
            grid.widget().find(".toolbar_button.toFirst").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toUpward").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toDownward").prop("disabled", isDisabled || isLast);
            grid.widget().find(".toolbar_button.toLast").prop("disabled", isDisabled || isLast);
        },

        changeToolbarButtonsAfter: function(grid, gridVue, moveAt, lastmoveAt) {
            var isDisabled = !gridVue.isSelection;
            var isFirst = false
            var isLast = false;

            if (moveAt == 0) {
                isFirst = true;
            }else if (moveAt == lastmoveAt) {
                isLast = true;
            }

            grid.widget().find(".toolbar_button.delete").prop("disabled", isDisabled);
            grid.widget().find(".toolbar_button.toFirst").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toUpward").prop("disabled", isDisabled || isFirst);
            grid.widget().find(".toolbar_button.toDownward").prop("disabled", isDisabled || isLast);
            grid.widget().find(".toolbar_button.toLast").prop("disabled", isDisabled || isLast);
        },

        onBushoChanged: function(code, entity) {
            var vue = this;

            // 得意先マスタ事前検索
            axios.post("/DAI04091/GetCustomerListForSelect", { bushoCd: code, noCache: true, })
                .then(res => vue.CustomerList = res.data);

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onCourseChanged: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onMngCdChanged: function(code, entity, comp) {
            var vue = this;

            var selectName = $(comp.$el).find(".select-name:visible");
            selectName.prop("disabled", !code || !code.includes("新規"))
                      .prop("placeholder", "備考(理由や用途)");

            vue.footerButtons.find(v => v.id == "DAI04091_Delete").disabled = code == 0;

            //条件変更ハンドラ
            vue.conditionChanged();
        },
        onBushoChangedOthers: function(code, entity) {
            var vue = this;

            // 得意先マスタ事前検索
            axios.post("/DAI04091/GetCustomerListForSelect", { bushoCd: code, noCache: true, })
                .then(res => vue.CustomerListOthers = res.data);

            //条件変更ハンドラ
            vue.conditionChangedOthers();
        },
        onCourseChangedOthers: function(code, entity) {
            var vue = this;

            //条件変更ハンドラ
            vue.conditionChangedOthers();
        },
        onMngCdChangedOthers: function(code, entity, comp) {
            var vue = this;

            var selectName = $(comp.$el).find(".select-name:visible");
            selectName.prop("disabled", !code || !code.includes("新規"))
                      .prop("placeholder", "備考(理由や用途)");

            vue.footerButtons.find(v => v.id == "DAI04091_DeleteOthers").disabled = code == 0;

            //条件変更ハンドラ
            vue.conditionChangedOthers();
        },
        conditionChanged: function(forced) {
            var vue = this;
            var grid1 = vue.DAI04091Grid1;

            if (!!grid1 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.viewModel.BushoCd && !!vue.viewModel.CourseCd && (!!vue.viewModel.MngCd && !vue.viewModel.MngCd.includes("新規"));
                var bushoChanged = !grid1.prevPostData || grid1.prevPostData.bushoCd != vue.viewModel.BushoCd;
                var courseChanged = !grid1.prevPostData || grid1.prevPostData.courseCd != vue.viewModel.CourseCd;
                var mngCdChanged = !grid1.prevPostData || grid1.prevPostData.mngCd != vue.viewModel.MngCd;

                if (!!bushoChanged) {
                    console.log("get customer list")
                    axios.post("/DAI04091/GetCustomerListForSelect", { bushoCd: vue.viewModel.BushoCd, noCache: true })
                        .then(res => vue.CustomerList = res.data);
                }

                if (required && (forced || bushoChanged || courseChanged || mngCdChanged)) {
                    grid1.searchData({ bushoCd: vue.viewModel.BushoCd, courseCd: vue.viewModel.CourseCd, mngCd: vue.viewModel.MngCd });
                }
            }
        },
        conditionChangedOthers: function(forced) {
            var vue = this;
            var grid2 = vue.DAI04091Grid2;

            if (!!grid2 && vue.getLoginInfo().isLogOn) {
                var required = !!vue.others.BushoCd && !!vue.others.CourseCd && (!!vue.others.MngCd && !vue.others.MngCd.includes("新規"));
                var bushoChanged = !grid2.prevPostData || grid2.prevPostData.bushoCd != vue.others.BushoCd;
                var courseChanged = !grid2.prevPostData || grid2.prevPostData.courseCd != vue.others.CourseCd;
                var mngCdChanged = !grid2.prevPostData || grid2.prevPostData.mngCd != vue.others.MngCd;

                if (!!bushoChanged) {
                    console.log("get customer list others")
                    axios.post("/DAI04091/GetCustomerListForSelect", { bushoCd: vue.others.BushoCd })
                        .then(res => vue.CustomerListOthers = res.data);
                }

                if (required && (forced || bushoChanged || courseChanged || mngCdChanged)) {
                    grid2.searchData({ bushoCd: vue.others.BushoCd, courseCd: vue.others.CourseCd, mngCd: vue.others.MngCd });
                }
            }
        },
        conditionChangedBoth: function(forced) {
            var vue = this;

            Promise.all([
                new Promise((resolve, reject ) => resolve()).then(() => vue.conditionChanged(true)),
                new Promise((resolve, reject ) => resolve()).then(() => vue.conditionChangedOthers(true)),
            ])
            .then(() => {
            })
            .catch(err => {
                console.log(vue.id + " conditionChangedBoth", err);
            })
            ;
        },
        GetCustomerListForSelect: function(ui) {
            var vue = this;
            return vue.CustomerList;
        },

        isUniqueArray: function(SendGrid) {
            var arr = _.cloneDeep(SendGrid.pdata)
            .filter(k=> !!k.コード).map(v => {
                return v.コード;
            })
            var ret = _.filter(arr, (val, i, iteratee) => _.includes(iteratee, val, i + 1));
            return ret.length;
        },

        GetGridCustomerCd: function(SendGrid) {
            var arr = _.cloneDeep(SendGrid.pdata)
            .filter(k=> !!k.コード).map(v => {
                return v.コード;
            })
            var ret = arr
            return ret;
        },

        saveCourse: function(isBoth) {
            var vue = this;
            var grid1 = vue.DAI04091Grid1;

            var params = _.cloneDeep(vue.viewModel);
            params.StartDate = moment(params.StartDate, "YYYY/MM/DD").format("YYYY/MM/DD");
            params.EndDate = moment(params.EndDate, "YYYY/MM/DD").format("YYYY/MM/DD");
            params.idx = 0;

            if (vue.isUniqueArray(grid1) == 0) {
                var CTCustomerCd = vue.GetGridCustomerCd(grid1);
                var CTCourseKbn = vue.viewModel.CourseKbn;
                var StartDate = params.StartDate;
                var EndDate = params.EndDate;
                console.log(StartDate);
                console.log(EndDate);
                console.log("コース区分",CTCourseKbn);
                var tc = new Date().getTime();//axios実行時のキャッシュを無効にするため、現在のタイムスタンプを渡す
                axios.post("/DAI04091/CustomerDuplicateCheck"
                            , {
                                timestamp:tc,
                                BushoCd: vue.viewModel.BushoCd,
                                CustomerCd: grid1.pdata.map(v => v.コード),
                                CourseCd: vue.viewModel.CourseCd,
                                CourseKbn: CTCourseKbn,
                                StartDate,
                                EndDate
                              })
                        .then(res => {
                            var Result = res.data;
                            console.log(Result);
                            if (Result.length == 0) {
                                console.log("セーブします")
                                vue.save(grid1, vue.viewModel, params, isBoth);
                            }
                            else
                            {
                                var course_customer=_.map(Result,v => {return v.コースＣＤ + "コース : " + v.得意先ＣＤ + " " + v.得意先略称 + "<br/>"});
                                console.log(course_customer);
                                $.dialogInfo({
                                title: "重複チェック",
                                contents: "別コースに得意先が存在しています。<br/>" + course_customer.join("")});
                            }
                        });
            }
            else
            {
                $.dialogInfo({
                    title: "重複チェック",
                    contents: "コースの中に得意先が重複しています。",
                });
            }

        },
        saveCourseOthers: function(isBoth) {
            var vue = this;
            var grid2 = vue.DAI04091Grid2;

            var params = _.cloneDeep(vue.others);
            params.idx = 1;

            if (vue.isUniqueArray(grid2) == 0) {

                vue.save(grid2, vue.others, params, isBoth);
            }
            else
            {
                $.dialogInfo({
                    title: "重複チェック",
                    contents: "コースの中に得意先が重複しています。",
                });
            }

        },
        saveCourseBoth: function() {
            var vue = this;
            var grid1 = vue.DAI04091Grid1;
            var grid2 = vue.DAI04091Grid2;

            if (vue.isUniqueArray(grid1) != 0 || vue.isUniqueArray(grid2) != 0) {
                $.dialogInfo({
                    title: "重複チェック",
                    contents: "コースの中に得意先が重複しています。",
                });
            }
            else
            {
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
                                Promise.all([
                                    new Promise((resolve, reject ) => resolve()).then(() => vue.saveCourse(true)),
                                    new Promise((resolve, reject ) => resolve()).then(() => vue.saveCourseOthers(true)),
                                ])
                                .then((res1, res2) => {
                                    console.log("save both promise all", res1, res2);
                                    vue.params.parent.conditionChanged();
                                })
                                .catch(err => {
                                    console.log(vue.id + " saveBoth", err);
                                    $.dialogErr({
                                        title: "異常終了",
                                        contents: "変更内容の保存に失敗しました",
                                    });
                                })
                                ;
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
        save: function(grid, model, params, isBoth) {
            var vue = this;

            params.EditUserCd = vue.getLoginInfo().uid;
            params.Memo =  _.isNaN(params.MngCd * 1)
                ? (
                    params.idx == 0
                        ? vue.$refs.PopupSelect_MngCd.selectName
                        : vue.$refs.PopupSelect_MngCdOthers.selectName
                )
                : params.Memo;
            params.StartDate = moment(params.StartDate, "YYYY年MM月DD日").format("YYYYMMDD");
            params.EndDate = moment(params.EndDate, "YYYY年MM月DD日").format("YYYYMMDD");

            var SaveList = _.cloneDeep(grid.pdata)
            .map(v => {
                v.部署ＣＤ = model.BushoCd ;
                v.コースＣＤ = model.CourseCd;
                v.得意先ＣＤ = v.コード;
                v.得意先名 = v.名称;
                return v;
            })
            .filter(v => !!v.得意先ＣＤ)
            .map((v, i) => {
                var r = {};
                r.部署ＣＤ = v.部署ＣＤ;
                r.コースＣＤ = v.コースＣＤ;
                r.ＳＥＱ = i + 1;
                r.得意先ＣＤ = v.得意先ＣＤ;
                r.修正担当者ＣＤ = vue.getLoginInfo().uid;

                return r;
            });

            if (!!vue.params && !!vue.params.IsCTI  && !!vue.params.CustomerInfo && params.idx == 0) {
                axios.post(
                    "/DAI04091/Save",
                    {
                        Condition: params,
                        SaveList: SaveList,
                    },
                )
                    .then(res => {
                        vue.params.Parent.after04091(vue.params.CustomerInfo);
                        // $(vue.$el).closest(".ui-dialog-content").dialog("close");
                        return;
                    })
                    .catch(err => {
                        console.log(err);
                        $.dialogErr({
                            title: "保存失敗",
                            contents: err.message,
                        });
                    });

                return;
            }

            var Message = {
                "department_code": model.BushoCd,
                "course_code": model.CourseCd,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };

            grid.saveData(
                {
                    uri: "/DAI04091/Save",
                    params: {
                        Condition: params,
                        SaveList: SaveList,
                        Message: Message,
                    },
                    confirm: {
                        isShow: !isBoth,
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.result) {
                                if (!isBoth) {
                                    $.dialogInfo({
                                        title: "保存完了",
                                        contents: "内容の保存が完了しました。",
                                    });

                                    if (!!vue.params && !!vue.params.IsCTI) {

                                    } else {
                                        vue.params.parent.conditionChanged();
                                    }
                                }

                                grid.refreshDataAndView();

                                if (model.MngCd != res.MngCd) {
                                    var comp = params.idx == 0
                                        ? vue.$refs.PopupSelect_MngCd
                                        : vue.$refs.PopupSelect_MngCdOthers;

                                    comp.getDataList(
                                        {
                                            BushoCd: model.BushoCd,
                                            CourseCd: model.CourseCd,
                                            WithNew: true,
                                            noCache: true,
                                        },
                                        () => comp.setSelectValue(res.MngCd),
                                    );
                                }

                            } else {
                                if (!isBoth) {
                                    $.dialogErr({
                                        title: "保存失敗",
                                        contents: res.message,
                                    });
                                }
                            }

                            return false;
                        },
                    },
                    error: {
                        isShow: false,
                        callback: (vue, grid, error)=>{
                            console.log(error);
                            if (!isBoth) {
                                $.dialogErr({
                                    title: "異常終了",
                                    contents: "変更内容の保存に失敗しました",
                                });
                            }
                            return false;
                        },
                    },
                }
            );
        },
        deleteCourse: function() {
            var vue = this;
            var grid1 = vue.DAI04091Grid1;

            var params = _.cloneDeep(vue.viewModel);
            params.idx = 0;

            vue.delete(grid1, vue.viewModel, params);
        },
        deleteCourseOthers: function() {
            var vue = this;
            var grid2 = vue.DAI04091Grid2;

            var params = _.cloneDeep(vue.others);
            params.idx = 1;

            vue.delete(grid2, vue.others, params);
        },
        delete: function(grid, model, params) {
            var vue = this;

            params.EditUserCd = vue.getLoginInfo().uid;

            var Message = {
                "department_code": model.BushoCd,
                "course_code": model.CourseCd,
                "custom_data": {
                    "message": "",
                    "values": {
                        "updateMaster": true,
                    },
                },
            };

            grid.saveData(
                {
                    uri: "/DAI04091/Delete",
                    params: {
                        Condition: params,
                        Message: Message,
                    },
                    confirm: {
                        isShow: true,
                        title: "コーステーブル削除確認",
                        message: "コーステーブルを削除します。宜しいですか？",
                    },
                    done: {
                        isShow: false,
                        callback: (gridVue, grid, res)=>{
                            if (!!res.result) {
                                $.dialogInfo({
                                    title: "削除完了",
                                    contents: "内容の削除が完了しました。",
                                });
                                vue.params.parent.conditionChanged();

                                model.MngCd = null;

                                var comp = params.idx == 0
                                    ? vue.$refs.PopupSelect_MngCd
                                    : vue.$refs.PopupSelect_MngCdOthers;

                                comp.getDataList(
                                    {
                                        BushoCd: model.BushoCd,
                                        CourseCd: model.CourseCd,
                                        WithNew: true,
                                        noCache: true,
                                    }
                                );

                                grid.refreshDataAndView();

                            } else {
                                $.dialogErr({
                                    title: "削除失敗",
                                    contents: res.message,
                                });
                            }

                            return false;
                        },
                    },
                    error: {
                        isShow: false,
                        callback: (vue, grid, error)=>{
                            console.log(error);
                            $.dialogErr({
                                title: "異常終了",
                                contents: "内容の削除に失敗しました",
                            });

                            return false;
                        },
                    },
                }
            );
        },
        CourseAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;

            // console.log("CourseAutoCompleteFunc", comp.id, dataList);

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

            // console.log("CourseAutoCompleteFunc", comp.id, list);

            return list;
        },
        CourseParamsChangedCheckFunc: function(newVal, oldVal, comp) {
            var vue = this;
            // console.log(comp.id + " CourseParamsChangedCheckFunc", newVal);
            return !!newVal.bushoCd;
        },
        MngCdAutoCompleteFunc: function(input, dataList, comp) {
            var vue = this;
            // console.log("MngCdAutoCompleteFunc", input, dataList, comp);

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["種別", "適用開始日", "適用終了日", "備考"];

            if ((input == comp.selectValue && comp.isUnique) || comp.isError) {
                keyAND = keyOR = [];
            }

            var list = dataList
                .filter(v => !!v.Cd)
                .map(v => { v.whole = _(v).pickBy((v, k) => wholeColumns.includes(k)).values().join(""); return v; })
                .filter(v => {
                    return keyOR.length == 0
                        || _.some(keyOR, k => !!v.コースＣＤ ? v.コースＣＤ.startsWith(k) : false)
                        || _.some(keyOR, k => v.whole.includes(k))
                })
                .filter(v => {
                    return keyAND.length == 0 || _.every(keyAND, k => v.whole.includes(k));
                })
                .map(v => {
                    var ret = v;
                    ret.label = v.種別
                              + (!!v.一時フラグ || !!v.備考 ? " : " : "")
                              + (!!v.適用開始日 && !!v.適用終了日 ? (v.適用開始日 + " ～ " + v.適用終了日) : "")
                              + (!!v.備考 ? ("(" + v.備考 + ")") : "");
                    ret.value = v.管理ＣＤ || "";
                    ret.text = v.種別 + (!!v.備考 ? ("(" + v.備考 + ")") : "");
                    return ret;
                })
                ;

            // //新規(一時)用
            // list.unshift({
            //     label: "新規(一時)",
            //     value: "",
            //     text: "新規(一時)",
            // });

            // //新規(基本)用
            // if (!list.some(v => v.一時フラグ == "0")) {
            //     list.unshift({
            //         label: "新規(基本)",
            //         value: "",
            //         text: "新規(基本)",
            //     });
            // }

            return list;
        },
        MngCdParamsChangedCheckFunc: function(newVal, oldVal, comp) {
            var vue = this;
            // console.log("MngCdParamsChangedCheckFunc", newVal, oldVal, comp);
            return !!newVal.BushoCd && newVal.CourseCd;
        },
        onAfterSearchFunc: function (gridVue, grid, res) {
            var vue = this;

            if (res.length < 15) {
                res.push(..._.range(0, 15 - res.length).map(v => {
                    return {
                        部署ＣＤ: null,
                        コースＣＤ: null,
                        ＳＥＱ: null,
                        得意先ＣＤ: null,
                        得意先名: null,
                    };
                }));
            }

            return res;
        },
        onChangeGrid: function(grid, ui, event) {
            var vue = this;
            console.log(grid.widget().prop("id") + " onChangeGrid", ui, event);

            var targetEvents = ["add", "delete", "addNodes", "deleteNodes"];

            if (targetEvents.includes(ui.source)) {
                // vue.resetData(grid);
            }
        },
        resetData: function(grid) {
            var vue = this;

            var rowList = grid.pdata
                .map((v, i) => {
                    var rowData = _.cloneDeep(v);

                    if (v.ＳＥＱ != i + 1) {
                        rowData.ＳＥＱ = i + 1;
                    }
                    if (v.コースＣＤ != grid.prevPostData.courseCd) {
                        rowData.コースＣＤ = grid.prevPostData.courseCd;
                    }

                    var content =  _.keys(rowData)
                        .filter(k => !k.startsWith("pq") && k != "Content" && k != "InitialValue")
                        .map(k => rowData[k])
                        .join(",");

                    if (v.Content != content) {
                        return {
                            rowIndx: i,
                            newRow: { ＳＥＱ: i + 1, Content: content },
                            history: true
                        };
                    } else {
                        return null;
                    }
                })
                .filter(v => !!v);

            console.log("resetData update", rowList);
            grid.updateRow({ rowList: rowList, history: true });
        },
        moveNodes: (event, vue) => {
            console.log("moveNodes", event, vue);
            var grid1 = vue.DAI04091Grid1;
            var grid2 = vue.DAI04091Grid2;

            var $btn = $(event.currentTarget);

            var isSelection = $btn.closest(".moveSelection").length == 1;

            var toRight = $btn.hasClass("toRight");
            var from = toRight ? grid1 : grid2;
            var to = toRight ? grid2 : grid1;

            if (!isSelection) {
                from.SelectRow().selectAll();
            }

            var fromNodes = from.SelectRow().getSelection().length > 0
                ? from.SelectRow().getSelection().map(v => v.rowData)
                : [];

            if (!fromNodes.length) return;
            var toNodes = _.cloneDeep(fromNodes).map(v => {
                delete v.pq_ri;
                return v;
            });

            var moveAt = $btn.closest(".moveFirst").length
                ? 0
                : $btn.closest(".moveLast").length
                    ? to.getData().length
                    : to.SelectRow().getSelection().length
                        ? (_.last(to.SelectRow().getSelection()).rowIndx + 1)
                        : 0;

            var isCopy = event.ctrlKey;

            console.log("moveNodes", fromNodes,toNodes);

            if (!isCopy) {
                from.deleteNodes(fromNodes);
            }
            to.addNodes(toNodes , moveAt);
            to.scrollRow({rowIndxPage: moveAt + (toNodes.length - 1)});
        },
        moveNodesSelf: (event, grid, vue) => {
            var $btn = $(event.currentTarget);

            var nodes = grid.SelectRow().getSelection().map(v => v.rowData);

            var moveAt = 0;
            var lastmoveAt = grid.pdata.length - 1;
            if ($btn.hasClass("toFirst")) {
                moveAt = 0;
                grid.moveNodes(nodes, moveAt);
            } else if ($btn.hasClass("toUpward")) {
                grid.SelectRow().getSelection().forEach(v => {
                    if (v.rowIndx != 0) {
                        grid.moveNodes([v.rowData], v.rowIndx - 1);
                        moveAt = moveAt < v.rowIndx - 1 ? (v.rowIndx - 1) : moveAt ;
                    } else {
                        moveAt = 0;
                    }
                });
            } else if ($btn.hasClass("toDownward")) {
                grid.SelectRow().getSelection().forEach(v => {
                    if (v.rowIndx != grid.pdata.length - 1) {
                        grid.moveNodes([v.rowData], v.rowIndx + 1 + 1);
                        moveAt = moveAt > v.rowIndx + 1 ? moveAt : v.rowIndx + 1;
                    } else {
                        moveAt = grid.pdata.length - 1;
                    }
                });
            } else if ($btn.hasClass("toLast")) {
                moveAt = grid.pdata.length - 1;
                grid.moveNodes(nodes, moveAt + 1);
            }
            console.log("moveNodesSelf", nodes, moveAt);
            grid.scrollRow({rowIndxPage: moveAt});

            if (grid == vue.DAI04091Grid1) {
                console.log("Active Grid is Left.");
                vue.changeToolbarButtonsAfter(grid, vue.$refs.DAI04091Grid1, moveAt, lastmoveAt);
            } else if (grid == vue.DAI04091Grid2) {
                console.log("Active Grid is Right.");
                vue.changeToolbarButtonsAfter(grid, vue.$refs.DAI04091Grid2, moveAt, lastmoveAt);
            }
        },
        onMainGridResize: grid => {

        },
        onSubGridResize: grid => {

        },
        onCustomerChangedInGrid: function(code, entity) {
            var vue = this;
            console.log("onCustomerChangedInGrid", code);
        },
        CustomerAutoCompleteFuncInGrid: function(input, dataList, comp) {
            var vue = this;

            if (!dataList.length) return [];

            var keywords = !!input ? editKeywords((input + "").split(/[, 、　]/).map(v => _.trim(v)).filter(v => !!v)) : [];
            var keyAND = keywords.filter(k => k.match(/^[\+＋]/)).map(k => k.replace(/^[\+＋]/, ""));
            var keyOR = keywords.filter(k => !k.match(/^[\+＋]/));

            var wholeColumns = ["CdNm", "得意先名略称", "得意先名カナ"];

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
        CustomerParamsChangedCheckFuncInGrid: function(newVal, oldVal, comp) {
            var vue = this;
            console.log(comp.id + " CustomerParamsChangedCheckFuncInGrid", newVal);
            return !!newVal.bushoCd;
        },
        getCustomerPsParamsInGrid: (vue, grid) => {
            return { bushoCd: !!vue.viewModel ? vue.viewModel.BushoCd : null };
        },
    }
}
</script>
