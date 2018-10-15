<template>
    <div class="lcf-sub-field" :data-lcf-field-type="type">
        <label :class="{required: required}">{{ label }}</label>
        <field :path="path" :field="field" :initialValue="initialValue" :errors="errors" />
    </div>
</template>

<script>
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin]
};
</script>
