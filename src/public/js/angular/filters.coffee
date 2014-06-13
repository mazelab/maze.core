filters = angular.module "maze.filters", []

filters.filter 'filterObject', [ () ->
  (items, search) ->
    result = []

    if not search or typeof search is not 'object' or not angular.element.isEmptyObject(search)
      result = items;
    else
      angular.forEach items, (value, key) ->
        failed = false;

        angular.forEach search, (value2, key2) ->
          return false if not value2 or failed

          if not value2[key2]?
            failed = true
            return false

          if typeof value2 is not 'string'
            failed = true if value2 is not value[key2]
          else
            regex = new RegExp(value2)
            failed = true if not regex.test(value[key2])

        result.push(value) if not failed

    return result
]