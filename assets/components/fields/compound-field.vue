<template>
    <div :class="myCssClasses" class="lcf-compound-field" :data-field-name="fieldName" v-if="shouldShow">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <div class="lcf-compound-input" :class="{'lcf-compound-with-label': !!label, 'lcf-has-error': hasError}">
                <component v-for="subField, fieldName in subFields" :is="subField.fieldComponent" :key="childIds[fieldName]" :path="path.concat(fieldName)" :field="subField" />
            </div>
        </lcf-input-wrapper>
    </div>
</template>

<script>
import { get, keys, mapValues } from 'lodash-es';
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
