<template>
    <div class="lcf-ml-preview" @click="click">
        <div class="lcf-ml-thumb" :style="style" />
        <span>{{ item.title }}</span>
        <button v-if="deletable" type="button" data-name="remove" @click.stop="deleteItem">Delete</button>
    </div>
</template>

<script>
export default {
    props: {
        item: {
            required: true
        },
        deletable: {
            required: false,
            default: false
        }
    },
    computed: {
        style: function() {
            var style = {};
            if (this.item.thumb) {
                style.backgroundImage = 'url('+this.item.thumb+')';
            }
            return style;
        }
    },
    methods: {
        click: function() {
            this.$emit('select', {item: this.item});
        },
        deleteItem: function() {
            this.$emit('deleteItem', {item: this.item});
        }
    }
};
</script>
