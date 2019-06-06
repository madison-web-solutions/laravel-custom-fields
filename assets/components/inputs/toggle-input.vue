<template>
    <div class="lcf-input lcf-input-toggle">
        <input type="hidden" :name="name" :value="value ? 'true' : 'false'" />
        <button @click="toggle" type="button">
            <i v-if="value" class="fas fa-toggle-on"></i>
            <i v-if="! value" class="fas fa-toggle-off"></i>
            <span>{{ value ? trueLabel : falseLabel }}</span>
        </button>
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        trueLabel: function() {
            return this.setting('true_label', 'On');
        },
        falseLabel: function() {
            return this.setting('false_label', 'Off');
        }
    },
    methods: {
        toggle: function() {
            this.$emit('change', {key: this._key, value: ! this.value});
        }
    }
};
</script>
