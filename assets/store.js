import _ from 'lodash';
import Vue from 'vue';

var isScalar = function(value) {
    switch (typeof value) {
        case 'string':
        case 'number':
        case 'boolean':
            return true;
        case 'undefined':
            return false;
        default:
            return (value === null);
    }
};

var addNode = function(state, value, children)
{
    var id = _.uniqueId('v');
    Vue.set(state.nodes, id, {value: value, children: children});
    return id;
}

var setInitialValue = function(state, groupName, fieldName, value)
{
    if (! state.groups[groupName]) {
        Vue.set(state.groups, groupName, {});
    }
    var recurse = function(value) {
        var id = _.uniqueId('v');
        if (isScalar(value)) {
            return addNode(state, value, null);
        } else if (_.isArray(value)) {
            return addNode(state, null, _.map(value, recurse));
        } else if (_.isObject(value)) {
            return addNode(state, null, _.mapValues(value, recurse));
        } else {
            throw new Error("value must be scalar, array or object");
        }
    };
    Vue.set(state.groups[groupName], fieldName, recurse(value));
};

var getId = function(state, path) {
    if (path.length < 2) {
        return null;
    }
    var groupName = path[0];
    var fieldName = path[1];
    if (!state.groups[groupName] || !state.groups[groupName][fieldName]) {
        return null;
    }
    var id = state.groups[groupName][fieldName];
    var getChildId = function(parentId, key) {
        var node = parentId ? state.nodes[parentId] : null;
        return (node && node.children) ? node.children[key] : null;
    };
    for (var i = 2; i < path.length; i++) {
        id = getChildId(id, path[i]);
    };
    return id;
};

var getNode = function(state, path) {
    return state.nodes[getId(state, path)];
};

var getOrCreateArrayNode = function(state, path) {
    var node = getNode(state, path);
    if (! node) {
        throw new Error("no node exists at path " + path);
    }
    if (node.children == null) {
        Vue.set(node, 'children', []);
    }
    if (!_.isArray(node.children)) {
        throw new Error("not an array at path " + path);
    }
    return node;
};

var getOrCreateObjectNode = function(state, path) {
    var node = getNode(state, path);
    if (! node) {
        throw new Error("no node exists at path " + path);
    }
    if (node.children == null) {
        Vue.set(node, 'children', {});
    }
    if (!_.isObject(node.children)) {
        throw new Error("not an object at path " + path);
    }
    return node;
};

var arrayInsert = function(state, path, index) {
    var node = getOrCreateArrayNode(state, path);
    var childId = addNode(state, null, null);
    node.children.splice(index, 0, childId);
};

var arrayDelete = function(state, path, index) {
    var node = getOrCreateArrayNode(state, path);
    var deletedId = node.children.splice(index, 1)[0];
    Vue.delete(state.nodes, deletedId);
};

var arrayMove = function(state, path, from, to) {
    var node = getOrCreateArrayNode(state, path);
    var cutId = node.children.splice(from, 1)[0];
    node.children.splice(to, 0, cutId);
};

var objectAddKey = function(state, path, key) {
    var node = getOrCreateObjectNode(state, path);
    var childId = addNode(state, null, null);
    Vue.set(node.children, key, childId);
};

var objectDeleteKey = function(state, path, key) {
    var node = getOrCreateObjectNode(state, path);
    var deletedId = node.children[key];
    Vue.delete(node.children, key);
    Vue.delete(state.nodes, deletedId);
};

var updateValue = function(state, path, value) {
    if (! isScalar(value)) {
        throw new Error("value is not a scalar");
    }
    var node = getNode(state, path);
    if (!node) {
        throw new Error("Path " + path + " not found in store");
    }
    Vue.set(node, 'value', value);
};

var parseValue = function(payload) {
    if (! isScalar(payload.value)) {
        throw new Error("payload.value must be a scalar primitive");
    }
    return payload.value;
};

var parsePath = function(payload) {
    if (!_.isString(payload.path) || payload.path == '') {
        throw new Error("payload.path must be a non-empty string");
    }
    var intRegex = /^(0|([1-9][0-9]*))$/;
    return _.map(payload.path.split('.'), (part) => intRegex.test(part) ? parseInt(part) : part);
};

export default {
    state: {
        groups: {},
        nodes: {}
    },
    getters: {
        getIdAtPath: function(state) {
            return function(path) {
                return getId(state, path);
            };
        }
    },
    mutations: {
        updateValue: function(state, payload) {
            var value = parseValue(payload);
            var path = parsePath(payload);
            updateValue(state, path, value);
        },
        setInitialValue: function(state, payload) {
            setInitialValue(state, payload.groupName, payload.fieldName, payload.value);
        },
        arrayInsert: function(state, payload) {
            var path = parsePath(payload);
            if (! _.isInteger(payload.index)) {
                throw new Error("payload.index must be an integer");
            }
            arrayInsert(state, path, payload.index);
        },
        arrayDelete: function(state, payload) {
            var path = parsePath(payload);
            if (! _.isInteger(payload.index)) {
                throw new Error("payload.index must be an integer");
            }
            arrayDelete(state, path, payload.index);
        },
        arrayMove: function(state, payload) {
            var path = parsePath(payload);
            if (! _.isInteger(payload.from)) {
                throw new Error("payload.from must be an integer");
            }
            if (! _.isInteger(payload.to)) {
                throw new Error("payload.to must be an integer");
            }
            arrayMove(state, path, payload.from, payload.to);
        },
        objectInitKeys: function(state, payload) {
            var path = parsePath(payload);
            if (! _.isArray(payload.keys)) {
                throw new Error("payload.keys must be an array");
            }
            var node = getOrCreateObjectNode(state, path);
            _.forEach(payload.keys, (key) => {
                if (! node.children[key]) {
                    var childId = addNode(state, null, null);
                    Vue.set(node.children, key, childId);
                }
            });
        }
    }
};
