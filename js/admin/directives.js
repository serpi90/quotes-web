'use strict';

(function(){
	angular.module('quotes')
		.directive('editQuotes', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/admin/editQuotes.html'
			};
		})
		.directive('editQuoted', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/admin/editQuoted.html'
			};
		})
		.directive('editQuotedModal', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/admin/editQuotedModal.html'
			};
		})
		.directive('changeYear', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/admin/changeYear.html'
			};
		})
	;
})()
