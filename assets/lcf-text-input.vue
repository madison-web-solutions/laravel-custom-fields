<template>
    <input ref="input" :name="name" type="text" :class="{'is-invalid': hasError}" :value="value" :placeholder="field.placeholder" @change="change" />
</template>

<script>
import _ from 'lodash';
export default {
    props: ['path', 'field', 'initialValue', 'errors'],
    data: function() {
        return {
            value: (this.initialValue == null) ? "" : String(this.initialValue)
        };
    },
    computed: {
        name: function() {
            var name = this.path[0];
            for (var i = 1; i < this.path.length; i++) {
                name += '[' + this.path[i] + ']';
            }
            return name;
        },
        hasError: function() {
            return !!(this.errors[this.path.join('.')] || []).length;
        }
    },
    methods: {
        change: function() {
            this.value = this.$refs.input.value;
        }
    }
};
</script>
