<template>
    <div ref="container" class="lcf-repeater-field">
        <div class="lcf-repeater-item" v-for="child, index in children" :key="child.key" :draggable="draggingIndex !== false" @dragstart="dragStart">
            <button type="button" @click="insert(index)" class="lcf-btn-icon" data-name="insert">insert</button>
            <button type="button" @click="remove(index)" class="lcf-btn-icon" data-name="remove">remove</button>
            <div class="lcf-repeater-index">
                <span class="lcf-repeater-move-handle" @mousedown="setDragging(index)">move</span>
                <label>{{ index }}</label>
                <button type="button" :disabled="index == 0" @click="moveUp(index)" class="lcf-btn-icon" data-name="move-up">up</button>
                <button type="button" :disabled="index == lastIndex" @click="moveDown(index)" class="lcf-btn-icon" data-name="move-down">down</button>
            </div>
            <field :path="path.concat(index)" :field="field.options.sub_field" :initialValue="child.initialValue" :errors="errors" />
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
    props: ['path', 'field', 'initialValue', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var children = [];
        if (_.isArray(this.initialValue)) {
            for (var i = 0; i < this.initialValue.length; i++) {
                children.push({
                    key: counter++,
                    initialValue: this.initialValue[i]
                });
            }
        }
        return {
            children: children,
            draggingIndex: false,
        };
    },
    computed: {
        lastIndex: function() {
            return _.get(this.value, 'length', 0) - 1;
        }
    },
    methods: {
        moveUp: function(index) {
            this.children.splice(index - 1, 0, this.children.splice(index, 1)[0]);
        },
        moveDown: function(index) {
            this.children.splice(index + 1, 0, this.children.splice(index, 1)[0]);
        },
        remove: function(index) {
            this.children.splice(index, 1);
        },
        insert: function(index) {
            this.children.splice(index, 0, {
                key: counter++,
                initialValue: null
            });
        },
        append: function() {
            this.children.push({
                key: counter++,
                initialValue: null
            });
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
            console.log('showInsertPosition '+newIndex);
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
            console.log('setDragging '+this.fieldPath);
            this.draggingIndex = index;
        },
        dragStart: function(e) {
            console.log('dragStart',this.fieldPath,this.draggingIndex);
            if (this.draggingIndex !== false) {
                this.draggingItem = e.target;
                document.addEventListener('dragover', this.dragOver);
                document.addEventListener('dragend', this.dragEnd);
            }
        },
        dragEnd: function(e) {
            console.log('dragEnd '+this.fieldPath);
            var newIndex = this.getClosest(e.pageY);
            this.showInsertPosition(false);
            this.children.splice(newIndex, 0, this.children.splice(this.draggingIndex, 1)[0]);
            this.draggingIndex = false;
            this.draggingItem = false;
            document.removeEventListener('dragover', this.dragOver);
            document.removeEventListener('dragend', this.dragEnd);
        },
        dragOver: function(e) {
            var newIndex = this.getClosest(e.pageY);
            this.showInsertPosition(newIndex);
        }
    },
    created: function() {
        this.draggingItem = false;
    }
};
</script>
