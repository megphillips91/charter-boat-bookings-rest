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

//adjust WP Time to UTC Time; return Y-m-d H:i:s
function get_UTC_time($wp_time_string){
	$offset_hours = wp_date('P');
	$offset_chunks = explode(':', $offset_hours);
	$adjustment = str_replace( '0', '', $offset_chunks[0] );
	$adjustment = str_replace( '-', '', $adjustment );
	$adjustment = str_replace( '+', '', $adjustment );
	$wp_date_object = new DateTime($wp_time_string, new DateTimeZone(get_option('timezone_string')));
	$adjust = (str_contains($offset_hours, '-')) ? 'sub' : 'add';
	$wp_date_object->$adjust(new DateInterval('PT'.$adjustment.'H'));
	return $wp_date_object->format('Y-m-d H:i:s');
	//MEGTODO: ln18 faulty logic here because taking out the zero won't work for 10hr adjustments off UTC...is there a 10 hour adjustment from UTC?
}


//takes WP array of objects with only one attribute and returns a simple single dimensional array
function cb_wp_collapse($results, $fieldname){
	$array = array();
	foreach($results as $result){
		$array[]=$result->$fieldname;
	}
	return $array;
}

/**
 * global public helper functions
 */
function cb_prepare_phone($phone){
	$phone = str_replace(" ", "", $phone);
	$phone = str_replace("-", "", $phone);
	$phone = str_replace("(", "", $phone);
	$phone = str_replace(")", "", $phone);
	$phone = str_replace("+1", "", $phone);
	return $phone;
	}



?>