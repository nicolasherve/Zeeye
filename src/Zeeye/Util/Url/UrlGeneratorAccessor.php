<?php

namespace Zeeye\Util\Url;

/**
 * Trait used to provide convenient access to the UrlGenerator operation
 *
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
trait UrlGeneratorAccessor {
	
	public function url($url) {
		return UrlGenerator::generate($url);
	}
}
