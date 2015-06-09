<?php

namespace Zeeye\Response;

use Zeeye\Util\Exception\InvalidTypeException;
use Zeeye\View\View;
use Zeeye\Zone\Zone;

/**
 * Class for HTML responses
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class HtmlResponse extends DisplayableResponse {

    /**
     * Constructor
     * 
     * @param Zone|View|string $content the content of the response
     */
    protected function __construct($content) {
        // If the given content is not a Zone nor a View nor a string
        if (!$content instanceof Zone && !$content instanceof View && !is_string($content)) {
            throw new InvalidTypeException('The given $content parameter is not valid: string, View or Zone expected, [%s] given instead', $content);
        }

        // Parent constructor
        parent::__construct($content);

        // Set the content type
        $this->setContentType('text/html');
    }

    /**
     * Return a new instance
     * 
     * @param Zone|View|string $content the content of the response
     */
    public static function create($content) {
        return new HtmlResponse($content);
    }

}
