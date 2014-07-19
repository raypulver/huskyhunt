angular.module('huskyhunt.service.modules', [])
.factory('Modules', function($http) {
  return {
    get: function(index) {
      if (index) {
        return $http.get('/api/modules.php?id=' + index);
      } else {
        return $http.get('/api/modules.php');
      }
    }
  }
});
