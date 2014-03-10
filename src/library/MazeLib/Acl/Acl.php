<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Acl_Acl
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Acl_Acl extends Zend_Acl
{

    /**
     * @see Zend_Acl::isAllowed()
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        if (!empty($role) && !$this->hasRole($role)) {
            $roleObj = new Zend_Acl_Role($role);
            $this->addRole($roleObj);
            $this->deny($role);
        }

        if (!empty($resource) && !$this->has($resource)) {
            $resObj = new Zend_Acl_Resource($resource);
            $this->add($resObj);
            $this->deny($role, $resource);
        }

        return parent::isAllowed($role, $resource, $privilege);
    }

}

