<template>
    <div>
        <textarea ref="input" :name="nameAttr" :class="{'lcf-input-has-error': hasError}" @change="change" @input="updatePreview">{{ value }}</textarea>
        <div v-html="html"></div>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var value = _.defaultTo(this.initialValue, _.get(this.field, 'options.default', null));
        return {
            value: value ? String(value) : '',
            html: '',
        };
    },
    methods: {
        change: function() {
            this.value = this.$refs.input.value;
        }
    },
    created: function() {
        this.updatePreview = _.debounce(() => {
            axios.post('/lcf/markdown', {
                input: this.$refs.input.value
            }).then(response => {
                this.html = response.data;
            });
        }, 300);
        this.updatePreview();
    }
};
</script>
