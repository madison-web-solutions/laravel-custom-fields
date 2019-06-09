<template>
    <div class="lcf-input lcf-input-select">
        <select ref="input" :class="inputClasses" :name="name" @change="change">
            <option ref="placeholder" :disabled="required" value="" :selected="isNull">{{ required ? 'Select' : '' }}</option>
            <option v-for="choice in choices" :value="choice.value" :selected="value == choice.value">{{ choice.label }}</option>
        </select>
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        choices: function() {
            return this.setting('choices');
        },
        isNull: function() {
            return this.value == null || this.value == '';
        }
    },
    methods: {
        change: function() {
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
