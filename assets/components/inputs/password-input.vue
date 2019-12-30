<template>
    <lcf-input-wrapper class="lcf-input lcf-input-password" v-bind="wrapperProps">
        <input :id="inputId" type="password" :class="inputClasses" :name="name" :placeholder="placeholder" :value="value" :disabled="disabled" @change="change" @input="input" />
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    methods: {
        change: function(e) {
            if (this.disabled) {return;}
            this.$emit('change', {key: this._key, value: e.target.value == '' ? null : e.target.value});
        },
        input: function(e) {
            if (this.disabled) {return;}
            this.$emit('input', {key: this._key, value: e.target.value});
        }
    }
};
</script>
