<template>
    <div class="lcf-switch-field">
        <select ref="switchSelect" :name="nameAttr + '[switch]'" @change="changeSwitch">
            <option v-for="key in switchKeys" :value="key" :selected="key == switchKey">{{ key }}</option>
        </select>
        <field :path="path.concat(switchKey)" :field="switchField" :errors="errors" />
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    computed: {
        switchFields: function() {
            return this.field.options.switch_fields;
        },
        switchKeys: function() {
            return _.keys(this.switchFields);
        },
        switchKey: function() {
            var switchNodeId = this.$store.getters.getIdAtPath(this.path.concat('switch'));
            var switchNode = this.$store.state.nodes[switchNodeId];
            var value = switchNode ? switchNode.value : null;
            return this.getValidSwitchKey(value);
        },
        switchField: function() {
            return this.switchFields[this.switchKey];
        }
    },
    methods: {
        getValidSwitchKey: function(input) {
            if (input && this.switchFields[input]) {
                return input;
            } else if (this.switchFields[this.defaultValue]) {
                return this.defaultValue;
            } else {
                return _.first(this.switchKeys)
            }
        },
        changeSwitch: function() {
            var selectedOption = this.$refs.switchSelect.options[this.$refs.switchSelect.selectedIndex];
            this.$store.commit('updateValue', {path: this.path.concat('switch').join('.'), value: selectedOption.value});
        }
    }
};
</script>
