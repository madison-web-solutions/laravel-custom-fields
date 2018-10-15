<template>
    <div class="lcf-switch-field">
        <select ref="switchSelect" :name="nameAttr + '[switch]'" @change="changeSwitch">
            <option v-for="option in switchOptions" :value="option" :selected="option == value.switch">{{ option }}</option>
        </select>
        <field v-if="switchField" :path="path.concat(value['switch'])" :field="switchField" :initialValue="value[value['switch']]" :errors="errors" />
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var value = {"switch": null};
        var switchVal = this.initialValue ? this.initialValue["switch"] : null;
        if (!switchVal || !this.field.options.switch_fields[switchVal]) {
            switchVal = _.first(_.keys(this.field.options.switch_fields));
        }
        value["switch"] = switchVal;
        value[switchVal] = this.initialValue ? this.initialValue[switchVal] : null;
        return {value: value};
    },
    computed: {
        switchOptions: function() {
            return _.keys(this.field.options.switch_fields);
        },
        switchField: function() {
            return this.field.options.switch_fields[this.value.switch];
        }
    },
    methods: {
        changeSwitch: function() {
            var selectedOption = this.$refs.switchSelect.options[this.$refs.switchSelect.selectedIndex];
            this.value.switch = selectedOption.value;
        }
    }
};
</script>
