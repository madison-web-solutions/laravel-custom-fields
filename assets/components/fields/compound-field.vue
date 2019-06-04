<template>
    <div class="lcf-field lcf-compound-field">
        <label class="lcf-field-label" v-if="label">{{ label }}</label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <component v-for="subField, fieldName in subFields" :is="subField.fieldComponent" :key="childIds[fieldName]" :path="path.concat(fieldName)" :field="subField" />
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get, keys } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.$lcfStore.objectInitKeys(this.pathStr, keys(this.subFields));
    },
    computed: {
        subFields: function() {
            return get(this.field, 'settings.subFields');
        }
    }
};
</script>
