<template>
    <div class="lcf-field lcf-object-field">
        <lcf-input-wrapper :key="nodeId" :class="wrapperClasses" :inputComponent="inputComponent" :settings="inputSettings" :value="childValues" :errors="childErrors" @change="updateMyValue" />
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get, forEach, includes } from 'lodash-es';
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
        },
        hasError: function() {
            return this.errors && this.errors.length;
        },
        wrapperClasses: function() {
            return {
                'lcf-has-error': this.hasError
            };
        }
    },
    methods: {
        updateMyValue: function(e) {
            forEach(e.value, (sub_value, key) => {
                if (includes(this.keys, key)) {
                    this.$lcfStore.updateValue(this.pathStr + '.' + key, e.value[key]);
                } else {
                    console.log("Unexpected key "+key);
                }
            });
        }
    }
};
</script>
