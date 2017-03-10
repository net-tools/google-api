<?php
/**
 * Calendar
 *
 * @author Pierre - dev@net-tools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\ServiceWrappers;



/**
 * Calendar helper
 *
 * Provides helper functions to get/set extended properties. Other method calls are transfered to the underlying service object.
 */
class Calendar extends ServiceWrapper
{
	/** 
     * Set an extended property in an event object
     *
     * @param \Google_Service_Calendar_Event $event Object describing the event to update with a new extended property value
     * @param string $propid The ID of the extended property
     * @param string $value The value of the extended property
     * @return \Google_Service_Calendar_Event The entry updated
     */
	public function setExtendedProperty(\Google_Service_Calendar_Event $event, $propid, $value)
	{
		// if the event object doesn't have a 'extendedProperties' property, create it
		if ( !$event->extendedProperties )
			$exp = new \Google_Service_Calendar_EventExtendedProperties();		
		else
			$exp = $event->extendedProperties;	

		// maybe we have to create the 'shared' subproperty
		$exp->shared or $exp->shared = array(); 
		
		// set extended property
		$exp->shared[$propid] = $value;
		$event->setExtendedProperties($exp);
		return $event;
	}
	
	
	/** 
     * Get an extended property value in an event object
     *
     * @param \Google_Service_Calendar_Event $event Object describing the event
     * @param string $propid The ID of the extended property
     * @return string|null The property value or null if unknown property
     */
	public function getExtendedProperty(\Google_Service_Calendar_Event $event, $propid)
	{
		if ( !$event->extendedProperties )
			return NULL;
		
		if ( !$event->extendedProperties->shared )
			return NULL;
			
		return $event->extendedProperties->shared[$propid];
	}
}

?>