<template>
    <div ref="input" class="lcf-radio-group" :class="{'lcf-input-has-error': hasError}" @change="change">
        <label v-for="optionLabel, optionValue in choices">
            <input :name="nameAttr" type="radio" :value="optionValue" :checked="value == optionValue" />
            {{ optionLabel }}
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
            this.$store.commit('updateValue', {path: this.pathStr, value: defaultValue});
        }
    },
    computed: {
        choices: function() {
            return this.field.options.choices;
        }
    },
    methods: {
        change: function() {
            var payload = {path: this.pathStr, value: null};
            var radios = this.$refs.input.querySelectorAll('input[type=radio]');
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    payload.value = radios[i].value;
                }
            }
            this.$store.commit('updateValue', payload);
        }
    }
};
</script>
