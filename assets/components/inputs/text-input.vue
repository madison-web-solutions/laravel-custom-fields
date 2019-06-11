<template>
    <div class="lcf-input lcf-input-text">
        <input type="text" :class="inputClasses" :name="name" :maxlength="max" :placeholder="placeholder" :value="value" @change="change" @input="input" />
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        max: function() {
            return this.setting('max');
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: e.target.value});
        },
        input: function(e) {
            this.$emit('input', {key: this._key, value: e.target.value});
        }
    }
};
</script>
