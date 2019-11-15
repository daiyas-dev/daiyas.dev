<template>
    <div
        class="form-group d-inline-flex align-items-center mb-0"
        :class='"DatePickerWrapper " + id'
    >
        <span v-if="label" class="text-nowrap d-flex align-items-center mr-1" :for="_id + '_calendar_btn'">{{label}}</span>
        <date-picker
            :id="_id"
            :ref="_id"
            :class="['target-input', editable ? 'editable' : 'readonly']"
            v-model="vmodel[bind]"
            :config="_config"
            @dp-hide="calendarHidden"
            @dp-change="dateChanged"
            autocomplete="off"
            :style="customStyle"
        >
        </date-picker>
        <button type="button" class="input-group-addon calendar-button btn btn-info p-0 border-0" :class='[hideButton ? "d-none" : ""]'
            :id="_id + '_calendar_btn'" @click="showCalendar"
        >
            <i class="fas fa-calendar-check fa-lg"></i>
        </button>
    </div>
</template>

<style scoped>
.DatePickerWrapper label:first {
    width: auto;
}
.DatePickerWrapper .target-input {
    width: 140px;
    padding-left: 6px;
    padding-right: 6px;
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.DatePickerWrapper .calendar-button {
    width: 40px !important;
    border-left-width: 0px !important;
    display: flex;
    justify-content: center;
    align-items: center;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
}
</style>

<script>
import DatePicker from "vue-bootstrap-datetimepicker";

export default {
    name: "DatePickerWrapper",
    data() {
        return {
            date: "",
            isValid: false,
            errorMsg: null,
            default: {
                locale: "ja",
                format: "YYYY/MM/DD",
                dayViewHeaderFormat: "YYYY/MM",
                useCurrent: false,
            },
        }
    },
    props: {
        id: String,
        label: String,
        vmodel: Object,
        bind: String,
        editable: Boolean,
        hideButton: Boolean,
        customStyle: String,
        config: Object,
        format: String,
        dayViewHeaderFormat: String,
        onChangedFunc: Function,
        onCalendarHiddenFunc: Function,
    },
    components: {
        "DatePicker": DatePicker,
    },
    computed: {
        _id: function() {
            return this.id + "_" + this._uid;
        },
        _config: function() {
            var cfg = $.extend(true, {}, this.default, this.config);
            cfg.format = this.format || cfg.format;
            cfg.dayViewHeaderFormat = this.dayViewHeaderFormat || cfg.dayViewHeaderFormat;

            return cfg;
        },
        $input: function() {
            return $(this.$el).find(".target-input")[0];
        },
        inputListeners: function () {
            var comp = this

            var ev = {};
            ev.change = comp.onChange;
            if (comp.editable == false) {
                ev.click = comp.showList;
            }

            return Object.assign(
                {},
                this.$listeners,
                ev,
            )
        },
    },
    created: function () {
    },
    mounted: function () {
    },
    methods: {
        showCalendar: function(event) {
            $(this.$el).find("#" + this._id).datetimepicker("show");
        },
        calendarHidden: function(event) {
            if (this.onCalendarHiddenFunc) {
                this.onCalendarHiddenFunc(event);
            }
        },
        dateChanged: function(event) {
            if (this.onChangedFunc) {
                this.onChangedFunc(event);
            }
        },
    }
}
</script>

