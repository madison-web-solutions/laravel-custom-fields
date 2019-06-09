import { assign, get } from 'lodash-es';
export default {
    props: ['path', 'field'],
    computed: {
        pathStr: function() {
            return this.path.join('.');
        },
        inputName: function() {
            return [this.path[1]].concat(this.path.slice(2).map(part => '[' + part + ']')).join('');
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
            return this.errors && this.errors.length;
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
        help: function() {
            return get(this.field, 'settings.help');
        },
        inputSettings: function() {
            return assign({name: this.inputName, key: this.path[this.path.length - 1]}, this.field.settings);
        }
    }
};
