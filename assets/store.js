import axios from 'axios';
import { uniqueId, isArray, isObject, isString, isInteger, map, mapValues, forEach, cloneDeep } from 'lodash-es';
import Vue from 'vue';

var store = new Vue({
    data: function() {
        return {
            groups: {},
            nodes: {},
            displayNames: {},
            mediaItems: {}
        };
    }
});

var el = document.body.appendChild(document.createElement('div'));
store.$mount(el);

var mapArrayOrObject = function(arrayOrObject, fn) {
    if (arrayOrObject == null) {
        return null;
    } else if (isArray(arrayOrObject)) {
        return map(arrayOrObject, fn);
    } else if (isObject(arrayOrObject)) {
        return mapValues(arrayOrObject, fn);
    } else {
        throw new Error("mapArrayOrObject expects first argument to be array or object or null, got " + typeof(arrayOrObject));
    }
};

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

// Create a new node with the given value and child node ids
// Return the id for the created node
var addNode = function(value, children) {
    var id = uniqueId('v');
    console.log("creating node " + id + " with value " + value);
    Vue.set(store.nodes, id, {value: value, children: children, errors: []});
    return id;
};

var setInitialValue = function(groupName, fieldName, value)
{
    if (! store.groups[groupName]) {
        Vue.set(store.groups, groupName, {});
    }
    var recurse = function(value) {
        var id = uniqueId('v');
        if (isScalar(value)) {
            return addNode(value, null);
        } else if (isArray(value)) {
            return addNode(null, map(value, recurse));
        } else if (isObject(value)) {
            return addNode(null, mapValues(value, recurse));
        } else {
            throw new Error("value must be scalar, array or object");
        }
    };
    Vue.set(store.groups[groupName], fieldName, recurse(value));
};

// Get the node id for a given absolute path
// path is an array, starting with the groupname
var getId = function(path) {
    if (path.length < 2) {
        return null;
    }
    var groupName = path[0];
    var fieldName = path[1];
    if (! store.groups[groupName] || ! store.groups[groupName][fieldName]) {
        return null;
    }
    var id = store.groups[groupName][fieldName];
    var getChildId = function(parentId, key) {
        var node = parentId ? store.nodes[parentId] : null;
        return (node && node.children) ? node.children[key] : null;
    };
    for (var i = 2; i < path.length; i++) {
        id = getChildId(id, path[i]);
    };
    return id;
};

var getNode = function(path) {
    return store.nodes[getId(path)];
};

var getValue = function(path) {
    console.log("getting value at path " + path.join('.'));
    var node = getNode(path);
    var value = node ? node.value : null;
    console.log("value at path " + path.join('.')+ " is " + value);
    return value;
};

var getErrors = function(path) {
    var node = getNode(path);
    return node ? node.errors : [];
};

var getChildNodes = function(path) {
    var node = getNode(path);
    return node ? mapArrayOrObject(node.children, id => store.nodes[id]) : null;
};

var getChildIds = function(path) {
    var node = getNode(path);
    return node.children;
};

var getChildKeys = function(path) {
    var node = getNode(path);
    return node ? mapArrayOrObject(node.children, (id, key) => key) : null;
};

var getChildValues = function(path) {
    return mapArrayOrObject(getChildNodes(path), node => node ? node.value : null);
};

var getChildErrors = function(path) {
    return mapArrayOrObject(getChildNodes(path), node => node ? node.errors : []);
};

var getOrCreateArrayNode = function(path) {
    var node = getNode(path);
    if (! node) {
        throw new Error("no node exists at path " + path);
    }
    if (node.children == null) {
        Vue.set(node, 'children', []);
    }
    if (! isArray(node.children)) {
        throw new Error("not an array at path " + path);
    }
    return node;
};

var getOrCreateObjectNode = function(path) {
    var node = getNode(path);
    if (! node) {
        throw new Error("no node exists at path " + path);
    }
    if (node.children == null) {
        Vue.set(node, 'children', {});
    }
    if (! isObject(node.children)) {
        throw new Error("not an object at path " + path);
    }
    return node;
};

var arrayInsert = function(path, index) {
    var node = getOrCreateArrayNode(path);
    var childId = addNode(null, null);
    node.children.splice(index, 0, childId);
};

