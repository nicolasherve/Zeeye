<?php
namespace Zeeye\Response;

use Zeeye\View\View; 
use Zeeye\Zone\Zone;
use Zeeye\Util\Exception\InvalidTypeException;
/**
 * Class for plain text responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class TextResponse extends DisplayableResponse {
	/**
     * The temporary content of the response
     * 
     * @var View|Zone|string
     */
    private $_tmpContent;
	
    /**
     * Constructor
     * 
     * @param Zone|View|string $content the content of the response
     */
    protected function __construct($content){
        parent::__construct();
        // If the given content is an object
        if (is_object($content)) {
        	// If the given content is a Zone instance
        	if ($content instanceof Zone) {
        		// The Zone is processed to generate the corresponding View or string
        		$content = $content->execute();
        	}
        	// If the given content is a View instance
        	elseif ($content instanceof View) {
        		// The Zones are processed to generate the corresponding View or string
        		$content->executeZones();
        	}
        	// If the given content is not a View instance
        	else {
        		throw new InvalidTypeException('The given $content parameter is not valid: string, View or Zone expected, [%s] given instead', $content);
        	}
        }
        // If the given content is not a string
        elseif (!is_string($content)) {
        	throw new InvalidTypeException('The given $content parameter is not valid: string, View or Zone expected, [%s] given instead', $content);
        }
        $this->_tmpContent = $content;
        $this->setContentType('text/plain');
    }
    
   	/**
   	 * Get the temporary content
   	 * 
   	 * @return string the temporary content
   	 */
    public function getTmpContent() {
    	return $this->_tmpContent;
    }
    
    /**
   	 * Set the temporary content
   	 * 
   	 * @param string $tmpContent the temporary content
   	 */
    public function setTmpContent($tmpContent) {
    	$this->_tmpContent = $tmpContent;
    }
    
	/**
     * Return a new instance
     * 
     * @param View|string $content the content of the response
     */
    public static function create($content) {
    	return new TextResponse($content);
    }
    
    /**
     * Build the response's final content
     */
    public function fetch() {
    	// If the temporary content is a View instance
    	if ($this->_tmpContent instanceof View) {
    		$this->_setContent($this->_tmpContent->render());
    	}
    	// The temporary content is a string
    	else {
    		$this->_setContent($this->_tmpContent);
    	}
    }
}