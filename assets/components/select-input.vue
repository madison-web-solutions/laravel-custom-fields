<template>
    <select ref="input" :name="nameAttr" :class="{'lcf-input-has-error': hasError}" @change="change">
        <option ref="placeholder" :disabled="field.options.required" :selected="value == null">{{ field.options.required ? 'Select' : '' }}</option>
        <option v-for="choice in choices" :value="choice.key" :selected="value == choice.key">{{ choice.label }}</option>
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
            this.updateMyValue(this.defaultValue);
        }
    },
    computed: {
        choices: function() {
            return this.field.options.choices;
        }
    },
    methods: {
        change: function() {
            var selectedOption = this.$refs.input.options[this.$refs.input.selectedIndex];
            if (selectedOption === this.$refs.placeholder) {
                this.updateMyValue(null);
            } else {
                this.updateMyValue(selectedOption.value);
            }
        }
    }
};
</script>
