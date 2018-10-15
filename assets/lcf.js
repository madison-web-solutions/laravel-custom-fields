var Vue = require('vue');

Vue.component('lcf-field-wrapper', require('./lcf-field-wrapper.vue'));
Vue.component('lcf-field', require('./lcf-field.vue'));
Vue.component('lcf-text-input', require('./lcf-text-input.vue'));
Vue.component('lcf-compound-input', require('./lcf-compound-input.vue'));
Vue.component('lcf-repeater-input', require('./lcf-repeater-input.vue'));
Vue.component('lcf-switch-input', require('./lcf-switch-input.vue'));
Vue.component('lcf-search-input', require('./lcf-search-input.vue'));

var initLcf = function() {
    _.forEach(document.querySelectorAll('lcf-field'), function(el) {
        var props = JSON.parse(el.getAttribute('data-props'));
        new Vue({
            el: el,
            render: function(h) {
                return h('lcf-field-wrapper', {props: props});
            }
        });
    });
};

if (document.readyState === "complete" || (document.readyState !== "loading" && !document.documentElement.doScroll)) {
    initLcf();
} else {
    document.addEventListener("DOMContentLoaded", initLcf);
}
