<template>
    <div class="lcf-input lcf-input-number">
        <input type="number" :name="name" :step="step" :min="min" :max="max" :value="value" @change="change" @keydown.enter.prevent="change" />
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        integersOnly: function() {
            return this.setting('integers_only');
        },
        step: function() {
            return this.integersOnly ? 1 : this.setting('step', 1);
        },
        max: function() {
            return this.setting('max');
        },
        min: function() {
            return this.setting('min');
        }
    },
    methods: {
        change: function(e) {
            var value = e.target.value * 1;
            if (this.integersOnly) {
                value = Math.round(value);
            }
            if (this.min != null) {
                value = Math.max(value, this.min);
            }
            if (this.max != null) {
                value = Math.min(value, this.max);
            }
            // clear the value first so that the new value definitely gets redrawn on screen
            this.$emit('change', {key: this._key, value: null});
            this.$nextTick(() => {
                this.$emit('change', {key: this._key, value: value});
            });
        }
    }
};
</script>
