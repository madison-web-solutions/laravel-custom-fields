<template>
    <div class="lcf-media-library" :class="cssClass" @dragover.prevent="dragIn" @dragenter.prevent="dragIn" @dragleave.prevent="dragOut" @dragend.prevent="dragOut" @drop.prevent="dragDrop">
        <div class="lcf-ml-tabs">
            <button type="button" :class="{active: mode == 'library'}" @click.prevent="libraryMode">Library</button>
            <button type="button" :class="{active: mode == 'upload'}" @click.prevent="uploadMode">Upload</button>
            <button type="button" @click="cancel">Cancel</button>
        </div>
        <div class="lcf-ml-panel" v-if="showLibrary">
            <p>Click to select:</p>
            <div class="lcf-ml-index">
                <template v-for="upload in uploads">
                    <div class="lcf-ml-preview" :class="'lcf-ml-'+upload.status" v-if="upload.status != 'done'">
                        <p v-if="upload.status == 'new'">Queued</p>
                        <p v-if="upload.status == 'active'">{{ upload.progress }}%</p>
                        <p v-if="upload.status == 'error'">Error: {{ upload.error }}</p>
                    </div>
                    <media-preview v-if="upload.status == 'done'" :key="upload.item.id" :item="upload.item" @select="select" />
                </template>
                <media-preview v-for="item in library" :key="item.id" :item="item" :deletable="true" @select="select" @deleteItem="deleteItem" />
            </div>
        </div>
        <div class="lcf-ml-panel" v-if="showUpload">
            <div class="lcf-ml-upload-form">
                <label :for="fileInputId">Choose a file</label>
                <input ref="fileInput" type="file" :id="fileInputId" class="file-input" multiple @change="filesSelected" />
                <p>... or drag and drop into this box</p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    data: function() {
        return {
            mode: 'library',
            fileInputId: Math.random().toString(36).substring(2),
            hasDragObj: false,
            uploads: [],
            uploading: false,
            library: [],
        }
    },
    computed: {
        cssClass: function() {
            return {
                'has-drag-obj': this.hasDragObj
            }
        },
        showUpload: function() {
            return this.hasDragObj || this.mode == 'upload';
        },
        showLibrary: function() {
            return ! this.showUpload;
        }
    },
    methods: {
        libraryMode: function() {
            this.mode = 'library';
        },
        uploadMode: function() {
            this.mode = 'upload';
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
                    item: null
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
                console.log(err);
                this.setUploadError(nextUpload, 'upload error');
                this.uploading = false;
                this.next();
            });
        },
        setUploadDone(upload, item) {
            upload.status = 'done';
            upload.progress = 100;
            upload.item = item;
        },
        setUploadError(upload, error) {
            upload.status = 'error';
            upload.error = error;
        },
        select(e) {
            if (e.item != null) {
                this.$emit('select', e);
            }
        },
        deleteItem: function(e) {
            if (e.item != null) {
                if (confirm("Are you sure you wish to delete this media item - this cannot be undone!")) {
                    axios.post('/lcf/media-library/delete', {item_id: e.item.id}).then(response => {
                        var index = this.library.indexOf(e.item);
                        this.library.splice(index, 1);
                    });
                }
            }
        },
        cancel: function() {
            this.$emit('close');
        }
    },
    created: function() {
        axios.get('/lcf/media-library').then(response => {
            this.library = response.data.data;
        });
    }
};
</script>