var arrayDelete = function(path, index) {
    var node = getOrCreateArrayNode(path);
    var deletedId = node.children.splice(index, 1)[0];
    Vue.delete(store.nodes, deletedId); // Also delete children?
};

var arrayMove = function(path, from, to) {
    var node = getOrCreateArrayNode(path);
    var cutId = node.children.splice(from, 1)[0];
    node.children.splice(to, 0, cutId);
};

var objectAddKey = function(path, key) {
    var node = getOrCreateObjectNode(state, path);
    var childId = addNode(state, null, null);
    Vue.set(node.children, key, childId);
};

var objectDeleteKey = function(path, key) {
    var node = getOrCreateObjectNode(path);
    var deletedId = node.children[key];
    Vue.delete(node.children, key);
    Vue.delete(store.nodes, deletedId); // Also delete children?
};

var updateValue = function(path, value) {
    console.log("updating value at path " + path.join('.')+ " to " + value);
    if (! isScalar(value)) {
        // @todo investigate js errors properly
        throw new Error("value is not a scalar");
    }
    var node = getNode(path);
    if (!node) {
        throw new Error("Path " + path + " not found in store");
    }
    Vue.set(node, 'value', value);
};

var recurseTree = function(path, node, fn) {
    fn(path, node);
    if (node.children) {
        forEach(node.children, function (childId, childKey) {
            recurseTree(path.concat(childKey), store.nodes[childId], fn);
        });
    }
};

var walkTree = function(groupName, fn) {
    var group = store.groups[groupName];
    if (! group) {
        throw new Error("No group " + groupName);
    }
    forEach(group, (nodeId, fieldName) => {
        recurseTree([fieldName], store.nodes[nodeId], fn);
    });
};

var setErrors = function(groupName, errors) {
    if (errors == null) {
        errors = {};
    }
    if (! isObject(errors)) {
        throw new Error("errors should be an object but got " + typeof(errors));
    }
    var handledPaths = mapValues(errors, () => false);
    walkTree(groupName, (path, node) => {
        var pathStr = path.join('.');
        console.log('checking path '+pathStr+' for errors');
        if (errors.hasOwnProperty(pathStr)) {
            var nodeErrors = errors[pathStr];
            handledPaths[pathStr] = true;
            if (! isArray(nodeErrors)) {
                throw new Error("Entries in error objects should be arrays but got " + typeof(nodeErrors) + " at path " + pathStr);
            }
            // @todo also check array entries are strings?
            Vue.set(node, 'errors', nodeErrors);
        } else {
            Vue.set(node, 'errors', []);
        }
    });
    // Caller might want to see if there were any errors within the error object which were not picked up by this function
    // IE because the error key didn't match any of the paths in the group
    // So return the handledPaths object - the caller can then see if there were any unhandled paths and show suitable messages etc
    return handledPaths;
};

var getDisplayName = function(fieldSettings, id) {
    if (! id) {
        return '';
    }
    var key = JSON.stringify([fieldSettings, id]);
    if (! store.displayNames.hasOwnProperty(key)) {
        Vue.set(store.displayNames, key, '');
        var settings = cloneDeep(fieldSettings);
        var type = settings.type;
        delete settings.type;
        axios.get('/lcf/display-name', {params: {type, settings, id}}).then(response => {
            Vue.set(store.displayNames, key, response.data);
        }, error => {
            console.log(error);
        });
    }
    return store.displayNames[key];
};

var getSuggestions = function(fieldSettings, search, callback) {
    var settings = cloneDeep(fieldSettings);
    var type = settings.type;
    delete settings.type;
    axios.get('/lcf/suggestions', {params: {type, settings, search}}).then(response => {
        callback(search, response.data);
    }, error => {
        console.log(error);
    });
};

var getMediaItem = function(id) {
    if (! id) {
        return null;
    }
    if (! store.mediaItems.hasOwnProperty(id)) {
        Vue.set(store.mediaItems, id, null);
        axios.get('/lcf/media-library/' + id).then(response => {
            Vue.set(store.mediaItems, id, response.data.item);
        }, error => {
            console.log(error);
        });
    }
    return store.mediaItems[id];
};

var searchIdCounter = 1;
var searchMediaLibrary = function(category, search, page, callback) {
    var searchId = searchIdCounter++;
    console.log('get '+searchId, category, search, page);
    axios.get('/lcf/media-library', {params: {
        category: category,
        search: search,
        page: page
    }}).then(response => {
        var itemIds = [];
        forEach(response.data.data, (item) => {
            Vue.set(store.mediaItems, item.id, item);
            itemIds.push(item.id);
        });
        callback(searchId, itemIds);
    }, error => {
        console.log(error);
    });
    return searchId;
};

