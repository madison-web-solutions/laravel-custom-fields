<template>
    <div>
        <input ref="input" :name="name" type="hidden" :value="value" />
        <input type="text" disabled :value="displayName" />
        <input ref="input" type="search" value="" placeholder="search" @input="search" />
        <button v-for="suggestion in suggestions" type="button" @click="change(suggestion)">{{ suggestion.label }}</button>
    </div>
</template>

<script>
import _ from 'lodash';
import axios from 'axios';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    data: function() {
        return {
            value: this.initialValue,
            displayName: '',
            suggestions: []
        }
    },
    computed: {
        name: function() {
            var name = this.path[1];
            for (var i = 2; i < this.path.length; i++) {
                name += '[' + this.path[i] + ']';
            }
            return name;
        },
        hasError: function() {
            return !!(this.errors[this.path.join('.')] || []).length;
        }
    },
    methods: {
        search: _.debounce(function() {
            console.log(this.$refs.input.value);
            if (this.$refs.input.value.length >= 2) {
                axios.get('/lcf/suggestions', {params: {
                    path: this.path.join('.'),
                    search: this.$refs.input.value,
                }}).then(response => {
                    console.log(response);
                    this.suggestions = response.data;
                });
            }
        }, 300),
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
                path: this.path.join('.'),
                id: this.initialValue,
            }}).then(response => {
                console.log(response);
                // make sure value still matches
                if (this.value == this.initialValue) {
                    this.displayName = response.data;
                }
            });
        }
    }
};
</script>
