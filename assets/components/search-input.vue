<template>
    <div>
        <input :name="nameAttr" type="hidden" :value="value" />
        <input type="text" :class="{'lcf-input-has-error': hasError}" disabled :value="displayName" />
        <input ref="input" type="search" value="" placeholder="Search" @input="search" @keydown.enter.prevent="search" />
        <ul v-if="hasResults">
            <li v-for="suggestion in suggestions">
                <button type="button" @click="change(suggestion)">{{ suggestion.label }}</button>
            </li>
        </ul>
        <p v-if="noResults">No results</p>
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
            suggestions: false, // false means not searched, an empty array means no results
        }
    },
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue);
        }
        if (this.value) {
            var origValue = this.value;
            axios.get('/lcf/display-name', {params: {
                type: this.field.type,
                options: this.field.options,
                id: origValue,
            }}).then(response => {
                // make sure value still matches
                if (this.value == origValue) {
                    this.displayName = response.data;
                }
            });
        }
        this.search = _.debounce(() => {
            this.suggestions = false;
            if (this.$refs.input.value.length >= 2) {
                axios.get('/lcf/suggestions', {params: {
                    type: this.field.type,
                    options: this.field.options,
                    search: this.$refs.input.value,
                }}).then(response => {
                    this.suggestions = response.data;
                });
            }
        }, 300);
    },
    computed: {
        searched: function() {
            return _.isArray(this.suggestions);
        },
        hasResults: function() {
            return this.searched && (this.suggestions.length > 0);
        },
        noResults: function() {
            return this.searched && (this.suggestions.length == 0);
        }
    },
    methods: {
        clearSearch: function() {
            this.suggestions = false;
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
