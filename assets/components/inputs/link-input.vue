<template>
    <div class="lcf-input lcf-input-link">
        <lcf-input-wrapper label="" help="" errors="">
            <lcf-radio-input key="manual" :settings="manualField" :value="manual ? 'true' : 'false'" :hasError="false" @change="change" />
        </lcf-input-wrapper>
        <lcf-input-wrapper  v-if="! manual" label="Link to" help="" errors="">
            <lcf-search-input key="link_id" :settings="linkIdField" :value="linkId" :hasError="false" @change="change" />
        </lcf-input-wrapper>
        <lcf-input-wrapper  v-if="manual" label="Link URL" help="" errors="">
            <lcf-text-input key="url" :settings="urlField" :value="url" :hasError="false" @change="change" />
        </lcf-input-wrapper>
        <lcf-input-wrapper  v-if="withLabel" label="Link label" help="" errors="">
            <lcf-text-input key="label" :settings="labelField" :value="label" :hasError="false" @change="change" />
        </lcf-input-wrapper>
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
                name: this.name + '[link_id]',
                type: 'link',
                required: this.required
            };
        },
        urlField: function() {
            return {
                name: this.name + '[url]',
                required: this.required,
                placeholder: 'https://'
            }
        },
        labelField: function() {
            return {
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
