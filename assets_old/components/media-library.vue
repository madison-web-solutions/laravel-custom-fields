<template>
    <div class="lcf-media-library" :class="cssClass" @dragover.prevent="dragIn" @dragenter.prevent="dragIn" @dragleave.prevent="dragOut" @dragend.prevent="dragOut" @drop.prevent="dragDrop">
        <div v-if="! showInspect" class="lcf-ml-tabs">
            <button type="button" :class="{active: mode == 'library'}" @click.prevent="libraryMode">Library</button>
            <button type="button" :class="{active: mode == 'upload'}" @click.prevent="uploadMode">Upload</button>
            <button v-if="! standalone" type="button" @click="cancel">Cancel</button>
        </div>
        <div class="lcf-ml-panel" v-if="showLibrary" @scroll="handleScroll">
            <p><input type="search" placeholder="search" ref="searchInput" @input="handleSearch" @keydown.enter.prevent="handleSearch" /></p>
            <p v-if="! standalone" >Click to select:</p>
            <div class="lcf-ml-index">
                <template v-for="upload in uploads">
                    <div class="lcf-ml-preview" :class="'lcf-ml-'+upload.status" v-if="upload.status != 'done'">
                        <p v-if="upload.status == 'new'">Queued</p>
                        <p v-if="upload.status == 'active'">{{ upload.progress }}%</p>
                        <p v-if="upload.status == 'error'">Error: {{ upload.error }}</p>
                    </div>
                </template>
                <media-preview v-for="item in library" :key="item.id" :item="item" @select="select" />
            </div>
        </div>
        <div class="lcf-ml-panel" v-if="showUpload">
            <div class="lcf-ml-upload-form">
                <label :for="fileInputId">Choose a file</label>
                <input ref="fileInput" type="file" :id="fileInputId" class="file-input" multiple @change="filesSelected" />
                <p>... or drag and drop into this box</p>
            </div>
        </div>
        <div class="lcf-ml-panel" v-if="showInspect">
            <media-inspect :item="selectedItem" :selectable="! standalone" :editable="true" :deletable="true" @close="libraryMode" @selectItem="selectSelectedItem" @deleteItem="deleteSelectedItem" @updated="selectedItemUpdated" />
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import _ from 'lodash';

// Helper object for fetching media info from the server
var getter = {
    _counter: 1,
    _getting: false,
};
getter.get = function(category, search, page, callback) {
    // Save a unique id for each call to get()
    // The idea of this is that if get() is called while a previous get() is still running,
    // then the second call can overrule the first one, and we'll only run the second callback
    var id = getter._counter++;
    console.log('get '+id, category, search, page);
    getter._getting = id;
    axios.get('/lcf/media-library', {params: {
        category: category,
        search: search,
        page: page
    }}).then(response => {
        if (getter._getting == id) {
            callback(response.data.data);
            getter._getting = false;
        }
    });
};
// Debounced version of the get function
getter.getDebounce = _.debounce(getter.get, 300);
// Function for finding out whether any items are currently being fetched
getter.isGetting = function() {
    return (getter._getting !== false);
};

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
            page: 0,
            hasMore: true,
            library: [],
            selectedItemId: null,
        }
    },
    computed: {
        cssClass: function() {
            return {
                'has-drag-obj': this.hasDragObj
            }
        },
        selectedItem: function() {
            return _.find(this.library, item => item.id == this.selectedItemId);
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
            this.library = [];
        },
        libraryAddMany: function(items, atStart) {
            _.forEach(items, item => {
                this.libraryAdd(item, atStart);
            });
        },
        libraryAdd: function(item, atStart) {
            var index = _.findIndex(this.library, existingItem => existingItem.id == item.id);
            if (index == -1) {
                if (atStart) {
                    this.library.unshift(item);
                } else {
                    this.library.push(item);
                }
            } else {
                this.library.splice(index, 1, item);
            }
        },
        handleSearch: function() {
            var searchString = this.$refs.searchInput.value;
            getter.getDebounce(this.category, this.$refs.searchInput.value, 0, (items) => {
                this.searchString = searchString;
                this.page = 0;
                this.hasMore = (items.length == 50);
                this.libraryClear();
                this.libraryAddMany(items);
            });
        },
        handleScroll: function(e) {
            var ele = e.target;
            var scrollProportion = ((ele.scrollTop + ele.offsetHeight) / ele.scrollHeight);
            if (this.hasMore && !getter.isGetting() && scrollProportion > 0.9) {
                var page = this.page + 1;
                getter.get(this.category, this.searchString, page, (items) => {
                    this.page = page;
                    this.hasMore = (items.length == 50);
                    this.libraryAddMany(items);
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

            axios.post('/lcf/media-library', formData, {
                onUploadProgress: function(e) {
                    nextUpload.progress = Math.round(100 * e.loaded / e.total);
                }
            }).then(response => {
                console.log(response);
                if (response.data.ok) {
                    this.setUploadDone(nextUpload, response.data.item);
                } else {
                    this.setUploadError(nextUpload, response.data.error);
                }
                this.uploading = false;
                this.next();
            }).catch(err => {
                var msg = 'upload error';
                if (err.response.status == 413) {
                    msg = 'File too large';
                }
                this.setUploadError(nextUpload, msg);
                this.uploading = false;
                this.next();
            });
        },
        setUploadDone(upload, item) {
            var index = this.uploads.indexOf(upload);
            this.uploads.splice(index, 1);
            this.libraryAdd(item, true);
        },
        setUploadError(upload, error) {
            upload.status = 'error';
            upload.error = error;
        },
        select(e) {
            if (e.item != null) {
                this.mode = 'inspect';
                this.selectedItemId = e.item.id;
            }
        },
        selectSelectedItem: function() {
            if (this.selectedItem) {
                this.$emit('select', {item: this.selectedItem});
            }
        },
        deleteSelectedItem: function() {
            if (this.selectedItem) {
                if (confirm("Are you sure you wish to delete this media item - this cannot be undone!")) {
                    axios.post('/lcf/media-library/delete', {item_id: this.selectedItemId}).then(response => {
                        var index = _.findIndex(this.library, item => item.id == this.selectedItemId);
                        this.library.splice(index, 1);
                        this.selectedItemId = null;
                        this.libraryMode();
                    });
                }
            }
        },
        selectedItemUpdated: function(newItem) {
            this.libraryAdd(newItem);
        },
        cancel: function() {
            this.$emit('close');
        }
    },
    created: function() {
        getter.get(this.category, '', 0, (items) => {
            this.libraryAddMany(items);
            this.hasMore = (items.length == 50);
        });
    }
};
</script>
