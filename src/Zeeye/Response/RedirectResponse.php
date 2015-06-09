<?php
namespace Zeeye\Response;

use Zeeye\Response\Response;
use Zeeye\Response\RedirectResponseException;
use Zeeye\Util\Url\UrlGenerator;
/**
 * Class for redirections responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class RedirectResponse extends Response {
    /**
     * Constructor
     * 
     * @param mixed $url URL used for the redirection
     */
    protected function __construct($url) {
        parent::__construct();
        // Sets the default HTTP status code
        $this->setStatus(302);
        // Generate the url from the given source
        $location = UrlGenerator::generate($url);
        
        $this->setHeader('Location', $location);
    }

    /**
     * Return a new instance
     * 
     * @param mixed $url URL used for the redirection
     */
    public static function create($url) {
    	return new RedirectResponse($url);
    }
    
    /**
     * Send the response headers
     */
    public function output() {
    	$this->_sendHeaders();
    }
}