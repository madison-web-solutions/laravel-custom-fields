<template>
    <input ref="input" :name="nameAttr" type="text" :maxlength="maxlength" :class="{'lcf-input-has-error': hasError}" :value="value" :placeholder="placeholder" @change="change" />
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
    methods: {
        change: function() {
            this.updateMyValue(this.$refs.input.value);
        },
        maxlength: function() {
            return _.get(this.field, 'options.max', false);
        }
    }
};
</script>
