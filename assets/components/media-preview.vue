<template>
    <div class="lcf-ml-preview" @click="click">
        <div class="lcf-ml-thumb" :style="style">
            <i v-if="iconClass" :class="iconClass"></i>
        </div>
        <span>{{ item.title }}</span>
    </div>
</template>

<script>

/*
case 'svg':
    $this->label = 'SVG Image';
    $this->mimeType = 'image/svg+xml';
    $this->category = 'Image';
    $this->sizable = false;
    break;
*/

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
            if (! this.item.url) {
                return 'warning fas fa-exclamation-triangle';
            }
            if (this.backgroundImage) {
                return null;
            }
            switch (this.item.extension) {
                case 'pdf':
                    return 'fas fa-file-pdf';
                case 'doc':
                case 'docx':
                    return 'fas fa-file-word';
                case 'xls':
                case 'xlsx':
                    return 'fas fa-file-excel';
                case 'csv':
                    return 'fas fa-file-csv';
                case 'ppt':
                case 'pptx':
                    return 'fas fa-file-powerpoint';
                default:
                    switch (this.item.Category) {
                        case 'Document':
                            return 'fas fa-file-alt';
                        case 'Image':
                            return 'fas-file-image';
                        default:
                            return 'fas fa-file';
                    }
            }
        }
    },
    methods: {
        click: function() {
            this.$emit('select', {item: this.item});
        }
    }
};
</script>
