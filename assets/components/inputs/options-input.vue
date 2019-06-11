<template>
    <div class="lcf-input lcf-input-options lcf-checkbox-group" :class="inputClasses">
        <label v-for="choice in choices" :class="{'lcf-has-error': hasChildError[choice.key]}">
            <input :name="name + '[' + choice.key +']'" type="checkbox" :data-key="choice.key" :checked="value[choice.key]" @change="change" />
            <span>{{ choice.label }}</span>
        </label>
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    props: ['hasChildError'],
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
