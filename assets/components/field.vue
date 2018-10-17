<template>
    <div class="lcf-field" :class="{'lcf-has-error': hasError}" :data-lcf-field-type="type">
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <component :is="field.inputComponent" :path="path" :field="field" :errors="errors" />
        <p v-if="hasError" class="lcf-field-errors"><span v-for="error in myErrors">{{ error }}</span></p>
    </div>
</template>

<script>
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin]
};
</script>
