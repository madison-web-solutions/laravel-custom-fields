<template>
    <input ref="input" :name="nameAttr" type="number" :step="step" :min="min" :max="max" :class="{'lcf-input-has-error': hasError}" :value="value" :placeholder="field.placeholder" @change="change" />
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue * 1);
        }
    },
    computed: {
        integersOnly: function() {
            return (this.type == 'integer');
        },
        step: function() {
            return this.integersOnly ? 1 : this.field.options.step;
        },
        max: function() {
            return this.field.options.max;
        },
        min: function() {
            return this.field.options.min;
        }
    },
    methods: {
        change: function() {
            this.updateMyValue(null); // this is required so that the new value definitely gets redrawn on screen
            var value = this.$refs.input.value * 1;
            if (this.integersOnly) {
                value = Math.round(value);
            }
            if (this.min != null) {
                value = Math.max(value, this.min);
            }
            if (this.max != null) {
                value = Math.min(value, this.max);
            }
            this.updateMyValue(value);
        }
    }
};
</script>
