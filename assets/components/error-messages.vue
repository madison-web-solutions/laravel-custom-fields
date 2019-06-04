<template>
    <p v-if="hasError" class="lcf-error">
        <span v-for="msg in errors" class="lcf-error-msg">{{ msg }}</span>
    </p>
</template>

<script>
export default {
    props: ['errors'],
    computed: {
        hasError: function() {
            return this.errors && this.errors.length;
        }
    }
};
</script>
