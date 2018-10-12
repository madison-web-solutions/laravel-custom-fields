<template>
    <div class="lcf-switch-field">
        <select ref="switchSelect" :name="name + '[switch]'" @change="changeSwitch">
            <option v-for="option in switchOptions" :value="option" :selected="option == value.switch">{{ option }}</option>
        </select>
        <lcf-field v-if="switchField" :path="path.concat(value['switch'])" :field="switchField" :initialValue="value[value['switch']]" :errors="errors" />
    </div>
</template>

<script>
import _ from 'lodash';
var counter = 0;
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
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
        name: function() {
            var name = this.path[0];
            for (var i = 1; i < this.path.length; i++) {
                name += '[' + this.path[i] + ']';
            }
            return name;
        },
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
