<template>
    <lcf-input-wrapper class="lcf-input lcf-input-search" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value" />
        <div class="lcf-combo">
            <input type="text" :class="inputClasses" :disabled="disabled" readonly :value="displayString" @click="toggleOpenSearch" />
            <button v-if="value" type="button" class="lcf-combo-button" @click="clearValue"><i class="fas fa-times"></i></button>
            <button type="button" class="lcf-combo-button" @click="toggleOpenSearch"><i class="fas fa-caret-down"></i></button>
        </div>
        <div ref="searchInterface" v-if="searchOpen" class="lcf-search-interface">
            <input ref="input" type="search" value="" placeholder="Search" :disabled="disabled" @input="handleSearch" @keydown.enter.prevent="handleSearch" />
            <p v-if="statusMessage" class="lcf-help">{{ statusMessage }}</p>
            <div v-if="hasResults" class="lcf-search-suggestions" aria-role="listbox" @scroll="handleScroll">
                <div v-for="suggestion in suggestions" class="lcf-search-suggestion" aria-role="option" @click="change(suggestion)">{{ suggestion.display_name }}</div>
            </div>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import Util from '../../util.js';
import inputMixin from '../../input-mixin.js';
import { debounce, isArray } from 'lodash-es';
export default {
    mixins: [inputMixin],
    props: {
        searchType: {
            type: String,
            required: true
        },
        searchSettings: {
            type: Object,
            required: true
        }
    },
    data: function() {
        return {
            searchOpen: false,
            searchString: null,
            page: 1,
            hasMore: true,
            suggestions: null, // null means not searched, an empty array means no results
            searchId: null,
        }
    },
    created: function() {
        this.getDebounce = debounce((searchString, page, callback) => {
            this.searchId = this.$lcfStore.getSuggestions(this.searchType, this.searchSettings, searchString, page, callback);
        }, 300);

        this.handleDocumentClick = (e) => {
            if (this.$refs.searchInterface) {
                if (! Util.elementIsOrContains(this.$refs.searchInterface, e.target)) {
                    // user clicked outside the search interface, so interpret this as trying to close the search
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
            return this.$lcfStore.getDisplayName(this.searchType, this.searchSettings, this.value);
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
            if (this.searchId) {
                return 'Searching ...';
            } else if (this.noResults) {
                return 'No results';
            } else {
                return null;
            }
        }
    },
    methods: {
        handleSearch: function() {
            this.suggestions = null;
            var searchString = this.$refs.input.value;
            if (searchString.length < 2) {
                return;
            }
            this.getDebounce(searchString, 1, (searchedId, suggestions, hasMore) => {
                if (this.searchId != searchedId) {
                    return;
                }
                this.searchId = null;
                this.searchString = searchString;
                this.page = 1;
                this.hasMore = hasMore;
                this.suggestions = suggestions;
            });
        },
        handleScroll: function(e) {
            var ele = e.target;
            var scrollProportion = ((ele.scrollTop + ele.offsetHeight) / ele.scrollHeight);
            if (this.hasMore && !this.searchId && scrollProportion > 0.9) {
                var page = this.page + 1;
                this.searchId = this.$lcfStore.getSuggestions(this.searchType, this.searchSettings, this.searchString, page, (searchedId, suggestions, hasMore) => {
                    if (this.searchId != searchedId) {
                        return;
                    }
                    this.searchId = null;
                    this.page = page;
                    this.hasMore = hasMore;
                    this.suggestions = this.suggestions.concat(suggestions);
                });
            }
        },
        toggleOpenSearch: function() {
            if (this.searchOpen) {
                this.closeSearch();
            } else {
                this.openSearch();
            }
        },
        closeSearch: function() {
            this.suggestions = null;
            this.$refs.input.value = '';
            this.searchOpen = false;
            document.removeEventListener('click', this.handleDocumentClick);
        },
        openSearch: function() {
            if (this.disabled) {return;}
            this.suggestions = null;
            this.searchOpen = true;
            window.setTimeout(() => {
                document.addEventListener('click', this.handleDocumentClick);
                this.$refs.input.value = '';
                this.$refs.input.focus();
            }, 10);
        },
        change: function(suggestion) {
            if (this.disabled) {return;}
            this.closeSearch();
            this.$emit('change', {key: this._key, value: suggestion.id});
        },
        clearValue: function() {
            if (this.disabled) {return;}
            this.$emit('change', {key: this._key, value: null});
        }
    }
};
</script>
