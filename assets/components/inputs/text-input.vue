<template>
    <div class="lcf-input lcf-input-text">
        <input type="text" :name="name" :maxlength="max" :value="value" @change="change" />
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
        }
    }
};
</script>
