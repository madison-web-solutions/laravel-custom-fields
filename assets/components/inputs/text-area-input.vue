<template>
    <div class="lcf-input lcf-input-text-area">
        <textarea :name="name" :class="inputClasses" :rows="rows" :maxlength="max" @change="change">{{ value }}</textarea>
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        rows: function() {
            return this.setting('rows', 5);
        },
        max: function() {
            return this.setting('max');
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: e.target.value});
        }
    }
};
</script>
