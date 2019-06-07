<template>
    <div class="lcf-radio-group">
        <label v-for="choice in choices">
            <input :name="name" type="radio" :value="choice.value" :checked="value == choice.value" @change="change" />
            <span>{{ choice.label }}</span>
        </label>
    </div>
</template>

<script>
import { find } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        choices: function() {
            return this.setting('choices');
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
