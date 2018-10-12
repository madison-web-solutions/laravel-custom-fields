<template>
    <div class="lcf-repeater-field">
        <div class="lcf-repeater-item" v-for="child, index in children" :key="child.key" >
            <button type="button" @click="insert(index)" class="lcf-btn-icon" data-name="insert">insert</button>
            <button type="button" @click="remove(index)" class="lcf-btn-icon" data-name="remove">remove</button>
            <div class="lcf-repeater-index">
                <label>{{ index }}</label>
                <button type="button" :disabled="index == 0" @click="moveUp(index)" class="lcf-btn-icon" data-name="move-up">up</button>
                <button type="button" :disabled="index == lastIndex" @click="moveDown(index)" class="lcf-btn-icon" data-name="move-down">down</button>
            </div>
            <lcf-field :path="path.concat(index)" :field="field.options.sub_field" :initialValue="child.initialValue" :errors="errors" />
        </div>
        <button type="button" @click="append" class="btn btn-icon-charcoal" data-name="append">add</button>
    </div>
</template>

<script>
import _ from 'lodash';
var counter = 0;
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
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
            children: children
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
        }
    }
};
</script>
