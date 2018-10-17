<template>
    <select ref="input" :name="nameAttr" :class="{'lcf-input-has-error': hasError}" @change="change">
        <option ref="placeholder" :disabled="field.options.required" :selected="value == null">{{ field.options.required ? 'Select' : '' }}</option>
        <option v-for="optionLabel, optionValue in choices" :value="optionValue" :selected="value == optionValue">{{ optionLabel }}</option>
    </select>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.$store.commit('updateValue', {path: this.pathStr, value: defaultValue});
        }
    },
    computed: {
        choices: function() {
            return this.field.options.choices;
        }
    },
    methods: {
        change: function() {
            var payload = {path: this.pathStr};
            var selectedOption = this.$refs.input.options[this.$refs.input.selectedIndex];
            if (selectedOption === this.$refs.placeholder) {
                payload.value = null;
            } else {
                payload.value = selectedOption.value;
            }
            this.$store.commit('updateValue', payload);
        }
    }
};
</script>
