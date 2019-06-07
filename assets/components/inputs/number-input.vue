<template>
    <div>
        <input type="number" :name="name" novalidate :step="step" :min="min" :max="max" :value="value" @change="change" @keydown.enter.prevent="change" />
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        integersOnly: function() {
            return this.setting('integers_only');
        },
        decimals: function() {
            return this.integersOnly ? 0 : this.setting('decimals');
        },
        decimalsStep: function() {
            return this.decimals == null ? 'any' : Math.pow(10, -this.decimals);
        },
        step: function() {
            return this.setting('step', this.decimalsStep);
        },
        max: function() {
            return this.setting('max');
        },
        min: function() {
            return this.setting('min');
        }
    },
    methods: {
        change: function(e) {
            var value = e.target.value * 1;
            if (this.step != 'any') {
                value = Math.round(value / this.step) * this.step;
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
