<template>
    <div class="lcf-field lcf-scalar-field" v-if="shouldShow">
        <component :key="nodeId" :is="inputComponent" v-bind="inputSettings" :value="value" :errors="errors" @change="updateMyValue" />
    </div>
</template>

<script>
import { assign } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue({value: this.defaultValue});
        }
    },
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
