<template>
    <div class="lcf-input lcf-input-radio">
        <label v-for="choice in choices">
            <input :name="name" type="radio" :value="choice.value" :checked="value == choice.value" />
            <span>{{ choice.label }}</span>
        </label>
    </div>
</template>

<script>
import { find, get } from 'lodash-es';
export default {
    props: ['settings', 'value', 'hasError'],
    computed: {
        name: function() {
            return get(this.settings, 'name');
        },
        _key: function() {
            return get(this.settings, 'key', this.$vnode.key);
        },
        choices: function() {
            return get(this.settings, 'choices');
        }
    },
    methods: {
        change: function() {
            var selectedRadio = find(this.$el.querySelectorAll('input[type=radio]'), radio => radio.checked);
            this.$emit('change', {key: this._key, value: selectedRadio ? selectedRadio.value : null});
        }
    }
};
</script>
