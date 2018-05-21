<?php
/**
 * RelLabelArrayProperty
 *
 * @author Pierre - dev@nettools.ovh
 * @license MIT
 */




namespace Nettools\GoogleAPI\Services\Misc;



/**
 * Class to store elements and implement interfaces ArrayAccess, Iterator and Countable for links, emails, etc. which are objects with rel or label properties
 *
 * Used as container for emails, phoneNumbers, etc.
 */
class RelLabelArrayProperty extends ArrayProperty 
{
	/**
	 * Search in array property objects whose rel property equals $rel
	 *
	 * @param string $rel
	 * @return array Returns an array of objects whose rel property matches $rel
	 */
	public function rel($rel)
	{
        $ret = array();
        foreach ( $this->_array as $e )
            if ( $e->rel == $rel )
                $ret[] = $e;
        
        return $ret;
	}
	
	
	
	/**
	 * Search in array property objects whose label property equals $label
	 *
	 * @param string $label
	 * @return array Returns an array of objects whose label property matches $label
	 */
	public function label($label)
	{
        $ret = array();
        foreach ( $this->_array as $e )
            if ( $e->label == $label )
                $ret[] = $e;
        
        return $ret;
	}
}

?>