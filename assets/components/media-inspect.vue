<template>
    <div class="lcf-ml-inspect">
        <div>
            <img v-if="isImage" :src="item.url" />
            <p v-if="! isImage"><strong>Media Type:</strong><br />{{ item.category }}</p>
        </div>
        <div>
            <div>
                <label>Title</label>
                <input type="text" name="title" :disabled="! editable" :value="vals.title" @change="change" @keyup="change" />
            </div>
            <div>
                <label>{{ isImage ? 'Alt' : 'Subtitle' }}</label>
                <input type="text" name="alt" :disabled="! editable" :value="vals.alt" @change="change" @keyup="change" />
            </div>
            <button v-if="editable && edited" data-name="save" type="button" @click.stop="saveUpdates">Save</button>
            <hr />
            <button type="button" data-name="close" @click.stop="close">Close</button>
            <button v-if="selectable" data-name="select" type="button" @click.stop="selectItem">Select</button>
            <button v-if="deletable" data-name="delete" type="button" @click.stop="deleteItem">Delete</button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    props: {
        item: {
            required: true
        },
        selectable: {
            required: false,
            default: false
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
                title: '',
                alt: ''
            }
        };
    },
    mounted: function() {
        this.setVals();
    },
    computed: {
        edited: function() {
            return this.vals.title != this.item.title || this.vals.alt != this.item.alt;
        },
        isImage: function() {
            return this.item.category == 'Image';
        }
    },
    methods: {
        setVals: function() {
            this.vals.title = this.item.title;
            this.vals.alt = this.item.alt;
        },
        change: function(e) {
            this.vals[e.target.name] = e.target.value;
        },
        close: function() {
            this.$emit('close');
        },
        selectItem: function() {
            this.$emit('selectItem');
        },
        deleteItem: function() {
            this.$emit('deleteItem');
        },
        saveUpdates: function() {
            var data = {
                item_id: this.item.id,
                title: this.vals.title,
                alt: this.vals.alt
            };
            axios.post('/lcf/media-library/update', data).then(response => {
                this.$emit('updated', response.data.item);
            }, error => {
                if (error.response.data && error.response.data.errors) {
                    alert('Error: ' + JSON.stringify(error.response.data.errors));
                } else {
                    alert('Server error');
                }
            });
        }
    }
};
</script>
