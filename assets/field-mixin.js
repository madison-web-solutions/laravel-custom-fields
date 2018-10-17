import _ from 'lodash';
export default {
    computed: {
        type: function() {
            return _.get(this.field, 'type', null);
        },
        id: function() {
            return this.$store.getters.getIdAtPath(this.path);
        },
        node: function() {
            return this.$store.state.nodes[this.id];
        },
        childNodeIds: function() {
            return this.node ? this.node.children : [];
        },
        length: function() {
            return _.isArray(this.childNodeIds) ? this.childNodeIds.length : 0;
        },
        value: function() {
            return this.node ? this.node.value : null;
        },
        defaultValue: function() {
            return _.get(this.field, 'options.default', null);
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
        myKey: function() {
            return this.path[this.path.length - 1];
        },
        pathStr: function() {
            return this.path.join('.');
        },
        pathStrWithoutGroup: function() {
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
            return this.errors[this.pathStrWithoutGroup] || [];
        },
        hasError: function() {
            return !!this.myErrors.length;
        }
    }
};
