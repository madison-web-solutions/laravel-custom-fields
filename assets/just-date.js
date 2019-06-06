import Util from './util.js';

// JustDate is supposed to represent a calendar date with no time part, no timezones etc
var JustDate = function() {
    if (arguments[0] instanceof Date) {
        this._date = new Date(arguments[0].getTime());
        this._date.setUTCHours(0, 0, 0, 0);
    } else {
        this._date = new Date(Date.UTC(arguments[0], arguments[1], arguments[2], 0, 0, 0, 0));
    }
};
JustDate.from = function(input, format) {
    var date = Util.dateFrom[format](input);
    return date ? new JustDate(date) : null;
};
JustDate.today = function() {
    return new JustDate(new Date());
};
JustDate.prototype.to = function(format) {
    return Util.dateTo[format](this._date);
};
JustDate.prototype.copy = function() {
    return new JustDate(this._date);
};
JustDate.prototype.getTime = function() {
    return this._date.getTime();
};
JustDate.prototype.getYear = function() {
    return this._date.getUTCFullYear();
};
JustDate.prototype.getMonth = function() {
    return this._date.getUTCMonth();
};
JustDate.prototype.getDate = function() {
    return this._date.getUTCDate();
};
JustDate.prototype.getDay = function() {
    return this._date.getUTCDay();
};
JustDate.prototype.getShortDayName = function() {
    return Util.shortDayName[this.getDay()];
};
JustDate.prototype.getLongDayName = function() {
    return Util.longDayName[this.getDay()];
};
JustDate.prototype.getShortMonthName = function() {
    return Util.shortMonthName[this.getMonth()];
};
JustDate.prototype.getLongMonthName = function() {
    return Util.longMonthName[this.getMonth()];
};
JustDate.prototype.addDays = function(days) {
    return new JustDate(this.getYear(), this.getMonth(), this.getDate() + days);
};
JustDate.prototype.nextDay = function() {
    return this.addDays(1);
};
JustDate.prototype.prevDay = function() {
    return this.addDays(-1);
};
JustDate.prototype.isSameAs = function(otherJustDate) {
    return this.getTime() == otherJustDate.getTime();
};
JustDate.prototype.isBefore = function(otherJustDate) {
    return this.getTime() < otherJustDate.getTime();
};
JustDate.prototype.isBeforeOrSameAs = function(otherJustDate) {
    return this.getTime() <= otherJustDate.getTime();
};
JustDate.prototype.isAfter= function(otherJustDate) {
    return this.getTime() > otherJustDate.getTime();
};
JustDate.prototype.isAfterOrSameAs = function(otherJustDate) {
    return this.getTime() >= otherJustDate.getTime();
};
JustDate.spanDays = function(justDate1, justDate2) {
    return Math.round((justDate2.getTime() - justDate1.getTime()) / (60 * 60 * 24 * 1000));
};

export default JustDate;
