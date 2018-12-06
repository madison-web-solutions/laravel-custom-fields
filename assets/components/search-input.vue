<template>
    <div>
        <input :name="nameAttr" type="hidden" :value="value" />
        <input type="text" :class="{'lcf-input-has-error': hasError}" disabled :value="displayName" />
        <input ref="input" type="search" value="" placeholder="Search" @input="search" />
        <ul v-if="suggestions">
            <li v-for="suggestion in suggestions">
                <button type="button" @click="change(suggestion)">{{ suggestion.label }}</button>
            </li>
        </ul>
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
            displayName: '',
            suggestions: []
        }
    },
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue);
        }
        if (this.value) {
            var origValue = this.value;
            axios.get('/lcf/display-name', {params: {
                path: this.pathStr,
                id: origValue,
            }}).then(response => {
                // make sure value still matches
                if (this.value == origValue) {
                    this.displayName = response.data;
                }
            });
        }
        this.search = _.debounce(() => {
            if (this.$refs.input.value.length >= 2) {
                axios.get('/lcf/suggestions', {params: {
                    path: this.pathStr,
                    search: this.$refs.input.value,
                }}).then(response => {
                    this.suggestions = response.data;
                });
            }
        }, 300);
    },
    methods: {
        clearSearch: function() {
            this.suggestions = [];
            this.$refs.input.value = '';
        },
        change: function(suggestion) {
            this.clearSearch();
            this.displayName = suggestion.label;
            this.updateMyValue(suggestion.id);
        }
    }
};
</script>
