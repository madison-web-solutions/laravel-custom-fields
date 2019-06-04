<template>
    <div class="lcf-field lcf-scalar-field">
        <lcf-input-wrapper :key="nodeId" :inputComponent="inputComponent" :settings="inputSettings" :value="value" :errors="errors" @change="updateMyValue" />
    </div>
</template>

<script>
import { assign } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    computed: {
        inputComponent: function() {
            return this.field.inputComponent;
        }
    },
    methods: {
        updateMyValue: function(e) {
            this.$lcfStore.updateValue(this.pathStr, e.value);
        }
    }
};
</script>
