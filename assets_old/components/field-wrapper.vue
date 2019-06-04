<template>
    <div class="lcf-sub-field" :data-lcf-field-type="type">
        <label :class="{required: required}">{{ label }}</label>
        <field :path="path" :field="field" :errors="errors" />
    </div>
</template>

<script>
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['groupName', 'fieldName', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        this.$store.commit('setInitialValue', {groupName: this.groupName, fieldName: this.fieldName, value: this.initialValue});
    },
    computed: {
        path: function() {
            return [this.groupName, this.fieldName];
        }
    }
};
</script>