var uploadToMediaLibrary = function(formData, progressFn, successFn, errorFn) {
    axios.post('/lcf/media-library', formData, {
        onUploadProgress: function(e) {
            progressFn(Math.round(100 * e.loaded / e.total));
        }
    }).then(response => {
        console.log(response);
        if (response.data.ok) {
            Vue.set(store.mediaItems, response.data.item.id, response.data.item);
            successFn(response.data.item.id);
        } else {
            errorFn(response.data.error);
        }
    }).catch(err => {
        var msg = 'upload error';
        if (err.response.status == 413) {
            msg = 'File too large';
        }
        errorFn(msg);
    });
};

var updateMediaItem = function(itemId, data, successFn, errorFn) {
    axios.post('/lcf/media-library/' + itemId, data).then(response => {
        Vue.set(store.mediaItems, response.data.item.id, response.data.item);
        successFn();
    }, error => {
        if (error.response.data && error.response.data.errors) {
            errorFn('Error: ' + JSON.stringify(error.response.data.errors));
        } else {
            errorFn('Server error');
        }
    });
};

var deleteMediaItem = function(itemId, callback) {
    axios.post('/lcf/media-library/' + itemId + '/delete', {}).then(response => {
        callback();
        Vue.delete(store.mediaItems, itemId);
    });
};







var pathArg = function(path) {
    if (! isString(path) || path == '') {
        throw new Error("path must be a non-empty string");
    }
    var intRegex = /^(0|([1-9][0-9]*))$/;
    return map(path.split('.'), part => intRegex.test(part) ? parseInt(part) : part);
};

var scalarArg = function(value) {
    if (! isScalar(value)) {
        throw new Error("value must be a scalar primitive");
    }
    return value;
};

var stringArg = function(arg) {
    if (! isString(arg) || arg == '') {
        throw new Error("argument must be a non-empty string");
    }
    return arg;
};

var integerArg = function(arg) {
    if (! isInteger(arg)) {
        throw new Error("argument must be an integer");
    }
    return arg;
};

export default {
    getId: function(path) {
        return getId(pathArg(path));
    },
    getValue: function(path) {
        return getValue(pathArg(path));
    },
    getErrors: function(path) {
        return getErrors(pathArg(path));
    },
    getChildIds: function(path) {
        return getChildIds(pathArg(path));
    },
    getChildKeys: function(path) {
        return getChildKeys(pathArg(path));
    },
    getChildValues: function(path) {
        return getChildValues(pathArg(path));
    },
    getChildErrors: function(path) {
        return getChildErrors(pathArg(path));
    },
    updateValue: function(path, value) {
        updateValue(pathArg(path), scalarArg(value));
    },
    setInitialValue: function(groupName, fieldName, complexValue) {
        setInitialValue(stringArg(groupName), stringArg(fieldName), complexValue);
    },
    setErrors: function(groupName, errors) {
        setErrors(stringArg(groupName), errors);
    },
    arrayInsert: function(path, index) {
        arrayInsert(pathArg(path), integerArg(index));
    },
    arrayDelete: function(path, index) {
        arrayDelete(pathArg(path), integerArg(index));
    },
    arrayMove: function(path, from, to) {
        arrayMove(pathArg(path), integerArg(from), integerArg(to));
    },
    objectInitKeys: function(path, keys) {
        if (! isArray(keys)) {
            throw new Error("keys must be an array");
        }
        var node = getOrCreateObjectNode(pathArg(path));
        forEach(keys, (key) => {
            key = stringArg(key);
            if (! node.children[key]) {
                var childId = addNode(null, null);
                console.log('initialising key '+key+' on '+path+' with node '+childId);
                Vue.set(node.children, key, childId);
            }
        });
    },
    getDisplayName: getDisplayName,
    getSuggestions: getSuggestions,
    getMediaItem: getMediaItem,
    searchMediaLibrary: searchMediaLibrary,
    updateMediaItem: updateMediaItem,
    uploadToMediaLibrary: uploadToMediaLibrary,
    deleteMediaItem: deleteMediaItem
};
