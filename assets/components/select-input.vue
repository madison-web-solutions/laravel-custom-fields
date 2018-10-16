<template>
    <select ref="input" :name="nameAttr" :class="{'lcf-input-has-error': hasError}" @change="change">
        <option ref="placeholder" :disabled="field.options.required" :selected="value == null">{{ field.options.required ? 'Select' : '' }}</option>
        <option v-for="optionLabel, optionValue in field.options.choices" :value="optionValue" :selected="value == optionValue">{{ optionLabel }}</option>
    </select>
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
            value: value
        };
    },
    methods: {
        change: function() {
            var selectedOption = this.$refs.input.options[this.$refs.input.selectedIndex];
            if (selectedOption === this.$refs.placeholder) {
                this.value = null;
            } else {
                this.value = selectedOption.value;
            }
        }
    }
};
</script>
