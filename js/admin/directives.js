'use strict';

(function(){
	angular.module('quotes.admin')
		.directive('navBar', function () {
			return {
				restrict: 'E',
				templateUrl: 'html/admin/navBar.html'
			};
		})
		;
})()
