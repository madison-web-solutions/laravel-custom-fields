<template>
    <div class="lcf-input lcf-input-text-area">
        <textarea :name="name" :rows="rows" :maxlength="max" @change="change">{{ value }}</textarea>
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
        rows: function() {
            return get(this.settings, 'rows', 5);
        },
        max: function() {
            return get(this.settings, 'max');
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: e.target.value});
        }
    }
};
</script>
