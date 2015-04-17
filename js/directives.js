'use strict';

(function(){
	angular.module('quotes')
		.directive('navBar', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/navBar.html'
			};
		})
		.directive('quotesList', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/quotesList.html'
			};
		})
		.directive('quoteAmount', function () {
			return {
				restrict: 'E',
				scope: { value: '@' },
				template: '<ng-pluralize count="{{value}}" when="{\'1\': \'1 quote\', \'other\':\'{{value}} quotes\' }"></ng-pluralize>'
			};
		})
		.directive('newQuoteModal', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/newQuoteModal.html'
			};
		})
		;
})()
