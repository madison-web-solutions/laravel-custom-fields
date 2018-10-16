<template>
    <div>
        <input :name="nameAttr" type="hidden" :value="value" />
        <input type="text" :class="{'lcf-input-has-error': hasError}" disabled :value="displayName" />
        <input ref="input" type="search" value="" placeholder="search" @input="search" />
        <button v-for="suggestion in suggestions" type="button" @click="change(suggestion)">{{ suggestion.label }}</button>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        return {
            value: this.initialValue,
            displayName: '',
            suggestions: []
        }
    },
    methods: {
        clearSearch: function() {
            this.suggestions = [];
            this.$refs.input.value = '';
        },
        change: function(suggestion) {
            this.clearSearch();
            this.value = suggestion.id;
            this.displayName = suggestion.label;
        }
    },
    created: function() {
        if (this.initialValue) {
            axios.get('/lcf/display-name', {params: {
                path: this.fieldPath,
                id: this.initialValue,
            }}).then(response => {
                console.log(response);
                // make sure value still matches
                if (this.value == this.initialValue) {
                    this.displayName = response.data;
                }
            });
        }
        this.search = _.debounce(() => {
            if (this.$refs.input.value.length >= 2) {
                axios.get('/lcf/suggestions', {params: {
                    path: this.fieldPath,
                    search: this.$refs.input.value,
                }}).then(response => {
                    this.suggestions = response.data;
                });
            }
        }, 300);
    }
};
</script>
