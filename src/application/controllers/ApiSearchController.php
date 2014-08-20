<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * ApiSearchController
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class ApiSearchController extends MazeLib_Rest_Controller
{
    /**
     * get paginated global search
     */
    public function getResourcesAction()
    {
        $searchAll = Core_Model_DiFactory::getSearchAll();
        $searchAll->setLimit($this->getParam('limit', 10))
                  ->setSearchTerm($this->getParam('search'));

        if($this->getParam('pagerAction') == 'last') {
            $searchAll->last();
        } else {
            $searchAll->page($this->getParam('page', 1));
        }

        $result = array();
        $search = $searchAll->asArray();
        foreach ($search["data"] as $id => $entry) {
            array_push($result, $entry);
        }

        $this->_helper->json->sendJson(array(
            "data"  => $result,
            "total" => $search["total"]
        ));
    }
}
