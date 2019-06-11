<template>
    <div class="lcf-field lcf-object-field" v-if="shouldShow">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="combinedErrors">
            <component :key="nodeId" :is="inputComponent" :settings="inputSettings" :value="childValues" :hasError="hasError" :hasChildError="hasChildError" @change="updateMyValue" />
        </lcf-input-wrapper>
    </div>
</template>

<script>
import { get, forEach, includes } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.$lcfStore.objectInitKeys(this.pathStr, this.keys);
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue({value: this.defaultValue});
        }
    },
    computed: {
        inputComponent: function() {
            return this.field.inputComponent;
        },
        keys: function() {
            return get(this.field, 'settings.keys');
        },
        combinedErrors: function() {
            var combinedErrors = [];
            forEach(this.errors, msg => {
                combinedErrors.push(msg);
            });
            forEach(this.childErrors, (childErrorMsgs, key) => {
                forEach(childErrorMsgs, msg => {
                    combinedErrors.push(key + ': ' + msg);
                });
            });
            return combinedErrors;
        },
        hasChildError: function() {
            var hasChildError = {};
            forEach(this.keys, key => {
                hasChildError[key] = this.childErrors[key] && this.childErrors[key].length;
            });
            return hasChildError;
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
