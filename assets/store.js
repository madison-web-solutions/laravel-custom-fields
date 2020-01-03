import axios from 'axios';
import { uniqueId, isArray, isObject, isString, isInteger, map, mapValues, includes, forEach, cloneDeep, some, every } from 'lodash-es';
import Vue from 'vue';

var store = new Vue({
    data: function() {
        return {
            top: {}, // Top level node ids
            nodes: {}, // Node objects
            mediaItems: {},
            mediaFolders: null,
            searchObjs: {}
        };
    }
});

// Mounting the store to an element in the dom allows store data to be inspected in vue dev tools
var el = document.body.appendChild(document.createElement('div'));
el.classList.add('lcf-store-dummy');
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

// Return true if value is string, number, boolean or null
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

var getNode = function(pathOrId) {
    if (pathOrId == null) {
        return null;
    }
    if (isString(pathOrId) && store.nodes[pathOrId]) {
        return store.nodes[pathOrId];
    }
    if (isString(pathOrId) || isArray(pathOrId)) {
        return store.nodes[getId(pathOrId)];
    }
    throw new Error("value must be node id (string) or path (string/array)");
};

// Get the node id for a given absolute path
// path is an array, starting with the groupname
// returns null if no node at path
var getId = function(path) {
    if (isString(path)) {
        path = path.split('.');
    }
    var id = store.top[path[0]];
    for (var i = 1; i < path.length; i++) {
        var node = id ? store.nodes[id] : null;
        if (node && node.children) {
            id = node.children[path[i]];
        } else {
            return null;
        }
    }
    return id;
};

