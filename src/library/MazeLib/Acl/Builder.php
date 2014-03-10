<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * MazeLib_Acl_Builder
 * 
 * @license http://opensource.org/licenses/MIT MIT
 */
class MazeLib_Acl_Builder
{

    /**
     * @var array
     */
    protected $_config;

    /**
     * @var MazeLib_Acl_Acl
     */
    protected $_acl;

    /**
     * @var boolean
     */
    protected $_baseState = false;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @param array $options 
     */
    public function __construct(MazeLib_Acl_Acl $acl = null, $options = null)
    {
        if($acl) {
            $this->_acl = $acl;
        } else {
            $this->_acl = new MazeLib_Acl_Acl();
        }
        
        $this->_options = $options;
    }

    public function getAcl()
    {
        return $this->_acl;
    }
    
    /**
     * @param Zend_Config $config 
     */
    public function addConfig(Zend_Config $config)
    {
        $this->_config = $config;

        $this->_buildAcl();
    }

    protected function _buildAcl()
    {
        $this->_setDefaultBehavior();
        $this->_createAclData();
        $this->_setPermissions();
    }

    public function _setDefaultBehavior()
    {
        // Standard der ACL setzen
        if (!isset($this->_options['base'])) {
            // Alles verbieten
            $this->_acl->deny();
            $this->_baseState = false;
        } else {
            if ($this->_options['base'] == 'allow') {
                // Alles erlauben
                $this->_acl->allow();
                $this->_baseState = true;
            } elseif ($this->_options['base'] == 'deny') {
                // Alles verbieten
                $this->_acl->deny();
                $this->_baseState = false;
            } else {
                // Alles verbieten
                $this->_acl->deny();
                $this->_baseState = false;
            }
        }
    }

    /**
     * @var void
     */
    protected function _createAclData()
    {
        // Wandel die Config in ein Array um
        $rolesArray = $this->_config->toArray();

        // Füge alle Variationen der ACL hinzu
        foreach ($rolesArray as $role => $ressources) {

            // Prüfe zuerst, ob die Rolle schon existiert
            if (!$this->_acl->hasRole($role)) {
                // Existiert nicht. Füge sie hinzu
                $roleObj = New Zend_Acl_Role($role);
                $this->_acl->addRole($roleObj);
            }

            // Prüfe, ob die Rolle Resourcen hat
            if (empty($ressources)) {
                continue;
            }

            // Lade die Ressourcen
            foreach ($ressources as $resource => $actions) {
                // Prüfe, ob die Ressource schon existiert
                if (!$this->_acl->has($resource)) {
                    // Existiert nicht. Füge sie hinzu
                    $resObj = new Zend_Acl_Resource($resource);
                    $this->_acl->addResource($resObj);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function _setPermissions()
    {
        // Wandel die Config in ein Array um
        $rolesArray = $this->_config->toArray();

        // Gehe die Daten durch um die Berechtigungen zu setzen
        foreach ($rolesArray as $role => $ressources) {

            // Prüfe, ob es überhaupt Ressourcen gibt
            if (empty($ressources)) {
                continue;
            }

            // Wenn eine Wildcard erstellt wurde, dann gib alle Ressourcen frei
            if (isset($ressources['*'])) {
                $this->_setAllowedOrDenied($role);
                continue;
            }

            // Gehe die Resourcen der Rolle durch
            foreach ($ressources as $ressource => $actions) {

                // Gehe die Actions durch und nutze den status
                foreach ($actions as $action => $state) {
                    // Prüfe, ob die Ressource auch Actions hat
                    if ($action == '*') {
                        // Hat keine Actions, alles freigeben
                        $action = null;
                    }

                    // Prüfe, ob Erlaubt oder Verboten werden soll
                    if ($state == 'allow') {
                        $state = true;
                    } else {
                        $state = false;
                    }

                    // Setze die Berechtigung
                    $this->_setAllowedOrDenied($role, $ressource, $action, $state);
                }
            }
        }
    }

    /**
     * @param 	Zend_Acl_Role_Interface|string|array 		$roles
     * @param 	Zend_Acl_Resource_Interface|string|array 	$resources
     * @param 	string|array 								$privileges
     * @return 	void
     */
    protected function _setAllowedOrDenied($roles = null, $resources = null, $privileges = null, $allow = null)
    {
        // Setze das Allow und Deny zu einer Struktur
        if ($allow == false) {
            $this->_acl->deny($roles, $resources, $privileges);
        } else {
            $this->_acl->allow($roles, $resources, $privileges);
        }
    }

}

