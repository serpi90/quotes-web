'use strict';

(function() {
	angular.module('quotes')
		.controller('NavBarController', function(quotesRepository, $scope) {
			var self = this;
			self.years = [];
			self.selectedYear = null;
			self.edit = 'quote';
			quotesRepository.getYears().then(function(data) {
				self.years = data;
				self.selectedYear = data[0];
			});
		});
})()
