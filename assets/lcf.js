import _ from 'lodash';
import Vue from 'vue';
import Vuex from 'vuex';
import storeDefn from './store.js';

Vue.use(Vuex);
var store = new Vuex.Store(storeDefn);

var componentNames = [
    'compound-input',
    'field-wrapper',
    'field',
    'link-input',
    'markdown-input',
    'media-input',
    'media-library',
    'media-preview',
    'number-input',
    'options-input',
    'radio-input',
    'repeater-input',
    'search-input',
    'select-input',
    'switch-input',
    'text-input',
    'text-area-input',
    'timestamp-input',
    'toggle-input'
];
_.forEach(componentNames, function(componentName) {
    var componentDefn = require('./components/' + componentName + '.vue');
    // If this is running under Laravel Mix v4 or higher then the require() function will
    // not automatically pull out the default object from the module, so we attempt to
    // detect and correct this situation below
    if (componentDefn.default && componentDefn.__esModule) {
        componentDefn = componentDefn.default;
    }
    Vue.component(componentName, componentDefn);
});

var initLcf = function() {
    _.forEach(document.querySelectorAll('lcf-field'), function(el) {
        var props = JSON.parse(el.getAttribute('data-props'));
        new Vue({
            el: el,
            store: store,
            render: function(h) {
                return h('field-wrapper', {props: props});
            }
        });
    });
};

// set CSRF token for axios requests
import axios from 'axios';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector('meta[name="csrf-token"]').content;

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    initLcf();
} else {
    document.addEventListener("DOMContentLoaded", initLcf);
}
