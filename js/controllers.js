'use strict';

(function() {
	angular.module('quotes')
		.controller('NewQuoteController', function(quotesRepository, $sce, $scope) {
			var self = this,
			emptyQuote = function(){
				return {
					quote: '',
					quoted: []
				};
			};
			self.quoted = [];
			self.search = {};
			self.new = emptyQuote();
			self.result = {
				show: false,
				message: "message",
				class: "success"
			};
			self.trustAsHtml = function(value) { return $sce.trustAsHtml(value); };
			self.submit = function() {
				quotesRepository.uploadQuote(self.new)
					.then(function(successResult) {
						self.result.show = true;
						if( successResult.mail ) {
							self.result.message = "Quote subida exitosamente";
							self.result.class = "success";
						} else {
							self.result.message = "Quote subida exitosamente, pero no se pudo enviar el mail.";
							self.result.class = "warning";
						}
						self.new = emptyQuote();
						$scope.$emit('newQuote');
					}, function(errorResult) {
						self.result.show = true;
						self.result.message = errorResult.errorDescription;
						self.result.class = "alert";
						;
					});
			};
			self.hideAlert = function() {
				self.result.show = false;
			};
			quotesRepository.getActiveQuoted().then(function(data) {
				self.quoted = data;
			});
		})
		.controller('QuotesController', function(quotesRepository, $scope) {
			var self = this, selectedYear, loaded = {years: false, quoted: false};
			self.selectedItem = null;
			self.selectList = [];
			self.years = [];
			self.quoted = [];
			self.quotes = [];
			self.openNewQuote = function() {
				$('#new-quote-modal').foundation('reveal', 'open');
			};

			// This is caled once self.years and self.quoted are filled.
			self.getSelectList = function() {
				
				angular.forEach(self.years, function(year) {
					var callback = function () {
						quotesRepository.getQuotesByYear(year).then(function(data) { self.quotes = data; });
					},
					item = {label: year, getQuotes: callback, group: "Año", order: 1};
					self.selectList.push(item);
				});
				angular.forEach(self.quoted, function(quoted) {
					var callback = function () {
						quotesRepository.getQuotesByQuoted(quoted).then(function(data) { self.quotes = data; });
					},
					item = {label: quoted.name, getQuotes: callback};
					if(quoted.active) {
						item.group = "Personas Actuales";
						item.order = 2;
					} else {
						item.group = "Ex-Mercap";
						item.order = 3; 
					}
					self.selectList.push(item);
				});
				self.selectedItem = self.selectList[0];
			};

			// Load the last year as starting page.
			quotesRepository.getYears().then(function(data) {
				self.years = data;
				self.selectedYear = data[0];
				quotesRepository.getQuotesByYear(self.selectedYear).then(function(data) { self.quotes = data; });
				loaded.years = true;
				// As i don't know which one is going to finish first, this or getQuoted(), the last one fills the list.
				if(loaded.quoted) { self.getSelectList(); }
			});
			// Load quoted information to be used in the select list.
			quotesRepository.getQuoted().then(function(data) {
				self.quoted = data;
				loaded.quoted = true;
				// As i don't know which one is going to finish first, this or getYears(), the last one fills the list.
				if(loaded.years) { self.getSelectList(); }
			});
			$scope.$on('newQuote', function(event, quote) {
				self.selectedItem.getQuotes();
			});
		});
})()
