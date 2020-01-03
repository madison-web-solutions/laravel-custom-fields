<template>
    <div class="lcf-media-library" :class="cssClass" @dragover.prevent="dragIn" @dragenter.prevent="dragIn" @dragleave.prevent="dragOut" @dragend.prevent="dragOut" @drop.prevent="dragDrop">
        <div v-if="! showInspect">
            <button type="button" class="lcf-ml-tab" :class="{active: mode == 'library'}" @click.prevent="libraryMode">Library</button>
            <button type="button" class="lcf-ml-tab" :class="{active: mode == 'upload'}" @click.prevent="uploadMode">Upload</button>
            <button v-if="! standalone" type="button" class="lcf-ml-tab" @click="cancel">Cancel</button>
        </div>
        <div v-if="showLibrary" class="lcf-panel">
            <div class="lcf-ml-search">
                <lcf-input-wrapper class="lcf-input">
                    <input ref="searchInput" type="search" placeholder="search" @input="handleSearch" @keydown.enter.prevent="handleSearch" />
                </lcf-input-wrapper>
                <lcf-select-input :value="selectedFolderId" :choices="folderChoices" placeholder="All Folders" @change="selectFolder"></lcf-select-input>
                <button class="lcf-btn" :disabled="! selectedFolderId" @click="selectFolder({value: parentFolderId})" title="To Parent Folder"><i class="fas fa-arrow-up"></i></button>
                <button class="lcf-btn" :disabled="! selectedFolderId" @click="editFolder" title="Edit Folder"><i class="fas fa-edit"></i></button>
                <button class="lcf-btn" @click="newFolder" title="New Folder"><i class="fas fa-folder-plus"></i></button>
            </div>

            <p v-if="! standalone" >Click to select:</p>
            <div class="lcf-ml-index" ref="libraryIndex" @scroll="handleScroll">
                <template v-for="upload in uploads">
                    <div class="lcf-ml-preview" :class="'lcf-ml-'+upload.status" v-if="upload.status != 'done'">
                        <p v-if="upload.status == 'new'">Queued</p>
                        <p v-if="upload.status == 'active'">{{ upload.progress }}%</p>
                        <p v-if="upload.status == 'error'">Error: {{ upload.error }}</p>
                    </div>
                </template>
                <template v-for="subfolder in subfolders">
                    <div class="lcf-ml-preview lcf-ml-subfolder" @click="selectFolder({value: subfolder.id})">
                        <div class="lcf-ml-thumb">
                            <i class="lcf-ml-preview-icon far fa-folder"></i>
                        </div>
                        <div class="lcf-ml-preview-title">{{ subfolder.name }}</div>
                    </div>
                </template>
                <lcf-media-preview v-for="item in items" :key="item.id" :item="item" @select="select" />
                <p v-if="searchId">Loading more...</p>
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
        <div class="lcf-panel" v-if="mode == 'new-folder'">
            <lcf-media-folder-edit :folderId="null" :suggestedParentId="selectedFolderId" @cancel="libraryMode" @updated="libraryMode" />
        </div>
        <div class="lcf-panel" v-if="mode == 'edit-folder'">
            <lcf-media-folder-edit :folderId="selectedFolderId" @cancel="libraryMode" @updated="libraryMode" @deleted="folderDeleted" />
        </div>
    </div>
</template>

<script>
import { get, includes, debounce, forEach, filter, find, map } from 'lodash-es';
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
            libraryScrollPos: 0,
            selectedFolderId: null,
            editFolderId: null,
        }
    },
    created: function() {
        this.getDebounce = debounce((category, searchString, folder, page, callback) => {
            this.searchId = this.$lcfStore.searchMediaLibrary(category, searchString, folder, page, callback);
        }, 300);

        this.searchId = this.$lcfStore.searchMediaLibrary(this.category, '', null, 1, (searchedId, itemIds, hasMore) => {
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
        },
        selectedFolder: function() {
            return find(this.folders, folder => folder.id == this.selectedFolderId);
        },
        subfolders: function() {
            return filter(this.folders, folder => folder.parent_id == this.selectedFolderId);
        },
        parentFolder: function() {
            if (! this.selectedFolder) {
                return null;
            }
            return find(this.folders, folder => folder.id == this.selectedFolder.parent_id);
        },
        parentFolderId: function() {
            return this.parentFolder ? this.parentFolder.id : null;
        }
    },
    methods: {
        libraryMode: function() {
            this.mode = 'library';
            this.selectedItemId = null;
            // scroll to previous position
            window.setTimeout(() => {
                this.$refs.libraryIndex.scrollTop = this.libraryScrollPos;
            }, 10);
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
        selectFolder: function(e) {
            this.selectedFolderId = e.value;
            this.handleSearch();
        },
        handleSearch: function() {
            var searchString = this.$refs.searchInput.value;
            this.getDebounce(this.category, searchString, this.selectedFolderId, 1, (searchedId, itemIds, hasMore) => {
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
        handleScroll: function() {
            var ele = this.$refs.libraryIndex;
            this.libraryScrollPos = ele.scrollTop;
            var scrollProportion = ((ele.scrollTop + ele.offsetHeight) / ele.scrollHeight);
            if (this.hasMore && !this.searchId && scrollProportion > 0.9) {
                var page = this.page + 1;
                this.searchId = this.$lcfStore.searchMediaLibrary(this.category, this.searchString, this.selectedFolderId, page, (searchedId, itemIds, hasMore) => {
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
                    autoSelect: (! this.standalone && files.length == 1),
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
                if (this.autoSelectId) {

                }
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
                if (nextUpload.autoSelect) {
                    this.$emit('select', {item: this.$lcfStore.getMediaItem(itemId)});
                }
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
        editFolder: function() {
            this.editFolderId = this.selectedFolderId;
            this.mode = 'edit-folder';
        },
        newFolder: function() {
            this.mode = 'new-folder'
        },
        folderDeleted: function(e) {
            this.libraryMode();
            window.setTimeout(() => {
                this.selectFolder({value: e.reassign_id});
            }, 10);
        },
        cancel: function() {
            this.$emit('close');
        }
    }
};
</script>
