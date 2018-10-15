import _ from 'lodash';
export default {
    computed: {
        type: function() {
            return _.get(this.field, 'type', null);
        },
        myKey: function() {
            return this.path[this.path.length - 1];
        },
        label: function() {
            return _.get(this.field, 'options.label', _.startCase(this.myKey));
        },
        required: function() {
            return _.get(this.field, 'options.required', false);
        },
        help: function() {
            return _.get(this.field, 'options.help', null);
        },
        fieldPath: function() {
            return this.path.join('.');
        },
        fieldPathWithoutGroup: function() {
            return this.path.slice(1).join('.');
        },
        nameAttr: function() {
            var name = this.path[1];
            for (var i = 2; i < this.path.length; i++) {
                name += '[' + this.path[i] + ']';
            }
            return name;
        },
        myErrors: function() {
            return this.errors[this.fieldPathWithoutGroup] || [];
        },
        hasError: function() {
            return !!this.myErrors.length;
        }
    }
};
