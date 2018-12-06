<template>
    <div ref="container" class="lcf-repeater-field">
        <div class="lcf-repeater-item" v-for="childId, index in childNodeIds" :key="childId" :draggable="draggingIndex !== false" @dragstart="dragStart">
            <div class="lcf-field-controls">
                <button v-if="! isFull" type="button" @click="insert(index)" class="lcf-btn-icon" data-name="insert" aria-label="Insert section" title="Insert section">
                    <i class="fas fa-plus-circle"></i>
                </button>
                <button type="button" @click="remove(index)" class="lcf-btn-icon" data-name="remove" aria-label="Remove section" title="Remove section">
                    <i class="fas fa-minus-circle"></i>
                </button>
                <div class="lcf-repeater-index">
                    <span class="lcf-repeater-move-handle" @mousedown="setDragging(index)" title="Move section" aria-label="Move section">
                        <i class="fas fa-arrows-alt"></i>
                    </span>
                    <label :title="'Position on page: ' + (index + 1)">{{ (index + 1) }}</label>
                    <button type="button" :disabled="index == 0" @click="moveUp(index)" class="lcf-btn-icon" data-name="move-up" aria-label="Move up" title="Move up">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <button type="button" :disabled="index == lastIndex" @click="moveDown(index)" class="lcf-btn-icon" data-name="move-down" aria-label="Move down" title="Move down">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <field :path="path.concat(index)" :field="field.options.sub_field" :errors="errors" />
        </div>
        <div ref="insertMarker" class="lcf-repeater-insert-marker"></div>
        <button v-if="! isFull" type="button" @click="append" class="btn btn-icon-charcoal" data-name="append" aria-label="append" title="append">{{ appendLabel }}</button>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
var counter = 0;
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        return {
            draggingIndex: false,
        };
    },
    created: function() {
        this.draggingItem = false;
        this.fillUp();
    },
    computed: {
        minLength: function() {
            return _.get(this.field, 'options.min', null);
        },
        maxLength: function() {
            return _.get(this.field, 'options.max', null);
        },
        isFull: function() {
            return (this.maxLength != null) && (this.length >= this.maxLength);
        },
        needsMore: function() {
            return (this.minLength != null) && (this.length < this.minLength);
        },
        lastIndex: function() {
            return this.length - 1;
        },
        appendLabel: function() {
            return _.get(this.field, 'options.append_label', 'Add');
        }
    },
    methods: {
        move: function(from, to) {
            this.$store.commit('arrayMove', {path: this.pathStr, from: from, to: to});
        },
        moveUp: function(index) {
            this.move(index, index - 1);
        },
        moveDown: function(index) {
            this.move(index, index + 1);
        },
        remove: function(index) {
            this.$store.commit('arrayDelete', {path: this.pathStr, index: index});
            this.fillUp();
        },
        insert: function(index) {
            this.$store.commit('arrayInsert', {path: this.pathStr, index: index});
        },
        append: function() {
            this.$store.commit('arrayInsert', {path: this.pathStr, index: this.length});
        },
        fillUp: function() {
            // If there's a minimum length, then append (blank) children until it is satisfied
            while (this.needsMore) {
                this.append();
            }
        },
        getItems: function(excluding) {
            return _.filter(this.$refs.container.childNodes, (ele) => {
                return (ele !== excluding) && (ele.nodeType == 1) && ele.classList.contains('lcf-repeater-item');
            });
        },
        getClosest: function(pageY) {
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            var midpoints = _.map(this.getItems(this.draggingItem), (ele) => {
                var rect = ele.getBoundingClientRect();
                return scrollTop + rect.top + 0.5 * rect.height;
            });
            for (var i = 0; i < midpoints.length; i++) {
                if (pageY < midpoints[i]) {
                    return i;
                }
            }
            return midpoints.length;
        },
        showInsertPosition: function(newIndex) {
            var marker = this.$refs.insertMarker;
            if (newIndex === false) {
                marker.classList.remove('active');
            } else {
                var items = this.getItems(this.draggingItem);
                if (newIndex === items.length) {
                    var lastItem = items[items.length - 1];
                    var pos = lastItem.offsetTop + lastItem.offsetHeight;
                } else {
                    var item = items[newIndex];
                    var pos = item.offsetTop;
                }
                marker.classList.add('active');
                marker.style.top = (pos + 'px');
            }
        },
        setDragging: function(index) {
            this.draggingIndex = index;
        },
        dragStart: function(e) {
            if (this.draggingIndex !== false) {
                this.draggingItem = e.target;
                document.addEventListener('dragover', this.dragOver);
                document.addEventListener('dragend', this.dragEnd);
            }
        },
        dragEnd: function(e) {
            var newIndex = this.getClosest(e.pageY);
            this.showInsertPosition(false);
            this.move(this.draggingIndex, newIndex);
            this.draggingIndex = false;
            this.draggingItem = false;
            document.removeEventListener('dragover', this.dragOver);
            document.removeEventListener('dragend', this.dragEnd);
        },
        dragOver: function(e) {
            var newIndex = this.getClosest(e.pageY);
            this.showInsertPosition(newIndex);
        }
    }
};
</script>
