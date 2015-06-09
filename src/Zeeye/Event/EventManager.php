<?php

namespace Zeeye\Event;

use Zeeye\App\App;
use Zeeye\Event\Event;
use Zeeye\Event\EventListener;
use Zeeye\Util\Exception\InvalidTypeException;

/**
 * Class used to manage the events
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
class EventManager {

    /**
     * Instances of listeners
     * 
     * @var array
     */
    private static $_listeners = null;

    private static function _defineListeners() {
        // If the listeners are already set
        if (isset(self::$_listeners)) {
            return;
        }

        // Get the registered listeners (class names)
        $classNames = App::getInstance()->getAppConfiguration()->getEventListeners();

        // If there is no defined listeners
        if (empty($classNames)) {
            self::$_listeners = array();
        }

        // For each registered class name
        foreach ($classNames as $className) {

            // Create a new listener instance
            $eventListener = new $className();

            // If the class does not implement EventListener, throw an exception
            if (!$eventListener instanceof EventListener) {
                throw new InvalidTypeException('The class named [' . $className . '] is not valid: EventListener expected, [%s] given instead', $eventListener);
            }

            // Store the event listener
            self::$_listeners[] = $eventListener;
        }
    }

    public static function dispatch($mixed, array $parameters = array()) {
        // Make sure the listeners are loaded
        self::_defineListeners();

        // Check the given parameters
        $event = null;
        if ($mixed instanceof Event) {
            $event = $mixed;
        } elseif (is_string($mixed)) {
            $event = Event::create($mixed, $parameters);
        } else {
            throw new InvalidTypeException('The given $mixed parameter is not valid: Event or string expected, [%s] given instead', $mixed);
        }

        // For each registered listener
        foreach (self::$_listeners as $listener) {

            //Get the list of handled events from the listener
            $handledEvents = $listener->getHandledEvents();

            // If the given event is not handled by the listener
            if (!isset($handledEvents[$event->getName()])) {
                continue;
            }

            // Get the method name expected to handle the event
            $methodName = $handledEvents[$event->getName()];

            // If the method name is empty
            if (empty($methodName)) {
                throw EventManagerException();
            }

            // If the method name does not refer to a method of the listener
            if (!method_exists($listener, $methodName)) {
                throw EventManagerException("No method named [$methodName] can be found for EventListerner [" . get_class($listener) . "]");
            }

            // Execute the listener's method
            $listener->$methodName($event);
        }
    }

}
