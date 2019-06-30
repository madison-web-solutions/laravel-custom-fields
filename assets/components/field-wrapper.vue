<template>
    <component :is="fieldComponent" :path="path" :field="field" :cssClasses="cssClasses" />
</template>

<script>
export default {
    props: ['groupName', 'fieldName', 'field', 'initialValue', 'cssClasses'],
    created: function() {
        this.$lcfStore.setInitialValue(this.groupName, this.fieldName, this.initialValue);
    },
    computed: {
        fieldComponent: function() {
            return this.field.fieldComponent || 'lcf-scalar-field';
        },
        path: function() {
            return [this.groupName, this.fieldName];
        }
    }
};
</script>
