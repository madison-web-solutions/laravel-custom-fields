<template>
    <div class="lcf-sub-field" :data-lcf-field-type="type">
        <label :class="{required: required}">{{ label }}</label>
        <lcf-field :path="[name]" :field="field" :initialValue="initialValue" :errors="errors" />
    </div>
</template>

<script>
export default {
    props: ['name', 'field', 'initialValue', 'errors'],
    computed: {
        type: function() {
            return _.get(this.field, 'type', null);
        },
        label: function() {
            return _.get(this.field, 'settings.label', _.startCase(this.name));
        },
        required: function() {
            return _.get(this.field, 'settings.required', null);
        }
    }
};
</script>
