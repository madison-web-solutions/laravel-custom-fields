<template>
    <div class="lcf-ml-preview" @click="click">
        <div class="lcf-ml-thumb" :style="style">
            <p v-if="! isImage" class="lcf-ml-thumb-alt"><strong>Media Type:</strong><br />{{ item.category }}</p>
        </div>
        <span>{{ item.title }}</span>
    </div>
</template>

<script>
export default {
    props: {
        item: {
            required: true
        }
    },
    computed: {
        style: function() {
            var style = {};
            if (this.item.thumb) {
                style.backgroundImage = 'url('+this.item.thumb+')';
            }
            return style;
        },
        isImage: function() {
            return this.item.category == 'Image';
        }
    },
    methods: {
        click: function() {
            this.$emit('select', {item: this.item});
        }
    }
};
</script>
