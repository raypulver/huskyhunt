angular.module('huskyhunt.services', [])
.factory('Scoreboard', function () {
  var scores = [
    {
      name: 'rip12002',
      value: 250
    },
    {
      name: 'ats11015',
      value: 240
    },
    {
      name: 'jes13020',
      value: 140
    },
    {
      name: 'mtb10004',
      value: 130
    },
    {
      name: 'urk10010',
      value: 130
    },
    {
      name: 'erm11007',
      value: 120
    },
    {
      name: 'shp08009',
      value: 110
    },
    {
      name: 'ity11001',
      value: 100
    },
    {
      name: 'cam08029',
      value: 90
    }
  ];
  return {
    getScores: function () {
      return scores;
    }
  }
})
.factory('Answer', function () {
  var answers = [
    3,
    1,
    3,
    1,
    3,
    3,
    1
  ];
  return {
    attempt: function (question, answer) {
      try {
        if (answers[question] == answer) {
          console.log('It worked!');
          return 50;
        } else {
          console.log('Unsuccessfully attempting question ' + question + ' with answer ' + answer);
          return -10;
        }
      }
      catch(err) {
        return 0;
      }
    }
  }
});
