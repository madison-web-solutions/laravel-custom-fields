<template>
    <div class="lcf-media-library" :class="cssClass" @dragover.prevent="dragIn" @dragenter.prevent="dragIn" @dragleave.prevent="dragOut" @dragend.prevent="dragOut" @drop.prevent="dragDrop">
        <div v-if="! showInspect">
            <button type="button" class="lcf-ml-tab" :class="{active: mode == 'library'}" @click.prevent="libraryMode">Library</button>
            <button type="button" class="lcf-ml-tab" :class="{active: mode == 'upload'}" @click.prevent="uploadMode">Upload</button>
            <button v-if="! standalone" type="button" class="lcf-ml-tab" @click="cancel">Cancel</button>
        </div>
        <div class="lcf-panel" :style="{display: showLibrary ? 'block' : 'none'}">
            <lcf-input-wrapper class="lcf-input">
                <input ref="searchInput" type="search" placeholder="search" @input="handleSearch" @keydown.enter.prevent="handleSearch" />
            </lcf-input-wrapper>

            <p v-if="! standalone" >Click to select:</p>
            <div class="lcf-ml-index" @scroll="handleScroll">
                <template v-for="upload in uploads">
                    <div class="lcf-ml-preview" :class="'lcf-ml-'+upload.status" v-if="upload.status != 'done'">
                        <p v-if="upload.status == 'new'">Queued</p>
                        <p v-if="upload.status == 'active'">{{ upload.progress }}%</p>
                        <p v-if="upload.status == 'error'">Error: {{ upload.error }}</p>
                    </div>
                </template>
                <lcf-media-preview v-for="item in items" :key="item.id" :item="item" @select="select" />
            </div>
        </div>
        <div class="lcf-panel" v-if="showUpload">
            <div class="lcf-ml-upload-form">
                <label class="lcf-btn" :for="fileInputId">Choose a file</label>
                <input ref="fileInput" type="file" :id="fileInputId" class="lcf-upload-file-input" multiple @change="filesSelected" />
                <p>... or drag and drop into this box</p>
            </div>
        </div>
        <div class="lcf-panel" v-if="showInspect">
            <lcf-media-inspect :item="selectedItem" :selectable="! standalone" :editable="true" :deletable="true" @close="libraryMode" @deleteItem="deleteSelectedItem" />
        </div>
    </div>
</template>

