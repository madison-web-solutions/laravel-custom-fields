<template>
    <textarea ref="input" :name="nameAttr" :rows="lines" :maxlength="maxlength" :class="{'lcf-input-has-error': hasError}" :placeholder="field.placeholder" @change="change">{{ value }}</textarea>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(String(this.defaultValue));
        }
    },
    computed: {
        lines: function() {
            return _.get(this.field, 'options.lines', 5);
        },
        maxlength: function() {
            return _.get(this.field, 'options.max', false);
        },
    },
    methods: {
        change: function() {
            this.updateMyValue(this.$refs.input.value);
        }
    }
};
</script>
