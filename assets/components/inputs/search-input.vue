<template>
    <div>
        <input type="hidden" :name="name" :value="value" />
        <input type="text" disabled :value="displayString" />
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
import inputMixin from '../../input-mixin.js';
import { debounce, isArray } from 'lodash-es';
export default {
    mixins: [inputMixin],
    data: function() {
        return {
            suggestions: false, // false means not searched, an empty array means no results
        }
    },
    created: function() {
        this.search = debounce(() => {
            this.suggestions = false;
            var search = this.$refs.input.value;
            if (search.length >= 2) {
                this.$lcfStore.getSuggestions(this.settings, search, (searched, suggestions) => {
                    if (this.$refs.input.value != searched) {
                        // Don't show suggestions for an out of date search
                        return;
                    }
                    this.suggestions = suggestions;
                });
            }
        }, 300);
    },
    computed: {
        displayName: function() {
            return this.$lcfStore.getDisplayName(this.settings, this.value);
        },
        displayString: function() {
            return this.displayName ? this.displayName : (this.value ? ('(' + this.value + ')') : '');
        },
        searched: function() {
            return isArray(this.suggestions);
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
            this.$emit('change', {key: this._key, value: suggestion.id});
        }
    }
};
</script>
