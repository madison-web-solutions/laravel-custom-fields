<template>
    <div>
        <input type="hidden" :name="name" :value="value" />
        <input type="text" :class="inputClasses" :value="displayValue" placeholder="dd/mm/yyyy hh:mm:ss" @change="change" />
    </div>
</template>

<script>
import { get, padStart, trim } from 'lodash-es';
import inputMixin from '../../input-mixin.js';
export default {
    mixins: [inputMixin],
    computed: {
        displayValue: function() {
            if (! this.value) {
                return '';
            }
            // Value from Laravel will be a string in UTC time like
            // "2019-06-07T13:19:04.202649Z"
            // If the value doesn't look like this, then we'll just show it as it is
            if (! /^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?Z$/.test(this.value)) {
                return this.value;
            }
            // Value is the expected timestamp format.
            // We parse this with javascript into a Date object in local time
            var d = new Date(this.value);
            if (isNaN(d)) {
                return this.value;
            }
            // Now format for display to the user
            var displayDate = [
                padStart(d.getDate(), 2, '0'),
                padStart(d.getMonth() + 1, 2, '0'),
                d.getFullYear()
            ].join('/');
            var displayTime = [
                padStart(d.getHours(), 2, '0'),
                padStart(d.getMinutes(), 2, '0'),
                padStart(d.getSeconds(), 2, '0')
            ].join(':');
            return displayDate + ' ' + displayTime;
        }
    },
    methods: {
        change: function(e) {
            var d = new Date();
            var inputValue = trim(e.target.value);

            // Look for a date at the start
            var match = /^(\d\d)\/(\d\d)\/(\d\d\d\d)\s*/.exec(inputValue);
            if (!match) {
                this.$emit('change', {key: this._key, value: inputValue});
                return;
            }
            d.setYear(match[3] * 1);
            d.setMonth((match[2] * 1) - 1);
            d.setDate(match[1] * 1);

            // Look for a time at the end
            if (inputValue.length == match[0].length) {
                // user didn't enter a time - we'll put midnight in
                d.setHours(0);
                d.setMinutes(0);
                d.setSeconds(0);
            } else {
                var match = /\s+(\d\d?):(\d\d?)(:(\d\d?))?$/.exec(inputValue);
                if (! match) {
                    this.$emit('change', {key: this._key, value: inputValue});
                    return;
                }
                d.setHours(match[1] * 1);
                d.setMinutes(match[2] * 1);
                d.setSeconds(match[4] == null ? 0 : (match[4] * 1));
            }

            // Now convert back to a string in the laravel UTC format
            var newDate = [
                d.getUTCFullYear(),
                padStart(d.getUTCMonth() + 1, 2, '0'),
                padStart(d.getUTCDate(), 2, '0')
            ].join('-');
            var newTime = [
                padStart(d.getUTCHours(), 2, '0'),
                padStart(d.getUTCMinutes(), 2, '0'),
                padStart(d.getUTCSeconds(), 2, '0')
            ].join(':');
            var newValue = newDate + 'T' + newTime + '.000000Z';
            this.$emit('change', {key: this._key, value: newValue});
        }
    }
};
</script>
