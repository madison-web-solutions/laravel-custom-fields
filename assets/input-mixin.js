export default {
    props: {
        label: {
            type: String,
            required: false
        },
        name: {
            type: String,
            required: false
        },
        value: {
            required: true
        },
        errors: {
            type: Array,
            required: false,
        },
        required: {
            type: Boolean,
            required: false,
            default: false
        },
        placeholder: {
            type: String,
            required: false
        },
        help: {
            type: String,
            required: false
        }
    },
    data: function() {
        return {
            inputId: Math.random().toString(36).substring(2)
        };
    },
    computed: {
        _key: function() {
            return this.$vnode.key;
        },
        hasError: function() {
            return (this.errors && this.errors.length);
        },
        wrapperProps: function() {
            return {
                label: this.label,
                labelFor: this.labelFor,
                required: this.required,
                help: this.help,
                errors: this.errors
            };
        },
        inputClasses: function() {
            return {
                'lcf-has-error': this.hasError
            };
        }
    },
    methods: {
    }
};
