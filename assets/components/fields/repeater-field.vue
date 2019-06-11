<template>
    <div class="lcf-field lcf-repeater-field">
        <lcf-input-wrapper :label="label" :required="required" :help="help" :errors="errors">
            <div class="lcf-input lcf-repeater-input" :class="{'lcf-has-error': hasError}">
                <div class="lcf-repeater-item" v-for="nodeId, index in childIds">
                    <div class="lcf-repeater-index">
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
            </div>
        </lcf-input-wrapper>
    </div>
</template>

<script>
import { get, keys } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    created: function() {
        this.createMinimumItems();
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
        }
    },
    methods: {
        createMinimumItems() {
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
            this.createMinimumItems();
        },
        move: function(from, to) {
            this.$lcfStore.arrayMove(this.pathStr, from, to);
        },
        moveUp: function(index) {
            this.move(index, index - 1);
        },
        moveDown: function(index) {
            this.move(index, index + 1);
        }
    }
};
</script>
