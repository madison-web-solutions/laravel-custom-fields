<template>
    <lcf-input-wrapper class="lcf-input lcf-input-media" v-bind="wrapperProps">
        <input type="hidden" :name="name" :value="value" />
        <lcf-media-library v-if="libraryOpen" :category="category" @select="select" @close="closeLibrary" />
        <div v-if="! libraryOpen" :class="inputClasses">
            <lcf-media-preview v-if="item" :item="item" />
            <div v-if="value && !item" class="lcf-ml-preview lcf-ml-active">
                <p>Loading</p>
            </div>
            <button type="button" class="lcf-btn" @click="openLibrary">{{ value ? 'Change' : 'Select' }}</button>
            <button type="button" class="lcf-btn" v-if="value" @click="remove" data-action="remove-image">Remove</button>
        </div>
    </lcf-input-wrapper>
</template>

<script>
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        category: {
            type: String,
            required: false
        }
    },
    data: function() {
        return {
            libraryOpen: false
        };
    },
    computed: {
        item: function() {
            return this.$lcfStore.getMediaItem(this.value);
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
            this.$emit('change', {key: this._key, value: null});
        },
        select: function(e) {
            this.libraryOpen = false;
            this.$emit('change', {key: this._key, value: e.item.id});
        }
    }
};
</script>
