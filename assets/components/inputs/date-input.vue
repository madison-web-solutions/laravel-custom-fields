<template>
    <lcf-input-wrapper class="lcf-input lcf-input-date" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value" />
        <input :id="inputId" :class="inputClasses" ref="input" :type="inputType" :placeholder="myPlaceholder" :value="displayValue" :disabled="disabled" @change="change" />
    </lcf-input-wrapper>
</template>

<script>
import Util from '../../util.js';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    data: function() {
        return {
            support: this.browserSupportsDateInput()
        }
    },
    computed: {
        inputType: function() {
            return this.support ? 'date' : 'text';
        },
        myPlaceholder: function() {
            return this.support ? null : 'dd/mm/yyyy';
        },
        displayValue: function() {
            if (this.support) {
                return this.value;
            } else {
                if (this.tempClear) {
                    return '';
                }
                // If it's a valid date, then convert to d/m/y for display
                var dmyValue = Util.dateConvert(this.value, 'ymd', 'dmy');
                return dmyValue ? dmyValue : this.value;
            }
        },
    },
    methods: {
        browserSupportsDateInput: function() {
            // Check whether the browser supports date inputs
            // If it does, you won't be able to set an illegal value on an input with type="date"
            var input = document.createElement('input');
            var notADateValue = 'not-a-date';
            input.setAttribute('type','date');
            input.setAttribute('value', notADateValue);
            return (input.value !== notADateValue);
        },
        change: function() {
            if (this.disabled) {return;}
            if (this.support) {
                var newValue = this.$refs.input.value;
                this.$emit('change', {key: this._key, value: newValue});
            } else {
                // If it's a valid date, then convert to y-m-d to send to the server
                var inputValue = this.$refs.input.value;
                var ymdValue = Util.dateConvert(inputValue, 'dmy', 'ymd');
                var newValue = ymdValue ? ymdValue : inputValue;
                // clear the value first so that the new value definitely gets redrawn on screen
                this.tempClear = true;
                this.$nextTick(() => {
                    this.$emit('change', {key: this._key, value: newValue});
                    this.tempClear = false;
                });
            }
        }
    }
};
</script>
