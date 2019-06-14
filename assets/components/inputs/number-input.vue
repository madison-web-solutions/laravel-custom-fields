<template>
    <lcf-input-wrapper class="lcf-input lcf-input-number" v-bind="wrapperProps">
        <input :id="inputId" type="number" :class="inputClasses" :name="name" novalidate :step="myStep" :min="min" :max="max" :value="value" :disabled="disabled" @change="change" @keydown.enter.prevent="change" />
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        max: {
            type: Number,
            required: false,
        },
        min: {
            type: Number,
            required: false,
        },
        integersOnly: {
            type: Boolean,
            required: false,
            default: false
        },
        decimals: {
            type: Number,
            required: false,
        },
        step: {
            type: Number,
            required: false,
        }
    },
    computed: {
        myStep: function() {
            if (this.step == null) {
                if (this.integersOnly) {
                    return 1;
                } else {
                    if (this.decimals == null) {
                        return 'any';
                    } else {
                        return Math.pow(10, -this.myDecimals);
                    }
                }
            } else {
                return this.step;
            }
        }
    },
    methods: {
        change: function(e) {
            if (this.disabled) {return;}
            var value = e.target.value * 1;
            if (this.myStep != 'any') {
                value = Math.round(value / this.myStep) * this.myStep;
            }
            if (this.min != null) {
                value = Math.max(value, this.min);
            }
            if (this.max != null) {
                value = Math.min(value, this.max);
            }
            // This last step is to try and avoid float rounding errors
            // For example, if the value is 1.131 and the step size is 0.01, you might think that we'd be able to round it to 1.13 as follows:
            // Math.round((1.131) / 0.01) * 0.01
            // But that actually yields 1.1300000000000001 because of rounding errors in the float representation of 0.01
            value = value.toFixed(12) * 1;
            // clear the value first so that the new value definitely gets redrawn on screen
            this.$emit('change', {key: this._key, value: null});
            this.$nextTick(() => {
                this.$emit('change', {key: this._key, value: value});
            });
        }
    }
};
</script>
