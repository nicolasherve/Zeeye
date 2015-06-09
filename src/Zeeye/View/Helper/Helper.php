<?php

namespace Zeeye\View\Helper;

use Zeeye\App\App;
use Zeeye\View\View;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Abstract class for all Helper objects
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
abstract class Helper {

    /**
     * The view using the current helper
     * 
     * @var View
     */
    protected $_view;

    /**
     * Get the view
     * 
     * @return View
     */
    public function getView() {
        return $this->_view;
    }

    /**
     * Set the view
     * 
     * @param View $view the view associated to the helper
     */
    public function setView(View $view) {
        $this->_view = $view;
    }

    /**
     * Factory method to create a Helper instance
     * 
     * The given name must corresponds to the configuration
     * 
     * @param $name name of the desired Helper instance
     * @param $view view to associate to the created helper instance
     * @return Helper the created Helper instance
     */
    public static function create($name, View $view) {
        // Get the requested helper class name
        $helperClassName = App::getInstance()->getAppConfiguration()->getHelper($name);

        // Instantiates the corresponding helper
        $helper = new $helperClassName();

        // If the helper does not extend the Helper class, throw an exception
        if (!$helper instanceof Helper) {
            throw new InvalidTypeException('The helper named [' . $name . '] is not valid: Helper expected, [%s] given instead', $helper);
        }

        // Set the view
        $helper->setView($view);

        // Call the setup method
        $helper->setup();

        return $helper;
    }

    public function setup() {
        
    }

}
