<?php

namespace Zeeye\Response;

use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\Util\String\Json;
use Zeeye\View\View;

/**
 * Class for JSON responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class JsonResponse extends DisplayableResponse {

    /**
     * Constructor
     * 
     * @param mixed $content the content of the response
     */
    protected function __construct($content) {
        // Only resources are forbidden
        if (is_resource($content)) {
            throw new InvalidTypeException('The given $content parameter is not valid: resource given');
        }

        // Parent constructor
        parent::__construct($content);

        // Set the content type
        $this->setContentType('application/json');
    }

    /**
     * Build the response's final content
     * 
     * @return string the built content
     */
    public function generateContent() {
        // If the raw content is a View instance
        if ($this->_rawContent instanceof View) {
            $this->_setContent($this->_rawContent->render());
        }
        // The raw content is anything else
        else {
            $this->_setContent(Json::encode($this->_rawContent));
        }

        return $this->getContent();
    }

    /**
     * Return a new instance
     * 
     * @param array|string $content the content of the response
     */
    public static function create($content) {
        return new JsonResponse($content);
    }

}
