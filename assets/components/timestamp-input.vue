<template>
    <div>
        <input ref="input" type="text" :class="{'lcf-input-has-error': hasError}" :value="formattedValue" :placeholder="placeholder" @change="change" @keydown.enter.prevent="change" />
        <input :name="nameAttr" type="hidden" :value="value" />
    </div>
</template>

<script>
import _ from 'lodash';
import lcfFieldMixin from '../field-mixin.js';
export default {
    props: ['path', 'field', 'errors'],
    mixins: [lcfFieldMixin],
    created: function() {
        if (this.value == null && this.defaultValue != null) {
            this.updateMyValue(this.defaultValue);
        }
    },
    computed: {
        placeholder: function() {
            switch (this.field.options.type) {
                case 'date':
                    return 'DD / MM / YYYY';
                case 'datetime':
                    return 'DD / MM / YYYY HH:MM';
                case 'time':
                    return 'HH:MM';
            }
        },
        formattedValue: function() {
            if (this.value == null) {
                return '';
            } else if (_.isInteger(this.value)) {
                var date = new Date(this.value * 1000);
                switch (this.field.options.type) {
                    case 'date':
                        return this.jsDateToUserDate(date);
                    case 'datetime':
                        return this.jsDateToUserDateTime(date);
                    case 'time':
                        return this.jsDateToUserTime(date);
                }
            } else {
                return this.value;
            }
        }
    },
    methods: {
        userDateToJsDate: function(dateString) {
            var result = /^(\d\d?)[-\.\/]?(\d\d?)[-\.\/]?(\d\d|\d\d\d\d)$/.exec(dateString.replace(/\s/g,''));
            if (!result) {return null;}
            var year = parseInt(result[3],10);
            if (year < 100) {if (year < 20) {year = 2000 + year;} else {year = 1900 + year;}}
            var month = parseInt(result[2], 10) - 1;
            var date = parseInt(result[1], 10);
            return new Date(Date.UTC(year, month, date, 0, 0, 0));
        },
        userDateTimeToJsDate: function(dateString) {
            var regex = /^\s*(\d\d?)\s*[-\.\/]?\s*(\d\d?)\s*[-\.\/]\s*?(\d\d|\d\d\d\d)\s+(\d\d?)\s*:\s*(\d\d)\s*$/
            var result = regex.exec(dateString);
            if (!result) {return null;}
            var year = parseInt(result[3],10);
            if (year < 100) {if (year < 20) {year = 2000 + year;} else {year = 1900 + year;}}
            var month = parseInt(result[2], 10) - 1;
            var date = parseInt(result[1], 10);
            var hours = parseInt(result[4], 10);
            var mins = parseInt(result[5], 10);
            return new Date(year, month, date, hours, mins, 0);
        },
        userTimeToJsDate: function(dateString) {
            var regex = /^\s*(\d\d?)\s*:\s*(\d\d)\s*$/
            var result = regex.exec(dateString);
            if (!result) {return null;}
            var hours = parseInt(result[1], 10);
            var mins = parseInt(result[2], 10);
            return new Date(1970, 0, 1, hours, mins, 0);
        },
        jsDateToUserDate: function(date) {
            return [
                _.padStart(date.getUTCDate(), 2, '0'),
                _.padStart(date.getUTCMonth() + 1, 2, '0'),
                _.padStart(date.getUTCFullYear(), 4, '0')
            ].join('/');
        },
        jsDateToUserDateTime: function(date) {
            return [
                _.padStart(date.getDate(), 2, '0'),
                _.padStart(date.getMonth() + 1, 2, '0'),
                _.padStart(date.getFullYear(), 4, '0')
            ].join('/') + ' ' + [
                _.padStart(date.getHours(), 2, '0'),
                _.padStart(date.getMinutes(), 2, '0')
            ].join(':');
        },
        jsDateToUserTime: function(date) {
            return [
                _.padStart(date.getHours(), 2, '0'),
                _.padStart(date.getMinutes(), 2, '0')
            ].join(':');
        },
        change: function() {
            this.updateMyValue(null); // this is required so that the new value definitely gets redrawn on screen
            var inputString = this.$refs.input.value;
            var date = null;
            switch (this.field.options.type) {
                case 'date':
                    date = this.userDateToJsDate(inputString);
                    break;
                case 'datetime':
                    date = this.userDateTimeToJsDate(inputString);
                    break;
                case 'time':
                    date = this.userTimeToJsDate(inputString);
                    break;
            }
            this.updateMyValue(date ? date.getTime() / 1000 : inputString);
        }
    }
};
</script>
