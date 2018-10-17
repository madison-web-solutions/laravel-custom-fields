<template>
    <div class="lcf-compound-field">
        <div class="lcf-compound-item" v-for="child, key in children" :key="key">
            <template v-if="conditions[key] !== false">
                <div class="lcf-compound-key">
                    <label>{{ child.label }}</label>
                </div>
                <field :path="path.concat(key)" :field="child.field" :errors="errors" />
            </template>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    data: function() {
        var children = {};
        for (var key in this.field.options.sub_fields) {
            var subField = this.field.options.sub_fields[key];
            children[key] = {
                field: subField,
                label: _.get(this.field, 'options.labels.'+key, key),
            };
        }
        return {
            children: children
        };
    },
    computed: {
        conditions: function() {
            if (this.field.options.conditions) {
                return _.mapValues(this.field.options.conditions, this.testCondition);
            } else {
                return {};
            }
        }
    },
    methods: {
        testCondition: function(conditionDefn) {
            if (! conditionDefn) {
                return true;
            }
            switch (conditionDefn[0]) {
                case 'eq':
                    return this.testEqCondition(conditionDefn[1], conditionDefn[2]);
            }
            return true;
        },
        testEqCondition(otherFieldKey, conditionValue) {
            var otherFieldId = this.childNodeIds[otherFieldKey];
            var otherFieldNode = this.$store.state.nodes[otherFieldId];
            if (!otherFieldNode) {
                return true;
            }
            return (otherFieldNode.value == conditionValue);
        }
    }
};
</script>
