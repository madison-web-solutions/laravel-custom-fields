<template>
    <lcf-input-wrapper class="lcf-input lcf-input-currency" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value" />
        <input :id="inputId" :class="inputClasses" ref="input" type="text" :value="displayValue" @change="change" @keydown.enter.prevent="change" />
    </lcf-input-wrapper>
</template>

<script>
import { trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    props: {
        symbol: {
            type: String,
            required: false,
        },
        symbolPlacement: {
            type: String,
            required: false
        },
        max: {
            type: Number,
            required: false,
        },
        min: {
            type: Number,
            required: false,
        }
    },
    mixins: [inputMixin],
    computed: {
        symbolBefore: function() {
            return (this.symbolPlacement == 'before') ? this.symbol : '';
        },
        symbolAfter: function() {
            return (this.symbolPlacement == 'after' ? this.symbol : '');
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
