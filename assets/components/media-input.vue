<template>
    <div>
        <input :name="nameAttr" type="hidden" :value="value" />
        <template v-if="!libraryOpen">
            <media-preview v-if="item" :item="item" />
            <div v-if="value && !item" class="lcf-ml-preview lcf-ml-active">
                <p>Loading</p>
            </div>
            <button type="button" @click="openLibrary">{{ value ? 'Change' : 'Select' }}</button>
            <button type="button" v-if="value" @click="remove">Remove</button>
        </template>
        <media-library v-if="libraryOpen" @select="select" @close="closeLibrary" />
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        return {
            item: null,
            libraryOpen: false
        };
    },
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue);
        }
        if (this.value) {
            var origValue = this.value;
            axios.get('/lcf/media-library/' + origValue).then(response => {
                if (this.value == origValue) {
                    this.item = response.data.item;
                }
            });
        }
    },
    computed: {
        selectLabel: function() {
            return this.value ? 'Change' : 'Select';
        }
    },
    methods: {
        openLibrary: function() {
            this.libraryOpen = true;
        },
        closeLibrary: function() {
            this.libraryOpen = false;
        },
        remove: function() {
            this.libraryOpen = false;
            this.item = null;
            this.updateMyValue(null);
        },
        select: function(e) {
            this.libraryOpen = false;
            this.item = e.item;
            this.updateMyValue(e.item.id);
        }
    }
};
</script>
