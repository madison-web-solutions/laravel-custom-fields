import { get } from 'lodash-es';
export default {
    props: ['settings', 'value', 'hasError'],
    computed: {
        name: function() {
            return this.setting('name');
        },
        _key: function() {
            return this.setting('key', this.$vnode.key);
        },
        required: function() {
            return this.setting('required', false);
        },
    },
    methods: {
        setting: function(name, defaultVal) {
            return get(this.settings, name, defaultVal == null ? null : defaultVal);
        }
    }
};
