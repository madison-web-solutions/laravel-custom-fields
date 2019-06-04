<template>
    <div class="lcf-field lcf-switch-field">
        <label class="lcf-field-label" v-if="label">{{ label }}</label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <select ref="switchSelect" class="lcf-switch-select" :name="inputName + '[switch]'" @change="changeSwitch">
            <option v-for="key in switchKeys" :value="key">{{ switchLabel(key) }}</option>
        </select>
        <component :is="switchField.fieldComponent" :key="switchId" :path="path.concat(switchKey)" :field="switchField" />
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get, keys } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.$lcfStore.objectInitKeys(this.pathStr, ['switch'].concat(this.switchKeys));
    },
    computed: {
        switchFields: function() {
            return get(this.field, 'settings.switch_fields');
        },
        switchKeys: function() {
            return keys(this.switchFields);
        },
        switchKey: function() {
            return this.childValues.switch;
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
            return get(this.field, 'settings.switch_labels.' + key, key);
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
            var selectedOption = this.$refs.switchSelect.options[this.$refs.switchSelect.selectedIndex];
            this.$lcfStore.updateValue(this.pathStr + '.switch', selectedOption.value);
        }
    }
};
</script>
