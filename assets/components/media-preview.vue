<template>
    <div class="lcf-ml-preview" @click="click">
        <div class="lcf-ml-thumb" :style="style">
            <i class="lcf-ml-preview-icon" v-if="iconClass" :class="iconClass"></i>
        </div>
        <div class="lcf-ml-preview-id">{{ item.id }}</div>
        <div class="lcf-ml-preview-title">{{ item.title }}</div>
    </div>
</template>

<script>
import Util from '../util.js';
export default {
    props: {
        item: {
            required: true
        }
    },
    computed: {
        style: function() {
            var style = {};
            if (this.backgroundImage) {
                style.backgroundImage = 'url('+this.backgroundImage+')';
            }
            return style;
        },
        backgroundImage: function() {
            if (this.item.thumb) {
                return this.item.thumb;
            } else if (this.item.extension == 'svg' && this.item.url) {
                return this.item.url;
            } else {
                return null;
            }
        },
        iconClass: function() {
            return Util.iconClassForMediaItem(this.item);
        }
    },
    methods: {
        click: function() {
            this.$emit('select', {item: this.item});
        }
    }
};
</script>
