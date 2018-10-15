var Vue = require('vue');
var _ = require('lodash');

var componentNames = [
    'compound-input',
    'field-wrapper',
    'field',
    'media-input',
    'media-library',
    'media-preview',
    'repeater-input',
    'search-input',
    'switch-input',
    'text-input'
];
_.forEach(componentNames, function(componentName) {
    Vue.component(componentName, require('./components/' + componentName + '.vue'));
});

var initLcf = function() {
    _.forEach(document.querySelectorAll('lcf-field'), function(el) {
        var props = JSON.parse(el.getAttribute('data-props'));
        new Vue({
            el: el,
            render: function(h) {
                return h('field-wrapper', {props: props});
            }
        });
    });
};

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    initLcf();
} else {
    document.addEventListener("DOMContentLoaded", initLcf);
}
