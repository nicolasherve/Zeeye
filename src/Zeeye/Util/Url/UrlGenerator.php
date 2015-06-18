<?php

namespace Zeeye\Util\Url;

use Zeeye\App\App;
use Zeeye\Router\Router;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Class used to generate URLs from different sources
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class UrlGenerator {

    /**
     * Generate a URL from the given URL representation
     * 
     * @param mixed $url the URL representation
     * @return string
     */
    public static function generate($url) {
        if ($url instanceof Url) {
            return $url->toString();
        }
        if (is_array($url)) {
            $url = Router::getInstance()->generateUrlForRoute($url[0], isset($url[1]) ? $url[1] : array());
            return $url->toString();
        }
        if (is_string($url)) {
            if ($url[0] == '/') {
                return App::getInstance()->getConf()->getWebroot() . $url;
            }
            return $url;
        }

        throw new InvalidTypeException('The given URL has an invalid type: Url, array or string expected, [%s] given instead', $url);
    }

}
