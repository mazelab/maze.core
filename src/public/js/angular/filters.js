// Generated by CoffeeScript 1.7.1
(function() {
  var filters;

  filters = angular.module("maze.filters", []);

  filters.filter('filterObject', [
    function() {
      return function(items, search) {
        var result;
        result = {};
        if (typeof items === !'object') {
          return;
        }
        if (!search || typeof search === !'object' || angular.element.isEmptyObject(search)) {
          result = items;
        } else {
          angular.forEach(items, function(value, key) {
            var failed;
            failed = false;
            angular.forEach(search, function(value2, key2) {
              var regex;
              if (!value2 || failed) {
                return false;
              }
              if (value[key2] == null) {
                failed = true;
                return false;
              }
              if (typeof value2 === !'string') {
                if (value2 === !value[key2]) {
                  return failed = true;
                }
              } else {
                regex = new RegExp(value2);
                if (!regex.test(value[key2])) {
                  return failed = true;
                }
              }
            });
            if (!failed) {
              return result[key] = value;
            }
          });
        }
        return result;
      };
    }
  ]);

}).call(this);
