<template>
    <lcf-input-wrapper class="lcf-input lcf-input-toggle" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value ? 'true' : 'false'" />
        <div aria-role="checkbox" :aria-checked="value ? 'true' : 'false'" class="lcf-toggle-switch" :class="inputClasses" @click="toggle">
            <i v-if="value" class="fas fa-toggle-on"></i>
            <i v-if="! value" class="fas fa-toggle-off"></i>
            <span>{{ value ? trueLabel : falseLabel }}</span>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        trueLabel: {
            type: String,
            required: false,
            default: 'On'
        },
        falseLabel: {
            type: String,
            required: false,
            default: 'Off'
        }
    },
    methods: {
        toggle: function() {
            this.$emit('change', {key: this._key, value: ! this.value});
        }
    }
};
</script>
