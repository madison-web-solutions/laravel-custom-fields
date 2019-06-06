
var iconClassForMediaItem = function(item) {
    if (! item) {
        return null;
    }
    if (! item.url) {
        return 'warning fas fa-exclamation-triangle';
    }
    if (item.thumb || item.extension == 'svg') {
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
};



export default {
    iconClassForMediaItem
};
