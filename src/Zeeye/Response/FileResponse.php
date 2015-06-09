<?php

namespace Zeeye\Response;

use Zeeye\App\App;
use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\Util\File\File;
use Zeeye\View\View;
use Zeeye\Zone\Zone;

/**
 * Class for all file responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class FileResponse extends Response {

    /**
     * The charset used in the HTTP header
     * 
     * @var string
     */
    private $_charset;

    /**
     * The name of the file when downloaded
     * 
     * @var string
     */
    private $_downloadFileName;

    /**
     * The file's path (if any)
     * 
     * @var string
     */
    private $_filePath;

    /**
     * The raw content of the response
     *
     * @var View|Zone|string
     */
    protected $_rawContent;

    /**
     * The final content that will be printed
     * 
     * @var string
     */
    protected $_content;

    /**
     * The content type
     * 
     * @var string
     */
    private $_contentType;

    /**
     * The content length (in bytes)
     * 
     * @var integer
     */
    private $_contentLength;

    /**
     * The cache duration (in seconds, 0 means no cache)
     *
     * @var integer
     */
    protected $_cache;

    /**
     * Constructor
     * 
     * @param string the content of the file or the file's path
     * @param string the content type
     */
    protected function __construct($contentOrFilePath, $contentType) {
        // Call the parent constructor
        parent::__construct();

        //If the argument corresponds to an existing file
        if (is_string($contentOrFilePath) && File::exists($contentOrFilePath)) {
            // Set the file path
            $this->_filePath = realpath($contentOrFilePath);
            // Default download is inline
            $this->displayInline();
        }
        // If the argument corresponds to a View
        elseif ($contentOrFilePath instanceof View || $contentOrFilePath instanceof Zone || is_string($contentOrFilePath)) {
            // Set the raw content
            $this->_rawContent = $contentOrFilePath;
            // Default download is inline
            $this->displayInline();
        }
        // Another type is an error
        else {
            throw new InvalidTypeException('The given $contentOrFilePath parameter is not valid: string expected, [%s] given instead', $contentOrFilePath);
        }

        // The default charset is the one indicated in the configuration
        $this->setCharset(App::getInstance()->getAppConfiguration()->getDefaultCharset());

        // If there is a given content type
        if (!empty($contentType)) {
            // Set the given content type
            $this->setContentType($contentType);
        }
    }

    /**
     * Return a new instance
     * 
     * @param string the content of the file or the file's path
     * @param string the content type
     */
    public static function create($contentOrFilePath, $contentType = null) {
        return new FileResponse($contentOrFilePath, $contentType);
    }

    /**
     * Execute eventual Zone instances contained in the raw content
     */
    public function executeZones() {
        // If the raw content is a Zone instance
        if ($this->_rawContent instanceof Zone) {
            $this->_rawContent = $this->_rawContent->execute();
        }
        // If the raw content is a View instance
        elseif ($this->_rawContent instanceof View) {
            $this->_rawContent->executeZones();
        }
    }

    /**
     * Build the response's final content and return it
     * 
     * @return string the built content
     */
    public function generateContent() {
        // If the raw content is a View instance
        if ($this->_rawContent instanceof View) {
            $this->_content = $this->_rawContent->render();
        }
        // The raw content is a string
        else {
            $this->_content = $this->_rawContent;
        }

        return $this->_content;
    }

    /**
     * Send the response headers and prints its content
     */
    public function output() {

        // Execute the eventual Zones
        $this->executeZones();

        // Generate the response's final content
        $this->generateContent();

        // The file that will be sent back to the client
        $file = null;

        // If there is no file path
        if (empty($this->_filePath)) {
            // If there is no explicit content type
            if (!isset($this->_contentType)) {
                // If the content will be downloaded as a file
                if (!empty($this->_downloadFileName)) {
                    // Use the file name to set the content type
                    $this->setContentType(File::getMimeType($this->_downloadFileName));
                }
                // If the content type is still undefined
                if (!isset($this->_contentType)) {
                    throw new FileResponseException('The content type is not defined for the response, please specify it');
                }
            }

            // Set the content length
            $this->_setContentLength(strlen($this->_content));

            // Create a temporary file resource
            $file = tmpfile();

            // Write the content to the temporary file
            fwrite($file, $this->_content);
        } else {
            // Set the file content length
            $this->_setContentLength(filesize($this->_filePath));

            // If there is no explicit content type
            if (!isset($this->_contentType)) {
                // Use the file name to set the content type
                $this->setContentType(File::getMimeType($this->_filePath));
            }

            // Create a file resource
            $file = fopen($this->_filePath, 'rb');
        }

        // If the file must be downloaded as an attachment
        if (!empty($this->_downloadFileName)) {
            $this->setHeader('Content-Disposition', 'attachment; filename="' . $this->_downloadFileName . '"');
        }
        // The file will be sent inline
        else {
            $this->setHeader('Content-Disposition', 'inline');
        }

        // Add the common headers
        $this->setHeader('Content-Type', $this->getContentType() . ';charset=' . $this->getCharset());
        if ($this->getContentLength() > 0) {
            $this->setHeader('Content-Length', $this->getContentLength());
        }

        $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s T', time()));

        // Handle eventual cache
        if (isset($this->_cache)) {
            if ($this->_cache == 0) {
                $this->setHeader('Cache-Control', 'no-store');
            } elseif ($this->_cache > 0) {
                // HTTP/1.0 cache
                $this->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + $this->_cache));
                // HTTP/1.1 cache, should overwrite previous Expires in appropriate clients
                $this->setHeader('Cache-Control', 'max-age=' . $this->_cache);
                // Make sure Pragma header isnot used
                header_remove('Pragma');
            }
        }

        // Sends the response's headers
        $this->_sendHeaders();

        // Send data in 16kb blocks
        $block = 1024 * 16;

        $start = 0;
        $end = $this->getContentLength() - 1;

        fseek($file, $start);

        while (!feof($file) && ($pos = ftell($file)) <= $end) {

            if ($pos + $block > $end) {
                // Don't read past the buffer.
                $block = $end - $pos + 1;
            }

            // Output a block of the file
            echo fread($file, $block);

            // Send the data now
            flush();
        }

        // Close the file
        fclose($file);
    }

    /**
     * The file will be downladed as an attachment with the given name
     * 
     * @param string $fileName the name of the attachment
     */
    public function downloadAs($fileName) {
        $this->_downloadFileName = $fileName;
    }

    /**
     * The file will be displayed inline
     */
    public function displayInline() {
        $this->_downloadFileName = null;
    }

    /**
     * Get the response's charset
     * 
     * @return string
     */
    public function getCharset() {
        return $this->_charset;
    }

    /**
     * Set the charset
     * 
     * @param string $charset the charset to use
     */
    public function setCharset($charset) {
        $this->_charset = $charset;
    }

    /**
     * Get the file's path (if any)
     * 
     * @return string
     */
    public function getFilePath() {
        return $this->_filePath;
    }

    /**
     * Get the content's type
     * 
     * @return string the content's type
     */
    public function getContentType() {
        return $this->_contentType;
    }

    /**
     * Set the content's type
     * 
     * @param string the content's type
     */
    public function setContentType($type) {
        $this->_contentType = $type;
    }

    /**
     * Get the content's length
     * 
     * @return integer the content's length
     */
    public function getContentLength() {
        return $this->_contentLength;
    }

    /**
     * Set the content's length
     * 
     * @param integer $length the content's length (in bytes)
     */
    protected function _setContentLength($length) {
        $this->_contentLength = $length;
    }

    /**
     * Set the cache duration
     *
     * @param integer $cache the cache duration
     */
    public function setCache($cache) {
        $this->_cache = $cache;
    }

}
