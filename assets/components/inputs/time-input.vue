<template>
    <lcf-input-wrapper class="lcf-input lcf-input-time" v-bind="wrapperProps">
        <input type="text" :class="inputClasses" :name="name" :placeholder="myPlaceholder" :value="value" @change="change" />
    </lcf-input-wrapper>
</template>

<script>
import Util from '../../util.js';
import { get, trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    props: {
        withSeconds: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    computed: {
        myPlaceholder: function() {
            return this.withSeconds ? 'hh:mm:ss' : 'hh:mm';
        }
    },
    methods: {
        change: function(e) {
            var inputValue = trim(e.target.value);
            if (inputValue == '') {
                this.$emit('change', {key: this._key, value: null});
                return;
            }
            var time = Util.timeParse(inputValue);
            if (time) {
                this.$emit('change', {key: this._key, value: Util.timeFormat(time[0], time[1], this.withSeconds ? time[2] : null)});
            } else {
                this.$emit('change', {key: this._key, value: null});
            }
        }
    }
};
</script>
