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
            <form ref="editForm">
                <lcf-text-input key="title" name="title" :disabled="editFieldsDisabled" label="Title" :value="vals.title" :errors="errors.title" @input="change" />
                <lcf-text-input key="alt" name="alt" :disabled="editFieldsDisabled" :label="isImage ? 'Alt' : 'Subtitle'" :value="vals.alt" :errors="errors.alt" @input="change" />
                <lcf-select-input key="folder_id" name="folder_id" :disabled="editFieldsDisabled" label="Folder" :value="vals.folder_id" :errors="errors.folder_id" :choices="folderChoices" placeholder="None" @change="change"></lcf-select-input>
                <div v-if="editable" class="lcf-input-wrapper lcf-input">
                    <label class="lcf-field-label">Replace File</label>
                    <input type="file" name="replace_file" ref="replaceFileInput" :disabled="editFieldsDisabled" @change="setReplaceFile" />
                    <lcf-error-messages :errors="errors.replace_file" />
                </div>
                <lcf-error-messages :errors="errors.unknown" />
            </form>

            <p class="lcf-ml-inspect-meta">ID: {{ item.id }}</p>
            <p class="lcf-ml-inspect-meta" v-if="imgWidth && imgHeight">Dimensions: {{ imgWidth }}px x {{ imgHeight }}px</p>
            <div class="lcf-btn-group-right" v-if="! updating">
                <button v-if="editable && edited" type="button" class="lcf-btn-primary" @click.stop="saveUpdates">Save Changes</button>
                <button type="button" class="lcf-btn" @click.stop="close"><i class="fas fa-long-arrow-alt-left"></i> Back</button>
                <button v-if="deletable" type="button" class="lcf-btn-danger" @click.stop="deleteItem">Delete</button>
            </div>
            <lcf-loading v-if="updating" />
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
            vals: {
                title: null,
                alt: null,
                folder_id: null,
                replace_file: null,
            },
            updating: false,
            errors: {},
            imgWidth: null,
            imgHeight: null,
        };
    },
    mounted: function() {
        this.resetForm();
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
        edited: function() {
            return (this.vals.title != this.title) || (this.vals.alt != this.alt) || (this.vals.folder_id != this.folderId) || (this.vals.replace_file != null);
        },
        editFieldsDisabled: function() {
            return this.updating || ! this.editable;
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
        resetForm: function() {
            this.vals.title = this.title;
            this.vals.alt = this.alt;
            this.vals.folder_id = this.folderId;
            this.vals.replace_file = null;
            if (this.editable) {
                this.$refs.replaceFileInput.value = null;
            }
        },
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
            this.vals[e.key] = e.value;
        },
        setReplaceFile: function() {
            this.vals.replace_file = this.$refs.replaceFileInput.files[0] || null;
        },
        close: function() {
            this.$emit('close');
        },
        deleteItem: function() {
            this.$emit('deleteItem');
        },
        saveUpdates: function() {
            if (this.editable && this.edited) {
                this.updating = true;
                this.errors = {};
                var data = new FormData(this.$refs.editForm);
                this.$lcfStore.updateMediaItem(this.item.id, data, () => {
                    this.updating = false;
                    window.setTimeout(() => this.resetForm(), 10);
                }, (errors) => {
                    this.errors = errors;
                    this.updating = false;
                });
            }
        }
    }
};
</script>
