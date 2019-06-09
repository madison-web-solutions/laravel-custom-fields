<template>
    <div class="lcf-field lcf-object-field">
        <lcf-input-wrapper :label="label" :help="help" :errors="errors">
            <component :key="nodeId" :is="inputComponent" :settings="inputSettings" :value="childValues" :hasError="hasError" @change="updateMyValue" />
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
