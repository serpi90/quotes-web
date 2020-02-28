/*jslint indent: 2 */
"use strict";
function QuoteRepository() {
  this.getQuotesFromYear = function (year, callback) {
    if (callback && typeof callback === 'function') {
      jQuery.ajax({
        type: 'GET',
        url: 'php/quotesByYear.php',
        data: { y: year },
        success: callback,
      });
    } else {
      return jQuery.parseJSON(jQuery.ajax({
        type: 'GET',
        url: 'php/quotesByYear.php',
        data: { y: year },
        async: false
      }).responseText);
    }
  };
  this.getQuotesByQuoted = function (quotedID, callback) {
    if (callback && typeof callback === 'function') {
      jQuery.ajax({
        type: 'GET',
        url: 'php/quotesByQuoted.php',
        data: { q: quotedID },
        success: callback,
      });
    } else {
      var json = jQuery.ajax({
        type: 'GET',
        url: 'php/quotesByQuoted.php',
        data: { q: quotedID },
        async: false
      }).responseText;
      return json;
    }
  };
  this.addQuote = function (quote, quotedIDs, callback) {
    if (callback && typeof callback === 'function') {
      jQuery.ajax({
        type: 'GET',
        url: 'php/newQuote.php',
        data: {
          'quoted[]': quotedIDs,
          "quote": quote
        },
        success: callback,
      });
    } else {
      var json = jQuery.ajax({
        type: 'GET',
        url: 'php/newQuote.php',
        data: {
          'quoted[]': quotedIDs,
          "quote": quote
        },
        async: false
      }).responseText;
      return json;
    }
  };
  this.getQuoted = function (getAll, callback) {
    var data = getAll ? {all: true} : {};
    if (callback && typeof callback === 'function') {
      jQuery.ajax({
        type: 'GET',
        url: 'php/quoted.php',
        data: data,
        success: callback,
      });
    } else {
      return jQuery.parseJSON(jQuery.ajax({
        type: 'GET',
        url: 'php/quoted.php',
        data: data,
        async: false
      }).responseText);
    }
  };
  this.quoteYears = function (callback) {
    if (callback && typeof callback === 'function') {
      jQuery.ajax({
        url: 'php/quoteYears.php',
        success: callback,
      });
    } else {
      return jQuery.parseJSON(jQuery.ajax({
        url: 'php/quoteYears.php',
        async: false
      }).responseText);
    }
  };
}