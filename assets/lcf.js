import Vue from 'vue';
import { forEach } from 'lodash-es';
import fieldMixin from './field-mixin';
import inputMixin from './input-mixin';

// Load all the components from the components directory
const componentFiles = require.context('./components', true, /\.vue$/i);
componentFiles.keys().map(key => {
    var componentName = 'lcf-' + key.split('/').pop().split('.')[0];
    Vue.component(componentName, componentFiles(key).default);
});

// Create the store which will contain lcf data, and store a reference to it in the Vue prototype
import store from './store.js';
Vue.prototype.$lcfStore = store;

var initLcf = function() {

    forEach(document.querySelectorAll('lcf'), function(el) {
        var component = 'lcf-' + el.getAttribute('data-component');
        var props = JSON.parse(el.getAttribute('data-props') || '{}');
        new Vue({
            el: el,
            render: function(h) {
                return h(component, {props: props});
            }
        });
    });

    forEach(document.querySelectorAll('lcf-initial-errors'), function(el) {
        var groupName = el.getAttribute('data-group-name');
        var errors = JSON.parse(el.getAttribute('data-errors') || '{}');
        store.setErrors(groupName, errors);
        el.parentElement.removeChild(el);
    });
};

export default {
    fieldMixin: fieldMixin,
    inputMixin: inputMixin,
    init: function() {
        if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
            initLcf();
        } else {
            document.addEventListener("DOMContentLoaded", initLcf);
        }
    }
}
