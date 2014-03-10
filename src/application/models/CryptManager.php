<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_CryptManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_CryptManager
{
    /**
     * ciphername constants or the name of the algorithm as string
     */
    CONST CIPHER = "blowfish";

    /**
     * characters for randomize
     */
    CONST CHARSET = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    /**
     * length in creating process for new key
     */
    CONST GENKEY_LENGTH = 32;

    /**
     * message when key in file couldn't found
     */
    CONST KEY_NOT_FOUND = "Key not found in configuration file";

    /**
     * path to config
     */
    CONST CONFIG_PATH = Core_Model_InstallManager::CONFIG_PATH;

    /**
     * file name of config
     */
    CONST CONFIG_FILE = Core_Model_InstallManager::CONFIG_FILE;

    /**
     * secret key for encrypt / decrypt
     *
     * @var string|null
     */
    protected $_securekey = null;

    /**
     * loads the security key from the file system
     * 
     * @return null|string
     */
    protected function _loadSecurityKey()
    {
        $filename = APPLICATION_PATH . self::CONFIG_PATH . self::CONFIG_FILE;
        if (file_exists($filename)){
            $config = new Zend_Config_Ini(APPLICATION_PATH . self::CONFIG_PATH . self::CONFIG_FILE);
            if ($config->get("security") && $config->get("security")->get("hash")){
                $this->_securekey = $config->get("security")->get("hash");
            }
        }

        if (is_null($this->_securekey)){
            Core_Model_DiFactory::getMessageManager()->addError(self::KEY_NOT_FOUND);
        }

        return $this->_securekey;
    }

    /**
     * decrypt crypttext
     * 
     * @param  string $crypttext string that will be decrypted
     * @return string decrypted string
     */
    public function decrypt($crypttext)
    {
       $vector = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER, MCRYPT_MODE_CBC), MCRYPT_RAND);
       $decode = trim(mcrypt_decrypt(self::CIPHER, $this->getSecureKey(), base64_decode($crypttext), MCRYPT_MODE_ECB, $vector));

       return $decode;
    }

    /**
     * encrypt plaintext
     * 
     * @param  string $plaintext string that will be encrypted
     * @return string encrypted string
     */
    public function encrypt($plaintext)
    {
        $vector = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $encode = base64_encode(mcrypt_encrypt(self::CIPHER, $this->getSecureKey(), $plaintext, MCRYPT_MODE_ECB, $vector));

        return $encode;
    }

    /**
     * generates a new random key
     * 
     * @return string
     */
    public function generateKey()
    {
        $length = self::GENKEY_LENGTH;
        $randstr = "";
        $rlength = $length;
        $charset = self::CHARSET;
        $counter = strlen($charset);
        while ($rlength--) {
            $randstr .= $charset[mt_rand(0, $counter -1)];
        }

        $salt = base64_encode("$$randstr$". uniqid() ."$");

        if (self::CIPHER == "blowfish"){
            if ($length > 56)
                $length = 56;
            $salt = str_replace("+", ".", $salt);
        } else if (self::CIPHER == "rijndael-128" && $length > 56){
            $length = 32;
        }

        $salt = substr($salt, 0, $length);

        return $salt;
    }

    /**
     * gets the security key
     * 
     * @return string|null
     */
    public function getSecureKey()
    {
        if (is_null($this->_securekey)){
            $this->_loadSecurityKey();
        }

        return $this->_securekey;
    }
}