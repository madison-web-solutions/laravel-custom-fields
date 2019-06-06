<template>
    <div class="lcf-input lcf-input-search">
        <input type="hidden" :name="name" :value="value" />

        <lcf-media-library v-if="libraryOpen" :category="category" @select="select" @close="closeLibrary" />

        <template v-if="! libraryOpen">
            <lcf-media-preview v-if="item" :item="item" />
            <div v-if="value && !item" class="lcf-ml-preview lcf-ml-active">
                <p>Loading</p>
            </div>
            <button type="button" @click="openLibrary">{{ value ? 'Change' : 'Select' }}</button>
            <button type="button" v-if="value" @click="remove" data-action="remove-image">Remove</button>
        </template>
    </div>
</template>

<script>
import { get } from 'lodash-es';
export default {
    props: ['settings', 'value', 'hasError'],
    data: function() {
        return {
            libraryOpen: false
        };
    },
    computed: {
        name: function() {
            return get(this.settings, 'name');
        },
        _key: function() {
            return get(this.settings, 'key', this.$vnode.key);
        },
        item: function() {
            return this.$lcfStore.getMediaItem(this.value);
        },
        category: function() {
            return get(this.settings, 'category');
        },
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
            this.$emit('change', {key: this._key, value: null});
        },
        select: function(e) {
            this.libraryOpen = false;
            this.$emit('change', {key: this._key, value: e.item.id});
        }
    }
};
</script>
