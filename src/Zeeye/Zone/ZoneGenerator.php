<?php
namespace Zeeye\Zone;

/**
 * Trait used to provide convenient operations to create zones
 */
trait ZoneGenerator {
	
	/**
     * Create and return a Zone instance
     * 
     * @param string $name the name refering to the zone
     * @return Zone the zone instance
     */
    public function createZone($name) {
        return Zone::create($name);
    }
	
}