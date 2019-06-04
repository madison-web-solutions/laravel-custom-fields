<template>
    <div class="lcf-input lcf-input-select">
        <select ref="input" :name="this.name" @change="change">
            <option ref="placeholder" :disabled="required" value="" :selected="isNull">{{ required ? 'Select' : '' }}</option>
            <option v-for="choice in choices" :value="choice.value" :selected="value == choice.value">{{ choice.label }}</option>
        </select>
    </div>
</template>

<script>
import { get } from 'lodash-es';
export default {
    props: ['settings', 'value', 'hasError'],
    computed: {
        name: function() {
            return get(this.settings, 'name');
        },
        _key: function() {
            return get(this.settings, 'key', this.$vnode.key);
        },
        required: function() {
            return get(this.settings, 'required', false);
        },
        choices: function() {
            return get(this.settings, 'choices');
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
