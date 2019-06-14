<template>
    <lcf-input-wrapper class="lcf-input lcf-input-text" v-bind="wrapperProps">
        <textarea :id="inputId" :name="name" :class="inputClasses" :rows="rows" :maxlength="max" @change="change">{{ value }}</textarea>
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
        },
        rows: {
            type: Number,
            required: false,
            default: 5
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: e.target.value});
        }
    }
};
</script>
