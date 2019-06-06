<template>
    <div class="lcf-input lcf-time-input">
        <input type="text" :name="name" :placeholder="placeholder" :value="value" @change="change" />
    </div>
</template>

<script>
import Util from '../../util.js';
import { trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        placeholder: function() {
            return 'hh:mm:ss';
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
                this.$emit('change', {key: this._key, value: Util.timeFormat(time[0], time[1], time[2])});
            } else {
                this.$emit('change', {key: this._key, value: null});
            }
        }
    }
};
</script>
