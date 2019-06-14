<template>
    <div class="lcf-input-wrapper">
        <label class="lcf-field-label" v-if="label" :for="labelFor">
            {{ label }}
            <sup v-if="required" class="lcf-required-asterisk">*</sup>
        </label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <slot></slot>
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
            required: false
        },
        labelFor: {
            type: String,
            required: false
        },
        required: {
            type: Boolean,
            required: false,
            default: false
        },
        help: {
            type: String,
            required: false
        },
        errors: {
            type: Array,
            required: false
        }
    }
};
</script>