<script>
import { get, includes, debounce, forEach, filter, map } from 'lodash-es';
export default {
    props: {
        category: {
            required: false
        },
        standalone: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data: function() {
        return {
            mode: 'library',
            fileInputId: Math.random().toString(36).substring(2),
            hasDragObj: false,
            uploads: [],
            uploading: false,
            searchString: null,
            page: 1,
            hasMore: true,
            itemIds: [],
            selectedItemId: null,
            searchId: null,
        }
    },
    created: function() {
        this.getDebounce = debounce((category, searchString, page, callback) => {
            this.searchId = this.$lcfStore.searchMediaLibrary(category, searchString, page, callback);
        }, 300);

        this.searchId = this.$lcfStore.searchMediaLibrary(this.category, '', 1, (searchedId, itemIds, hasMore) => {
            if (this.searchId != searchedId) {
                return;
            }
            this.searchId = null;
            this.hasMore = hasMore;
            this.libraryAddMany(itemIds);
        });
    },
    computed: {
        cssClass: function() {
            return {
                'lcf-ml-has-drag-obj': this.hasDragObj
            }
        },
        items: function() {
            return filter(map(this.itemIds, (itemId) => this.$lcfStore.getMediaItem(itemId)));
        },
        selectedItem: function() {
            return this.$lcfStore.getMediaItem(this.selectedItemId);
        },
        showUpload: function() {
            return this.hasDragObj || this.mode == 'upload';
        },
        showLibrary: function() {
            return this.mode == 'library' && ! this.hasDragObj;
        },
        showInspect: function() {
            return this.mode == 'inspect' && this.selectedItem && ! this.hasDragObj;
        }
    },
    methods: {
        libraryMode: function() {
            this.mode = 'library';
            this.selectedItemId = null;
        },
        uploadMode: function() {
            this.mode = 'upload';
        },
        libraryClear: function() {
            this.itemIds = [];
        },
        libraryAddMany: function(itemIds, atStart) {
            forEach(itemIds, itemId => {
                this.libraryAdd(itemId, atStart);
            });
        },
        libraryAdd: function(itemId, atStart) {
            if (includes(this.itemIds, itemId)) {
                return;
            }
            if (atStart) {
                this.itemIds.unshift(itemId);
            } else {
                this.itemIds.push(itemId);
            }
        },
        handleSearch: function() {
            var searchString = this.$refs.searchInput.value;
            this.getDebounce(this.category, searchString, 1, (searchedId, itemIds, hasMore) => {
                if (this.searchId != searchedId) {
                    return;
                }
                this.searchId = null;
                this.searchString = searchString;
                this.page = 1;
                this.hasMore = hasMore;
                this.libraryClear();
                this.libraryAddMany(itemIds);
            });
        },
        handleScroll: function(e) {
            var ele = e.target;
            var scrollProportion = ((ele.scrollTop + ele.offsetHeight) / ele.scrollHeight);
            if (this.hasMore && !this.searchId && scrollProportion > 0.9) {
                var page = this.page + 1;
                this.searchId = this.$lcfStore.searchMediaLibrary(this.category, this.searchString, page, (searchedId, itemIds, hasMore) => {
                    if (this.searchId != searchedId) {
                        return;
                    }
                    this.searchId = null;
                    this.page = page;
                    this.hasMore = hasMore;
                    this.libraryAddMany(itemIds);
                });
            }
        },
        dragIn: function() {
            this.hasDragObj = true;
        },
        dragOut: function() {
            this.hasDragObj = false;
        },
        dragDrop: function(e) {
            this.hasDragObj = false;
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                this.upload(e.dataTransfer.files);
            } else {
                debugger;
            }
        },
        filesSelected: function() {
            this.upload(this.$refs.fileInput.files);
        },
        upload: function(files) {
            for (var file of files) {
                this.uploads.unshift({
                    file: file,
                    status: 'new',
                    progress: 0,
                    error: null,
                });
            }
            this.next();
            this.libraryMode();
        },
        next: function() {
            if (this.uploading) {
                return;
            }
            var nextUpload = false;
            for (var i = this.uploads.length - 1; i >= 0; i--) {
                if (this.uploads[i].status == 'new') {
                    nextUpload = this.uploads[i];
                    break;
                }
            }
            if (!nextUpload) {
                return;
            }
            this.uploading = true;
            nextUpload.status = 'active';
            nextUpload.progress = 0;
            var formData = new FormData();
            formData.append('file', nextUpload.file);
            if (this.category) {
                formData.append('category', this.category);
            }
            this.$lcfStore.uploadToMediaLibrary(formData, (progress) => {
                nextUpload.progress = progress;
            }, (itemId) => {
                var index = this.uploads.indexOf(nextUpload);
                this.uploads.splice(index, 1);
                this.libraryAdd(itemId, true);
                this.uploading = false;
                this.next();
            }, (errorMsg) => {
                nextUpload.status = 'error';
                nextUpload.error = errorMsg;
                this.uploading = false;
                this.next();
            });
        },
        select(e) {
            if (e.item != null) {
                if (this.standalone) {
                    this.mode = 'inspect';
                    this.selectedItemId = e.item.id;
                } else {
                    this.$emit('select', {item: e.item});
                }
            }
        },
        deleteSelectedItem: function() {
            if (this.selectedItem) {
                if (confirm("Are you sure you wish to delete this media item - this cannot be undone!")) {
                    this.$lcfStore.deleteMediaItem(this.selectedItemId, () => {
                        var index = this.itemIds.indexOf(this.selectedItemId);
                        this.itemIds.splice(index, 1);
                        this.selectedItemId = null;
                        this.libraryMode();
                    });
                }
            }
        },
        cancel: function() {
            this.$emit('close');
        }
    }
};
</script>
