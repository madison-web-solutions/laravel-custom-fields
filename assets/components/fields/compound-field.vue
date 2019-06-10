<template>
    <div class="lcf-field lcf-compound-field">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <div class="lcf-compound-input" :class="label ? 'lcf-compound-with-label' : ''">
                <component v-for="subField, fieldName in subFields" :is="subField.fieldComponent" :key="childIds[fieldName]" :path="path.concat(fieldName)" :field="subField" />
            </div>
        </lcf-input-wrapper>
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
            return get(this.field, 'settings.sub_fields');
        }
    }
};
</script>
