<template>
    <div class="lcf-compound-field">
        <div class="lcf-compound-item" v-for="child, key in children" :key="key">
            <template v-if="conditions[key] !== false">
                <div class="lcf-compound-key">
                    <label :class="{required: child.required}">{{ child.label }}</label>
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
    created: function() {
        this.$store.commit('objectInitKeys', {path: this.pathStr, keys: _.keys(this.field.options.sub_fields)});
    },
    computed: {
        subFields: function() {
            return this.field.options.sub_fields;
        },
        children: function() {
            return _.mapValues(this.subFields, (field, key) => {
                return {
                    field: field,
                    label: _.get(field, 'options.label', _.startCase(key)),
                    required: _.get(field, 'options.required', false)
                };
            })
        },
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
                case 'in':
                    return this.testInCondition(conditionDefn[1], conditionDefn[2]);
            }
            return true;
        },
        testEqCondition(otherFieldPath, conditionValue) {
            var otherFieldNode = this.getNodeAtPath(this.resolveRelativePath(otherFieldPath));
            if (!otherFieldNode) {
                return true;
            }
            return (otherFieldNode.value == conditionValue);
        },
        testInCondition(otherFieldPath, conditionValues) {
            var otherFieldNode = this.getNodeAtPath(this.resolveRelativePath(otherFieldPath));
            if (!otherFieldNode) {
                return true;
            }
            return _.includes(conditionValues, otherFieldNode.value);
        },
    }
};
</script>
