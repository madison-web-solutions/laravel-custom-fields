<template>
    <div class="lcf-field lcf-object-field">
        <lcf-input-wrapper :key="nodeId" :inputComponent="inputComponent" :settings="inputSettings" :value="childValues" :error="childErrors" @change="updateMyValue" />
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get, forEach } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.$lcfStore.objectInitKeys(this.pathStr, this.keys);
    },
    computed: {
        inputComponent: function() {
            return this.field.inputComponent;
        },
        keys: function() {
            return get(this.field, 'settings.keys');
        }
    },
    methods: {
        updateMyValue: function(e) {
            forEach(this.keys, key => this.$lcfStore.updateValue(this.pathStr + '.' + key, e.value[key]));
        }
    }
};
</script>
