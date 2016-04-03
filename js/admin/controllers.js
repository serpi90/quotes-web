'use strict';

(function() {
	angular.module('quotes')
		.controller('NavBarController', function(quotesRepository, $scope) {
			var self = this;
			self.years = [];
			self.selectedYear = null;
			self.edit = 'quoted';
			quotesRepository.getYears().then(function(data) {
				self.years = data;
				self.selectedYear = data[0];
			});
		})
		.controller('EditQuotedController', function(quotesRepository, $scope) {
			var self = this;
			self.quoted = [];
			self.current = {};
			self.openModal = function( toEdit ) {
				self.current = toEdit;
				$('#edit-quoted-modal').foundation('reveal','open');
			};

			quotesRepository.getQuoted().then(function(data) {
				self.quoted = data;
			});
		})

	;
})()
