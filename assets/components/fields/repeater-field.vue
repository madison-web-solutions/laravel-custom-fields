<template>
    <div class="lcf-field lcf-repeater-field">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <div class="lcf-input lcf-repeater-input" :class="{'lcf-has-error': hasError}">
                <div v-for="nodeId, index in childIds" :class="{'lcf-drag-hide': (draggingStartedIndex === index)}" class="lcf-repeater-item" :data-index="index" :draggable="draggableIndex === index" @dragstart="dragStart">
                    <div class="lcf-repeater-index" @mousedown="setDraggableIndex(index)">
                        <span>{{ index + 1 }}</span>
                    </div>
                    <div class="lcf-repeater-subfield">
                        <component :is="subField.fieldComponent" :key="nodeId" :path="path.concat(index)" :field="subField" />
                    </div>
                    <div class="lcf-repeater-item-controls">
                        <button v-if="canAdd" type="button" @click="insert(index)" class="lcf-btn-icon" data-name="btn-insert" aria-label="Add row">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                        <button type="button" @click="moveUp(index)" class="lcf-btn-icon" aria-label="Move up">
                            <i class="fas fa-caret-up"></i>
                        </button>
                        <button type="button" @click="remove(index)" class="lcf-btn-icon lcf-danger" aria-label="Remove row">
                            <i class="fas fa-minus-circle"></i>
                        </button>
                        <button type="button" @click="moveDown(index)" class="lcf-btn-icon" aria-label="Move down">
                            <i class="fas fa-caret-down"></i>
                        </button>
                    </div>
                </div>
                <div v-if="canAdd" class="lcf-repeater-append">
                    <button type="button" class="lcf-btn" @click="insert(length)"><i class="fas fa-plus-circle"></i> Add row</button>
                </div>
                <div ref="insertMarker" class="lcf-repeater-insert-marker" :style="insertMarkerStyle"></div>
            </div>
        </lcf-input-wrapper>
    </div>
</template>

<script>
import { get, keys, map } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    data: function() {
        return {
            // index of the item whose move handle has been clicked on (and can therefore be dragged)
            draggableIndex: false,
            // index of the item for which dragging has started
            draggingStartedIndex: false,
            // potential new position of the dragging item, if the user releases the mouse
            dropIndex: false
        };
    },
    created: function() {
        this.fillUp();
    },
    computed: {
        subField: function() {
            return get(this.field, 'settings.sub_field');
        },
        length: function() {
            return this.childIds ? this.childIds.length: 0;
        },
        min: function() {
            return get(this.field, 'settings.min');
        },
        max: function() {
            return get(this.field, 'settings.max');
        },
        canAdd: function() {
            return this.max == null || this.length < this.max;
        },
        insertMarkerStyle: function() {
            if (this.dropIndex === false) {
                return {display: 'none'};
            }
            var otherItems = this.$el.querySelectorAll('.lcf-repeater-item[draggable=false]');
            if (this.dropIndex === otherItems.length) {
                var lastItem = otherItems[otherItems.length - 1];
                var pos = lastItem.offsetTop + lastItem.offsetHeight;
            } else {
                var insertBeforeItem = otherItems[this.dropIndex];
                var pos = insertBeforeItem.offsetTop;
            }
            return {
                display: 'block',
                top: pos + 'px'
            };
        },
    },
    methods: {
        fillUp() {
            // If there's a minimum length, then append (blank) children until it is satisfied
            if (this.min != null) {
                while(this.length < this.min) {
                    this.insert(this.length);
                }
            }
        },
        insert: function(index) {
            this.$lcfStore.arrayInsert(this.pathStr, index);
        },
        remove: function(index) {
            this.$lcfStore.arrayDelete(this.pathStr, index);
            this.fillUp();
        },
        move: function(from, to) {
            this.$lcfStore.arrayMove(this.pathStr, from, to);
        },
        moveUp: function(index) {
            this.move(index, index - 1);
        },
        moveDown: function(index) {
            this.move(index, index + 1);
        },
        setDraggableIndex: function(index) {
            this.draggableIndex = index;
            document.addEventListener('mouseup', this.clearDraggableIndex);
        },
        clearDraggableIndex: function() {
            this.draggableIndex = false;
            document.removeEventListener('mouseup', this.clearDraggableIndex);
        },
        dragStart: function(e) {
            if (this.draggableIndex !== false) {
                window.setTimeout(() => {
                    this.draggingStartedIndex = this.draggableIndex;
                }, 100);
                document.addEventListener('dragover', this.dragOver);
                document.addEventListener('dragend', this.dragEnd);
            }
        },
        dragEnd: function(e) {
            if (this.dropIndex != null) {
                this.move(this.draggableIndex, this.dropIndex);
            }
            this.draggableIndex = false;
            this.draggingStartedIndex = false;
            this.dropIndex = false;
            document.removeEventListener('dragover', this.dragOver);
            document.removeEventListener('dragend', this.dragEnd);
        },
        dragOver: function(e) {
            this.dropIndex = this.getTargetIndexFromCursorPosition(e.pageY);
            e.preventDefault();
        },
        getTargetIndexFromCursorPosition: function(pageY) {
            var otherItems = this.$el.querySelectorAll('.lcf-repeater-item[draggable=false]');
            var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            var midpoints = map(otherItems, (ele) => {
                var rect = ele.getBoundingClientRect();
                return scrollTop + rect.top + 0.5 * rect.height;
            });
            console.log(otherItems.length, midpoints, pageY);
            for (var i = 0; i < midpoints.length; i++) {
                if (pageY < midpoints[i]) {
                    return i;
                }
            }
            return midpoints.length;
       },
    }
};
</script>
