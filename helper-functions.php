<?php
namespace Charter_Boat_Bookings;
use \Datetime;
use \DateTimeZone;
use \DateInterval;
/**
 * =======================================
 * HELPER FUNCTIONS
 * functions used in any context but not readily available in WP or PHP
 * =======================================
 */
//takes WP array of objects with only one attribute and returns a simple single dimensional array
function cb_wp_collapse($results, $fieldname){
	$array = array();
	foreach($results as $result){
		$array[]=$result->$fieldname;
	}
	return $array;
}



?>