<?php
namespace Zeeye\View;

/**
 * Trait used to provide convenient operations to create views
 */
trait ViewGenerator {
	
	/**
     * Create and return a View instance
     *
     * @param string $filePath the path of the view file
     * @return View a view instance
     */
    public function createView($filePath = null) {
        return View::create($filePath);
    }
	
}