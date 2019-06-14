<template>
    <lcf-input-wrapper class="lcf-input lcf-input-select" v-bind="wrapperProps">
        <div class="lcf-select-wrapper">
            <select :id="inputId" ref="input" :class="inputClasses" :name="name" :disabled="disabled" @change="change">
                <option ref="placeholder" :disabled="required" value="" :selected="isNull">{{ required ? 'Select' : '' }}</option>
                <option v-for="choice in choices" :value="choice.value" :selected="value == choice.value">{{ choice.label }}</option>
            </select>
            <button type="button" class="lcf-combo-button"><i class="fas fa-caret-down"></i></button>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        choices: {
            type: Array,
            required: true,
        }
    },
    computed: {
        isNull: function() {
            return this.value == null || this.value == '';
        }
    },
    methods: {
        change: function() {
            if (this.disabled) {return;}
            var selectedOption = this.$refs.input.options[this.$refs.input.selectedIndex];
            if (selectedOption === this.$refs.placeholder) {
                this.$emit('change', {key: this._key, value: null});
            } else {
                this.$emit('change', {key: this._key, value: selectedOption.value});
            }
        }
    }
};
</script>
