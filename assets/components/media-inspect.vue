<template>
    <div class="lcf-ml-inspect">
        <div>
            <img v-if="hasImage" :src="item.url" />
            <p v-if="iconClass"><i :class="iconClass"></i></p>
            <p v-if="! hasImage">Media Type: {{ item.category }}</p>
            <p>URL: {{ item.url }}</p>
        </div>
        <div>
            <div>
                <label>Title</label>
                <input ref="titleInput" type="text" :disabled="! editable" :value="displayTitle" @change="changeTitle" @keyup="changeTitle" />
            </div>
            <div>
                <label>{{ isImage ? 'Alt' : 'Subtitle' }}</label>
                <input ref="altInput" type="text" :disabled="! editable" :value="displayAlt" @change="changeAlt" @keyup="changeAlt" />
            </div>
            <button v-if="editable && edited" type="button" @click.stop="saveUpdates">Save</button>
            <hr />
            <button type="button" @click.stop="close">Close</button>
            <button v-if="deletable" type="button" @click.stop="deleteItem">Delete</button>
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
            editedTitle: null,
            editedAlt: null
        };
    },
    computed: {
        title: function() {
            return get(this.item, 'title');
        },
        alt: function() {
            return get(this.item, 'alt');
        },
        displayTitle: function() {
            return (this.editedTitle == null ? this.title : this.editedTitle);
        },
        displayAlt: function() {
            return (this.editedAlt == null ? this.alt : this.editedAlt);
        },
        edited: function() {
            return (this.editedTitle != this.title) || (this.editedAlt != this.alt);
        },
        isImage: function() {
            return this.item.category == 'Image';
        },
        hasImage: function() {
            return this.isImage && this.item.url;
        },
        iconClass: function() {
            return Util.iconClassForMediaItem(this.item);
        }
    },
    methods: {
        changeTitle: function() {
            this.editedTitle = this.$refs.titleInput.value;
        },
        changeAlt: function() {
            this.editedAlt = this.$refs.altInput.value;
        },
        close: function() {
            this.$emit('close');
        },
        deleteItem: function() {
            this.$emit('deleteItem');
        },
        saveUpdates: function() {
            this.$lcfStore.updateMediaItem(this.item.id, {title: this.displayTitle, alt: this.displayAlt}, () => {
                this.editedTitle = null;
                this.editedAlt = null;
            }, (errorMsg) => {
                alert(errorMsg);
            });
        }
    }
};
</script>
