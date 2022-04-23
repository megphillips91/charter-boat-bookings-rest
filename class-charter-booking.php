<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Boat_Bookings;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

/**
 * The assumption of this new plugin is to integrate with WP at the most basic level
 * WooCommerce can integrate, but you need an additional plugin to enable online payments for bookings
 */
class Charter_Booking {
    public $id; //charter id
    public $customer_id; //as to hold a unique ID of the customer from whatever underlying framework
    public $customer_phone; //string
    public $charter_name; //string
    public $charter_dateTime; //date string
    public $duration; //hours float
    public $start_location; //location_id
    public $end_location; //location_id
    public $seats; //number of seats if not private
    public $is_private; //boolean
    public $booking_status;
    public $booking_meta;
    public $date_object;



}


?>