<template>
    <div class="lcf-input-wrapper">
        <label class="lcf-field-label" v-if="label">{{ label }}</label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <slot></slot>
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
export default {
    props: ['label', 'help', 'errors']
};
</script>
