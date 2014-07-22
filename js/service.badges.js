angular.module('huskyhunt.service.badges', []).factory('Badges', function ($http) {
  return {
    get: function () {
      return $http.get('/api/badges.php');
    }
  }
});
