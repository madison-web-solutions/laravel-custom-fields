<template>
    <lcf-input-wrapper class="lcf-input lcf-input-options" v-bind="wrapperProps" :errors="combinedErrors">
        <div class="lcf-checkbox-group" :class="inputClasses">
            <label v-for="choice in choices" :class="{'lcf-has-error': hasChildError[choice.key]}">
                <input :name="name + '[' + choice.key +']'" type="checkbox" :data-key="choice.key" :checked="value[choice.key]" :disabled="disabled" @change="change" />
                <span>{{ choice.label }}</span>
            </label>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        childErrors: {
            type: Object,
            required: true
        },
        choices: {
            type: Array,
            required: true,
        }
    },
    computed: {
        combinedErrors: function() {
            var combinedErrors = [];
            forEach(this.errors, msg => {
                combinedErrors.push(msg);
            });
            forEach(this.childErrors, (childErrorMsgs, key) => {
                forEach(childErrorMsgs, msg => {
                    combinedErrors.push(key + ': ' + msg);
                });
            });
            return combinedErrors;
        },
        hasChildError: function() {
            var hasChildError = {};
            forEach(this.keys, key => {
                hasChildError[key] = this.childErrors[key] && this.childErrors[key].length;
            });
            return hasChildError;
        }
    },
    methods: {
        change: function(e) {
            if (this.disabled) {return;}
            var payload = {key: this._key, value: {}};
            payload[e.target.getAttribute('data-key')] = e.target.checked;
            this.$emit('change', payload);
        }
    }
};
</script>
