<template>
    <div :class="isFullScreen ? 'fullscreen' : ''">
        <textarea ref="input" :name="nameAttr" :class="{'lcf-input-has-error': hasError}" @change="change" @input="updatePreview">{{ value }}</textarea>
        <div class="preview" v-html="html"></div>
        <button type="button" name="fullscreen" @click="toggleFullScreen" aria-label="Enter fullscreen mode"><i class="fas fa-expand"></i></button>
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
            isFullScreen: false,
        };
    },
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(String(this.defaultValue));
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
            this.updateMyValue(this.$refs.input.value);
        },
        toggleFullScreen: function() {
            return this.isFullScreen = ! this.isFullScreen;
        }
    },
};
</script>
