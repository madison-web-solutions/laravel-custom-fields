<template>
    <div class="lcf-field lcf-repeater-field">
        <label class="lcf-field-label" v-if="label">{{ label }}</label>
        <p v-if="help" class="lcf-help">{{ help }}</p>
        <div class="lcf-repeater-item" v-for="id, index in childIds">
            <div class="lcf-repeater-item-controls">
                <button type="button" @click="insert(index)" class="lcf-btn-icon">add</button>
                <button type="button" @click="remove(index)" class="lcf-btn-icon">remove</button>
                <button type="button" @click="moveUp(index)" class="lcf-btn-icon">up</button>
                <button type="button" @click="moveDown(index)" class="lcf-btn-icon">down</button>
            </div>
            <component :is="subField.fieldComponent" :key="id" :path="path.concat(index)" :field="subField" />
        </div>
        <button type="button" @click="insert(length)" class="lcf-btn-icon">add</button>
        <lcf-error-messages :errors="errors" />
    </div>
</template>

<script>
import { get, keys } from 'lodash-es';
import FieldMixin from '../../field-mixin.js';
export default {
    mixins: [FieldMixin],
    computed: {
        subField: function() {
            return get(this.field, 'settings.sub_field');
        },
        length: function() {
            return this.childIds ? this.childIds.length: 0;
        }
    },
    methods: {
        insert: function(index) {
            this.$lcfStore.arrayInsert(this.pathStr, index);
        },
        remove: function(index) {
            this.$lcfStore.arrayDelete(this.pathStr, index);
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
