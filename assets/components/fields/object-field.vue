<template>
    <div :class="myCssClasses" class="lcf-object-field" v-if="shouldShow">
        <component :key="nodeId" :is="inputComponent" v-bind="inputSettings" :value="childValues" :errors="errors" :childErrors="childErrors" @change="updateMyValue" />
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
