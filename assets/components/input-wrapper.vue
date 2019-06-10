<template>
    <div class="lcf-input-wrapper">
        <label class="lcf-field-label" v-if="label">{{ label }}<sup v-if="required" class="lcf-required-asterisk">*</sup></label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <slot></slot>
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
export default {
    props: ['label', 'required', 'help', 'errors']
};
</script>
