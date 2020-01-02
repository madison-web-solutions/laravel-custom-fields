<template>
    <lcf-input-wrapper class="lcf-input lcf-input-ckeditor" :class="isFullScreen ? 'lcf-fullscreen' : ''" v-bind="wrapperProps">
        <input :id="inputId" :name="name" type="hidden" :value="value" />
        <div ref="editor"></div>
    </lcf-input-wrapper>
</template>

<script>
import { debounce } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
import ckeditorConfig from '../../ckeditor-config.js';
import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
const INPUT_DEBOUNCE_WAIT = 300;
export default {
    mixins: [inputMixin],
    props: {},
    data: function() {
        return {
            lastEditorData: '',
            isFullScreen: false,
        };
    },
    mounted: function() {
        this.instance = null;
        this.createEditor();
    },
    beforeDestroy() {
        if (this.instance) {
            this.instance.destroy();
            this.instance = null;
        }
    },
    watch: {
        value: function() {
            if (this.instance && this.lastEditorData != this.value) {
                this.instance.setData(this.value);
            }
        }
    },
    methods: {
        createEditor: function() {
            if (this.instance) {
                return;
            }

            var config = ckeditorConfig();
            if (this.value) {
                config.initialData = this.value;
            }

            ClassicEditor.create(this.$refs.editor, config).then((editor) => {
                // Save the reference to the instance for further use.
                this.instance = editor;
                this.initEditor(editor);
            }).catch(error => {
                console.error(error.stack);
            });
        },
        initEditor: function(editor) {
            // Set initial disabled state.
            editor.isReadOnly = this.disabled;

            const handleEditorDataChange = (evt) => {
                if (this.disabled) {return;}
                // Cache the last editor data.
                const data = editor.getData();
                console.log(data);
                if (data != this.lastEditorData) {
                    this.lastEditorData = data;
                    this.$emit('change', {key: this._key, value: data});
                }
            };
            editor.model.document.on('change:data', debounce(handleEditorDataChange, INPUT_DEBOUNCE_WAIT));

            console.log(Array.from(editor.ui.componentFactory.names()));
        }
    }
};
</script>
