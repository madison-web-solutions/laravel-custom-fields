<template>
    <div class="lcf-input lcf-currency-input">
        <input type="hidden" :name="name" :value="value" />
        <input ref="input" type="text" :value="displayValue" @change="change" @keydown.enter.prevent="change" />
    </div>
</template>

<script>
import { trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        symbol: function() {
            return this.setting('symbol');
        },
        symbolBefore: function() {
            return (this.setting('symbol_placement') == 'before') ? this.symbol : '';
        },
        symbolAfter: function() {
            return (this.setting('symbol_placement') == 'after') ? this.symbol : '';
        },
        max: function() {
            return this.setting('max');
        },
        min: function() {
            return this.setting('min');
        },
        displayValue: function() {
            if (Number.isInteger(this.value)) {
                return this.symbolBefore + (this.value / 100).toFixed(2) + this.symbolAfter;
            } else {
                return '';
            }
        }
    },
    methods: {
        change: function(e) {
            var rawValue = this.$refs.input.value;
            var floatVal = parseFloat(rawValue.replace(/[^0-9\.]/g, ''));
            if (! isFinite(floatVal)) {
                this.$emit('change', {key: this._key, value: null});
            }
            var penceVal = Math.round(floatVal * 100);
            if (this.min != null) {
                penceVal = Math.max(penceVal, this.min);
            }
            if (this.max != null) {
                penceVal = Math.min(penceVal, this.max);
            }
            // clear the value first so that the new value definitely gets redrawn on screen
            this.$emit('change', {key: this._key, value: null});
            this.$nextTick(() => {
                this.$emit('change', {key: this._key, value: penceVal});
            });
        }
    }
};
</script>
