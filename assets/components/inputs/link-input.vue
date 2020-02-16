<template>
    <lcf-input-wrapper class="lcf-input lcf-input-link" v-bind="wrapperProps">
        <lcf-radio-input key="manual" :name="name + '[manual]'" :choices="manualChoices" :value="manualVal ? 'true' : 'false'" :disabled="disabled" @change="change" />
        <lcf-search-input v-if="! manualVal" key="link_id" :name="name + '[link_id]'" label="Link to" :required="required" searchType="link" :searchSettings="{}" :value="linkIdVal" :disabled="disabled" @change="change" />
        <lcf-text-input v-if="manualVal" key="url" :name="name + '[url]'" label="Link URL" :required="required" placeholder="https://" :value="urlVal" :disabled="disabled" @change="change" />
        <lcf-text-input v-if="withLabel" key="label" :name="name + '[label]'" :value="labelVal" label="Link label" :placeholder="defaultLabel" :disabled="disabled" @change="change" />
    </lcf-input-wrapper>
</template>

<script>
import { get } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        withLabel: {
            type: Boolean,
            required: false,
            default: true
        }
    },
    computed: {
        manualVal: function() {
            return get(this.value, 'manual', false);
        },
        linkIdVal: function() {
            return get(this.value, 'link_id');
        },
        urlVal: function() {
            return get(this.value, 'url');
        },
        labelVal: function() {
            return get(this.value, 'label');
        },
        linkInfo: function() {
            if (this.linkIdVal && ! this.manualVal) {
                return this.$lcfStore.lookupSearchObj('link', this.searchSettings, this.linkIdVal);
            } else {
                return {url: this.urlVal, label: this.urlVal};
            }
        },
        defaultLabel: function() {
            return get(this.linkInfo, 'label');
        },
        manualChoices: function() {
            return [
                {value: 'false', label: 'Search for page to link to'},
                {value: 'true', label: 'Enter URL manually'}
            ];
        }
    },
    methods: {
        change: function(e) {
            if (this.disabled) {return;}
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
