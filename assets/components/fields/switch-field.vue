<template>
    <div :class="myCssClasses" class="lcf-switch-field" v-if="shouldShow">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <lcf-select-input key="switch" class="lcf-switch-select" :name="inputName + '[switch]'" :required="true" :choices="switchChoices" :value="switchKey" @change="changeSwitch" />
            <component :is="switchField.fieldComponent" :key="switchId" :path="path.concat(switchKey)" :field="switchField" />
        </lcf-input-wrapper>
    </div>
</template>

<script>
import { get, keys, forEach } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.$lcfStore.objectInitKeys(this.pathStr, ['switch'].concat(this.switchKeys));
        if (this.switchKey == null && this.defaultValue != null) {
            this.changeSwitch({value: this.defaultValue});
        }
    },
    computed: {
        switchFields: function() {
            return get(this.field, 'settings.switchFields');
        },
        switchKeys: function() {
            return keys(this.switchFields);
        },
        switchChoices: function() {
            var choices = [];
            forEach(this.switchKeys, (key) => {
                choices.push({value: key, label: this.switchLabel(key)});
            });
            return choices;
        },
        switchKey: function() {
            return this.childValues.switch || this.getValidSwitchKey();
        },
        switchField: function() {
            return this.switchFields[this.switchKey];
        },
        switchId: function() {
            return this.childIds[this.switchKey];
        },
        switchValue: function() {
            return this.childValues[this.switchKey];
        }
    },
    methods: {
        switchLabel: function(key) {
            var label = get(this.field, 'settings.switchLabels.' + key);
            if (! label) {
                // If no label is set, guess one by proper-casing the key
                label = key.replace(/^\w/, t => t.toUpperCase()).replace(/_\w/g, t => ' ' + t.charAt(1).toUpperCase());
            }
            return label;
        },
        getValidSwitchKey: function(input) {
            if (input && this.switchFields[input]) {
                return input;
            //} else if (this.switchFields[this.defaultValue]) {
            //    return this.defaultValue;
            } else {
                return this.switchKeys[0];
            }
        },
        changeSwitch: function(e) {
            this.$lcfStore.updateValue(this.pathStr + '.switch', e.value);
        }
    }
};
</script>
