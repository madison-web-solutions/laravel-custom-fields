<template>
    <div class="lcf-field lcf-switch-field">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <lcf-select-input key="switch" class="lcf-switch-select" :settings="switchSelectSettings" :value="switchKey" @change="changeSwitch" />
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
    },
    computed: {
        switchFields: function() {
            return get(this.field, 'settings.switch_fields');
        },
        switchKeys: function() {
            return keys(this.switchFields);
        },
        switchSelectSettings: function() {
            var settings = {
                name: this.inputName + '[switch]',
                choices: []
            };
            forEach(this.switchKeys, (key) => {
                settings.choices.push({value: key, label: this.switchLabel(key)});
            });
            return settings;
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
            this.$lcfStore.updateValue(this.pathStr + '.switch', e.value);
        }
    }
};
</script>
