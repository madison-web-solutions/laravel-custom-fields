<template>
    <lcf-input-wrapper class="lcf-input lcf-input-time" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value" />
        <input :id="inputId" :class="inputClasses" ref="input" type="text" :placeholder="myPlaceholder" :value="displayValue" :disabled="disabled" @change="change" />
    </lcf-input-wrapper>
</template>

<script>
import Util from '../../util.js';
import { get, trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        withSeconds: {
            type: Boolean,
            required: false,
            default: false
        },
        step: {
            type: String,
            required: false,
        }
    },
    computed: {
        myPlaceholder: function() {
            return this.withSeconds ? 'hh:mm:ss' : 'hh:mm';
        },
        stepSeconds: function() {
            return Util.timeParse(this.step);
        },
        displayValue: function() {
            if (this.tempClear) {
                return '';
            }
            var parsedValue = this.parse(this.value, false);
            return parsedValue ? parsedValue : this.value;
        },
    },
    methods: {
        parse: function(val, withRounding) {
            var secsSinceMidnight = Util.timeParse(val);
            if (secsSinceMidnight == null) {
                return null;
            }
            if (withRounding && this.stepSeconds != null) {
                secsSinceMidnight = Math.round(secsSinceMidnight / this.stepSeconds) * this.stepSeconds;
            }
            var time = Util.timeSplit(secsSinceMidnight);
            return Util.timeFormat(time[0], time[1], this.withSeconds ? time[2] : null);
        },
        change: function(e) {
            if (this.disabled) {return;}
            var inputValue = this.$refs.input.value;
            // clear the value first so that the new value definitely gets redrawn on screen
            this.tempClear = true;
            if (inputValue != '') {
                var newValue = this.parse(inputValue, true);
                this.$nextTick(() => {
                    this.$emit('change', {key: this._key, value: newValue ? newValue : inputValue});
                    this.tempClear = false;
                });
            }
        }
    }
};
</script>
