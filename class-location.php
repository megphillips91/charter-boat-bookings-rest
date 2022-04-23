<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Bookings;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

/**
 * The assumption of this new plugin is to integrate with WP at the most basic level
 * WooCommerce can integrate, but you need an additional plugin to enable online payments for bookings
 */
class Charter_Location {
    public $id; //charter id
    public $name;
    public $lat;
    public $lng;
    public $address;
    public $directions; //full url
    public $region;



}


?>