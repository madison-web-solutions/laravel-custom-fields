<template>
    <div class="lcf-ml-inspect" :data-extension="extension" :data-category="category">
        <div data-name="back">
            <a href="#" @click.stop="close"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
        </div>
        <div data-name="preview">
            <img v-if="hasImage" :src="item.url" />
            <p v-if="iconClass" class="lcf-ml-preview-icon"><i :class="iconClass"></i></p>
            <p v-if="! hasImage">Media Type: {{ category }}</p>
        </div>
        <div data-name="details">
            <lcf-text-input key="title" label="Title" :value="displayVals.title" @input="change" />
            <lcf-text-input key="alt" :label="isImage ? 'Alt' : 'Subtitle'" :value="displayVals.alt" @input="change" />
            <p v-if="imgWidth && imgHeight">{{ imgWidth }}px x {{ imgHeight }}px</p>
            <div class="lcf-btn-group-right">
                <button v-if="editable && edited" type="button" class="lcf-btn-primary" @click.stop="saveUpdates">Save Changes</button>
                <button type="button" class="lcf-btn" @click.stop="close"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                <button v-if="deletable" type="button" class="lcf-btn-danger" @click.stop="deleteItem">Delete</button>
            </div>
        </div>
        <div data-name="url">
            URL:
            <a v-if="item.url" :href="item.url" target="_blank">{{ item.url }}</a>
            <span v-if="! item.url" class="lcf-error">Error - File not found</span>
        </div>
    </div>
</template>

<script>
import { get } from 'lodash-es';
import Util from '../util.js';
export default {
    props: {
        item: {
            required: true
        },
        editable: {
            required: false,
            default: false
        },
        deletable: {
            required: false,
            default: false
        }
    },
    data: function() {
        return {
            editedVals: {
                title: null,
                alt: null
            },
            imgWidth: null,
            imgHeight: null,
        };
    },
    created: function() {
        this.getDimensions();
    },
    computed: {
        title: function() {
            return get(this.item, 'title');
        },
        alt: function() {
            return get(this.item, 'alt');
        },
        displayVals: function() {
            return {
                title: this.editedVals.title == null ? this.title : this.editedVals.title,
                alt: this.editedVals.alt == null ? this.alt : this.editedVals.alt
            };
        },
        edited: function() {
            return (this.displayVals.title != this.title) || (this.displayVals.alt != this.alt);
        },
        category: function() {
            return get(this.item, 'category')
        },
        extension: function() {
            return get(this.item, 'extension');
        },
        isImage: function() {
            return get(this.item, 'category') == 'Image';
        },
        hasImage: function() {
            return this.isImage && this.item.url;
        },
        iconClass: function() {
            return Util.iconClassForMediaItem(this.item);
        },
        imageWithDimensionsUrl: function() {
            return (this.hasImage && this.extension != 'svg') ? this.item.url : null;
        }
    },
    watch: {
        imageWithDimensionsUrl: function() {
            this.getDimensions();
        }
    },
    methods: {
        getDimensions: function() {
            this.imgWidth = null;
            this.imgHeight = null;
            if (this.imageWithDimensionsUrl) {
                var img = new Image();
                img.onload = () => {
                    this.imgWidth = img.width;
                    this.imgHeight = img.height;
                };
                img.src = this.imageWithDimensionsUrl;
            }
        },
        change: function(e) {
            this.editedVals[e.key] = e.value;
        },
        close: function() {
            this.$emit('close');
        },
        deleteItem: function() {
            this.$emit('deleteItem');
        },
        saveUpdates: function() {
            if (this.edited) {
                this.$lcfStore.updateMediaItem(this.item.id, this.displayVals, () => {
                    this.editedVals.title = null;
                    this.editedVals.alt = null;
                }, (errorMsg) => {
                    alert(errorMsg);
                });
            }
        }
    }
};
</script>
