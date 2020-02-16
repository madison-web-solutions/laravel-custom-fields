import { assign, get } from 'lodash-es';
export default {
    props: ['path', 'field', 'cssClasses'],
    computed: {
        pathStr: function() {
            return this.path.join('.');
        },
        inputName: function() {
            return [this.path[1]].concat(this.path.slice(2).map(part => '[' + part + ']')).join('');
        },
        fieldName: function() {
            return this.path[this.path.length - 1];
        },
        nodeId: function() {
            return this.$lcfStore.getId(this.pathStr);
        },
        value: function() {
            return this.$lcfStore.getValue(this.pathStr);
        },
        errors: function() {
            return this.$lcfStore.getErrors(this.pathStr);
        },
        hasError: function() {
            return !!this.errors.length;
        },
        childIds: function() {
            return this.$lcfStore.getChildIds(this.pathStr);
        },
        childValues: function() {
            return this.$lcfStore.getChildValues(this.pathStr);
        },
        childErrors: function() {
            return this.$lcfStore.getChildErrors(this.pathStr);
        },
        label: function() {
            return get(this.field, 'settings.label');
        },
        required: function() {
            return get(this.field, 'settings.required');
        },
        help: function() {
            return get(this.field, 'settings.help');
        },
        inputSettings: function() {
            return assign({name: this.inputName, key: this.fieldName}, this.field.settings);
        },
        myCssClasses: function() {
            var fieldClasses = get(this.field, 'settings.cssClasses', []);
            return ['lcf-field'].concat(fieldClasses).concat(this.cssClasses);
        },
        condition: function() {
            return get(this.field, 'settings.condition');
        },
        shouldShow: function() {
            return this.$lcfStore.testCondition(this.pathStr, this.condition);
        },
        defaultValue: function() {
            return get(this.field, 'settings.default');
        }
    },
    mounted: function() {
        this.$lcfStore.updateShowing(this.pathStr, this.shouldShow);
    },
    watch: {
        shouldShow: function(newValue) {
            this.$lcfStore.updateShowing(this.pathStr, newValue);
        }
    }
};
