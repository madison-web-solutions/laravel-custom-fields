<template>
    <div ref="input" class="lcf-checkboxes-group" :class="{'lcf-input-has-error': hasError}">
        <label v-for="label, key in choices">
            <input :name="nameAttr + '[' + key +']'" type="checkbox" value="true" :data-key="key" :checked="checked[key]" @change="change" />
            <span>{{ label }}</span>
        </label>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        this.$store.commit('objectInitKeys', {path: this.pathStr, keys: _.keys(this.field.options.choices)});
        // @todo default
    },
    computed: {
        choices: function() {
            return this.field.options.choices;
        },
        checked: function() {
            return _.mapValues(this.childNodeIds, (childNodeId) => {
                var childNode = this.$store.state.nodes[childNodeId];
                return childNode ? childNode.value : false;
            });
        }
    },
    methods: {
        change: function(e) {
            this.$store.commit('updateValue', {
                path: this.path.concat(e.target.getAttribute('data-key')).join('.'),
                value: e.target.checked
            });
        }
    }
};
</script>
