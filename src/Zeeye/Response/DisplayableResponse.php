<?php

namespace Zeeye\Response;

use Zeeye\App\App;
use Zeeye\Filter\Filter;
use Zeeye\Response\Response;
use Zeeye\Util\String\Utf8String;
use Zeeye\View\View;
use Zeeye\Zone\Zone;

/**
 * Abstract class for all displayable responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class DisplayableResponse extends Response {

    /**
     * The charset used in the HTTP header
     * 
     * @var string
     */
    protected $_charset;

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
    protected $_contentType;

    /**
     * The content length (in bytes)
     * 
     * @var integer
     */
    protected $_contentLength;

    /**
     * The cache duration (in seconds, 0 means no cache)
     * 
     * @var integer
     */
    protected $_cache;

    /**
     * List of the registered filters
     *
     * @var array
     */
    protected $_filters;

    /**
     * Constructor
     * 
     * @param mixed $content the content that will be used to create the response
     */
    protected function __construct($content) {
        parent::__construct();

        // Set the raw content
        $this->_setRawContent($content);
        // The final content is not set yet
        $this->_setContent(null);
        // The default charset is the one indicated in the configuration
        $this->setCharset(App::getInstance()->getAppConfiguration()->getDefaultCharset());
        // Get the filters
        $this->_filters = Filter::getAll();
    }

    /**
     * Send the response headers and prints its content
     */
    public function output() {
        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->beforeExecuteZones($this);
        }

        // Execute the eventual Zones
        $this->executeZones();

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->beforeGenerateContent($this);
        }

        // Generate the response's final content
        $this->generateContent();

        // Add the common headers
        $this->setHeader('Content-Type', $this->getContentType() . ';charset=' . $this->getCharset());
        
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

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->beforeDisplay($this);
        }

        // Sends the response's headers
        $this->_sendHeaders();
        // Prints the response's final content
        echo $this->getContent();

        // Filters callback
        foreach ($this->_filters as $filter) {
            $filter->afterDisplay($this);
        }
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
     * Get the response's raw content
     *
     * @return string the response's raw content
     */
    public function getRawContent() {
        return $this->_rawContent;
    }

    /**
     * Set the response's raw content
     *
     * @param string $rawContent the response's raw content
     */
    protected function _setRawContent($rawContent) {
        $this->_rawContent = $rawContent;
    }

    /**
     * Get the response's final content
     * 
     * @return string the response's final content
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * Set the response's final content
     * 
     * @param string $content the response's final content
     */
    protected function _setContent($content) {
        // Set the content
        $this->_content = $content;

        // Define the content length
        $length = 0;

        // If the charset is UTF-8 and the content of the response contains some UTF-8 characters
        if (Utf8String::isUtf8Code($this->_charset) && Utf8String::isUtf8($content)) {
            $length = Utf8String::length($content);
        } else {
            $length = strlen($content);
        }

        // Set the content length
        $this->_setContentLength($length);
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
     * Build the response's final content, assign it and return it
     * 
     * @return string the built content
     */
    public function generateContent() {
        // If the raw content is a View instance
        if ($this->_rawContent instanceof View) {
            $this->_setContent($this->_rawContent->render());
        }
        // The raw content is a string
        else {
            $this->_setContent($this->_rawContent);
        }

        return $this->getContent();
    }

}
