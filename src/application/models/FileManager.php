<?php
/**
 * Maze.core
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Core_Model_FileManager
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
class Core_Model_FileManager
{

    /**
     * zend file adapter
     * 
     * @var Zend_File_Transfer_Adapter_Abstract
     */
    protected $_fileAdapter;

    /**
     * @var ResourceBundle
     */
    protected $_stream;

    /**
     * @return type
     */
    protected function _getStream()
    {
        return $this->_stream;
    }
    
    /**
     * @param type $stream
     */
    protected function _setStream($stream)
    {
        $this->_stream = $stream;
    }
    
    /**
     * saves an base64 encoded data to a file
     * 
     * @param  string $file
     * @param  string $base64
     * @return boolean
     */
    public function base64DecodeToFile($file, $data)
    {
        if (!$data) {
            return false;
        }

        if (preg_match("/data:.+base64\,(.*)/", $data, $matches)) {
            $data = $matches[1];
        }

        $data = base64_decode($data);

        $this->openFile($file);
        $this->write($data);
        $this->save();

        return true;
        
    }
    
    /**
     * return images as base64 encrypted data uri
     * 
     * @param  string $file
     * @param  string $mime mediatype
     * @return string|null data fragment
     */
    public function base64EncodeImage($file, $mime = "image/png")
    {
        if (Zend_Loader::isReadable($file) && $file != null) {
            $binary = fread(fopen($file, "r"), filesize($file));
            $base64 = base64_encode($binary);

            return "data:$mime;base64,$base64";
        }

        return null;
    }
    
    /**
     * creates given directory
     * 
     * @param string $pathname
     * @param integer $mode
     * @param boolen $recursive
     * @return boolen 
     */
    public function createFolder($pathname, $recursive = false, $mode = 0755){
        $result = false;
        
        if (!is_dir($pathname) && !is_file($pathname)) {
            $result = mkdir($pathname, $mode, $recursive);
        }

        return $result;
    }
    
   /**
    * deletes a file
    *
    * @param string $file
    * @return boolean 
    */
   public function fileDelete($file)
    {
        if (file_exists($file) && is_file($file)) {
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * recursively folder copy
     * 
     * @param string $source
     * @param string $target
     */
    function fullCopy($source, $target) {
        if (is_dir($source)) {
            @mkdir($target);

            $d = dir($source);

            while (FALSE !== ( $entry = $d->read() )) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }
    
    /**
     *
     * @return Zend_File_Transfer_Adapter_Http
     */
    public function getFileTransferAdapter()
    {
        if ($this->_fileAdapter == null) {
            $this->setFileTransferAdapter(new Zend_File_Transfer_Adapter_Http());
        }

        return $this->_fileAdapter;
    }
    
    /**
     * detects the MIME Content-type for a file
     * 
     * @param  string $file
     * @return string|null
     */
    public function mimeContentType($file)
    {
        $mime = null;
        if (file_exists($file)){
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            $mime = finfo_file($finfo, $file);
            finfo_close($finfo);
        }

        return $mime;
    }

    /**
     * Opens file for further interaction
     * 
     * @param string $file
     * @param boolean $overwrite
     * @throws RuntimeException
     */
    public function openFile($file, $overwrite = false)
    {
        $mode = 'a';
        if($overwrite) {
            $mode = 'w';
        }
        
        // surpress warnings to get error
        $stream = @fopen($file, $mode);    

        if($stream === false) {
            throw new RuntimeException("Can't open File $file");
        }

        $this->_setStream($stream);
    }
    
    /**
     * converts image to another content-type
     * 
     * @param  string $file
     * @param  string $extension
     * @return boolean
     */
    public function imageConvertTo($file, $extension)
    {
        if ($extension == "jpg") {
            $extension = "jpeg";
        }

        $mime = explode("/", $this->mimeContentType($file));
        if (!isset($mime[0]) || !isset($mime[1]) || $extension == $mime[1]){
            return false;
        }

        $image = call_user_func("imagecreatefrom". $mime[1], $file);
        call_user_func("image". $extension, $image, $file);

        return imagedestroy($image);
    }

    /**
     * resize an existing image with GD library
     * 
     * @param  string $file path to the image
     * @param  integer $newWidth
     * @param  string $targetName [optional] the name to save the filename to
     * @return string|boolean image source when converted
     */
    public function imageResize($file, $newWidth, $targetName = null, $stretch = false)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension == "jpg") {
            $extension = "jpeg";
        }
        
        // convert to the correct image if extension and file mime mishmash
        $this->imageConvertTo($file, $extension);

        $functionCreate = "imagecreatefrom". $extension;
        $functionOutput = "image". $extension;
        
        if (!function_exists($functionCreate) || !function_exists($functionOutput) || !extension_loaded('gd')) {
            return false;
        }

        list($width, $height) = getimagesize($file);
        $newHeight = $height* $newWidth / $width;
        if ($stretch) {
            $newHeight = $newWidth;
        }

        $image  = imagecreatetruecolor($newWidth, $newHeight);
        $source = call_user_func($functionCreate, $file);

        // set transparency
        imagefill($image, 0, 0, imagecolortransparent($image, imagecolorallocate($image, 0, 0, 0)));        
        imagealphablending($image, false);
        imagesavealpha($image,true);
        
        if ($targetName == null) {
            $targetName = $file;
        }

        imagecopyresized($image, $source, 0, 0, 0, 0,$newWidth, $newHeight, $width, $height);
        if (call_user_func_array($functionOutput, array($image, $targetName))) {
            return $image;
        }

        return false;
    }
    
    /**
     * receive the file information from the client (upload)
     * 
     * @param  string $filename
     * @param  string $property http post file property
     * @return boolean|array
     */
    public function receiveHttpFileInfo($filename, $property = null)
    {
        $httpFiles = $_FILES;

        if (!is_array($httpFiles) || sizeof($httpFiles) == 0) {
            return false;
        }

        foreach ($httpFiles as $name => $file) {
            if (!isset($file["name"]) || empty($file["name"])) {
                continue;
            }
            
            if ($filename == ($file["name"] || $name)){
                if ($property && isset($file[$property])) {
                    return $file[$property];
                }
                
                return $file;
            }
        }

        return false;
    }
    
    /**
     * delete a folder and its content recursive
     * 
     * @param string $dir
     * @param boolean $onlyContent
     */
    function rrmdir($dir, $onlyContent = false)
    {
        foreach(new RecursiveDirectoryIterator($dir) as $file) {
            if($file->getFilename() === '.' || $file->getFilename() === '..') {
                continue;
            }
            
            if($file->isDir()) {
                $this->rrmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        if(!$onlyContent) {
            rmdir($dir);
        }
    }
    
    /**
     * close stream and save
     */
    public function save()
    {
        fclose($this->_getStream());
    }
    
    /**
     * @param Zend_File_Transfer_Adapter_Abstract $adapterName
     * @return Core_Model_FileManager 
     */
    public function setFileTransferAdapter(Zend_File_Transfer_Adapter_Abstract $adapterName)
    {
        $this->_fileAdapter = $adapterName;

        return $this;
    }
    
    /**
     * upload a file from the client
     * 
     * @param  string $filename file to receive
     * @param  string $destination destination for the given file
     * @param  string $newname rename uploaded file
     * @param  boolean $path shall the path be returned ?
     * @return boolean|string
     */
    public function uploadFile($filename, $destination, $newname = null, $path = false)
    {
        if (!is_dir($destination)){
            if(!$this->createFolder($destination, true)) {
                return null;
            }
        }

        $adapter = $this->getFileTransferAdapter();
        $adapter->setDestination($destination);

        if ((is_file($newname) && !is_writable($newname)) || !is_writable($destination)) {
            return null;
        }
        
        if(is_string($newname)){
            $adapter->addFilter('rename', array(
                "target"    => $newname,
                "overwrite" => true)
            );
        }

        if ($adapter->receive($filename)) {
            return $adapter->getFileName(null, $path);
        }

        return null;
    }
    
    /**
     * write in file
     * 
     * @param string $text
     */
    public function write($text)
    {
        fwrite($this->_getStream(), $text);
    }
    
}
