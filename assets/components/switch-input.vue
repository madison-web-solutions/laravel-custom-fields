<template>
    <div class="lcf-switch-field">
        <select ref="switchSelect" :name="nameAttr + '[switch]'" @change="changeSwitch">
            <option v-for="option in switchOptions" :value="option" :selected="option == switchVal">{{ option }}</option>
        </select>
        <keep-alive>
            <field v-for="child in children" :key="child.key" v-if="switchVal == child.key" :path="path.concat(child.key)" :field="child.field" :initialValue="child.initialValue" :errors="errors" />
        </keep-alive>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var switchVal = _.defaultTo(_.get(this.initialValue, 'switch', null), _.get(this.field, 'options.default', null));
        if (!switchVal || !this.field.options.switch_fields[switchVal]) {
            switchVal = _.first(_.keys(this.field.options.switch_fields));
        }
        var children = [];
        for (var key in this.field.options.switch_fields) {
            children.push({
                key: key,
                field: this.field.options.switch_fields[key],
                initialValue: _.get(this.initialValue, key, null)
            });
        }
        return {
            switchVal: switchVal,
            children: children
        };
    },
    computed: {
        switchOptions: function() {
            return _.keys(this.field.options.switch_fields);
        }
    },
    methods: {
        changeSwitch: function() {
            var selectedOption = this.$refs.switchSelect.options[this.$refs.switchSelect.selectedIndex];
            this.switchVal = selectedOption.value;
        }
    }
};
</script>