var getValue = function(path) {
    var node = getNode(path);
    var value = node ? node.value : null;
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




// Create a new node and return the id for the created node
var addNode = function() {
    var id = uniqueId('v');
    Vue.set(store.nodes, id, {value: null, children: null, errors: []});
    return id;
};

var setInitialValue = function(groupName, fieldName, value) {
    groupName = stringArg(groupName);
    fieldName = stringArg(fieldName);
    var groupNodeId = store.top[groupName];
    if (! groupNodeId) {
        groupNodeId = addNode();
        Vue.set(store.top, groupName, groupNodeId);
    }
    var groupNode = getOrCreateObjectNode(groupNodeId);
    var fieldNodeId = groupNode.children[fieldName];
    if (! fieldNodeId) {
        fieldNodeId = addNode();
        Vue.set(groupNode.children, fieldName, fieldNodeId);
    }
    setValueDeepAtId(fieldNodeId, value);
};

// External
var setValueDeep = function(path, value) {
    var nodeId = getId(path);
    if (! nodeId) {
        throw new Error("no node exists at path " + path);
    }
    setValueDeepAtId(nodeId, value);
};

// Internal
var setValueDeepAtId = function(nodeId, value) {
    var node = store.nodes[nodeId];
    if (! node) {
        throw new Error("no node exists with id " + nodeId);
    }
    deleteNodeChildren(node);
    if (value == null || isScalar(value)) {
        Vue.set(node, 'value', value);
    } else if (isArray(value) || isObject(value)) {
        Vue.set(node, 'value', null);
        var childIds = mapArrayOrObject(value, (childValue, key) => {
            var childId = addNode();
            setValueDeepAtId(childId, childValue);
            return childId;
        });
        Vue.set(node, 'children', childIds);
    } else {
        throw new Error("value must be scalar, array or object");
    }
};

// Return the value tree from the node at the specified path

var getValueDeep = function(path) {
    return getValueDeepAtId(getId(path));
};

var getValueDeepAtId = function(nodeId) {
    var node = store.nodes[nodeId];
    if (! node) {
        return null;
    } else if (node.children == null) {
        return node.value;
    } else {
        return mapArrayOrObject(node.children, getValueDeepAtId);
    }
};


// Delete a node from the tree, and recursively delete all sub-nodes
// Return the value tree from the deleted node

var deleteNode = function(path) {
    return deleteNodeAtId(getId(path));
};

var deleteNodeAtId = function(nodeId) {
    var node = store.nodes[nodeId];
    var ret = null;
    if (node) {
        ret = deleteNodeChildren(node);
        Vue.delete(store.nodes, nodeId);
    }
    return ret;
};

var deleteNodeChildren = function(node) {
    var ret = null;
    if (node.children == null) {
        ret = node.value;
    } else {
        ret =  mapArrayOrObject(node.children, deleteNodeAtId);
        Vue.set(node, 'children', null);
    }
    return ret;
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
    index = integerArg(index);
    var node = getOrCreateArrayNode(path);
    node.children.splice(index, 0, addNode());
};

var arrayDelete = function(path, index) {
    index = integerArg(index);
    var node = getOrCreateArrayNode(path);
    var deletedId = node.children.splice(index, 1)[0];
    deleteNodeAtId(deletedId);
};

var arrayMove = function(path, from, to) {
    from = integerArg(from);
    to = integerArg(to);
    var node = getOrCreateArrayNode(path);
    var cutId = node.children.splice(from, 1)[0];
    node.children.splice(to, 0, cutId);
};

var objectAddKey = function(path, key) {
    var node = getOrCreateObjectNode(path);
    Vue.set(node.children, key, addNode());
};

var objectDeleteKey = function(path, key) {
    var node = getOrCreateObjectNode(path);
    deleteNodeAtId(node.children[key]);
    Vue.delete(node.children, key);
};

var updateValue = function(path, value) {
    if (! isScalar(value)) {
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

var walkTree = function(path, fn) {
    var node = getNode(path);
    if (!node) {
        throw new Error("Path " + path + " not found in store");
    }
    recurseTree([], node, fn);
};

var setErrors = function(groupName, errors) {
    groupName = stringArg(groupName);
    if (errors == null) {
        errors = {};
    }
    if (! isObject(errors)) {
        throw new Error("errors should be an object but got " + typeof(errors));
    }
    var handledPaths = mapValues(errors, () => false);
    walkTree(groupName, (path, node) => {
        if (path.length == 0) {
            return;
        }
        var pathStr = path.join('.');
        //console.log('checking path '+pathStr+' for errors');
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

var resolveRelativePath = function(pathStr, relativePathStr) {
    var absolutePath = pathArg(pathStr);
    while (relativePathStr[0] == '^') {
        absolutePath.pop();
        relativePathStr = relativePathStr.substr(1);
    }
    return absolutePath.join('.') + (relativePathStr ? '.' + relativePathStr : '');
};

var testCondition = function(pathStr, condition) {
    if (! condition) {
        return true;
    }

    if (condition[0] == 'and') {
        return every(condition[1], (subCondition) => testCondition(pathStr, subCondition));
    }
    if (condition[0] == 'or') {
        return some(condition[1], (subCondition) => testCondition(pathStr, subCondition));
    }
    if (condition[0] == 'not') {
        return ! testCondition(pathStr, condition[1]);
    }

    var testNodePath = pathArg(resolveRelativePath(pathStr, '^' + condition[1]));
    var testNode = getNode(testNodePath);
    if (! testNode) {
        return true;
    }
    switch (condition[0]) {
        case 'eq':
            return condition[2] == testNode.value;
        case 'neq':
            return condition[2] != testNode.value;
        case 'in':
            return includes(condition[2], testNode.value);
        case 'nin':
            return ! includes(condition[2], testNode.value);
        case 'empty':
            return (testNode.value == null || testNode.value === '');
        case 'filled':
            return (testNode.value != null && testNode.value !== '');
    }
    return true;
};

var searchIdCounter = 1;

var lookupSearchObj = function(searchType, searchSettings, id) {
    if (! id) {
        return null;
    }
    var params = {id};
    if (searchType == 'model') {
        params.model_class = searchSettings.model_class;
        params.finder_context = searchSettings.finder_context;
        var key = searchSettings.model_class + ':' + id + ':' + searchSettings.finder_context;
        var url = '/lcf/model-lookup';
    } else if (searchType == 'link') {
        var key = 'link:' + id;
        var url = '/lcf/link-lookup';
    } else {
        throw new Error('Unrecognised search type ' + searchType);
    }
    if (! store.searchObjs.hasOwnProperty(key)) {
        Vue.set(store.searchObjs, key, '');
        axios.get(url, {params: params}).then(response => {
            Vue.set(store.searchObjs, key, response.data);
        }, error => {
            console.log(error);
        });
    }
    return store.searchObjs[key];
};

var getDisplayName = function(searchType, searchSettings, id) {
    var obj = lookupSearchObj(searchType, searchSettings, id);
    return obj ? obj.display_name : '';
};

var getSuggestions = function(searchType, searchSettings, searchString, page, callback) {
    var searchId = searchIdCounter++;
    var params = {search: searchString, page: page};
    if (searchType == 'model') {
        params.model_class = searchSettings.model_class;
        params.finder_context = searchSettings.finder_context;
        var key = (result) => {
            return searchSettings.model_class + ':' + result.id + ':' + searchSettings.finder_context;
        };
        var url = '/lcf/model-suggestions';
    } else if (searchType == 'link') {
        var key = (result) => {
            return 'link:' + result.id;
        };
        var url = '/lcf/link-suggestions';
    } else {
        throw new Error('Unrecognised search type ' + searchType);
    }
    axios.get(url, {params: params}).then(response => {
        forEach(response.data.results, result => {
            Vue.set(store.searchObjs, key(result), result);
        });
        callback(searchId, response.data.results, response.data.has_more);
    }, error => {
        console.log(error);
    });
    return searchId;
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

var searchMediaLibrary = function(category, search, folderId, page, callback) {
    var searchId = searchIdCounter++;
    axios.get('/lcf/media-library', {params: {
        category: category,
        search: search,
        folder_id: folderId,
        page: page
    }}).then(response => {
        var itemIds = [];
        forEach(response.data.data, (item) => {
            Vue.set(store.mediaItems, item.id, item);
            itemIds.push(item.id);
        });
        callback(searchId, itemIds, response.data.meta.last_page > response.data.meta.current_page);
    }, error => {
        console.log(error);
    });
    return searchId;
};

var getMediaLibraryFolders = function() {
    if (store.mediaFolders == null) {
        store.mediaFolders = [];
        axios.get('/lcf/media-library/folders', {}).then(response => {
            store.mediaFolders = response.data.folders;
        }, error => {
            console.log(error);
        });
    }
    return store.mediaFolders;
};

var updateMediaLibraryFolders = function(folders) {
    store.mediaFolders = folders;
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

var getMarkdown = function(input, callback) {
    axios.post('/lcf/markdown', {
        input: input
    }).then(response => {
        callback(response.data);
    }, error => {
        console.log(error);
    });
};

var pathArg = function(path) {
    if (! isString(path) || path == '') {
        throw new Error("path must be a non-empty string");
    }
    var intRegex = /^(0|([1-9][0-9]*))$/;
    return map(path.split('.'), part => intRegex.test(part) ? parseInt(part) : part);
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
    getId: getId,
    getValue: getValue,
    getValueDeep: getValueDeep,
    getErrors: getErrors,
    getChildIds: getChildIds,
    getChildKeys: getChildKeys,
    getChildValues: getChildValues,
    getChildErrors: getChildErrors,
    updateValue: updateValue,
    setInitialValue: setInitialValue,
    setValueDeep: setValueDeep,
    setErrors: setErrors,
    arrayInsert: arrayInsert,
    arrayDelete: arrayDelete,
    arrayMove: arrayMove,
    objectInitKeys: function(path, keys) {
        if (! isArray(keys)) {
            throw new Error("keys must be an array");
        }
        var node = getOrCreateObjectNode(path);
        forEach(keys, (key) => {
            key = stringArg(key);
            if (! node.children[key]) {
                Vue.set(node.children, key, addNode());
            }
        });
    },
    testCondition: testCondition,
    lookupSearchObj: lookupSearchObj,
    getDisplayName: getDisplayName,
    getSuggestions: getSuggestions,
    getMediaItem: getMediaItem,
    searchMediaLibrary: searchMediaLibrary,
    getMediaLibraryFolders: getMediaLibraryFolders,
    updateMediaLibraryFolders: updateMediaLibraryFolders,
    updateMediaItem: updateMediaItem,
    uploadToMediaLibrary: uploadToMediaLibrary,
    deleteMediaItem: deleteMediaItem,
    getMarkdown: getMarkdown
};
