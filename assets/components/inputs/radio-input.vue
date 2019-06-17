<template>
    <lcf-input-wrapper class="lcf-input lcf-input-radio" v-bind="wrapperProps">
        <div class="lcf-radio-group" :class="myInputClasses">
            <label v-for="choice in choices">
                <input :name="name" type="radio" :value="choice.value" :checked="value == choice.value" :disabled="disabled" @change="change" />
                <span>{{ choice.label }}</span>
            </label>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import { assign, find } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        choices: {
            type: Array,
            required: true,
        },
        inputLayout: {
            type: String,
            required: false,
            default: 'horizontal'
        }
    },
    computed: {
        myInputClasses: function() {
            return assign({
                'lcf-layout-vertical': (this.inputLayout == 'vertical'),
                'lcf-layout-horizontal': (this.inputLayout == 'horizontal'),
            }, this.inputClasses);
        }
    },
    methods: {
        change: function() {
            if (this.disabled) {return;}
            var selectedRadio = find(this.$el.querySelectorAll('input[type=radio]'), radio => radio.checked);
            this.$emit('change', {key: this._key, value: selectedRadio ? selectedRadio.value : null});
        }
    }
};
</script>
