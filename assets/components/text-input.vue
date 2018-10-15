<template>
    <input ref="input" :name="nameAttr" type="text" :class="{'lcf-input-has-error': hasError}" :value="value" :placeholder="field.placeholder" @change="change" />
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var value = _.defaultTo(this.initialValue, _.get(this.field, 'options.default', null));
        return {
            value: value ? String(value) : ''
        };
    },
    methods: {
        change: function() {
            this.value = this.$refs.input.value;
        }
    }
};
</script>
