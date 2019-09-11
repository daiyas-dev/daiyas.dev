<template>
    <div
        class="form-group d-inline-flex mb-0"
        :class='"DatePickerWrapper " + id'
        :style='width ? ("width: " + width + "px") : ""'
    >
        <span v-if="label" class="text-nowrap d-flex align-items-center mr-1" :for="id + '_calendar_btn'">{{label}}</span>
        <date-picker
            :id="id"
            :ref="id"
            :class="['target-input', editable ? 'editable' : 'readonly']"
            v-model="vmodel[bind]"
            :config="_config"
            @dp-hide="calendarHidden"
            @dp-change="dateChanged"
            autocomplete="off"
        >
        </date-picker>
        <button type="button" class="input-group-addon calendar-button btn btn-info p-0 border-0" :class='[hideButton ? "d-none" : ""]'
            :id="id + '_calendar_btn'" @click="showCalendar"
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
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
}
.DatePickerWrapper .calendar-button {
    width: 60px !important;
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
        width: Number,
        config: Object,
        onChangeFunc: Function,
        onCalendarHiddenFunc: Function,
    },
    components: {
        "DatePicker": DatePicker,
    },
    computed: {
        _config: function() {
            return $.extend(true, {}, this.default, this.config);
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
            $(this.$el).find("#" + this.id).datetimepicker("show");
        },
        calendarHidden: function(evnet) {
            $(this.$el).find("button").focus();
            if (this.onCalendarHiddenFunc) {
                this.onCalendarHiddenFunc(event);
            }
        },
        dateChanged: function(evnet) {
            if (this.onChangeFunc) {
                this.onChangeFunc(event);
            }
        },
    }
}
</script>

