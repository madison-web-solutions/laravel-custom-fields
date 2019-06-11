<template>
    <div class="lcf-input lcf-input-search">
        <input type="hidden" :name="name" :value="value" />
        <div class="lcf-combo" @click="toggleOpenSearch">
            <input type="text" :class="inputClasses" disabled :value="displayString" />
            <button type="button" class="lcf-combo-button"><i class="fas fa-caret-down"></i></button>
        </div>
        <div ref="searchInterface" v-if="searchOpen" class="lcf-search-interface">
            <input ref="input" type="search" value="" placeholder="Search" @input="search" @keydown.enter.prevent="search" />
            <p v-if="statusMessage" class="lcf-help">{{ statusMessage }}</p>
            <div v-if="hasResults" aria-role="listbox">
                <div v-for="suggestion in suggestions" class="lcf-search-suggestion" aria-role="option" @click="change(suggestion)">{{ suggestion.label }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import Util from '../../util.js';
import inputMixin from '../../input-mixin.js';
import { debounce, isArray } from 'lodash-es';
export default {
    mixins: [inputMixin],
    data: function() {
        return {
            searchOpen: false,
            searchingFor: null,
            suggestions: null, // null means not searched, an empty array means no results
        }
    },
    created: function() {
        this.search = debounce(() => {
            this.suggestions = null;
            this.searchingFor = this.$refs.input.value;
            if (this.searchingFor.length >= 2) {
                this.$lcfStore.getSuggestions(this.settings, this.searchingFor, (searched, suggestions) => {
                    if (this.searchingFor == searched) {
                        this.searchingFor = null;
                        this.suggestions = suggestions;
                    } else {
                        // Don't show suggestions for an out of date search
                        return;
                    }
                });
            }
        }, 300);
        this.handleDocumentClick = (e) => {
            console.log('handleDocumentClick');
            if (this.$refs.searchInterface) {
                if (! Util.elementIsOrContains(this.$refs.searchInterface, e.target)) {
                    // user clicked outside the search interface, so interpret this as trying to close the search
                    console.log('handleDocumentClick closing');
                    this.closeSearch();
                }
            }
        };
    },
    destroyed: function() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    computed: {
        displayName: function() {
            return this.$lcfStore.getDisplayName(this.settings, this.value);
        },
        displayString: function() {
            return this.displayName ? this.displayName : (this.value ? ('(' + this.value + ')') : '');
        },
        hasResults: function() {
            return isArray(this.suggestions) && (this.suggestions.length > 0);
        },
        noResults: function() {
            return isArray(this.suggestions) && (this.suggestions.length == 0);
        },
        statusMessage: function() {
            if (this.searchingFor) {
                return 'Searching ...';
            } else if (this.noResults) {
                return 'No results';
            } else {
                return null;
            }
        }
    },
    methods: {
        toggleOpenSearch: function() {
            if (this.searchOpen) {
                this.closeSearch();
            } else {
                this.openSearch();
            }
        },
        closeSearch: function() {
            console.log('closeSearch');
            this.suggestions = null;
            this.$refs.input.value = '';
            this.searchOpen = false;
            document.removeEventListener('click', this.handleDocumentClick);
        },
        openSearch: function() {
            console.log('openSearch');
            this.suggestions = null;
            this.searchOpen = true;
            window.setTimeout(() => {
                document.addEventListener('click', this.handleDocumentClick);
                this.$refs.input.value = '';
                this.$refs.input.focus();
            }, 10);
        },
        change: function(suggestion) {
            this.closeSearch();
            this.$emit('change', {key: this._key, value: suggestion.id});
        }
    }
};
</script>
