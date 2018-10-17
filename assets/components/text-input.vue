<template>
    <input ref="input" :name="nameAttr" type="text" :class="{'lcf-input-has-error': hasError}" :value="value" :placeholder="field.placeholder" @change="change" />
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.$store.commit('updateValue', {path: this.pathStr, value: String(this.defaultValue)});
        }
    },
    methods: {
        change: function() {
            this.$store.commit('updateValue', {path: this.pathStr, value: this.$refs.input.value});
        }
    }
};
</script>
