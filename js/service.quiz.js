angular.module('huskyhunt.service.quiz', [])
.factory('Quiz', function ($http) {
  return {
    attempt: function (question, answer) {
      question = parseInt(question);
      if (!isNaN(answer)) {
        answer = parseInt(answer);
      }
      return $http({
        method: 'post',
        url: '/api/try.php',
        data: {
          'q': question,
          'a': answer
        }
      });
    }
  }
});
