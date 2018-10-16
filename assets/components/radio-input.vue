<template>
    <div ref="input" class="lcf-radio-group" :class="{'lcf-input-has-error': hasError}" @change="change">
        <label v-for="optionLabel, optionValue in field.options.choices">
            <input :name="nameAttr" type="radio" :value="optionValue" :checked="value == optionValue" />
            {{ optionLabel }}
        </label>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var value = _.defaultTo(this.initialValue, _.get(this.field, 'options.default', null));
        return {
            value: value
        };
    },
    methods: {
        change: function() {
            this.value = null;
            var radios = this.$refs.input.querySelectorAll('input[type=radio]');
            for (var i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    this.value = radios[i].value;
                }
            }
        }
    }
};
</script>
