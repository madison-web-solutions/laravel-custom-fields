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
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        return {
            html: '',
        };
    },
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.$store.commit('updateValue', {path: this.pathStr, value: String(this.defaultValue)});
        }
        this.updatePreview = _.debounce(() => {
            axios.post('/lcf/markdown', {
                input: this.$refs.input.value
            }).then(response => {
                this.html = response.data;
            });
        }, 300);
        this.updatePreview();
    },
    methods: {
        change: function() {
            this.$store.commit('updateValue', {path: this.pathStr, value: this.$refs.input.value});
        }
    },
};
</script>
