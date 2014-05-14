var filters = angular.module("mazeFilters", []);

filters.filter('filterObject', function () {
    return function (items, search) {
        var result = [];

        if(!search || typeof search !== 'object' || angular.element.isEmptyObject(search)) {
            result = items;
        } else {
            angular.forEach(items, function (value, key) {
                var failed = false;

                angular.forEach(search, function(value2, key2) {
                    if(value2 === undefined || failed === true) {
                        return false;
                    }
                    if(value[key2] === undefined) {
                        failed = true;
                        return false;
                    };

                    if(typeof value2 !== 'string') {
                        if(value2 !== value[key2]) {
                            failed = true;
                        }
                    } else if (typeof value2 === 'string') {
                        var regex = new RegExp(value2);
                        if(!regex.test(value[key2])) {
                            failed = true;
                        }
                    }
                });

                if(failed === false) {
                    result.push(value);
                }
            });
        }

        return result;
    }
});