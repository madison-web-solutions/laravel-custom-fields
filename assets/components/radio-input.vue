<template>
    <div ref="input" class="lcf-radio-group" :class="{'lcf-input-has-error': hasError}" @change="change">
        <label v-for="choice in choices">
            <input :name="nameAttr" type="radio" :value="choice.key" :checked="value == choice.key" />
            <span>{{ choice.label }}</span>
        </label>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue);
        }
    },
    computed: {
        choices: function() {
            return this.field.options.choices;
        }
    },
    methods: {
        change: function() {
            var radios = this.$refs.input.querySelectorAll('input[type=radio]');
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    this.updateMyValue(radios[i].value);
                    return;
                }
            }
            this.updateMyValue(null);
        }
    }
};
</script>
