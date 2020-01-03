<template>
    <div class="lcf-ml-inspect" :data-extension="extension" :data-category="category">
        <div data-name="back">
            <a href="#" @click.stop.prevent="close"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
        </div>
        <div data-name="preview">
            <img v-if="hasImage" :src="item.url" />
            <p v-if="iconClass" class="lcf-ml-preview-icon"><i :class="iconClass"></i></p>
            <p v-if="! hasImage">Media Type: {{ category }}</p>
        </div>
        <div data-name="details">
            <lcf-text-input key="title" :disabled="! editable" label="Title" :value="displayVals.title" @input="change" />
            <lcf-text-input key="alt" :disabled="! editable" :label="isImage ? 'Alt' : 'Subtitle'" :value="displayVals.alt" @input="change" />
            <lcf-select-input key="folder_id" :disabled="! editable" label="Folder" :value="displayVals.folder_id" :choices="folderChoices" placeholder="None" @change="change"></lcf-select-input>
            <p class="lcf-ml-inspect-meta">ID: {{ item.id }}</p>
            <p class="lcf-ml-inspect-meta" v-if="imgWidth && imgHeight">Dimensions: {{ imgWidth }}px x {{ imgHeight }}px</p>
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
import { get, map } from 'lodash-es';
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
                title: false,
                alt: false,
                folder_id: false,
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
        folderId: function() {
            return get(this.item, 'folder_id');
        },
        displayVals: function() {
            return {
                title: this.editedVals.title === false ? this.title : this.editedVals.title,
                alt: this.editedVals.alt === false ? this.alt : this.editedVals.alt,
                folder_id: this.editedVals.folder_id === false ? this.folderId : this.editedVals.folder_id
            };
        },
        edited: function() {
            return (this.displayVals.title != this.title) || (this.displayVals.alt != this.alt) || (this.displayVals.folder_id != this.folderId);
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
        },
        folders: function() {
            return this.$lcfStore.getMediaLibraryFolders();
        },
        folderChoices: function() {
            return map(this.folders, (folder) => {
                return {
                    value: folder.id,
                    label: folder.path.join(' / ')
                };
            });
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
                    this.editedVals.title = false;
                    this.editedVals.alt = false;
                    this.editedVals.folder_id = false;
                }, (errorMsg) => {
                    alert(errorMsg);
                });
            }
        }
    }
};
</script>
