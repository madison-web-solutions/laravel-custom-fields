<template>
    <lcf-input-wrapper class="lcf-input lcf-input-text" v-bind="wrapperProps">
        <input :id="inputId" type="text" :class="inputClasses" :name="name" :maxlength="max" :placeholder="placeholder" :value="value" :disabled="disabled" @change="change" @input="input" />
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        max: {
            type: Number,
            required: false,
        }
    },
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
