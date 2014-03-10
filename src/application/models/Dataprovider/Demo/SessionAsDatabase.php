<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_Dataprovider_Demo_SessionAsDatabase
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_Dataprovider_Demo_SessionAsDatabase
{

    CONST DEMO_SESSION = 'test1234';

    protected $_database;
    protected $_dummyDb = array(
        'user' => array(
            'clientSample1' => array(
                '_id' => 'clientSample1',
                'status' => false,
                'group' => 'client',
                'label' => 'client sample 1',
                'email' => 'clientSample1'
            ),
            'clientSample2' => array(
                '_id' => 'clientSample2',
                'status' => false,
                'group' => 'client',
                'label' => 'client sample 2',
                'email' => 'clientSample2'
            ),
            'adminSample1' => array(
                '_id' => 'adminSample1',
                'status' => false,
                'group' => 'admin',
                'email' => 'adminSample1',
                'username' => 'adminSample1'
            ),
            'adminSample2' => array(
                '_id' => 'adminSample2',
                'status' => false,
                'group' => 'admin',
                'email' => 'adminSample2',
                'username' => 'adminSample2'
            )
        ),
        'domain' => array(
            'domainSample1' => array(
                '_id' => 'domainSample1',
                'name' => 'domainSample1.com',
                'owner' => 'clientSample1'
            ),
            'domainSample2' => array(
                '_id' => 'domainSample2',
                'name' => 'domainSample2.org',
                'owner' => 'clientSample1'
            )
        ),
        'node' => array(
            'nodeSample1' => array(
                '_id' => 'nodeSample1',
                'name' => 'nodeSample1',
                'ipAddress' => '192.168.0.1'
            )
        ),
        'log' => array(
            'update1' => array(
                '_id' => '7345632876301',
                'message' => 'log test message',
                'user' => 'phpunit'
            )
        ),
        'modules' => array(
            0 => array(
                '_id' => 0,
                'name' => 'deploymentSample1',
                'label' => 'Deployment Sample 1',
                'vendor' => 'sample',
                'description' => 'valid deployment sample 1',
                'repository' => array(
                    'name' => 'deployment/foo',
                    'version' => '0.0.1',
                    'url' => 'http://vendor.sample/foo',
                    'type' => 'vcs'
                )
            ),
            1 => array(
                '_id' => 1,
                'name' => 'deploymentNoFiles',
                'label' => 'Deployment Sample with not file definition',
                'vendor' => 'sample',
                'description' => 'invalid deployment sample 1',
                'repository' => array(
                    'name' => 'deployment/nofiles',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/foo',
                    'type' => 'vcs'
                )
            ),
            2 => array(
                '_id' => 2,
                'name' => 'deploymentInvalid',
                'label' => 'Deployment Sample with invalid file definition',
                'vendor' => 'sample',
                'description' => 'invalid deployment sample 2',
                'repository' => array(
                    'name' => 'deployment/invalid',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/foo',
                    'type' => 'vcs'
                )
            ),
            3 => array(
                '_id' => 3,
                'name' => 'deploymentNoRepo',
                'label' => 'Deployment Sample with no repository definition',
                'vendor' => 'sample',
                'description' => 'invalid deployment sample 3'
            ),
            4 => array(
                '_id' => 4,
                'name' => 'sampleModule1',
                'label' => 'sample Module 1',
                'vendor' => 'sample',
                'description' => 'sample 1',
                'repository' => array(
                    'name' => 'sample/1',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample1',
                    'type' => 'vcs'
                ),
                'config' => array(
                    'clients' => array(
                        'clientSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'domains' => array(
                        'domainSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'nodes' => array(
                        'nodeSample1' => array(
                            'foo' => 'bar'
                        )
                    )
                )
            ),
            5 => array(
                '_id' => 5,
                'name' => 'sampleModule2',
                'label' => 'sample Module 2',
                'vendor' => 'sample',
                'description' => 'sample for installed module',
                'installed' => true,
                'repository' => array(
                    'name' => 'sample/2',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample2',
                    'type' => 'vcs'
                ),
                'config' => array(
                    'clients' => array(
                        'clientSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'domains' => array(
                        'domainSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'nodes' => array(
                        'nodeSample1' => array(
                            'foo' => 'bar'
                        )
                    )
                )
            ),
            6 => array(
                '_id' => 6,
                'name' => 'sampleModule3',
                'label' => 'sample Module 3',
                'vendor' => 'sample',
                'description' => 'sample for updateable module',
                'updateable' => true,
                'installed' => true,
                'repository' => array(
                    'name' => 'sample/3',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample3',
                    'type' => 'vcs'
                ),
                'update' => array(
                    'name' => 'sampleModule3',
                    'label' => 'sample Module 3',
                    'vendor' => 'sample',
                    'description' => 'sample for update set',
                    'repository' => array(
                        'name' => 'sample/3',
                        'version' => '1.0.1',
                        'url' => 'http://vendor.sample/sample3',
                        'type' => 'vcs'
                    )
                )
            ),
            7 => array(
                '_id' => 7,
                'name' => 'sampleModule4',
                'label' => 'sample Module 4',
                'vendor' => 'sample',
                'description' => 'sample 4',
                'repository' => array(
                    'name' => 'sample/4',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample4',
                    'type' => 'vcs'
                )
            ),
            8 => array(
                '_id' => 8,
                'name' => 'sampleModule5',
                'label' => 'sample Module 5',
                'vendor' => 'sample',
                'description' => 'sample 5',
                'repository' => array(
                    'name' => 'sample/5',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample5',
                    'type' => 'vcs'
                )
            ),
            9 => array(
                '_id' => 9,
                'name' => 'sampleModule6',
                'label' => 'sample Module 6',
                'vendor' => 'sample',
                'description' => 'sample 6',
                'repository' => array(
                    'name' => 'sample/6',
                    'version' => 'no Version',
                    'url' => 'http://vendo',
                    'type' => 'unknown'
                )
            ),
            10 => array(
                '_id' => 10,
                'name' => 'sampleModule7',
                'label' => 'sample Module 7',
                'vendor' => 'sample',
                'description' => 'sample 7',
                'installed' => true
            ),
            11 => array(
                '_id' => 11,
                'name' => 'sampleModule8',
                'label' => 'sample Module 8',
                'vendor' => 'sample',
                'description' => 'sample 8',
                'installed' => true,
                'repository' => array(
                    'name' => 'sample/8',
                    'version' => '1.0.0',
                    'url' => 'http://vendor.sample/sample8',
                    'type' => 'vcs'
                ),
                'config' => array(
                    'clients' => array(
                        'clientSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'domains' => array(
                        'domainSample1' => array(
                            'foo' => 'bar'
                        )
                    ),
                    'nodes' => array(
                        'nodeSample1' => array(
                            'foo' => 'bar'
                        )
                    )
                )
            )
        ),
        "news" => array(
            4456780 => array(
                "_id"           => "4456780",
                "title"         => "oldestCreatedNews",
                "content"       => "content of announcement",
                "teaser"        => "content of ...",
                "status"        => "public",
                "creator"       => 1,
                "onlineSince"   => "1000011145",
                "creationDate"  => "1000011145",
                "modifiedDate"  => "1000011145",
                "sticky"        => true,
                "tags"          => array("announcement", "intern", "two", "once")
            ),
            4456781 => array(
                "_id"           => "4456781",
                "content"       => "content of announcement",
                "status"        => "public",
                "creator"       => 1,
                "onlineSince"   => "1000012345",
                "creationDate"  => "1000012345",
                "modifiedDate"  => "1000012345",
                "tags"          => array("announcement", "intern", "two")
            ),
            4456782 => array(
                "_id"           => "4456782",
                "title"         => "findMeNow",
                "content"       => "content of announcement",
                "teaser"        => "content of ...",
                "status"        => "public",
                "creator"       => 1,
                "onlineSince"   => "1000022345",
                "creationDate"  => "1000022344",
                "modifiedDate"  => "1000022345",
                "sticky"        => true,
                "tags"          => array("intern")
            ),
            4456783 => array(
                "_id"           => "4456783",
                "title"         => "news test",
                "content"       => "content of announcement",
                "teaser"        => "content of ...",
                "status"        => "public",
                "creator"       => 1,
                "onlineSince"   => "1000012345",
                "creationDate"  => "1000012345",
                "modifiedDate"  => "1000012345",
                "sticky"        => false,
                "tags"          => array("id000" => "announcement",
                                         "id001" => "intern")
            ),
            4456784 => array(
                "_id"           => "4456784",
                "title"         => "news test",
                "content"       => "content of announcement",
                "teaser"        => "content of ...",
                "status"        => "draft",
                "creator"       => 1,
                "onlineSince"   => "1000012345",
                "creationDate"  => "1000012345",
                "modifiedDate"  => "1000012345",
                "sticky"        => false,
                "tags"          => null
            )
        )
    );

    /**
     * inits demo data
     */
    public function __construct()
    {
        $demoSession = new Zend_Session_Namespace(self::DEMO_SESSION);

        if ($demoSession->database) {
            $this->_database = $demoSession->database;
        } else {
            $demoSession->database = $this->_getDatabase($forceDatabase = true);
        }
    }
    
    /**
     * adds a certain data to database
     * 
     * @param array $collectionData
     */
    protected function _addCollection(array $collectionData)
    {
        $database = $this->_getDatabase();

        foreach ($collectionData as $collection => $data) {
            if (!array_key_exists($collection, $database)) {
                $database[$collection] = $data;
            }
        }
    }

    /**
     * generate rnd id
     * 
     * @return string
     */
    protected function _generateId()
    {
        return md5(time());
    }
    
    /**
     * get data of a certain dummy collection
     * 
     * @param string $collectionName
     * @return array
     */
    protected function _getCollection($collectionName)
    {
        $database = $this->_getDatabase();
        if (!array_key_exists($collectionName, $database)) {
            return array();
        }

        return $database[$collectionName];
    }

    /**
     * get dummy database
     * 
     * @param bollean $forceDatabase
     * @return array
     */
    protected function _getDatabase($forceDatabase = false)
    {
        $demoSession = new Zend_Session_Namespace(self::DEMO_SESSION);

        if ($forceDatabase || !$demoSession->database) {
            return $this->_dummyDb;
        }

        $database = $demoSession->database;

        return $database;
    }
    
    /**
     * sets dataset of a certain dummy collection
     * 
     * @param string $collection
     * @param array $collectionData
     */
    protected function _setCollection($collection, array $collectionData)
    {
        $demoSession = new Zend_Session_Namespace(self::DEMO_SESSION);
        $demoSession->database[$collection] = $collectionData;
    }

}
