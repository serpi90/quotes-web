(function(){
	var app = angular.module('quotes', ['angular.filter']);
	app.factory('quotesRepository', function($http, $q){
		var self = {};
		self.getYears = function() {
			var deferred = $q.defer();
			$http.get('php/quoteYears.php')
				.success(function(years){ deferred.resolve(years); })
				.error(function(error){ deferred.reject(error); })
				;
			return deferred.promise;
		}
		self.getQuoted = function() {
			var deferred = $q.defer();
			$http.get('php/quoted.php?all')
				.success(function(quoted){ deferred.resolve(quoted); })
				.error(function(error){ deferred.reject(error); })
				;
			return deferred.promise;
		}
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
		}
		self.getQuotesByQuoted= function(quoted) {
			var deferred = $q.defer();
			$http.get('php/quotesByQuoted.php',{ params: { q: quoted.id } })
				.success(function(quotes){ deferred.resolve(quotes); })
				.error(function(error){ deferred.reject(error); })
				;
			return deferred.promise;
		}
		return self;
	});
	app.controller('QuotesController', function(quotesRepository){
		var self = this, selectedYear;
		self.selectedItem = null;;
		self.selectList = [];
		self.years = [];
		self.quoted = [];
		self.quotes = [];
		var loaded = { years: false, quoted: false };

		// This is caled once self.years and self.quoted are filled.
		self.getSelectList = function(){
			angular.forEach( self.years, function( year ){
				var callback = function ( ) {
					quotesRepository.getQuotesByYear( year ).then(function(data){ self.quotes = data; });
				},
				item = {label: year, getQuotes: callback, group: "Años", order: 1};
				self.selectList.push( item );
			} );
			angular.forEach( self.quoted, function( quoted ){
				var callback = function ( ) {
					quotesRepository.getQuotesByQuoted( quoted ).then(function(data){ self.quotes = data; });
				},
				item = {label: quoted.quoted, getQuotes: callback};
				if( quoted.active ) {
					item.group = "Actuales";
					item.order = 2;
				} else {
					item.group = "Ex-Mercap";
					item.order = 3; 
				}
				self.selectList.push( item );
			} );
			self.selectedItem = self.selectList[0];
		}

		// Load the last year as starting page.
		quotesRepository.getYears().then(function(data){
			self.years = data;
			self.selectedYear = data[0];
			quotesRepository.getQuotesByYear( self.selectedYear ).then( function(data) { self.quotes = data; } );
			loaded.years = true;
			// As i don't know which one is going to finish first, this or getQuoted(), the last one fills the list.
			if( loaded.quoted ) { self.getSelectList(); }
		})
		// Load quoted information to be used in the select list.
		quotesRepository.getQuoted().then(function(data){
			self.quoted = data;
			loaded.quoted = true;
			// As i don't know which one is going to finish first, this or getYears(), the last one fills the list.
			if( loaded.years ){ self.getSelectList(); }
		})
	})
	.directive('quoteAmount', function () {
		return {
			restrict: 'E',
			scope: { value: '@' },
			template: '<ng-pluralize count="{{value}}" when="{\'1\': \'1 quote\', \'other\':\'{{value}} quotes\' }"></ng-pluralize>'
		};
	})
	;
})()
