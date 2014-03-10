<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Module_Composer
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Module_Composer
{

    /**
     * error message when composer configuration is corrupted
     */
    CONST ERROR_COMPOSER_CORRUPT = 'Composer configuration is corrupted. Manuell restoring necessary';
    
    /**
     * key for composer vcs repo type
     */
    CONST REPO_VCS = 'vcs';

    /**
     * loaded composer
     * 
     * @var Zend_Config
     */
    protected $_composer;
    
    /**
     * composer directory
     * 
     * @var string
     */
    protected $_path;
    
    /**
     * initial composer settings
     * 
     * @var array
     */
    private $_composerInitialSettings = array(
        'name' => 'cdsinternetagentur/maze.dashboard-modules',
        'description' => 'maze module management for maze.dashboard with composer',
        'authors' => array(
            array(
                'name' => 'cds-internetagentur',
                'email' => 'info@cds-spremberg.de'
            )
        ),
        'minimum-stability' => 'stable',
        'config' => array(
            'vendor-dir' => '.'
        )
    );
    
    /**
     * set composer path on construct
     * 
     * @param string $path
     */
    public function __construct($path = null) {
        if($path) {
            $this->setComposerPath($path);
        }
    }
    
    /**
     * add repository entry of the given module in composer file
     * 
     * @param Core_Model_ValueObject_Module $module
     * @return boolean
     */
    protected function _addRepository(Core_Model_ValueObject_Module $module)
    {
        $repo = array(
            'url' => $module->getData('repository/url'),
            'type' => $module->getData('repository/type')
        );
        
        if(!$repo['url'] || !$repo['type'] || self::REPO_VCS !== $repo['type']) {
            return false;
        }
        
        $repositories = $this->_getRepositoriesAsArray();
        foreach($repositories as  $key => $repository) {
            if($repository['url'] === $repo['url']) {
                return true;
            }
        }

        $repositories[] = $repo;
        $this->getComposer()->repositories = $repositories;
        
        return true;
    }
    
    /**
     * adds module require to composer
     * 
     * @param Core_Model_ValueObject_Module $module
     * @return boolean
     */
    protected function _addRequire(Core_Model_ValueObject_Module $module)
    {
        if(!($name = $module->getData('repository/name')) || 
                !($version = $module->getData('repository/version'))) { 
            return false;
        }
        
        $require = $this->_getRequireAsArray();
        $require[$name] = $version;
        
        $this->getComposer()->require = $require;
        
        return true;
    }
    
    /**
     * gets repositories from composer
     * 
     * @return array
     */
    protected function _getRepositoriesAsArray()
    {
        if(!($this->getComposer()) || !($repositories = $this->getComposer()->repositories)) {
            return array();
        }
        
        return $repositories->toArray();
    }
    
    /**
     * gets requires from composer
     * 
     * @return array
     */
    protected function _getRequireAsArray()
    {
        if(!$this->getComposer() || !($requires = $this->getComposer()->require)) {
            return array();
        }
        
        return $requires->toArray();
    }
    
    /**
     * get composer service
     * 
     * @return Core_Service_Interface_Composer
     */
    protected function _getService()
    {
        return Core_Service_DiFactory::getComposer();
    }
    
    /**
     * load contents of composer file as zend config instance
     * 
     * @return Zend_Config_Json|null
     */
    protected function _loadComposer()
    {
        if(!($path = $this->getComposerFilePath()) || !file_exists($path)) {
            return new Zend_Config($this->_composerInitialSettings, true);
        }

        return new Zend_Config_Json($path, null, array('allowModifications' => true));
    }
    
    /**
     * removes repository entry of the given module
     * 
     * @param Core_Model_ValueObject_Module $module
     * @return boolean
     */
    protected function _removeRepository(Core_Model_ValueObject_Module $module)
    {
        if(!($repoUrl = $module->getData('repository/url'))) {
            return true;
        }
        
        $repositories = $this->_getRepositoriesAsArray();
        foreach($repositories as  $key => $repository) {
            if($repository['url'] === $repoUrl) {
                $repoKey = $key;
            }
        }
        
        if(isset($repoKey)) {
            unset($repositories[$repoKey]);
            
            if(!$repositories) {
                unset($this->getComposer()->repositories);
            } else {
                $this->getComposer()->repositories = $repositories;
            }
        }
        
        return true;
    }
    
    /**
     * removes respositpry entroy of the given module
     * 
     * @param Core_Model_ValueObject_Module $module
     * @return boolean
     */
    protected function _removeRequire(Core_Model_ValueObject_Module $module)
    {
        if(!($name = $module->getData('repository/name')) || !($require = $this->_getRequireAsArray())) { 
            return true;
        }

        if(!key_exists($name, $require)) {
            return true;
        }
        
        unset($require[$name]);
        
        if(!$require) {
            unset($this->getComposer()->require);
        } else {
            $this->getComposer()->require = $require;
        }
        
        return true;
    }
    
    /**
     * saves content of the actual composer instance into the composer.json file
     * 
     * @return boolean
     */
    protected function _saveComposer()
    {
        if(!$this->getComposer()) {
            return false;
        }
        
        $json = new Zend_Config_Writer_Json();
        $json->setPrettyPrint(true)->setConfig($this->getComposer());
        
        try {
            $json->write($this->getComposerFilePath());
            return true;
        } catch (Exception $exc) {
            Core_Model_DiFactory::getMessageManager()->addError($exc->getMessage());
            return false;
        }
    }
    
    /**
     * 
     * @return Zend_Config|null
     */
    public function getComposer($force = false)
    {
        if((!$this->_composer || $force)) {
            $this->_composer = $this->_loadComposer();
        }
        
        return $this->_composer;
    }
    
    /**
     * get composer content as array
     * 
     * @return array
     */
    public function getComposerAsArray()
    {
        if(!$this->getComposer()) {
            return array();
        }

        return $this->getComposer()->toArray();
    }
    
    /**
     * get composer file path
     * 
     * @return string|null
     */
    public function getComposerFilePath()
    {
        if(!$this->getComposerPath()) {
            return null;
        }
        
        return $this->getComposerPath() . DIRECTORY_SEPARATOR . 'composer.json';
    }
    
    /**
     * get composer path
     * 
     * @return string|null
     */
    public function getComposerPath()
    {
        return $this->_path;
    }
    
    /**
     * calls composer install
     * 
     * @return boolean
     */
    public function install()
    {
        if(!file_exists($this->getComposerFilePath())) { 
            return false;
        }
        
        return $this->_getService()->install($this->getComposerPath());
    }
    
    /**
     * validate the composer file
     * 
     * @param boolean $strict determines if missing composer file is a bad state
     * or just reset it then
     * @return boolean
     */
    public function isValid($strict = false)
    {
        if(!($path = $this->getComposerFilePath())) {
            return false;
        }
        if(($strict && !file_exists($path)) ||
                (!$strict && !file_exists($path) && !$this->resetComposer())) {
            return false;
        }
        
        if(!$this->_getService()->validate($this->getComposerPath())) {
            Core_Model_DiFactory::getMessageManager()->addError(self::ERROR_COMPOSER_CORRUPT);    
            return false;
        }
        
        return true;
    }
    
    /**
     * remove module from composer
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function removeModule($moduleName)
    {
        if(!($module = Core_Model_DiFactory::getModuleManager()->getModule($moduleName)) ||
                !$module->getData('repository/name')) {
            return false;
        }
        if(!$this->isValid(true)) {
            return false;
        }
        
        $oldConfig = clone $this->getComposer();
        if(!$this->_removeRepository($module) || !$this->_removeRequire($module)) {
            $this->_composer = $oldConfig;
            return false;
        }
        
        if(!$this->_saveComposer()) {
            $this->_composer = $oldConfig;
            return false;
        }
        
        if(!$this->updateModule($moduleName)) {
            $this->_composer = $oldConfig;
            $this->_saveComposer();
            return false;
        }

        return true;
    }
    
    /**
     * set composer file to initial settings
     * 
     * @return boolean
     */
    public function resetComposer()
    {
        $this->_composer = new Zend_Config($this->_composerInitialSettings, true);
        
        return $this->_saveComposer();
    }
    
    /**
     * set composer path
     * 
     * @param string $path
     * @return Core_Model_ComposerManager
     */
    public function setComposerPath($path = null)
    {
        if(!$path || is_string($path)) {
            $this->_path = $path;
        }
        
        return $this;
    }
    
    /**
     * adds repository dependencies of given module and installs it
     * 
     * restores older composer if something went wrong
     * 
     * @param string $moduleName name of the module
     * @return boolean
     */
    public function setModule($moduleName)
    {
        if(!($module = Core_Model_DiFactory::getModuleManager()->getModule($moduleName))
                || !$module->getData('repository')) {
            return false;
        }
        if(!$this->isValid()) {
            return false;
        }
        
        $oldConfig = clone $this->getComposer();
        if(!$this->_addRepository($module) || !$this->_addRequire($module)) {
            $this->_composer = $oldConfig;
            return false;
        }
        
        if(!$this->_saveComposer()) {
            $this->_composer = $oldConfig;
            return false;
        }
        
        if(!($this->updateModule($moduleName))) {
            $this->_composer = $oldConfig;
            $this->_saveComposer();
            return false;
        }
        
        return true;
    }
    
    /**
     * calls composer update on a certain maze module
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function update()
    {
        if(!file_exists($this->getComposerFilePath())) { 
            return false;
        }
        
        return $this->_getService()->update($this->getComposerPath());
    }
    
    /**
     * calls composer update on a certain maze module
     * 
     * @param string $moduleName
     * @return boolean
     */
    public function updateModule($moduleName)
    {
        if(!file_exists($this->getComposerFilePath())) { 
            return false;
        }
        if(!($module = Core_Model_DiFactory::getModuleManager()->getModule($moduleName)) ||
                !$module->getData('repository/name')) {
            return false;
        }
        
        return $this->_getService()->update($this->getComposerPath(), $module->getData('repository/name'));
    }
    
}
