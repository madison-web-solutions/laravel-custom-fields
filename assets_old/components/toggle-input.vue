<template>
    <div class="lcf-toggle" :class="{'lcf-input-has-error': hasError}">
        <input :name="nameAttr" type="hidden" :value="value ? 'true' : 'false'" />
        <button @click="toggle" type="button">
            <span :style="{display: value ? 'inline-block' : 'none'}"><i class="fas fa-toggle-on"></i></span>
            <span :style="{display: value ? 'none' : 'inline-block'}"><i class="fas fa-toggle-off"></i></span>
            <span>{{ value ? field.options.true_label : field.options.false_label }}</span>
        </button>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null) {
            this.updateMyValue(!!this.defaultValue);
        }
    },
    methods: {
        toggle: function() {
            this.updateMyValue(! this.value);
        }
    }
};
</script>
