<template>
    <div class="lcf-input lcf-input-text">
        <input type="text" :name="name" :value="value" @change="change" />
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
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: e.target.value});
        }
    }
};
</script>
