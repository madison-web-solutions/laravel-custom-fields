<template>
    <div class="lcf-input lcf-input-options">
        <label v-for="choice in choices">
            <input :name="name + '[' + choice.key +']'" type="checkbox" :data-key="choice.key" :checked="value[choice.key]" @change="change" />
            <span>{{ choice.label }}</span>
        </label>
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
        choices: function() {
            return get(this.settings, 'choices');
        }
    },
    methods: {
        change: function(e) {
            var payload = {key: this._key, value: {}};
            payload[e.target.getAttribute('data-key')] = e.target.checked;
            this.$emit('change', payload);
        }
    }
};
</script>
