<template>
    <div ref="container" class="lcf-repeater-field">
        <div class="lcf-repeater-item" v-for="childId, index in childNodeIds" :key="childId" :draggable="draggingIndex !== false" @dragstart="dragStart">
            <button type="button" @click="insert(index)" class="lcf-btn-icon" data-name="insert">insert</button>
            <button type="button" @click="remove(index)" class="lcf-btn-icon" data-name="remove">remove</button>
            <div class="lcf-repeater-index">
                <span class="lcf-repeater-move-handle" @mousedown="setDragging(index)">move</span>
                <label>{{ index }}</label>
                <button type="button" :disabled="index == 0" @click="moveUp(index)" class="lcf-btn-icon" data-name="move-up">up</button>
                <button type="button" :disabled="index == lastIndex" @click="moveDown(index)" class="lcf-btn-icon" data-name="move-down">down</button>
            </div>
            <field :path="path.concat(index)" :field="field.options.sub_field" :errors="errors" />
        </div>
        <div ref="insertMarker" class="lcf-repeater-insert-marker"></div>
        <button type="button" @click="append" class="btn btn-icon-charcoal" data-name="append">add</button>
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
    },
    computed: {
        lastIndex: function() {
            return this.length - 1;
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
        },
        insert: function(index) {
            this.$store.commit('arrayInsert', {path: this.pathStr, index: index});
        },
        append: function() {
            this.$store.commit('arrayInsert', {path: this.pathStr, index: this.length});
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
                marker.style.top = pos;
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
