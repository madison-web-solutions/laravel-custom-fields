<template>
    <div class="lcf-link-input" :class="{'lcf-input-has-error': hasError, 'lcf-link-input-with-label': withLabel}">
        <field :path="path.concat('manual')" :field="manualField" :errors="errors" />
        <div class="lcf-link-input-input">
            <div class="lcf-link-input-link">
                <label>Link To:</label>
                <field v-if="! isManual" :path="path.concat('obj')" :field="objField" :errors="errors" />
                <field v-if="isManual" :path="path.concat('url')" :field="urlField" :errors="errors" />
            </div>
            <div v-if="withLabel" class="lcf-link-input-label">
                <label>Link Label:</label>
                <field :path="path.concat('label')" :field="labelField" :errors="errors" />
            </div>
        </div>
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
            objCache: {}
        };
    },
    created: function() {
        this.$store.commit('objectInitKeys', {path: this.pathStr, keys: ['manual', 'obj', 'url']});
    },
    computed: {
        isManual: function() {
            return this.getValueAtPath(this.path.concat('manual'));
        },
        obj: function() {
            return this.getValueAtPath(this.path.concat('obj'));
        },
        url: function() {
            return this.getValueAtPath(this.path.concat('url'));
        },
        withLabel: function() {
            return true;
        },
        info: function() {
            var objSpec = this.obj;
            if (this.isManual || ! objSpec) {
                return {url: this.url, label: this.url};
            } else {
                if (! this.objCache[this.obj] ) {
                    this.$set(this.objCache, objSpec, {url: '', label: ''});
                    axios.get('/lcf/link-lookup', {params: {
                        spec: objSpec,
                    }}).then(response => {
                        if (response.data) {
                            this.$set(this.objCache, objSpec, response.data);
                        }
                    });
                }
                return this.objCache[objSpec];
            }
        },
        defaultLabel: function() {
            return this.info.label;
        },
        manualField: function() {
            return {
                inputComponent: 'toggle-input',
                type: 'toggle',
                options: {
                    default: false,
                    false_label: 'Search for page to link to',
                    true_label: 'Enter URL manually'
                }
            };
        },
        objField: function() {
            return {
                inputComponent: 'search-input',
                type: 'link',
                options: {
                    required: this.required,
                }
            };
        },
        urlField: function() {
            return {
                inputComponent: 'text-input',
                type: 'text',
                options: {
                    required: this.required,
                    placeholder: 'https://'
                }
            }
        },
        labelField: function() {
            return {
                inputComponent: 'text-input',
                type: 'text',
                options: {
                    required: false,
                    placeholder: this.defaultLabel
                }
            };
        }
    },
    methods: {
        lookupObj: function(spec) {

        }
    }
};
</script>
