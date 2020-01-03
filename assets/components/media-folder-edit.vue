<template>
    <div>
        <h3 v-if="folderId">Edit Folder: {{ loading ? folderId : folder.name }}</h3>
        <h3 v-if="! folderId">New Folder</h3>
        <form v-if="! loading" class="lcf-form" ref="editForm">
            <lcf-field-wrapper v-for="field, fieldName in this.editFields" :key="fieldName" :groupName="fieldGroupName" :fieldName="fieldName" :field="field" :initialValue="folder[fieldName]" />
            <div class="lcf-btn-group">
                <button class="lcf-btn" @click="cancelEdit" type="button">Cancel</button>
                <button class="lcf-btn-primary" @click="updateOrCreateFolder" type="button">{{ folder.id ? 'Update' : 'Save' }} Folder</button>
            </div>
        </form>
        <div v-if="! loading && folder.id" style="margin-top: 40px;">
            <h5>Delete Folder: {{ loading ? folderId : folder.name }}</h5>
            <form class="lcf-form" ref="deleteForm">
                <lcf-field-wrapper v-for="field, fieldName in this.deleteFields" :key="fieldName" :groupName="deleteFieldGroupName" :fieldName="fieldName" :field="field" :initialValue="folder[fieldName]" />
                <div class="lcf-btn-group">
                    <button class="lcf-btn-danger" @click="deleteFolder" type="button">Delete Folder</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { cloneDeep } from 'lodash-es';
export default {
    props: ['folderId', 'suggestedParentId'],
    data: function() {
        return {
            loading: true,
            editFields: {},
            deleteFields: {},
            reassignIdField: {},
            folder: {},
            fieldGroupName: Math.random().toString(16).substring(2),
        }
    },
    mounted: function() {
        axios.get('/lcf/media-library/folders/' + (this.folderId ? this.folderId : 'new'), {}).then(response => {
            this.folder = response.data.folder;
            if (! this.folderId && this.suggestedParentId) {
                this.folder.parent_id = this.suggestedParentId;
            }
            this.editFields = response.data.edit_fields;
            this.deleteFields = response.data.delete_fields;
            this.loading = false;
        }, error => {
            console.log(error);
        });
    },
    computed: {
        deleteFieldGroupName: function() {
            return this.fieldGroupName + '_delete';
        }
    },
    methods: {
        cancelEdit: function() {
            this.$emit('cancel');
        },
        updateOrCreateFolder: function() {
            var data = new FormData(this.$refs.editForm);
            axios.post('/lcf/media-library/folders/' + (this.folderId ? this.folderId : 'new'), data).then(response => {
                this.$lcfStore.updateMediaLibraryFolders(response.data.folders);
                this.$emit('updated');
            }, error => {
                this.$lcfStore.setErrors(this.fieldGroupName, response.data.errors);
            });
        },
        deleteFolder: function() {
            if (! this.folderId || ! confirm("Are you sure you wish to delete this media folder - this cannot be undone!")) {
                return;
            }
            var data = new FormData(this.$refs.deleteForm);
            axios.post('/lcf/media-library/folders/' + this.folderId + '/delete', data).then(response => {
                this.$lcfStore.updateMediaLibraryFolders(response.data.folders);
                this.$emit('deleted', {reassign_id: data.get('reassign_id')});
            }, error => {
                this.$lcfStore.setErrors(this.deleteFieldGroupName, response.data.errors);
            });
        }
    }
};
</script>
