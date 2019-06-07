<template>
    <div class="lcf-checkbox-group">
        <label v-for="choice in choices">
            <input :name="name + '[' + choice.key +']'" type="checkbox" :data-key="choice.key" :checked="value[choice.key]" @change="change" />
            <span>{{ choice.label }}</span>
        </label>
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        choices: function() {
            return this.setting('choices');
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
