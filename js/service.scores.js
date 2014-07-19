angular.module('huskyhunt.service.scores', []).factory('Scores', function ($http) {
  return {
    get: function () { return $http.get('/api/scores.php'); },
  }
});
