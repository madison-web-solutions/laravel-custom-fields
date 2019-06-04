<template>
    <component :is="fieldComponent" :path="path" :field="field" />
</template>

<script>
export default {
    props: ['groupName', 'fieldName', 'field', 'initialValue'],
    created: function() {
        console.log("created top level field");
        this.$lcfStore.setInitialValue(this.groupName, this.fieldName, this.initialValue);
    },
    mounted: function() {
        console.log("mounted top level field");
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
