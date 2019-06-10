import { padStart, forEach, isObjectLike, isArrayLike, isString, isInteger, isFinite } from 'lodash-es';

var clamp = function(val, min, max) {
    return Math.max(Math.min(val, max), min);
};

var lerp = function(x0, x1, t) {
    return x0 + t * (x1 - x0);
};

var inverseLerp = function(x0, x1, xt) {
    return (xt - x0) / (x1 - x0);
};

var sterlingFormat = function(pence) {
    return '£' + (pence / 100).toFixed(2);
};

var elementIsOrContains = function(containerEle, childEle) {
    var testEle = childEle;
    while (testEle) {
        if (containerEle === testEle) {
            return true;
        }
        testEle = testEle.parentElement;
    }
    return false;
};

var iconClassForMediaItem = function(item) {
    if (! item) {
        return null;
    }
    if (! item.url) {
        return 'warning fas fa-exclamation-triangle';
    }
    if (item.thumb || item.extension == 'svg') {
        return null;
    }
    switch (this.item.extension) {
        case 'pdf':
            return 'fas fa-file-pdf';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word';
        case 'xls':
        case 'xlsx':
            return 'fas fa-file-excel';
        case 'csv':
            return 'fas fa-file-csv';
        case 'ppt':
        case 'pptx':
            return 'fas fa-file-powerpoint';
        default:
            switch (this.item.Category) {
                case 'Document':
                    return 'fas fa-file-alt';
                case 'Image':
                    return 'fas-file-image';
                default:
                    return 'fas fa-file';
            }
    }
};

var shortDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
var longDayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
var shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var longMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

var shortDayName = function(i) {
    return shortDayNames[i];
};
var longDayName = function(i) {
    return longDayNames[i];
};
var shortMonthName = function(i) {
    return shortMonthNames[i];
};
var longMonthName = function(i) {
    return longMonthNames[i];
};
var ordinalSuffix = function( d ) {
    if( d > 3 && d < 21 ) {
        return 'th';
    }

    switch( d % 10 ) {
        case 1:
            return 'st';
        case 2:
            return 'nd';
        case 3:
            return 'rd';
        default:
            return 'th';
    }
};

var dateFrom = {
    date: function(date) {
        return (date instanceof Date) ? new Date(date.getTime()) : null;
    },
    ymd: function(ymd) {
        var match = /^(\d\d\d\d)-(\d\d)-(\d\d)$/.exec(ymd);
        if (match) {
            return new Date(Date.UTC(match[1] * 1, (match[2] * 1) - 1, match[3] * 1, 0, 0, 0, 0));
        } else {
            return null;
        }
    },
    dmy: function(dmy) {
        var match = /^(\d\d)\/(\d\d)\/(\d\d\d\d)$/.exec(dmy);
        if (match) {
            return new Date(Date.UTC(match[3], (match[2] * 1) - 1, match[1] * 1, 0, 0, 0, 0));
        } else {
            return null;
        }
    },
    jsTimestamp: function(ts) {
        return isInteger(ts) ? new Date(ts) : null;
    },
    phpTimestamp: function(ts) {
        return isFinite(ts) ? new Date(Math.round(ts * 1000)) : null;
    },
};

var dateTo = {
    ymd: function(date) {
        return [
            date.getUTCFullYear(),
            padStart(date.getUTCMonth() + 1, 2, '0'),
            padStart(date.getUTCDate(), 2, '0')
        ].join('-');
    },
    dmy: function(date) {
        return [
            padStart(date.getUTCDate(), 2, '0'),
            padStart(date.getUTCMonth() + 1, 2, '0'),
            date.getUTCFullYear().toString()
        ].join('/');
    },
    JsTimestamp: function(date) {
        return date.getTime();
    },
    phpTimestamp: function(date) {
        return Math.round(date.getTime() / 1000);
    },
    pretty: function(date, withDay) {
        var parts = [
            date.getUTCDate(),
            shortMonthName(date.getUTCMonth()),
            date.getUTCFullYear().toString().substr(2,2)
        ];
        if (withDay) {
            parts.unshift(shortDayName(date.getDay()));
        }
        return parts.join(' ');
    },
    prettyLong: function(date, withDay) {
        var parts = [
            date.getUTCDate() + ordinalSuffix( date.getUTCDate() ),
            longMonthName(date.getUTCMonth()),
            date.getUTCFullYear().toString()
        ];
        if (withDay) {
            parts.unshift(longDayName(date.getDay()));
        }
        return parts.join(' ');
    }
};

var dateConvert = function(input, fromFormat, toFormat) {
    var inMethod = dateFrom[fromFormat];
    var date = (inMethod ? inMethod(input) : null);
    if (date && toFormat) {
        var outMethod = dateTo[toFormat];
        return (outMethod ? outMethod(date) : null);
    }
    return date;
};

var timeParse = function(input) {
    var res1 = /^(\d\d)(\d\d)(\d\d)?$/.exec(input);
    var res2 = /^(\d\d?):(\d\d?)(:(\d\d?))?$/.exec(input);
    if (res1) {
        var hours = parseInt(res1[1], 10);
        var mins = parseInt(res1[2], 10);
        var secs = (res1[3] == null ? 0 : parseInt(res1[3]));
    } else if (res2) {
        var hours = parseInt(res2[1], 10);
        var mins = parseInt(res2[2], 10);
        var secs = (res2[4] == null ? 0 : parseInt(res2[4]));
    } else {
        return null;
    }
    hours = clamp(hours, 0, 23);
    mins = clamp(mins, 0, 59);
    secs = clamp(secs, 0, 59);
    return [hours, mins, secs];
};

var timeFormat = function(hours, mins, secs) {
    var parts = [
        padStart(hours, 2, '0'),
        padStart(mins, 2, '0')
    ];
    if (secs != null) {
        parts.push(padStart(secs, 2, '0'));
    }
    return parts.join(':');
};

var replaceAll = function(find, replace, subject) {
    return subject.split(find).join(replace);
};

export default {
    clamp,
    lerp,
    inverseLerp,
    sterlingFormat,
    elementIsOrContains,
    iconClassForMediaItem,
    shortDayName,
    longDayName,
    shortMonthName,
    longMonthName,
    ordinalSuffix,
    dateFrom,
    dateTo,
    dateConvert,
    timeParse,
    timeFormat,
    replaceAll
};
