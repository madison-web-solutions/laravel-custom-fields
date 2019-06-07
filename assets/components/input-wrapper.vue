<template>
    <div class="lcf-input-wrapper" :data-lcf-input-wrapper="inputComponent">
        <label class="lcf-field-label" v-if="label">{{ label }}</label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <component class="lcf-input" :data-lcf-input="inputComponent" :key="_key" :is="inputComponent" :settings="settings" :value="value" :hasError="hasError" @change="change" />
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get } from 'lodash-es';
export default {
    props: ['inputComponent', 'settings', 'value', 'errors'],
    computed: {
        _key: function() {
            return get(this.settings, 'key', this.$vnode.key);
        },
        label: function() {
            return get(this.settings, 'label');
        },
        help: function() {
            return get(this.settings, 'help');
        },
        hasError: function() {
            return this.errors && this.errors.length;
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', e);
        }
    }
};
</script>
