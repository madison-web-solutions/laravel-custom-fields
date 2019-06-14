<template>
    <lcf-input-wrapper class="lcf-input lcf-input-text" v-bind="wrapperProps">
        <input type="text" :class="inputClasses" :name="name" :maxlength="max" :placeholder="placeholder" :value="value" @change="change" @input="input" />
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
            this.$emit('change', {key: this._key, value: e.target.value});
        },
        input: function(e) {
            this.$emit('input', {key: this._key, value: e.target.value});
        }
    }
};
</script>
