'use strict';

(function(){
	angular.module('quotes')
		.factory('quotesRepository', function($http, $q){
			var self = {};
			self.getYears = function() {
				var deferred = $q.defer();
				$http.get('php/quoteYears.php')
					.success(function(years){ deferred.resolve(years); })
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			self.getActiveQuoted = function(active) {
				var deferred = $q.defer();
				$http.get('php/quoted.php')
					.success(function(quoted){
						angular.forEach(quoted, function( q ) {
							delete q.active;
							return q;
						});
						deferred.resolve(quoted);
					})
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			self.getQuoted = function(active) {
				var deferred = $q.defer();
				$http.get('php/quoted.php?all')
					.success(function(quoted){ deferred.resolve(quoted); })
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			self.getQuotesByYear = function(year) {
				var deferred = $q.defer();
				$http.get('php/quotesByYear.php',{ params: { y: year } })
					.success(function(quotes){
						angular.forEach(quotes, function(quote) { quote.year = year; } );
						deferred.resolve(quotes);
					})
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			self.getQuotesByQuoted = function(quoted) {
				var deferred = $q.defer();
				$http.get('php/quotesByQuoted.php',{ params: { q: quoted.id } })
					.success(function(quotes){ deferred.resolve(quotes); })
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			self.uploadQuote = function(newQuote) {
				var deferred = $q.defer();
				$http.get('php/newQuote.php', { params:
					{
						"quoted[0]": newQuote.quoted.id,
						quote: newQuote.quote
					}})
					.success(function(result){
						if(result.error){
							deferred.reject(result);
						} else {
							deferred.resolve(result);
						}
					})
					.error(function(error){ deferred.reject(error); })
					;
				return deferred.promise;
			};
			return self;
		});
})()
