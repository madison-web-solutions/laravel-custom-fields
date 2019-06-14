<template>
    <lcf-input-wrapper class="lcf-input lcf-input-markdown" :class="isFullScreen ? 'lcf-fullscreen' : ''" v-bind="wrapperProps">
        <textarea :id="inputId" :class="inputClasses" ref="input" :name="name" :disabled="disabled" @change="change" @input="updatePreview">{{ value }}</textarea>
        <div class="lcf-markdown-preview" v-html="html"></div>
        <button type="button" data-name="fullscreen" class="lcf-btn-icon" @click="toggleFullScreen" aria-label="Enter fullscreen mode"><i class="fas fa-expand"></i></button>
    </lcf-input-wrapper>
</template>

<script>
import { debounce } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    data: function() {
        return {
            html: '',
            isFullScreen: false,
        };
    },
    created: function() {
        this.updatePreview = debounce(() => {
            this.$lcfStore.getMarkdown(this.$refs.input.value, html => {
                this.html = html;
            });
        }, 300);
        this.updatePreview();
    },
    methods: {
        change: function() {
            if (this.disabled) {return;}
            this.$emit('change', {key: this._key, value: this.$refs.input.value});
        },
        toggleFullScreen: function() {
            return this.isFullScreen = ! this.isFullScreen;
        }
    }
};
</script>
