<template>
    <lcf-input-wrapper class="lcf-input lcf-input-options" v-bind="wrapperProps" :errors="combinedErrors">
        <div class="lcf-checkbox-group" :class="myInputClasses">
            <label v-for="choice in choices" :class="{'lcf-has-error': hasChildError[choice.key]}">
                <input :name="name + '[' + choice.key +']'" type="checkbox" :data-key="choice.key" :checked="value[choice.key]" :disabled="disabled" @change="change" />
                <span>{{ choice.label }}</span>
            </label>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import { assign, forEach } from 'lodash-es';
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
        },
        inputLayout: {
            type: String,
            required: false,
            default: 'horizontal'
        },
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
        },
        myInputClasses: function() {
            return assign({
                'lcf-layout-vertical': (this.inputLayout == 'vertical'),
                'lcf-layout-horizontal': (this.inputLayout == 'horizontal'),
            }, this.inputClasses);
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
