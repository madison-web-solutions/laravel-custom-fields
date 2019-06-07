<template>
    <div>
        <lcf-input-wrapper key="manual" inputComponent="lcf-radio-input" :settings="manualField" :value="manual ? 'true' : 'false'" @change="change" />
        <lcf-input-wrapper v-if="! manual" key="link_id" inputComponent="lcf-search-input" :settings="linkIdField" :value="linkId" @change="change" />
        <lcf-input-wrapper v-if="manual" key="url" inputComponent="lcf-text-input" :settings="urlField" :value="url" @change="change" />
        <lcf-input-wrapper v-if="withLabel" key="label" inputComponent="lcf-text-input" :settings="labelField" :value="label" @change="change" />
    </div>
</template>

<script>
import { get } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        withLabel: function() {
            return this.setting('with_label', true);
        },
        manual: function() {
            return get(this.value, 'manual', false);
        },
        linkId: function() {
            return get(this.value, 'link_id');
        },
        url: function() {
            return get(this.value, 'url');
        },
        label: function() {
            return get(this.value, 'label');
        },
        linkInfo: function() {
            if (this.linkId && ! this.manual) {
                return this.$lcfStore.lookupLink(this.linkId);
            } else {
                return {url: this.url, label: this.url};
            }
        },
        defaultLabel: function() {
            return get(this.linkInfo, 'label');
        },
        manualField: function() {
            return {
                default: false,
                name: this.name + '[manual]',
                choices: [
                    {value: 'false', label: 'Search for page to link to'},
                    {value: 'true', label: 'Enter URL manually'}
                ]
            };
        },
        linkIdField: function() {
            return {
                label: 'Link to',
                name: this.name + '[link_id]',
                type: 'link',
                required: this.required
            };
        },
        urlField: function() {
            return {
                label: 'Link URL',
                name: this.name + '[url]',
                required: this.required,
                placeholder: 'https://'
            }
        },
        labelField: function() {
            return {
                label: 'Link label',
                name: this.name + '[label]',
                placeholder: this.defaultLabel
            };
        }
    },
    methods: {
        change: function(e) {
            var payload = {key: this._key, value: {}};
            if (e.key == 'manual') {
                payload.value.manual = (e.value == 'true');
            } else {
                payload.value[e.key] = e.value;
            }
            this.$emit('change', payload);
        }
    }
};
</script>
