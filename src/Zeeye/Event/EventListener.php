<?php

namespace Zeeye\Event;

/**
 * Interface defining behavior for event listeners
 * 
 * @author     Nicolas Hervé <nherve@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php
 */
interface EventListener {

    public function getHandledEvents();
}
